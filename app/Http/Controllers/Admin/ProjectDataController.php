<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectDataController extends Controller
{
    public function index(Request $request)
    {
        $states = ['johor', 'pulau pinang', 'selangor', 'wp kuala lumpur'];

        $allColumns = Schema::getColumnListing('project_details');

        $virtualColumns = [
            'total_units' => 'Total Units',
            'total_belum_dijual_units' => 'Total Belum Dijual Units',
            'total_telah_dijual_units' => 'Total Telah Dijual Units',
            'new_first_vp_date' => 'New First VP Date',
            'final_ccc_date_virtual' => 'Final CCC Date',
            'final_vp_date_virtual' => 'Final VP Date',
            'actual_percentage_virtual' => 'Actual %',
            'min_price_virtual' => 'Minimum Price',
            'max_price_virtual' => 'Maximum Price',
        ];

        $columnOrderData = DB::table('column_orders')
            ->where('table_name', 'project_details')
            ->orderBy('order_index')
            ->get();

        $columnOrder = [];
        foreach ($columnOrderData as $col) {
            if ($col->is_visible) {
                $columnOrder[] = $col->column_key;
            }
        }

        if (empty($columnOrder)) {
            $columnOrder = $allColumns;
        }

        $sub = ProjectDetail::select(DB::raw('MAX(id) as id'))
            ->whereIn(DB::raw('LOWER(state)'), $states)
            ->groupBy('project_code');

        $query = ProjectDetail::with(['unitSummaries', 'unitBoxes'])
            ->whereIn('id', $sub);

        // Filters
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
        if ($request->filled('agreement_type')) {
            $query->where('agreement_type', $request->agreement_type);
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
                $startDate = Carbon::parse($range[0])->format('Y-m-d');
                $endDate = Carbon::parse($range[1])->format('Y-m-d');
                $query->whereRaw("STR_TO_DATE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
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
                    'Dis','12'), '%d %m %Y') BETWEEN ? AND ?", [$startDate, $endDate]);
            }
        }
        if ($request->filled('final_ccc_date')) {
            $range = explode(' - ', $request->final_ccc_date);
            if (count($range) === 2) {
                $startDate = Carbon::parse($range[0])->format('Y-m-d');
                $endDate = Carbon::parse($range[1])->format('Y-m-d');
                $query->whereHas('unitSummaries', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween(DB::raw("STR_TO_DATE(ccc_date, '%d/%m/%Y')"), [$startDate, $endDate]);
                });
            }
        }
        if ($request->filled('final_vp_date')) {
            $range = explode(' - ', $request->final_vp_date);
            if (count($range) === 2) {
                $startDate = Carbon::parse($range[0])->format('Y-m-d');
                $endDate = Carbon::parse($range[1])->format('Y-m-d');
                $query->whereHas('unitSummaries', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween(DB::raw("STR_TO_DATE(vp_date, '%d/%m/%Y')"), [$startDate, $endDate]);
                });
            }
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $projects = $query->get();

        // Precompute virtual columns
        $projects->each(function ($project) {
            $actuals = $project->unitSummaries->pluck('actual_percentage')->filter()->map(fn($v) => floatval(preg_replace('/[^0-9.]/', '', $v)));
            $minPrices = $project->unitSummaries->pluck('min_price')->filter()->map(fn($v) => floatval(preg_replace('/[^0-9.]/', '', $v)));
            $maxPrices = $project->unitSummaries->pluck('max_price')->filter()->map(fn($v) => floatval(preg_replace('/[^0-9.]/', '', $v)));

            $project->virtual_sort_values = [
                'total_units' => $project->unitBoxes->count(),
                'total_telah_dijual_units' => $project->unitBoxes->where('status_jualan', 'Telah Dijual')->count(),
                'total_belum_dijual_units' => $project->unitBoxes->where('status_jualan', '!=', 'Telah Dijual')->count(),
                'new_first_vp_date' => $project->new_vp_date ?? null,
                'final_ccc_date_virtual' => optional($project->unitSummaries->firstWhere('ccc_date'))?->ccc_date,
                'final_vp_date_virtual' => optional($project->unitSummaries->firstWhere('vp_date'))?->vp_date,
                'actual_percentage_virtual' => $actuals->avg(),
                'min_price_virtual' => $minPrices->min(),
                'max_price_virtual' => $maxPrices->max(),
            ];
        });

        if (array_key_exists($sortBy, $virtualColumns)) {
            $projects = $projects->{strtolower($sortOrder) === 'asc' ? 'sortBy' : 'sortByDesc'}(fn($p) => $p->virtual_sort_values[$sortBy] ?? null);
        } else {
            if (in_array($sortBy, $allColumns)) {
                $projects = $projects->{strtolower($sortOrder) === 'asc' ? 'sortBy' : 'sortByDesc'}($sortBy);
            }
        }

        $perPage = 50;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedProjects = new LengthAwarePaginator(
            $projects->forPage($currentPage, $perPage),
            $projects->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $agreementTypes = DB::table('project_details')
            ->select('agreement_type')
            ->whereNotNull('agreement_type')
            ->where('agreement_type', '!=', '')
            ->whereNotIn('agreement_type', ['-', 'N/A', 'null', 'NULL'])
            ->distinct()
            ->pluck('agreement_type')
            ->toArray();

        $projects = $paginatedProjects;

        return view('admin.project-data.index', compact(
            'projects', 'states', 'columnOrder', 'allColumns', 'virtualColumns', 'columnOrderData', 'agreementTypes'
        ));
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
