<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidStage;
use App\Models\Platform;
use App\Models\ReasonToIgnore;
use App\Models\ScopeReview;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        // One page manages Sources, Platforms, Bid Stages and Reasons to Ignore (four tables).
        $sources         = Source::ordered()->get();
        $platforms       = Platform::ordered()->get();
        $bidStages       = BidStage::ordered()->get();
        $reasonsToIgnore = ReasonToIgnore::ordered()->get();

        return view('admin.sources.index', compact('sources', 'platforms', 'bidStages', 'reasonsToIgnore'));
    }

    public function create()
    {
        return view('admin.sources.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:sources,name',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Source::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->boolean('is_active');

        Source::create($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source created successfully.');
    }

    public function edit(Source $source)
    {
        return view('admin.sources.edit', compact('source'));
    }

    public function update(Request $request, Source $source)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('sources')->ignore($source->id)],
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $source->update($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source updated successfully.');
    }

    public function destroy(Source $source)
    {
        // Block deletion if scope reviews still reference this source name.
        $usageCount = ScopeReview::where('source', $source->name)->count();

        if ($usageCount > 0) {
            return redirect()->route('admin.sources.index')
                ->with('error', "Cannot delete source '{$source->name}' because it is used by {$usageCount} scope review(s).");
        }

        $source->delete();

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'sources'              => 'required|array',
            'sources.*.id'         => 'required|exists:sources,id',
            'sources.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['sources'] as $sourceData) {
            Source::where('id', $sourceData['id'])
                ->update(['sort_order' => $sourceData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
