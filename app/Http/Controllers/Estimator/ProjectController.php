<?php

namespace App\Http\Controllers\Estimator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRemark;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects assigned to the estimator
     */
    public function index(Request $request)
    {
        $query = Project::with(['statusRecord', 'typeRecord', 'assignedTo'])
                       ->where('assigned_to', Auth::id());

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by due date with new default logic
        $dueFilter = $request->get('due_filter', 'current'); // Default to current projects
        
        switch ($dueFilter) {
            case 'current':
                // Current projects: due date today or in the future, or no due date
                // Exclude SUBMITTED and DECLINED projects
                $query->where(function($q) {
                    $q->whereDate('due_date', '>=', now())
                      ->orWhereNull('due_date');
                })->whereNotIn('status', ['SUBMITTED', 'DECLINED','GC NONRESPONSIVE!!']);
                break;
            case 'all':
                // All projects - no filter applied
                break;
            case 'past':
                // Past projects: due date in the past
                $query->whereDate('due_date', '<', now());
                break;
            case 'overdue':
                // Keep the existing overdue logic for backward compatibility
                $query->overdue();
                break;
            case 'due_soon':
                // Keep the existing due soon logic for backward compatibility
                $query->dueSoon();
                break;
        }

        // Handle sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Define sortable columns
        $sortableColumns = [
            'name' => 'name',
            'status' => 'status',
            'type' => 'type',
            'due_date' => 'due_date',
            'created_at' => 'created_at'
        ];
        
        if (array_key_exists($sortBy, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sortBy], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $projects = $query->paginate(10);

        // Get filter options
        $statuses = Status::all();
        $types = Project::select('type')->distinct()->whereNotNull('type')->get();

        // Get project counts for dashboard
        $totalProjects = Project::where('assigned_to', Auth::id())->count();
        $overdueProjects = Project::where('assigned_to', Auth::id())->overdue()->count();
        $dueSoonProjects = Project::where('assigned_to', Auth::id())->dueSoon()->count();
        $completedProjects = Project::where('assigned_to', Auth::id())
                                   ->where('status', 'completed')
                                   ->count();

        return view('estimator.projects.index', compact(
            'projects',
            'statuses',
            'types',
            'totalProjects',
            'overdueProjects',
            'dueSoonProjects',
            'completedProjects'
        ));
    }

    /**
     * Display the specified project
     */
    public function show(Project $project)
    {
        // Ensure estimator can only view their assigned projects
        if ($project->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to view this project.');
        }

        $project->load(['statusRecord', 'typeRecord', 'assignedTo', 'remarks.user']);
        $statuses = Status::all();

        return view('estimator.projects.show', compact('project', 'statuses'));
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, Project $project)
    {
        // Ensure estimator can only update their assigned projects
        if ($project->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to update this project.');
        }

        $request->validate([
            'status' => 'required|exists:statuses,name'
        ]);

        $oldStatus = $project->status;
        $project->update([
            'status' => $request->status
        ]);

        // Add a system remark about status change
        ProjectRemark::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'remark' => "Status changed from '{$oldStatus}' to '{$request->status}'"
        ]);

        return redirect()->back()->with('success', 'Project status updated successfully.');
    }

    /**
     * Store a new remark
     */
    public function storeRemark(Request $request, Project $project)
    {
        // Ensure estimator can only add remarks to their assigned projects
        if ($project->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to add remarks to this project.');
        }

        $request->validate([
            'remark' => 'required|string|max:1000'
        ]);

        ProjectRemark::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'remark' => $request->remark
        ]);

        return redirect()->back()->with('success', 'Remark added successfully.');
    }

    /**
     * Delete a remark (only if created by the current user)
     */
    public function deleteRemark(ProjectRemark $remark)
    {
        // Ensure estimator can only delete their own remarks
        if ($remark->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to delete this remark.');
        }

        // Ensure the project is assigned to the estimator
        if ($remark->project->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to modify remarks on this project.');
        }

        $remark->delete();

        return redirect()->back()->with('success', 'Remark deleted successfully.');
    }

    /**
     * Get dashboard data for estimator
     */
    public function dashboard()
    {
        $userId = Auth::id();
        
        // Project statistics
        $totalProjects = Project::where('assigned_to', $userId)->count();
        $overdueProjects = Project::where('assigned_to', $userId)->overdue()->count();
        $dueSoonProjects = Project::where('assigned_to', $userId)->dueSoon()->count();
        $completedProjects = Project::where('assigned_to', $userId)
                                   ->where('status', 'COMPLETED')
                                   ->count();
        $inProgressProjects = Project::where('assigned_to', $userId)
                                    ->where('status', 'IN PROGRESS')
                                    ->count();

        // Recent projects
        $recentProjects = Project::where('assigned_to', $userId)
                                ->with(['statusRecord', 'typeRecord'])
                                ->orderBy('updated_at', 'desc')
                                ->limit(5)
                                ->get();

        // Projects by status
        $projectsByStatus = Project::where('assigned_to', $userId)
                                  ->selectRaw('status, COUNT(*) as count')
                                  ->whereNotNull('status')
                                  ->groupBy('status')
                                  ->get()
                                  ->pluck('count', 'status');

        // Recent remarks by the estimator
        $recentRemarks = ProjectRemark::with(['project'])
                                     ->where('user_id', $userId)
                                     ->orderBy('created_at', 'desc')
                                     ->limit(10)
                                     ->get();

        return view('estimator.dashboard', compact(
            'totalProjects',
            'overdueProjects',
            'dueSoonProjects',
            'completedProjects',
            'inProgressProjects',
            'recentProjects',
            'projectsByStatus',
            'recentRemarks'
        ));
    }
}