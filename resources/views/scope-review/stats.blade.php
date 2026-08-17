@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Scope Review Stats</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bid status, platform, and estimator summary.</p>
                </div>
                <a href="{{ route('scope-review.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        <!-- Headline Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($statCards as $card)
                <div class="{{ $card['bg'] }} shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $card['label'] }}</p>
                        <span class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center {{ $card['icon_bg'] }}">
                            <x-dynamic-component :component="$card['icon']" class="w-4 h-4 {{ $card['icon_color'] }}" />
                        </span>
                    </div>
                    <p class="text-3xl font-bold mt-2 {{ $card['value_color'] }}">{{ $card['value'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Projects</p>
                    <a href="{{ $card['href'] }}" class="inline-flex items-center gap-1 mt-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            <!-- Bid Status Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-blue-800 text-white px-6 py-3">
                    <h3 class="text-lg font-bold text-center tracking-wide">BID STATUS SUMMARY</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-blue-500 text-white">
                            <tr>
                                <th class="px-6 py-2 text-left font-semibold">Status</th>
                                <th class="px-6 py-2 text-right font-semibold">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($bidStatusSummary as $row)
                                @continue(!empty($row['hide_if_zero']) && $row['count'] === 0)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-2 text-gray-800 dark:text-gray-200 font-medium">{{ $row['label'] }}</td>
                                    <td class="px-6 py-2 text-right text-gray-800 dark:text-gray-200">
                                        <a href="{{ route('scope-review.index', $row['filters']) }}"
                                           class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                                            {{ number_format($row['count']) }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-green-100 dark:bg-green-900/30 font-bold">
                                <td class="px-6 py-2 text-green-900 dark:text-green-200">TOTAL YES</td>
                                <td class="px-6 py-2 text-right text-green-900 dark:text-green-200">
                                    <a href="{{ route('scope-review.index', ['decision' => 'approved']) }}" class="hover:underline">
                                        {{ number_format($totalYes) }}
                                    </a>
                                </td>
                            </tr>
                            <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                <td class="px-6 py-2 text-gray-900 dark:text-gray-100">TOTAL PROJECTS</td>
                                <td class="px-6 py-2 text-right text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('scope-review.index') }}" class="hover:underline">
                                        {{ number_format($totalProjects) }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Platforms Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-blue-500 text-white px-6 py-3">
                    <h3 class="text-lg font-bold text-center tracking-wide">PLATFORMS SUMMARY</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-blue-500/90 text-white">
                            <tr>
                                <th class="px-6 py-2 text-left font-semibold">Platform</th>
                                <th class="px-6 py-2 text-right font-semibold">Count</th>
                                <th class="px-6 py-2 text-right font-semibold">Yes Bids</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($platformSummary as $platform)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-2 text-gray-800 dark:text-gray-200 font-medium">{{ $platform->platform }}</td>
                                    <td class="px-6 py-2 text-right text-gray-800 dark:text-gray-200">
                                        <a href="{{ route('scope-review.index', ['platform' => $platform->platform]) }}"
                                           class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ number_format($platform->total) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-2 text-right text-gray-800 dark:text-gray-200">
                                        <a href="{{ route('scope-review.index', ['platform' => $platform->platform, 'decision' => 'approved']) }}"
                                           class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ number_format($platform->yes_bids) }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No platform data.</td>
                                </tr>
                            @endforelse
                            @if($platformSummary->isNotEmpty())
                                <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                    <td class="px-6 py-2 text-gray-900 dark:text-gray-100">TOTAL</td>
                                    <td class="px-6 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($platformTotalCount) }}</td>
                                    <td class="px-6 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($platformTotalYes) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Review by Estimator -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="bg-blue-500 text-white px-6 py-3">
                <h3 class="text-lg font-bold text-center tracking-wide">PENDING REVIEW BY ESTIMATOR (No Bid Status)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Estimator</th>
                            <th class="px-6 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">Pending Review</th>
                            <th class="px-6 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">Total Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($estimatorSummary as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-2 text-gray-800 dark:text-gray-200 font-medium">{{ $row['estimator']->name }}</td>
                                <td class="px-6 py-2 text-right {{ $row['pending_review'] > 0 ? 'bg-red-50 dark:bg-red-900/20 font-bold text-red-700 dark:text-red-300' : 'text-gray-800 dark:text-gray-200' }}">
                                    @if($row['pending_review'] > 0)
                                        <a href="{{ route('scope-review.index', ['assigned_estimator_id' => $row['estimator']->id, 'decision' => '__pending__']) }}"
                                           class="hover:underline">
                                            {{ number_format($row['pending_review']) }}
                                        </a>
                                    @else
                                        {{ number_format($row['pending_review']) }}
                                    @endif
                                </td>
                                <td class="px-6 py-2 text-right text-gray-800 dark:text-gray-200">
                                    <a href="{{ route('scope-review.index', ['assigned_estimator_id' => $row['estimator']->id]) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ number_format($row['total_assigned']) }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                            <td class="px-6 py-2 text-gray-900 dark:text-gray-100">TOTAL</td>
                            <td class="px-6 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($estimatorTotalPending) }}</td>
                            <td class="px-6 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($estimatorTotalAssigned) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
