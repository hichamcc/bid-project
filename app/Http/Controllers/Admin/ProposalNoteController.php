<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Append-only notes on a proposal; each note records its author and timestamp.
 * AJAX requests (the notes popup on the list) get the re-rendered thread back;
 * normal form posts (the show page) redirect back.
 */
class ProposalNoteController extends Controller
{
    public function index(Proposal $proposal)
    {
        return $this->thread($proposal);
    }

    public function store(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $proposal->notes()->create([
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        return $this->notesResponse($request, $proposal, 'Note added.');
    }

    public function destroy(Request $request, Proposal $proposal, ProposalNote $note)
    {
        if ($note->proposal_id !== $proposal->id) {
            abort(404);
        }

        if (!$note->editableBy(Auth::user())) {
            abort(403);
        }

        $note->delete();

        return $this->notesResponse($request, $proposal, 'Note deleted.');
    }

    private function notesResponse(Request $request, Proposal $proposal, string $message)
    {
        if ($request->ajax()) {
            return $this->thread($proposal);
        }

        return redirect()->back()->with('success', $message);
    }

    private function thread(Proposal $proposal)
    {
        $proposal->load(['notes.user', 'latestNote.user'])->loadCount('notes');

        return view('admin.proposals.partials.notes-thread', compact('proposal'));
    }
}
