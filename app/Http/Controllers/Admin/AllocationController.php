<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\JobAssignedMail;
use App\Mail\JobDueDateChangedMail;
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
        $sortDir = $request->get('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query = Allocation::with(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'), 'projects'])
            ->orderBy('assigned_date', $sortDir);

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
            'project_name'              => 'required|string|max:255',
            'gc'                        => 'nullable|string|max:255',
            'project_status'            => 'nullable|string|max:255',
            'project_information'       => 'nullable|string',
            'web_link'                  => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
            'other_gc_names'            => 'nullable|array',
            'other_gc_names.*'          => 'string|max:255',
            'other_gc_data'             => 'nullable|array',
            'other_gc_data.*.due_date'  => 'nullable|date',
            'other_gc_data.*.web_link'  => 'nullable|string|max:2048|regex:/^https?:\/\/.+/',
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

        // Build other_gc data — apply -2 days (skip Sunday) to each due date
        $otherGcData = [];
        foreach ($validated['other_gc_names'] ?? [] as $gcName) {
            $rawDate = $validated['other_gc_data'][$gcName]['due_date'] ?? null;
            if ($rawDate) {
                $gcAssignedDate = Carbon::parse($rawDate)->subDays(2);
                if ($gcAssignedDate->isSunday()) {
                    $gcAssignedDate->subDay();
                }
                $rawDate = $gcAssignedDate->toDateString();
            }
            $otherGcData[$gcName] = [
                'due_date' => $rawDate,
                'web_link' => $validated['other_gc_data'][$gcName]['web_link'] ?? null,
            ];
        }

        // Create one Project per assigned estimator (primary GC)
        // Plus one additional Project per other GC per estimator
        $projectType = $validated['job_type'] === 'MU' ? 'MULTIUNIT' : 'NON MU';
        foreach ($selected->values() as $index => $estimator) {
            $letter      = chr(65 + $index); // A, B, C
            $projectName = "{$validated['job_number']}{$letter}. {$validated['project_name']}";

            // Primary GC project
            Project::create([
                'allocation_id'       => $allocation->id,
                'name'                => $projectName,
                'gc'                  => $validated['gc'] ?? null,
                'status'              => $validated['project_status'] ?? null,
                'project_information' => $validated['project_information'] ?? null,
                'web_link'            => $validated['web_link'] ?? null,
                'other_gc'            => !empty($otherGcData) ? $otherGcData : null,
                'due_date'            => $assignedDate,
                'type'                => $projectType,
                'assigned_to'         => $estimator->id,
            ]);

            // One project per other GC, using that GC's due date and web link
            foreach ($otherGcData as $gcName => $gcInfo) {
                Project::create([
                    'allocation_id'       => $allocation->id,
                    'name'                => $projectName,
                    'gc'                  => $gcName,
                    'status'              => $validated['project_status'] ?? null,
                    'project_information' => $validated['project_information'] ?? null,
                    'web_link'            => $gcInfo['web_link'] ?? null,
                    'due_date'            => $gcInfo['due_date'] ?? null,
                    'type'                => $projectType,
                    'assigned_to'         => $estimator->id,
                ]);
            }
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
        // Delete all linked projects first
        $allocation->projects()->delete();

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

        // Build unique GC groups from projects: gc_name => current due_date
        $gcGroups = $allocation->projects
            ->groupBy(fn($p) => $p->gc ?? '')
            ->map(fn($projects) => $projects->first()->due_date);

        // All active GCs for the "Add Other GCs" dropdown
        $gcs = Gc::active()->ordered()->get();

        // GC names already used (primary + existing other GCs) — excluded from "add" dropdown
        $excludedGcNames = $allocation->projects->pluck('gc')->filter()->unique()->values()->toArray();

        return view('admin.allocation.edit', compact(
            'allocation', 'slots', 'allEstimators', 'limit', 'gcGroups', 'gcs', 'excludedGcNames'
        ));
    }

    public function update(Request $request, Allocation $allocation)
    {
        $allocation->load([
            'estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'),
            'projects',
        ]);

        $slots        = $request->input('slots', []);
        $newEstimators = array_filter($request->input('new_estimators', []));

        $emailQueue = []; // collect emails to send with rate limiting

        // --- Job type change ---
        $newJobType = $request->input('job_type');
        if ($newJobType && $newJobType !== $allocation->job_type) {

            if ($allocation->job_type === 'MU' && $newJobType === 'NON_MU') {
                // Auto-remove the last estimator (slot C)
                if ($allocation->estimators->count() > 2) {
                    $lastEstimator = $allocation->estimators->last();
                    $allocation->estimators()->detach($lastEstimator->id);
                    Project::where('allocation_id', $allocation->id)
                        ->where('assigned_to', $lastEstimator->id)
                        ->delete();
                    $emailQueue[] = fn() use ($lastEstimator, $allocation) => Mail::to($lastEstimator->email)->send(new JobRemovedMail($allocation, $lastEstimator));
                    $allocation->load(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'), 'projects']);
                }

            } elseif ($allocation->job_type === 'NON_MU' && $newJobType === 'MU') {
                // Auto-assign a third estimator using the same logic as store()
                $eligible = User::whereIn('role', ['estimator', 'head_estimator'])
                    ->where('MU', 'yes')
                    ->whereNotIn('id', $allocation->estimators->pluck('id'))
                    ->get();

                // Filter out estimators with off days overlapping the allocation window
                $eligible = $eligible->filter(function ($estimator) use ($allocation) {
                    return !\App\Models\EstimatorOffDay::where('user_id', $estimator->id)
                        ->where('start_date', '<=', $allocation->due_date)
                        ->where('end_date', '>=', $allocation->assigned_date)
                        ->exists();
                });

                // Calculate effective load for the allocation's month
                $targetMonth = $allocation->due_date->month;
                $targetYear  = $allocation->due_date->year;
                $usedLocations = $allocation->estimators->pluck('location')->filter()->unique()->values()->toArray();

                $eligible = $eligible->map(function ($estimator) use ($targetMonth, $targetYear) {
                    $load = DB::table('allocation_user')
                        ->join('allocations', 'allocation_user.allocation_id', '=', 'allocations.id')
                        ->where('allocation_user.user_id', $estimator->id)
                        ->whereMonth('allocations.due_date', $targetMonth)
                        ->whereYear('allocations.due_date', $targetYear)
                        ->where('allocation_user.status', 'open')
                        ->sum('allocations.days_required');
                    $estimator->effective_load = $load / ($estimator->weight ?? 1.0);
                    return $estimator;
                });

                $newEstimator = null;
                foreach ($eligible->sortBy('effective_load') as $candidate) {
                    $location = $candidate->location;
                    if ($location && in_array($location, $usedLocations)) continue;
                    $newEstimator = $candidate;
                    break;
                }

                if ($newEstimator) {
                    // Determine next letter
                    $allocation->load('projects');
                    $jobNumber      = $allocation->job_number;
                    $usedLetterOrds = $allocation->projects->map(function ($p) use ($jobNumber) {
                        $pos    = strlen($jobNumber);
                        $letter = strlen($p->name) > $pos ? $p->name[$pos] : null;
                        return ($letter && ctype_upper($letter)) ? ord($letter) : null;
                    })->filter();
                    $nextOrd   = $usedLetterOrds->isEmpty() ? 65 : $usedLetterOrds->max() + 1;
                    $newLetter = chr($nextOrd);

                    $firstProject = $allocation->projects->first();
                    $dotPos       = $firstProject ? strpos($firstProject->name, '. ') : false;
                    $baseName     = $dotPos !== false ? substr($firstProject->name, $dotPos + 2) : null;
                    $projectName  = $jobNumber . $newLetter . ($baseName ? '. ' . $baseName : '');

                    Project::create([
                        'allocation_id'       => $allocation->id,
                        'name'                => $projectName,
                        'gc'                  => $firstProject?->gc,
                        'status'              => $firstProject?->status,
                        'project_information' => $firstProject?->project_information,
                        'web_link'            => $firstProject?->web_link,
                        'due_date'            => $allocation->assigned_date,
                        'type'                => 'MULTIUNIT',
                        'assigned_to'         => $newEstimator->id,
                    ]);

                    $allocation->estimators()->attach($newEstimator->id, ['status' => 'open']);
                    $emailQueue[] = fn() use ($newEstimator, $allocation) => Mail::to($newEstimator->email)->send(new JobAssignedMail($allocation, $newEstimator));
                    $allocation->load(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc'), 'projects']);
                }
            }

            $newProjectType = $newJobType === 'MU' ? 'MULTIUNIT' : 'NON MU';
            $allocation->update(['job_type' => $newJobType]);
            Project::where('allocation_id', $allocation->id)->update(['type' => $newProjectType]);
            $allocation->refresh();
        }

        // --- Days required change ---
        $newDaysRequired = $request->input('days_required');
        if ($newDaysRequired !== null && (float) $newDaysRequired !== (float) $allocation->days_required) {
            $allocation->update(['days_required' => (float) $newDaysRequired]);
            $allocation->refresh();
        }

        // --- Project name / web link / project information ---
        $projectUpdates = [];

        if ($request->filled('project_name')) {
            // Rebuild each project's name keeping its individual prefix (e.g. "26077A. ")
            foreach ($allocation->projects as $project) {
                $dotPos = strpos($project->name, '. ');
                $prefix = $dotPos !== false ? substr($project->name, 0, $dotPos + 2) : '';
                $project->update(['name' => $prefix . trim($request->input('project_name'))]);
            }
            $allocation->load('projects');
        }

        if ($request->has('web_link')) {
            $projectUpdates['web_link'] = $request->input('web_link') ?: null;
        }

        if ($request->has('project_information')) {
            $projectUpdates['project_information'] = $request->input('project_information') ?: null;
        }

        if (!empty($projectUpdates)) {
            Project::where('allocation_id', $allocation->id)->update($projectUpdates);
        }

        // Derive limit and projectType from (possibly updated) job_type
        $limit        = $allocation->job_type === 'MU' ? 3 : 2;
        $projectType  = $allocation->job_type === 'MU' ? 'MULTIUNIT' : 'NON MU';

        // --- Due date changes ---
        $dueDateChanged = false;

        // Main due date
        if ($request->filled('due_date')) {
            $newDueDate      = Carbon::parse($request->input('due_date'));
            $newAssignedDate = $newDueDate->copy()->subDays(2);
            if ($newAssignedDate->isSunday()) {
                $newAssignedDate->subDay();
            }

            if ($newDueDate->toDateString() !== $allocation->due_date->toDateString()) {
                $dueDateChanged = true;
                $allocation->update([
                    'due_date'      => $newDueDate,
                    'assigned_date' => $newAssignedDate,
                ]);
                $allocation->refresh();

                // Update all projects whose gc matches the primary GC (due_date was assigned_date)
                // We identify main GC projects as those with other_gc set OR as the first gc group
                // Simplest: update all projects for this allocation whose due_date was the old assigned_date
                Project::where('allocation_id', $allocation->id)
                    ->whereNotIn('gc', array_keys($request->input('gc_due_dates', [])))
                    ->update(['due_date' => $newAssignedDate]);
            }
        }

        // Per other-GC due dates (admin enters estimator due date directly — stored as-is)
        foreach ($request->input('gc_due_dates', []) as $gcName => $rawDate) {
            if (empty($rawDate)) continue;
            $gcAssignedDate = Carbon::parse($rawDate)->subDays(2);
            if ($gcAssignedDate->isSunday()) {
                $gcAssignedDate->subDay();
            }
            // Update the separate other-GC projects
            Project::where('allocation_id', $allocation->id)
                ->where('gc', $gcName)
                ->update(['due_date' => $gcAssignedDate]);

            // Also update the other_gc JSON on main projects that reference this GC
            Project::where('allocation_id', $allocation->id)
                ->where('gc', '!=', $gcName)
                ->get()
                ->each(function ($project) use ($gcName, $gcAssignedDate) {
                    $otherGc = $project->other_gc;
                    if (is_array($otherGc) && isset($otherGc[$gcName])) {
                        $otherGc[$gcName]['due_date'] = $gcAssignedDate->toDateString();
                        $project->other_gc = $otherGc;
                        $project->save();
                    }
                });
        }

        // Add new other GCs
        $newOtherGcNames = array_filter($request->input('other_gc_names', []));
        // All other-GC names (existing + new) — used to identify main projects
        $allOtherGcNames = array_merge(
            array_keys($request->input('gc_due_dates', [])),
            $newOtherGcNames
        );
        foreach ($newOtherGcNames as $gcName) {
            // Skip if this GC already has projects for this allocation
            if ($allocation->projects->where('gc', $gcName)->isNotEmpty()) {
                continue;
            }

            $rawDate = $request->input("other_gc_data.{$gcName}.due_date");
            $webLink = $request->input("other_gc_data.{$gcName}.web_link") ?: null;

            $gcAssignedDate = null;
            if ($rawDate) {
                $gcAssignedDate = Carbon::parse($rawDate)->subDays(2);
                if ($gcAssignedDate->isSunday()) {
                    $gcAssignedDate->subDay();
                }
            }

            // Create one project per current estimator with status RECEIVED
            foreach ($allocation->estimators as $estimator) {
                $mainProject = $allocation->projects->firstWhere('assigned_to', $estimator->id);
                $projectName = $mainProject ? $mainProject->name : $allocation->job_number;

                Project::create([
                    'allocation_id' => $allocation->id,
                    'name'          => $projectName,
                    'gc'            => $gcName,
                    'status'        => 'RECEIVED',
                    'due_date'      => $gcAssignedDate,
                    'web_link'      => $webLink,
                    'type'          => $projectType,
                    'assigned_to'   => $estimator->id,
                ]);
            }

            // Update other_gc JSON only on main projects (not other-GC projects)
            $allocation->projects
                ->filter(fn($p) => !in_array($p->gc, $allOtherGcNames))
                ->each(function ($project) use ($gcName, $gcAssignedDate, $webLink) {
                    $otherGc = $project->other_gc ?? [];
                    $otherGc[$gcName] = [
                        'due_date' => $gcAssignedDate?->toDateString(),
                        'web_link' => $webLink,
                    ];
                    $project->other_gc = $otherGc;
                    $project->save();
                });

            // Refresh projects so subsequent iterations see the new ones
            $allocation->load('projects');
        }

        // Notify open estimators if main due date changed
        if ($dueDateChanged) {
            $allocation->load('estimators');
            foreach ($allocation->estimators as $estimator) {
                if ($estimator->pivot->status === 'open') {
                    $emailQueue[] = fn() => Mail::to($estimator->email)->send(new JobDueDateChangedMail($allocation, $estimator));
                }
            }
        }

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
                Project::where('allocation_id', $allocation->id)
                    ->where('assigned_to', $originalId)
                    ->delete();
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

        // Load all allocations for the month keyed by assigned_date (what the calendar displays)
        $allocations = Allocation::with(['estimators' => fn($q) => $q->orderBy('allocation_user.id', 'asc')])
            ->whereMonth('assigned_date', $month)
            ->whereYear('assigned_date', $year)
            ->get();

        // Index: $jobsByDateAndUser['Y-m-d'][userId] = [allocations]
        // Also build submitted map: [estimatorId => [allocationId, ...]]
        $jobsByDateAndUser = [];
        $submittedMap = [];
        foreach ($allocations as $allocation) {
            $dateKey = $allocation->assigned_date->format('Y-m-d');
            foreach ($allocation->estimators as $estimator) {
                $jobsByDateAndUser[$dateKey][$estimator->id][] = $allocation;
                if ($estimator->pivot->status === 'submitted') {
                    $submittedMap[$estimator->id][] = $allocation->id;
                }
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
            'estimators', 'days', 'jobsByDateAndUser', 'submittedMap', 'totals', 'month', 'year', 'currentDate',
            'muLabels', 'nonMuLabels'
        ));
    }
}
