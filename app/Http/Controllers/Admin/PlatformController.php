<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\ScopeReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformController extends Controller
{
    public function create()
    {
        return view('admin.platforms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:platforms,name',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Platform::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->boolean('is_active');

        Platform::create($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Platform created successfully.');
    }

    public function edit(Platform $platform)
    {
        return view('admin.platforms.edit', compact('platform'));
    }

    public function update(Request $request, Platform $platform)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('platforms')->ignore($platform->id)],
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $platform->update($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Platform updated successfully.');
    }

    public function destroy(Platform $platform)
    {
        $usageCount = ScopeReview::where('platform', $platform->name)->count();

        if ($usageCount > 0) {
            return redirect()->route('admin.sources.index')
                ->with('error', "Cannot delete platform '{$platform->name}' because it is used by {$usageCount} scope review(s).");
        }

        $platform->delete();

        return redirect()->route('admin.sources.index')
            ->with('success', 'Platform deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'platforms'              => 'required|array',
            'platforms.*.id'         => 'required|exists:platforms,id',
            'platforms.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['platforms'] as $platformData) {
            Platform::where('id', $platformData['id'])
                ->update(['sort_order' => $platformData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
