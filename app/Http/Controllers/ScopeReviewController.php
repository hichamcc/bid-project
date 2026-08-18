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

        $savedViews = ScopeReviewView::query()
            ->where(fn($q) => $q->sharedDefaults()->orWhere('user_id', $user->id))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('scope-review.index', compact('scopeReviews', 'estimators', 'savedViews'));
    }

    public function stats()
    {
        $this->authorizeAdmin();

        // --- Bid Status Summary ---
        $yesNonMu     = ScopeReview::where('decision', 'approved')->where('project_type', 'NON_MU')->count();
        $yesMu        = ScopeReview::where('decision', 'approved')->where('project_type', 'MU')->count();
        $yesNoType    = ScopeReview::where('decision', 'approved')->whereNull('project_type')->count();
        $no           = ScopeReview::where('decision', 'not_in_scope')->count();
        $rfiRequested = ScopeReview::where('decision', 'rfi_requested')->count();
        $skipped      = ScopeReview::where('decision', 'skipped')->count();
        $notReviewed  = ScopeReview::pendingReview()->count();
        $totalYes     = ScopeReview::where('decision', 'approved')->count();
        $totalProjects = ScopeReview::count();

        $bidStatusSummary = [
            ['label' => 'YES - NON MU',      'count' => $yesNonMu,     'filters' => ['decision' => 'approved', 'project_type' => 'NON_MU']],
            ['label' => 'YES - MU',          'count' => $yesMu,        'filters' => ['decision' => 'approved', 'project_type' => 'MU']],
            ['label' => 'YES - No Type',     'count' => $yesNoType,    'filters' => ['decision' => 'approved'], 'hide_if_zero' => true],
            ['label' => 'NO',                'count' => $no,           'filters' => ['decision' => 'not_in_scope']],
            ['label' => 'REQUESTED RFI',     'count' => $rfiRequested, 'filters' => ['decision' => 'rfi_requested']],
            ['label' => 'SKIP',              'count' => $skipped,      'filters' => ['decision' => 'skipped']],
            ['label' => 'NOT YET REVIEWED',  'count' => $notReviewed,  'filters' => ['decision' => '__pending__']],
        ];

        // --- Headline stat cards ---
        $statCards = [
            [
                'label' => 'Pending Scope Review', 'value' => $notReviewed,
                'href' => route('scope-review.index', ['decision' => '__pending__']),
                'bg' => 'bg-orange-50 dark:bg-orange-900/20', 'icon_bg' => 'bg-orange-100 dark:bg-orange-900/40',
                'icon_color' => 'text-orange-500 dark:text-orange-300', 'value_color' => 'text-gray-900 dark:text-gray-100',
                'icon' => 'phosphor-hourglass',
            ],
            [
                'label' => 'RFI Sent', 'value' => $rfiRequested,
                'href' => route('scope-review.index', ['decision' => 'rfi_requested']),
                'bg' => 'bg-purple-50 dark:bg-purple-900/20', 'icon_bg' => 'bg-purple-100 dark:bg-purple-900/40',
                'icon_color' => 'text-purple-500 dark:text-purple-300', 'value_color' => 'text-gray-900 dark:text-gray-100',
                'icon' => 'phosphor-envelope',
            ],
            [
                'label' => 'Approved', 'value' => $totalYes,
                'href' => route('scope-review.index', ['decision' => 'approved']),
                'bg' => 'bg-green-50 dark:bg-green-900/20', 'icon_bg' => 'bg-green-100 dark:bg-green-900/40',
                'icon_color' => 'text-green-600 dark:text-green-300', 'value_color' => 'text-green-700 dark:text-green-300',
                'icon' => 'phosphor-check-circle',
            ],
            [
                'label' => 'Not Within Scope', 'value' => $no,
                'href' => route('scope-review.index', ['decision' => 'not_in_scope']),
                'bg' => 'bg-blue-50 dark:bg-blue-900/20', 'icon_bg' => 'bg-blue-100 dark:bg-blue-900/40',
                'icon_color' => 'text-blue-500 dark:text-blue-300', 'value_color' => 'text-gray-900 dark:text-gray-100',
                'icon' => 'phosphor-x-circle',
            ],
            [
                'label' => 'Skipped', 'value' => $skipped,
                'href' => route('scope-review.index', ['decision' => 'skipped']),
                'bg' => 'bg-gray-50 dark:bg-gray-700/40', 'icon_bg' => 'bg-gray-200 dark:bg-gray-600',
                'icon_color' => 'text-gray-500 dark:text-gray-300', 'value_color' => 'text-gray-900 dark:text-gray-100',
                'icon' => 'phosphor-prohibit',
            ],
        ];

        // --- Platforms Summary (count + yes bids) ---
        $platformSummary = ScopeReview::selectRaw("
                platform,
                COUNT(*) as total,
                SUM(CASE WHEN decision = 'approved' THEN 1 ELSE 0 END) as yes_bids
            ")
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        $platformTotalCount = $platformSummary->sum('total');
        $platformTotalYes   = $platformSummary->sum('yes_bids');

        // --- Pending Review by Estimator ---
        $estimatorSummary = User::whereIn('role', ['estimator', 'head_estimator'])
            ->orderBy('name')
            ->get()
            ->map(function ($estimator) {
                return [
                    'estimator'      => $estimator,
                    'pending_review' => ScopeReview::pendingReview()->where('assigned_estimator_id', $estimator->id)->count(),
                ];
            });

        $estimatorTotalPending = $estimatorSummary->sum('pending_review');

        return view('scope-review.stats', compact(
            'statCards',
            'bidStatusSummary',
            'totalYes',
            'totalProjects',
            'platformSummary',
            'platformTotalCount',
            'platformTotalYes',
            'estimatorSummary',
            'estimatorTotalPending'
        ));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        // Modal (AJAX) request wants just the form body.
        if ($this->wantsPartial($request)) {
            return view('scope-review.partials.create-form', compact('estimators'));
        }

        return view('scope-review.create', compact('estimators'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $rules = [
            'entry_date'             => 'required|date',
            'source'                 => 'nullable|string|max:255',
            'bid_stage'              => 'nullable|string|max:255',
            'platform'               => 'nullable|string|max:255',
            'project_name'           => 'required|string|max:255',
            'due_date'               => 'nullable|date',
            'project_link'           => 'nullable|string|max:2048',
            'location'               => 'nullable|string|max:255',
            'notes'                  => 'nullable|string',
            'assigned_estimator_id'  => 'nullable|exists:users,id',
        ];

        if ($this->wantsPartial($request)) {
            $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

            return $this->handleModalSubmit($request, $rules, 'scope-review.partials.create-form',
                compact('estimators'),
                function (array $validated) {
                    $this->assertProjectNameNotDuplicate($validated['project_name']);
                    ScopeReview::create($validated + ['created_by' => Auth::id()]);
                },
                'Scope review entry created.'
            );
        }

        $validated = $request->validate($rules);
        $this->assertProjectNameNotDuplicate($validated['project_name']);
        ScopeReview::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('scope-review.index')
            ->with('success', 'Scope review entry created.');
    }

    public function edit(Request $request, ScopeReview $scopeReview)
    {
        $user = Auth::user();
        $this->authorizeView($scopeReview, $user);

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        if ($this->wantsPartial($request)) {
            return view('scope-review.partials.edit-form', compact('scopeReview', 'estimators'));
        }

        return view('scope-review.edit', compact('scopeReview', 'estimators'));
    }

    public function update(Request $request, ScopeReview $scopeReview)
    {
        $user = Auth::user();
        $this->authorizeView($scopeReview, $user);

        // Modal (AJAX) submit: run the same logic, but re-render the form partial
        // with errors on failure and return a JSON success signal on success.
        if ($this->wantsPartial($request)) {
            try {
                $this->performUpdate($request, $scopeReview, $user);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
                $errors = new \Illuminate\Support\ViewErrorBag();
                $errors->put('default', $e->validator->errors());

                return response()->view('scope-review.partials.edit-form', [
                    'scopeReview' => $scopeReview->fresh(),
                    'estimators'  => $estimators,
                    'errors'      => $errors,
                ], 422);
            }

            return response()->json(['ok' => true]);
        }

        $this->performUpdate($request, $scopeReview, $user);

        return redirect()->route('scope-review.index')
            ->with('success', $user->isAdmin() ? 'Scope review updated.' : 'Scope review submitted.');
    }

    public function destroy(ScopeReview $scopeReview)
    {
        $this->authorizeAdmin();

        // Don't delete an entry that's already been turned into a real job.
        if ($scopeReview->isConverted()) {
            return redirect()->route('scope-review.index')
                ->with('error', 'This scope review has already been assigned to a job and cannot be deleted. Remove the job from Distribution first.');
        }

        $scopeReview->delete();

        return redirect()->route('scope-review.index')
            ->with('success', 'Scope review deleted.');
    }

    /**
     * Core update logic (validation + apply), shared by the normal and modal paths.
     */
    private function performUpdate(Request $request, ScopeReview $scopeReview, User $user): void
    {
        if ($user->isAdmin()) {
            $validated = $request->validate([
                'entry_date'             => 'required|date',
                'source'                 => 'nullable|string|max:255',
                'bid_stage'              => 'nullable|string|max:255',
                'platform'               => 'nullable|string|max:255',
                'project_name'           => 'required|string|max:255',
                'due_date'               => 'nullable|date',
                'project_link'           => 'nullable|string|max:2048',
                'location'               => 'nullable|string|max:255',
                'notes'                  => 'nullable|string',
                'assigned_estimator_id'  => 'nullable|exists:users,id',
                // Admin can also set the review/decision fields. Decision is
                // optional for admin ('' / null = leave pending).
                'project_type'           => 'nullable|required_if:decision,approved|in:MU,NON_MU',
                'decision'               => 'nullable|in:approved,rfi_requested,not_in_scope,skipped',
                'duration'               => 'nullable|string|max:255',
                'estimator_notes'        => 'nullable|string',
                'uploaded_in_oh'         => 'nullable|boolean',
            ]);

            $this->assertProjectNameNotDuplicate($validated['project_name'], $scopeReview->id);

            $validated['decision'] = ($validated['decision'] ?? null) ?: null;
            $this->applyReviewFields($validated, $scopeReview, $user);

            $scopeReview->update($validated);

            return;
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

        $this->applyReviewFields($validated, $scopeReview, $user);

        $scopeReview->update($validated);
    }

    /**
     * Apply the review-side effects of a decision change (uploaded_in_oh cast,
     * reviewed_at stamp, project-number generation on approve, and status-history
     * logging). Mutates $validated in place and writes a history row if the
     * decision actually changed. Shared by the admin and estimator update paths.
     */
    private function applyReviewFields(array &$validated, ScopeReview $scopeReview, User $user): void
    {
        $validated['uploaded_in_oh'] = request()->boolean('uploaded_in_oh');

        $decisionChanged = array_key_exists('decision', $validated)
            && $validated['decision'] !== $scopeReview->decision;

        // Stamp reviewed_at whenever a decision is present on this submission.
        if (!empty($validated['decision'])) {
            $validated['reviewed_at'] = now();
        }

        if (($validated['decision'] ?? null) === 'approved' && !$scopeReview->project_number) {
            $validated['project_number'] = $this->nextProjectNumber();
        }

        if ($decisionChanged && !empty($validated['decision'])) {
            ScopeReviewStatusHistory::create([
                'scope_review_id' => $scopeReview->id,
                'user_id'         => $user->id,
                'decision'        => $validated['decision'],
                'created_at'      => now(),
            ]);
        }
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

    /**
     * Block duplicate project names (case-insensitive, trimmed) to avoid
     * accidentally logging the same opportunity twice.
     */
    private function assertProjectNameNotDuplicate(string $projectName, ?int $ignoreId = null): void
    {
        $normalized = strtolower(trim($projectName));

        $query = ScopeReview::whereRaw('LOWER(TRIM(project_name)) = ?', [$normalized]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_name' => 'This project already exists in Scope Review. Please avoid duplicate entries.',
            ]);
        }
    }

    /**
     * True when the request is the modal (AJAX) variant that wants just the
     * form partial / JSON, not a full page or redirect.
     */
    private function wantsPartial(Request $request): bool
    {
        return $request->boolean('modal') || $request->header('X-Scope-Modal') === '1';
    }

    /**
     * Run a modal write action: on validation failure re-render the form partial
     * with errors (422); on success return a JSON signal so the JS closes the
     * modal and refreshes the list.
     */
    private function handleModalSubmit(Request $request, array $rules, string $partial, array $viewData, \Closure $persist, string $successMessage)
    {
        try {
            $validated = $request->validate($rules);
            $persist($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = new \Illuminate\Support\ViewErrorBag();
            $errors->put('default', $e->validator->errors());

            return response()->view($partial, array_merge($viewData, ['errors' => $errors]), 422);
        }

        return response()->json(['ok' => true, 'message' => $successMessage]);
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
