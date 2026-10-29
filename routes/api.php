
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Services\TaskService;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        // Project and task routes
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::get('/tasks', [TaskController::class, 'index']);

        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{task}/status', [TaskController::class, 'updateStatus']);

        // Admin-only routes
        Route::middleware('admin')->group(function () {

            Route::get('/users', [UserController::class, 'index']);

            Route::post('/projects', [ProjectController::class, 'store']);
            Route::put('/projects/{project}', [ProjectController::class, 'update']);
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

            // Ask Django to identify overdue tasks
            Route::post('/tasks/sync-overdue', function (TaskService $taskService) {
                return response()->json(
                    $taskService->syncOverdueTasks()
                );
            });
        });
    });
});
