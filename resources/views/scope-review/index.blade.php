@extends('components.layouts.app')

@section('content')
{{-- Notes-thread Alpine component defined globally on page load, because the
     partial's markup is injected into the modal via innerHTML (where an inline
     <script> would never execute). --}}
<script>
    // Plain-JS notes controller (no Alpine) so buttons can't double-bind when the
    // notes partial is injected into the modal via innerHTML. Each button passes
    // `this`; we walk up to the enclosing .sr-notes widget for its data attributes.
    window.srNotes = {
        root(el) { return el.closest('.sr-notes'); },
        async send(el, url, method, body) {
            const widget = this.root(el);
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-Scope-Modal': '1',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': widget.dataset.csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'text/html',
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                if (!res.ok) throw new Error('bad status ' + res.status);
                widget.outerHTML = await res.text();
            } catch (e) {
                alert('Could not save the note. Please try again.');
            }
        },
        add(el) {
            const widget = this.root(el);
            const ta = widget.querySelector('[data-note-new]');
            const body = (ta.value || '').trim();
            if (!body) return;
            this.send(el, widget.dataset.storeUrl, 'POST', { body });
        },
        startEdit(el, id) {
            const widget = this.root(el);
            widget.querySelector('[data-note-read="' + id + '"]').classList.add('hidden');
            widget.querySelector('[data-note-edit="' + id + '"]').classList.remove('hidden');
        },
        cancelEdit(el, id) {
            const widget = this.root(el);
            widget.querySelector('[data-note-edit="' + id + '"]').classList.add('hidden');
            widget.querySelector('[data-note-read="' + id + '"]').classList.remove('hidden');
        },
        saveEdit(el, id) {
            const widget = this.root(el);
            const body = (widget.querySelector('[data-note-edit-input="' + id + '"]').value || '').trim();
            if (!body) return;
            this.send(el, widget.dataset.baseUrl + '/' + id, 'PUT', { body });
        },
        destroy(el, id) {
            if (!confirm('Delete this note?')) return;
            const widget = this.root(el);
            this.send(el, widget.dataset.baseUrl + '/' + id, 'DELETE', null);
        },
    };
</script>
<div class="py-12"
     x-data="{
        panelOpen: false,
        current: null,
        openPanel(scopeReview) {
            this.current = scopeReview;
            this.panelOpen = true;
        },

        // Delete confirmation
        deleteOpen: false,
        deleteId: null,
        deleteName: '',
        confirmDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.deleteOpen = true;
        },

        // Bulk selection + delete
        selected: [],
        toggleAll(event) {
            if (event.target.checked) {
                this.selected = Array.from(document.querySelectorAll('[data-row-checkbox]')).map(cb => cb.value);
            } else {
                this.selected = [];
            }
        },
        get allSelected() {
            const boxes = document.querySelectorAll('[data-row-checkbox]');
            return boxes.length > 0 && this.selected.length === boxes.length;
        },
        bulkDeleteOpen: false,

        // Form modal (Add / Edit)
        formOpen: false,
        formLoading: false,
        formSubmitting: false,
        formTitle: '',
        // Alpine's mutation observer (v3) auto-initialises x-data nodes added via
        // innerHTML, so we just set the HTML — no manual initTree (that would
        // double-initialise and cross-bind click handlers).
        setBody(html) {
            this.$refs.formBody.innerHTML = html;
        },
        async openForm(url, title) {
            this.formTitle = title;
            this.formOpen = true;
            this.formLoading = true;
            this.$refs.formBody.innerHTML = '';
            try {
                const res = await fetch(url, { headers: { 'X-Scope-Modal': '1', 'X-Requested-With': 'XMLHttpRequest' } });
                this.setBody(await res.text());
            } catch (e) {
                this.$refs.formBody.innerHTML = '<p class=\'text-red-600 p-4\'>Failed to load the form. Please try again.</p>';
            } finally {
                this.formLoading = false;
            }
        },
        closeForm() {
            this.formOpen = false;
            this.$refs.formBody.innerHTML = '';
        },
        async submitForm(event) {
            event.preventDefault();
            const form = event.target.closest('form[data-scope-form]');
            if (!form) return;
            this.formSubmitting = true;
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Scope-Modal': '1', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: new FormData(form),
                });
                if (res.status === 422) {
                    // Validation errors — re-render the form partial with messages.
                    this.setBody(await res.text());
                } else if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Something went wrong. Please try again.');
                }
            } catch (e) {
                alert('Network error. Please try again.');
            } finally {
                this.formSubmitting = false;
            }
        }
     }"
     @click="if ($event.target.closest('[data-scope-cancel]')) { $event.preventDefault(); closeForm(); }"
     @submit="if ($event.target.closest('form[data-scope-form]')) submitForm($event)">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Scope Review</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track all bidding opportunities.</p>
                </div>
                @if(auth()->user()->isAdmin())
                    <div class="flex gap-2">
                        <a href="{{ route('scope-review.stats') }}"
                           class="inline-flex items-center gap-1.5 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold py-2 px-4 rounded">
                            <x-phosphor-chart-bar width="16" height="16" />
                            Stats
                        </a>
                        <a href="{{ route('scope-review.import.create') }}"
                           class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded">
                            Import
                        </a>
                        <a href="{{ route('scope-review.create') }}"
                           @click.prevent="openForm('{{ route('scope-review.create') }}', 'Add Opportunity')"
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                            Add Opportunity
                        </a>
                    </div>
                @else
                    {{-- Estimator: toggle between everything and only what's assigned to them --}}
                    @php $mineActive = request()->filled('mine'); @endphp
                    <div class="inline-flex rounded-md overflow-hidden border border-gray-300 dark:border-gray-600">
                        <a href="{{ route('scope-review.index', array_merge(array_diff_key(request()->query(), ['mine' => '', 'page' => '']), [])) }}"
                           class="px-4 py-2 text-sm font-medium {{ $mineActive ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-blue-600 text-white' }}">
                            All
                        </a>
                        <a href="{{ route('scope-review.index', array_merge(array_diff_key(request()->query(), ['page' => '']), ['mine' => 1])) }}"
                           class="px-4 py-2 text-sm font-medium border-l border-gray-300 dark:border-gray-600 {{ $mineActive ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Assigned to me
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if(request()->boolean('imported'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                Import complete.
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Headline Stat Cards (admin) -->
        @if($statCards)
            {{-- One row of compact cards. grid-cols-7 isn't in the compiled bundle,
                 so the large-screen layout is done via a scoped media query. --}}
            <style>
                .stat-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; }
                @media (min-width: 640px)  { .stat-cards { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
                @media (min-width: 1024px) { .stat-cards { grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.6rem; } }
            </style>
            <div class="stat-cards">
                @foreach($statCards as $card)
                    <a href="{{ $card['href'] }}"
                       class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <div class="absolute inset-x-0 top-0 h-1 {{ $card['bg'] }}"></div>
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center mb-1.5 {{ $card['icon_bg'] }}">
                            <x-dynamic-component :component="$card['icon']" class="w-4 h-4 {{ $card['icon_color'] }}" />
                        </span>
                        <p class="text-2xl font-extrabold tracking-tight leading-none {{ $card['value_color'] }}">{{ $card['value'] }}</p>
                        <p class="text-sm font-bold uppercase text-gray-600 dark:text-gray-300 mt-1 leading-tight">{{ $card['label'] }}</p>
                    </a>
                @endforeach
            </div>
        @endif

        @if(!empty($approvedStats))
            <!-- Approved breakdown (total + MU / NON MU) -->
            <div class="mt-3">
                <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden max-w-md">
                    <div class="flex items-stretch">
                        <a href="{{ route('scope-review.index', ['decision' => 'approved']) }}"
                           class="flex-1 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <p class="text-2xl font-extrabold tracking-tight leading-none text-green-700 dark:text-green-300">{{ number_format($approvedStats['total']) }}</p>
                            <p class="text-sm font-bold uppercase text-gray-600 dark:text-gray-300 mt-1 leading-tight">Total Approved</p>
                        </a>
                        <a href="{{ route('scope-review.index', ['decision' => 'approved', 'project_type' => 'MU']) }}"
                           class="px-4 py-3 text-center border-l border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <p class="text-xl font-extrabold leading-none text-purple-700 dark:text-purple-300">{{ number_format($approvedStats['mu']) }}</p>
                            <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mt-1">MU</p>
                        </a>
                        <a href="{{ route('scope-review.index', ['decision' => 'approved', 'project_type' => 'NON_MU']) }}"
                           class="px-4 py-3 text-center border-l border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <p class="text-xl font-extrabold leading-none text-blue-700 dark:text-blue-300">{{ number_format($approvedStats['non_mu']) }}</p>
                            <p class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mt-1">NON MU</p>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Saved Views -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-2 items-center">
                @foreach($savedViews as $view)
                    <a href="{{ route('scope-review.index', $view->filters ?? []) }}"
                       class="px-3 py-1.5 text-sm rounded-md {{ $view->is_default ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-medium' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $view->name }}
                    </a>
                @endforeach
                @if($savedViews->isEmpty())
                    <span class="text-sm text-gray-400 italic">No saved views yet — filter below, then save a view for quick access.</span>
                @endif
            </div>

            <!-- Filters -->
            {{-- Filter form: no visible fields of its own; the column filters in the table header attach
                 to it via form="srFilters" (same layout as the proposals list). Nothing applies until
                 "Filter" is clicked (or Enter in a text filter, via the off-screen submit button).
                 Keeps the current sort and the toggles/cards that have no field of their own. --}}
            <form id="srFilters" method="GET" action="{{ route('scope-review.index') }}">
                @foreach(['sort', 'direction', 'mine', 'ready_for_assignment', 'unassigned', 'year', 'uploaded_in_oh', 'not_uploaded'] as $keptParam)
                    @if(request()->filled($keptParam))
                        <input type="hidden" name="{{ $keptParam }}" value="{{ request($keptParam) }}">
                    @endif
                @endforeach
                <button type="submit" tabindex="-1" aria-hidden="true" style="position: absolute; left: -9999px;">Filter</button>
            </form>
            <script>
                // Filters are only applied by the "Filter" button; this just tracks unapplied changes.
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('[form="srFilters"]:not(button)').forEach(el => {
                        el.addEventListener(el.tagName === 'SELECT' || el.type === 'date' ? 'change' : 'input', () => {
                            document.querySelectorAll('[data-sr-filter-apply]').forEach(b => b.classList.add('is-dirty'));
                        });
                    });
                });
            </script>
            @php
                $srFilterParams = ['project_name', 'project_number', 'location', 'source', 'platform', 'project_type', 'decision', 'assigned_estimator_id', 'ready_for_assignment', 'unassigned', 'mine', 'reason_to_ignore', 'entry_date', 'due_date', 'uploaded_in_oh', 'not_uploaded', 'bid_stage'];
            @endphp

            <!-- List -->
            @php
                $currentSort = request('sort');
                $currentDir  = strtolower(request('direction')) === 'asc' ? 'asc' : 'desc';
                // Build a sortable-header link: clicking toggles asc/desc, preserving other query params.
                $sortLink = function (string $key) use ($currentSort, $currentDir) {
                    $isActive = $currentSort === $key;
                    $nextDir  = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
                    $query    = array_merge(request()->query(), ['sort' => $key, 'direction' => $nextDir]);
                    unset($query['page']); // reset pagination when re-sorting
                    return [
                        'url'      => route('scope-review.index') . '?' . http_build_query($query),
                        'active'   => $isActive,
                        'dir'      => $currentDir,
                    ];
                };
            @endphp

            @if(auth()->user()->isAdmin())
                <!-- Bulk action toolbar -->
                <div x-show="selected.length > 0" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="flex items-center justify-between gap-4 px-6 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-900/40">
                    <span class="text-sm font-medium text-red-800 dark:text-red-200">
                        <span x-text="selected.length"></span> selected
                    </span>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="selected = []"
                                class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                            Clear
                        </button>
                        <button type="button" @click="bulkDeleteOpen = true"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                            <x-phosphor-trash width="16" height="16" />
                            Delete Selected
                        </button>
                    </div>
                </div>
            @endif

            @php $isAdminView = auth()->user()->isAdmin(); @endphp
            {{-- Freeze the first columns (checkbox + Project # + Project Name) while scrolling horizontally.
                 Sticky cells need an opaque background, so they don't show the row hover/selected tint. --}}
            <style>
                .sr-table th.sticky-col, .sr-table td.sticky-col { position: sticky; z-index: 1; background: #fff; }
                .dark .sr-table th.sticky-col, .dark .sr-table td.sticky-col { background: #1f2937; } /* gray-800 */
                .sr-table thead th.sticky-col { z-index: 3; background: #f9fafb; }                    /* gray-50 */
                .dark .sr-table thead th.sticky-col { background: #374151; }                          /* gray-700 */
                @if($isAdminView)
                    .sr-table .sticky-col-0 { left: 0;      width: 48px;  min-width: 48px; }
                    .sr-table .sticky-col-1 { left: 48px;   width: 110px; min-width: 110px; }
                    .sr-table .sticky-col-2 { left: 158px;  width: 240px; min-width: 240px; }
                    .sr-table .sticky-col-2 { box-shadow: 6px 0 6px -4px rgba(0,0,0,0.12); }
                @else
                    .sr-table .sticky-col-1 { left: 0;      width: 110px; min-width: 110px; }
                    .sr-table .sticky-col-2 { left: 110px;  width: 240px; min-width: 240px; }
                    .sr-table .sticky-col-2 { box-shadow: 6px 0 6px -4px rgba(0,0,0,0.12); }
                @endif
                /* Column filter row (same idea as the proposals list) */
                .sr-table thead tr:first-child th { white-space: nowrap; }
                .sr-table tr.sr-col-filter th { padding: 0.375rem 0.5rem; background: #f3f4f6; font-weight: normal; text-transform: none; letter-spacing: normal; }
                .dark .sr-table tr.sr-col-filter th { background: #374151; }
                .sr-table tr.sr-col-filter input,
                .sr-table tr.sr-col-filter select { width: 100%; min-width: 6rem; font-size: 0.75rem; padding: 0.25rem 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; background: #fff; color: #111827; }
                .sr-table tr.sr-col-filter select { padding-right: 1.75rem; }
                .sr-table tr.sr-col-filter input[type="date"] { min-width: 7.5rem; padding-right: 0.25rem; }
                .dark .sr-table tr.sr-col-filter input,
                .dark .sr-table tr.sr-col-filter select { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
                /* Filter / Clear stay visible at the right edge while the table scrolls sideways */
                .sr-table tr.sr-col-filter th.sr-filter-actions { position: sticky; right: 0; z-index: 2; box-shadow: -4px 0 6px -4px rgba(0,0,0,0.15); white-space: nowrap; text-align: right; }
                .sr-table .sr-filter-btn { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 500; border-radius: 0.25rem; border: 1px solid transparent; }
                .sr-table .sr-filter-apply { color: #1d4ed8; background: #eff6ff; border-color: #bfdbfe; }
                .sr-table .sr-filter-apply:hover { background: #dbeafe; }
                .sr-table .sr-filter-apply.is-dirty { color: #fff; background: #2563eb; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25); }
                .sr-table .sr-filter-clear { color: #374151; background: #e5e7eb; }
                .sr-table .sr-filter-clear:hover { background: #d1d5db; }
            </style>
            <div class="overflow-x-auto">
                <table class="sr-table min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            @if($isAdminView)
                                <th class="sticky-col sticky-col-0 px-4 py-3">
                                    <input type="checkbox" @change="toggleAll($event)" :checked="allSelected"
                                           title="Select all"
                                           class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                                </th>
                            @endif
                            @php $entryH = $sortLink('entry_date'); @endphp
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <a href="{{ $entryH['url'] }}" class="group inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-100">
                                    <span>Entry Date</span>
                                    @if($entryH['active'])
                                        @if($entryH['dir'] === 'asc')
                                            <x-phosphor-caret-up width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                        @else
                                            <x-phosphor-caret-down width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                        @endif
                                    @else
                                        <x-phosphor-caret-up-down width="12" height="12" class="text-gray-300 dark:text-gray-500 group-hover:text-gray-400" />
                                    @endif
                                </a>
                            </th>
                            @php
                                $sortableHeaders = [
                                    'project_number' => 'Project #',
                                    'project_name'   => 'Project Name',
                                    'source'         => 'Source',
                                    'platform'       => 'Platform',
                                    'location'       => 'Location',
                                    'due_date'       => 'Due',
                                ];
                            @endphp
                            @php $stickyHeaderClass = ['project_number' => 'sticky-col sticky-col-1', 'project_name' => 'sticky-col sticky-col-2']; @endphp
                            @foreach($sortableHeaders as $key => $label)
                                @php $h = $sortLink($key); @endphp
                                <th class="{{ $stickyHeaderClass[$key] ?? '' }} px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <a href="{{ $h['url'] }}" class="group inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-100">
                                        <span>{{ $label }}</span>
                                        @if($h['active'])
                                            @if($h['dir'] === 'asc')
                                                <x-phosphor-caret-up width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                            @else
                                                <x-phosphor-caret-down width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                            @endif
                                        @else
                                            <x-phosphor-caret-up-down width="12" height="12" class="text-gray-300 dark:text-gray-500 group-hover:text-gray-400" />
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estimator</th>
                            @foreach(['type' => 'Type', 'decision' => 'Decision', 'reason_to_ignore' => 'Reason to Ignore', 'bid_stage' => 'Bid Stage'] as $key => $label)
                                @php $h = $sortLink($key); @endphp
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <a href="{{ $h['url'] }}" class="group inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-100">
                                        <span>{{ $label }}</span>
                                        @if($h['active'])
                                            @if($h['dir'] === 'asc')
                                                <x-phosphor-caret-up width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                            @else
                                                <x-phosphor-caret-down width="12" height="12" class="text-gray-700 dark:text-gray-100" />
                                            @endif
                                        @else
                                            <x-phosphor-caret-up-down width="12" height="12" class="text-gray-300 dark:text-gray-500 group-hover:text-gray-400" />
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                        {{-- Column filters: nothing applies until "Filter" is clicked (or Enter in a text filter). --}}
                        @php
                            $sourceFilterOptions = \App\Models\Source::active()->ordered()->pluck('name')->all();
                            $platformFilterOptions = \App\Models\Platform::active()->ordered()->pluck('name')->all();
                            $reasonFilterOptions = \App\Models\ReasonToIgnore::active()->ordered()->pluck('name')->all();
                            $bidStageFilterOptions = \App\Models\BidStage::active()->ordered()->pluck('name')->all();
                            // Keep a currently-applied value in its list even if that option is now inactive.
                            $withCurrent = fn (array $options, string $param) =>
                                (request($param) && !in_array(request($param), $options, true)) ? array_merge($options, [request($param)]) : $options;
                        @endphp
                        <tr class="sr-col-filter">
                            @if($isAdminView)
                                <th class="sticky-col sticky-col-0"></th>
                            @endif
                            <th><input type="date" name="entry_date" form="srFilters" value="{{ request('entry_date') }}" title="Entry date"></th>
                            <th class="sticky-col sticky-col-1"><input type="text" name="project_number" form="srFilters" value="{{ request('project_number') }}" placeholder="filter"></th>
                            <th class="sticky-col sticky-col-2"><input type="text" name="project_name" form="srFilters" value="{{ request('project_name') }}" placeholder="filter"></th>
                            <th>
                                <select name="source" form="srFilters">
                                    <option value="">All</option>
                                    @foreach($withCurrent($sourceFilterOptions, 'source') as $option)
                                        <option value="{{ $option }}" {{ request('source') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select name="platform" form="srFilters">
                                    <option value="">All</option>
                                    @foreach($withCurrent($platformFilterOptions, 'platform') as $option)
                                        <option value="{{ $option }}" {{ request('platform') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th><input type="text" name="location" form="srFilters" value="{{ request('location') }}" placeholder="filter"></th>
                            <th><input type="date" name="due_date" form="srFilters" value="{{ request('due_date') }}" title="Due date"></th>
                            <th>
                                <select name="assigned_estimator_id" form="srFilters">
                                    <option value="">All</option>
                                    @foreach($estimators as $estimator)
                                        <option value="{{ $estimator->id }}" {{ request('assigned_estimator_id') == $estimator->id ? 'selected' : '' }}>{{ $estimator->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select name="project_type" form="srFilters">
                                    <option value="">All</option>
                                    <option value="MU" {{ request('project_type') === 'MU' ? 'selected' : '' }}>MU</option>
                                    <option value="NON_MU" {{ request('project_type') === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                                </select>
                            </th>
                            <th>
                                <select name="decision" form="srFilters">
                                    <option value="">All</option>
                                    @foreach(['__pending__' => 'Not Yet Reviewed', 'pending' => 'Pending', 'approved' => 'Approved', 'rfi_requested' => 'RFI Requested', 'not_in_scope' => 'Not In Scope', 'skipped' => 'Skipped'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('decision') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select name="reason_to_ignore" form="srFilters">
                                    <option value="">All</option>
                                    @foreach($withCurrent($reasonFilterOptions, 'reason_to_ignore') as $option)
                                        <option value="{{ $option }}" {{ request('reason_to_ignore') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select name="bid_stage" form="srFilters">
                                    <option value="">All</option>
                                    @foreach($withCurrent($bidStageFilterOptions, 'bid_stage') as $option)
                                        <option value="{{ $option }}" {{ request('bid_stage') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="sr-filter-actions">
                                <button type="submit" form="srFilters" class="sr-filter-btn sr-filter-apply" data-sr-filter-apply title="Apply filters">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 0.875rem; height: 0.875rem;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    Filter
                                </button>
                                @if(request()->hasAny($srFilterParams))
                                    <a href="{{ route('scope-review.index') }}" class="sr-filter-btn sr-filter-clear" title="Clear all filters">Clear</a>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($scopeReviews as $scopeReview)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                :class="selected.includes('{{ $scopeReview->id }}') ? 'bg-red-50 dark:bg-red-900/10' : ''">
                                @if($isAdminView)
                                    <td class="sticky-col sticky-col-0 px-4 py-4">
                                        @unless($scopeReview->isConverted())
                                            <input type="checkbox" data-row-checkbox value="{{ $scopeReview->id }}"
                                                   x-model="selected"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                                        @endunless
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->entry_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="sticky-col sticky-col-1 px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $scopeReview->project_number ?? '—' }}
                                </td>
                                <td class="sticky-col sticky-col-2 px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @if($scopeReview->project_link)
                                        <a href="{{ $scopeReview->project_link }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $scopeReview->project_name }}
                                        </a>
                                    @else
                                        {{ $scopeReview->project_name }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->source ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->platform ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->location ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->due_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->assignedEstimator?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($scopeReview->project_type)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-md
                                            {{ $scopeReview->project_type === 'MU' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                            {{ $scopeReview->project_type === 'NON_MU' ? 'NON MU' : 'MU' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $decisionColors = [
                                            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'rfi_requested' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'not_in_scope' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'skipped' => 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200',
                                            'pending' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        ];
                                        $decisionLabels = [
                                            'approved' => 'Approved',
                                            'rfi_requested' => 'RFI Requested',
                                            'not_in_scope' => 'Not In Scope',
                                            'skipped' => 'Skipped',
                                            'pending' => 'Pending',
                                        ];
                                    @endphp
                                    @if($scopeReview->decision)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-md {{ $decisionColors[$scopeReview->decision] }}">
                                            {{ $decisionLabels[$scopeReview->decision] }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-md bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Not Yet Reviewed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->reason_to_ignore ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->bid_stage ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button"
                                                @click="openPanel({{ json_encode([
                                                    'project_name' => $scopeReview->project_name,
                                                    'project_number' => $scopeReview->project_number,
                                                    'entry_date' => $scopeReview->entry_date->format('M d, Y'),
                                                    'source' => $scopeReview->source,
                                                    'platform' => $scopeReview->platform,
                                                    'location' => $scopeReview->location,
                                                    'due_date' => $scopeReview->due_date?->format('M d, Y'),
                                                    'project_link' => $scopeReview->project_link,
                                                    'notes' => $scopeReview->notes,
                                                    'assigned_estimator' => $scopeReview->assignedEstimator ? ['name' => $scopeReview->assignedEstimator->name] : null,
                                                    'decision' => $scopeReview->decision,
                                                    'project_type' => $scopeReview->project_type,
                                                    'bid_stage' => $scopeReview->bid_stage,
                                                    'reason_to_ignore' => $scopeReview->reason_to_ignore,
                                                    'duration' => $scopeReview->duration,
                                                    'uploaded_in_oh' => $scopeReview->uploaded_in_oh,
                                                    'intention_to_bid_email_sent' => $scopeReview->intention_to_bid_email_sent,
                                                    'not_bidding_email_sent' => $scopeReview->not_bidding_email_sent,
                                                    'estimator_notes' => $scopeReview->estimator_notes,
                                                    'additional_notes' => $scopeReview->noteEntries->map(fn($n) => [
                                                        'user' => $n->user?->name ?? 'Unknown',
                                                        'context' => $n->context,
                                                        'body' => $n->body,
                                                        'at' => $n->created_at->copy()->setTimezone('America/New_York')->format('M d, Y g:i A') . ' EST',
                                                        'edited' => $n->updated_at->gt($n->created_at),
                                                    ]),
                                                    'status_history' => $scopeReview->statusHistories->map(fn($h) => [
                                                        'field' => $h->field,
                                                        'old_value' => $h->old_value,
                                                        'new_value' => $h->new_value,
                                                        'decision' => $h->decision,
                                                        'user' => $h->user?->name,
                                                        'changed_at' => $h->created_at->copy()->setTimezone('America/New_York')->format('M d, Y g:i A') . ' EST',
                                                    ]),
                                                ]) }})"
                                                title="View"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                            <x-phosphor-eye width="16" height="16" />
                                        </button>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('scope-review.edit', $scopeReview) }}"
                                               @click.prevent="openForm('{{ route('scope-review.edit', $scopeReview) }}', @js('Edit: '.$scopeReview->project_name))"
                                               title="Edit"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 cursor-pointer">
                                                <x-phosphor-pencil-simple width="16" height="16" />
                                            </a>
                                        @elseif($scopeReview->assigned_estimator_id === auth()->id())
                                            <a href="{{ route('scope-review.edit', $scopeReview) }}"
                                               @click.prevent="openForm('{{ route('scope-review.edit', $scopeReview) }}', @js('Review: '.$scopeReview->project_name))"
                                               title="Review"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 cursor-pointer">
                                                <x-phosphor-pencil-simple width="16" height="16" />
                                            </a>
                                        @endif
                                        @if(auth()->user()->isAdmin() && $scopeReview->isAssignable())
                                            @php
                                                // Pull the first number out of the free-text duration ("1 DAY" -> 1, "2.5 days" -> 2.5)
                                                $daysRequired = preg_match('/[0-9]+(?:\.[0-9]+)?/', (string) $scopeReview->duration, $m) ? $m[0] : null;
                                            @endphp
                                            <a href="{{ route('admin.allocation.index', array_filter([
                                                    'scope_review_id' => $scopeReview->id,
                                                    'job_number' => $scopeReview->project_number,
                                                    'due_date' => optional($scopeReview->due_date)->format('Y-m-d'),
                                                    'project_name' => mb_strtoupper($scopeReview->project_name),
                                                    'job_type' => $scopeReview->project_type,
                                                    'web_link' => $scopeReview->project_link,
                                                    'days_required' => $daysRequired,
                                                    'project_status' => 'RECEIVED',
                                                ], fn($v) => $v !== null && $v !== '')) . '#job-form' }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60">
                                                <x-phosphor-user-plus width="14" height="14" />
                                                Assign
                                            </a>
                                        @elseif($scopeReview->isConverted())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-md bg-gray-50 text-gray-400 dark:bg-gray-800 dark:text-gray-500 italic">
                                                <x-phosphor-check-circle width="14" height="14" />
                                                Assigned
                                            </span>
                                        @endif
                                        @if(auth()->user()->isAdmin() && !$scopeReview->isConverted())
                                            <button type="button"
                                                    @click="confirmDelete({{ $scopeReview->id }}, @js($scopeReview->project_name))"
                                                    title="Delete"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60">
                                                <x-phosphor-trash width="16" height="16" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 14 : 13 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No scope review entries yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($scopeReviews->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $scopeReviews->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- View Side Panel -->
    <div x-show="panelOpen"
         x-cloak
         class="fixed inset-0 z-50"
         @keydown.escape.window="panelOpen = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50"
             x-show="panelOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="panelOpen = false"></div>

        <!-- Panel -->
        <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto"
             x-show="panelOpen && current"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            <div x-show="current">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="current.project_name"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="current.project_number">
                            Project #<span x-text="current.project_number"></span>
                        </p>
                    </div>
                    <button type="button" @click="panelOpen = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Admin intake details -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Details</h4>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Entry Date</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.entry_date"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Source</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.source || '—'"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Platform</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.platform || '—'"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Location</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.location || '—'"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Bid Due Date</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.due_date || '—'"></dd>
                        </div>
                        <div class="flex justify-between gap-3" x-show="current.project_link">
                            <dt class="text-gray-500 dark:text-gray-400">Project Link</dt>
                            <dd><a :href="current.project_link" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Open</a></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Assigned Estimator</dt>
                            <dd class="text-gray-900 dark:text-gray-100" x-text="current.assigned_estimator?.name || '—'"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Estimator review details -->
                <div class="p-6">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Estimator Review</h4>
                    <template x-if="current.decision">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between items-center gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Decision</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': current.decision === 'approved',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': current.decision === 'rfi_requested',
                                              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': current.decision === 'not_in_scope',
                                              'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200': current.decision === 'skipped',
                                              'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': current.decision === 'pending'
                                          }"
                                          x-text="({approved: 'Approved', rfi_requested: 'RFI Requested', not_in_scope: 'Not In Scope', skipped: 'Skipped', pending: 'Pending'})[current.decision]"></span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Project Type</dt>
                                <dd>
                                    <template x-if="current.project_type">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="current.project_type === 'MU' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'"
                                              x-text="current.project_type === 'NON_MU' ? 'NON MU' : 'MU'"></span>
                                    </template>
                                    <span x-show="!current.project_type" class="text-gray-400 text-xs">—</span>
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Bid Stage</dt>
                                <dd class="text-gray-900 dark:text-gray-100 text-right" x-text="current.bid_stage || '—'"></dd>
                            </div>
                            <div x-show="current.reason_to_ignore" class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Reason to Ignore</dt>
                                <dd class="text-gray-900 dark:text-gray-100 text-right" x-text="current.reason_to_ignore"></dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Duration</dt>
                                <dd class="text-gray-900 dark:text-gray-100" x-text="current.duration || '—'"></dd>
                            </div>
                            <div class="flex justify-between items-center gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Uploaded in OH</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="current.uploaded_in_oh ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                          x-text="current.uploaded_in_oh ? 'Yes' : 'No'"></span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center gap-3" x-show="current.intention_to_bid_email_sent">
                                <dt class="text-gray-500 dark:text-gray-400">Intention to Bid Email Sent</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Yes</span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center gap-3" x-show="current.not_bidding_email_sent">
                                <dt class="text-gray-500 dark:text-gray-400">Not Bidding Email Sent</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Yes</span>
                                </dd>
                            </div>
                        </dl>
                    </template>
                    <template x-if="!current.decision">
                        <div>
                            <div x-show="current.bid_stage" class="flex justify-between gap-3 text-sm mb-2">
                                <dt class="text-gray-500 dark:text-gray-400">Bid Stage</dt>
                                <dd class="text-gray-900 dark:text-gray-100 text-right" x-text="current.bid_stage"></dd>
                            </div>
                            <div x-show="current.reason_to_ignore" class="flex justify-between gap-3 text-sm mb-2">
                                <dt class="text-gray-500 dark:text-gray-400">Reason to Ignore</dt>
                                <dd class="text-gray-900 dark:text-gray-100 text-right" x-text="current.reason_to_ignore"></dd>
                            </div>
                            <p class="text-sm text-gray-400 italic">Not reviewed yet.</p>
                        </div>
                    </template>
                </div>

                <!-- Status change history -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700" x-show="current.status_history && current.status_history.length">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Activities</h4>
                    <ul class="space-y-3">
                        <template x-for="(entry, i) in (current.status_history || [])" :key="i">
                            <li class="flex items-start gap-3 text-sm"
                                x-data="{
                                    decisionLabels: {approved: 'Approved', rfi_requested: 'RFI Requested', not_in_scope: 'Not In Scope', skipped: 'Skipped', pending: 'Pending'},
                                    fieldLabels: {decision: 'Bid Decision', bid_stage: 'Bid Stage', reason_to_ignore: 'Reason to Ignore', duration: 'Duration', estimator_notes: 'Notes', notes: 'Notes'},
                                    noteVerbs: {note_added: 'added', note_edited: 'edited', note_deleted: 'deleted'},
                                    get isNote() { return ['note_added','note_edited','note_deleted'].includes(entry.field); },
                                    get isDecision() { return entry.field === 'decision' || (!entry.field && entry.decision); },
                                    get fieldLabel() { return this.fieldLabels[entry.field] || 'Status'; },
                                    get noteVerb() { return this.noteVerbs[entry.field] || 'changed'; },
                                    get noteText() { return entry.field === 'note_deleted' ? entry.old_value : entry.new_value; },
                                    get newDisplay() {
                                        if (this.isDecision) return this.decisionLabels[entry.new_value || entry.decision] || (entry.new_value || entry.decision);
                                        return entry.new_value;
                                    }
                                }">
                                <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0"
                                      :class="{
                                          'bg-green-500': isDecision && (entry.new_value || entry.decision) === 'approved',
                                          'bg-yellow-500': isDecision && (entry.new_value || entry.decision) === 'rfi_requested',
                                          'bg-red-500': (isDecision && (entry.new_value || entry.decision) === 'not_in_scope') || entry.field === 'note_deleted',
                                          'bg-gray-500': isDecision && (entry.new_value || entry.decision) === 'skipped',
                                          'bg-blue-500': (isDecision && (entry.new_value || entry.decision) === 'pending') || (!isDecision && !isNote),
                                          'bg-purple-500': isNote && entry.field !== 'note_deleted'
                                      }"></span>
                                <div class="min-w-0">
                                    {{-- Note events read "Note added/edited/deleted by X" with a quote; decision changes read "Marked X"; other fields read "Field updated to Y". --}}
                                    <template x-if="isNote">
                                        <div>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                Note <span class="font-medium" x-text="noteVerb"></span>
                                                <template x-if="entry.user"><span> by <span x-text="entry.user"></span></span></template>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 whitespace-pre-line border-l-2 border-gray-200 dark:border-gray-600 pl-2" x-text="noteText"></p>
                                        </div>
                                    </template>
                                    <template x-if="!isNote && isDecision">
                                        <p class="text-gray-900 dark:text-gray-100">
                                            Marked <span class="font-medium" x-text="newDisplay"></span>
                                            <template x-if="entry.user"><span> by <span x-text="entry.user"></span></span></template>
                                        </p>
                                    </template>
                                    <template x-if="!isNote && !isDecision">
                                        <p class="text-gray-900 dark:text-gray-100">
                                            <span class="font-medium" x-text="fieldLabel"></span>
                                            <template x-if="newDisplay">
                                                <span> updated to <span class="font-medium" x-text="newDisplay"></span></span>
                                            </template>
                                            <template x-if="!newDisplay"><span> cleared</span></template>
                                            <template x-if="entry.user"><span> by <span x-text="entry.user"></span></span></template>
                                        </p>
                                    </template>
                                    <p class="text-xs text-gray-400" x-text="entry.changed_at"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Add / Edit Form Modal -->
    <div x-show="formOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="closeForm()">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50"
             x-show="formOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeForm()"></div>

        <!-- Dialog -->
        <div class="relative min-h-full flex items-start justify-center p-4 sm:p-8">
            <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-lg shadow-xl"
                 x-show="formOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="formTitle"></h3>
                    <button type="button" @click="closeForm()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Loading spinner -->
                <div x-show="formLoading" class="p-10 text-center text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin w-6 h-6 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="mt-2 text-sm">Loading…</p>
                </div>

                <!-- Form body (AJAX-injected) -->
                <div class="p-6" x-ref="formBody" x-show="!formLoading" :class="formSubmitting ? 'opacity-50 pointer-events-none' : ''"></div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="deleteOpen = false">
        <div class="fixed inset-0 bg-black/50"
             x-show="deleteOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="deleteOpen = false"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6"
             x-show="deleteOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <x-phosphor-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete Scope Review</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to delete
                        <span class="font-medium text-gray-700 dark:text-gray-300" x-text="deleteName"></span>?
                        This cannot be undone.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleteOpen = false"
                        class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium">
                    Cancel
                </button>
                <form method="POST" :action="`{{ url('scope-review') }}/${deleteId}`">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Bulk delete confirmation modal -->
        <div x-show="bulkDeleteOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition.opacity>
            <div class="absolute inset-0 bg-black/50" @click="bulkDeleteOpen = false"></div>

            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6"
                 x-show="bulkDeleteOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                        <x-phosphor-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete Selected</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Are you sure you want to delete
                            <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="selected.length"></span> scope review<span x-show="selected.length !== 1">s</span></span>?
                            This cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="bulkDeleteOpen = false"
                            class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('scope-review.bulk-destroy') }}">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit"
                                class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
