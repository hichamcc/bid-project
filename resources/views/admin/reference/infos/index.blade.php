@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Infos table -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Infos</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Materials / vendors reference.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.reference.import.create', 'infos') }}"
                           class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded text-sm">Import</a>
                        <a href="{{ route('admin.reference.infos.create') }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">Add Info</a>
                    </div>
                </div>

                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item or description..."
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">Search</button>
                        @if(request('search'))
                            <a href="{{ route('admin.reference.infos.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($infos as $info)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $info->item }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $info->description ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $info->status ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.reference.infos.edit', $info) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>
                                            <form method="POST" action="{{ route('admin.reference.infos.destroy', $info) }}" onsubmit="return confirm('Delete this info?');">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No infos yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reminders (comment-style) -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Reminders</h3>

                {{-- Add a reminder --}}
                <form method="POST" action="{{ route('admin.reference.infos.reminders.store') }}" class="space-y-2 mb-4">
                    @csrf
                    <textarea name="body" rows="3" required
                              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                              placeholder="Add a reminder...">{{ old('body') }}</textarea>
                    @error('body') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex justify-end">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">Save</button>
                    </div>
                </form>

                {{-- List --}}
                <div class="space-y-3">
                    @php $me = auth()->user(); @endphp
                    @forelse($reminders as $reminder)
                        <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-3"
                             x-data="{ editing: false }">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $reminder->user?->name ?? 'Unknown' }}</span>
                                    <span>{{ $reminder->created_at->copy()->setTimezone('America/New_York')->format('M d, Y g:i A') }} EST</span>
                                    @if($reminder->updated_at->gt($reminder->created_at))
                                        <span class="italic">(edited)</span>
                                    @endif
                                </div>
                                @if($reminder->editableBy($me))
                                    <div class="flex items-center gap-2 text-xs flex-shrink-0">
                                        <button type="button" @click="editing = !editing" class="text-blue-600 dark:text-blue-400 hover:underline" x-text="editing ? 'Cancel' : 'Edit'"></button>
                                        <form method="POST" action="{{ route('admin.reference.infos.reminders.destroy', $reminder) }}" onsubmit="return confirm('Delete this reminder?');">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line" x-show="!editing">{{ $reminder->body }}</p>

                            <form method="POST" action="{{ route('admin.reference.infos.reminders.update', $reminder) }}" x-show="editing" x-cloak class="mt-1 space-y-2">
                                @csrf @method('PUT')
                                <textarea name="body" rows="3" required
                                          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ $reminder->body }}</textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="editing = false" class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Cancel</button>
                                    <button class="px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">No reminders yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
