@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Source</h2>
                <a href="{{ route('admin.sources.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.sources.update', $source) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $source->name) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('name') border-red-500 @enderror">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $source->sort_order) }}" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('sort_order') border-red-500 @enderror">
                            @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $source->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('admin.sources.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
