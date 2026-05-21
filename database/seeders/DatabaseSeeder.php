<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Create Regular Users
        $jane = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $john = User::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // 3. Create Tasks matching mockups
        \App\Models\Task::create([
            'title' => 'Launch New Marketing Campaign',
            'description' => 'Plan and launch multi-channel marketing campaigns for the new product including email newsletters and social promotions.',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => '2026-12-31',
            'assigned_to' => $jane->id,
            'ai_summary' => 'This task focuses on launching a new multi-channel marketing campaign to drive product engagement and brand awareness.',
            'ai_priority' => 'high',
        ]);

        \App\Models\Task::create([
            'title' => 'Develop API Campaign',
            'description' => 'Establish API-driven tracking campaigns to monitor advertising and social media click-through rates.',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => '2026-12-31',
            'assigned_to' => $jane->id,
            'ai_summary' => 'Establish API-driven tracking campaigns to monitor advertising efficiency and track user click metrics.',
            'ai_priority' => 'high',
        ]);

        \App\Models\Task::create([
            'title' => 'Develop API Endpoints',
            'description' => 'Develop and test core REST API endpoints for user task list CRUD operations and priority updates.',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => '2026-12-31',
            'assigned_to' => $jane->id,
            'ai_summary' => 'Develop and test essential REST API endpoints for user task list operations and key model bindings.',
            'ai_priority' => 'high',
        ]);

        \App\Models\Task::create([
            'title' => 'Refactor API Endpoints',
            'description' => 'Optimize query execution plans and refactor legacy endpoint handlers for improved system latency.',
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => '2026-12-31',
            'assigned_to' => $jane->id,
            'ai_summary' => 'Optimize query execution plans and refactor legacy endpoint handlers to reduce database server load.',
            'ai_priority' => 'low',
        ]);

        \App\Models\Task::create([
            'title' => 'Launch New Product',
            'description' => 'Prepare the final product build, coordinate deployment pipelines, and publish release documentation.',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => '2026-06-30',
            'assigned_to' => $john->id,
            'ai_summary' => 'Prepare the final product build and deploy the production environment for active customer access.',
            'ai_priority' => 'high',
        ]);

        \App\Models\Task::create([
            'title' => 'Setup DB Migrations',
            'description' => 'Configure core tables, foreign keys, and indexes for tasks and user roles and run migrations.',
            'priority' => 'medium',
            'status' => 'completed',
            'due_date' => '2026-05-15',
            'assigned_to' => $john->id,
            'ai_summary' => 'Configure core tables, foreign key constraints and indexes, and run migrations on production MySQL DB.',
            'ai_priority' => 'medium',
        ]);
    }
}
