@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Workload Report</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">View current workload for all estimators (jobs with today or future due dates)</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800  shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.workload.index') }}" class="space-y-4" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                        <!-- Exclude Statuses Multi-select with Checkboxes -->
                        <div x-data="{
                            open: false,
                            selected: {{ json_encode($excludedStatuses) }},
                            toggle(value) {
                                if (this.selected.includes(value)) {
                                    this.selected = this.selected.filter(item => item !== value);
                                } else {
                                    this.selected.push(value);
                                }
                            },
                            isSelected(value) {
                                return this.selected.includes(value);
                            },
                            get selectedCount() {
                                return this.selected.length;
                            }
                        }" class="relative">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Exclude Statuses (Optional)
                            </label>

                            <!-- Selected Values Display -->
                            <button type="button"
                                    @click="open = !open"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-left flex items-center justify-between">
                                <span x-text="selectedCount > 0 ? selectedCount + ' status(es) excluded' : 'Select statuses to exclude'"
                                      class="text-sm"
                                      :class="selectedCount > 0 ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'">
                                </span>
                                <svg class="w-5 h-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown with Checkboxes -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-100 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
                                 style="display: none;">
                                <div class="py-1">
                                    @foreach($allStatuses as $status)
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                            <input type="checkbox"
                                                   name="excluded_statuses[]"
                                                   value="{{ $status->name }}"
                                                   @change="toggle('{{ $status->name }}')"
                                                   :checked="isSelected('{{ $status->name }}')"
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:focus:ring-blue-600">
                                            <span class="ml-2 text-sm flex items-center gap-2">
                                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $status->color }};"></span>
                                                <span class="text-gray-900 dark:text-gray-100">{{ $status->name }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click to select multiple statuses</p>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors">
                                Apply Filter
                            </button>
                            <a href="{{ route('admin.workload.index') }}"
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-colors">
                                Clear
                            </a>
                        </div>
                    </div>

                    @if(!empty($excludedStatuses))
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Excluding statuses:</strong>
                                @foreach($excludedStatuses as $excludedStatus)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mr-2"
                                          style="background-color: {{ $allStatuses->where('name', $excludedStatus)->first()?->color }}20; color: {{ $allStatuses->where('name', $excludedStatus)->first()?->color }};">
                                        {{ $excludedStatus }}
                                    </span>
                                @endforeach
                            </p>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Estimator Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($workloadData as $data)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Estimator Name -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $data['estimator']->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $data['estimator']->role)) }}
                            </p>
                        </div>

                        <!-- Total Jobs -->
                        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Jobs</div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $data['total_jobs'] }}
                            </div>
                        </div>

                        <!-- Status Breakdown -->
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status Breakdown</h4>
                            @if(!empty($data['status_counts']))
                                <div class="space-y-2">
                                    @foreach($data['status_counts'] as $status => $count)
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                {{ $status ?: 'No Status' }}
                                            </span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                {{ $count }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">No jobs</p>
                            @endif
                        </div>

                        <!-- Type Breakdown -->
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Type Breakdown</h4>
                            @if(!empty($data['type_counts']))
                                <div class="space-y-2">
                                    @foreach($data['type_counts'] as $type => $count)
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                {{ $type }}
                                            </span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                {{ $count }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">No jobs</p>
                            @endif
                        </div>

                        <!-- View Jobs Button -->
                        @if($data['total_jobs'] > 0)
                            <button type="button"
                                    x-data
                                    x-on:click="$dispatch('modal:open', 'jobs-modal-{{ $data['estimator']->id }}')"
                                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors">
                                View Jobs List
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Jobs Modal for this estimator -->
                <x-modal id="jobs-modal-{{ $data['estimator']->id }}" x-data class="!max-w-7xl w-full p-6 rounded-lg bg-white dark:bg-gray-800 backdrop:bg-gray-900/50" style="max-width: 90vw !important;">
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Jobs for {{ $data['estimator']->name }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Total: {{ $data['total_jobs'] }} job(s)
                        </p>
                    </div>

                    <div class="overflow-x-auto max-h-[600px]">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Job Name</th>
                                    <th scope="col" class="px-6 py-3">Due Date</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['projects'] as $project)
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $project->name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                            @if($project->due_date)
                                                {{ $project->due_date->format('M d, Y') }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500 italic">No due date</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($project->statusRecord)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                      style="background-color: {{ $project->statusRecord->color }}20; color: {{ $project->statusRecord->color }};">
                                                    {{ $project->status }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                    {{ $project->status ?: 'No Status' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($project->typeRecord)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                      style="background-color: {{ $project->typeRecord->color }}20; color: {{ $project->typeRecord->color }};">
                                                    {{ $project->type }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                    {{ $project->type ?: 'Regular' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button"
                                onclick="document.getElementById('jobs-modal-{{ $data['estimator']->id }}').close()"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-colors">
                            Close
                        </button>
                    </div>
                </x-modal>
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-gray-600 dark:text-gray-400">No estimators found.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
