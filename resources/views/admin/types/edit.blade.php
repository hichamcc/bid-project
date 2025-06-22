@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Edit Type: {{ $type->name }}</h2>
                    <a href="{{ route('admin.types.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Types
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.types.update', $type) }}">
                    @csrf
                    @method('PUT')

                    <!-- Type Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Type Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $type->name) }}" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div class="mb-6">
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <input type="color" 
                                   id="color" 
                                   name="color" 
                                   value="{{ old('color', $type->color) }}" 
                                   required
                                   class="h-12 w-20 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('color') border-red-500 @enderror">
                            <div class="flex-1">
                                <input type="text" 
                                       id="color-hex" 
                                       value="{{ old('color', $type->color) }}" 
                                       placeholder="#8b5cf6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Choose a color or enter hex code</p>
                            </div>
                        </div>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $type->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-6">
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            Sort Order
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="{{ old('sort_order', $type->sort_order) }}" 
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('sort_order') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first in lists</p>
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Active -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $type->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (Type will be available for projects)
                            </label>
                        </div>
                        @if($type->projects()->count() > 0 && !$type->is_active)
                            <p class="mt-1 text-sm text-yellow-600">
                                ⚠️ This type is currently used by {{ $type->projects()->count() }} project(s). 
                                Consider keeping it active or migrating projects to another type.
                            </p>
                        @endif
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
                        <div class="flex items-center space-x-3">
                            <div id="color-preview" class="w-4 h-4 rounded-full" style="background-color: {{ old('color', $type->color) }}"></div>
                            <span id="name-preview" class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ old('color', $type->color) }}20; color: {{ old('color', $type->color) }}">
                                {{ old('name', $type->name) }}
                            </span>
                        </div>
                    </div>

                    <!-- Type Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Type Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <p><strong>Created:</strong> {{ $type->created_at->format('M d, Y \a\t g:i A') }}</p>
                                <p><strong>Last Updated:</strong> {{ $type->updated_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div>
                                <p><strong>Current Sort Order:</strong> {{ $type->sort_order }}</p>
                            </div>
                        </div>
                        @if($type->projects()->count() > 0)
                            <div class="mt-2 pt-2 border-t border-gray-200">
                                <a href="{{ route('admin.projects.index', ['type' => $type->name]) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    View projects using this type →
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.types.show', $type) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            View Type
                        </a>
                        <a href="{{ route('admin.types.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorHexInput = document.getElementById('color-hex');
    const nameInput = document.getElementById('name');
    const colorPreview = document.getElementById('color-preview');
    const namePreview = document.getElementById('name-preview');

    function updatePreview() {
        const color = colorInput.value;
        const name = nameInput.value || 'Type Name';
        
        colorPreview.style.backgroundColor = color;
        namePreview.style.backgroundColor = color + '20';
        namePreview.style.color = color;
        namePreview.textContent = name;
        colorHexInput.value = color;
    }

    function updateColorFromHex() {
        const hexValue = colorHexInput.value;
        if (/^#[0-9A-Fa-f]{6}$/.test(hexValue)) {
            colorInput.value = hexValue;
            updatePreview();
        }
    }

    colorInput.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    colorHexInput.addEventListener('input', updateColorFromHex);
    colorHexInput.addEventListener('blur', updateColorFromHex);
});
</script>
@endsection