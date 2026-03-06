@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        Monthly Workload — {{ $currentDate->format('F Y') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Jobs shown on their assigned date (due date − 2 days). Mon–Sat only.
                    </p>
                </div>
                <a href="{{ route('admin.allocation.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Distribution
                </a>
            </div>
        </div>

        <!-- Month Navigator -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-4">
                <form method="GET" action="{{ route('admin.allocation.monthly') }}" class="flex items-center gap-4 flex-wrap">
                    <a href="{{ route('admin.allocation.monthly', ['month' => $currentDate->copy()->subMonth()->month, 'year' => $currentDate->copy()->subMonth()->year]) }}"
                       class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded font-medium">
                        &larr; Prev
                    </a>
                    <select name="month"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="2099"
                           class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded">
                        Go
                    </button>
                    <a href="{{ route('admin.allocation.monthly', ['month' => $currentDate->copy()->addMonth()->month, 'year' => $currentDate->copy()->addMonth()->year]) }}"
                       class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded font-medium">
                        Next &rarr;
                    </a>
                </form>
            </div>
        </div>

        <!-- Grid Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="overflow-auto" style="max-height: calc(100vh - 280px);">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="sticky top-0 z-20">
                        <!-- Estimator name row -->
                        <tr class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap border-r border-gray-200 dark:border-gray-600 w-36">
                                Date
                            </th>
                            @foreach($estimators as $estimator)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 min-w-[160px]">
                                    <div>{{ $estimator->name }}</div>
                                    <div class="font-normal text-gray-400 dark:text-gray-400 normal-case mt-0.5">
                                        {{ ucfirst(str_replace('_', ' ', $estimator->role)) }}
                                        @if($estimator->weight && $estimator->weight != 1)
                                            · {{ $estimator->weight }}x
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>

                        <!-- Monthly totals row -->
                        <tr class="bg-blue-50 dark:bg-blue-900/20 border-b-2 border-blue-200 dark:border-blue-700">
                            <td class="sticky left-0 z-10 bg-blue-50 dark:bg-blue-900/20 px-4 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 border-r border-blue-200 dark:border-blue-700 whitespace-nowrap">
                                Month Total
                            </td>
                            @foreach($estimators as $estimator)
                                <td class="px-4 py-2 text-center border-r border-blue-200 dark:border-blue-700">
                                    <span class="font-bold text-blue-700 dark:text-blue-300">
                                        {{ $totals[$estimator->id]['total_days'] }}d
                                    </span>
                                    @if(($estimator->weight ?? 1) != 1)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                            (eff. {{ $totals[$estimator->id]['effective_load'] }})
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($days as $day)
                            @php
                                $dateKey    = $day->format('Y-m-d');
                                $isToday    = $day->isToday();
                                $isSaturday = $day->isSaturday();
                                $isMonday   = $day->isMonday();
                                $hasAnyJob  = collect($jobsByDateAndUser[$dateKey] ?? [])->isNotEmpty();
                            @endphp
                            @if($isMonday && !$loop->first)
                                <tr class="border-t-4 border-gray-400 dark:border-gray-500">
                                    <td colspan="{{ $estimators->count() + 1 }}" class="p-0"></td>
                                </tr>
                            @endif
                            <tr class="
                                {{ $isToday ? 'bg-yellow-50 dark:bg-yellow-900/10' : ($isSaturday ? 'bg-gray-50 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800') }}
                                {{ $hasAnyJob ? '' : 'opacity-60' }}
                                hover:bg-gray-50 dark:hover:bg-gray-700/50
                            ">
                                <!-- Date cell -->
                                <td class="sticky left-0 z-10 px-4 py-3 border-r border-gray-200 dark:border-gray-600 whitespace-nowrap
                                    {{ $isToday ? 'bg-yellow-50 dark:bg-yellow-900/10' : ($isSaturday ? 'bg-gray-50 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800') }}">
                                    <div class="font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $day->format('D') }}
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">
                                        {{ $day->format('M d') }}
                                        @if($isToday)
                                            <span class="ml-1 px-1 py-0.5 text-xs bg-yellow-400 text-yellow-900 rounded">Today</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- One cell per estimator -->
                                @foreach($estimators as $estimator)
                                    @php
                                        $jobs = $jobsByDateAndUser[$dateKey][$estimator->id] ?? [];
                                    @endphp
                                    <td class="px-3 py-2 border-r border-gray-100 dark:border-gray-700 align-top min-w-[200px]">
                                        @if(count($jobs) > 0)
                                            <div class="space-y-1">
                                                @foreach($jobs as $job)
                                                    @php
                                                        $isMU      = $job->job_type === 'MU';
                                                        $labelMap  = $isMU ? $muLabels : $nonMuLabels;
                                                        $typeLabel = $isMU ? 'MU' : 'NM';
                                                        $assigned  = $job->estimators->map(fn($e) => $labelMap[$e->id] ?? '?')->implode(', ');
                                                    @endphp
                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded text-xs whitespace-nowrap
                                                        {{ $isMU ? 'bg-purple-50 dark:bg-purple-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                                                        <span class="font-bold text-gray-800 dark:text-gray-100">{{ $job->job_number }}</span>
                                                        <span class="px-1 py-0.5 rounded font-semibold
                                                            {{ $isMU ? 'bg-purple-200 text-purple-800 dark:bg-purple-800 dark:text-purple-100' : 'bg-blue-200 text-blue-800 dark:bg-blue-800 dark:text-blue-100' }}">
                                                            {{ $typeLabel }}
                                                        </span>
                                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $job->days_required }}D</span>
                                                        <span class="text-gray-500 dark:text-gray-400">{{ $assigned }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
