@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Edit Proposal</h2>
                    <a href="{{ route('admin.proposals.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Proposals
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.proposals.update', $proposal) }}" id="proposalForm">
                    @csrf
                    @method('PUT')

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
                                <option value="{{ $project->id }}" {{ (old('project_id', $proposal->project_id) == $project->id) ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Project Information Panel -->
                    <div id="projectInfo" class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Primary GC</label>
                                <p id="primaryGC" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Other GCs</label>
                                <p id="otherGCs" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Estimator</label>
                                <p id="estimator" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Date -->
                    <div class="mb-6">
                        <label for="submission_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Submission Date
                        </label>
                        <input type="date" 
                               id="submission_date" 
                               name="submission_date" 
                               value="{{ old('submission_date', $proposal->submission_date?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('submission_date') border-red-500 @enderror">
                        @error('submission_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label for="price_original" class="block text-sm font-medium text-gray-700 mb-2">
                                Price Original ($)
                            </label>
                            <input type="number" 
                                   id="price_original" 
                                   name="price_original" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('price_original', $proposal->price_original) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_original') border-red-500 @enderror">
                            @error('price_original')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_ve" class="block text-sm font-medium text-gray-700 mb-2">
                                Price VE ($)
                            </label>
                            <input type="number" 
                                   id="price_ve" 
                                   name="price_ve" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('price_ve', $proposal->price_ve) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_ve') border-red-500 @enderror">
                            @error('price_ve')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gc_price" class="block text-sm font-medium text-gray-700 mb-2">
                                GC Price ($)
                            </label>
                            <input type="number" 
                                   id="gc_price" 
                                   name="gc_price" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('gc_price', $proposal->gc_price) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gc_price') border-red-500 @enderror">
                            @error('gc_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Result -->
                    <div class="mb-6">
                        <label for="result" class="block text-sm font-medium text-gray-700 mb-2">
                            Result
                        </label>
                        <select id="result" 
                                name="result" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('result') border-red-500 @enderror">
                            <option value="">Select result</option>
                            <option value="pending" {{ old('result', $proposal->result) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="win" {{ old('result', $proposal->result) == 'win' ? 'selected' : '' }}>Win</option>
                            <option value="loss" {{ old('result', $proposal->result) == 'loss' ? 'selected' : '' }}>Loss</option>
                        </select>
                        @error('result')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.proposals.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Proposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const projectsData = @json($projects->load(['assignedTo', 'gcRecord'])->keyBy('id'));

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
    document.getElementById('otherGCs').textContent = project.other_gc && project.other_gc.length > 0 
        ? project.other_gc.join(', ') 
        : '-';
    document.getElementById('estimator').textContent = project.assigned_to 
        ? project.assigned_to.name 
        : '-';
    
    projectInfo.classList.remove('hidden');
}

// Show project info on page load
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    if (projectSelect.value) {
        showProjectInfo(projectSelect.value);
    }
});
</script>
@endsection