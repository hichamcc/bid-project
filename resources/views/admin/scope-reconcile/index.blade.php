@extends('components.layouts.app')

@php
    // Best-effort display name for an allocation: its first project's name with
    // the "{job_number}{letter}. " prefix stripped, for a sanity check only.
    $allocName = function ($alloc) {
        $proj = $alloc->relationLoaded('projects') ? $alloc->projects->first() : $alloc->projects()->first();
        $name = $proj?->name;
        if ($name) {
            $name = preg_replace('/^' . preg_quote($alloc->job_number, '/') . '[A-Z]?\.\s*/i', '', $name);
        }
        return $name ?: '—';
    };
@endphp

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Reconcile Scope Reviews &rarr; Allocations</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Proposed links between approved scope reviews and allocations, matched on
                <span class="font-medium">Project #</span> = <span class="font-medium">Job #</span>.
                Review and approve the ones to link.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.scope-reconcile.approve') }}"
              x-data="{ count: {{ count($matched) }} }"
              onsubmit="return confirm('Link the selected scope reviews to their allocations?');">
            @csrf

            {{-- ============ MATCHED (auto-selectable) ============ --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Matches ({{ count($matched) }})</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Exactly one allocation found for the project number.</p>
                    </div>
                    @if(count($matched))
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-medium"
                                x-bind:disabled="count === 0">
                            Approve &amp; Link (<span x-text="count"></span>)
                        </button>
                    @endif
                </div>

                @if(count($matched))
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 w-10">
                                        <input type="checkbox" checked
                                               @change="$root.querySelectorAll('[data-match-cb]').forEach(cb => cb.checked = $event.target.checked); count = $root.querySelectorAll('[data-match-cb]:checked').length"
                                               class="rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Project #</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Scope Review</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Job #</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Allocation Project</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($matched as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" data-match-cb checked
                                                   name="pairs[{{ $row['scope']->id }}]"
                                                   value="{{ $row['allocation']->id }}"
                                                   @change="count = $root.querySelectorAll('[data-match-cb]:checked').length"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                                        </td>
                                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['scope']->project_number }}</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['scope']->project_name }}</td>
                                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['allocation']->job_number }}</td>
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $allocName($row['allocation']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">No one-to-one matches found.</p>
                @endif
            </div>

            {{-- ============ AMBIGUOUS (choose one) ============ --}}
            @if(count($ambiguous))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-yellow-700 dark:text-yellow-300">Ambiguous ({{ count($ambiguous) }})</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">The project number matches more than one allocation. Pick the correct one to link (or leave unselected).</p>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($ambiguous as $row)
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-900 dark:text-gray-100 mb-2">
                                    <span class="font-medium">{{ $row['scope']->project_number }}</span> — {{ $row['scope']->project_name }}
                                </p>
                                <div class="space-y-1.5 pl-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <input type="radio" name="pairs[{{ $row['scope']->id }}]" value="" checked
                                               @change="count = $root.querySelectorAll('[data-match-cb]:checked').length"
                                               class="border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-400">
                                        Do not link
                                    </label>
                                    @foreach($row['allocations'] as $alloc)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input type="radio" name="pairs[{{ $row['scope']->id }}]" value="{{ $alloc->id }}"
                                                   class="border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                                            Job #{{ $alloc->job_number }} &middot; {{ $allocName($alloc) }}
                                            <span class="text-xs text-gray-400">(allocation #{{ $alloc->id }})</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-medium">
                            Approve &amp; Link Selected
                        </button>
                    </div>
                </div>
            @endif
        </form>

        {{-- ============ UNMATCHED (info only) ============ --}}
        @if(count($unmatched))
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">No Allocation Found ({{ count($unmatched) }})</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Approved scope reviews whose project number has no matching allocation job number.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Project #</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Scope Review</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($unmatched as $sr)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $sr->project_number }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sr->project_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ============ SKIP PROJECT NUMBERS ============ --}}
        @if(count($skips))
            <form method="POST" action="{{ route('admin.scope-reconcile.approve-skips') }}"
                  x-data="{ count: {{ count($skips) }} }"
                  onsubmit="return confirm('Clear the project number and mark these scope reviews as Skipped?');">
                @csrf
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Skip Project Numbers ({{ count($skips) }})</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Project number is the placeholder text "skip". Confirm to clear the number and set the decision to <span class="font-medium">Skipped</span>.</p>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium"
                                x-bind:disabled="count === 0">
                            Confirm &amp; Skip (<span x-text="count"></span>)
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 w-10">
                                        <input type="checkbox" checked
                                               @change="$root.querySelectorAll('[data-skip-cb]').forEach(cb => cb.checked = $event.target.checked); count = $root.querySelectorAll('[data-skip-cb]:checked').length"
                                               class="rounded border-gray-300 dark:border-gray-600 text-gray-700 focus:ring-gray-500">
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Scope Review</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Current Project #</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-300">Current Decision</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($skips as $sr)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" data-skip-cb checked
                                                   name="ids[]" value="{{ $sr->id }}"
                                                   @change="count = $root.querySelectorAll('[data-skip-cb]:checked').length"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-gray-700 focus:ring-gray-500">
                                        </td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sr->project_name }}</td>
                                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $sr->project_number }}</td>
                                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $sr->decision ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        @endif

    </div>
</div>
@endsection
