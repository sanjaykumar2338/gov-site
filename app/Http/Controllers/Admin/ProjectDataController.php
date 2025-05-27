<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectDetail;
use Illuminate\Http\Request;

class ProjectDataController extends Controller
{
    public function index(Request $request)
    {
        $states = ['johor', 'pulau pinang', 'selangor', 'wp kuala lumpur'];

        $query = ProjectDetail::whereIn(\DB::raw('LOWER(state)'), $states);

        // Filter by a specific state from the 4
        if ($request->filled('state')) {
            $query->whereRaw('LOWER(state) = ?', [strtolower($request->state)]);
        }

        // Search by project_name or developer_name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                ->orWhere('developer_name', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(6);

        return view('admin.project-data.index', compact('projects', 'states'));
    }

    public function count()
    {
        $count = \App\Models\ProjectDetail::count();

        return response()->json([
            'total_projects' => $count
        ]);
    }

    public function show(ProjectDetail $project)
    {
        $project->load(['unitSummaries', 'unitBoxes']);
        return view('admin.project-data.show', compact('project'));
    }
    
}
