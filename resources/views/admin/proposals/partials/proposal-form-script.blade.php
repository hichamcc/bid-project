{{-- Project info panel + duplicate-proposal warning for the create/edit proposal forms. Needs $projects.
     Elements are looked up on each call (and may be missing: the edit form has no warning box),
     so it also works for forms injected into the list page's popup. --}}
<script>
const projectsData = @json($projects->load(['assignedTo', 'gcRecord'])->keyBy('id'));
const existingProposals = @json($projects->load('proposals')->pluck('proposals', 'id')->map->pluck('id'));

function showProjectInfo(projectId) {
    const projectInfo = document.getElementById('projectInfo');
    const warningDiv = document.getElementById('existingProposalWarning');
    if (!projectInfo) return;

    if (!projectId) {
        projectInfo.classList.add('hidden');
        if (warningDiv) warningDiv.classList.add('hidden');
        return;
    }

    const project = projectsData[projectId];
    if (!project) return;

    document.getElementById('primaryGC').textContent = project.gc || '-';
    // other_gc is an object keyed by GC name (legacy rows may be a plain array).
    const otherGcNames = Array.isArray(project.other_gc) ? project.other_gc : Object.keys(project.other_gc || {});
    document.getElementById('otherGCs').textContent = otherGcNames.length > 0 ? otherGcNames.join(', ') : '-';
    document.getElementById('estimator').textContent = project.assigned_to
        ? project.assigned_to.name
        : '-';

    projectInfo.classList.remove('hidden');

    if (warningDiv) {
        const hasProposal = existingProposals[projectId] && existingProposals[projectId].length > 0;
        warningDiv.classList.toggle('hidden', !hasProposal);
    }
}

// Fill the panel for an already-selected project (page load, or after a form is injected).
function initProposalForm() {
    const projectSelect = document.getElementById('project_id');
    if (projectSelect && projectSelect.value) {
        showProjectInfo(projectSelect.value);
    }
}

document.addEventListener('DOMContentLoaded', initProposalForm);
</script>
