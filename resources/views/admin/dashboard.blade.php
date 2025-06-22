@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Admin Dashboard</h2>
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y \a\t g:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
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
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalProjects) }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm text-green-600">
                            <span>+{{ $projectsThisMonth }} this month</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Overdue Projects</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($overdueProjects) }}</dd>
                            </dl>
                        </div>
                    </div>
                    @if($overdueProjects > 0)
                        <div class="mt-4">
                            <a href="{{ route('admin.projects.index', ['due_filter' => 'overdue']) }}" 
                               class="text-sm text-red-600 hover:text-red-800">
                                View overdue projects →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Due Soon -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Due Soon</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($dueSoonProjects) }}</dd>
                            </dl>
                        </div>
                    </div>
                    @if($dueSoonProjects > 0)
                        <div class="mt-4">
                            <a href="{{ route('admin.projects.index', ['due_filter' => 'due_soon']) }}" 
                               class="text-sm text-yellow-600 hover:text-yellow-800">
                                View due soon →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalUsers) }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-sm text-gray-600">
                            {{ $activeEstimators }} estimators, {{ $adminUsers }} admins
                        </div>
                    </div>
                </div>
            </div>
        </div>

           <!-- Recent Activities -->
           <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Activities</h3>
                    <span class="text-sm text-gray-500">Latest project remarks</span>
                </div>
                <div class="flow-root">
                    <ul class="-mb-8">
                        @forelse($recentActivities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                                {{ $activity->user->initials() }}
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ $activity->user->name }}</span>
                                                    added a remark to
                                                    <a href="{{ route('admin.projects.show', $activity->project) }}" 
                                                       class="font-medium text-blue-600 hover:text-blue-800">
                                                        {{ $activity->project->name }}
                                                    </a>
                                                </p>
                                                <div class="mt-2 text-sm text-gray-700">
                                                    "{{ Str::limit($activity->remark, 100) }}"
                                                </div>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm text-center py-8">
                                No recent activities
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Project Trends Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Project Creation Trend</h3>
                    <div class="h-64">
                        <canvas id="projectTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Projects by Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Projects by Status</h3>
                    <div class="space-y-3">
                        @forelse($projectsByStatus as $status => $count)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                    <span class="text-sm text-gray-900">{{ $status ?: 'No Status' }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-600">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No projects found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        

        <!-- Workload Distribution and Recent Projects -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Workload by Estimator -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Workload by Estimator</h3>
                    <div class="space-y-3">
                        @forelse($workloadByEstimator as $estimator)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium mr-3">
                                        {{ $estimator->initials() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $estimator->name }}</div>
                                        <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $estimator->role)) }}</div>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-600">
                                    {{ $estimator->assigned_projects_count }} active
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No estimators found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Projects</h3>
                        <a href="{{ route('admin.projects.index') }}" 
                           class="text-sm text-blue-600 hover:text-blue-800">
                            View all →
                        </a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentProjects as $project)
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="hover:text-blue-600">
                                            {{ $project->name }}
                                        </a>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($project->statusRecord)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                                {{ $project->status }}
                                            </span>
                                        @endif
                                        @if($project->assignedTo)
                                            <span class="text-xs text-gray-500">
                                                {{ $project->assignedTo->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $project->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No recent projects</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

     
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Trend Chart
    const ctx = document.getElementById('projectTrendChart').getContext('2d');
    const monthlyData = @json($monthlyTrend);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Projects Created',
                data: monthlyData.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection