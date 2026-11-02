<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gc as GC;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\GcEmail;
use App\Models\Project;
use App\Models\Proposal;

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

        $gcs = $query->selectRaw("gcs.*,
        (SELECT COUNT(DISTINCT REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1'))
         FROM projects
         WHERE projects.gc = gcs.name) as projects_count,
        (SELECT COUNT(DISTINCT REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1'))
         FROM projects
         WHERE projects.gc = gcs.name AND projects.type = 'MULTIUNIT') as mu_count,
        (SELECT COUNT(DISTINCT REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1'))
         FROM projects
         WHERE projects.gc = gcs.name AND (projects.type = 'NON MU' OR projects.type IS NULL)) as non_mu_count,
        (SELECT COUNT(*) FROM proposals p JOIN projects pr ON pr.id = p.project_id
         WHERE pr.gc = gcs.name AND (p.result_art IS NULL OR p.result_art = 'pending')) as art_pending,
        (SELECT COUNT(*) FROM proposals p JOIN projects pr ON pr.id = p.project_id
         WHERE pr.gc = gcs.name AND p.result_art = 'win') as art_won,
        (SELECT COUNT(*) FROM proposals p JOIN projects pr ON pr.id = p.project_id
         WHERE pr.gc = gcs.name AND p.result_art = 'loss') as art_loss");

        // Sortable columns (header key => SQL column/alias). Default: name.
        $sortable = ['mu' => 'mu_count', 'non_mu' => 'non_mu_count'];
        $sort = $request->input('sort');
        $direction = strtolower((string) $request->input('direction')) === 'asc' ? 'asc' : 'desc';

        if ($sort && isset($sortable[$sort])) {
            $query->orderBy($sortable[$sort], $direction)->orderBy('name');
        } else {
            $query->ordered();
        }

        $gcs = $query->paginate(15)->withQueryString();

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
    public function show(Request $request, GC $gc)
    {
        // Date range from the top filter bar: proposals by submitted date,
        // emails by sent date, projects by due date.
        $from = $request->date('from');
        $to = $request->date('to');
        $inRange = fn ($date) => (!$from || ($date && $date->gte($from->copy()->startOfDay())))
            && (!$to || ($date && $date->lte($to->copy()->endOfDay())));

        $proposals = Proposal::query()
            ->select('proposals.*')
            ->join('projects', 'projects.id', '=', 'proposals.project_id')
            ->where('projects.gc', $gc->name)
            ->when($from, fn ($q) => $q->whereDate('proposals.submission_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('proposals.submission_date', '<=', $to))
            ->with('project')
            ->withCount('emails')
            ->orderByDesc('proposals.submission_date')
            ->get();

        $stats = [
            'art' => $this->resultStats($proposals, 'result_art'),
            'gc'  => $this->resultStats($proposals, 'result_gc'),
        ];

        $projects = Project::query()
            ->where(fn ($q) => $q->where('gc', $gc->name)->orWhere('other_gc', 'LIKE', '%"' . $gc->name . '"%'))
            ->when($from, fn ($q) => $q->whereDate('due_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('due_date', '<=', $to))
            ->with(['assignedTo', 'statusRecord', 'proposals'])
            ->orderByDesc('due_date')
            ->get();

        $emails = GcEmail::forGc($gc)
            ->with('proposal.project')
            ->get()
            ->filter(fn ($email) => $inRange($email->display_date))
            ->sortByDesc(fn ($email) => $email->display_date?->timestamp ?? 0)
            ->values();

        $contacts = $gc->contacts;

        $uniqueKey = "REGEXP_REPLACE(name, '^[^0-9]*([0-9]+).*$', '\\\\1')";

        $totalProjects = Project::where('gc', $gc->name)
                                ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                ->value('total');

        $activeProjects = Project::where('gc', $gc->name)
                                 ->whereNotIn('status', ['completed', 'SUBMITTED', 'DECLINED', 'MISSED', 'Avoided'])
                                 ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                 ->value('total');

        $completedProjects = Project::where('gc', $gc->name)
                                    ->whereIn('status', ['completed', 'SUBMITTED'])
                                    ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                    ->value('total');

        $muProjects = Project::where('gc', $gc->name)
                             ->where('type', 'MULTIUNIT')
                             ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                             ->value('total');

        $nonMuProjects = Project::where('gc', $gc->name)
                                ->where(fn($q) => $q->where('type', 'NON MU')->orWhereNull('type'))
                                ->selectRaw("COUNT(DISTINCT {$uniqueKey}) as total")
                                ->value('total');

        return view('admin.gcs.show', compact(
            'gc',
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'muProjects',
            'nonMuProjects',
            'proposals',
            'stats',
            'projects',
            'emails',
            'contacts'
        ));
    }

    /**
     * Sent / won / lost / pending counts for MU, NON MU and total.
     * Pending = no result yet or "pending" (same rule as the GC list).
     */
    private function resultStats($proposals, string $field): array
    {
        $groups = [
            'MU'     => $proposals->filter(fn ($p) => optional($p->project)->type === 'MULTIUNIT'),
            'NON MU' => $proposals->filter(fn ($p) => optional($p->project)->type !== 'MULTIUNIT'),
            'Total'  => $proposals,
        ];

        return collect($groups)->map(function ($group) use ($field) {
            $won = $group->where($field, 'win')->count();
            $lost = $group->where($field, 'loss')->count();

            return [
                'sent'     => $group->count(),
                'won'      => $won,
                'lost'     => $lost,
                'pending'  => $group->count() - $won - $lost,
                'win_rate' => ($won + $lost) > 0 ? round($won / ($won + $lost) * 100) : null,
            ];
        })->all();
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