<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GC;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Project;


class GCController extends Controller
{
    /**
     * Display a listing of GCs
     */
    public function index(Request $request)
    {
        $query = GC::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('company', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by active status
        if ($request->filled('active_filter')) {
            $query->where('is_active', $request->active_filter === 'active');
        }

        $gcs = $query->selectRaw('gcs.*, 
        (SELECT COUNT(*) FROM projects 
         WHERE projects.gc = gcs.name 
         OR projects.other_gc LIKE CONCAT(\'%"\', gcs.name, \'"%\')) as projects_count')
    ->ordered()
    ->paginate(15);

        return view('admin.gcs.index', compact('gcs'));
    }

    /**
     * Show the form for creating a new GC
     */
    public function create()
    {
        return view('admin.gcs.create');
    }

    /**
     * Store a newly created GC
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gcs,name',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:gcs,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active is set properly
        $validated['is_active'] = $request->has('is_active');

        GC::create($validated);

        return redirect()->route('admin.gcs.index')
            ->with('success', 'GC created successfully.');
    }

    /**
     * Display the specified GC
     */
    public function show(GC $gc)
    {
        // Get recent projects for this GC (latest 10)
        $recentProjects = $gc->projects()
                             ->latest()
                             ->limit(10)
                             ->get();
    
        // Get project statistics
        $totalProjects = $gc->projects()->count();
        
        $activeProjects = $gc->projects()
                             ->whereNotIn('status', ['completed', 'cancelled'])
                             ->count();
        
        $completedProjects = $gc->projects()
                                ->where('status', 'completed')
                                ->count();
    
        // Add recent projects to the GC object for the view
        $gc->recentProjects = $recentProjects;
    
        return view('admin.gcs.show', compact(
            'gc',
            'totalProjects',
            'activeProjects',
            'completedProjects'
        ));
    }

    /**
     * Show the form for editing the specified GC
     */
    public function edit(GC $gc)
    {
        return view('admin.gcs.edit', compact('gc'));
    }

    /**
     * Update the specified GC
     */
    public function update(Request $request, GC $gc)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('gcs')->ignore($gc->id)],
            'company' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('gcs')->ignore($gc->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        // Ensure is_active is set properly
        $validated['is_active'] = $request->has('is_active');

        $gc->update($validated);

        return redirect()->route('admin.gcs.index')
            ->with('success', 'GC updated successfully.');
    }

    /**
     * Remove the specified GC
     */
    public function destroy(GC $gc)
    {
        // Check if GC has any projects
        if ($gc->hasProjects()) {
            return redirect()->route('admin.gcs.index')
                ->with('error', "Cannot delete GC '{$gc->name}' because it is being used by {$gc->projects()->count()} project(s).");
        }

        $gc->delete();

        return redirect()->route('admin.gcs.index')
            ->with('success', 'GC deleted successfully.');
    }

    /**
     * Get GCs for API/AJAX requests
     */
    public function api(Request $request)
    {
        $query = GC::active()->ordered();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $gcs = $query->select('id', 'name', 'company', 'email', 'phone')
                    ->get()
                    ->map(function ($gc) {
                        return [
                            'id' => $gc->id,
                            'name' => $gc->name,
                            'display_name' => $gc->display_name,
                            'company' => $gc->company,
                            'email' => $gc->email,
                            'phone' => $gc->formatted_phone,
                        ];
                    });

        return response()->json($gcs);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(GC $gc)
    {
        $gc->update(['is_active' => !$gc->is_active]);

        $status = $gc->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "GC '{$gc->name}' has been {$status}.");
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'gcs' => 'required|array',
            'gcs.*' => 'exists:gcs,id'
        ]);

        $gcs = GC::whereIn('id', $request->gcs)->get();
        $count = $gcs->count();

        switch ($request->action) {
            case 'activate':
                GC::whereIn('id', $request->gcs)->update(['is_active' => true]);
                $message = "{$count} GC(s) activated successfully.";
                break;
                
            case 'deactivate':
                GC::whereIn('id', $request->gcs)->update(['is_active' => false]);
                $message = "{$count} GC(s) deactivated successfully.";
                break;
                
            case 'delete':
                // Check if any GCs have projects
                $gcsWithProjects = $gcs->filter(fn($gc) => $gc->hasProjects());
                
                if ($gcsWithProjects->count() > 0) {
                    $names = $gcsWithProjects->pluck('name')->implode(', ');
                    return redirect()->back()
                        ->with('error', "Cannot delete the following GCs because they have projects: {$names}");
                }
                
                GC::whereIn('id', $request->gcs)->delete();
                $message = "{$count} GC(s) deleted successfully.";
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}