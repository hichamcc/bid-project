<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\JobAssignedMail;
use App\Models\Allocation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function index(Request $request)
    {
        $allocations = Allocation::with('estimators')
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        return view('admin.allocation.index', compact('allocations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_number'    => 'required|string|max:255',
            'due_date'      => 'required|date',
            'days_required' => 'required|numeric|min:0.1',
            'job_type'      => 'required|in:MU,NON_MU',
        ]);

        // Check if job number is already allocated
        if (Allocation::where('job_number', $validated['job_number'])->exists()) {
            return redirect()->route('admin.allocation.index')
                ->withInput()
                ->with('error', "Job \"{$validated['job_number']}\" has already been allocated.");
        }

        $dueDate      = Carbon::parse($validated['due_date']);
        $assignedDate = $dueDate->copy()->subDays(2);

        // If assigned date falls on Sunday, move back to Saturday
        if ($assignedDate->isSunday()) {
            $assignedDate->subDay();
        }

        $allocation = Allocation::create([
            'job_number'    => $validated['job_number'],
            'due_date'      => $dueDate,
            'assigned_date' => $assignedDate,
            'days_required' => $validated['days_required'],
            'job_type'      => $validated['job_type'],
        ]);

        // Determine eligible estimators and how many to assign
        if ($validated['job_type'] === 'MU') {
            $eligible    = User::whereIn('role', ['estimator', 'head_estimator'])->where('MU', 'yes')->get();
            $assignCount = 3;
        } else {
            $eligible    = User::whereIn('role', ['estimator', 'head_estimator'])->where('NON_MU', 'yes')->get();
            $assignCount = 2;
        }

        // Calculate effective load per eligible estimator for the target month
        $targetMonth = $dueDate->month;
        $targetYear  = $dueDate->year;

        // Filter out estimators with off days overlapping assigned_date–due_date
        $eligible = $eligible->filter(function ($estimator) use ($assignedDate, $dueDate) {
            return !\App\Models\EstimatorOffDay::where('user_id', $estimator->id)
                ->where('start_date', '<=', $dueDate)
                ->where('end_date', '>=', $assignedDate)
                ->exists();
        });

        $eligible = $eligible->map(function ($estimator) use ($targetMonth, $targetYear) {
            $load = DB::table('allocation_user')
                ->join('allocations', 'allocation_user.allocation_id', '=', 'allocations.id')
                ->where('allocation_user.user_id', $estimator->id)
                ->whereMonth('allocations.due_date', $targetMonth)
                ->whereYear('allocations.due_date', $targetYear)
                ->where('allocations.status', 'open')
                ->sum('allocations.days_required');

            $weight                    = $estimator->weight ?? 1.0;
            $estimator->effective_load = $load / $weight;

            return $estimator;
        });

        $selected = $eligible->sortBy('effective_load')->take($assignCount);

        $allocation->estimators()->attach($selected->pluck('id'));

        $warning = $selected->count() < $assignCount
            ? "Warning: only {$selected->count()} of {$assignCount} estimators were available (others have off days during this period)."
            : null;

        // Notify each assigned estimator by email
        foreach ($selected as $estimator) {
            Mail::to($estimator->email)->send(new JobAssignedMail($allocation, $estimator));
        }

        $redirect = redirect()->route('admin.allocation.index')
            ->with('success', "Job {$allocation->job_number} assigned to {$selected->count()} estimator(s).");

        if ($warning) {
            $redirect = $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function destroy(Allocation $allocation)
    {
        $allocation->delete();

        return redirect()->route('admin.allocation.index')
            ->with('success', 'Allocation deleted.');
    }

    public function monthly(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])
            ->orderBy('name')
            ->get();

        $currentDate = Carbon::create($year, $month, 1);

        // Build label maps based on insertion order (id asc)
        $muPool = User::whereIn('role', ['estimator', 'head_estimator'])
            ->where('MU', 'yes')->orderBy('id')->get();
        $nonMuPool = User::whereIn('role', ['estimator', 'head_estimator'])
            ->where('NON_MU', 'yes')->orderBy('id')->get();

        $muLabels = [];
        foreach ($muPool as $i => $u) {
            $muLabels[$u->id] = (string)($i + 1);
        }

        $nonMuLabels = [];
        foreach ($nonMuPool as $i => $u) {
            $nonMuLabels[$u->id] = chr(65 + $i); // A, B, C...
        }

        // Build list of Mon–Sat days in the month
        $days = [];
        $cursor = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        while ($cursor->lte($endOfMonth)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $days[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        // Load all allocations for the month (keyed by due_date month)
        $allocations = Allocation::with(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc')])
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->get();

        // Index: $jobsByDateAndUser['Y-m-d'][userId] = [allocations]
        $jobsByDateAndUser = [];
        foreach ($allocations as $allocation) {
            $dateKey = $allocation->assigned_date->format('Y-m-d');
            foreach ($allocation->estimators as $estimator) {
                $jobsByDateAndUser[$dateKey][$estimator->id][] = $allocation;
            }
        }

        // Per-estimator totals for the month
        $totals = [];
        foreach ($estimators as $estimator) {
            $assigned = $allocations->filter(fn($a) => $a->estimators->contains('id', $estimator->id));
            $totalDays = $assigned->sum('days_required');
            $weight    = $estimator->weight ?? 1.0;
            $totals[$estimator->id] = [
                'total_days'    => $totalDays,
                'effective_load'=> round($totalDays / $weight, 2),
            ];
        }

        return view('admin.allocation.monthly', compact(
            'estimators', 'days', 'jobsByDateAndUser', 'totals', 'month', 'year', 'currentDate',
            'muLabels', 'nonMuLabels'
        ));
    }
}
