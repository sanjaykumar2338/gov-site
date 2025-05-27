<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ProjectDataController extends Controller
{
    public function index(Request $request)
    {
        $states = ['johor', 'pulau pinang', 'selangor', 'wp kuala lumpur'];

        // ✅ Get all columns from the actual `project_details` table
        $allColumns = Schema::getColumnListing('project_details');

        // ✅ Get saved visible columns in order
        $columnOrder = DB::table('column_orders')
            ->where('table_name', 'project_details')
            ->where('is_visible', 1)
            ->orderBy('order_index')
            ->pluck('column_key')
            ->toArray();

        // ✅ If no saved config exists, default to all columns
        if (empty($columnOrder)) {
            $columnOrder = $allColumns;
        }

        $query = ProjectDetail::whereIn(DB::raw('LOWER(state)'), $states);

        if ($request->filled('state')) {
            $query->whereRaw('LOWER(state) = ?', [strtolower($request->state)]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                ->orWhere('developer_name', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(10);

        return view('admin.project-data.index', compact('projects', 'states', 'columnOrder', 'allColumns'));
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

    public function saveColumnOrder(Request $request)
{
    $request->validate([
        'table_name' => 'required|string',
        'columns' => 'required|array',
    ]);

    $tableName = $request->table_name;
    $columns = $request->columns;

    DB::table('column_orders')->where('table_name', $tableName)->delete();

    foreach ($columns as $col) {
        DB::table('column_orders')->insert([
            'table_name' => $tableName,
            'column_key' => $col['column_key'],
            'order_index' => $col['order_index'],
            'is_visible' => $col['is_visible'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return response()->json(['status' => 'success', 'message' => 'Column order saved successfully.']);
}


}
