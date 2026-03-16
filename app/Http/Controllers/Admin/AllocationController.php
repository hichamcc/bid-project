<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\JobAssignedMail;
use App\Mail\JobRemovedMail;
use App\Models\Allocation;
use App\Models\Gc;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Allocation::with(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'), 'projects'])
            ->orderBy('due_date', 'desc');

        if ($request->filled('job_number')) {
            $query->where('job_number', 'like', '%' . $request->job_number . '%');
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('estimator_id')) {
            $query->whereHas('estimators', fn($q) => $q->where('users.id', $request->estimator_id));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('assigned_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('assigned_date', '<=', $request->date_to);
        }

        $allocations = $query->paginate(20)->withQueryString();

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        $gcs        = Gc::active()->ordered()->get();
        $statuses   = Status::ordered()->get();

        return view('admin.allocation.index', compact('allocations', 'estimators', 'gcs', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_number'         => 'required|string|max:255',
            'due_date'           => 'required|date',
            'days_required'      => 'required|numeric|min:0.1',
            'job_type'           => 'required|in:MU,NON_MU',
            'project_name'       => 'required|string|max:255',
            'gc'                 => 'nullable|string|max:255',
            'project_status'     => 'nullable|string|max:255',
            'project_information'=> 'nullable|string',
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
                ->where('allocation_user.status', 'open')
                ->sum('allocations.days_required');

            $weight                    = $estimator->weight ?? 1.0;
            $estimator->effective_load = $load / $weight;

            return $estimator;
        });

        // Pick up to $assignCount estimators, sorted by effective load,
        // ensuring no two selected estimators share the same location.
        // Estimators with a null location are not subject to the location constraint.
        $selected = collect();
        $usedLocations = [];

        foreach ($eligible->sortBy('effective_load') as $estimator) {
            if ($selected->count() >= $assignCount) {
                break;
            }
            $location = $estimator->location;
            if ($location && in_array($location, $usedLocations)) {
                continue; // skip — another estimator from this location already selected
            }
            $selected->push($estimator);
            if ($location) {
                $usedLocations[] = $location;
            }
        }

        $attachData = $selected->pluck('id')->mapWithKeys(fn($id) => [$id => ['status' => 'open']]);
        $allocation->estimators()->attach($attachData);

        // Create one Project per assigned estimator
        $projectType = $validated['job_type'] === 'MU' ? 'MULTIUNIT' : 'NON MU';
        foreach ($selected->values() as $index => $estimator) {
            $letter      = chr(65 + $index); // A, B, C
            $projectName = "{$validated['job_number']}{$letter}. {$validated['project_name']}";
            Project::create([
                'allocation_id'       => $allocation->id,
                'name'                => $projectName,
                'gc'                  => $validated['gc'] ?? null,
                'status'              => $validated['project_status'] ?? null,
                'project_information' => $validated['project_information'] ?? null,
                'due_date'            => $assignedDate,
                'type'                => $projectType,
                'assigned_to'         => $estimator->id,
            ]);
        }

        $warning = $selected->count() < $assignCount
            ? "Warning: only {$selected->count()} of {$assignCount} estimators were available (others have off days during this period)."
            : null;

        // Notify each assigned estimator by email
        foreach ($selected as $index => $estimator) {
            if ($index > 0) {
                usleep(600000); // 600ms between sends — Resend allows max 2 req/sec
            }
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

    public function edit(Allocation $allocation)
    {
        $allocation->load([
            'estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'),
            'projects',
        ]);

        $allEstimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        $limit         = $allocation->job_type === 'MU' ? 3 : 2;

        // Build slot data: each slot has a letter, the current estimator, their pivot status, and linked project
        $slots = $allocation->estimators->values()->map(function ($estimator, $index) use ($allocation) {
            return [
                'letter'    => chr(65 + $index),
                'estimator' => $estimator,
                'status'    => $estimator->pivot->status,
                'project'   => $allocation->projects->firstWhere('assigned_to', $estimator->id),
            ];
        });

        return view('admin.allocation.edit', compact('allocation', 'slots', 'allEstimators', 'limit'));
    }

    public function update(Request $request, Allocation $allocation)
    {
        $allocation->load([
            'estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'),
            'projects',
        ]);

        $limit        = $allocation->job_type === 'MU' ? 3 : 2;
        $projectType  = $allocation->job_type === 'MU' ? 'MULTIUNIT' : 'NON MU';
        $slots        = $request->input('slots', []);   // [original_user_id => ['new_id' => ..., 'project_id' => ...]]
        $newEstimators = array_filter($request->input('new_estimators', []));

        // Validate final count
        $removals    = collect($slots)->filter(fn($s) => empty($s['new_id']))->count();
        $finalCount  = $allocation->estimators->count() - $removals + count($newEstimators);
        if ($finalCount > $limit) {
            return back()->with('error', "Cannot exceed {$limit} estimators for a {$allocation->job_type} job.");
        }

        // Validate no duplicates
        $keptIds = collect($slots)
            ->filter(fn($s) => !empty($s['new_id']))
            ->pluck('new_id')
            ->merge($newEstimators)
            ->map(fn($id) => (int)$id);
        if ($keptIds->unique()->count() !== $keptIds->count()) {
            return back()->with('error', 'Cannot assign the same estimator twice.');
        }

        $emailQueue = []; // collect emails to send with rate limiting
        $currentEstimators = $allocation->estimators->keyBy('id');

        // Process existing slots
        foreach ($slots as $originalId => $data) {
            $originalId = (int)$originalId;
            $newId      = !empty($data['new_id']) ? (int)$data['new_id'] : null;
            $projectId  = !empty($data['project_id']) ? (int)$data['project_id'] : null;
            $project    = $projectId ? Project::find($projectId) : null;

            if ($newId === null) {
                // Remove
                $allocation->estimators()->detach($originalId);
                if ($project) {
                    $hasActivity = $project->remarks()->exists()
                        || $project->proposals()->exists()
                        || $project->progress()->exists();
                    if ($hasActivity) {
                        $project->update(['assigned_to' => null]);
                    } else {
                        $project->delete();
                    }
                }
                $oldUser = User::find($originalId);
                if ($oldUser) {
                    $emailQueue[] = fn() => Mail::to($oldUser->email)->send(new JobRemovedMail($allocation, $oldUser));
                }
            } elseif ($newId !== $originalId) {
                // Swap
                $allocation->estimators()->detach($originalId);
                $allocation->estimators()->attach($newId, ['status' => 'open']);
                if ($project) {
                    $project->update(['assigned_to' => $newId]);
                }
                $oldUser = User::find($originalId);
                $newUser = User::find($newId);
                if ($oldUser) {
                    $emailQueue[] = fn() => Mail::to($oldUser->email)->send(new JobRemovedMail($allocation, $oldUser));
                }
                if ($newUser) {
                    $emailQueue[] = fn() => Mail::to($newUser->email)->send(new JobAssignedMail($allocation, $newUser));
                }
            }
            // else: unchanged — no action
        }

        // Add new estimators
        foreach ($newEstimators as $newId) {
            $newId = (int)$newId;

            // Find next letter (max used + 1, never reuse)
            $allocation->load('projects');
            $jobNumber       = $allocation->job_number;
            $usedLetterOrds  = $allocation->projects->map(function ($p) use ($jobNumber) {
                $pos    = strlen($jobNumber);
                $letter = strlen($p->name) > $pos ? $p->name[$pos] : null;
                return ($letter && ctype_upper($letter)) ? ord($letter) : null;
            })->filter();
            $nextOrd  = $usedLetterOrds->isEmpty() ? 65 : $usedLetterOrds->max() + 1;
            $newLetter = chr($nextOrd);

            // Derive base project name from first existing project
            $baseName = null;
            $firstProject = $allocation->projects->first();
            if ($firstProject) {
                $dotPos   = strpos($firstProject->name, '. ');
                $baseName = $dotPos !== false ? substr($firstProject->name, $dotPos + 2) : null;
            }
            $projectName = $jobNumber . $newLetter . ($baseName ? '. ' . $baseName : '');

            // Copy GC, status, project_information from existing project if available
            $refProject = $firstProject;
            Project::create([
                'allocation_id'       => $allocation->id,
                'name'                => $projectName,
                'gc'                  => $refProject?->gc,
                'status'              => $refProject?->status,
                'project_information' => $refProject?->project_information,
                'due_date'            => $allocation->assigned_date,
                'type'                => $projectType,
                'assigned_to'         => $newId,
            ]);

            $allocation->estimators()->attach($newId, ['status' => 'open']);

            $newUser = User::find($newId);
            if ($newUser) {
                $emailQueue[] = fn() => Mail::to($newUser->email)->send(new JobAssignedMail($allocation, $newUser));
            }
        }

        // Send emails with rate limiting
        foreach ($emailQueue as $index => $send) {
            if ($index > 0) {
                usleep(600000);
            }
            $send();
        }

        return redirect()->route('admin.allocation.index')
            ->with('success', "Allocation #{$allocation->job_number} updated.");
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
