{{-- Reopen popup, shared by the project page and Distribution.
     Open with projectReopen.open(url, label, estimatorName). --}}
<div id="projectReopenModal" style="display:none; position:fixed; inset:0; z-index:50; background:rgba(0,0,0,0.5);" onclick="if (event.target === this) projectReopen.close()">
    <div style="background:#fff; border-radius:8px; max-width:480px; width:90%; margin:80px auto; padding:24px; position:relative;">
        <h3 style="font-size:18px; font-weight:700; color:#111; margin:0 0 4px;">Reopen Project</h3>
        <p style="font-size:14px; color:#6b7280; margin:0 0 16px;">
            <span id="projectReopenLabel"></span><br>
            Goes back to <strong>IN PROGRESS</strong> and <strong>Open</strong> in <span id="projectReopenEstimator"></span>'s workload. They'll get an email.
        </p>

        <form id="projectReopenForm" method="POST" action="">
            @csrf
            <div style="margin-bottom:12px;">
                <label for="projectReopenDue" style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">New due date</label>
                <input type="date" id="projectReopenDue" name="due_date" required
                       style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px; font-size:14px;">
            </div>
            <div style="margin-bottom:16px;">
                <label for="projectReopenReason" style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">Reason</label>
                <textarea id="projectReopenReason" name="reason" rows="3" required maxlength="500"
                          placeholder="e.g. Addendum 2 received, GC asked for a revised price"
                          style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px; font-size:14px;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="projectReopen.close()"
                        style="padding:8px 14px; font-size:14px; border-radius:6px; background:#e5e7eb; color:#374151;">Cancel</button>
                <button type="submit"
                        style="padding:8px 14px; font-size:14px; font-weight:600; border-radius:6px; background:#2563eb; color:#fff;">Reopen</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.projectReopen = {
        open(url, label, estimatorName) {
            document.getElementById('projectReopenForm').action = url;
            document.getElementById('projectReopenLabel').textContent = label;
            document.getElementById('projectReopenEstimator').textContent = estimatorName;
            document.getElementById('projectReopenDue').value = '';
            document.getElementById('projectReopenReason').value = '';
            document.getElementById('projectReopenModal').style.display = 'block';
            document.getElementById('projectReopenDue').focus();
        },
        close() {
            document.getElementById('projectReopenModal').style.display = 'none';
        },
    };
    document.addEventListener('keydown', e => { if (e.key === 'Escape') projectReopen.close(); });
</script>
