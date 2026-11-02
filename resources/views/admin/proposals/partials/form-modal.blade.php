{{-- Create / Edit proposal popup shared by the proposals list and grid. Needs $projects. --}}
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
                this.setReturn();
                initProposalForm();
            } catch (e) {
                this.body().innerHTML = '<p class="text-sm text-red-600">Could not load the form.</p>';
            }
        },
        // Pages set window.pnReturnTo (e.g. 'grid') so a successful save returns there.
        setReturn() {
            const input = this.body().querySelector('input[name="_return"]');
            if (input && window.pnReturnTo) input.value = window.pnReturnTo;
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
