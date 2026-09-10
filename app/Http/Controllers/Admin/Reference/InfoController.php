<?php

namespace App\Http\Controllers\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\ReferenceInfo;
use App\Models\ReferenceReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferenceInfo::ordered();

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('item', 'like', $term)->orWhere('description', 'like', $term));
        }

        $infos     = $query->get();
        $reminders = ReferenceReminder::with('user')->orderByDesc('created_at')->get();

        return view('admin.reference.infos.index', compact('infos', 'reminders'));
    }

    public function create()
    {
        return view('admin.reference.infos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = $data['sort_order'] ?? (ReferenceInfo::max('sort_order') + 1);

        ReferenceInfo::create($data);

        return redirect()->route('admin.reference.infos.index')->with('success', 'Info saved.');
    }

    public function edit(ReferenceInfo $info)
    {
        return view('admin.reference.infos.edit', compact('info'));
    }

    public function update(Request $request, ReferenceInfo $info)
    {
        $info->update($this->validated($request));

        return redirect()->route('admin.reference.infos.index')->with('success', 'Info updated.');
    }

    public function destroy(ReferenceInfo $info)
    {
        $info->delete();

        return redirect()->route('admin.reference.infos.index')->with('success', 'Info deleted.');
    }

    /**
     * Add a reminder (comment-style entry).
     */
    public function storeReminder(Request $request)
    {
        $validated = $request->validate(['body' => 'required|string|max:5000']);

        ReferenceReminder::create([
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        return redirect()->route('admin.reference.infos.index')->with('success', 'Reminder added.');
    }

    public function updateReminder(Request $request, ReferenceReminder $reminder)
    {
        if (!$reminder->editableBy(Auth::user())) {
            abort(403);
        }

        $validated = $request->validate(['body' => 'required|string|max:5000']);
        $reminder->update(['body' => $validated['body']]);

        return redirect()->route('admin.reference.infos.index')->with('success', 'Reminder updated.');
    }

    public function destroyReminder(ReferenceReminder $reminder)
    {
        if (!$reminder->editableBy(Auth::user())) {
            abort(403);
        }

        $reminder->delete();

        return redirect()->route('admin.reference.infos.index')->with('success', 'Reminder deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'item'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
    }
}
