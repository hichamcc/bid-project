{{-- Notes popup shared by the proposals list and grid. Open with pnNotes.open(id, title). --}}
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
    // Plain-JS notes popup for the proposals list / grid (same approach as srNotes on Scope Review).
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
            const widget = this.body().querySelector('.pn-notes');
            if (widget && this.onChange) this.onChange(widget.dataset.proposalId);
        },
        // Optional hook set by the page (the grid uses it to refresh the row).
        onChange: null,
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
