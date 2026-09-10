@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Skipped GCs</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">GCs to skip / proceed / hold, with notes.</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.reference.import.create', 'skipped-gcs') }}"
                       class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded text-sm">Import</a>
                    <a href="{{ route('admin.reference.skipped-gcs.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">Add Entry</a>
                </div>
            </div>

            @if(session('success'))
                <div class="m-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <form method="GET" class="flex flex-wrap gap-2 items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search GC name..."
                           class="flex-1 min-w-48 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <select name="status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\ReferenceSkippedGc::STATUSES as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">Filter</button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.reference.skipped-gcs.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">Clear</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">GC Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Note</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($skippedGcs as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-md {{ $row->statusBadgeClass() }}">{{ $row->status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row->gc_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $row->note ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.reference.skipped-gcs.edit', $row) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>
                                        <form method="POST" action="{{ route('admin.reference.skipped-gcs.destroy', $row) }}" onsubmit="return confirm('Delete this entry?');">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
