<?php

namespace App\Http\Controllers\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\ReferenceSkippedGc;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkippedGcController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferenceSkippedGc::ordered();

        if ($request->filled('search')) {
            $query->where('gc_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $skippedGcs = $query->get();

        return view('admin.reference.skipped-gcs.index', compact('skippedGcs'));
    }

    public function create()
    {
        return view('admin.reference.skipped-gcs.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = $data['sort_order'] ?? (ReferenceSkippedGc::max('sort_order') + 1);

        ReferenceSkippedGc::create($data);

        return redirect()->route('admin.reference.skipped-gcs.index')->with('success', 'Entry saved.');
    }

    public function edit(ReferenceSkippedGc $skippedGc)
    {
        return view('admin.reference.skipped-gcs.edit', compact('skippedGc'));
    }

    public function update(Request $request, ReferenceSkippedGc $skippedGc)
    {
        $skippedGc->update($this->validated($request));

        return redirect()->route('admin.reference.skipped-gcs.index')->with('success', 'Entry updated.');
    }

    public function destroy(ReferenceSkippedGc $skippedGc)
    {
        $skippedGc->delete();

        return redirect()->route('admin.reference.skipped-gcs.index')->with('success', 'Entry deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'gc_name'    => 'required|string|max:255',
            'status'     => ['required', Rule::in(ReferenceSkippedGc::STATUSES)],
            'note'       => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
