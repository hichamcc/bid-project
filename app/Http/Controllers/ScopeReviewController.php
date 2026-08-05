<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ScopeReview;
use App\Models\ScopeReviewStatusHistory;
use App\Models\ScopeReviewView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScopeReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ScopeReview::with(['assignedEstimator', 'creator', 'statusHistories.user']);

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('project_type')) {
            $query->where('project_type', $request->project_type);
        }
        if ($request->filled('decision')) {
            if ($request->decision === '__pending__') {
                $query->pendingReview();
            } else {
                $query->where('decision', $request->decision);
            }
        }
        if ($request->filled('assigned_estimator_id')) {
            $query->where('assigned_estimator_id', $request->assigned_estimator_id);
        }
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        if ($request->filled('search')) {
            $query->where('project_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('ready_for_assignment')) {
            $query->readyForAssignment();
        }

        $scopeReviews = $query->orderByDesc('entry_date')->paginate(25)->withQueryString();

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        $stats = [
            'pending_review' => ScopeReview::pendingReview()->count(),
            'rfi_requested'  => ScopeReview::where('decision', 'rfi_requested')->count(),
            'approved'       => ScopeReview::where('decision', 'approved')->count(),
            'not_in_scope'   => ScopeReview::where('decision', 'not_in_scope')->count(),
            'skipped'        => ScopeReview::where('decision', 'skipped')->count(),
        ];

        if ($user->isAdmin()) {
            $stats['ready_for_assignment'] = ScopeReview::readyForAssignment()->count();
        }

        $savedViews = ScopeReviewView::query()
            ->where(fn($q) => $q->sharedDefaults()->orWhere('user_id', $user->id))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('scope-review.index', compact('scopeReviews', 'estimators', 'stats', 'savedViews'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        return view('scope-review.create', compact('estimators'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'entry_date'             => 'required|date',
            'source'                 => 'nullable|string|max:255',
            'platform'               => 'nullable|string|max:255',
            'project_name'           => 'required|string|max:255',
            'due_date'               => 'nullable|date',
            'project_link'           => 'nullable|string|max:2048',
            'location'               => 'nullable|string|max:255',
            'notes'                  => 'nullable|string',
            'assigned_estimator_id'  => 'nullable|exists:users,id',
        ]);

        ScopeReview::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('scope-review.index')
            ->with('success', 'Scope review entry created.');
    }

    public function edit(ScopeReview $scopeReview)
    {
        $user = Auth::user();
        $this->authorizeView($scopeReview, $user);

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        return view('scope-review.edit', compact('scopeReview', 'estimators'));
    }

    public function update(Request $request, ScopeReview $scopeReview)
    {
        $user = Auth::user();
        $this->authorizeView($scopeReview, $user);

        if ($user->isAdmin()) {
            $validated = $request->validate([
                'entry_date'             => 'required|date',
                'source'                 => 'nullable|string|max:255',
                'platform'               => 'nullable|string|max:255',
                'project_name'           => 'required|string|max:255',
                'due_date'               => 'nullable|date',
                'project_link'           => 'nullable|string|max:2048',
                'location'               => 'nullable|string|max:255',
                'notes'                  => 'nullable|string',
                'assigned_estimator_id'  => 'nullable|exists:users,id',
            ]);

            $scopeReview->update($validated);

            return redirect()->route('scope-review.index')->with('success', 'Scope review updated.');
        }

        // Estimator: can only update their own review fields, and only while assigned to them.
        if ($scopeReview->assigned_estimator_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'project_type'     => 'nullable|required_if:decision,approved|in:MU,NON_MU',
            'decision'         => 'required|in:approved,rfi_requested,not_in_scope,skipped',
            'duration'         => 'nullable|string|max:255',
            'estimator_notes'  => 'nullable|string',
            'uploaded_in_oh'   => 'nullable|boolean',
        ]);

        $validated['uploaded_in_oh'] = $request->boolean('uploaded_in_oh');
        $validated['reviewed_at'] = now();

        if ($validated['decision'] === 'approved' && !$scopeReview->project_number) {
            $validated['project_number'] = $this->nextProjectNumber();
        }

        $decisionChanged = $validated['decision'] !== $scopeReview->decision;

        $scopeReview->update($validated);

        if ($decisionChanged) {
            ScopeReviewStatusHistory::create([
                'scope_review_id' => $scopeReview->id,
                'user_id'         => $user->id,
                'decision'        => $validated['decision'],
                'created_at'      => now(),
            ]);
        }

        return redirect()->route('scope-review.index')->with('success', 'Scope review submitted.');
    }

    /**
     * Next job number: {2-digit year}{sequence}, e.g. 26601, 26602...
     * Prefers the highest existing number for the current year (increments
     * its sequence portion). If none exist yet this year, continues from the
     * highest numeric job number found anywhere (any year), so the sequence
     * doesn't reset to an arbitrary made-up starting point.
     */
    private function nextProjectNumber(): string
    {
        $yearPrefix = now()->format('y');

        $maxForYear = max(
            (int) $this->maxNumericJobNumber(Allocation::query(), 'job_number', $yearPrefix),
            (int) $this->maxNumericJobNumber(ScopeReview::query(), 'project_number', $yearPrefix)
        );

        if ($maxForYear > 0) {
            return (string) ($maxForYear + 1);
        }

        $maxOverall = max(
            (int) $this->maxNumericJobNumber(Allocation::query(), 'job_number'),
            (int) $this->maxNumericJobNumber(ScopeReview::query(), 'project_number')
        );

        return (string) ($maxOverall + 1);
    }

    private function maxNumericJobNumber($query, string $column, ?string $prefix = null): ?string
    {
        if ($prefix !== null) {
            $query->where($column, 'like', $prefix . '%');
        }

        return $query->whereRaw("{$column} REGEXP '^[0-9]+$'")
            ->orderByRaw("CAST({$column} AS UNSIGNED) DESC")
            ->value($column);
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    private function authorizeView(ScopeReview $scopeReview, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if (($user->isEstimator() || $user->isHeadEstimator()) && $scopeReview->assigned_estimator_id === $user->id) {
            return;
        }

        abort(403);
    }
}
