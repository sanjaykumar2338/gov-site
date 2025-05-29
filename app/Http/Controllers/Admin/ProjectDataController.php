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

        $allColumns = Schema::getColumnListing('project_details');

        $columnOrder = DB::table('column_orders')
            ->where('table_name', 'project_details')
            ->where('is_visible', 1)
            ->orderBy('order_index')
            ->pluck('column_key')
            ->toArray();

        if (empty($columnOrder)) {
            $columnOrder = $allColumns;
        }

        $sub = ProjectDetail::select(DB::raw('MAX(id) as id'))
            ->whereIn(DB::raw('LOWER(state)'), $states)
            ->groupBy('project_code');

        $query = ProjectDetail::with(['unitSummaries', 'unitBoxes'])
            ->whereIn('id', $sub);

        if ($request->filled('state')) {
            $query->whereRaw('LOWER(state) = ?', [strtolower($request->state)]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('developer_name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('project_status')) {
            $query->where('overall_status', $request->project_status);
        }

        if ($request->filled('min_price')) {
            $query->whereHas('unitSummaries', function ($q) use ($request) {
                $q->where('min_price', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('unitSummaries', function ($q) use ($request) {
                $q->where('max_price', '<=', $request->max_price);
            });
        }

        if ($request->filled('vp_date_range')) {
            $range = explode(' - ', $request->vp_date_range);

            if (count($range) === 2) {
                $startDate = \Carbon\Carbon::parse($range[0])->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($range[1])->format('Y-m-d');

                // Apply Malay month translation and date comparison
                $query->whereRaw("
                    STR_TO_DATE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                        new_vp_date,
                        'Mac','03'),
                        'Jan','01'),
                        'Feb','02'),
                        'Apr','04'),
                        'Mei','05'),
                        'Jun','06'),
                        'Jul','07'),
                        'Ogos','08'),
                        'Sep','09'),
                        'Okt','10'),
                        'Nov','11'),
                        'Dis','12'), '%d %m %Y')
                    BETWEEN ? AND ?
                ", [$startDate, $endDate]);
            }
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortBy, $allColumns) && in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $projects = $query->paginate(10)->appends($request->query());

        return view('admin.project-data.index', compact('projects', 'states', 'columnOrder', 'allColumns'));
    }

    public function count()
    {
        $count = \App\Models\ProjectDetail::count();

        return response()->json([
            'total_projects' => $count
        ]);
    }

    public function show(ProjectDetail $project, Request $request)
    {
        $project->load(['unitSummaries', 'unitBoxes']);

        $unitBoxes = $project->unitBoxes;

        // Get distinct values dynamically
        $kuotaBumiOptions = $unitBoxes->pluck('kuota_bumi')->unique()->filter()->values();
        $statusJualanOptions = $unitBoxes->pluck('status_jualan')->unique()->filter()->values();

        // Filtering
        $filteredBoxes = $unitBoxes;

        if ($request->filled('no_unit')) {
            $filteredBoxes = $filteredBoxes->filter(fn($box) =>
                str_contains(strtolower($box->no_unit), strtolower($request->no_unit))
            );
        }

        if ($request->filled('kuota_bumi')) {
            $filteredBoxes = $filteredBoxes->filter(fn($box) =>
                strtolower($box->kuota_bumi) === strtolower($request->kuota_bumi)
            );
        }

        if ($request->filled('status_jualan')) {
            $filteredBoxes = $filteredBoxes->filter(fn($box) =>
                strtolower($box->status_jualan) === strtolower($request->status_jualan)
            );
        }

        // ✅ Sorting (default: no sorting)
        if ($request->filled('sort_box_by')) {
            $sortBy = $request->input('sort_box_by');
            $sortOrder = strtolower($request->input('sort_box_order', 'asc'));

            $filteredBoxes = $filteredBoxes->sortBy(function ($box) use ($sortBy) {
                return strtolower(data_get($box, $sortBy));
            });

            if ($sortOrder === 'desc') {
                $filteredBoxes = $filteredBoxes->reverse();
            }

            $filteredBoxes = $filteredBoxes->values(); // reindex
        }


        $totalUnits = $unitBoxes->count();
        $soldUnits = $unitBoxes->where('status_jualan', 'Telah Dijual')->count();
        $unsoldUnits = $unitBoxes->where('status_jualan', 'Belum Dijual')->count();

        return view('admin.project-data.show', compact(
            'project', 'filteredBoxes', 'totalUnits', 'soldUnits', 'unsoldUnits',
            'kuotaBumiOptions', 'statusJualanOptions'
        ));
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
