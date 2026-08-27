<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\ScopeReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hidden (link-only) admin tool to reconcile imported scope reviews with
 * imported allocations by matching scope_review.project_number to
 * allocation.job_number. Admin reviews the proposed matches and approves the
 * ones to link (sets scope_review.allocation_id).
 *
 * Not linked from any menu; access is via the direct URL only, still behind
 * the admin auth middleware.
 */
class ScopeReconcileController extends Controller
{
    /**
     * Project-number values (normalised) that mean "skipped", not a real number.
     */
    private const SKIP_TOKENS = ['skip', 'skipped'];

    public function index(Request $request)
    {
        // Candidates: approved scope reviews not yet linked, that carry a real
        // project number (excluding the "skip" placeholder rows, handled below).
        $scopeReviews = ScopeReview::where('decision', 'approved')
            ->whereNull('allocation_id')
            ->whereNotNull('project_number')
            ->where('project_number', '!=', '')
            ->orderBy('project_number')
            ->get()
            ->reject(fn ($sr) => in_array($this->normalise($sr->project_number), self::SKIP_TOKENS, true));

        // Scope reviews whose project_number is the literal "skip"/"skipped" text.
        // Proposed cleanup: null the project number and set decision = skipped.
        $skips = ScopeReview::whereNotNull('project_number')
            ->where('project_number', '!=', '')
            ->orderBy('project_name')
            ->get()
            ->filter(fn ($sr) => in_array($this->normalise($sr->project_number), self::SKIP_TOKENS, true))
            ->values();

        // Index allocations by a normalised job number. A normalised key can map
        // to multiple allocations (duplicate job numbers) -> ambiguous.
        $allocationsByJob = Allocation::with(['projects' => fn ($q) => $q->limit(1)])
            ->orderBy('id')->get()
            ->groupBy(fn ($a) => $this->normalise($a->job_number));

        $matched   = [];   // exactly one allocation -> auto-selectable
        $ambiguous = [];   // more than one allocation for the same number
        $unmatched = [];   // no allocation for this number

        foreach ($scopeReviews as $sr) {
            $key   = $this->normalise($sr->project_number);
            $allocs = $allocationsByJob->get($key);

            if (!$allocs || $allocs->isEmpty()) {
                $unmatched[] = $sr;
            } elseif ($allocs->count() === 1) {
                $matched[] = ['scope' => $sr, 'allocation' => $allocs->first()];
            } else {
                $ambiguous[] = ['scope' => $sr, 'allocations' => $allocs];
            }
        }

        return view('admin.scope-reconcile.index', compact('matched', 'ambiguous', 'unmatched', 'skips'));
    }

    /**
     * Clean up "skip" project-number rows: null the project_number and set the
     * decision to skipped, for the selected (and re-verified) scope reviews.
     */
    public function approveSkips(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:scope_reviews,id',
        ]);

        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($validated, &$updated, &$skipped) {
            $reviews = ScopeReview::whereIn('id', $validated['ids'])->get();

            foreach ($reviews as $sr) {
                // Guard: only act on rows whose project_number really is a skip token.
                if (!in_array($this->normalise($sr->project_number), self::SKIP_TOKENS, true)) {
                    $skipped++;
                    continue;
                }

                $sr->update([
                    'project_number' => null,
                    'decision'       => 'skipped',
                ]);
                $updated++;
            }
        });

        $message = $updated . ' scope review' . ($updated === 1 ? '' : 's') . ' cleared and marked skipped.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' skipped (no longer a "skip" project number).';
        }

        return redirect()->route('admin.scope-reconcile.index')
            ->with($updated > 0 ? 'success' : 'error', $message);
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            // pairs[scope_review_id] = allocation_id
            'pairs'   => 'required|array|min:1',
            'pairs.*' => 'required|integer|exists:allocations,id',
        ]);

        $linked  = 0;
        $skipped = 0;

        DB::transaction(function () use ($validated, &$linked, &$skipped) {
            foreach ($validated['pairs'] as $scopeReviewId => $allocationId) {
                // Only link approved, still-unlinked scope reviews, and guard that
                // the project_number really matches the chosen allocation's job_number.
                $sr = ScopeReview::where('id', (int) $scopeReviewId)
                    ->where('decision', 'approved')
                    ->whereNull('allocation_id')
                    ->first();

                $alloc = Allocation::find($allocationId);

                if (!$sr || !$alloc || $this->normalise($sr->project_number) !== $this->normalise($alloc->job_number)) {
                    $skipped++;
                    continue;
                }

                $sr->update(['allocation_id' => $alloc->id]);
                $linked++;
            }
        });

        $message = $linked . ' scope review' . ($linked === 1 ? '' : 's') . ' linked to allocations.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' skipped (already linked or mismatch).';
        }

        return redirect()->route('admin.scope-reconcile.index')
            ->with($linked > 0 ? 'success' : 'error', $message);
    }

    /**
     * Normalise a job/project number for comparison: trim + lowercase.
     */
    private function normalise(?string $value): string
    {
        return strtolower(trim((string) $value));
    }
}
