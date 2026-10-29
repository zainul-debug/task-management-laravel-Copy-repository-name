<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    // Admins see all tasks; users see their assigned tasks.
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->taskService->getAllForUser(
                $request->user()
            ),
        ]);
    }

    // Admin creates and assigns a task.
    public function store(TaskRequest $request, \App\Models\Project $project)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can create tasks.',
            ], 403);
        }

        $data = $request->validated();
        $data['project_id'] = $project->id;

        // Default status must match the database enum.
        $data['status'] = 'PENDING';

        $task = $this->taskService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'data' => $task->load(['project', 'user']),
        ], 201);
    }

    // Users can update only their own assigned task status.
    public function updateStatus(Request $request, Task $task)
    {
        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            $task->assigned_to != $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update this task.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'in:PENDING,IN_PROGRESS,DONE,OVERDUE',
            ],
        ]);

        $task = $this->taskService->updateStatus(
            $task,
            $validated['status'],
            $user
        );
        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully.',
            'data' => $task,
        ]);
    }
}
