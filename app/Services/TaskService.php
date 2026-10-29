<?php
namespace App\Services;

use App\Services\DjangoOverdueService;
use App\Models\Task;
use Illuminate\Validation\ValidationException;

class TaskService
{
  public function syncOverdueTasks(): array
    {
        $tasks = Task::where('status', '!=', 'DONE')
            ->whereNotNull('due_date')
            ->get(['id', 'due_date', 'status'])
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'due_date' => $task->due_date->format('Y-m-d'),
                    'status' => $task->status,
                ];
            })
            ->toArray();

        $djangoService = app(DjangoOverdueService::class);
        $result = $djangoService->checkOverdueTasks($tasks);

        $overdueIds = collect($result['overdue_tasks'] ?? [])
            ->pluck('id')
            ->all();

        if (!empty($overdueIds)) {
            Task::whereIn('id', $overdueIds)
                ->where('status', '!=', 'DONE')
                ->update(['status' => 'OVERDUE']);
        }

        return [
            'success' => true,
            'updated_task_ids' => $overdueIds,
        ];
    }
    public function getAllForUser($user)
    {
        $query = Task::with(['project', 'user']);

        // Admins can see all tasks.
        // Regular users can see only their assigned tasks.
        if ($user->role !== 'admin') {
            $query->where('assigned_to', $user->id);
        }

        return $query->latest()->get();
    }

    public function create(array $data)
    {
        return Task::create($data);
    }

    public function updateStatus(Task $task, string $status, $user)
    {
        // An overdue task cannot return to IN_PROGRESS.
        if (
            $task->status === 'OVERDUE' &&
            $status === 'IN_PROGRESS'
        ) {
            throw ValidationException::withMessages([
                'status' => [
                    'An overdue task cannot be moved back to IN_PROGRESS.',
                ],
            ]);
        }

        // Only admins can close an overdue task.
        if (
            $task->status === 'OVERDUE' &&
            $status === 'DONE' &&
            $user->role !== 'admin'
        ) {
            throw ValidationException::withMessages([
                'status' => [
                    'Only an admin can mark an overdue task as DONE.',
                ],
            ]);
        }

        $task->update([
            'status' => $status,
        ]);

        return $task->load(['project', 'user']);
    }
}
