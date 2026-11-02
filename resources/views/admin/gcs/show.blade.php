@extends('components.layouts.app')

@section('content')
@php
    $tabs = [
        'overview'  => ['Overview', null],
        'proposals' => ['Proposals', $proposals->count()],
        'projects'  => ['Projects', $projects->count()],
        'emails'    => ['Emails', $emails->count()],
        'contacts'  => ['Contacts', $contacts->count()],
    ];
    $activeTab = array_key_exists(request('tab'), $tabs) ? request('tab') : 'overview';
    // Contact form failed validation → reopen it on the Contacts tab
    if (old('_contact_form')) {
        $activeTab = 'contacts';
    }
    $hasRange = request()->filled('from') || request()->filled('to');
@endphp

{{-- Scoped CSS: the compiled Tailwind bundle may not include every utility used here. --}}
<style>
    [x-cloak] { display: none !important; }
    .gc-page { padding-left: 0.75rem; padding-right: 0.75rem; }
    .gc-tabs { display: flex; gap: 0.25rem; border-bottom: 1px solid #e5e7eb; overflow-x: auto; }
    .gc-tab { padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 500; color: #6b7280; border-bottom: 2px solid transparent; margin-bottom: -1px; white-space: nowrap; }
    .gc-tab:hover { color: #111827; }
    .gc-tab.active { color: #2563eb; border-bottom-color: #2563eb; }
    .gc-tab .count { margin-left: 0.35rem; padding: 0 0.45rem; border-radius: 9999px; background: #f3f4f6; font-size: 0.75rem; color: #4b5563; }
    .gc-tab.active .count { background: #dbeafe; color: #1d4ed8; }
    .gc-table { width: 100%; border-collapse: collapse; }
    .gc-table th, .gc-table td { white-space: nowrap; padding: 0.4rem 0.5rem; text-align: left; vertical-align: top; }
    .gc-table thead th { background: #f9fafb; font-size: 0.7rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.03em; }
    .gc-table thead tr.filters th { padding-top: 0; text-transform: none; }
    .gc-table tbody td { font-size: 0.8rem; color: #111827; border-top: 1px solid #f3f4f6; }
    .gc-table tbody tr:hover td { background: #f9fafb; }
    .gc-table .wrap { white-space: normal; min-width: 12rem; max-width: 22rem; }
    .gc-table .preview { white-space: normal; min-width: 18rem; max-width: 28rem; color: #4b5563; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer; }
    .gc-table .preview.open { -webkit-line-clamp: unset; }
    .gc-filter { width: 100%; min-width: 5rem; padding: 0.2rem 0.35rem; font-size: 0.75rem; font-weight: 400; border: 1px solid #d1d5db; border-radius: 0.25rem; background: #fff; color: #111827; }
    .gc-stat-table td, .gc-stat-table th { padding: 0.4rem 0.75rem; text-align: right; font-size: 0.85rem; }
    .gc-stat-table th:first-child, .gc-stat-table td:first-child { text-align: left; }
    .gc-stat-table thead th { font-size: 0.7rem; text-transform: uppercase; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
    .gc-stat-table tbody tr + tr td { border-top: 1px solid #f3f4f6; }
    .gc-stat-table tr.total td { font-weight: 600; border-top: 1px solid #e5e7eb; }
    .gc-stat-link { color: inherit; text-decoration: none; border-bottom: 1px dashed transparent; }
    .gc-stat-link:hover { color: #2563eb; border-bottom-color: #2563eb; cursor: pointer; }
</style>

<div class="py-6 gc-page"
     x-data="{ tab: @js($activeTab) }"
     x-init="$watch('tab', t => { const u = new URL(location.href); u.searchParams.set('tab', t); history.replaceState(null, '', u); })">

    {{-- Header --}}
    <div class="bg-white shadow-sm sm:rounded-lg mb-4 p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center">
            <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                {{ $gc->initials() }}
            </div>
            <div class="ml-3">
                <h2 class="text-xl font-semibold text-gray-900">{{ $gc->name }}</h2>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    @if($gc->company)<span>{{ $gc->company }}</span>@endif
                    @if($gc->is_active)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.gcs.edit', $gc) }}" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-3 rounded">Edit GC</a>
            <a href="{{ route('admin.projects.create', ['gc' => $gc->name]) }}" class="bg-purple-500 hover:bg-purple-700 text-white text-sm font-semibold py-2 px-3 rounded">New Project</a>
            <a href="{{ route('admin.gcs.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white text-sm font-semibold py-2 px-3 rounded">Back to GCs</a>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    {{-- Date range: applies to every tab (and the stats) --}}
    <form method="GET" action="{{ route('admin.gcs.show', $gc) }}"
          class="bg-white shadow-sm sm:rounded-lg mb-4 px-4 py-3 flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" :value="tab">
        <div>
            <label for="from" class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
        </div>
        <div>
            <label for="to" class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-md">Apply</button>
        @if($hasRange)
            <a :href="'{{ route('admin.gcs.show', $gc) }}?tab=' + tab" class="text-sm text-gray-600 hover:text-gray-900 underline py-1.5">Clear dates</a>
        @endif
        <p class="text-xs text-gray-500 py-1.5">Proposals &amp; stats by submitted date · Emails by sent date · Projects by due date</p>
    </form>

    {{-- Tabs --}}
    <div class="bg-white shadow-sm sm:rounded-lg">
        <nav class="gc-tabs px-2">
            @foreach($tabs as $key => [$label, $count])
                <button type="button" class="gc-tab" :class="{ 'active': tab === '{{ $key }}' }" @click="tab = '{{ $key }}'">
                    {{ $label }}@if(!is_null($count))<span class="count">{{ $count }}</span>@endif
                </button>
            @endforeach
        </nav>

        <div class="p-4">
            <div x-show="tab === 'overview'" @if($activeTab !== 'overview') x-cloak @endif>
                @include('admin.gcs.partials.tab-overview')
            </div>
            <div x-show="tab === 'proposals'" @if($activeTab !== 'proposals') x-cloak @endif>
                @include('admin.gcs.partials.tab-proposals')
            </div>
            <div x-show="tab === 'projects'" @if($activeTab !== 'projects') x-cloak @endif>
                @include('admin.gcs.partials.tab-projects')
            </div>
            <div x-show="tab === 'emails'" @if($activeTab !== 'emails') x-cloak @endif>
                @include('admin.gcs.partials.tab-emails')
            </div>
            <div x-show="tab === 'contacts'" @if($activeTab !== 'contacts') x-cloak @endif>
                @include('admin.gcs.partials.tab-contacts')
            </div>
        </div>
    </div>
</div>

<script>
    /*
     * Per-column filters for the tab tables. In a table's header, an input/select with
     * data-filter-col="N" filters on column N: inputs match "contains", selects match the
     * whole value. A cell's data-value (if set) is matched instead of its text.
     */
    window.gcTableFilter = {
        apply(table) {
            const filters = [...table.querySelectorAll('[data-filter-col]')]
                .map(el => ({ col: +el.dataset.filterCol, exact: el.tagName === 'SELECT', value: el.value.trim().toLowerCase() }))
                .filter(f => f.value !== '');
            let shown = 0;
            table.querySelectorAll('tbody tr[data-row]').forEach(tr => {
                const ok = filters.every(f => {
                    const td = tr.children[f.col];
                    const v = ((td && (td.dataset.value ?? td.textContent)) || '').trim().toLowerCase().replace(/\s+/g, ' ');
                    return f.exact ? v === f.value : v.includes(f.value);
                });
                tr.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            const scope = table.closest('[data-filter-scope]');
            const total = table.querySelectorAll('tbody tr[data-row]').length;
            const count = scope.querySelector('[data-filter-count]');
            if (count) count.textContent = filters.length ? `Showing ${shown} of ${total}` : `${total} total`;
            const empty = table.querySelector('tr[data-empty-filtered]');
            if (empty) empty.style.display = (total && !shown) ? '' : 'none';
            const clear = scope.querySelector('[data-filter-clear]');
            if (clear) clear.style.display = filters.length ? '' : 'none';
        },
        clear(scope) {
            scope.querySelectorAll('[data-filter-col]').forEach(el => el.value = '');
            scope.querySelectorAll('table').forEach(t => this.apply(t));
        },
        // Used by the Overview stats: open a tab with some filters preset.
        set(tab, values) {
            const scope = document.querySelector(`[data-filter-scope="${tab}"]`);
            if (!scope) return;
            scope.querySelectorAll('[data-filter-col]').forEach(el => el.value = '');
            Object.entries(values).forEach(([name, v]) => {
                const el = scope.querySelector(`[data-filter-name="${name}"]`);
                if (el) el.value = v;
            });
            scope.querySelectorAll('table').forEach(t => this.apply(t));
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-filter-scope] table').forEach(table => {
            table.querySelectorAll('[data-filter-col]').forEach(el =>
                el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', () => gcTableFilter.apply(table)));
            gcTableFilter.apply(table);
        });
        document.querySelectorAll('[data-filter-clear]').forEach(btn =>
            btn.addEventListener('click', () => gcTableFilter.clear(btn.closest('[data-filter-scope]'))));
    });
</script>

@include('admin.proposals.partials.email-preview-modal')
@endsection
