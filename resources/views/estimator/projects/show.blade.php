@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center space-x-3 mb-2">
                            <h2 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h2>
                            
                            @if($project->statusRecord)
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                    {{ $project->status }}
                                </span>
                            @endif

                            @if($project->typeRecord)
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-700">
                                    {{ $project->type }}
                                </span>
                            @endif
                        </div>
                        @if($project->client_name)
                            <p class="text-gray-600">Client: {{ $project->client_name }}</p>
                        @endif
                    </div>
                    <a href="{{ route('estimator.projects.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                        ← Back to Projects
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Project Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Project Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                        
                        <!-- GC Information -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">General Contractor Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($project->gc)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary GC</label>
                                        <p class="text-gray-900">{{ $project->gc }}</p>
                                    </div>
                                @endif
                                
                                @if($project->other_gc && count($project->other_gc) > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Other GCs</label>
                                        <p class="text-gray-900">{{ implode(', ', $project->other_gc) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Scope Information -->
                        @if($project->scope)
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Project Scope</h4>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $project->scope }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Additional Project Information -->
                        @if($project->project_information)
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Additional Project Information</h4>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $project->project_information }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($project->description)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <p class="text-gray-900">{{ $project->description }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($project->client_name)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                                    <p class="text-gray-900">{{ $project->client_name }}</p>
                                </div>
                            @endif

                            @if($project->client_email)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Email</label>
                                    <p class="text-gray-900">{{ $project->client_email }}</p>
                                </div>
                            @endif

                            @if($project->client_phone)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Phone</label>
                                    <p class="text-gray-900">{{ $project->client_phone }}</p>
                                </div>
                            @endif

                            @if($project->budget)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget</label>
                                    <p class="text-gray-900">${{ number_format($project->budget, 2) }}</p>
                                </div>
                            @endif

                            @if($project->due_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                    <p class="text-gray-900">
                                        {{ $project->due_date->format('M d, Y') }}
                                        @if($project->due_date->isPast())
                                            <span class="text-red-600 font-medium ml-2">(Overdue)</span>
                                        @elseif($project->due_date->isToday())
                                            <span class="text-yellow-600 font-medium ml-2">(Due Today)</span>
                                        @elseif($project->due_date->diffInDays() <= 3)
                                            <span class="text-yellow-600 font-medium ml-2">(Due Soon)</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
                                <p class="text-gray-900">{{ $project->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                                <p class="text-gray-900">{{ $project->updated_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Remarks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Project Activity</h3>
                        
                        <!-- Add New Remark Form -->
                        <form action="{{ route('estimator.projects.remarks.store', $project) }}" method="POST" class="mb-6">
                            @csrf
                            <div class="mb-4">
                                <label for="remark" class="block text-sm font-medium text-gray-700 mb-1">Add a Remark</label>
                                <textarea name="remark" 
                                          id="remark" 
                                          rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Enter your remark about this project..."
                                          required></textarea>
                                @error('remark')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                                Add Remark
                            </button>
                        </form>

                        <!-- Remarks List -->
                        @if($project->remarks->count() > 0)
                            <div class="space-y-4">
                                @foreach($project->remarks as $remark)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium">
                                                        {{ $remark->user->initials() }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $remark->user->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $remark->created_at->format('M d, Y \a\t g:i A') }}</p>
                                                    </div>
                                                </div>
                                                <p class="text-gray-700">{{ $remark->remark }}</p>
                                            </div>
                                            
                                            @if($remark->user_id === Auth::id())
                                                <form action="{{ route('estimator.projects.remarks.destroy', $remark) }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this remark?')" class="ml-4">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No remarks yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Be the first to add a remark about this project.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Update -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                        
                        <form action="{{ route('estimator.projects.status', $project) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Project Status</label>
                                <select name="status" 
                                        id="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}" 
                                                {{ $project->status === $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Project Quick Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Info</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Status:</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                    {{ $project->status ?: 'No Status' }}
                                </span>
                            </div>

                            @if($project->type)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Type:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $project->type }}</span>
                                </div>
                            @endif

                            @if($project->gc)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Primary GC:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $project->gc }}</span>
                                </div>
                            @endif
                            
                            @if($project->other_gc && count($project->other_gc) > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Other GCs:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ count($project->other_gc) }} GCs</span>
                                </div>
                            @endif

                            @if($project->due_date)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Due Date:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $project->due_date->format('M d, Y') }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Days Left:</span>
                                    <span class="text-sm font-medium {{ $project->due_date->isPast() ? 'text-red-600' : ($project->due_date->diffInDays() <= 3 ? 'text-yellow-600' : 'text-gray-900') }}">
                                        @if($project->due_date->isPast())

                                            {{ $project->due_date->diffForHumans() }} days overdue
                                        @elseif($project->due_date->isToday())
                                            Due today
                                        @else

                                            {{ intval($project->due_date->diffForHumans() ) }} days left
                                        @endif
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Assigned to:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $project->assignedTo->name }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Remarks:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $project->remarks->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        
                        <div class="space-y-3">
                            <a href="{{ route('estimator.projects.index') }}" 
                               class="w-full bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 inline-block text-center">
                                Back to Projects
                            </a>
                            
                            @if($project->client_email)
                                <a href="mailto:{{ $project->client_email }}" 
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 inline-block text-center">
                                    Email Client
                                </a>
                            @endif

                            @if($project->client_phone)
                                <a href="tel:{{ $project->client_phone }}" 
                                   class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 inline-block text-center">
                                    Call Client
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
    <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg z-50">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('success-message').style.display = 'none';
        }, 3000);
    </script>
@endif
@endsection