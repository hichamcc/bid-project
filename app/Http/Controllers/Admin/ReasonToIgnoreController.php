<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReasonToIgnore;
use App\Models\ScopeReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReasonToIgnoreController extends Controller
{
    public function create()
    {
        return view('admin.reason-to-ignore.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:reason_to_ignore,name',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = ReasonToIgnore::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->boolean('is_active');

        ReasonToIgnore::create($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Reason to ignore created successfully.');
    }

    public function edit(ReasonToIgnore $reasonToIgnore)
    {
        return view('admin.reason-to-ignore.edit', compact('reasonToIgnore'));
    }

    public function update(Request $request, ReasonToIgnore $reasonToIgnore)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('reason_to_ignore')->ignore($reasonToIgnore->id)],
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $reasonToIgnore->update($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Reason to ignore updated successfully.');
    }

    public function destroy(ReasonToIgnore $reasonToIgnore)
    {
        $usageCount = ScopeReview::where('reason_to_ignore', $reasonToIgnore->name)->count();

        if ($usageCount > 0) {
            return redirect()->route('admin.sources.index')
                ->with('error', "Cannot delete reason '{$reasonToIgnore->name}' because it is used by {$usageCount} scope review(s).");
        }

        $reasonToIgnore->delete();

        return redirect()->route('admin.sources.index')
            ->with('success', 'Reason to ignore deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'reasons'              => 'required|array',
            'reasons.*.id'         => 'required|exists:reason_to_ignore,id',
            'reasons.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['reasons'] as $reasonData) {
            ReasonToIgnore::where('id', $reasonData['id'])
                ->update(['sort_order' => $reasonData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
