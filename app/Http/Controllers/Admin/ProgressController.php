<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Display a listing of progress entries
     */
    public function index(Request $request)
    {
        $query = Progress::with(['project.assignedTo']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('project', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('gc', 'like', '%' . $request->search . '%');
                })->orWhere('job_number', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by estimator
        if ($request->filled('estimator_id')) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('assigned_to', $request->estimator_id);
            });
        }

        // Filter by completion status
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('submission_date');
            } elseif ($request->status === 'pending') {
                $query->whereNull('submission_date')->whereNotNull('assigned_date');
            } elseif ($request->status === 'overdue') {
                $query->whereNull('submission_date')
                     ->whereNotNull('assigned_date')
                     ->where('assigned_date', '<=', now()->subDays(30));
            }
        }

        // Filter by assigned date range
        if ($request->filled('assigned_from')) {
            $query->whereDate('assigned_date', '>=', $request->assigned_from);
        }
        if ($request->filled('assigned_to')) {
            $query->whereDate('assigned_date', '<=', $request->assigned_to);
        }

        // Filter by submission date range
        if ($request->filled('submitted_from')) {
            $query->whereDate('submission_date', '>=', $request->submitted_from);
        }
        if ($request->filled('submitted_to')) {
            $query->whereDate('submission_date', '<=', $request->submitted_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = [
            'created_at', 'assigned_date', 'submission_date', 'job_number',
            'total_sqft', 'total_lnft', 'total_sinks', 'total_slabs', 'total_hours'
        ];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $progress = $query->paginate(100);
        
        // Get filter options
        $projects = Project::orderBy('name')->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])
                          ->orderBy('name')
                          ->get();

        return view('admin.progress.index', compact('progress', 'projects', 'estimators'));
    }

    /**
     * Show the form for creating a new progress entry
     */
    public function create()
    {
        $projects = Project::with('assignedTo')->orderBy('name')->get();
        return view('admin.progress.create', compact('projects'));
    }

    /**
     * Store a newly created progress entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'job_number' => 'nullable|string|max:255',
            'assigned_date' => 'nullable|date',
            'submission_date' => 'nullable|date|after_or_equal:assigned_date',
            'total_sqft' => 'nullable|numeric|min:0',
            'total_lnft' => 'nullable|numeric|min:0',
            'total_sinks' => 'nullable|integer|min:0',
            'total_slabs' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|numeric|min:0',
        ]);

        Progress::create($validated);

        return redirect()->route('admin.progress.index')
            ->with('success', 'Progress entry created successfully.');
    }

    /**
     * Display the specified progress entry
     */
    public function show(Progress $progress)
    {
        $progress->load(['project.assignedTo']);
        return view('admin.progress.show', compact('progress'));
    }

    /**
     * Show the form for editing the progress entry
     */
    public function edit(Progress $progress)
    {
        $projects = Project::with('assignedTo')->orderBy('name')->get();
        return view('admin.progress.edit', compact('progress', 'projects'));
    }

    /**
     * Update the specified progress entry
     */
    public function update(Request $request, Progress $progress)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'job_number' => 'nullable|string|max:255',
            'assigned_date' => 'nullable|date',
            'submission_date' => 'nullable|date|after_or_equal:assigned_date',
            'total_sqft' => 'nullable|numeric|min:0',
            'total_lnft' => 'nullable|numeric|min:0',
            'total_sinks' => 'nullable|integer|min:0',
            'total_slabs' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|numeric|min:0',
        ]);

        $progress->update($validated);

        return redirect()->route('admin.progress.index')
            ->with('success', 'Progress entry updated successfully.');
    }

    /**
     * Remove the specified progress entry
     */
    public function destroy(Progress $progress)
    {
        $progress->delete();

        return redirect()->route('admin.progress.index')
            ->with('success', 'Progress entry deleted successfully.');
    }
}