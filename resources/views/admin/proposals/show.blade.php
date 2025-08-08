@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Proposal Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.proposals.edit', $proposal) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit
                        </a>
                        <a href="{{ route('admin.proposals.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Proposals
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proposal Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Project Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Project Name</label>
                            <p class="text-lg text-gray-900 font-medium">{{ $proposal->project->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Primary GC</label>
                            <p class="text-sm text-gray-900">{{ $proposal->project->gc ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Other GCs</label>
                            <p class="text-sm text-gray-900">
                                {{ $proposal->project->other_gc && count($proposal->project->other_gc) > 0 
                                   ? implode(', ', $proposal->project->other_gc) 
                                   : '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Assigned Estimator</label>
                            <p class="text-sm text-gray-900">{{ $proposal->project->assignedTo?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Project Status</label>
                            @if($proposal->project->statusRecord)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                      style="background-color: {{ $proposal->project->statusRecord->color }}20; color: {{ $proposal->project->statusRecord->color }};">
                                    {{ $proposal->project->status }}
                                </span>
                            @else
                                <span class="text-gray-400">No Status</span>
                            @endif
                        </div>

                        @if($proposal->project->due_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Project Due Date</label>
                                <p class="text-sm text-gray-900">{{ $proposal->project->due_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Proposal Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Proposal Details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Submission Date</label>
                            <p class="text-sm text-gray-900">
                                {{ $proposal->submission_date?->format('M d, Y') ?? 'Not set' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Result</label>
                            @if($proposal->result === 'win')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Win
                                </span>
                            @elseif($proposal->result === 'loss')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Loss
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Created</label>
                            <p class="text-sm text-gray-900">{{ $proposal->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $proposal->updated_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Information -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Price Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-blue-600 mb-1">Price Original</label>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ $proposal->price_original ? '$' . number_format($proposal->price_original, 2) : 'Not set' }}
                        </p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-green-600 mb-1">Price VE</label>
                        <p class="text-2xl font-bold text-green-900">
                            {{ $proposal->price_ve ? '$' . number_format($proposal->price_ve, 2) : 'Not set' }}
                        </p>
                     
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-purple-600 mb-1">GC Price</label>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ $proposal->gc_price ? '$' . number_format($proposal->gc_price, 2) : 'Not set' }}
                        </p>
                    </div>
                </div>

             
            </div>
        </div>
    </div>
</div>
@endsection