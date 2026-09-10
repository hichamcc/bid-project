<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GC Name <span class="text-red-500">*</span></label>
    <input type="text" name="gc_name" value="{{ old('gc_name', $skippedGc->gc_name ?? '') }}" required
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    @error('gc_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        @foreach(\App\Models\ReferenceSkippedGc::STATUSES as $s)
            <option value="{{ $s }}" {{ old('status', $skippedGc->status ?? 'SKIP') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
    <textarea name="note" rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('note', $skippedGc->note ?? '') }}</textarea>
</div>
