<?php

namespace App\Services;

use App\Models\Project;

class ProjectService
{
    public function getAll()
    {
        return Project::all();
    }

    public function create(array $data)
    {
        return Project::create($data);
    }

    public function update(Project $project,array $data)
    {
        $project->update($data);

        return $project;
    }

    public function delete(Project $project)
    {
        return $project->delete();
    }
}