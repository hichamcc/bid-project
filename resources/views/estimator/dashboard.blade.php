@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 overflow-hidden shadow-lg sm:rounded-lg mb-8">
            <div class="p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">Welcome back, {{ Auth::user()->name }}</h1>
                        <p class="mt-2 text-blue-100">Here's what's happening with your projects today.</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-blue-600 text-xl font-bold">
                                {{ Auth::user()->initials() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-blue-100 text-sm">
                    Last updated: {{ now()->format('l, F j, Y \a\t g:i A') }}
                </div>
            </div>
        </div>

        <!-- Priority Alerts -->
        @if($overdueProjects > 0 || $dueSoonProjects > 0)
            <div class="mb-8">
                @if($overdueProjects > 0)
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>{{ $overdueProjects }} {{ Str::plural('project', $overdueProjects) }} overdue!</strong> 
                                    These require immediate attention.
                                    <a href="{{ route('estimator.projects.index', ['due_filter' => 'overdue']) }}" 
                                       class="font-medium underline hover:text-red-800 ml-2">
                                        View overdue projects →
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($dueSoonProjects > 0)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>{{ $dueSoonProjects }} {{ Str::plural('project', $dueSoonProjects) }} due soon.</strong> 
                                    Plan your work accordingly.
                                    <a href="{{ route('estimator.projects.index', ['due_filter' => 'due_soon']) }}" 
                                       class="font-medium underline hover:text-yellow-800 ml-2">
                                        View due soon →
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Performance Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Projects -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProjects) }}</p>
                            <p class="text-sm font-medium text-gray-500">Total Projects</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Projects -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($inProgressProjects) }}</p>
                            <p class="text-sm font-medium text-gray-500">In Progress</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($overdueProjects) }}</p>
                            <p class="text-sm font-medium text-gray-500">Overdue</p>
                        </div>
                    </div>
                    @if($overdueProjects > 0)
                        <div class="mt-3">
                            <a href="{{ route('estimator.projects.index', ['due_filter' => 'overdue']) }}" 
                               class="text-xs text-red-600 hover:text-red-800 font-medium">
                                Review now →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Due Soon -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($dueSoonProjects) }}</p>
                            <p class="text-sm font-medium text-gray-500">Due Soon</p>
                        </div>
                    </div>
                    @if($dueSoonProjects > 0)
                        <div class="mt-3">
                            <a href="{{ route('estimator.projects.index', ['due_filter' => 'due_soon']) }}" 
                               class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">
                                Plan ahead →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($completedProjects) }}</p>
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Projects (2/3 width) -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-900">My Active Projects</h3>
                            <a href="{{ route('estimator.projects.index') }}" 
                               class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium transition duration-150">
                                View All Projects
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @forelse($recentProjects as $project)
                            <div class="flex items-center justify-between py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h4 class="text-lg font-medium text-gray-900 truncate">
                                            <a href="{{ route('estimator.projects.show', $project) }}" 
                                               class="hover:text-blue-600 transition duration-150">
                                                {{ $project->name }}
                                            </a>
                                        </h4>
                                        
                                        @if($project->statusRecord)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                                {{ $project->status }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($project->description)
                                        <p class="text-gray-600 text-sm mb-2">{{ Str::limit($project->description, 80) }}</p>
                                    @endif

                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        @if($project->client_name)
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                {{ $project->client_name }}
                                            </span>
                                        @endif
                                        
                                        @if($project->due_date)
                                            <span class="flex items-center {{ $project->due_date->isPast() ? 'text-red-600' : (intval($project->due_date->diffForHumans() ) <= 3 ? 'text-yellow-600' : '') }}">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Due {{ $project->due_date->format('M d') }}
                                                @if($project->due_date->isPast())
                                                    ({{ intval($project->due_date->diffForHumans() ) }} days overdue)
                                                @elseif($project->due_date->diffInDays() <= 3)
                                                    ({{ intval($project->due_date->diffForHumans() ) }} days left)
                                                @endif
                                            </span>
                                        @endif

                                        <span class="text-gray-400">•</span>
                                        <span>Updated {{ $project->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="ml-6 flex-shrink-0">
                                    <a href="{{ route('estimator.projects.show', $project) }}" 
                                       class="bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition duration-150">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">No active projects</h3>
                                <p class="mt-2 text-gray-500">You don't have any projects assigned yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar (1/3 width) -->
            <div class="space-y-8">
                <!-- Projects by Status -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Project Status Overview</h3>
                    </div>
                    <div class="p-6">
                        @forelse($projectsByStatus as $status => $count)
                            @php
                                $percentage = $totalProjects > 0 ? round(($count / $totalProjects) * 100) : 0;
                                $colors = [
                                    'pending' => 'gray',
                                    'in_progress' => 'blue',
                                    'completed' => 'green',
                                    'on_hold' => 'yellow',
                                    'cancelled' => 'red'
                                ];
                                $color = $colors[$status] ?? 'gray';
                            @endphp
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center flex-1">
                                    <div class="w-3 h-3 rounded-full bg-{{ $color }}-500 mr-3"></div>
                                    <span class="text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ $count }}</span>
                                    <span class="text-xs text-gray-500">({{ $percentage }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                                <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm text-center py-4">No project data available</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        @forelse($recentRemarks->take(5) as $remark)
                            <div class="flex space-x-3 mb-4 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                        {{ Auth::user()->initials() }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium">
                                        <a href="{{ route('estimator.projects.show', $remark->project) }}" 
                                           class="hover:text-blue-600">
                                            {{ $remark->project->name }}
                                        </a>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($remark->remark, 60) }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $remark->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('estimator.projects.index') }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition duration-150 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            View All Projects
                        </a>
                        
                        @if($overdueProjects > 0)
                            <a href="{{ route('estimator.projects.index', ['due_filter' => 'overdue']) }}" 
                               class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition duration-150 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Review Overdue ({{ $overdueProjects }})
                            </a>
                        @endif
                        
                        @if($dueSoonProjects > 0)
                            <a href="{{ route('estimator.projects.index', ['due_filter' => 'due_soon']) }}" 
                               class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg text-sm font-medium transition duration-150 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Due Soon ({{ $dueSoonProjects }})
                            </a>
                        @endif

                        @if($inProgressProjects > 0)
                            <a href="{{ route('estimator.projects.index', ['status' => 'in_progress']) }}" 
                               class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition duration-150 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Active Projects ({{ $inProgressProjects }})
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection