<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Models\ProjectRemark;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Project Statistics
        $totalProjects = Project::count();
        $projectsThisMonth = Project::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();
        $overdueProjects = Project::overdue()->count();
        $dueSoonProjects = Project::dueSoon()->count();

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
                                        ->limit(15)
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
            'overdueByEstimator'
        ));
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