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
                            @if($proposal->project->other_gc && count($proposal->project->other_gc) > 0)
                                <div class="space-y-2 mt-2">
                                    @foreach($proposal->project->other_gc as $gcName => $gcData)
                                        <div class="bg-gray-50 p-2 rounded text-sm">
                                            <div class="font-medium text-gray-900">{{ $gcName }}</div>
                                            @if(is_array($gcData))
                                                @if(isset($gcData['due_date']) && $gcData['due_date'])
                                                    <div class="text-gray-600 text-xs mt-1">Due: {{ \Carbon\Carbon::parse($gcData['due_date'])->format('M d, Y') }}</div>
                                                @endif
                                                @if(isset($gcData['web_link']) && $gcData['web_link'])
                                                    <div class="mt-1">
                                                        <a href="{{ $gcData['web_link'] }}" 
                                                           target="_blank" 
                                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 text-xs">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            View Link
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-900">-</p>
                            @endif
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
                            <label class="block text-sm font-medium text-gray-600">Job Number</label>
                            <p class="text-sm text-gray-900">{{ $proposal->job_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Submission Date</label>
                            <p class="text-sm text-gray-900">
                                {{ $proposal->submission_date?->format('M d, Y') ?? 'Not set' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Responded</label>
                            @if($proposal->responded === 'yes')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Yes
                                </span>
                            @elseif($proposal->responded === 'no')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    No
                                </span>
                            @else
                                <span class="text-gray-400">Not set</span>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Result GC</label>
                            @if($proposal->result_gc === 'win')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Win
                                </span>
                            @elseif($proposal->result_gc === 'loss')
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
                            <label class="block text-sm font-medium text-gray-600">Result ART</label>
                            @if($proposal->result_art === 'win')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Win
                                </span>
                            @elseif($proposal->result_art === 'loss')
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
                        <label class="block text-sm font-medium text-blue-600 mb-1">ARTELYE Price</label>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ $proposal->price_original ? '$' . number_format($proposal->price_original, 2) : 'Not set' }}
                        </p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-green-600 mb-1">ARTELYE VE Price</label>
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

        <!-- Follow-up Information -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Follow-up Tracking</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- First Follow-up -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-900 mb-3">First Follow-up</h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Date</label>
                                <p class="text-sm text-gray-900">
                                    {{ $proposal->first_follow_up_date ? $proposal->first_follow_up_date->format('M d, Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Response</label>
                                @if($proposal->first_follow_up_respond === 'yes')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Yes
                                    </span>
                                @elseif($proposal->first_follow_up_respond === 'no')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        No
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            @if($proposal->first_follow_up_attachment)
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Email Attachment</label>
                                    <a href="{{ Storage::url($proposal->first_follow_up_attachment) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                        1st Follow-up Email
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Second Follow-up -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Second Follow-up</h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Date</label>
                                <p class="text-sm text-gray-900">
                                    {{ $proposal->second_follow_up_date ? $proposal->second_follow_up_date->format('M d, Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Response</label>
                                @if($proposal->second_follow_up_respond === 'yes')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Yes
                                    </span>
                                @elseif($proposal->second_follow_up_respond === 'no')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        No
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            @if($proposal->second_follow_up_attachment)
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Email Attachment</label>
                                    <a href="{{ Storage::url($proposal->second_follow_up_attachment) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                        2nd Follow-up Email
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Third Follow-up -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Third Follow-up</h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Date</label>
                                <p class="text-sm text-gray-900">
                                    {{ $proposal->third_follow_up_date ? $proposal->third_follow_up_date->format('M d, Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Response</label>
                                @if($proposal->third_follow_up_respond === 'yes')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Yes
                                    </span>
                                @elseif($proposal->third_follow_up_respond === 'no')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        No
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            @if($proposal->third_follow_up_attachment)
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Email Attachment</label>
                                    <a href="{{ Storage::url($proposal->third_follow_up_attachment) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                        3rd Follow-up Email
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection