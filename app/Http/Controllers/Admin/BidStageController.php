<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidStage;
use App\Models\ScopeReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BidStageController extends Controller
{
    public function create()
    {
        return view('admin.bid-stages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:bid_stages,name',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = BidStage::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->boolean('is_active');

        BidStage::create($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Bid stage created successfully.');
    }

    public function edit(BidStage $bidStage)
    {
        return view('admin.bid-stages.edit', compact('bidStage'));
    }

    public function update(Request $request, BidStage $bidStage)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('bid_stages')->ignore($bidStage->id)],
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $bidStage->update($validated);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Bid stage updated successfully.');
    }

    public function destroy(BidStage $bidStage)
    {
        $usageCount = ScopeReview::where('bid_stage', $bidStage->name)->count();

        if ($usageCount > 0) {
            return redirect()->route('admin.sources.index')
                ->with('error', "Cannot delete bid stage '{$bidStage->name}' because it is used by {$usageCount} scope review(s).");
        }

        $bidStage->delete();

        return redirect()->route('admin.sources.index')
            ->with('success', 'Bid stage deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'bid_stages'              => 'required|array',
            'bid_stages.*.id'         => 'required|exists:bid_stages,id',
            'bid_stages.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['bid_stages'] as $stageData) {
            BidStage::where('id', $stageData['id'])
                ->update(['sort_order' => $stageData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
