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

                <!-- Job Settings -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Job Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Type</label>
                            <select name="job_type" id="job_type_select"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="MU" {{ $allocation->job_type === 'MU' ? 'selected' : '' }}>MU</option>
                                <option value="NON_MU" {{ $allocation->job_type === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Days Required</label>
                            <input type="number" name="days_required" step="0.5" min="0.5"
                                   value="{{ $allocation->days_required }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    @php $firstProject = $allocation->projects->first(); @endphp

                    <div class="mt-4 space-y-4">
                        <!-- Project Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Name</label>
                            <input type="text" name="project_name"
                                   value="{{ $firstProject ? trim(preg_replace('/^[^0-9]*[0-9]+[A-Za-z]*\.\s*/', '', $firstProject->name)) : '' }}"
                                   placeholder="Project name (without job number)"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Job number prefix (e.g. 26077A.) is preserved automatically.</p>
                        </div>

                        <!-- Web Link -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Web Link</label>
                            <input type="text" name="web_link"
                                   value="{{ $firstProject?->web_link ?? '' }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Project Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Information</label>
                            <textarea name="project_information" rows="3"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $firstProject?->project_information ?? '' }}</textarea>
                        </div>
                    </div>

                </div>

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

                <!-- Due Dates -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Due Dates</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Changing the main due date will notify all open estimators by email.
                        Other GC due dates are estimator due dates (stored directly, -2 days applied on save).
                    </p>

                    <div class="space-y-3">
                        <!-- Main allocation due date -->
                        <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <span class="w-40 flex-shrink-0">
                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Main Due Date</span>
                                @if($allocation->projects->first()?->gc)
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ $allocation->projects->first()->gc }}</span>
                                @endif
                            </span>
                            <input type="date"
                                   name="due_date"
                                   value="{{ $allocation->due_date->format('Y-m-d') }}"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                Estimator due: {{ $allocation->assigned_date->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Per other-GC due dates -->
                        @foreach($gcGroups as $gcName => $currentDueDate)
                            @if($gcName !== '' && $gcName !== $allocation->projects->first()?->gc)
                                @php $realDueDate = $currentDueDate ? $currentDueDate->copy()->addDays(2) : null; @endphp
                                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-40 flex-shrink-0 truncate" title="{{ $gcName }}">
                                        {{ $gcName }}
                                    </span>
                                    <input type="date"
                                           name="gc_due_dates[{{ $gcName }}]"
                                           value="{{ $realDueDate ? $realDueDate->format('Y-m-d') : '' }}"
                                           class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                        Estimator due: {{ $currentDueDate ? $currentDueDate->format('M d, Y') : '—' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Add Other GCs -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Add Other GCs</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Select additional GCs. A project will be auto-created per estimator for each new GC. Due date is the real GC due date (−2 days applied on save).
                    </p>

                    <select id="edit_other_gc"
                            name="other_gc_select[]"
                            multiple
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($gcs as $gcItem)
                            @if(!in_array($gcItem->name, $excludedGcNames))
                                <option value="{{ $gcItem->name }}">{{ $gcItem->name }}</option>
                            @endif
                        @endforeach
                    </select>

                    <div id="edit_other_gc_details" class="mt-3 space-y-3"></div>

                    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                    <script>
                        $(document).ready(function () {
                            $('#edit_other_gc').select2({ placeholder: 'Select Other GCs', allowClear: true });
                            $('#edit_other_gc').on('change', updateEditOtherGcFields);

                            function updateEditOtherGcFields() {
                                var selected = $('#edit_other_gc').val() || [];
                                var container = $('#edit_other_gc_details');
                                container.empty();
                                selected.forEach(function (gcName) {
                                    if (!gcName) return;
                                    container.append(
                                        '<div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">' +
                                        '<p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">' + gcName + '</p>' +
                                        '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                                        '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Due Date</label>' +
                                        '<input type="date" name="other_gc_data[' + gcName + '][due_date]" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>' +
                                        '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Web Link</label>' +
                                        '<input type="url" name="other_gc_data[' + gcName + '][web_link]" placeholder="https://example.com" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>' +
                                        '</div>' +
                                        '<input type="hidden" name="other_gc_names[]" value="' + gcName + '">' +
                                        '</div>'
                                    );
                                });
                            }
                        });
                    </script>
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
