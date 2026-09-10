<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Platform <span class="text-red-500">*</span></label>
    <input type="text" name="platform" value="{{ old('platform', $login->platform ?? '') }}" required
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    @error('platform') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email / Username</label>
    <input type="text" name="email" value="{{ old('email', $login->email ?? '') }}"
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
    <input type="text" name="password" value="{{ old('password') }}" autocomplete="off"
           placeholder="{{ ($login->password ?? null) ? 'Leave blank to keep current password' : '' }}"
           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stored encrypted. @if($login->password ?? null) Leave blank to keep the existing password. @endif</p>
    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
    <textarea name="notes" rows="2"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('notes', $login->notes ?? '') }}</textarea>
</div>
