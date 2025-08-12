<?php

namespace App\Http\Controllers\Estimator;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use App\Models\Project;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Display a listing of progress entries for the authenticated estimator
     */
    public function index(Request $request)
    {
        $query = Progress::with(['project'])
                        ->whereHas('project', function($q) {
                            $q->where('assigned_to', auth()->id());
                        });

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

        $progress = $query->paginate(15);
        
        // Get projects assigned to this estimator
        $projects = Project::where('assigned_to', auth()->id())
                          ->orderBy('name')
                          ->get();

        return view('estimator.progress.index', compact('progress', 'projects'));
    }

    /**
     * Show the form for creating a new progress entry
     */
    public function create()
    {
        // Only show projects assigned to this estimator
        $projects = Project::where('assigned_to', auth()->id())
                          ->orderBy('name')
                          ->get();
                          
        return view('estimator.progress.create', compact('projects'));
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

        // Verify the project is assigned to this estimator
        $project = Project::where('id', $validated['project_id'])
                         ->where('assigned_to', auth()->id())
                         ->first();
        
        if (!$project) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You can only create progress entries for projects assigned to you.');
        }

        Progress::create($validated);

        return redirect()->route('estimator.progress.index')
            ->with('success', 'Progress entry created successfully.');
    }

    /**
     * Display the specified progress entry
     */
    public function show(Progress $progress)
    {
        // Verify the progress belongs to a project assigned to this estimator
        if ($progress->project->assigned_to !== auth()->id()) {
            abort(403, 'You can only view progress entries for projects assigned to you.');
        }

        $progress->load(['project']);
        return view('estimator.progress.show', compact('progress'));
    }

    /**
     * Show the form for editing the progress entry
     */
    public function edit(Progress $progress)
    {
        // Verify the progress belongs to a project assigned to this estimator
        if ($progress->project->assigned_to !== auth()->id()) {
            abort(403, 'You can only edit progress entries for projects assigned to you.');
        }

        $projects = Project::where('assigned_to', auth()->id())
                          ->orderBy('name')
                          ->get();
                          
        return view('estimator.progress.edit', compact('progress', 'projects'));
    }

    /**
     * Update the specified progress entry
     */
    public function update(Request $request, Progress $progress)
    {
        // Verify the progress belongs to a project assigned to this estimator
        if ($progress->project->assigned_to !== auth()->id()) {
            abort(403, 'You can only edit progress entries for projects assigned to you.');
        }

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

        // Verify the new project is also assigned to this estimator
        $project = Project::where('id', $validated['project_id'])
                         ->where('assigned_to', auth()->id())
                         ->first();
        
        if (!$project) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You can only assign progress entries to projects assigned to you.');
        }

        $progress->update($validated);

        return redirect()->route('estimator.progress.index')
            ->with('success', 'Progress entry updated successfully.');
    }

    /**
     * Remove the specified progress entry
     */
    public function destroy(Progress $progress)
    {
        // Verify the progress belongs to a project assigned to this estimator
        if ($progress->project->assigned_to !== auth()->id()) {
            abort(403, 'You can only delete progress entries for projects assigned to you.');
        }

        $progress->delete();

        return redirect()->route('estimator.progress.index')
            ->with('success', 'Progress entry deleted successfully.');
    }
}