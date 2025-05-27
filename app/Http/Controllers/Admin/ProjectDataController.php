<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectDetail;

class ProjectDataController extends Controller
{
    public function index()
    {
        $states = ['johor', 'pulau pinang', 'selangor', 'wp kuala lumpur'];

        $projects = ProjectDetail::whereRaw('LOWER(state) IN ("johor", "pulau pinang", "selangor", "wp kuala lumpur")')
            ->latest()
            ->paginate(8);

        return view('admin.project-data.index', compact('projects'));
    }

    public function show(ProjectDetail $project)
    {
        $project->load(['unitSummaries', 'unitBoxes']);
        return view('admin.project-data.show', compact('project'));
    }
    
}
