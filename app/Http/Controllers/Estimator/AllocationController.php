<?php

namespace App\Http\Controllers\Estimator;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Allocation::whereHas('estimators', fn($q) => $q->where('users.id', Auth::id()))
            ->with(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc')])
            ->orderBy('due_date', 'asc');

        if ($request->filled('search')) {
            $query->where('job_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->whereHas('estimators', function ($q) use ($request) {
                $q->where('users.id', Auth::id())
                  ->where('allocation_user.status', $request->status);
            });
        }

        $allocations = $query->paginate(20)->withQueryString();

        return view('estimator.workload.index', compact('allocations'));
    }

    public function updateStatus(Request $request, Allocation $allocation)
    {
        // Ensure the allocation belongs to this estimator
        if (!$allocation->estimators->contains('id', Auth::id())) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:open,submitted',
        ]);

        $allocation->estimators()->updateExistingPivot(Auth::id(), ['status' => $request->status]);

        return redirect()->back()->with('success', "Job {$allocation->job_number} marked as {$request->status}.");
    }
}
