@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Project Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.projects.edit', $project) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Project
                        </a>
                        <a href="{{ route('admin.projects.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Project Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Project Name
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->name }}</p>
                        </div>

                        @if($project->gc)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                General Contractor
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->gc }}</p>
                        </div>
                        @endif

                        @if($project->rfi)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                RFI
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->rfi }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </label>
                            <div class="mt-1">
                                @if($project->statusRecord)
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                        {{ $project->status }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No Status
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </label>
                            <div class="mt-1">
                                @if($project->typeRecord)
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $project->getTypeColor() }}-100 text-{{ $project->getTypeColor() }}-800">
                                        {{ $project->type }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No Type
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($project->assignedTo)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Assigned To
                            </label>
                            <div class="mt-1 flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium mr-3">
                                    {{ $project->assignedTo->initials() }}
                                </div>
                                <div>
                                    <p class="text-lg text-gray-900">{{ $project->assignedTo->name }}</p>
                                    <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $project->assignedTo->role)) }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($project->web_link)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Web Link
                            </label>
                            <div class="mt-1">
                                <a href="{{ $project->web_link }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    {{ $project->web_link }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dates and Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                    
                    <div class="space-y-4">
                        @if($project->assigned_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Assigned Date
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->assigned_date->format('M d, Y') }}</p>
                        </div>
                        @endif

                        @if($project->due_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Due Date
                            </label>
                            <div class="mt-1">
                                <p class="text-lg {{ $project->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $project->due_date->format('M d, Y') }}
                                </p>
                                @if($project->isOverdue())
                                    <p class="text-red-500 text-sm">Overdue by {{ abs($project->daysUntilDue()) }} days</p>
                                @elseif($project->daysUntilDue() !== null && $project->daysUntilDue() >= 0)
                                    <p class="text-gray-600 text-sm">
                                        @if($project->daysUntilDue() <= 7)
                                            <span class="text-yellow-600">Due in {{ $project->daysUntilDue() }} days</span>
                                        @else
                                            Due in {{ $project->daysUntilDue() }} days
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Last Updated
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $project->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scope and Project Information -->
        @if($project->scope || $project->project_information)
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($project->scope)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Scope</h3>
                    <div class="text-gray-700 whitespace-pre-wrap">{{ $project->scope }}</div>
                </div>
            </div>
            @endif

            @if($project->project_information)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                    <div class="text-gray-700 whitespace-pre-wrap">{{ $project->project_information }}</div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Remarks Section -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Remarks ({{ $project->remarks->count() }})</h3>
                </div>

                <!-- Add Remark Form -->
                <form method="POST" action="{{ route('admin.projects.remarks.store', $project) }}" class="mb-6">
                    @csrf
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <textarea name="remark" 
                                      rows="3" 
                                      placeholder="Add a remark..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('remark') border-red-500 @enderror"></textarea>
                            @error('remark')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex-shrink-0">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded h-full">
                                Add Remark
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Remarks List -->
                @forelse($project->remarks as $remark)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                    {{ $remark->user->initials() }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-medium text-gray-900">{{ $remark->user->name }}</span>
                                    <span class="text-gray-500 text-sm">{{ $remark->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                                <div class="text-gray-700 whitespace-pre-wrap">{{ $remark->remark }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No remarks yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.projects.edit', $project) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Project
                    </a>
                    
                    <form method="POST" 
                          action="{{ route('admin.projects.destroy', $project) }}" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete Project
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection