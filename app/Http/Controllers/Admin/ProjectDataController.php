<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectDetail;

class ProjectDataController extends Controller
{
    public function index()
    {
        $projects = ProjectDetail::latest()->paginate(15);
        return view('admin.project-data.index', compact('projects'));
    }

    public function show(ProjectDetail $project)
    {
        $project->load(['unitSummaries', 'unitBoxes']);
        return view('admin.project-data.show', compact('project'));
    }
}
