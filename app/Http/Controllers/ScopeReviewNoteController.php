<?php

namespace App\Http\Controllers;

use App\Models\ScopeReview;
use App\Models\ScopeReviewNote;
use App\Models\ScopeReviewStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Append-able notes on a scope review. Notes are separate from the two legacy
 * single-note fields (notes / estimator_notes) and are tagged admin|estimator.
 * Add/edit/delete are each recorded in the scope review's activity history.
 */
class ScopeReviewNoteController extends Controller
{
    public function store(Request $request, ScopeReview $scopeReview)
    {
        $user = Auth::user();
        $this->authorizeParticipant($scopeReview, $user);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        // Context follows the author's role: admins post admin notes, everyone
        // else (assigned estimator) posts estimator notes.
        $context = $user->isAdmin() ? 'admin' : 'estimator';

        $note = $scopeReview->noteEntries()->create([
            'user_id' => $user->id,
            'context' => $context,
            'body'    => $validated['body'],
        ]);

        $this->logHistory($scopeReview, $user, 'note_added', null, $note->body);

        return $this->notesResponse($request, $scopeReview);
    }

    public function update(Request $request, ScopeReview $scopeReview, ScopeReviewNote $note)
    {
        $user = Auth::user();
        $this->assertNoteBelongs($scopeReview, $note);

        if (!$note->editableBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $old = $note->body;
        $note->update(['body' => $validated['body']]);

        if ($old !== $note->body) {
            $this->logHistory($scopeReview, $user, 'note_edited', $old, $note->body);
        }

        return $this->notesResponse($request, $scopeReview);
    }

    public function destroy(Request $request, ScopeReview $scopeReview, ScopeReviewNote $note)
    {
        $user = Auth::user();
        $this->assertNoteBelongs($scopeReview, $note);

        if (!$note->editableBy($user)) {
            abort(403);
        }

        $body = $note->body;
        $note->delete();

        $this->logHistory($scopeReview, $user, 'note_deleted', $body, null);

        return $this->notesResponse($request, $scopeReview);
    }

    /**
     * Only admins and the assigned estimator may add notes.
     */
    private function authorizeParticipant(ScopeReview $scopeReview, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if (($user->isEstimator() || $user->isHeadEstimator()) && $scopeReview->assigned_estimator_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function assertNoteBelongs(ScopeReview $scopeReview, ScopeReviewNote $note): void
    {
        if ($note->scope_review_id !== $scopeReview->id) {
            abort(404);
        }
    }

    private function logHistory(ScopeReview $scopeReview, User $user, string $field, ?string $old, ?string $new): void
    {
        ScopeReviewStatusHistory::create([
            'scope_review_id' => $scopeReview->id,
            'user_id'         => $user->id,
            'field'           => $field,
            'old_value'       => $old,
            'new_value'       => $new,
            'created_at'      => now(),
        ]);
    }

    /**
     * Re-render the notes list partial (AJAX modal) or redirect back (full page).
     */
    private function notesResponse(Request $request, ScopeReview $scopeReview)
    {
        $scopeReview->load(['noteEntries.user']);

        if ($request->boolean('modal') || $request->header('X-Scope-Modal') === '1' || $request->wantsJson()) {
            return view('scope-review.partials.notes-thread', compact('scopeReview'));
        }

        return redirect()->route('scope-review.index')->with('success', 'Note saved.');
    }
}
