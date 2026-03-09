@extends('components.layouts.app')

@section('content')
<div class="py-12"
     x-data="{
        modal: false,
        pendingForm: null,
        jobNumber: '',
        assignedDate: '',
        openEarlyWarning(formEl, jobNumber, assignedDate) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const assigned = new Date(assignedDate);
            assigned.setHours(0, 0, 0, 0);
            if (today < assigned) {
                this.pendingForm = formEl;
                this.jobNumber = jobNumber;
                this.assignedDate = assignedDate;
                this.modal = true;
            } else {
                formEl.submit();
            }
        },
        confirm() {
            this.modal = false;
            this.pendingForm.submit();
        }
     }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">My Workload</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View and manage your allocated jobs.</p>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search & Filter -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-4">
                <form method="GET" action="{{ route('estimator.workload.index') }}" class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-48">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by job number..."
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select name="status"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('estimator.workload.index') }}"
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Job Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assigned Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($allocations as $allocation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $allocation->job_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $allocation->job_type === 'MU' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ $allocation->job_type === 'NON_MU' ? 'NON MU' : 'MU' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->days_required }}d
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->assigned_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $allocation->due_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $allocation->status === 'submitted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ ucfirst($allocation->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if($allocation->status === 'open')
                                        <form method="POST" action="{{ route('estimator.workload.status', $allocation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="submitted">
                                            <button type="button"
                                                    @click="openEarlyWarning($el.closest('form'), '{{ $allocation->job_number }}', '{{ $allocation->assigned_date->format('Y-m-d') }}')"
                                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded">
                                                Mark Submitted
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('estimator.workload.status', $allocation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit"
                                                    onclick="return confirm('Reopen job {{ $allocation->job_number }}?')"
                                                    class="px-3 py-1.5 bg-gray-400 hover:bg-gray-500 text-white text-xs font-medium rounded">
                                                Reopen
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No jobs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allocations->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $allocations->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- Early Submission Warning Modal -->
    <div x-show="modal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         @keydown.escape.window="modal = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="modal = false"></div>

        <!-- Dialog -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6 z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Icon -->
            <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 dark:bg-yellow-900/40 rounded-full mx-auto mb-4">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center mb-2">
                Submitting Early
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-1">
                Job <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="jobNumber"></span>
                is not scheduled to start until <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="new Date(assignedDate + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>.
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                Are you sure you want to mark it as submitted now?
            </p>

            <div class="flex gap-3">
                <button @click="modal = false"
                        class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-sm">
                    Cancel
                </button>
                <button @click="confirm()"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm">
                    Yes, Submit Anyway
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
