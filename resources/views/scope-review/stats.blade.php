@extends('components.layouts.app')

@php
    $approvalRate = $totalProjects > 0 ? round($totalYes / $totalProjects * 100) : 0;
@endphp

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Scope Review Analytics</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $selectedYear }} &middot; {{ number_format($totalProjects) }} opportunities &middot; {{ $approvalRate }}% approved
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('scope-review.stats') }}" class="flex items-center gap-2">
                    <label for="year" class="text-sm text-gray-600 dark:text-gray-400">Year</label>
                    <select name="year" id="year" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('scope-review.index') }}"
                   class="text-sm bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            {{-- ============ BID STATUS ============ --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Bid Status</h3>
                <table class="min-w-full text-sm border border-gray-300 dark:border-gray-600">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right font-medium text-gray-600 dark:text-gray-300">Count</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right font-medium text-gray-600 dark:text-gray-300">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Row tints: NOT YET REVIEWED -> red, REQUESTED RFI (pending) -> yellow.
                            $rowTints = [
                                'NOT YET REVIEWED' => 'bg-red-50 dark:bg-red-900/20',
                                'REQUESTED RFI'    => 'bg-yellow-50 dark:bg-yellow-900/20',
                            ];
                        @endphp
                        @foreach($bidStatusSummary as $row)
                            @continue(!empty($row['hide_if_zero']) && $row['count'] === 0)
                            @php $pct = $totalProjects > 0 ? round($row['count'] / $totalProjects * 100) : 0; @endphp
                            <tr class="{{ $rowTints[$row['label']] ?? 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-gray-700 dark:text-gray-300">
                                    <a href="{{ route('scope-review.index', $row['filters']) }}" class="hover:underline">{{ $row['label'] }}</a>
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($row['count']) }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-500 dark:text-gray-400">{{ $pct }}%</td>
                            </tr>
                        @endforeach
                        <tr class="bg-green-50 dark:bg-green-900/20 font-semibold">
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-green-800 dark:text-green-300">Total Yes</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-green-800 dark:text-green-300">{{ number_format($totalYes) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-green-700 dark:text-green-400">{{ $approvalRate }}%</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 font-semibold">
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-gray-900 dark:text-gray-100">Total Projects</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($totalProjects) }}</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-500 dark:text-gray-400">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ============ PLATFORMS ============ --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Platforms</h3>
                <table class="min-w-full text-sm border border-gray-300 dark:border-gray-600">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium text-gray-600 dark:text-gray-300">Platform</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right font-medium text-gray-600 dark:text-gray-300">Count</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right font-medium text-gray-600 dark:text-gray-300">Yes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($platformSummary as $platform)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-gray-700 dark:text-gray-300">
                                    <a href="{{ route('scope-review.index', ['year' => $selectedYear, 'platform' => $platform->platform]) }}" class="hover:underline">{{ $platform->platform }}</a>
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($platform->total) }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($platform->yes_bids) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="border border-gray-300 dark:border-gray-600 px-3 py-4 text-center text-gray-400 dark:text-gray-500">No platform data.</td></tr>
                        @endforelse
                        @if($platformSummary->isNotEmpty())
                            <tr class="bg-gray-50 dark:bg-gray-700 font-semibold">
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-gray-900 dark:text-gray-100">Total</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($platformTotalCount) }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($platformTotalYes) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ PENDING REVIEW BY ESTIMATOR ============ --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pending Review by Estimator</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($estimatorTotalPending) }} pending total</span>
            </div>
            <table class="min-w-full text-sm border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium text-gray-600 dark:text-gray-300">Estimator</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right font-medium text-gray-600 dark:text-gray-300">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estimatorSummary as $row)
                        <tr class="{{ $row['pending_review'] > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 {{ $row['pending_review'] > 0 ? 'text-red-800 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                @if($row['pending_review'] > 0)
                                    <a href="{{ route('scope-review.index', ['year' => $selectedYear, 'assigned_estimator_id' => $row['estimator']->id, 'decision' => '__pending__']) }}" class="hover:underline">{{ $row['estimator']->name }}</a>
                                @else
                                    {{ $row['estimator']->name }}
                                @endif
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-right tabular-nums {{ $row['pending_review'] > 0 ? 'font-semibold text-red-800 dark:text-red-300' : 'text-gray-900 dark:text-gray-100' }}">{{ number_format($row['pending_review']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
