@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Status Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.statuses.edit', $status) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Status
                        </a>
                        <a href="{{ route('admin.statuses.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Statuses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Status Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status Name
                            </label>
                            <div class="mt-1 flex items-center space-x-3">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $status->color }}"></div>
                                <p class="text-lg text-gray-900">{{ $status->name }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Color
                            </label>
                            <div class="mt-1 flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $status->color }}"></div>
                                <span class="text-lg text-gray-900 font-mono">{{ $status->color }}</span>
                            </div>
                        </div>

                        @if($status->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $status->description }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </label>
                            <div class="mt-1">
                                @if($status->is_active)
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Sort Order
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $status->sort_order }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Projects Using This Status
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $status->projects->count() }} projects</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $status->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Last Updated
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $status->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status ID
                            </label>
                            <p class="mt-1 text-lg text-gray-900">#{{ $status->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Preview -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">How This Status Appears</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Badge Style -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Badge Style</p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full" 
                              style="background-color: {{ $status->color }}20; color: {{ $status->color }};">
                            {{ $status->name }}
                        </span>
                    </div>

                    <!-- Dot Indicator -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">With Dot Indicator</p>
                        <div class="flex items-center justify-center space-x-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $status->color }}"></div>
                            <span class="text-sm font-medium text-gray-900">{{ $status->name }}</span>
                        </div>
                    </div>

                    <!-- Full Color Block -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Color Block</p>
                        <div class="w-full h-8 rounded flex items-center justify-center text-white font-medium" 
                             style="background-color: {{ $status->color }};">
                            {{ $status->name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Using This Status -->
        @if($status->projects->count() > 0)
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Projects Using This Status ({{ $status->projects->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Project
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assigned To
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($status->projects->take(10) as $project)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $project->name }}
                                            </div>
                                            @if($project->gc)
                                                <div class="text-sm text-gray-500">
                                                    GC: {{ $project->gc }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project->assignedTo)
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium mr-2">
                                                    {{ $project->assignedTo->initials() }}
                                                </div>
                                                <span class="text-sm text-gray-900">{{ $project->assignedTo->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-sm">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($project->due_date)
                                            <span class="{{ $project->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                                {{ $project->due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">No due date</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($status->projects->count() > 10)
                        <div class="px-6 py-3 border-t border-gray-200 text-center">
                            <a href="{{ route('admin.projects.index', ['status' => $status->name]) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm">
                                View all {{ $status->projects->count() }} projects with this status →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.statuses.edit', $status) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Status
                    </a>
                    
                    @if($status->projects->count() === 0)
                        <form method="POST" 
                              action="{{ route('admin.statuses.destroy', $status) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this status? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete Status
                            </button>
                        </form>
                    @else
                        <span class="text-gray-500 text-sm flex items-center">
                            Cannot delete - this status is being used by {{ $status->projects->count() }} project(s)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection