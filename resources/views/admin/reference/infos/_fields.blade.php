<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item <span class="text-red-500">*</span></label>
    <input type="text" name="item" value="{{ old('item', $info->item ?? '') }}" required
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    @error('item') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
    <textarea name="description" rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $info->description ?? '') }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
    <input type="text" name="status" value="{{ old('status', $info->status ?? '') }}" placeholder="Yes / No / OK / free text"
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
</div>
