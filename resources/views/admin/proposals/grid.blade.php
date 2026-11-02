@extends('components.layouts.app')

@section('content')
{{-- AG Grid (Community, free) trial version of the proposals list. All rows are loaded once
     from proposals.grid.data and filtered/sorted in the browser. Filters, sort and column
     layout are remembered per browser. --}}
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@32.3.3/dist/ag-grid-community.min.js"></script>

<style>
    #proposalGrid {
        --ag-font-size: 12px;
        --ag-header-height: 34px;
        --ag-header-background-color: #f9fafb;
        --ag-selected-row-background-color: #dbeafe;
    }
    #proposalGrid .ag-cell-wrap-text { word-break: break-word; line-height: 1.35; padding-top: 6px; padding-bottom: 6px; }
    .pg-chip { display: inline-block; padding: 1px 8px; border-radius: 4px; font-size: 11px; line-height: 18px; }
    .pg-green { background: #dcfce7; color: #166534; }
    .pg-red { background: #fee2e2; color: #991b1b; }
    .pg-yellow { background: #fef9c3; color: #854d0e; }
    .pg-blue { background: #dbeafe; color: #1e40af; }
    .pg-gray { background: #e5e7eb; color: #1f2937; }
    .pg-purple { background: #f3e8ff; color: #6b21a8; }
    .pg-pill { border-radius: 9999px; font-weight: 600; }
    .pg-muted { color: #9ca3af; }
    .pg-actions { display: flex; gap: 2px; justify-content: flex-end; align-items: center; height: 100%; }
    .pg-actions button { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; }
    .pg-actions svg { width: 16px; height: 16px; }
    /* Colours match the classic table: blue view, indigo edit, red delete. */
    .pg-actions .pg-view { color: #2563eb; }
    .pg-actions .pg-view:hover { color: #1e3a8a; background: #eff6ff; }
    .pg-actions .pg-edit { color: #4f46e5; }
    .pg-actions .pg-edit:hover { color: #312e81; background: #eef2ff; }
    .pg-actions .pg-delete { color: #dc2626; }
    .pg-actions .pg-delete:hover { color: #7f1d1d; background: #fef2f2; }
    /* Free "set filter": checkbox list used for Status, Type, GC, results, follow-ups. */
    .choice-filter { padding: 8px; width: 240px; font-size: 12px; }
    .choice-filter input[type="text"] { width: 100%; padding: 4px 6px; border: 1px solid #d1d5db; border-radius: 4px; margin-bottom: 6px; }
    .choice-filter .choice-list { max-height: 240px; overflow-y: auto; }
    .choice-filter label { display: flex; align-items: center; gap: 6px; padding: 2px 0; cursor: pointer; }
    .choice-floating { width: 100%; padding: 2px 6px; border: 1px solid #d1d5db; border-radius: 4px; background: #fff; font-size: 12px; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    /* Responded toggle in the toolbar, same as the list page. */
    .responded-toggle { display: inline-flex; border: 1px solid #d1d5db; border-radius: 0.25rem; overflow: hidden; background: #fff; }
    .responded-toggle button { display: inline-flex; align-items: center; justify-content: center; height: 1.8rem; min-width: 1.8rem; padding: 0 0.5rem; font-size: 0.75rem; color: #4b5563; }
    .responded-toggle button + button { border-left: 1px solid #d1d5db; }
    .responded-toggle button.is-active { background: #dbeafe; color: #1e40af; }
    .responded-toggle .dot { width: 0.6rem; height: 0.6rem; border-radius: 9999px; }
</style>

<div class="py-6">
    <div class="max-w-10xl mx-auto" style="padding-left: 0.5rem; padding-right: 0.5rem;">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="px-6 py-4 bg-white border-b border-gray-200 flex justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Proposals Management <span class="text-sm font-normal text-gray-400">(grid view)</span></h2>
                    <a href="{{ route('admin.proposals.index') }}" class="text-xs text-blue-600 hover:text-blue-800">Switch to classic table</a>
                </div>
                <button type="button" onclick="pnForm.openCreate()"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Proposal
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('error') && !old('_modal'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg">
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2 border-b border-gray-200">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="text-gray-600">Responded</span>
                    <div class="responded-toggle" id="respondedToggle">
                        <button type="button" data-value="" class="is-active" title="All">All</button>
                        <button type="button" data-value="yes" title="Responded"><span class="dot" style="background:#22c55e"></span></button>
                        <button type="button" data-value="no" title="Not responded"><span class="dot" style="background:#ef4444"></span></button>
                    </div>
                    <button type="button" onclick="pgGrid.clearFilters()" class="text-gray-600 hover:text-gray-900">Clear filters</button>
                    <button type="button" onclick="pgGrid.resetColumns()" class="text-gray-600 hover:text-gray-900">Reset columns</button>
                    <button type="button" onclick="pgGrid.exportCsv()" class="text-gray-600 hover:text-gray-900">Export CSV</button>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span id="pgRowCount" class="text-gray-500"></span>
                    <span id="pgSelection" class="hidden items-center gap-3">
                        <span class="font-medium text-blue-800"><span id="pgSelectedCount">0</span> selected</span>
                        <button type="button" onclick="pgGrid.api.deselectAll()" class="text-gray-600 hover:text-gray-900">Clear</button>
                        <button type="button" onclick="pgBulk.open()"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium">
                            Edit
                        </button>
                    </span>
                </div>
            </div>

            <div id="proposalGrid" class="ag-theme-quartz" style="height: calc(100vh - 230px); min-height: 400px;"></div>
        </div>
    </div>
</div>

<!-- Bulk edit popup: only fields changed from "Keep current" are applied -->
<div id="pgBulkModal" class="fixed inset-0 z-50 hidden overflow-y-auto" onclick="if (event.target === this) pgBulk.close()">
    <div class="fixed inset-0 bg-black opacity-50" style="pointer-events: none;"></div>
    <form method="POST" action="{{ route('admin.proposals.bulk-update') }}"
          class="relative bg-white rounded-lg shadow-xl mx-auto mt-24 w-full max-w-md">
        @csrf
        <div id="pgBulkIds"></div>
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Edit <span id="pgBulkCount"></span> proposal(s)</h3>
            <button type="button" onclick="pgBulk.close()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label for="pg_bulk_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="pg_bulk_status" name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white">
                    <option value="">— Keep current —</option>
                    @foreach(\App\Models\Proposal::STATUSES as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                    @endforeach
                    <option value="none">Not set</option>
                </select>
            </div>
            @foreach(['result_gc' => 'GC Result', 'result_art' => 'ART Result'] as $resultField => $resultLabel)
                <div>
                    <label for="pg_bulk_{{ $resultField }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $resultLabel }}</label>
                    <select id="pg_bulk_{{ $resultField }}" name="{{ $resultField }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white">
                        <option value="">— Keep current —</option>
                        <option value="pending">Pending</option>
                        <option value="win">Win</option>
                        <option value="loss">Loss</option>
                    </select>
                </div>
            @endforeach
        </div>
        <div class="flex justify-end gap-3 px-5 py-3 border-t border-gray-200">
            <button type="button" onclick="pgBulk.close()" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Apply to selected</button>
        </div>
    </form>
</div>

<!-- Hidden delete form, submitted from a row's delete button -->
<form id="pgDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="_return" value="grid">
</form>

@include('admin.proposals.partials.notes-modal')

@include('admin.proposals.partials.form-modal')

<script>
    window.pnReturnTo = 'grid';

    (function () {
        const STATUS_LABELS = @json(\App\Models\Proposal::STATUSES);
        const STATUS_CLASSES = { open: 'pg-blue', closed: 'pg-gray', inactive: 'pg-red', on_hold: 'pg-yellow' };
        const RESULT_LABELS = { win: 'Win', loss: 'Loss', pending: 'Pending' };
        const RESULT_CLASSES = { win: 'pg-green', loss: 'pg-red', pending: 'pg-yellow' };
        const FOLLOW_UPS = [['first', 'Follow-Up 1'], ['second', 'Follow-Up 2'], ['third', 'Follow-Up 3'], ['fourth', 'Follow-Up 4']];
        const DATA_URL = '{{ route('admin.proposals.grid.data') }}';
        const STATE_KEY = 'proposalGrid.state.v1';
        const BLANK = '(Blank)';

        const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const fmtDate = (iso) => {
            if (!iso) return '';
            const [y, m, d] = iso.split('-').map(Number);
            return new Date(y, m - 1, d).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        };
        const fmtMoney = (v) => v == null ? '' : '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const resultLabel = (v) => RESULT_LABELS[v] || 'Pending';
        const followUpState = (fu) => !fu.date ? 'Not sent' : (fu.respond === 'yes' ? 'Responded' : 'No response');
        // Date filter compares a local-midnight Date with the ISO (Y-m-d) cell value.
        const dateComparator = (filterDate, cellValue) => {
            if (!cellValue) return -1;
            const [y, m, d] = cellValue.split('-').map(Number);
            const cell = new Date(y, m - 1, d);
            return cell < filterDate ? -1 : (cell > filterDate ? 1 : 0);
        };

        // ---- Free "set filter": a checkbox list of the column's values (AG Grid's own Set Filter is Enterprise).
        class ChoiceFilter {
            init(params) {
                this.params = params;
                this.selected = null; // null = everything selected (filter inactive)
                this.gui = document.createElement('div');
                this.gui.className = 'choice-filter';
            }
            getGui() { return this.gui; }
            valuesOf(node) {
                const v = this.params.getValue(node);
                const arr = Array.isArray(v) ? v : [v];
                const clean = arr.filter((x) => x !== null && x !== undefined && x !== '');
                return clean.length ? clean.map(String) : [BLANK];
            }
            allValues() {
                const set = new Set();
                this.params.api.forEachNode((node) => this.valuesOf(node).forEach((v) => set.add(v)));
                const order = this.params.colDef.filterParams?.order;
                return [...set].sort((a, b) => {
                    if (order) return (order.indexOf(a) + 1 || 999) - (order.indexOf(b) + 1 || 999);
                    if (a === BLANK) return 1;
                    if (b === BLANK) return -1;
                    return a.localeCompare(b, undefined, { sensitivity: 'base' });
                });
            }
            afterGuiAttached() { this.render(); }
            render() {
                const values = this.allValues();
                const isOn = (v) => this.selected === null || this.selected.has(v);
                this.gui.innerHTML =
                    '<input type="text" placeholder="Search...">' +
                    '<label><input type="checkbox" data-all> <strong>(Select all)</strong></label>' +
                    '<div class="choice-list">' +
                    values.map((v) => '<label><input type="checkbox" data-value="' + esc(v) + '"' + (isOn(v) ? ' checked' : '') + '> ' + esc(v) + '</label>').join('') +
                    '</div>';
                const all = this.gui.querySelector('[data-all]');
                const boxes = [...this.gui.querySelectorAll('[data-value]')];
                const syncAll = () => { all.checked = boxes.every((b) => b.checked); };
                syncAll();
                const apply = () => {
                    const checked = boxes.filter((b) => b.checked).map((b) => b.dataset.value);
                    this.selected = checked.length === boxes.length ? null : new Set(checked);
                    syncAll();
                    this.params.filterChangedCallback();
                };
                all.addEventListener('change', () => { boxes.forEach((b) => { b.checked = all.checked; }); apply(); });
                boxes.forEach((b) => b.addEventListener('change', apply));
                this.gui.querySelector('input[type="text"]').addEventListener('input', (e) => {
                    const q = e.target.value.toLowerCase();
                    boxes.forEach((b) => { b.parentElement.style.display = b.dataset.value.toLowerCase().includes(q) ? '' : 'none'; });
                });
            }
            isFilterActive() { return this.selected !== null; }
            doesFilterPass(p) { return this.valuesOf(p.node).some((v) => this.selected.has(v)); }
            getModel() { return this.selected === null ? null : { values: [...this.selected] }; }
            setModel(model) { this.selected = model && model.values ? new Set(model.values) : null; }
        }

        // Floating-filter cell for ChoiceFilter: shows the current selection, click opens the list.
        class ChoiceFloatingFilter {
            init(params) {
                this.gui = document.createElement('div');
                this.gui.className = 'choice-floating';
                this.gui.textContent = 'All';
                this.gui.addEventListener('click', () => params.showParentFilter());
            }
            getGui() { return this.gui; }
            onParentModelChanged(model) {
                this.gui.textContent = !model ? 'All' : (model.values.length === 1 ? model.values[0] : model.values.length + ' selected');
            }
        }

        const choiceCol = (extra) => Object.assign({ filter: ChoiceFilter, floatingFilterComponent: ChoiceFloatingFilter, suppressFloatingFilterButton: true }, extra);
        const textFilter = { filter: 'agTextColumnFilter', filterParams: { buttons: ['reset'], maxNumConditions: 2 } };
        const numberCol = (field, headerName) => ({
            field, headerName, width: 130, filter: 'agNumberColumnFilter', filterParams: { buttons: ['reset'] },
            valueFormatter: (p) => fmtMoney(p.value), cellClass: 'ag-right-aligned-cell', type: 'rightAligned',
        });

        const columnDefs = [
            {
                field: 'submission_date', headerName: 'Submitted Date', width: 150,
                filter: 'agDateColumnFilter', filterParams: { comparator: dateComparator, buttons: ['reset'] },
                cellRenderer: (p) => !p.value ? '<span class="pg-muted">-</span>'
                    : '<span class="pg-chip ' + (p.data.responded === 'yes' ? 'pg-green' : 'pg-red') + '" title="' + (p.data.responded === 'yes' ? 'GC confirmed receipt' : 'GC has not confirmed receipt') + '">' + esc(fmtDate(p.value)) + '</span>',
            },
            choiceCol({
                colId: 'status', headerName: 'Status', width: 120,
                valueGetter: (p) => p.data.status ? (STATUS_LABELS[p.data.status] || p.data.status) : '',
                filterParams: { order: [...Object.values(STATUS_LABELS), BLANK] },
                cellRenderer: (p) => p.data.status ? '<span class="pg-chip pg-pill ' + (STATUS_CLASSES[p.data.status] || 'pg-blue') + '">' + esc(p.value) + '</span>' : '<span class="pg-muted">-</span>',
            }),
            Object.assign({
                field: 'project_name', headerName: 'Project', width: 280, pinned: 'left', wrapText: true, autoHeight: true,
                filterValueGetter: (p) => (p.data.project_name || '') + ' ' + (p.data.job_number || ''),
                tooltipValueGetter: (p) => p.data.job_number ? 'Job #' + p.data.job_number : null,
                cellStyle: { fontWeight: 600 },
            }, textFilter),
            choiceCol({
                colId: 'type', headerName: 'Type', width: 100,
                valueGetter: (p) => p.data.type === 'MULTIUNIT' ? 'MU' : (p.data.type ? 'NON MU' : ''),
                cellRenderer: (p) => !p.value ? '<span class="pg-muted">-</span>'
                    : '<span class="pg-chip pg-pill ' + (p.value === 'MU' ? 'pg-purple' : 'pg-blue') + '">' + esc(p.value) + '</span>',
            }),
            choiceCol({ field: 'gc', headerName: 'GC', width: 200, wrapText: true, autoHeight: true }),
            choiceCol({
                field: 'other_gcs', headerName: 'Other GCs', width: 180,
                valueFormatter: (p) => (p.value || []).join(', '),
                tooltipValueGetter: (p) => (p.value || []).join(', ') || null,
                comparator: (a, b) => (a || []).join(', ').localeCompare((b || []).join(', ')),
            }),
            numberCol('price_original', 'ARTELYE Price'),
            numberCol('price_ve', 'ARTELYE VE Price'),
            numberCol('gc_price', 'GC Price'),
            ...FOLLOW_UPS.map(([nth, label]) => choiceCol({
                colId: nth + '_follow_up', headerName: label, width: 140,
                valueGetter: (p) => p.data.follow_ups[nth].date, // sorts by date
                filterValueGetter: (p) => followUpState(p.data.follow_ups[nth]),
                filterParams: { order: ['Responded', 'No response', 'Not sent'] },
                cellRenderer: (p) => {
                    const fu = p.data.follow_ups[nth];
                    if (!fu.date) return '<span class="pg-muted">-</span>';
                    let html = '<span class="pg-chip ' + (fu.respond === 'yes' ? 'pg-green' : 'pg-red') + '">' + esc(fmtDate(fu.date)) + '</span>';
                    if (fu.attachment) html += ' <a href="' + esc(fu.attachment) + '" target="_blank" title="View attachment" style="color:#2563eb">&#8681;</a>';
                    return html;
                },
            })),
            ...[['result_gc', 'GC Result'], ['result_art', 'ART Result']].map(([field, headerName]) => choiceCol({
                colId: field, headerName, width: 115,
                valueGetter: (p) => resultLabel(p.data[field]),
                filterParams: { order: ['Pending', 'Win', 'Loss'] },
                cellRenderer: (p) => '<span class="pg-chip pg-pill ' + (RESULT_CLASSES[p.data[field]] || 'pg-yellow') + '">' + esc(p.value) + '</span>',
            })),
            Object.assign({
                colId: 'notes', headerName: 'Notes', width: 280,
                valueGetter: (p) => p.data.latest_note ? p.data.latest_note.body : '',
                filterValueGetter: (p) => p.data.latest_note ? p.data.latest_note.author + ' ' + p.data.latest_note.body : '',
                tooltipValueGetter: (p) => p.data.latest_note ? p.data.latest_note.body : null,
                cellRenderer: (p) => {
                    const n = p.data.latest_note;
                    if (!n) return '<span style="color:#2563eb;cursor:pointer">+ Add note</span>';
                    return '<span style="cursor:pointer"><span class="pg-muted">' + esc(n.author) + ', ' + esc(n.at) + ':</span> ' + esc(n.body) +
                        (p.data.notes_count > 1 ? ' <span class="pg-muted">+' + (p.data.notes_count - 1) + ' more</span>' : '') + '</span>';
                },
                onCellClicked: (p) => pnNotes.open(p.data.id, p.data.project_name || ''),
            }, textFilter),
            {
                colId: 'actions', headerName: 'Actions', width: 110, pinned: 'right',
                sortable: false, filter: false, resizable: false, suppressMovable: true,
                cellRenderer: () =>
                    '<div class="pg-actions">' +
                    // Same SVG icons as the classic table's View / Edit / Delete buttons.
                    '<button type="button" data-action="view" title="View" class="pg-view">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                    '</button>' +
                    '<button type="button" data-action="edit" title="Edit" class="pg-edit">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' +
                    '</button>' +
                    '<button type="button" data-action="delete" title="Delete" class="pg-delete">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                    '</button>' +
                    '</div>',
                onCellClicked: (p) => {
                    const action = p.event.target.closest('[data-action]')?.dataset.action;
                    if (action === 'view') window.location = p.data.urls.show;
                    if (action === 'edit') pnForm.openEdit(p.data.urls.edit);
                    if (action === 'delete' && confirm('Are you sure you want to delete this proposal?')) {
                        const form = document.getElementById('pgDeleteForm');
                        form.action = p.data.urls.destroy;
                        form.submit();
                    }
                },
            },
        ];

        let responded = '';

        const saveState = () => {
            try {
                localStorage.setItem(STATE_KEY, JSON.stringify({
                    columns: pgGrid.api.getColumnState(),
                    filters: pgGrid.api.getFilterModel(),
                    responded,
                }));
            } catch (e) { /* storage unavailable: layout just isn't remembered */ }
        };
        const loadState = () => {
            try { return JSON.parse(localStorage.getItem(STATE_KEY) || 'null'); } catch (e) { return null; }
        };

        const updateCounts = () => {
            const api = pgGrid.api;
            let total = 0;
            api.forEachNode(() => total++);
            const shown = api.getDisplayedRowCount();
            document.getElementById('pgRowCount').textContent = shown === total ? total + ' proposals' : 'Showing ' + shown + ' of ' + total;
            const selected = api.getSelectedRows().length;
            document.getElementById('pgSelectedCount').textContent = selected;
            const sel = document.getElementById('pgSelection');
            sel.classList.toggle('hidden', selected === 0);
            sel.classList.toggle('flex', selected > 0);
        };

        const setResponded = (value) => {
            responded = value;
            document.querySelectorAll('#respondedToggle button').forEach((b) => b.classList.toggle('is-active', b.dataset.value === value));
            pgGrid.api.onFilterChanged();
        };

        const gridOptions = {
            columnDefs,
            defaultColDef: { sortable: true, resizable: true, filter: true, floatingFilter: true },
            rowSelection: { mode: 'multiRow', headerCheckbox: true, selectAll: 'filtered' },
            getRowId: (p) => String(p.data.id),
            rowHeight: 34,
            tooltipShowDelay: 400,
            animateRows: false,
            overlayNoRowsTemplate: '<span class="pg-muted">No proposals found.</span>',
            isExternalFilterPresent: () => responded !== '',
            doesExternalFilterPass: (node) => responded === 'yes' ? node.data.responded === 'yes' : node.data.responded !== 'yes',
            onFilterChanged: () => { updateCounts(); saveState(); },
            onSortChanged: saveState,
            onColumnMoved: saveState,
            onColumnResized: (e) => { if (e.finished) saveState(); },
            onColumnVisible: saveState,
            onSelectionChanged: updateCounts,
            onRowDataUpdated: updateCounts,
        };

        window.pgGrid = {
            api: null,
            async load(ids) {
                const url = ids ? DATA_URL + '?' + ids.map((id) => 'ids[]=' + encodeURIComponent(id)).join('&') : DATA_URL;
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('bad status ' + res.status);
                return res.json();
            },
            async refreshRows(ids) {
                const rows = await this.load(ids);
                this.api.applyTransaction({ update: rows });
            },
            clearFilters() { this.api.setFilterModel(null); setResponded(''); },
            resetColumns() { this.api.resetColumnState(); saveState(); },
            exportCsv() {
                this.api.exportDataAsCsv({
                    fileName: 'proposals.csv',
                    columnKeys: this.api.getAllDisplayedColumns().map((c) => c.getColId()).filter((id) => id !== 'actions' && !id.startsWith('ag-Grid')),
                    processCellCallback: (p) => {
                        const id = p.column.getColId();
                        if (id.endsWith('_follow_up')) {
                            const fu = p.node.data.follow_ups[id.replace('_follow_up', '')];
                            return fu.date ? fu.date + ' (' + followUpState(fu) + ')' : '';
                        }
                        if (id === 'other_gcs') return (p.value || []).join(', ');
                        return p.value ?? '';
                    },
                });
            },
        };

        document.addEventListener('DOMContentLoaded', async () => {
            pgGrid.api = agGrid.createGrid(document.getElementById('proposalGrid'), gridOptions);
            pgGrid.api.showLoadingOverlay();

            document.querySelectorAll('#respondedToggle button').forEach((b) => b.addEventListener('click', () => { setResponded(b.dataset.value); saveState(); }));

            try {
                pgGrid.api.setGridOption('rowData', await pgGrid.load());
                const state = loadState();
                if (state) {
                    if (state.columns) pgGrid.api.applyColumnState({ state: state.columns, applyOrder: true });
                    if (state.filters) pgGrid.api.setFilterModel(state.filters);
                    if (state.responded) setResponded(state.responded);
                }
            } catch (e) {
                pgGrid.api.setGridOption('overlayNoRowsTemplate', '<span style="color:#dc2626">Could not load proposals.</span>');
                pgGrid.api.setGridOption('rowData', []);
            }
            updateCounts();
        });

        // Refresh just that row after its notes change in the popup.
        pnNotes.onChange = (proposalId) => pgGrid.refreshRows([proposalId]).catch(() => {});
    })();

    // Bulk edit popup: posts the selected ids to the same bulk-update action as the list page.
    window.pgBulk = {
        open() {
            const ids = pgGrid.api.getSelectedRows().map((r) => r.id);
            if (!ids.length) return;
            document.getElementById('pgBulkIds').innerHTML = ids.map((id) => '<input type="hidden" name="ids[]" value="' + Number(id) + '">').join('');
            document.getElementById('pgBulkCount').textContent = ids.length;
            document.getElementById('pgBulkModal').classList.remove('hidden');
        },
        close() { document.getElementById('pgBulkModal').classList.add('hidden'); },
    };
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') pgBulk.close(); });
</script>
@endsection
