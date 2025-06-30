@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Add New Project</h2>
                    <a href="{{ route('admin.projects.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Create Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.projects.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- General Contractor  select from gcs table-->
                        <div>
                            <label for="gc" class="block text-sm font-medium text-gray-700 mb-2">
                                General Contractor
                            </label>
                            <select id="gc" 
                                    name="gc" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gc') border-red-500 @enderror">
                                <option value="">Select a GC</option>
                                @foreach($gcs as $gc)
                                    <option value="{{ $gc->name }}" {{ old('gc') == $gc->name ? 'selected' : '' }}>
                                        {{ $gc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Other GC  / select from gcs table use select2 multiple-->
                        <div>
                            <label for="other_gc" class="block text-sm font-medium text-gray-700 mb-2">
                                Other GC
                            </label>
                            <select id="other_gc" 
                                    name="other_gc[]" 
                                    multiple
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('other_gc') border-red-500 @enderror">
                                <option value="">Select a GC</option>
                                @foreach($gcs as $gc)
                                    <option value="{{ $gc->name }}" {{ old('other_gc') == $gc->name ? 'selected' : '' }}>
                                        {{ $gc->name }}
                                    </option>
                                @endforeach
                            </select>
                              <!-- jquery -->
                              <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                            <!-- select2 script --> 
                            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
                          
                            <!-- select2 script --> 
                            <script>
                                $(document).ready(function() {
                                    $('#other_gc').select2();
                                });
                            </script>
                            @error('other_gc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror 
                        </div>

                        <!-- RFI -->
                        <div>
                            <label for="rfi" class="block text-sm font-medium text-gray-700 mb-2">
                                RFI
                            </label>
                            <input type="text" 
                                   id="rfi" 
                                   name="rfi" 
                                   value="{{ old('rfi') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rfi') border-red-500 @enderror">
                            @error('rfi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assigned Date -->
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

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Due Date
                            </label>
                            <input type="date" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ old('due_date') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select id="status" 
                                    name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                                <option value="">Select a status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->name }}" {{ old('status') === $status->name ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                           <!-- Type -->
                           <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Type
                            </label>
                            <select id="type" 
                                    name="type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                                <option value="">Select a type</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->name }}" {{ old('type') === $type->name ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                 

                        <!-- Assigned To -->
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                                Assigned To (Estimator)
                            </label>
                            <select id="assigned_to" 
                                    name="assigned_to" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('assigned_to') border-red-500 @enderror">
                                <option value="">Select an estimator</option>
                                @foreach($estimators as $estimator)
                                    <option value="{{ $estimator->id }}" {{ old('assigned_to') == $estimator->id ? 'selected' : '' }}>
                                        {{ $estimator->name }} ({{ ucfirst(str_replace('_', ' ', $estimator->role)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Web Link -->
                        <div class="md:col-span-2">
                            <label for="web_link" class="block text-sm font-medium text-gray-700 mb-2">
                                Web Link
                            </label>
                            <input type="url" 
                                   id="web_link" 
                                   name="web_link" 
                                   value="{{ old('web_link') }}" 
                                   placeholder="https://example.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('web_link') border-red-500 @enderror">
                            @error('web_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Scope -->
                        <div class="md:col-span-2">
                            <label for="scope" class="block text-sm font-medium text-gray-700 mb-2">
                                Scope
                            </label>
                            <textarea id="scope" 
                                      name="scope" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('scope') border-red-500 @enderror">{{ old('scope') }}</textarea>
                            @error('scope')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Project Information -->
                        <div class="md:col-span-2">
                            <label for="project_information" class="block text-sm font-medium text-gray-700 mb-2">
                                Project Information
                            </label>
                            <textarea id="project_information" 
                                      name="project_information" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_information') border-red-500 @enderror">{{ old('project_information') }}</textarea>
                            @error('project_information')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('admin.projects.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection