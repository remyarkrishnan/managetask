<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(array $filters = [])
    {
        return Task::query()->with('user')->filter($filters)->orderBy('created_at', 'desc')->paginate(10);
    }

    public function find(int $id)
    {
        return Task::query()->with('user')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Task::create($data);
    }

    public function update(int $id, array $data)
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id)
    {
        $task = Task::findOrFail($id);
        return $task->delete();
    }

    public function getDashboardStats(?int $userId = null): array
    {
        $query = Task::query();
        if ($userId !== null) {
            $query->where('assigned_to', $userId);
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $highPriority = (clone $query)->where('priority', 'high')->count();

        // Count completed tasks per month for the Jan-May charts
        $monthlyCompleted = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;
            $monthlyCompleted[$monthName] = (clone $query)
                ->where('status', 'completed')
                ->whereMonth('created_at', $monthNum)
                ->count();
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'high_priority' => $highPriority,
            'monthly_completed' => $monthlyCompleted,
        ];
    }
}
