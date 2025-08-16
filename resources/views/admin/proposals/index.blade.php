@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Proposals Management</h2>
                    <a href="{{ route('admin.proposals.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Proposal
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.proposals.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search by project name, GC, or job number..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="responded" class="block text-sm font-medium text-gray-700 mb-1">Responded</label>
                            <select id="responded" 
                                    name="responded" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="yes" {{ request('responded') == 'yes' ? 'selected' : '' }}>Responded</option>
                                <option value="no" {{ request('responded') == 'no' ? 'selected' : '' }}>Not Responded</option>
                            </select>
                        </div>

                        <div>
                            <label for="estimator_id" class="block text-sm font-medium text-gray-700 mb-1">Estimator</label>
                            <select id="estimator_id" 
                                    name="estimator_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Estimators</option>
                                @foreach($estimators as $estimator)
                                    <option value="{{ $estimator->id }}" {{ request('estimator_id') == $estimator->id ? 'selected' : '' }}>
                                        {{ $estimator->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="result_gc" class="block text-sm font-medium text-gray-700 mb-1">Result GC</label>
                            <select id="result_gc" 
                                    name="result_gc" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All GC Results</option>
                                <option value="pending" {{ request('result_gc') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="win" {{ request('result_gc') == 'win' ? 'selected' : '' }}>Win</option>
                                <option value="loss" {{ request('result_gc') == 'loss' ? 'selected' : '' }}>Loss</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="result_art" class="block text-sm font-medium text-gray-700 mb-1">Result ART</label>
                            <select id="result_art" 
                                    name="result_art" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All ART Results</option>
                                <option value="pending" {{ request('result_art') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="win" {{ request('result_art') == 'win' ? 'selected' : '' }}>Win</option>
                                <option value="loss" {{ request('result_art') == 'loss' ? 'selected' : '' }}>Loss</option>
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Apply Filters
                            </button>
                            <a href="{{ route('admin.proposals.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        @if(request()->hasAny(['search', 'responded', 'result_gc', 'result_art', 'estimator_id']))
            <div class="bg-blue-50 border border-blue-200 px-4 py-3 rounded mb-6">
                <p class="text-blue-800">
                    Showing {{ $proposals->total() }} filtered result(s)
                    @if(request('search'))
                        for search: "<strong>{{ request('search') }}</strong>"
                    @endif
                </p>
            </div>
        @endif

        <!-- Proposals Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'gc', 'sort_direction' => request('sort_by') == 'gc' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    GCs
                                    @if(request('sort_by') == 'gc')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'submission_date', 'sort_direction' => request('sort_by') == 'submission_date' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Submission Date
                                    @if(request('sort_by') == 'submission_date')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Follow-ups
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price_original', 'sort_direction' => request('sort_by') == 'price_original' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    ARTELYE Price
                                    @if(request('sort_by') == 'price_original')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price_ve', 'sort_direction' => request('sort_by') == 'price_ve' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    ARTELYE VE Price
                                    @if(request('sort_by') == 'price_ve')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'gc_price', 'sort_direction' => request('sort_by') == 'gc_price' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    GC Price
                                    @if(request('sort_by') == 'gc_price')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'result_gc', 'sort_direction' => request('sort_by') == 'result_gc' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Result GC
                                    @if(request('sort_by') == 'result_gc')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'result_art', 'sort_direction' => request('sort_by') == 'result_art' && request('sort_direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="group inline-flex items-center hover:text-gray-700">
                                    Result ART
                                    @if(request('sort_by') == 'result_art')
                                        @if(request('sort_direction', 'desc') == 'asc')
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                                            </svg>
                                        @else
                                            <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($proposals as $proposal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 ">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $proposal->project->name }}
                                    </div>
                                    @if($proposal->job_number)
                                        <div class="text-xs text-gray-500">
                                            Job #{{ $proposal->job_number }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="space-y-1">
                                        @if($proposal->project->gc)
                                            <div><span class="font-medium text-gray-700">Primary:</span> {{ $proposal->project->gc }}</div>
                                        @endif
                                        @if($proposal->project->other_gc && count($proposal->project->other_gc) > 0)
                                            <div><span class="font-medium text-gray-700">Others:</span> {{ implode(', ', array_slice($proposal->project->other_gc, 0, 2)) }}@if(count($proposal->project->other_gc) > 2)<span class="text-gray-500"> +{{ count($proposal->project->other_gc) - 2 }} more</span>@endif</div>
                                        @endif
                                        @if(!$proposal->project->gc && (!$proposal->project->other_gc || count($proposal->project->other_gc) == 0))
                                            <span class="text-gray-400 text-xs">No GCs specified</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($proposal->submission_date)
                                        <span class="px-2 py-1 text-xs rounded {{ $proposal->responded == 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $proposal->submission_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="space-y-1">
                                        @if($proposal->first_follow_up_date)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">1st:</span>
                                                <span class="px-2 py-1 text-xs rounded {{ $proposal->first_follow_up_respond == 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $proposal->first_follow_up_date->format('M d') }}
                                                </span>
                                                @if($proposal->first_follow_up_attachment)
                                                    <a href="{{ Storage::url($proposal->first_follow_up_attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View attachment">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                        @if($proposal->second_follow_up_date)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">2nd:</span>
                                                <span class="px-2 py-1 text-xs rounded {{ $proposal->second_follow_up_respond == 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $proposal->second_follow_up_date->format('M d') }}
                                                </span>
                                                @if($proposal->second_follow_up_attachment)
                                                    <a href="{{ Storage::url($proposal->second_follow_up_attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View attachment">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                        @if($proposal->third_follow_up_date)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">3rd:</span>
                                                <span class="px-2 py-1 text-xs rounded {{ $proposal->third_follow_up_respond == 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $proposal->third_follow_up_date->format('M d') }}
                                                </span>
                                                @if($proposal->third_follow_up_attachment)
                                                    <a href="{{ Storage::url($proposal->third_follow_up_attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View attachment">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                        @if(!$proposal->first_follow_up_date && !$proposal->second_follow_up_date && !$proposal->third_follow_up_date)
                                            <span class="text-gray-400 text-xs">No follow-ups</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $proposal->price_original ? '$' . number_format($proposal->price_original, 2) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $proposal->price_ve ? '$' . number_format($proposal->price_ve, 2) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $proposal->gc_price ? '$' . number_format($proposal->gc_price, 2) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($proposal->result_gc === 'win')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Win
                                        </span>
                                    @elseif($proposal->result_gc === 'loss')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Loss
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($proposal->result_art === 'win')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Win
                                        </span>
                                    @elseif($proposal->result_art === 'loss')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Loss
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-1">
                                        <a href="{{ route('admin.proposals.show', $proposal) }}" 
                                           class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-full transition-colors"
                                           title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.proposals.edit', $proposal) }}" 
                                           class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-full transition-colors"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.proposals.destroy', $proposal) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this proposal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-full transition-colors"
                                                    title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                    No proposals found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($proposals->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $proposals->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection