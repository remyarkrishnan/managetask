<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard()
    {
        $response = $this->get('/tasks');
        $response->assertRedirect('/login');
    }

    public function test_users_can_view_the_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/tasks');
        $response->assertStatus(200);
    }

    public function test_admins_can_create_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->post('/tasks', [
            'title' => 'New Task',
            'description' => 'Task Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => '2026-12-31',
            'assigned_to' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    public function test_regular_users_cannot_create_tasks()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post('/tasks', [
            'title' => 'New Task',
            'description' => 'Task Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => '2026-12-31',
            'assigned_to' => $user->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_admins_can_update_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        
        $task = Task::create([
            'title' => 'Old Title',
            'description' => 'Old Desc',
            'priority' => 'low',
            'status' => 'pending',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($admin)->put('/tasks/' . $task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated Desc',
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => '2026-12-31',
            'assigned_to' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Title']);
    }

    public function test_users_can_update_assigned_tasks()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $task = Task::create([
            'title' => 'Old Title',
            'description' => 'Old Desc',
            'priority' => 'low',
            'status' => 'pending',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->put('/tasks/' . $task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated Desc',
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => '2026-12-31',
            'assigned_to' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Title']);
    }

    public function test_users_cannot_update_unassigned_tasks()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        
        $task = Task::create([
            'title' => 'Old Title',
            'description' => 'Old Desc',
            'priority' => 'low',
            'status' => 'pending',
            'assigned_to' => $user2->id,
        ]);

        $response = $this->actingAs($user1)->put('/tasks/' . $task->id, [
            'title' => 'Updated Title',
            'description' => 'Updated Desc',
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => '2026-12-31',
            'assigned_to' => $user2->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_admins_can_delete_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $task = Task::create([
            'title' => 'To Delete',
            'description' => 'Desc',
            'priority' => 'low',
            'status' => 'pending',
            'assigned_to' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete('/tasks/' . $task->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_regular_users_cannot_delete_tasks()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $task = Task::create([
            'title' => 'To Delete',
            'description' => 'Desc',
            'priority' => 'low',
            'status' => 'pending',
            'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete('/tasks/' . $task->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
