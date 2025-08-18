@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Progress Management</h2>
                    <a href="{{ route('admin.progress.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Progress Entry
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.progress.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search by project name, GC, or job number..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                            <select id="project_id" 
                                    name="project_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="estimator_id" class="block text-sm font-medium text-gray-700 mb-1">Estimator</label>
                            <select id="estimator_id" 
                                    name="estimator_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Estimators</option>
                                @foreach($estimators as $estimator)
                                    <option value="{{ $estimator->id }}" {{ request('estimator_id') == $estimator->id ? 'selected' : '' }}>
                                        {{ $estimator->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.progress.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        @if(request()->hasAny(['search', 'project_id', 'estimator_id']))
            <div class="bg-blue-50 border border-blue-200 px-4 py-3 rounded mb-6">
                <p class="text-blue-800">
                    Showing {{ $progress->total() }} filtered result(s)
                    @if(request('search'))
                        for search: "<strong>{{ request('search') }}</strong>"
                    @endif
                </p>
            </div>
        @endif

        <!-- Progress Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estimator
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'assigned_date', 'sort_direction' => request('sort_by') == 'assigned_date' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Assigned Date
                                    @if(request('sort_by') == 'assigned_date')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'submission_date', 'sort_direction' => request('sort_by') == 'submission_date' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Submission Date
                                    @if(request('sort_by') == 'submission_date')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Measurements
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_hours', 'sort_direction' => request('sort_by') == 'total_hours' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Hours
                                    @if(request('sort_by') == 'total_hours')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performance Point
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($progress as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 ">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $entry->project->name }}
                                    </div>
                                    @if($entry->job_number)
                                        <div class="text-xs text-gray-500">
                                            Job #{{ $entry->job_number }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->project->assignedTo?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->assigned_date?->format('M d, Y') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($entry->submission_date)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                                            {{ $entry->submission_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="space-y-1">
                                        @if($entry->total_sqft)
                                            <div class="text-xs">{{ number_format($entry->total_sqft, 0) }} sq ft</div>
                                        @endif
                                        @if($entry->total_lnft)
                                            <div class="text-xs">{{ number_format($entry->total_lnft, 0) }} ln ft</div>
                                        @endif
                                        @if($entry->total_sinks)
                                            <div class="text-xs">{{ $entry->total_sinks }} sinks</div>
                                        @endif
                                        @if($entry->total_slabs)
                                            <div class="text-xs">{{ $entry->total_slabs }} slabs</div>
                                        @endif
                                        @if(!$entry->total_sqft && !$entry->total_lnft && !$entry->total_sinks && !$entry->total_slabs)
                                            <span class="text-gray-400 text-xs">No measurements</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->total_hours ? number_format($entry->total_hours, 1) . ' hrs' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $totalSlabs = $entry->total_slabs ?: 0;
                                        $sqft = $entry->total_sqft ?: 0;
                                        $lnft = $entry->total_lnft ?: 0;
                                        $sinks = $entry->total_sinks ?: 0;
                                        $denominator = $sqft + $lnft + $sinks;
                                        $performancePoint = $denominator > 0 ? $totalSlabs / $denominator : 0;
                                    @endphp
                                    {{ $performancePoint > 0 ? number_format($performancePoint, 4)*1000 : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.progress.show', $entry) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('admin.progress.edit', $entry) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form method="POST" 
                                              action="{{ route('admin.progress.destroy', $entry) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this progress entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    No progress entries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($progress->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $progress->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection