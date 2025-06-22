<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $query = Status::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by active status
        if ($request->filled('active_filter')) {
            $query->where('is_active', $request->active_filter === 'active');
        }

        $statuses = $query->ordered()->paginate(15);

        return view('admin.statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:statuses,name',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-assign sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Status::max('sort_order') + 1;
        }

        Status::create($validated);

        return redirect()->route('admin.statuses.index')
            ->with('success', 'Status created successfully.');
    }

    public function show(Status $status)
    {
        $status->load('projects');
        
        return view('admin.statuses.show', compact('status'));
    }

    public function edit(Status $status)
    {
        return view('admin.statuses.edit', compact('status'));
    }

    public function update(Request $request, Status $status)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('statuses')->ignore($status->id)],
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $status->update($validated);

        return redirect()->route('admin.statuses.index')
            ->with('success', 'Status updated successfully.');
    }

    public function destroy(Status $status)
    {
        // Check if status is being used by projects
        $projectCount = $status->projects()->count();
        
        if ($projectCount > 0) {
            return redirect()->route('admin.statuses.index')
                ->with('error', "Cannot delete status '{$status->name}' because it is being used by {$projectCount} project(s).");
        }

        $status->delete();

        return redirect()->route('admin.statuses.index')
            ->with('success', 'Status deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'statuses' => 'required|array',
            'statuses.*.id' => 'required|exists:statuses,id',
            'statuses.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['statuses'] as $statusData) {
            Status::where('id', $statusData['id'])
                  ->update(['sort_order' => $statusData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}