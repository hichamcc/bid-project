@extends('components.layouts.app')

@section('content')
<div class="py-6">
    {{-- Minimal side gutters so the wide table shows as many columns as possible. --}}
    <div class="max-w-10xl mx-auto" style="padding-left: 0.5rem; padding-right: 0.5rem;">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Proposals Management</h2>
                    <button type="button" onclick="pnForm.openCreate()"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Proposal
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @php
            $filterParams = ['search', 'job_type', 'submission_date', 'status', 'project', 'gc', 'other_gc',
                             'first_follow_up', 'second_follow_up', 'third_follow_up', 'fourth_follow_up',
                             'result_gc', 'result_art', 'notes', 'responded'];
            $followUps = ['first' => 'Follow-Up 1', 'second' => 'Follow-Up 2', 'third' => 'Follow-Up 3', 'fourth' => 'Follow-Up 4'];
        @endphp

        {{-- Spreadsheet-style table: one line per proposal, a filter under each column header.
             Scoped CSS because the compiled Tailwind bundle may not include every utility needed. --}}
        <style>
            .proposal-table th, .proposal-table td { white-space: nowrap; padding-left: 0.5rem; padding-right: 0.5rem; }
            .proposal-table tbody { font-size: 0.75rem; line-height: 1rem; }
            .proposal-table tbody td { padding-top: 0.375rem; padding-bottom: 0.375rem; }
            .proposal-table .col-filter input,
            .proposal-table .col-filter select { width: 100%; min-width: 6rem; font-size: 0.75rem; padding: 0.25rem 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; background: #fff; font-weight: normal; text-transform: none; letter-spacing: normal; }
            .proposal-table .col-filter select { padding-right: 1.75rem; }
            .proposal-table .cell-truncate { display: block; overflow: hidden; text-overflow: ellipsis; }
            /* Responded filter: small segmented control next to the submitted-date filter. */
            .proposal-table .responded-toggle { display: inline-flex; border: 1px solid #d1d5db; border-radius: 0.25rem; overflow: hidden; background: #fff; flex-shrink: 0; }
            .proposal-table .responded-toggle button { display: inline-flex; align-items: center; justify-content: center; height: 1.6rem; min-width: 1.6rem; padding: 0 0.35rem; font-size: 0.7rem; font-weight: 500; color: #4b5563; text-transform: none; letter-spacing: normal; }
            .proposal-table .responded-toggle button + button { border-left: 1px solid #d1d5db; }
            .proposal-table .responded-toggle button:hover { background: #f3f4f6; }
            .proposal-table .responded-toggle button.is-active { background: #dbeafe; color: #1e40af; }
            .proposal-table .responded-toggle .dot { width: 0.6rem; height: 0.6rem; border-radius: 9999px; }
            .proposal-table .responded-toggle .dot-green { background: #22c55e; }
            .proposal-table .responded-toggle .dot-red { background: #ef4444; }
            /* Project / GC show their full text and may wrap onto extra lines. */
            .proposal-table td.cell-wrap { white-space: normal; max-width: 22rem; word-break: break-word; }
            /* Actions column stays pinned to the right while the table scrolls sideways. */
            /* Project sticks to the left edge once scrolled to it; Actions stays pinned right. */
            .proposal-table .col-project,
            .proposal-table .col-actions { position: sticky; z-index: 1; background: #fff; }
            .proposal-table .col-project { left: 0; box-shadow: 4px 0 6px -4px rgba(0, 0, 0, 0.15); }
            .proposal-table .col-actions { right: 0; box-shadow: -4px 0 6px -4px rgba(0, 0, 0, 0.15); }
            .proposal-table thead tr:first-child .col-project,
            .proposal-table thead tr:first-child .col-actions { background: #f9fafb; }
            .proposal-table thead tr.col-filter .col-project,
            .proposal-table thead tr.col-filter .col-actions { background: #f3f4f6; }
            .proposal-table tbody tr:hover .col-project,
            .proposal-table tbody tr:hover .col-actions { background: #f9fafb; }
        </style>

        <!-- Filters (column filters in the table header submit this same form) -->
        <form id="proposalFilters" method="GET" action="{{ route('admin.proposals.index') }}"
              class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4 flex flex-wrap gap-3 items-end">
            @if(request('sort_by'))
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="sort_direction" value="{{ request('sort_direction', 'desc') }}">
            @endif
            <div class="flex-1" style="min-width: 16rem;">
                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Search by project name, GC, or job number..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Apply Filters
                </button>
                @if(request()->hasAny($filterParams))
                    <a href="{{ route('admin.proposals.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <!-- Results Summary -->
        @if(request()->hasAny($filterParams))
            <div class="bg-blue-50 border border-blue-200 px-4 py-3 rounded mb-6">
                <p class="text-blue-800">
                    Showing {{ $proposals->total() }} filtered result(s)
                    @if(request('search'))
                        for search: "<strong>{{ request('search') }}</strong>"
                    @endif
                </p>
            </div>
        @endif

        <!-- Proposals Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
             x-data="{
                selected: [],
                bulkEditOpen: false,
                toggleAll(event) {
                    this.selected = event.target.checked
                        ? Array.from(document.querySelectorAll('[data-row-checkbox]')).map(cb => cb.value)
                        : [];
                },
                get allSelected() {
                    const boxes = document.querySelectorAll('[data-row-checkbox]');
                    return boxes.length > 0 && this.selected.length === boxes.length;
                },
             }">
            <!-- Bulk action toolbar -->
            <div x-show="selected.length > 0" x-cloak
                 class="flex flex-wrap items-center justify-between gap-4 px-4 py-3 bg-blue-50 border-b border-blue-200">
                <span class="text-sm font-medium text-blue-800">
                    <span x-text="selected.length"></span> selected
                </span>
                <div class="flex items-center gap-3">
                    <button type="button" @click="selected = []" class="text-sm text-gray-600 hover:text-gray-900">
                        Clear
                    </button>
                    <button type="button" @click="bulkEditOpen = true"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                </div>
            </div>

            <!-- Bulk edit popup: only fields changed from "Keep current" are applied -->
            <div x-show="bulkEditOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="bulkEditOpen = false">
                <div class="fixed inset-0 bg-black opacity-50" @click="bulkEditOpen = false"></div>
                <form method="POST" action="{{ route('admin.proposals.bulk-update') }}"
                      class="relative bg-white rounded-lg shadow-xl mx-auto mt-24 w-full max-w-md">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">
                            Edit <span x-text="selected.length"></span> proposal<span x-show="selected.length !== 1">s</span>
                        </h3>
                        <button type="button" @click="bulkEditOpen = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label for="bulk_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="bulk_status" name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white">
                                <option value="">— Keep current —</option>
                                @foreach(\App\Models\Proposal::STATUSES as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                @endforeach
                                <option value="none">Not set</option>
                            </select>
                        </div>
                        @foreach(['result_gc' => 'GC Result', 'result_art' => 'ART Result'] as $resultField => $resultLabel)
                            <div>
                                <label for="bulk_{{ $resultField }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $resultLabel }}</label>
                                <select id="bulk_{{ $resultField }}" name="{{ $resultField }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white">
                                    <option value="">— Keep current —</option>
                                    <option value="pending">Pending</option>
                                    <option value="win">Win</option>
                                    <option value="loss">Loss</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-3 px-5 py-3 border-t border-gray-200">
                        <button type="button" @click="bulkEditOpen = false"
                                class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                            Apply to selected
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="proposal-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-3 py-3" style="width: 2rem;">
                                <input type="checkbox" title="Select all on this page"
                                       :checked="allSelected" @change="toggleAll($event)"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'submission_date', 'label' => 'Submitted Date'])</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'status', 'label' => 'Status'])</th>
                            <th class="col-project px-3 py-3">Project</th>
                            <th class="px-3 py-3">Type</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'gc', 'label' => 'GC'])</th>
                            <th class="px-3 py-3">Other GCs</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'price_original', 'label' => 'ARTELYE Price'])</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'price_ve', 'label' => 'ARTELYE VE Price'])</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'gc_price', 'label' => 'GC Price'])</th>
                            @foreach($followUps as $nth => $label)
                                <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => "{$nth}_follow_up_date", 'label' => $label])</th>
                            @endforeach
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'result_gc', 'label' => 'GC Result'])</th>
                            <th class="px-3 py-3">@include('admin.proposals.partials.sort-header', ['field' => 'result_art', 'label' => 'ART Result'])</th>
                            <th class="px-3 py-3">Notes</th>
                            <th class="col-actions px-3 py-3 text-right">Actions</th>
                        </tr>
                        {{-- Column filters: text inputs submit on Enter, selects/dates on change. --}}
                        <tr class="col-filter bg-gray-100">
                            <th class="px-3 py-2"></th>
                            <th class="px-3 py-2">
                                <div style="display: flex; gap: 0.25rem; align-items: center;">
                                    <input type="date" name="submission_date" form="proposalFilters" value="{{ request('submission_date') }}" onchange="this.form.submit()">
                                    {{-- Compact responded filter matching the date colour: All / green = responded / red = not responded.
                                         The hidden input keeps the current value when the form is submitted by another filter. --}}
                                    <input type="hidden" name="responded" form="proposalFilters" value="{{ request('responded') }}">
                                    <div class="responded-toggle">
                                        @foreach(['' => ['All', 'All'], 'yes' => ['', 'Responded'], 'no' => ['', 'Not responded']] as $respondedValue => [$respondedText, $respondedTitle])
                                            <button type="submit" form="proposalFilters" name="responded" value="{{ $respondedValue }}"
                                                    title="{{ $respondedTitle }}"
                                                    class="{{ (string) request('responded') === $respondedValue ? 'is-active' : '' }}">
                                                @if($respondedValue === '')
                                                    {{ $respondedText }}
                                                @else
                                                    <span class="dot {{ $respondedValue === 'yes' ? 'dot-green' : 'dot-red' }}"></span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </th>
                            <th class="px-3 py-2">
                                <select name="status" form="proposalFilters" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="none" {{ request('status') === 'none' ? 'selected' : '' }}>Not set</option>
                                    @foreach(\App\Models\Proposal::STATUSES as $statusValue => $statusLabel)
                                        <option value="{{ $statusValue }}" {{ request('status') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="col-project px-3 py-2">
                                <input type="text" name="project" form="proposalFilters" value="{{ request('project') }}" placeholder="filter">
                            </th>
                            <th class="px-3 py-2">
                                <select name="job_type" form="proposalFilters" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="MULTIUNIT" {{ request('job_type') == 'MULTIUNIT' ? 'selected' : '' }}>MU</option>
                                    <option value="NON MU" {{ request('job_type') == 'NON MU' ? 'selected' : '' }}>NON MU</option>
                                </select>
                            </th>
                            <th class="px-3 py-2">
                                <select name="gc" form="proposalFilters" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($gcOptions as $gcOption)
                                        <option value="{{ $gcOption }}" {{ request('gc') === $gcOption ? 'selected' : '' }}>{{ $gcOption }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-3 py-2">
                                <select name="other_gc" form="proposalFilters" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($otherGcOptions as $gcOption)
                                        <option value="{{ $gcOption }}" {{ request('other_gc') === $gcOption ? 'selected' : '' }}>{{ $gcOption }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-3 py-2"></th>
                            <th class="px-3 py-2"></th>
                            <th class="px-3 py-2"></th>
                            @foreach($followUps as $nth => $label)
                                <th class="px-3 py-2">
                                    <select name="{{ $nth }}_follow_up" form="proposalFilters" onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <option value="yes" {{ request("{$nth}_follow_up") === 'yes' ? 'selected' : '' }}>Responded</option>
                                        <option value="no" {{ request("{$nth}_follow_up") === 'no' ? 'selected' : '' }}>No response</option>
                                        <option value="none" {{ request("{$nth}_follow_up") === 'none' ? 'selected' : '' }}>Not sent</option>
                                    </select>
                                </th>
                            @endforeach
                            @foreach(['result_gc', 'result_art'] as $resultField)
                                <th class="px-3 py-2">
                                    <select name="{{ $resultField }}" form="proposalFilters" onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <option value="pending" {{ request($resultField) === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="win" {{ request($resultField) === 'win' ? 'selected' : '' }}>Win</option>
                                        <option value="loss" {{ request($resultField) === 'loss' ? 'selected' : '' }}>Loss</option>
                                    </select>
                                </th>
                            @endforeach
                            <th class="px-3 py-2">
                                <input type="text" name="notes" form="proposalFilters" value="{{ request('notes') }}" placeholder="filter">
                            </th>
                            <th class="col-actions px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-gray-900">
                        @forelse($proposals as $proposal)
                            @php
                                $project = $proposal->project;
                                $otherGcNames = $project->other_gc ? array_keys($project->other_gc) : [];
                            @endphp
                            <tr class="hover:bg-gray-50" :class="selected.includes('{{ $proposal->id }}') ? 'bg-blue-50' : ''">
                                <td class="px-3 py-2">
                                    <input type="checkbox" data-row-checkbox value="{{ $proposal->id }}" x-model="selected"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-3 py-2">
                                    {{-- Green = GC confirmed they received the proposal, red = not confirmed yet. --}}
                                    @if($proposal->submission_date)
                                        <span class="px-2 py-0.5 text-xs rounded {{ $proposal->responded === 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                                              title="{{ $proposal->responded === 'yes' ? 'GC confirmed receipt' : 'GC has not confirmed receipt' }}">
                                            {{ $proposal->submission_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @include('admin.proposals.partials.status-badge', ['status' => $proposal->status])
                                </td>
                                <td class="col-project cell-wrap px-3 py-2 font-medium" style="min-width: 14rem;"
                                    title="{{ $proposal->job_number ? 'Job #' . $proposal->job_number : '' }}">
                                    {{ $project->name }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($project->type === 'MULTIUNIT')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800">MU</span>
                                    @elseif($project->type)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">NON MU</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="cell-wrap px-3 py-2" style="min-width: 10rem;">
                                    {{ $project->gc ?: '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <span class="cell-truncate" style="max-width: 12rem;" title="{{ implode(', ', $otherGcNames) }}">
                                        {{ $otherGcNames ? implode(', ', $otherGcNames) : '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    {{ $proposal->price_original ? '$' . number_format($proposal->price_original, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $proposal->price_ve ? '$' . number_format($proposal->price_ve, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $proposal->gc_price ? '$' . number_format($proposal->gc_price, 2) : '-' }}
                                </td>
                                @foreach($followUps as $nth => $label)
                                    <td class="px-3 py-2">
                                        @include('admin.proposals.partials.follow-up-cell', [
                                            'date' => $proposal->{"{$nth}_follow_up_date"},
                                            'respond' => $proposal->{"{$nth}_follow_up_respond"},
                                            'attachment' => $proposal->{"{$nth}_follow_up_attachment"},
                                        ])
                                    </td>
                                @endforeach
                                <td class="px-3 py-2">
                                    @include('admin.proposals.partials.result-badge', ['result' => $proposal->result_gc])
                                </td>
                                <td class="px-3 py-2">
                                    @include('admin.proposals.partials.result-badge', ['result' => $proposal->result_art])
                                </td>
                                <td class="px-3 py-2">
                                    <button type="button" class="text-left hover:text-blue-700" data-notes-cell="{{ $proposal->id }}"
                                            onclick="pnNotes.open({{ $proposal->id }}, @js($proposal->project->name))">
                                        @include('admin.proposals.partials.notes-cell', ['proposal' => $proposal])
                                    </button>
                                </td>
                                <td class="col-actions px-3 py-2 text-right font-medium">
                                    <div class="flex justify-end space-x-1">
                                        <a href="{{ route('admin.proposals.show', $proposal) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-full transition-colors"
                                           title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.proposals.edit', $proposal) }}"
                                           onclick="event.preventDefault(); pnForm.openEdit(this.href)"
                                           class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-full transition-colors"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.proposals.destroy', $proposal) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this proposal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-full transition-colors"
                                                    title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="19" class="px-6 py-4 text-center text-gray-500">
                                    No proposals found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($proposals->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $proposals->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Notes popup -->
<div id="pnNotesModal" class="fixed inset-0 z-50 hidden" onclick="if (event.target === this) pnNotes.close()">
    <div class="absolute inset-0 bg-black opacity-50" style="pointer-events: none;"></div>
    <div class="relative bg-white rounded-lg shadow-xl mx-auto mt-24 w-full max-w-lg">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900 truncate">Notes — <span id="pnNotesTitle"></span></h3>
            <button type="button" onclick="pnNotes.close()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div id="pnNotesBody" class="p-5"></div>
    </div>
</div>

<script>
    // Plain-JS notes popup for the proposals list (same approach as srNotes on Scope Review).
    window.pnNotes = {
        modal() { return document.getElementById('pnNotesModal'); },
        body() { return document.getElementById('pnNotesBody'); },
        async request(url, method, payload) {
            const res = await fetch(url, {
                method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'text/html',
                },
                body: payload ? JSON.stringify(payload) : null,
            });
            if (!res.ok) throw new Error('bad status ' + res.status);
            this.body().innerHTML = await res.text();
            this.syncCell();
        },
        // Copy the refreshed notes cell from the thread response into the table row.
        syncCell() {
            const widget = this.body().querySelector('.pn-notes');
            const tpl = widget && widget.querySelector('template[data-notes-cell]');
            const cell = widget && document.querySelector('[data-notes-cell="' + widget.dataset.proposalId + '"]');
            if (tpl && cell) cell.innerHTML = tpl.innerHTML;
        },
        async open(proposalId, title) {
            document.getElementById('pnNotesTitle').textContent = title;
            this.body().innerHTML = '<p class="text-sm text-gray-400">Loading...</p>';
            this.modal().classList.remove('hidden');
            try {
                await this.request('{{ url('admin/proposals') }}/' + proposalId + '/notes', 'GET', null);
                const ta = this.body().querySelector('[data-note-new]');
                if (ta) ta.focus();
            } catch (e) {
                this.body().innerHTML = '<p class="text-sm text-red-600">Could not load notes.</p>';
            }
        },
        close() { this.modal().classList.add('hidden'); },
        async add(el) {
            const widget = el.closest('.pn-notes');
            const body = (widget.querySelector('[data-note-new]').value || '').trim();
            if (!body) return;
            try { await this.request(widget.dataset.storeUrl, 'POST', { body }); }
            catch (e) { alert('Could not save the note. Please try again.'); }
        },
        async destroy(el, id) {
            if (!confirm('Delete this note?')) return;
            const widget = el.closest('.pn-notes');
            try { await this.request(widget.dataset.baseUrl + '/' + id, 'DELETE', null); }
            catch (e) { alert('Could not delete the note. Please try again.'); }
        },
    };
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') pnNotes.close(); });
</script>

<!-- Create / Edit proposal popup. Holds one form at a time (both forms use the same
     element ids), fetched from the create / edit route each time it opens.
     Forms post normally so attachments upload; a failed submit redirects back here and
     the server re-renders that form inside the popup with the entered values and errors. -->
@php
    $reopenModal = old('_modal');
    $reopenProposal = $reopenModal === 'edit' ? \App\Models\Proposal::find(old('_proposal_id')) : null;
@endphp
<div id="pnFormModal" class="fixed inset-0 z-50 {{ $reopenModal ? '' : 'hidden' }} overflow-y-auto" onclick="if (event.target === this) pnForm.close()">
    <div class="fixed inset-0 bg-black opacity-50" style="pointer-events: none;"></div>
    <div class="relative bg-white rounded-lg shadow-xl mx-auto my-8 w-full max-w-4xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 id="pnFormTitle" class="text-lg font-semibold text-gray-900">{{ $reopenProposal ? 'Edit Proposal' : 'Add New Proposal' }}</h3>
            <div class="flex items-center gap-4">
                <a id="pnFormFullPage" href="{{ $reopenProposal ? route('admin.proposals.edit', $reopenProposal) : route('admin.proposals.create') }}"
                   class="text-sm text-blue-600 hover:text-blue-800">Open full page</a>
                <button type="button" onclick="pnForm.close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
        </div>
        <div id="pnFormBody" class="p-6">
            @if($reopenModal && session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif
            @if($reopenModal === 'create')
                @include('admin.proposals.partials.create-modal-form')
            @elseif($reopenProposal)
                @include('admin.proposals.partials.edit-modal-form', ['proposal' => $reopenProposal])
            @endif
        </div>
    </div>
</div>

@include('admin.proposals.partials.proposal-form-script')

<script>
    window.pnForm = {
        modal() { return document.getElementById('pnFormModal'); },
        body() { return document.getElementById('pnFormBody'); },
        show(title, fullPageUrl) {
            document.getElementById('pnFormTitle').textContent = title;
            document.getElementById('pnFormFullPage').href = fullPageUrl;
            this.modal().classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        },
        openCreate() {
            this.load('Add New Proposal', '{{ route('admin.proposals.create') }}');
        },
        openEdit(editUrl) {
            this.load('Edit Proposal', editUrl);
        },
        async load(title, url) {
            this.body().innerHTML = '<p class="text-sm text-gray-400">Loading...</p>';
            this.show(title, url);
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
                if (!res.ok) throw new Error('bad status ' + res.status);
                this.body().innerHTML = await res.text();
                initProposalForm();
            } catch (e) {
                this.body().innerHTML = '<p class="text-sm text-red-600">Could not load the form.</p>';
            }
        },
        close() {
            this.modal().classList.add('hidden');
            this.body().innerHTML = '';
            document.body.style.overflow = '';
        },
    };
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !pnForm.modal().classList.contains('hidden')) pnForm.close(); });
    @if($reopenModal)
        document.body.style.overflow = 'hidden';
    @endif
</script>
@endsection
