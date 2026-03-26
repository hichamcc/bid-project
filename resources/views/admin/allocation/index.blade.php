@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Workload Distribution</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign jobs to estimators automatically based on current workload.</p>
                </div>
                <a href="{{ route('admin.allocation.monthly') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Monthly View
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Allocation Form -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add New Job</h3>
                <form method="POST" action="{{ route('admin.allocation.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                        <!-- Job Number -->
                        <div>
                            <label for="job_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Job Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="job_number"
                                   name="job_number"
                                   value="{{ old('job_number') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('job_number') border-red-500 @enderror">
                            @error('job_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   id="due_date"
                                   name="due_date"
                                   value="{{ old('due_date') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Days Required -->
                        <div>
                            <label for="days_required" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Days Required <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   id="days_required"
                                   name="days_required"
                                   value="{{ old('days_required') }}"
                                   step="0.5"
                                   min="0.5"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('days_required') border-red-500 @enderror">
                            @error('days_required')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Job Type -->
                        <div>
                            <label for="job_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Job Type <span class="text-red-500">*</span>
                            </label>
                            <select id="job_type"
                                    name="job_type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('job_type') border-red-500 @enderror">
                                <option value="">-- Select --</option>
                                <option value="MU" {{ old('job_type') === 'MU' ? 'selected' : '' }}>MU (assigned to 3 estimators)</option>
                                <option value="NON_MU" {{ old('job_type') === 'NON_MU' ? 'selected' : '' }}>NON MU (assigned to 2 estimators)</option>
                            </select>
                            @error('job_type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Other Information for Project (collapsible) -->
                    <div x-data="{ open: true }" class="mt-4">
                        <button type="button"
                                @click="open = !open"
                                class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline focus:outline-none">
                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            Other Information for Project
                        </button>

                        <div x-show="open" x-cloak x-transition class="mt-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                                <!-- Project Name -->
                                <div class="lg:col-span-2">
                                    <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Project Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="project_name"
                                           name="project_name"
                                           value="{{ old('project_name') }}"
                                           placeholder="e.g. DOWNTOWN TOWER"
                                           oninput="this.value = this.value.toUpperCase()"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_name') border-red-500 @enderror">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Final name will be: <em>5454A. Project Name</em></p>
                                    @error('project_name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- GC -->
                                <div>
                                    <label for="gc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        GC
                                    </label>
                                    <select id="gc"
                                            name="gc"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- None --</option>
                                        @foreach($gcs as $gcItem)
                                            <option value="{{ $gcItem->name }}" {{ old('gc') === $gcItem->name ? 'selected' : '' }}>
                                                {{ $gcItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status
                                    </label>
                                    <select id="project_status"
                                            name="project_status"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- None --</option>
                                        @foreach($statuses as $statusItem)
                                            <option value="{{ $statusItem->name }}" {{ old('project_status') === $statusItem->name ? 'selected' : '' }}>
                                                {{ $statusItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Web Link -->
                                <div class="lg:col-span-2">
                                    <label for="web_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Web Link
                                    </label>
                                    <input type="url"
                                           id="web_link"
                                           name="web_link"
                                           value="{{ old('web_link') }}"
                                           placeholder="https://example.com"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Other GCs -->
                                <div class="lg:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Other GCs
                                    </label>
                                    <select id="alloc_other_gc"
                                            name="other_gc_select[]"
                                            multiple
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @foreach($gcs as $gcItem)
                                            <option value="{{ $gcItem->name }}"
                                                {{ is_array(old('other_gc_select')) && in_array($gcItem->name, old('other_gc_select')) ? 'selected' : '' }}>
                                                {{ $gcItem->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div id="alloc_other_gc_details" class="mt-3 space-y-3"></div>

                                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                                    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
                                    <script>
                                        $(document).ready(function () {
                                            $('#alloc_other_gc').select2({ placeholder: 'Select Other GCs', allowClear: true });
                                            $('#alloc_other_gc').on('change', updateAllocOtherGcFields);

                                            function updateAllocOtherGcFields() {
                                                var selected = $('#alloc_other_gc').val() || [];
                                                var container = $('#alloc_other_gc_details');
                                                container.empty();
                                                selected.forEach(function (gcName) {
                                                    if (!gcName) return;
                                                    container.append(
                                                        '<div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">' +
                                                        '<p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">' + gcName + '</p>' +
                                                        '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                                                        '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Due Date</label>' +
                                                        '<input type="date" name="other_gc_data[' + gcName + '][due_date]" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>' +
                                                        '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Web Link</label>' +
                                                        '<input type="url" name="other_gc_data[' + gcName + '][web_link]" placeholder="https://example.com" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>' +
                                                        '</div>' +
                                                        '<input type="hidden" name="other_gc_names[]" value="' + gcName + '">' +
                                                        '</div>'
                                                    );
                                                });
                                            }

                                            @if(old('other_gc_select'))
                                                updateAllocOtherGcFields();
                                            @endif
                                        });
                                    </script>
                                </div>

                                <!-- Project Information -->
                                <div class="lg:col-span-4">
                                    <label for="project_information" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Project Information
                                    </label>
                                    <textarea id="project_information"
                                              name="project_information"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('project_information') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                            Assign Job
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Allocations Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allocation History</h3>
                <form method="GET" action="{{ route('admin.allocation.index') }}" class="flex flex-wrap gap-3 items-end">

                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Job Number</label>
                        <input type="text" name="job_number" value="{{ request('job_number') }}"
                               placeholder="Search..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                        <select name="job_type"
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="MU" {{ request('job_type') === 'MU' ? 'selected' : '' }}>MU</option>
                            <option value="NON_MU" {{ request('job_type') === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Estimator</label>
                        <select name="estimator_id"
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Estimators</option>
                            @foreach($estimators as $estimator)
                                <option value="{{ $estimator->id }}" {{ request('estimator_id') == $estimator->id ? 'selected' : '' }}>
                                    {{ $estimator->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Due Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Due Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sort By Date</label>
                        <select name="sort"
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="asc" {{ request('sort', 'asc') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>Newest First</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            Filter
                        </button>
                        @if(request()->hasAny(['job_number', 'job_type', 'estimator_id', 'date_from', 'date_to', 'sort']))
                            <a href="{{ route('admin.allocation.index') }}"
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">
                                Clear
                            </a>
                        @endif
                    </div>

                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Job Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Job Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assigned Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">GCs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assigned To / Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($allocations as $allocation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $allocation->job_number }}
                                </td>
                                @php
                                    $firstProject = $allocation->projects->first();
                                    $dotPos = $firstProject ? strpos($firstProject->name, '. ') : false;
                                    $jobName = $dotPos !== false ? substr($firstProject->name, $dotPos + 2) : null;
                                @endphp
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $jobName ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $allocation->job_type === 'MU' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ $allocation->job_type === 'NON_MU' ? 'NON MU' : 'MU' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->days_required }}d
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->due_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->assigned_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    @php $gcCount = $allocation->projects->pluck('gc')->filter()->unique()->count(); @endphp
                                    @if($gcCount > 0)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $gcCount }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $jobEstimatorIds = $allocation->estimators->pluck('id');
                                        $allEstimators   = \App\Models\User::whereIn('role', ['estimator','head_estimator'])->orderBy('name')->get();
                                        $orderedForJob   = $allEstimators->filter(fn($e) => $jobEstimatorIds->contains($e->id))->values();
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        @forelse($allocation->estimators as $estimator)
                                            @php
                                                $pos    = $orderedForJob->search(fn($e) => $e->id === $estimator->id);
                                                $letter = chr(65 + $pos);
                                                $status = $estimator->pivot->status;
                                                $project = $allocation->projects->firstWhere('assigned_to', $estimator->id);
                                            @endphp
                                            <div class="flex items-center gap-1.5">
                                                @if($project)
                                                    <a href="{{ route('admin.projects.show', $project) }}"
                                                       class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                                        {{ $estimator->name }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-800 dark:text-gray-200 text-xs">{{ $estimator->name }}</span>
                                                @endif
                                                <span class="px-1.5 py-0.5 text-xs font-semibold rounded-full
                                                    {{ $status === 'submitted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </div>
                                        @empty
                                            <span class="text-gray-400 italic text-xs">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.allocation.edit', $allocation) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.allocation.destroy', $allocation) }}"
                                              onsubmit="return confirm('Delete this allocation?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No allocations yet. Use the form above to assign a job.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allocations->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $allocations->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
