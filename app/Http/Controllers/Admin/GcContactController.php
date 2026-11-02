<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gc as GC;
use App\Models\GcContact;
use Illuminate\Http\Request;

class GcContactController extends Controller
{
    public function store(Request $request, GC $gc)
    {
        $gc->contacts()->create($this->validated($request));

        return $this->backToContacts($gc)->with('success', 'Contact added.');
    }

    public function update(Request $request, GC $gc, GcContact $contact)
    {
        abort_unless($contact->gc_id === $gc->id, 404);

        $contact->update($this->validated($request));

        return $this->backToContacts($gc)->with('success', 'Contact updated.');
    }

    public function destroy(GC $gc, GcContact $contact)
    {
        abort_unless($contact->gc_id === $gc->id, 404);

        $contact->delete();

        return $this->backToContacts($gc)->with('success', 'Contact deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'role'          => 'nullable|string|max:255',
            'office_number' => 'nullable|string|max:50',
            'mobile'        => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
        ]);
    }

    private function backToContacts(GC $gc)
    {
        return redirect()->route('admin.gcs.show', ['gc' => $gc, 'tab' => 'contacts']);
    }
}
