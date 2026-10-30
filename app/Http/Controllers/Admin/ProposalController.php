<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    /**
     * Display a listing of proposals
     */
    public function index(Request $request)
    {
        $query = Proposal::with(['project.assignedTo', 'latestNote.user']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('project', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('gc', 'like', '%' . $request->search . '%');
                })->orWhere('job_number', 'like', '%' . $request->search . '%');
            });
        }

        // Per-column filters (header filter row on the list). Columns are qualified
        // with the table name because sorting by GC joins `projects`, which also has `status`.
        if ($request->status === 'none') {
            $query->whereNull('proposals.status');
        } elseif ($request->filled('status')) {
            $query->where('proposals.status', $request->status);
        }

        if ($request->filled('submission_date')) {
            $query->whereDate('proposals.submission_date', $request->submission_date);
        }

        if ($request->filled('project')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('project', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->project . '%');
                })->orWhere('job_number', 'like', '%' . $request->project . '%');
            });
        }

        if ($request->filled('gc')) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('gc', $request->gc);
            });
        }

        if ($request->filled('other_gc')) {
            // other_gc is JSON (a list of names or an object keyed by name); matching the
            // quoted name covers both formats, same as Project's GC lookup.
            $query->whereHas('project', function($q) use ($request) {
                $q->where('other_gc', 'like', '%"' . $request->other_gc . '"%');
            });
        }

        if ($request->filled('gc_received')) {
            $request->gc_received === 'yes'
                ? $query->whereNotNull('proposals.gc_received_date')
                : $query->whereNull('proposals.gc_received_date');
        }

        foreach (['first', 'second', 'third', 'fourth'] as $nth) {
            $value = $request->input("{$nth}_follow_up");
            if ($value === 'none') {
                $query->whereNull("proposals.{$nth}_follow_up_date");
            } elseif (in_array($value, ['yes', 'no'], true)) {
                $query->whereNotNull("proposals.{$nth}_follow_up_date")
                      ->where("proposals.{$nth}_follow_up_respond", $value);
            }
        }

        if ($request->filled('notes')) {
            $query->whereHas('notes', function($q) use ($request) {
                $q->where('body', 'like', '%' . $request->notes . '%');
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

        // Filter by job type (MU / NON MU)
        if ($request->filled('job_type')) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('type', $request->job_type);
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
            'status',
            'responded',
            'gc_received_date',
            'first_follow_up_date',
            'second_follow_up_date',
            'third_follow_up_date',
            'fourth_follow_up_date',
            'price_original', 
            'price_ve', 
            'gc_price', 
            'result_gc',
            'result_art',
            'gc'
        ];
        
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'gc') {
                // Sort by project GC field - need to join with projects table
                $query->join('projects', 'proposals.project_id', '=', 'projects.id')
                      ->orderBy('projects.gc', $sortDirection)
                      ->select('proposals.*'); // Make sure we only select proposal fields
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // After sorting: the GC sort's select('proposals.*') would otherwise drop notes_count.
        $proposals = $query->withCount('notes')->paginate(100);
        
        // Get filter options
        $projects = Project::orderBy('name')->get();
        $estimators = \App\Models\User::whereIn('role', ['estimator', 'head_estimator'])
                                     ->orderBy('name')
                                     ->get();

        // GC / Other GC filter options: only GCs that appear on projects with a proposal.
        $proposalProjects = Project::whereIn('id', Proposal::select('project_id'))->get(['id', 'gc', 'other_gc']);
        $gcOptions = $proposalProjects->pluck('gc')->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        $otherGcOptions = $proposalProjects->flatMap(fn($project) => array_keys($project->other_gc))
                                           ->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('admin.proposals.index', compact('proposals', 'projects', 'estimators', 'gcOptions', 'otherGcOptions'));
    }

    /**
     * Show the form for creating a new proposal
     */
    public function create(Request $request)
    {
        $projects = Project::all();

        // The list page's popup fetches just the form. It's a fresh request, so it never
        // carries old input/errors from an earlier submit and always opens blank.
        if ($request->ajax()) {
            return view('admin.proposals.partials.create-modal-form', compact('projects'));
        }

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
            'status' => 'required|in:' . implode(',', array_keys(Proposal::STATUSES)),
            'submission_date' => 'nullable|date',
            'responded' => 'nullable|in:yes,no',
            'gc_received_date' => 'nullable|date',
            'first_follow_up_date' => 'nullable|date',
            'first_follow_up_respond' => 'nullable|in:yes,no',
            'first_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'second_follow_up_date' => 'nullable|date',
            'second_follow_up_respond' => 'nullable|in:yes,no',
            'second_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'third_follow_up_date' => 'nullable|date',
            'third_follow_up_respond' => 'nullable|in:yes,no',
            'third_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'fourth_follow_up_date' => 'nullable|date',
            'fourth_follow_up_respond' => 'nullable|in:yes,no',
            'fourth_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
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

        // Handle file uploads
        $attachmentPaths = [];
        $attachmentFields = [
            'first_follow_up_attachment',
            'second_follow_up_attachment',
            'third_follow_up_attachment',
            'fourth_follow_up_attachment'
        ];
        
        foreach ($attachmentFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('proposal-attachments', 'public');
                $validated[$field] = $path;
            }
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
        $proposal->load(['project', 'notes.user']);
        return view('admin.proposals.show', compact('proposal'));
    }

    /**
     * Show the form for editing the proposal
     */
    public function edit(Request $request, Proposal $proposal)
    {
        $projects = Project::all();

        // The list page's popup fetches just the form.
        if ($request->ajax()) {
            return view('admin.proposals.partials.edit-modal-form', compact('proposal', 'projects'));
        }

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
            'status' => 'nullable|in:' . implode(',', array_keys(Proposal::STATUSES)),
            'submission_date' => 'nullable|date',
            'responded' => 'nullable|in:yes,no',
            'gc_received_date' => 'nullable|date',
            'first_follow_up_date' => 'nullable|date',
            'first_follow_up_respond' => 'nullable|in:yes,no',
            'first_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'second_follow_up_date' => 'nullable|date',
            'second_follow_up_respond' => 'nullable|in:yes,no',
            'second_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'third_follow_up_date' => 'nullable|date',
            'third_follow_up_respond' => 'nullable|in:yes,no',
            'third_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'fourth_follow_up_date' => 'nullable|date',
            'fourth_follow_up_respond' => 'nullable|in:yes,no',
            'fourth_follow_up_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
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

        // Handle file uploads
        $attachmentFields = [
            'first_follow_up_attachment',
            'second_follow_up_attachment',
            'third_follow_up_attachment',
            'fourth_follow_up_attachment'
        ];
        
        foreach ($attachmentFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($proposal->$field && \Storage::disk('public')->exists($proposal->$field)) {
                    \Storage::disk('public')->delete($proposal->$field);
                }
                // Store new file
                $file = $request->file($field);
                $path = $file->store('proposal-attachments', 'public');
                $validated[$field] = $path;
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