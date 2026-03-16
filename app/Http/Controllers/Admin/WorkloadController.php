<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkloadController extends Controller
{
    public function index(Request $request)
    {
        // Get all statuses for the filter
        $allStatuses = Status::ordered()->get();

        // Get excluded statuses from request
        $excludedStatuses = $request->get('excluded_statuses', []);

        // Get all estimators
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])
            ->orderBy('name')
            ->get();

        // Calculate workload for each estimator
        $workloadData = [];

        foreach ($estimators as $estimator) {
            // Get projects with future due dates (today and later)
            $query = Project::where('assigned_to', $estimator->id)
                ->where(function($query) {
                    $query->whereDate('due_date', '>=', now()->startOfDay())
                        ->orWhereNull('due_date');
                });

            // Apply status exclusion filter
            if (!empty($excludedStatuses)) {
                $query->whereNotIn('status', $excludedStatuses);
            }

            $projects = $query->with(['statusRecord', 'typeRecord'])
                ->orderBy('due_date', 'asc')
                ->get();

            // Count total jobs
            $totalJobs = $projects->count();

            // Group by status and count
            $statusCounts = $projects->groupBy('status')->map->count()->toArray();

            // Group by type and count (handle empty as "Regular")
            $typeCounts = $projects->map(function($project) {
                return $project->type ?: 'Regular';
            })->groupBy(function($type) {
                return $type;
            })->map->count()->toArray();

            $openDistributionDays = DB::table('allocation_user')
                ->join('allocations', 'allocation_user.allocation_id', '=', 'allocations.id')
                ->where('allocation_user.user_id', $estimator->id)
                ->where('allocation_user.status', 'open')
                ->sum('allocations.days_required');

            $workloadData[] = [
                'estimator'            => $estimator,
                'total_jobs'           => $totalJobs,
                'status_counts'        => $statusCounts,
                'type_counts'          => $typeCounts,
                'projects'             => $projects,
                'open_distribution_days' => $openDistributionDays,
            ];
        }

        return view('admin.workload.index', compact('workloadData', 'allStatuses', 'excludedStatuses'));
    }
}
