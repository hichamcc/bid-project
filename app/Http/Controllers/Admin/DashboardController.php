<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Models\ProjectRemark;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Project Statistics (deduplicated by leading job number)
        $uniqueJobKey = "REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1')";

        $totalProjects = Project::selectRaw("COUNT(DISTINCT {$uniqueJobKey}) as total")->value('total');

        $projectsThisMonth = Project::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->selectRaw("COUNT(DISTINCT {$uniqueJobKey}) as total")
                                  ->value('total');

        $overdueProjects = Project::overdue()
                                  ->selectRaw("COUNT(DISTINCT {$uniqueJobKey}) as total")
                                  ->value('total');

        $dueSoonProjects = Project::dueSoon()
                                  ->selectRaw("COUNT(DISTINCT {$uniqueJobKey}) as total")
                                  ->value('total');

        // Group by leading job number (e.g. "26077A. NAME" → "26077").
        // Names without a leading number fall back to the full name.
        // This collapses all rows for the same job (A/B variants, multiple estimators) to one.
        $uniqueKey = "REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1')";

        $submittedMU = Project::where('type', 'MULTIUNIT')
                              ->where('status', 'SUBMITTED')
                              ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                              ->value('total');

        $submittedMUThisMonth = Project::where('type', 'MULTIUNIT')
                                       ->where('status', 'SUBMITTED')
                                       ->whereRaw('MONTH(COALESCE(submitted_at, due_date)) = ?', [now()->month])
                                       ->whereRaw('YEAR(COALESCE(submitted_at, due_date)) = ?', [now()->year])
                                       ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                       ->value('total');

        $submittedNonMU = Project::where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'))
                                 ->where('status', 'SUBMITTED')
                                 ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                 ->value('total');

        $submittedNonMUThisMonth = Project::where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'))
                                          ->where('status', 'SUBMITTED')
                                          ->whereRaw('MONTH(COALESCE(submitted_at, due_date)) = ?', [now()->month])
                                          ->whereRaw('YEAR(COALESCE(submitted_at, due_date)) = ?', [now()->year])
                                          ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                          ->value('total');

        // Proposal ART Statistics
        $proposalWin     = Proposal::where('result_art', 'win')->count();
        $proposalLoss    = Proposal::where('result_art', 'loss')->count();
        $proposalPending = Proposal::where(fn($q) => $q->whereNull('result_art')->orWhere('result_art', 'pending'))->count();

        // User Statistics
        $totalUsers = User::count();
        $activeEstimators = User::whereIn('role', ['estimator', 'head_estimator'])
                               ->count();
        $adminUsers = User::where('role', 'admin')->count();

        // Recent Projects (last 10)
        $recentProjects = Project::with(['assignedTo', 'statusRecord', 'typeRecord'])
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        // Recent Activities (last 15 remarks)
        $recentActivities = ProjectRemark::with(['user', 'project'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();

        // Projects by Status
        $projectsByStatus = Project::selectRaw('status, COUNT(*) as count')
                                  ->whereNotNull('status')
                                  ->groupBy('status')
                                  ->get()
                                  ->pluck('count', 'status');

        // Projects by Type
        $projectsByType = Project::selectRaw('type, COUNT(*) as count')
                                ->whereNotNull('type')
                                ->groupBy('type')
                                ->get()
                                ->pluck('count', 'type');

        // Monthly project creation trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Project::whereMonth('created_at', $date->month)
                           ->whereYear('created_at', $date->year)
                           ->count();
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // Workload by Estimator
        $workloadByEstimator = User::whereIn('role', ['estimator', 'head_estimator'])
                                  ->withCount(['assignedProjects' => function($query) {
                                      $query->whereNotIn('status', ['completed', 'cancelled']);
                                  }])
                                  ->orderBy('assigned_projects_count', 'desc')
                                  ->get();

        // Overdue projects by estimator
        $overdueByEstimator = Project::selectRaw('assigned_to, COUNT(*) as count')
                                    ->whereDate('due_date', '<', now())
                                    ->whereNotNull('assigned_to')
                                    ->groupBy('assigned_to')
                                    ->with('assignedTo')
                                    ->get();

        return view('admin.dashboard', compact(
            'totalProjects',
            'projectsThisMonth',
            'overdueProjects',
            'dueSoonProjects',
            'totalUsers',
            'activeEstimators',
            'adminUsers',
            'recentProjects',
            'recentActivities',
            'projectsByStatus',
            'projectsByType',
            'monthlyTrend',
            'workloadByEstimator',
            'overdueByEstimator',
            'submittedMU',
            'submittedMUThisMonth',
            'submittedNonMU',
            'submittedNonMUThisMonth',
            'proposalWin',
            'proposalLoss',
            'proposalPending'
        ));
    }

    public function submittedProjects(Request $request)
    {
        $type = $request->get('type', 'MU');

        $uniqueKey = "REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1')";

        $query = Project::where('status', 'SUBMITTED')
            ->selectRaw("{$uniqueKey} as job_key, MIN(name) as project_name, MAX(COALESCE(submitted_at, due_date)) as latest_date")
            ->groupByRaw($uniqueKey)
            ->orderByRaw('latest_date DESC');

        if ($type === 'MU') {
            $query->where('type', 'MULTIUNIT');
        } else {
            $query->where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'));
        }

        $projects = $query->get()->pluck('project_name');

        return response()->json($projects);
    }

    public function checkDuplicates(Request $request)
    {
        $type = $request->get('type', 'MU');

        $uniqueKey = "REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1')";

        $query = Project::where('status', 'SUBMITTED')
            ->selectRaw("
                {$uniqueKey} as job_key,
                {$uniqueKey} as job_number,
                name,
                allocation_id,
                gc
            ");

        if ($type === 'MU') {
            $query->where('type', 'MULTIUNIT');
        } else {
            $query->where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'));
        }

        $rows = $query->get();

        // Group by extracted job number, collect all unique keys per job number
        $byJobNumber = [];
        foreach ($rows as $row) {
            $jobNumber = $row->job_number;
            if (!isset($byJobNumber[$jobNumber])) {
                $byJobNumber[$jobNumber] = [];
            }
            $key = $row->job_key;
            if (!isset($byJobNumber[$jobNumber][$key])) {
                $byJobNumber[$jobNumber][$key] = [];
            }
            $byJobNumber[$jobNumber][$key][] = [
                'name'          => $row->name,
                'allocation_id' => $row->allocation_id,
                'gc'            => $row->gc,
            ];
        }

        // Keep only job numbers that have more than one unique key (duplicates)
        $duplicates = array_filter($byJobNumber, fn($keys) => count($keys) > 1);

        ksort($duplicates);

        return response()->json([
            'total_unique_keys' => $rows->pluck('job_key')->unique()->count(),
            'duplicate_job_numbers' => count($duplicates),
            'duplicates' => $duplicates,
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'monthly');

        switch ($type) {
            case 'status':
                $data = Project::selectRaw('status, COUNT(*) as count')
                              ->whereNotNull('status')
                              ->groupBy('status')
                              ->get();
                break;
            
            case 'type':
                $data = Project::selectRaw('type, COUNT(*) as count')
                              ->whereNotNull('type')
                              ->groupBy('type')
                              ->get();
                break;
            
            case 'monthly':
            default:
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $count = Project::whereMonth('created_at', $date->month)
                                   ->whereYear('created_at', $date->year)
                                   ->count();
                    $data[] = [
                        'month' => $date->format('M Y'),
                        'count' => $count
                    ];
                }
                break;
        }

        return response()->json($data);
    }
}