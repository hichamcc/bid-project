@extends('components.layouts.app')

@section('content')
<div class="py-12"
     x-data="{
        panelOpen: false,
        current: null,
        openPanel(scopeReview) {
            this.current = scopeReview;
            this.panelOpen = true;
        }
     }">
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
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Opportunity
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
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('scope-review.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-40">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Project Name</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Source</label>
                        <input type="text" name="source" value="{{ request('source') }}"
                               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Platform</label>
                        <input type="text" name="platform" value="{{ request('platform') }}"
                               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Project Type</label>
                        <select name="project_type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="MU" {{ request('project_type') === 'MU' ? 'selected' : '' }}>MU</option>
                            <option value="NON_MU" {{ request('project_type') === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Decision</label>
                        <select name="decision" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="__pending__" {{ request('decision') === '__pending__' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('decision') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rfi_requested" {{ request('decision') === 'rfi_requested' ? 'selected' : '' }}>RFI Requested</option>
                            <option value="not_in_scope" {{ request('decision') === 'not_in_scope' ? 'selected' : '' }}>Not In Scope</option>
                            <option value="skipped" {{ request('decision') === 'skipped' ? 'selected' : '' }}>Skipped</option>
                        </select>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Estimator</label>
                            <select name="assigned_estimator_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All</option>
                                @foreach($estimators as $estimator)
                                    <option value="{{ $estimator->id }}" {{ request('assigned_estimator_id') == $estimator->id ? 'selected' : '' }}>
                                        {{ $estimator->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'source', 'platform', 'project_type', 'decision', 'assigned_estimator_id', 'ready_for_assignment']))
                            <a href="{{ route('scope-review.index') }}"
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- List -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Platform</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estimator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Decision</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($scopeReviews as $scopeReview)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $scopeReview->project_number ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->source ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $scopeReview->platform ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @if($scopeReview->project_link)
                                        <a href="{{ $scopeReview->project_link }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $scopeReview->project_name }}
                                        </a>
                                    @else
                                        {{ $scopeReview->project_name }}
                                    @endif
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
                                        ];
                                        $decisionLabels = [
                                            'approved' => 'Approved',
                                            'rfi_requested' => 'RFI Requested',
                                            'not_in_scope' => 'Not In Scope',
                                            'skipped' => 'Skipped',
                                        ];
                                    @endphp
                                    @if($scopeReview->decision)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-md {{ $decisionColors[$scopeReview->decision] }}">
                                            {{ $decisionLabels[$scopeReview->decision] }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            Pending
                                        </span>
                                    @endif
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
                                                    'duration' => $scopeReview->duration,
                                                    'uploaded_in_oh' => $scopeReview->uploaded_in_oh,
                                                    'estimator_notes' => $scopeReview->estimator_notes,
                                                    'status_history' => $scopeReview->statusHistories->map(fn($h) => [
                                                        'decision' => $h->decision,
                                                        'user' => $h->user?->name,
                                                        'changed_at' => $h->created_at->format('M d, Y g:i A'),
                                                    ]),
                                                ]) }})"
                                                title="View"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                            <x-phosphor-eye width="16" height="16" />
                                        </button>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('scope-review.edit', $scopeReview) }}"
                                               title="Edit"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60">
                                                <x-phosphor-pencil-simple width="16" height="16" />
                                            </a>
                                        @elseif($scopeReview->assigned_estimator_id === auth()->id())
                                            <a href="{{ route('scope-review.edit', $scopeReview) }}"
                                               title="Review"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60">
                                                <x-phosphor-pencil-simple width="16" height="16" />
                                            </a>
                                        @endif
                                        @if(auth()->user()->isAdmin() && $scopeReview->isApproved() && !$scopeReview->isConverted())
                                            <a href="{{ route('admin.allocation.index', [
                                                    'scope_review_id' => $scopeReview->id,
                                                    'job_number' => $scopeReview->project_number,
                                                    'due_date' => optional($scopeReview->due_date)->format('Y-m-d'),
                                                    'project_name' => $scopeReview->project_name,
                                                    'job_type' => $scopeReview->project_type,
                                                    'web_link' => $scopeReview->project_link,
                                                ]) . '#job-form' }}"
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
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
                        <div x-show="current.notes">
                            <dt class="text-gray-500 dark:text-gray-400 mb-1">Notes</dt>
                            <dd class="text-gray-900 dark:text-gray-100 whitespace-pre-line" x-text="current.notes"></dd>
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
                                              'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200': current.decision === 'skipped'
                                          }"
                                          x-text="({approved: 'Approved', rfi_requested: 'RFI Requested', not_in_scope: 'Not In Scope', skipped: 'Skipped'})[current.decision]"></span>
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
                            <div x-show="current.estimator_notes">
                                <dt class="text-gray-500 dark:text-gray-400 mb-1">Estimator Notes</dt>
                                <dd class="text-gray-900 dark:text-gray-100 whitespace-pre-line" x-text="current.estimator_notes"></dd>
                            </div>
                        </dl>
                    </template>
                    <p x-show="!current.decision" class="text-sm text-gray-400 italic">Not reviewed yet.</p>
                </div>

                <!-- Status change history -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700" x-show="current.status_history && current.status_history.length">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status History</h4>
                    <ul class="space-y-3">
                        <template x-for="entry in (current.status_history || [])" :key="entry.changed_at + entry.decision">
                            <li class="flex items-start gap-3 text-sm">
                                <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0"
                                      :class="{
                                          'bg-green-500': entry.decision === 'approved',
                                          'bg-yellow-500': entry.decision === 'rfi_requested',
                                          'bg-red-500': entry.decision === 'not_in_scope',
                                          'bg-gray-500': entry.decision === 'skipped'
                                      }"></span>
                                <div class="min-w-0">
                                    <p class="text-gray-900 dark:text-gray-100">
                                        Marked
                                        <span class="font-medium" x-text="({approved: 'Approved', rfi_requested: 'RFI Requested', not_in_scope: 'Not In Scope', skipped: 'Skipped'})[entry.decision]"></span>
                                        <template x-if="entry.user">
                                            <span> by <span x-text="entry.user"></span></span>
                                        </template>
                                    </p>
                                    <p class="text-xs text-gray-400" x-text="entry.changed_at"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
