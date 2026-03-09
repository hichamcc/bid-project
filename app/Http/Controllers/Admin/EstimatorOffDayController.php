<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstimatorOffDay;
use App\Models\User;
use Illuminate\Http\Request;

class EstimatorOffDayController extends Controller
{
    public function index(Request $request)
    {
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        $query = EstimatorOffDay::with('estimator')->orderBy('start_date', 'desc');

        if ($request->filled('estimator_id')) {
            $query->where('user_id', $request->estimator_id);
        }

        $offDays = $query->paginate(20)->withQueryString();

        return view('admin.off-days.index', compact('offDays', 'estimators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:255',
        ]);

        EstimatorOffDay::create($validated);

        return redirect()->route('admin.off-days.index')
            ->with('success', 'Off day range added successfully.');
    }

    public function destroy(EstimatorOffDay $offDay)
    {
        $offDay->delete();

        return redirect()->route('admin.off-days.index')
            ->with('success', 'Off day range removed.');
    }
}
