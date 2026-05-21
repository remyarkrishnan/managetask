<?php

namespace App\Repositories\Cache;

use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as Cache;

class CachingTaskRepository implements TaskRepositoryInterface
{
    protected $repository;
    protected $cache;
    protected $cacheTag = 'tasks';
    protected $ttl = 3600; // Cache for 1 hour

    public function __construct(TaskRepositoryInterface $repository, Cache $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function all(array $filters = [])
    {
        // Create a unique key based on the serialized filters
        $key = 'tasks.all.' . md5(serialize($filters));

        return $this->cache->tags([$this->cacheTag])->remember($key, $this->ttl, function () use ($filters) {
            return $this->repository->all($filters);
        });
    }

    public function find(int $id)
    {
        $key = 'tasks.find.' . $id;

        return $this->cache->tags([$this->cacheTag])->remember($key, $this->ttl, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    public function create(array $data)
    {
        $task = $this->repository->create($data);
        $this->clearCache();
        return $task;
    }

    public function update(int $id, array $data)
    {
        $task = $this->repository->update($id, $data);
        $this->clearCache();
        return $task;
    }

    public function delete(int $id)
    {
        $result = $this->repository->delete($id);
        $this->clearCache();
        return $result;
    }

    public function getDashboardStats(?int $userId = null): array
    {
        $key = 'tasks.stats.' . ($userId ?? 'all');

        return $this->cache->tags([$this->cacheTag])->remember($key, $this->ttl, function () use ($userId) {
            return $this->repository->getDashboardStats($userId);
        });
    }

    protected function clearCache()
    {
        $this->cache->tags([$this->cacheTag])->flush();
    }
}
