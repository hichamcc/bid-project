@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">GC Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.gcs.edit', $gc) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit GC
                        </a>
                        <a href="{{ route('admin.gcs.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to GCs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- GC Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">GC Information</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl">
                                    {{ $gc->initials() }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-xl font-semibold text-gray-900">{{ $gc->name }}</h4>
                                @if($gc->company)
                                    <p class="text-gray-600">{{ $gc->company }}</p>
                                @endif
                                <div class="mt-1">
                                    @if($gc->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($gc->email || $gc->phone)
                            <div class="border-t pt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h5>
                                
                                @if($gc->email)
                                    <div class="flex items-center mb-2">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <a href="mailto:{{ $gc->email }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $gc->email }}
                                        </a>
                                    </div>
                                @endif

                                @if($gc->phone)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <a href="tel:{{ $gc->phone }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $gc->formatted_phone }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($gc->address)
                            <div class="border-t pt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Address</h5>
                                <div class="text-gray-900 whitespace-pre-line">{{ $gc->address }}</div>
                            </div>
                        @endif

                        @if($gc->notes)
                            <div class="border-t pt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Notes</h5>
                                <div class="text-gray-900 whitespace-pre-line">{{ $gc->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Statistics</h3>
                    
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-blue-900">{{ $totalProjects }}</div>
                                    <div class="text-sm text-blue-700">Total Projects</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-green-900">{{ $activeProjects }}</div>
                                    <div class="text-sm text-green-700">Active Projects</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900">{{ $completedProjects }}</div>
                                    <div class="text-sm text-gray-700">Completed Projects</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($totalProjects > 0)
                        <div class="mt-6">
                            <a href="{{ route('admin.projects.index', ['gc' => $gc->name]) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 inline-block text-center">
                                View All Projects
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        @if(isset($gc->recentProjects) && $gc->recentProjects->count() > 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                        @if($totalProjects > 10)
                            <a href="{{ route('admin.projects.index', ['gc' => $gc->name]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View all {{ $totalProjects }} projects →
                            </a>
                        @endif
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($gc->recentProjects as $project)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                            @if($project->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($project->gc === $gc->name)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Primary
                                                </span>
                                            @elseif(in_array($gc->name, $project->other_gc ?? []))
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Other
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($project->statusRecord)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                                    {{ $project->status }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    No Status
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($project->assignedTo)
                                                <div class="text-sm text-gray-900">{{ $project->assignedTo->name }}</div>
                                            @else
                                                <span class="text-gray-400">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($project->due_date)
                                                {{ $project->due_date->format('M d, Y') }}
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
                    </div>
                </div>
            </div>
        @elseif($totalProjects === 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Projects</h3>
                    <p class="text-gray-500 mb-4">This GC doesn't have any projects assigned yet.</p>
                    <a href="{{ route('admin.projects.create', ['gc' => $gc->name]) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                        Create First Project
                    </a>
                </div>
            </div>
        @endif

        <!-- System Information -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <p><strong>Created:</strong> {{ $gc->created_at->format('M d, Y \a\t g:i A') }}</p>
                        <p><strong>Last Updated:</strong> {{ $gc->updated_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <p><strong>GC ID:</strong> #{{ $gc->id }}</p>
                        <p><strong>Status:</strong> {{ $gc->is_active ? 'Active' : 'Inactive' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admin.gcs.edit', $gc) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit GC
                    </a>
                    
                    @if($totalProjects === 0)
                        <form method="POST" 
                              action="{{ route('admin.gcs.destroy', $gc) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this GC? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete GC
                            </button>
                        </form>
                    @else
                        <span class="text-gray-500 text-sm flex items-center">
                            Cannot delete - this GC is being used by {{ $totalProjects }} project(s)
                        </span>
                    @endif

                

                    <a href="{{ route('admin.projects.create', ['gc' => $gc->name]) }}" 
                       class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Create New Project
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection