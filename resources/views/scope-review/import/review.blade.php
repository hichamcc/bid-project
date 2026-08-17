@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Review Import</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Sheet "{{ $sheet }}" &middot; {{ number_format($totalRows) }} total row(s).
                        @if($totalBatches > 1)
                            Showing rows {{ number_format($batchStart) }}–{{ number_format($batchEnd) }}
                            (batch {{ $batch }} of {{ $totalBatches }}).
                        @endif
                        Nothing is saved until you click Import.
                    </p>
                </div>
                <a href="{{ route('scope-review.import.create') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
            </div>
            @if($totalBatches > 1)
                <div class="px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-800 dark:text-blue-300 font-medium">
                            This file is imported in {{ $totalBatches }} batches of up to 500 rows.
                            Each "Import" button saves the current batch, then loads the next.
                        </span>
                        <span class="text-blue-700 dark:text-blue-400">Batch {{ $batch }} / {{ $totalBatches }}</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-blue-100 dark:bg-blue-900/40 overflow-hidden">
                        <div class="h-full bg-blue-600 dark:bg-blue-500 rounded-full" style="width: {{ round(($batch - 1) / $totalBatches * 100) }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Column mapping summary -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Detected Column Mapping</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    @foreach($fieldKeywords as $field => $keywords)
                        <div class="p-2 rounded border {{ isset($columnMapping[$field]) ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/40' }}">
                            <p class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $field)) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if(isset($columnMapping[$field]))
                                    "{{ $header[$columnMapping[$field]] }}"
                                @else
                                    Not found — fill in manually below
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    "Bid Status" is parsed into both Decision and Project Type (e.g. "Yes - NON MU" → Approved / NON MU).
                    Rows with an unrecognized status are highlighted below and need a manual decision.
                </p>
            </div>
        </div>

        <!-- Row-by-row review -->
        <form method="POST" action="{{ route('scope-review.import.commit') }}"
              id="scope-import-form"
              x-data="scopeImport({
                  commitUrl: '{{ route('scope-review.import.commit') }}',
                  token: @js($token),
                  sheet: @js($sheet),
                  batch: {{ $batch }},
                  totalBatches: {{ $totalBatches }},
                  chunkSize: 50,
                  csrf: '{{ csrf_token() }}'
              })"
              @submit.prevent="run()">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="sheet" value="{{ $sheet }}">
            <input type="hidden" name="batch" value="{{ $batch }}">
            <input type="hidden" name="total_batches" value="{{ $totalBatches }}">

            <!-- Import progress overlay -->
            <div x-show="importing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 max-w-sm w-full text-center">
                    <svg class="animate-spin w-8 h-8 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100" x-text="progressLabel"></p>
                    <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all" :style="`width: ${progressPct}%`"></div>
                    </div>
                </div>
            </div>

            <!-- Import error -->
            <div x-show="errorMessage" x-cloak class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" x-text="errorMessage"></div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Import</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entry Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project Name</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Source</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Platform</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Location</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Due Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estimator</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status (raw)</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Decision</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project #</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Uploaded in OH</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($previewRows as $i => $row)
                                <tr class="{{ !$row['status_recognized'] && $row['status_raw'] ? 'bg-red-50 dark:bg-red-900/10' : '' }} {{ $row['estimator_name_raw'] && !$row['estimator_id'] ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                    <td class="px-3 py-2">
                                        <input type="checkbox" name="rows[{{ $i }}][include]" value="1" checked class="rounded border-gray-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="date" name="rows[{{ $i }}][entry_date]" value="{{ $row['entry_date'] }}" required
                                               class="w-36 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][project_name]" value="{{ $row['project_name'] }}" required
                                               class="w-48 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <input type="hidden" name="rows[{{ $i }}][project_link]" value="{{ $row['project_link'] }}">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][source]" value="{{ $row['source'] }}"
                                               class="w-28 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][platform]" value="{{ $row['platform'] }}"
                                               class="w-28 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][location]" value="{{ $row['location'] }}"
                                               class="w-28 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="date" name="rows[{{ $i }}][due_date]" value="{{ $row['due_date'] }}"
                                               class="w-36 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="rows[{{ $i }}][estimator_id]"
                                                class="w-36 px-2 py-1 text-sm border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $row['estimator_name_raw'] && !$row['estimator_id'] ? 'border-yellow-400' : 'border-gray-300 dark:border-gray-600' }}">
                                            <option value="">— Unassigned —</option>
                                            @foreach($estimators as $estimator)
                                                <option value="{{ $estimator->id }}" {{ (string) $row['estimator_id'] === (string) $estimator->id ? 'selected' : '' }}>
                                                    {{ $estimator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row['estimator_name_raw'] && !$row['estimator_id'])
                                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-0.5">"{{ $row['estimator_name_raw'] }}" not matched</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 max-w-[120px] truncate" title="{{ $row['status_raw'] }}">
                                        {{ $row['status_raw'] ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="rows[{{ $i }}][decision]"
                                                class="w-32 px-2 py-1 text-sm border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ !$row['status_recognized'] && $row['status_raw'] ? 'border-red-400' : 'border-gray-300 dark:border-gray-600' }}">
                                            <option value="" {{ !$row['decision'] ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $row['decision'] === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rfi_requested" {{ $row['decision'] === 'rfi_requested' ? 'selected' : '' }}>RFI Requested</option>
                                            <option value="not_in_scope" {{ $row['decision'] === 'not_in_scope' ? 'selected' : '' }}>Not In Scope</option>
                                            <option value="skipped" {{ $row['decision'] === 'skipped' ? 'selected' : '' }}>Skipped</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="rows[{{ $i }}][project_type]"
                                                class="w-24 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="" {{ !$row['project_type'] ? 'selected' : '' }}>—</option>
                                            <option value="MU" {{ $row['project_type'] === 'MU' ? 'selected' : '' }}>MU</option>
                                            <option value="NON_MU" {{ $row['project_type'] === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][project_number]" value="{{ $row['project_number'] }}"
                                               class="w-24 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="rows[{{ $i }}][uploaded_in_oh]" value="0">
                                        <input type="checkbox" name="rows[{{ $i }}][uploaded_in_oh]" value="1" {{ $row['uploaded_in_oh'] ? 'checked' : '' }}
                                               class="rounded border-gray-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][notes]" value="{{ $row['notes'] }}"
                                               class="w-40 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </td>
                                    <input type="hidden" name="rows[{{ $i }}][duration]" value="{{ $row['duration'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][reason_to_ignore]" value="{{ $row['reason_to_ignore'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][bid_stage]" value="{{ $row['bid_stage'] }}">
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-block w-3 h-3 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 rounded mr-1"></span> Estimator not matched
                        &nbsp;&nbsp;
                        <span class="inline-block w-3 h-3 bg-red-100 dark:bg-red-900/30 border border-red-300 rounded mr-1"></span> Unrecognized status — set decision manually
                    </p>
                    <button type="submit" :disabled="importing"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        @if($totalBatches > 1)
                            Import Batch {{ $batch }} of {{ $totalBatches }} ({{ count($previewRows) }} rows){{ $batch < $totalBatches ? ' & Next' : '' }}
                        @else
                            Import {{ count($previewRows) }} Row(s)
                        @endif
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    function scopeImport(config) {
        return {
            importing: false,
            errorMessage: '',
            progressLabel: '',
            progressPct: 0,

            async run() {
                if (this.importing) return;
                this.errorMessage = '';
                this.importing = true;

                const form = this.$el;

                // Collect the distinct row indices present in this batch's form.
                const rowIndices = new Set();
                form.querySelectorAll('[name^="rows["]').forEach(el => {
                    const m = el.name.match(/^rows\[(\d+)\]/);
                    if (m) rowIndices.add(parseInt(m[1], 10));
                });
                const indices = Array.from(rowIndices).sort((a, b) => a - b);

                if (indices.length === 0) {
                    this.importing = false;
                    this.errorMessage = 'No rows to import.';
                    return;
                }

                // Slice into sub-chunks so each POST stays under max_input_vars.
                const chunks = [];
                for (let i = 0; i < indices.length; i += config.chunkSize) {
                    chunks.push(indices.slice(i, i + config.chunkSize));
                }

                let importedTotal = 0;
                let lastResponse = null;

                for (let c = 0; c < chunks.length; c++) {
                    const isLastChunk = (c === chunks.length - 1);
                    this.progressLabel = `Saving rows ${importedTotal + 1}–${importedTotal + chunks[c].length} of ${indices.length}…`;

                    const fd = new FormData();
                    fd.append('token', config.token);
                    fd.append('sheet', config.sheet);
                    fd.append('batch', config.batch);
                    fd.append('total_batches', config.totalBatches);
                    fd.append('is_last_chunk', isLastChunk ? '1' : '0');

                    // Copy every input belonging to the rows in this chunk.
                    chunks[c].forEach(idx => {
                        form.querySelectorAll(`[name^="rows[${idx}]"]`).forEach(el => {
                            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
                            fd.append(el.name, el.value);
                        });
                    });

                    let res;
                    try {
                        res = await fetch(config.commitUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': config.csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: fd,
                        });
                    } catch (e) {
                        this.importing = false;
                        this.errorMessage = `Network error after importing ${importedTotal} row(s). Imported rows are saved. Re-running the import may create duplicates.`;
                        return;
                    }

                    if (!res.ok) {
                        this.importing = false;
                        const chunkNo = c + 1;
                        this.errorMessage = `Import failed at chunk ${chunkNo} after ${importedTotal} row(s) saved. Fix the data and retry — note: already-imported rows may duplicate on a full re-run.`;
                        return;
                    }

                    lastResponse = await res.json();
                    importedTotal += (lastResponse.imported || 0);
                    this.progressPct = Math.round(((c + 1) / chunks.length) * 100);
                }

                // Batch fully committed — navigate.
                if (lastResponse && lastResponse.next_batch_url) {
                    this.progressLabel = 'Loading next batch…';
                    window.location = lastResponse.next_batch_url;
                } else if (lastResponse && lastResponse.done_url) {
                    this.progressLabel = 'Finishing…';
                    window.location = lastResponse.done_url + '?imported=1';
                } else {
                    // Single batch, no navigation target — go to the list.
                    window.location = '{{ route('scope-review.index') }}?imported=1';
                }
            }
        }
    }
</script>
@endsection
