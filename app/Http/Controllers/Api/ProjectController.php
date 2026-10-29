<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        return response()->json(
            $this->projectService->getAll()
        );
    }

    
public function store(ProjectRequest $request)
{
    $data = $request->validated();

    // Automatically assign the logged-in user's ID
    $data['created_by'] = $request->user()->id;

    $project = $this->projectService->create($data);

    return response()->json($project, 201);
}


    public function show(Project $project)
    {
        return response()->json($project);
    }

    public function update(ProjectRequest $request,Project $project)
    {
        return response()->json(
            $this->projectService->update(
                $project,
                $request->validated()
            )
        );
    }

    public function destroy(Project $project)
    {
        $this->projectService->delete($project);

        return response()->json([
            'message'=>'Project deleted'
        ]);
    }
}