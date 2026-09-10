@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Log Ins</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Platform credentials (passwords are encrypted at rest).</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.reference.import.create', 'logins') }}"
                       class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded text-sm">Import</a>
                    <a href="{{ route('admin.reference.logins.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">Add Login</a>
                </div>
            </div>

            @if(session('success'))
                <div class="m-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search platform or email..."
                           class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.reference.logins.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">Clear</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Platform</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Password</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logins as $login)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" x-data="{ shown: false }">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $login->platform }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $login->email ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    @if($login->password)
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono" x-show="!shown">••••••••</span>
                                            <span class="font-mono" x-show="shown" x-cloak>{{ $login->password }}</span>
                                            <button type="button" @click="shown = !shown" class="text-xs text-blue-600 dark:text-blue-400 hover:underline" x-text="shown ? 'Hide' : 'Show'"></button>
                                            <button type="button" @click="navigator.clipboard.writeText(@js($login->password))" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Copy</button>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $login->notes ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.reference.logins.edit', $login) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>
                                        <form method="POST" action="{{ route('admin.reference.logins.destroy', $login) }}" onsubmit="return confirm('Delete this login?');">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No logins yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
