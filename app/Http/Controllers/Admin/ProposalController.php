<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Project;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * Display a listing of proposals
     */
    public function index(Request $request)
    {
        $query = Proposal::with(['project.assignedTo']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('project', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('gc', 'like', '%' . $request->search . '%');
                })->orWhere('job_number', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by responded status
        if ($request->filled('responded')) {
            $query->where('responded', $request->responded);
        }

        // Filter by result_gc
        if ($request->filled('result_gc')) {
            if ($request->result_gc === 'pending') {
                $query->where(function($q) {
                    $q->whereNull('result_gc')->orWhere('result_gc', 'pending');
                });
            } else {
                $query->where('result_gc', $request->result_gc);
            }
        }

        // Filter by result_art
        if ($request->filled('result_art')) {
            if ($request->result_art === 'pending') {
                $query->where(function($q) {
                    $q->whereNull('result_art')->orWhere('result_art', 'pending');
                });
            } else {
                $query->where('result_art', $request->result_art);
            }
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

        // Filter by submission date range
        if ($request->filled('date_from')) {
            $query->whereDate('submission_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submission_date', '<=', $request->date_to);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price_ve', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_ve', '<=', $request->price_max);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = [
            'created_at', 
            'submission_date', 
            'job_number',
            'responded',
            'first_follow_up_date',
            'second_follow_up_date',
            'third_follow_up_date',
            'price_original', 
            'price_ve', 
            'gc_price', 
            'result_gc',
            'result_art'
        ];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $proposals = $query->paginate(100);
        
        // Get filter options
        $projects = Project::orderBy('name')->get();
        $estimators = \App\Models\User::whereIn('role', ['estimator', 'head_estimator'])
                                     ->orderBy('name')
                                     ->get();

        return view('admin.proposals.index', compact('proposals', 'projects', 'estimators'));
    }

    /**
     * Show the form for creating a new proposal
     */
    public function create()
    {
        $projects = Project::all();
        return view('admin.proposals.create', compact('projects'));
    }

    /**
     * Store a newly created proposal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'job_number' => 'nullable|string|max:255',
            'submission_date' => 'nullable|date',
            'responded' => 'nullable|in:yes,no',
            'first_follow_up_date' => 'nullable|date',
            'first_follow_up_respond' => 'nullable|in:yes,no',
            'second_follow_up_date' => 'nullable|date',
            'second_follow_up_respond' => 'nullable|in:yes,no',
            'third_follow_up_date' => 'nullable|date',
            'third_follow_up_respond' => 'nullable|in:yes,no',
            'price_original' => 'nullable|numeric|min:0',
            'price_ve' => 'nullable|numeric|min:0',
            'result_gc' => 'nullable|in:pending,win,loss',
            'result_art' => 'nullable|in:pending,win,loss',
            'gc_price' => 'nullable|numeric|min:0',
        ]);

        // Check if project already has a proposal
        if (Proposal::where('project_id', $validated['project_id'])->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This project already has a proposal. Only one proposal per project is allowed.');
        }

        Proposal::create($validated);

        return redirect()->route('admin.proposals.index')
            ->with('success', 'Proposal created successfully.');
    }

    /**
     * Display the specified proposal
     */
    public function show(Proposal $proposal)
    {
        $proposal->load(['project']);
        return view('admin.proposals.show', compact('proposal'));
    }

    /**
     * Show the form for editing the proposal
     */
    public function edit(Proposal $proposal)
    {
        $projects = Project::all();
        return view('admin.proposals.edit', compact('proposal', 'projects'));
    }

    /**
     * Update the specified proposal
     */
    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'job_number' => 'nullable|string|max:255',
            'submission_date' => 'nullable|date',
            'responded' => 'nullable|in:yes,no',
            'first_follow_up_date' => 'nullable|date',
            'first_follow_up_respond' => 'nullable|in:yes,no',
            'second_follow_up_date' => 'nullable|date',
            'second_follow_up_respond' => 'nullable|in:yes,no',
            'third_follow_up_date' => 'nullable|date',
            'third_follow_up_respond' => 'nullable|in:yes,no',
            'price_original' => 'nullable|numeric|min:0',
            'price_ve' => 'nullable|numeric|min:0',
            'result_gc' => 'nullable|in:pending,win,loss',
            'result_art' => 'nullable|in:pending,win,loss',
            'gc_price' => 'nullable|numeric|min:0',
        ]);

        // Check if changing to a project that already has a different proposal
        if ($validated['project_id'] != $proposal->project_id) {
            if (Proposal::where('project_id', $validated['project_id'])->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'The selected project already has a proposal. Only one proposal per project is allowed.');
            }
        }

        $proposal->update($validated);

        return redirect()->route('admin.proposals.index')
            ->with('success', 'Proposal updated successfully.');
    }

    /**
     * Remove the specified proposal
     */
    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return redirect()->route('admin.proposals.index')
            ->with('success', 'Proposal deleted successfully.');
    }
}