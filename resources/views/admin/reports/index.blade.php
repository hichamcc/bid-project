@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">User Performance Reports</h2>
                <p class="text-gray-600 mt-2">View statistics and performance metrics for estimators</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- User Selection -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Select Estimator *</label>
                            <select id="user_id" 
                                    name="user_id" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose an estimator</option>
                                @foreach($estimators as $estimator)
                                    <option value="{{ $estimator->id }}" {{ $filters['user_id'] == $estimator->id ? 'selected' : '' }}>
                                        {{ $estimator->name }} ({{ ucfirst(str_replace('_', ' ', $estimator->role)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <select id="year" 
                                    name="year" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $filters['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month (Optional)</label>
                            <select id="month" 
                                    name="month" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Months</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filters['month'] == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedUser && $stats)
        <!-- Report Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-blue-50 border-b border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-blue-900">{{ $selectedUser->name }}</h3>
                        <p class="text-blue-700">{{ ucfirst(str_replace('_', ' ', $selectedUser->role)) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-600">Report Period</p>
                        <p class="font-medium text-blue-900">
                            @if($filters['month'])
                                {{ Carbon\Carbon::create(null, $filters['month'], 1)->format('F') }} 
                            @endif
                            {{ $filters['year'] }}
                            @if(!$filters['month'])
                                (Full Year)
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Total Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-blue-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_projects'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Proposals -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-green-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Proposals</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_proposals'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Entries -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-purple-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Progress Entries</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['progress_entries'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        @if(!empty($stats['status_counts']))
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Status Distribution</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($stats['status_counts'] as $status => $count)
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700">{{ $status ?: 'No Status' }}</h4>
                        <p class="text-lg font-bold text-gray-900">{{ $count }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Proposal Response & Follow-up Stats -->
        @if($stats['total_proposals'] > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Response Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Proposal Responses</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-green-50 p-3 rounded-lg text-center">
                                <h4 class="text-sm font-medium text-green-700">Responded</h4>
                                <p class="text-xl font-bold text-green-900">{{ $stats['responded_yes'] }}</p>
                            </div>
                            <div class="bg-red-50 p-3 rounded-lg text-center">
                                <h4 class="text-sm font-medium text-red-700">No Response</h4>
                                <p class="text-xl font-bold text-red-900">{{ $stats['responded_no'] }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <h4 class="text-sm font-medium text-gray-700">Unknown</h4>
                                <p class="text-xl font-bold text-gray-900">{{ $stats['no_response'] }}</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700">Overall Response Rate</span>
                                <span class="text-xl font-bold text-blue-900">{{ $stats['response_rate'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow-up Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Follow-up Activities</h3>
                    <div class="space-y-3">
                        @if($stats['first_follow_ups'] > 0)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <span class="text-sm font-medium text-gray-700">1st Follow-ups</span>
                                <div class="text-xs text-gray-500">{{ $stats['first_follow_up_responded'] }} responded ({{ $stats['first_follow_up_response_rate'] }}%)</div>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['first_follow_ups'] }}</span>
                        </div>
                        @endif
                        
                        @if($stats['second_follow_ups'] > 0)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <span class="text-sm font-medium text-gray-700">2nd Follow-ups</span>
                                <div class="text-xs text-gray-500">{{ $stats['second_follow_up_responded'] }} responded ({{ $stats['second_follow_up_response_rate'] }}%)</div>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['second_follow_ups'] }}</span>
                        </div>
                        @endif
                        
                        @if($stats['third_follow_ups'] > 0)
                        <div class="flex justify-between items-center py-2">
                            <div>
                                <span class="text-sm font-medium text-gray-700">3rd Follow-ups</span>
                                <div class="text-xs text-gray-500">{{ $stats['third_follow_up_responded'] }} responded ({{ $stats['third_follow_up_response_rate'] }}%)</div>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['third_follow_ups'] }}</span>
                        </div>
                        @endif
                        
                        @if($stats['first_follow_ups'] == 0 && $stats['second_follow_ups'] == 0 && $stats['third_follow_ups'] == 0)
                        <div class="text-center py-4">
                            <span class="text-gray-400">No follow-up activities recorded</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Win/Loss Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Win/Loss Statistics</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">GC Results</h4>
                                <div class="space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Wins:</span>
                                        <span class="font-medium text-green-600">{{ $stats['gc_wins'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Losses:</span>
                                        <span class="font-medium text-red-600">{{ $stats['gc_losses'] }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1">
                                        <span class="text-sm font-medium">Win Rate:</span>
                                        <span class="font-bold text-blue-600">{{ $stats['gc_win_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">ART Results</h4>
                                <div class="space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Wins:</span>
                                        <span class="font-medium text-green-600">{{ $stats['art_wins'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Losses:</span>
                                        <span class="font-medium text-red-600">{{ $stats['art_losses'] }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1">
                                        <span class="text-sm font-medium">Win Rate:</span>
                                        <span class="font-bold text-blue-600">{{ $stats['art_win_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700">Total Proposals:</span>
                                <span class="text-xl font-bold text-blue-900">{{ $stats['total_proposals'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Volume Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Volume</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-sm text-gray-600">Total Hours:</span>
                                <span class="font-medium">{{ number_format($stats['total_hours'], 1) }} hrs</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-sm text-gray-600">Square Footage:</span>
                                <span class="font-medium">{{ number_format($stats['total_sqft']) }} sq ft</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-sm text-gray-600">Total Slabs:</span>
                                <span class="font-medium">{{ $stats['total_slabs'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Progress Entries:</span>
                                <span class="font-medium">{{ $stats['progress_entries'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Stats -->
        @if($stats['total_proposals'] > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-green-700">ARTELYE Total Pricing</h4>
                        <p class="text-xl font-bold text-green-900">${{ number_format($stats['total_proposal_value']) }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-700">ARTELYE VE Total Pricing</h4>
                        <p class="text-xl font-bold text-blue-900">${{ number_format($stats['total_ve_value']) }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700">GC Total Pricing</h4>
                        <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_gc_price']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @else
        <!-- No Data State -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M9 17a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0116.07 12h15.86a2 2 0 011.664.89l.812 1.22A2 2 0 0036.07 15H37a2 2 0 012 2v18a2 2 0 01-2 2H11a2 2 0 01-2-2V17z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M23 23a4 4 0 100 8 4 4 0 000-8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Report Generated</h3>
                <p class="mt-1 text-sm text-gray-500">Select an estimator from the dropdown above to generate their performance report.</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection