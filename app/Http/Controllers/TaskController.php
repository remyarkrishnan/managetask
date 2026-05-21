<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskController extends Controller
{
    protected $taskService;
    protected $userRepo;

    public function __construct(TaskService $taskService, UserRepositoryInterface $userRepo)
    {
        $this->taskService = $taskService;
        $this->userRepo = $userRepo;
    }

    /**
     * Display the task management dashboard.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'priority', 'assigned_to']);
        
        // Non-admin users can only view their assigned tasks
        if ($request->user()->role !== 'admin') {
            $filters['assigned_to'] = $request->user()->id;
        }

        $tasks = $this->taskService->all($filters);
        
        $userIdForStats = $request->user()->role === 'admin' ? null : $request->user()->id;
        $stats = $this->taskService->getDashboardStats($userIdForStats);

        // Fetch users list for assigning tasks (only visible or useful for admin)
        $users = $request->user()->role === 'admin' 
            ? UserResource::collection($this->userRepo->all()) 
            : [];

        // If the request expects JSON (API), return raw resources
        if ($request->expectsJson() || $request->wantsJson()) {
            return TaskResource::collection($tasks);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
            'filters' => $filters,
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(TaskStoreRequest $request)
    {
        Gate::authorize('create', Task::class);

        $task = $this->taskService->store($request->validated());

        if ($request->expectsJson() || $request->wantsJson()) {
            return new TaskResource($task);
        }

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    /**
     * Update the specified task.
     */
    public function update(TaskUpdateRequest $request, int $id)
    {
        $task = $this->taskService->find($id);
        Gate::authorize('update', $task);

        $updatedTask = $this->taskService->update($id, $request->validated());

        if ($request->expectsJson() || $request->wantsJson()) {
            return new TaskResource($updatedTask);
        }

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    /**
     * Update status of the task.
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,completed'],
        ]);

        $task = $this->taskService->find($id);
        Gate::authorize('update', $task);

        $updatedTask = $this->taskService->update($id, ['status' => $request->status]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return new TaskResource($updatedTask);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    /**
     * Get / Refresh AI summary for a task.
     */
    public function aiSummary(int $id, Request $request)
    {
        $task = $this->taskService->find($id);
        Gate::authorize('update', $task);

        $updatedTask = $this->taskService->refreshAiSummary($id);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ai_summary' => $updatedTask->ai_summary,
                'ai_priority' => $updatedTask->ai_priority,
            ]);
        }

        return redirect()->back()->with('success', 'AI Summary refreshed.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(int $id, Request $request)
    {
        $task = $this->taskService->find($id);
        Gate::authorize('delete', $task);

        $this->taskService->delete($id);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully.']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
