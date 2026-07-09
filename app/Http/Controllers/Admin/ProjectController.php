<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Project;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Models\Gc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProjectController extends Controller
{
    public function index(Request $request)
    {
       
        $query = Project::with(['assignedTo', 'statusRecord', 'typeRecord']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('gc', 'like', '%' . $request->search . '%')
                  ->orWhereJsonContains('other_gc', $request->search)
                  ->orWhere('rfi', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $statuses = is_array($request->status) ? $request->status : [$request->status];
            $query->whereIn('status', $statuses);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'NON MU') {
                $query->where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'));
            } else {
                $query->where('type', $request->type);
            }
        }

        // Filter by GC
        if ($request->filled('gc')) {
            $query->where('gc', $request->gc);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
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
            'gc' => 'gc',
            'status' => 'status',
            'type' => 'type',
            'assigned_to' => 'assigned_to',
            'due_date' => 'due_date',
            'created_at' => 'created_at'
        ];
        
        if (array_key_exists($sortBy, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sortBy], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $projects = $query->paginate(100);
        
        // Get filter options
        $statuses = Status::active()->ordered()->get();
        $types = Type::active()->ordered()->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        $gcs = Gc::active()->ordered()->get();
        return view('admin.projects.index', compact('projects', 'statuses', 'types', 'estimators', 'gcs'));
    }

    public function create()
    {
        $statuses = Status::active()->ordered()->get();
        $types = Type::active()->ordered()->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        $gcs = Gc::active()->ordered()->get();
        return view('admin.projects.create', compact('statuses', 'types', 'estimators', 'gcs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gc' => 'nullable|string|max:255',
            'other_gc_names' => 'nullable|array',
            'other_gc_names.*' => 'string|max:255|distinct',
            'other_gc_data' => 'nullable|array',
            'other_gc_data.*' => 'array',
            'other_gc_data.*.due_date' => 'nullable|date',
            'other_gc_data.*.web_link' => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
            'scope' => 'nullable|string',
            'assigned_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date|required_with:assigned_to',
            'rfi_due_date' => 'nullable|date|after_or_equal:rfi_request_date',
            'rfi_request_date' => 'nullable|date|after_or_equal:assigned_date',
            'first_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'second_rfi_request_date' => 'nullable|date',
            'second_rfi_due_date' => 'nullable|date|after_or_equal:second_rfi_request_date',
            'second_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'third_rfi_request_date' => 'nullable|date',
            'third_rfi_due_date' => 'nullable|date|after_or_equal:third_rfi_request_date',
            'third_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'status' => 'nullable|exists:statuses,name',
            'type' => 'nullable|exists:types,name',
            'rfi' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'days_required' => 'nullable|numeric|min:0.1|required_with:assigned_to',
            'project_information' => 'nullable|string',
            'web_link' => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
        ]);

        // Normalize empty-string select values to null (e.g. "Select an estimator" blank option)
        if (($validated['assigned_to'] ?? null) === '') {
            $validated['assigned_to'] = null;
        }

        // Process Other GC data
        if (!empty($validated['other_gc_names'])) {
            $otherGcData = [];
            foreach ($validated['other_gc_names'] as $gcName) {
                $otherGcData[$gcName] = [
                    'due_date' => $validated['other_gc_data'][$gcName]['due_date'] ?? null,
                    'web_link' => $validated['other_gc_data'][$gcName]['web_link'] ?? null,
                ];
            }
            $validated['other_gc'] = $otherGcData;
        } else {
            $validated['other_gc'] = [];
        }

        // Remove the temporary form fields
        unset($validated['other_gc_names'], $validated['other_gc_data']);

        // Handle file uploads
        $attachmentFields = [
            'first_rfi_attachment',
            'second_rfi_attachment',
            'third_rfi_attachment'
        ];

        foreach ($attachmentFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('project-rfi-attachments', 'public');
                $validated[$field] = $path;
            }
        }

        $daysRequired = $validated['days_required'] ?? null;
        unset($validated['days_required']);

        DB::transaction(function () use ($validated, $daysRequired) {
            $project = Project::create($validated);
            $this->syncAllocationForProject($project, $daysRequired);
        });

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load(['assignedTo', 'statusRecord', 'typeRecord', 'remarks.user']);
        
        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $statuses = Status::active()->ordered()->get();
        $types = Type::active()->ordered()->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        $gcs = Gc::active()->ordered()->get();
        return view('admin.projects.edit', compact('project', 'statuses', 'types', 'estimators', 'gcs'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gc' => 'nullable|string|max:255',
            'other_gc_names' => 'nullable|array',
            'other_gc_names.*' => 'string|max:255|distinct',
            'other_gc_data' => 'nullable|array',
            'other_gc_data.*' => 'array',
            'other_gc_data.*.due_date' => 'nullable|date',
            'other_gc_data.*.web_link' => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
            'scope' => 'nullable|string',
            'assigned_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date|required_with:assigned_to',
            'rfi_due_date' => 'nullable|date|after_or_equal:rfi_request_date',
            'rfi_request_date' => 'nullable|date|after_or_equal:assigned_date',
            'first_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'second_rfi_request_date' => 'nullable|date',
            'second_rfi_due_date' => 'nullable|date|after_or_equal:second_rfi_request_date',
            'second_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'third_rfi_request_date' => 'nullable|date',
            'third_rfi_due_date' => 'nullable|date|after_or_equal:third_rfi_request_date',
            'third_rfi_attachment' => 'nullable|file|mimes:eml,msg,pdf,png,jpg,jpeg|max:10240',
            'status' => 'nullable|exists:statuses,name',
            'type' => 'nullable|exists:types,name',
            'rfi' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'days_required' => 'nullable|numeric|min:0.1|required_with:assigned_to',
            'project_information' => 'nullable|string',
            'web_link' => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
        ]);

        // Normalize empty-string select values to null (e.g. "Select an estimator" blank option)
        if (($validated['assigned_to'] ?? null) === '') {
            $validated['assigned_to'] = null;
        }

        // Process Other GC data
        if (!empty($validated['other_gc_names'])) {
            $otherGcData = [];
            foreach ($validated['other_gc_names'] as $gcName) {
                $otherGcData[$gcName] = [
                    'due_date' => $validated['other_gc_data'][$gcName]['due_date'] ?? null,
                    'web_link' => $validated['other_gc_data'][$gcName]['web_link'] ?? null,
                ];
            }
            $validated['other_gc'] = $otherGcData;
        } else {
            $validated['other_gc'] = [];
        }

        // Remove the temporary form fields
        unset($validated['other_gc_names'], $validated['other_gc_data']);

        // Handle file uploads
        $attachmentFields = [
            'first_rfi_attachment',
            'second_rfi_attachment',
            'third_rfi_attachment'
        ];

        foreach ($attachmentFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($project->$field && Storage::disk('public')->exists($project->$field)) {
                    Storage::disk('public')->delete($project->$field);
                }
                // Store new file
                $file = $request->file($field);
                $path = $file->store('project-rfi-attachments', 'public');
                $validated[$field] = $path;
            }
        }

        // Set submitted_at when status changes to SUBMITTED for the first time
        if (
            isset($validated['status']) &&
            $validated['status'] === 'SUBMITTED' &&
            $project->status !== 'SUBMITTED' &&
            is_null($project->submitted_at)
        ) {
            $validated['submitted_at'] = now();
        }

        $daysRequired = $validated['days_required'] ?? null;
        unset($validated['days_required']);

        DB::transaction(function () use ($project, $validated, $daysRequired) {
            $project->update($validated);
            $this->syncAllocationForProject($project, $daysRequired);
        });

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        // Only allow admin users to delete projects
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can delete projects.');
        }

        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function addRemark(Request $request, Project $project)
    {
        $validated = $request->validate([
            'remark' => 'required|string|max:1000',
        ]);

        $project->remarks()->create([
            'user_id' => auth()->id(),
            'remark' => $validated['remark'],
        ]);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Remark added successfully.');
    }

    /**
     * Keep the Allocation/allocation_user pivot in sync with a manually
     * assigned/unassigned/edited Project, so the estimator's workload page
     * and the auto-distribution load calculation both see manual assignments.
     */
    private function syncAllocationForProject(Project $project, ?float $daysRequired): void
    {
        $estimatorId = $project->assigned_to;

        // No estimator assigned: drop any existing allocation link.
        if (!$estimatorId) {
            $this->detachProjectAllocation($project);
            return;
        }

        $dueDate = $project->due_date;
        $allocationDueDate = $dueDate->copy()->addDays((int) ceil($daysRequired));

        if (!$project->allocation_id) {
            // New assignment: create the Allocation.
            $allocation = Allocation::create([
                'job_number'     => $this->deriveJobNumber($project->name, $project->id),
                'assigned_date'  => $dueDate,
                'due_date'       => $allocationDueDate,
                'days_required'  => $daysRequired,
                'job_type'       => $this->deriveJobType($project->type),
            ]);

            $allocation->estimators()->attach($estimatorId, ['status' => 'open']);
            $project->update(['allocation_id' => $allocation->id]);
            return;
        }

        // Existing assignment: keep the Allocation's estimator/dates in sync.
        $allocation = Allocation::find($project->allocation_id);
        if (!$allocation) {
            return;
        }

        $currentEstimatorId = $allocation->estimators()->value('users.id');
        if ($currentEstimatorId !== $estimatorId) {
            if ($currentEstimatorId) {
                $allocation->estimators()->detach($currentEstimatorId);
            }
            $allocation->estimators()->attach($estimatorId, ['status' => 'open']);
        }

        $allocation->update([
            'assigned_date' => $dueDate,
            'due_date'      => $allocationDueDate,
            'days_required' => $daysRequired,
            'job_type'      => $this->deriveJobType($project->type),
        ]);
    }

    private function detachProjectAllocation(Project $project): void
    {
        if (!$project->allocation_id) {
            return;
        }

        $allocation = Allocation::find($project->allocation_id);
        $project->update(['allocation_id' => null]);

        if ($allocation) {
            $allocation->delete();
        }
    }

    private function deriveJobType(?string $type): string
    {
        return $type === 'MULTIUNIT' ? 'MU' : 'NON_MU';
    }

    private function deriveJobNumber(string $projectName, int $projectId): string
    {
        // Mirrors Allocation::getProjectNameAttribute()'s prefix pattern
        // (e.g. "1111A. Some Name" -> "1111A"). Falls back to the project id
        // when no such prefix exists, since job_number must stay unique and
        // manually-named projects often share the same free-text name.
        if (preg_match('/^([^0-9]*[0-9]+[A-Za-z]*)\.\s*/', $projectName, $matches)) {
            return trim($matches[1]);
        }

        return 'PRJ-' . $projectId;
    }
}