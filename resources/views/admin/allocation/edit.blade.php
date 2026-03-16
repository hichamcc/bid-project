@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        Edit Distribution — Job #{{ $allocation->job_number }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $allocation->job_type === 'MU' ? 'MU' : 'NON MU' }}
                        &middot; Due {{ $allocation->due_date->format('M d, Y') }}
                        &middot; Estimator Due {{ $allocation->assigned_date->format('M d, Y') }}
                        &middot; Max {{ $limit }} estimator(s)
                    </p>
                </div>
                <a href="{{ route('admin.allocation.index') }}"
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">
                    Cancel
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Edit Form -->
        <form method="POST" action="{{ route('admin.allocation.update', $allocation) }}"
              x-data="{ count: {{ $slots->count() }}, limit: {{ $limit }} }">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <!-- Current Slots -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Current Estimators</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Select <em>"— Remove —"</em> to unassign an estimator from this slot.
                        Swap by choosing a different estimator. Emails are sent automatically on save.
                    </p>

                    <div class="space-y-3">
                        @foreach($slots as $slot)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">

                                <!-- Letter badge -->
                                <span class="w-7 h-7 flex-shrink-0 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-sm rounded-full">
                                    {{ $slot['letter'] }}
                                </span>

                                <!-- Hidden project_id -->
                                <input type="hidden"
                                       name="slots[{{ $slot['estimator']->id }}][project_id]"
                                       value="{{ $slot['project']?->id ?? '' }}">

                                <!-- Estimator Select -->
                                <div class="flex-1 min-w-0">
                                    <select name="slots[{{ $slot['estimator']->id }}][new_id]"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">— Remove —</option>
                                        @foreach($allEstimators as $est)
                                            <option value="{{ $est->id }}"
                                                {{ $est->id === $slot['estimator']->id ? 'selected' : '' }}>
                                                {{ $est->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status badge -->
                                <span class="flex-shrink-0 px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $slot['status'] === 'submitted'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($slot['status']) }}
                                </span>

                                <!-- Project link -->
                                @if($slot['project'])
                                    <a href="{{ route('admin.projects.show', $slot['project']) }}"
                                       class="flex-shrink-0 max-w-[160px] truncate text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                       title="{{ $slot['project']->name }}">
                                        {{ $slot['project']->name }}
                                    </a>
                                @else
                                    <span class="flex-shrink-0 text-xs text-gray-400 italic">No project</span>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Add New Estimator -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700"
                     x-data="{
                         rows: [],
                         add() { this.rows.push(Date.now()); },
                         remove(id) { this.rows = this.rows.filter(r => r !== id); }
                     }">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Add Estimator</h3>

                    <template x-for="row in rows" :key="row">
                        <div class="flex items-center gap-3 mb-3">
                            <select name="new_estimators[]"
                                    class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Select Estimator —</option>
                                @foreach($allEstimators as $est)
                                    <option value="{{ $est->id }}">{{ $est->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="remove(row)"
                                    class="flex-shrink-0 text-red-500 hover:text-red-700 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="add()"
                            :disabled="count + rows.length >= limit"
                            :class="count + rows.length >= limit ? 'opacity-40 cursor-not-allowed' : 'hover:underline'"
                            class="flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Estimator
                    </button>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Max {{ $limit }} total for {{ $allocation->job_type === 'MU' ? 'MU' : 'NON MU' }} jobs.
                        The new project name will be auto-derived from existing projects.
                    </p>
                </div>

                <!-- Actions -->
                <div class="p-6 flex justify-end gap-3">
                    <a href="{{ route('admin.allocation.index') }}"
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-md">
                        Save Changes
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>
@endsection
