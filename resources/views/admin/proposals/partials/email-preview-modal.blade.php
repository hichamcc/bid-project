{{-- Attachment preview popup. Any link with data-email-preview="<preview url>" opens it
     (ctrl/cmd-click still follows the link's href, i.e. the file itself). Include once per page. --}}
<div id="emailPreviewModal" class="fixed inset-0 z-50 hidden" onclick="if (event.target === this) emailPreview.close()">
    <div class="absolute inset-0 bg-black opacity-50" style="pointer-events: none;"></div>
    <div class="relative bg-white rounded-lg shadow-xl mx-auto w-full overflow-hidden" style="max-width: 56rem; margin-top: 4vh;">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-gray-50">
            <h3 id="emailPreviewTitle" class="text-sm font-semibold text-gray-700 truncate">Attachment</h3>
            <button type="button" onclick="emailPreview.close()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div id="emailPreviewBody"></div>
    </div>
</div>

<script>
    window.emailPreview = {
        modal() { return document.getElementById('emailPreviewModal'); },
        body() { return document.getElementById('emailPreviewBody'); },
        async open(url) {
            document.getElementById('emailPreviewTitle').textContent = 'Attachment';
            this.body().innerHTML = '<p class="px-5 py-6 text-sm text-gray-400">Loading...</p>';
            this.modal().classList.remove('hidden');
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
                if (!res.ok) throw new Error('bad status ' + res.status);
                this.body().innerHTML = await res.text();
                const title = this.body().querySelector('[data-email-preview-title]');
                if (title) document.getElementById('emailPreviewTitle').textContent = title.textContent.trim();
            } catch (e) {
                this.body().innerHTML = '<p class="px-5 py-6 text-sm text-red-600">Could not load the attachment.</p>';
            }
        },
        close() {
            this.modal().classList.add('hidden');
            this.body().innerHTML = '';
        },
    };
    document.addEventListener('click', (e) => {
        const link = e.target.closest('[data-email-preview]');
        if (!link || e.ctrlKey || e.metaKey || e.shiftKey) return;
        e.preventDefault();
        emailPreview.open(link.dataset.emailPreview);
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') emailPreview.close(); });
</script>
