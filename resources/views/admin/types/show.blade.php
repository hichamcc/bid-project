@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Type Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.types.edit', $type) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Type
                        </a>
                        <a href="{{ route('admin.types.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Types
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Type Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Type Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Type Name
                            </label>
                            <div class="mt-1 flex items-center space-x-3">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $type->color }}"></div>
                                <p class="text-lg text-gray-900">{{ $type->name }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Color
                            </label>
                            <div class="mt-1 flex items-center space-x-3">
                                <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $type->color }}"></div>
                                <span class="text-lg text-gray-900 font-mono">{{ $type->color }}</span>
                            </div>
                        </div>

                        @if($type->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $type->description }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </label>
                            <div class="mt-1">
                                @if($type->is_active)
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
                            <p class="mt-1 text-lg text-gray-900">{{ $type->sort_order }}</p>
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
                                Projects Using This Type
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $type->projects->count() }} projects</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $type->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Last Updated
                            </label>
                            <p class="mt-1 text-lg text-gray-900">{{ $type->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Type ID
                            </label>
                            <p class="mt-1 text-lg text-gray-900">#{{ $type->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Type Preview -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">How This Type Appears</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Badge Style -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Badge Style</p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full" 
                              style="background-color: {{ $type->color }}20; color: {{ $type->color }};">
                            {{ $type->name }}
                        </span>
                    </div>

                    <!-- Dot Indicator -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">With Dot Indicator</p>
                        <div class="flex items-center justify-center space-x-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $type->color }}"></div>
                            <span class="text-sm font-medium text-gray-900">{{ $type->name }}</span>
                        </div>
                    </div>

                    <!-- Full Color Block -->
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Color Block</p>
                        <div class="w-full h-8 rounded flex items-center justify-center text-white font-medium" 
                             style="background-color: {{ $type->color }};">
                            {{ $type->name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Using This Type -->
        @if($type->projects->count() > 0)
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Projects Using This Type ({{ $type->projects->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Project
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
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
                            @foreach($type->projects->take(10) as $project)
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
                                        @if($project->statusRecord)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                                {{ $project->status }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                No Status
                                            </span>
                                        @endif
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
                    @if($type->projects->count() > 10)
                        <div class="px-6 py-3 border-t border-gray-200 text-center">
                            <a href="{{ route('admin.projects.index', ['type' => $type->name]) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm">
                                View all {{ $type->projects->count() }} projects with this type →
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
                    <a href="{{ route('admin.types.edit', $type) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Type
                    </a>
                    
                    @if($type->projects->count() === 0)
                        <form method="POST" 
                              action="{{ route('admin.types.destroy', $type) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this type? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete Type
                            </button>
                        </form>
                    @else
                        <span class="text-gray-500 text-sm flex items-center">
                            Cannot delete - this type is being used by {{ $type->projects->count() }} project(s)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection