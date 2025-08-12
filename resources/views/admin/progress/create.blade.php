@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Add New Progress Entry</h2>
                    <a href="{{ route('admin.progress.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Progress
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Create Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.progress.store') }}">
                    @csrf

                    <!-- Project Selection -->
                    <div class="mb-6">
                        <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Project <span class="text-red-500">*</span>
                        </label>
                        <select id="project_id" 
                                name="project_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror"
                                onchange="showProjectInfo(this.value)">
                            <option value="">Select a project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Project Information Panel -->
                    <div id="projectInfo" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Primary GC</label>
                                <p id="primaryGC" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Assigned Estimator</label>
                                <p id="estimator" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Due Date</label>
                                <p id="dueDate" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Job Number -->
                    <div class="mb-6">
                        <label for="job_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Job Number
                        </label>
                        <input type="text" 
                               id="job_number" 
                               name="job_number" 
                               value="{{ old('job_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('job_number') border-red-500 @enderror">
                        @error('job_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="assigned_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Assigned Date
                            </label>
                            <input type="date" 
                                   id="assigned_date" 
                                   name="assigned_date" 
                                   value="{{ old('assigned_date') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('assigned_date') border-red-500 @enderror">
                            @error('assigned_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="submission_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Submission Date
                            </label>
                            <input type="date" 
                                   id="submission_date" 
                                   name="submission_date" 
                                   value="{{ old('submission_date') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('submission_date') border-red-500 @enderror">
                            @error('submission_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Measurements Section -->
                    <div class="mb-6 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Measurements</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="total_sqft" class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Square Feet
                                </label>
                                <input type="number" 
                                       id="total_sqft" 
                                       name="total_sqft" 
                                       step="0.01"
                                       min="0"
                                       value="{{ old('total_sqft') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_sqft') border-red-500 @enderror">
                                @error('total_sqft')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="total_lnft" class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Linear Feet
                                </label>
                                <input type="number" 
                                       id="total_lnft" 
                                       name="total_lnft" 
                                       step="0.01"
                                       min="0"
                                       value="{{ old('total_lnft') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_lnft') border-red-500 @enderror">
                                @error('total_lnft')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="total_sinks" class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Sinks
                                </label>
                                <input type="number" 
                                       id="total_sinks" 
                                       name="total_sinks" 
                                       min="0"
                                       value="{{ old('total_sinks') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_sinks') border-red-500 @enderror">
                                @error('total_sinks')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="total_slabs" class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Slabs
                                </label>
                                <input type="number" 
                                       id="total_slabs" 
                                       name="total_slabs" 
                                       min="0"
                                       value="{{ old('total_slabs') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_slabs') border-red-500 @enderror">
                                @error('total_slabs')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Total Hours -->
                    <div class="mb-6">
                        <label for="total_hours" class="block text-sm font-medium text-gray-700 mb-2">
                            Total Hours
                        </label>
                        <input type="number" 
                               id="total_hours" 
                               name="total_hours" 
                               step="0.01"
                               min="0"
                               value="{{ old('total_hours') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_hours') border-red-500 @enderror">
                        @error('total_hours')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.progress.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Progress Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const projectsData = @json($projects->load(['assignedTo'])->keyBy('id'));

function showProjectInfo(projectId) {
    const projectInfo = document.getElementById('projectInfo');
    
    if (!projectId) {
        projectInfo.classList.add('hidden');
        return;
    }
    
    const project = projectsData[projectId];
    if (!project) return;
    
    // Show project information
    document.getElementById('primaryGC').textContent = project.gc || '-';
    document.getElementById('estimator').textContent = project.assigned_to 
        ? project.assigned_to.name 
        : '-';
    document.getElementById('dueDate').textContent = project.due_date 
        ? new Date(project.due_date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
          })
        : '-';
    
    projectInfo.classList.remove('hidden');
}

// Show project info on page load if project is already selected
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    if (projectSelect.value) {
        showProjectInfo(projectSelect.value);
    }
});
</script>
@endsection