{{-- Create form body. Rendered standalone (AJAX modal) and inside create.blade.php. --}}
<form method="POST" action="{{ route('scope-review.store') }}" data-scope-form>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Entry Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('entry_date') border-red-500 @enderror">
            @error('entry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Source</label>
            <select name="source"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('source') border-red-500 @enderror">
                <option value="">-- Select source --</option>
                @foreach(\App\Models\Source::active()->ordered()->pluck('name') as $sourceOption)
                    <option value="{{ $sourceOption }}" {{ old('source') === $sourceOption ? 'selected' : '' }}>{{ $sourceOption }}</option>
                @endforeach
            </select>
            @error('source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platform</label>
            <input type="text" name="platform" value="{{ old('platform') }}" placeholder="e.g. Dodge, BuildingConnected"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('platform') border-red-500 @enderror">
            @error('platform') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Project Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="project_name" value="{{ old('project_name') }}" required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('project_name') border-red-500 @enderror">
            @error('project_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bid Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('due_date') border-red-500 @enderror">
            @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
            <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Charlotte, NC"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('location') border-red-500 @enderror">
            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Link</label>
            <input type="url" name="project_link" value="{{ old('project_link') }}" placeholder="https://example.com"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('project_link') border-red-500 @enderror">
            @error('project_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Estimator</label>
            <select name="assigned_estimator_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('assigned_estimator_id') border-red-500 @enderror">
                <option value="">-- Not assigned yet --</option>
                @foreach($estimators as $estimator)
                    <option value="{{ $estimator->id }}" {{ old('assigned_estimator_id') == $estimator->id ? 'selected' : '' }}>
                        {{ $estimator->name }}
                    </option>
                @endforeach
            </select>
            @error('assigned_estimator_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex justify-end space-x-4">
        <a href="{{ route('scope-review.index') }}" data-scope-cancel
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Cancel
        </a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create
        </button>
    </div>
</form>
