<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TaskService
{
    protected $repo;
    protected $aiService;

    public function __construct(TaskRepositoryInterface $repo, AIService $aiService)
    {
        $this->repo = $repo;
        $this->aiService = $aiService;
    }

    public function all(array $filters = [])
    {
        return $this->repo->all($filters);
    }

    public function find(int $id)
    {
        return $this->repo->find($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $task = $this->repo->create($data);
            $aiData = $this->aiService->generateSummary($task);
            return $this->repo->update($task->id, $aiData);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $task = $this->repo->update($id, $data);
            
            // If title or description changed, update AI summary
            if (isset($data['title']) || isset($data['description'])) {
                $aiData = $this->aiService->generateSummary($task);
                $task = $this->repo->update($task->id, $aiData);
            }
            return $task;
        });
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function refreshAiSummary(int $id)
    {
        return DB::transaction(function () use ($id) {
            $task = $this->repo->find($id);
            $aiData = $this->aiService->generateSummary($task);
            return $this->repo->update($task->id, $aiData);
        });
    }

    public function getDashboardStats(?int $userId = null): array
    {
        return $this->repo->getDashboardStats($userId);
    }
}
