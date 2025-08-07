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
            $query->whereHas('project', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('gc', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by result
        if ($request->filled('result')) {
            if ($request->result === 'pending') {
                $query->whereNull('result');
            } else {
                $query->where('result', $request->result);
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
        
        $allowedSorts = ['created_at', 'submission_date', 'price_original', 'price_ve', 'gc_price', 'result'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $proposals = $query->paginate(10);
        
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
            'submission_date' => 'nullable|date',
            'price_original' => 'nullable|numeric|min:0',
            'price_ve' => 'nullable|numeric|min:0',
            'result' => 'nullable|in:pending,win,loss',
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
            'submission_date' => 'nullable|date',
            'price_original' => 'nullable|numeric|min:0',
            'price_ve' => 'nullable|numeric|min:0',
            'result' => 'nullable|in:pending,win,loss',
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