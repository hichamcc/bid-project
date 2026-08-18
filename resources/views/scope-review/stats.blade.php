@extends('components.layouts.app')

@php
    // Only use color classes already present in the compiled CSS bundle
    // (green/red/yellow/gray/purple/blue) so no rebuild is required.
    $barColors = [
        'green' => 'bg-green-500', 'red' => 'bg-red-500', 'yellow' => 'bg-yellow-500',
        'gray' => 'bg-gray-400', 'purple' => 'bg-purple-500', 'blue' => 'bg-blue-500',
    ];
    $dotColors = $barColors;
    $approvalRate = $totalProjects > 0 ? round($totalYes / $totalProjects * 100) : 0;
@endphp

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Scope Review Analytics</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($totalProjects) }} total opportunities &middot;
                    <span class="text-green-600 dark:text-green-300 font-medium">{{ $approvalRate }}% approved</span>
                </p>
            </div>
            <a href="{{ route('scope-review.index') }}"
               class="inline-flex items-center gap-1.5 text-sm bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to List
            </a>
        </div>

        <!-- Headline Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($statCards as $card)
                <a href="{{ $card['href'] }}"
                   class="group relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $card['bg'] }}"></div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center {{ $card['icon_bg'] }}">
                            <x-dynamic-component :component="$card['icon']" class="w-4 h-4 {{ $card['icon_color'] }}" />
                        </span>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="text-3xl font-extrabold tracking-tight {{ $card['value_color'] }}">{{ $card['value'] }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">{{ $card['label'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Bid Status Breakdown (with bars) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Bid Status Breakdown</h3>
                    <span class="text-xs text-gray-400 dark:text-gray-500">of {{ number_format($totalProjects) }} total</span>
                </div>

                <div class="space-y-3.5">
                    @foreach($bidStatusSummary as $row)
                        @continue(!empty($row['hide_if_zero']) && $row['count'] === 0)
                        @php $pct = $totalProjects > 0 ? round($row['count'] / $totalProjects * 100) : 0; @endphp
                        <a href="{{ route('scope-review.index', $row['filters']) }}" class="block group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full {{ $dotColors[$row['color']] ?? 'bg-gray-400' }}"></span>
                                    {{ $row['label'] }}
                                </span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">
                                    {{ number_format($row['count']) }}
                                    <span class="text-xs font-normal text-gray-400 ml-1">{{ $pct }}%</span>
                                </span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full {{ $barColors[$row['color']] ?? 'bg-gray-400' }} group-hover:opacity-80 transition-opacity"
                                     style="width: {{ max($pct, $row['count'] > 0 ? 3 : 0) }}%"></div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Totals footer -->
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-3">
                    <a href="{{ route('scope-review.index', ['decision' => 'approved']) }}"
                       class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-gray-200 dark:border-gray-600 p-3 hover:shadow-sm transition-shadow">
                        <p class="text-xs font-medium text-green-700 dark:text-green-300">Total Yes</p>
                        <p class="text-2xl font-extrabold text-green-700 dark:text-green-300">{{ number_format($totalYes) }}</p>
                    </a>
                    <a href="{{ route('scope-review.index') }}"
                       class="rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 p-3 hover:shadow-sm transition-shadow">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Total Projects</p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ number_format($totalProjects) }}</p>
                    </a>
                </div>
            </div>

            <!-- Platforms -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Platforms</h3>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Count · Yes</span>
                </div>
                <div class="space-y-2.5">
                    @php $maxPlatform = $platformSummary->max('total') ?: 1; @endphp
                    @forelse($platformSummary as $platform)
                        <a href="{{ route('scope-review.index', ['platform' => $platform->platform]) }}"
                           class="block group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate mr-2">{{ $platform->platform }}</span>
                                <span class="flex items-center gap-1.5 flex-shrink-0 text-sm">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ number_format($platform->total) }}</span>
                                    @if($platform->yes_bids > 0)
                                        <span class="px-1.5 py-0.5 rounded-md text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 tabular-nums">{{ number_format($platform->yes_bids) }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full bg-blue-500 group-hover:opacity-80 transition-opacity"
                                     style="width: {{ round($platform->total / $maxPlatform * 100) }}%"></div>
                            </div>
                        </a>
                    @empty
                        <p class="text-center text-sm text-gray-400 dark:text-gray-500 py-6">No platform data.</p>
                    @endforelse
                </div>
                @if($platformSummary->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-sm">
                        <span class="font-bold text-gray-700 dark:text-gray-300">Total</span>
                        <span class="flex items-center gap-1.5">
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ number_format($platformTotalCount) }}</span>
                            <span class="px-1.5 py-0.5 rounded-md text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ number_format($platformTotalYes) }}</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pending Review by Estimator -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pending Review by Estimator</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Assigned opportunities with no bid decision yet</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-sm font-bold {{ $estimatorTotalPending > 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ number_format($estimatorTotalPending) }} pending
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($estimatorSummary as $row)
                    @if($row['pending_review'] > 0)
                        <a href="{{ route('scope-review.index', ['assigned_estimator_id' => $row['estimator']->id, 'decision' => '__pending__']) }}"
                           class="flex items-center justify-between rounded-xl border border-red-300 dark:border-gray-600 bg-red-50 dark:bg-red-900/10 px-3 py-2 hover:shadow-sm transition-shadow">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate mr-2">{{ $row['estimator']->name }}</span>
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center">{{ $row['pending_review'] }}</span>
                        </a>
                    @else
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 dark:border-gray-700 px-3 py-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400 truncate mr-2">{{ $row['estimator']->name }}</span>
                            <span class="flex-shrink-0 text-xs text-gray-300 dark:text-gray-600">0</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
