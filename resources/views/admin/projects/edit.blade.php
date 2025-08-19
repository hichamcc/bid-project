@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Edit Project: {{ $project->name }}</h2>
                    <a href="{{ route('admin.projects.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $project->name) }}" 
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
                                @foreach($gcs as $gcOption)
                                    <option value="{{ $gcOption->name }}" 
                                            {{ old('gc', isset($project) ? $project->gc : '') == $gcOption->name ? 'selected' : '' }}>
                                        {{ $gcOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Other GC with Details -->
                        <div class="md:col-span-2">
                            <label for="other_gc" class="block text-sm font-medium text-gray-700 mb-2">
                                Other GCs
                            </label>
                            <select id="other_gc" 
                                    name="other_gc_select[]" 
                                    multiple
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('other_gc') border-red-500 @enderror">
                                <option value="">Select GCs</option>
                                @foreach($gcs as $gc)
                                    @php
                                        $isSelected = false;
                                        if (old('other_gc_select')) {
                                            $isSelected = in_array($gc->name, old('other_gc_select'));
                                        } else {
                                            $isSelected = array_key_exists($gc->name, $project->other_gc ?? []);
                                        }
                                    @endphp
                                    <option value="{{ $gc->name }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $gc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('other_gc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Dynamic Other GC Details -->
                            <div id="other_gc_details" class="mt-4 space-y-4">
                                <!-- Dynamic fields will be inserted here -->
                            </div>

                            <!-- Include jQuery and Select2 -->
                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
                            
                            <script>
                                $(document).ready(function() {
                                    // Get existing project data
                                    const existingOtherGc = @json($project->other_gc ?? []);
                                    const oldFormData = @json(old('other_gc_data', []));
                                    
                                    $('#other_gc').select2({
                                        placeholder: 'Select Other GCs',
                                        allowClear: true
                                    });
                                    
                                    // Handle selection changes
                                    $('#other_gc').on('change', function() {
                                        updateOtherGcFields();
                                    });
                                    
                                    function updateOtherGcFields() {
                                        const selectedGCs = $('#other_gc').val() || [];
                                        const container = $('#other_gc_details');
                                        
                                        // Clear existing fields
                                        container.empty();
                                        
                                        // Create fields for each selected GC
                                        selectedGCs.forEach(function(gcName) {
                                            if (gcName) {
                                                // Get existing data for this GC
                                                let existingData = {
                                                    due_date: '',
                                                    web_link: ''
                                                };
                                                
                                                // Check for old form data first (form validation errors)
                                                if (oldFormData[gcName]) {
                                                    existingData = oldFormData[gcName];
                                                } else if (existingOtherGc[gcName]) {
                                                    existingData = existingOtherGc[gcName];
                                                }
                                                
                                                const fieldHtml = `
                                                    <div class="bg-gray-50 p-4 rounded-lg border" data-gc="${gcName}">
                                                        <h4 class="font-medium text-gray-900 mb-3">${gcName}</h4>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                                                <input type="date" 
                                                                       name="other_gc_data[${gcName}][due_date]" 
                                                                       value="${existingData.due_date || ''}"
                                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Web Link</label>
                                                                <input type="url" 
                                                                       name="other_gc_data[${gcName}][web_link]" 
                                                                       value="${existingData.web_link || ''}"
                                                                       placeholder="https://example.com"
                                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            </div>
                                                        </div>
                                                        <!-- Hidden input to ensure GC is included in form data -->
                                                        <input type="hidden" name="other_gc_names[]" value="${gcName}">
                                                    </div>
                                                `;
                                                container.append(fieldHtml);
                                            }
                                        });
                                    }
                                    
                                    // Initialize fields with existing data
                                    updateOtherGcFields();
                                });
                            </script>
                        </div>

                         <!-- RFI -->
                        <div>
                            <label for="rfi" class="block text-sm font-medium text-gray-700 mb-2">
                                RFI
                            </label>
                            <input type="text" 
                                   id="rfi" 
                                   name="rfi" 
                                   value="{{ old('rfi', $project->rfi) }}" 
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
                                   value="{{ old('assigned_date', $project->assigned_date?->format('Y-m-d')) }}" 
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
                                   value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}" 
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
                                    <option value="{{ $status->name }}" {{ old('status', $project->status) === $status->name ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <script>    
                            $(document).ready(function() {
                                // initial value
                                if ($('#status').val() === 'RFI REQUESTED') {
                                    $('#rfi_section').show();
                                } else {
                                    $('#rfi_section').hide();
                                }
                                $('#status').change(function() {
                                    if ($(this).val() === 'RFI REQUESTED') {
                                        $('#rfi_section').show();
                                    } else {
                                        $('#rfi_section').hide();
                                    }
                                });
                            });
                        </script>

                        <!-- RFI Section -->
                        <div class="md:col-span-2" id="rfi_section" style="display: none;">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">RFI Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- First RFI Request Date -->
                                <div>
                                    <label for="rfi_request_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        First RFI Request Date
                                    </label>
                                    <input type="date" 
                                           id="rfi_request_date" 
                                           name="rfi_request_date" 
                                           value="{{ old('rfi_request_date', $project->rfi_request_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rfi_request_date') border-red-500 @enderror">
                                    @error('rfi_request_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- First RFI Due Date -->
                                <div>
                                    <label for="rfi_due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        First RFI Due Date
                                    </label>
                                    <input type="date" 
                                           id="rfi_due_date" 
                                           name="rfi_due_date" 
                                           value="{{ old('rfi_due_date', $project->rfi_due_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rfi_due_date') border-red-500 @enderror">
                                    @error('rfi_due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- First RFI Attachment -->
                                <div>
                                    <label for="first_rfi_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                        1st RFI Attachment
                                    </label>
                                    <input type="file" 
                                           id="first_rfi_attachment" 
                                           name="first_rfi_attachment" 
                                           accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_rfi_attachment') border-red-500 @enderror">
                                    @if($project->first_rfi_attachment)
                                        <p class="mt-1 text-sm text-gray-600">Current: 1st RFI Attachment</p>
                                    @endif
                                    @error('first_rfi_attachment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Second RFI Request Date -->
                                <div>
                                    <label for="second_rfi_request_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Second RFI Request Date
                                    </label>
                                    <input type="date" 
                                           id="second_rfi_request_date" 
                                           name="second_rfi_request_date" 
                                           value="{{ old('second_rfi_request_date', $project->second_rfi_request_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('second_rfi_request_date') border-red-500 @enderror">
                                    @error('second_rfi_request_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Second RFI Due Date -->
                                <div>
                                    <label for="second_rfi_due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Second RFI Due Date
                                    </label>
                                    <input type="date" 
                                           id="second_rfi_due_date" 
                                           name="second_rfi_due_date" 
                                           value="{{ old('second_rfi_due_date', $project->second_rfi_due_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('second_rfi_due_date') border-red-500 @enderror">
                                    @error('second_rfi_due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Second RFI Attachment -->
                                <div>
                                    <label for="second_rfi_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                        2nd RFI Attachment
                                    </label>
                                    <input type="file" 
                                           id="second_rfi_attachment" 
                                           name="second_rfi_attachment" 
                                           accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('second_rfi_attachment') border-red-500 @enderror">
                                    @if($project->second_rfi_attachment)
                                        <p class="mt-1 text-sm text-gray-600">Current: 2nd RFI Attachment</p>
                                    @endif
                                    @error('second_rfi_attachment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Third RFI Request Date -->
                                <div>
                                    <label for="third_rfi_request_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Third RFI Request Date
                                    </label>
                                    <input type="date" 
                                           id="third_rfi_request_date" 
                                           name="third_rfi_request_date" 
                                           value="{{ old('third_rfi_request_date', $project->third_rfi_request_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('third_rfi_request_date') border-red-500 @enderror">
                                    @error('third_rfi_request_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Third RFI Due Date -->
                                <div>
                                    <label for="third_rfi_due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Third RFI Due Date
                                    </label>
                                    <input type="date" 
                                           id="third_rfi_due_date" 
                                           name="third_rfi_due_date" 
                                           value="{{ old('third_rfi_due_date', $project->third_rfi_due_date?->format('Y-m-d')) }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('third_rfi_due_date') border-red-500 @enderror">
                                    @error('third_rfi_due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Third RFI Attachment -->
                                <div>
                                    <label for="third_rfi_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                        3rd RFI Attachment
                                    </label>
                                    <input type="file" 
                                           id="third_rfi_attachment" 
                                           name="third_rfi_attachment" 
                                           accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('third_rfi_attachment') border-red-500 @enderror">
                                    @if($project->third_rfi_attachment)
                                        <p class="mt-1 text-sm text-gray-600">Current: 3rd RFI Attachment</p>
                                    @endif
                                    @error('third_rfi_attachment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
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
                                    <option value="{{ $type->name }}" {{ old('type', $project->type) === $type->name ? 'selected' : '' }}>
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
                                    <option value="{{ $estimator->id }}" {{ old('assigned_to', $project->assigned_to) == $estimator->id ? 'selected' : '' }}>
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
                                   value="{{ old('web_link', $project->web_link) }}" 
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
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('scope') border-red-500 @enderror">{{ old('scope', $project->scope) }}</textarea>
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
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_information') border-red-500 @enderror">{{ old('project_information', $project->project_information) }}</textarea>
                            @error('project_information')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Project Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <p><strong>Created:</strong> {{ $project->created_at->format('M d, Y \a\t g:i A') }}</p>
                                <p><strong>Last Updated:</strong> {{ $project->updated_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div>
                                @if($project->remarks()->count() > 0)
                                    <p><strong>Remarks:</strong> {{ $project->remarks()->count() }} remark(s)</p>
                                @endif
                                @if($project->assignedTo)
                                    <p><strong>Currently Assigned To:</strong> {{ $project->assignedTo->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('admin.projects.show', $project) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            View Project
                        </a>
                        <a href="{{ route('admin.projects.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection