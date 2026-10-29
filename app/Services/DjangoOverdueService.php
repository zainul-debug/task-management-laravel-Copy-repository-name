<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class DjangoOverdueService
{
    public function checkOverdueTasks(array $tasks): array
    {
        $response = Http::timeout(10)
		->post('http://127.0.0.1:8001/api/check-overdue/', [
                'tasks' => $tasks,
            ]);

        $response->throw();

        return $response->json();
    }
}
