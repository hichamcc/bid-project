<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;


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
            $query->where('type', $request->type);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by due date
        if ($request->filled('due_filter')) {
            switch ($request->due_filter) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'due_soon':
                    $query->dueSoon();
                    break;
                case 'no_due_date':
                    $query->whereNull('due_date');
                    break;
            }
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get filter options
        $statuses = Status::active()->ordered()->get();
        $types = Type::active()->ordered()->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        return view('admin.projects.index', compact('projects', 'statuses', 'types', 'estimators'));
    }

    public function create()
    {
        $statuses = Status::active()->ordered()->get();
        $types = Type::active()->ordered()->get();
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        
        return view('admin.projects.create', compact('statuses', 'types', 'estimators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gc' => 'nullable|string|max:255',
            'scope' => 'nullable|string',
            'assigned_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'nullable|exists:statuses,name',
            'type' => 'nullable|exists:types,name',
            'rfi' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'project_information' => 'nullable|string',
            'web_link' => 'nullable|url|max:255',
        ]);

        Project::create($validated);

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
        
        return view('admin.projects.edit', compact('project', 'statuses', 'types', 'estimators'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gc' => 'nullable|string|max:255',
            'scope' => 'nullable|string',
            'assigned_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:assigned_date',
            'status' => 'nullable|exists:statuses,name',
            'type' => 'nullable|exists:types,name',
            'rfi' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'project_information' => 'nullable|string',
            'web_link' => 'nullable|url|max:255',
        ]);

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
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
}