@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Progress Entry Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.progress.edit', $progress) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit
                        </a>
                        <a href="{{ route('admin.progress.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Project Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Project Name</label>
                            <p class="text-lg text-gray-900 font-medium">{{ $progress->project->name }}</p>
                        </div>

                        @if($progress->job_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Job Number</label>
                                <p class="text-sm text-gray-900">{{ $progress->job_number }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Primary GC</label>
                            <p class="text-sm text-gray-900">{{ $progress->project->gc ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Other GCs</label>
                            <p class="text-sm text-gray-900">
                                {{ $progress->project->other_gc && count($progress->project->other_gc) > 0 
                                   ? implode(', ', $progress->project->other_gc) 
                                   : '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Assigned Estimator</label>
                            <p class="text-sm text-gray-900">{{ $progress->project->assignedTo?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Project Status</label>
                            @if($progress->project->statusRecord)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                      style="background-color: {{ $progress->project->statusRecord->color }}20; color: {{ $progress->project->statusRecord->color }};">
                                    {{ $progress->project->status }}
                                </span>
                            @else
                                <span class="text-gray-400">No Status</span>
                            @endif
                        </div>

                        @if($progress->project->due_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Project Due Date</label>
                                <p class="text-sm text-gray-900">{{ $progress->project->due_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Progress Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Assigned Date</label>
                            <p class="text-sm text-gray-900">
                                {{ $progress->assigned_date?->format('M d, Y') ?? 'Not assigned' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Submission Date</label>
                            @if($progress->submission_date)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $progress->submission_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Not submitted
                                </span>
                            @endif
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-600">Created</label>
                            <p class="text-sm text-gray-900">{{ $progress->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $progress->updated_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Measurements Information -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Measurements</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-blue-600 mb-1">Total Square Feet</label>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ $progress->total_sqft ? number_format($progress->total_sqft, 2) . ' sq ft' : 'Not set' }}
                        </p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-green-600 mb-1">Total Linear Feet</label>
                        <p class="text-2xl font-bold text-green-900">
                            {{ $progress->total_lnft ? number_format($progress->total_lnft, 2) . ' ln ft' : 'Not set' }}
                        </p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-purple-600 mb-1">Total Sinks</label>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ $progress->total_sinks ?? 'Not set' }}
                        </p>
                    </div>

                    <div class="bg-orange-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-orange-600 mb-1">Total Slabs</label>
                        <p class="text-2xl font-bold text-orange-900">
                            {{ $progress->total_slabs ?? 'Not set' }}
                        </p>
                    </div>
                </div>

                <!-- Total Hours -->
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Total Hours</label>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $progress->total_hours ? number_format($progress->total_hours, 2) . ' hrs' : 'Not set' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Performance Point -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Point</h3>
                
                <div class="bg-indigo-50 p-6 rounded-lg">
                    @php
                        $totalSlabs = $progress->total_slabs ?: 0;
                        $sqft = $progress->total_sqft ?: 0;
                        $lnft = $progress->total_lnft ?: 0;
                        $sinks = $progress->total_sinks ?: 0;
                        $denominator = $sqft + $lnft + $sinks;
                        $performancePoint = $denominator > 0 ? $totalSlabs / $denominator : 0;
                    @endphp
                    
                    <div class="text-center">
                        <label class="block text-sm font-medium text-indigo-600 mb-2">Performance Point</label>
                        <p class="text-4xl font-bold text-indigo-900">
                            {{ $performancePoint > 0 ? number_format($performancePoint, 4) : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection