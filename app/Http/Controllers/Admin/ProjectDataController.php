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

        // All column names from table
        $allColumns = Schema::getColumnListing('project_details');

        // Visible columns
        $columnOrder = DB::table('column_orders')
            ->where('table_name', 'project_details')
            ->where('is_visible', 1)
            ->orderBy('order_index')
            ->pluck('column_key')
            ->toArray();

        if (empty($columnOrder)) {
            $columnOrder = $allColumns;
        }

        // ✅ Main query: Select only latest unique project per project_code
        $sub = ProjectDetail::select(DB::raw('MAX(id) as id'))
            ->whereIn(DB::raw('LOWER(state)'), $states)
            ->groupBy('project_code');

        $query = ProjectDetail::with(['unitSummaries', 'unitBoxes'])
            ->whereIn('id', $sub)
            ->latest();

        // Optional: Filter
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

        $projects = $query->paginate(10);

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
