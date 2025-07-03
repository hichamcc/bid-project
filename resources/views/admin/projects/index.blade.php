@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">Projects Management</h2>
                    <a href="{{ route('admin.projects.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Project
                    </a>
                </div>
            </div>
        </div>

     <!-- Search and Filter -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('admin.projects.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search projects..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
 
                <!-- Status Filter -->
                <div class="">
                    <div class="multi-select-dropdown">
                        <button type="button" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-left flex items-center justify-between"
                                onclick="toggleDropdown()">
                            <span id="display-text">All Statuses</span>
                            <svg id="dropdown-arrow" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                
                        <div id="dropdown-menu" class="hidden absolute z-50 w-56 mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                            <!-- Select All Option -->
                            <div class="p-2 border-b border-gray-200">
                                <label class="flex items-center hover:bg-gray-50 p-1 rounded cursor-pointer">
                                    <input type="checkbox" 
                                           id="select-all"
                                           onchange="toggleAll()"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Select All</span>
                                </label>
                            </div>
                
                            <!-- Status Options -->
                            @foreach($statuses as $status)
                                <label class="flex items-center hover:bg-gray-50 p-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="status[]" 
                                           value="{{ $status->name }}"
                                           {{ in_array($status->name, request('status', [])) ? 'checked' : '' }}
                                           onchange="updateDisplayText()"
                                           class="status-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $status->name }}</span>
                                    @if(isset($status->count))
                                        <span class="ml-auto text-xs text-gray-500">({{ $status->count }})</span>
                                    @endif
                                </label>
                            @endforeach
            
                        </div>
                    </div>
                </div>

 
                <!-- Type Filter -->
                <div>
                    <select name="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->name }}" {{ request('type') === $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
 
                <!-- Assigned To Filter -->
                <div>
                    <select name="assigned_to" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Estimators</option>
                        @foreach($estimators as $estimator)
                            <option value="{{ $estimator->id }}" {{ request('assigned_to') == $estimator->id ? 'selected' : '' }}>
                                {{ $estimator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
 
                <!-- Due Date Filter -->
                <div>
                    <select name="due_filter" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Due Dates</option>
                        <option value="overdue" {{ request('due_filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="due_soon" {{ request('due_filter') === 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                        <option value="no_due_date" {{ request('due_filter') === 'no_due_date' ? 'selected' : '' }}>No Due Date</option>
                    </select>
                </div>
            </div>
 
            <div class="flex space-x-2">
                <button type="submit" 
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'type', 'assigned_to', 'due_filter']))
                    <a href="{{ route('admin.projects.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
 </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Projects Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Other GC
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned To
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($projects as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $project->name }}
                                        </div>
                                        @if($project->gc)
                                            <div class="text-sm text-gray-500">
                                                GC: {{ $project->gc }}
                                            </div>
                                        @endif
                                        @if($project->rfi)
                                            <div class="text-sm text-gray-500">
                                                RFI: {{ $project->rfi }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                         <!-- Other GCs -->
                                @if($project->other_gc && count($project->other_gc) > 0)
                                <div class=" gap-1">
                                    @foreach($project->other_gc as $otherGc)
                                        <div     class="items-center px-2 py-1 mt-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $otherGc }}
                                        </div> 
                                    @endforeach
                                </div>
                            @endif
                                      
                        </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($project->statusRecord)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: {{ $project->statusRecord->color }}20; color: {{ $project->statusRecord->color }};">
                                                {{ $project->status }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                No Status
                                            </span>
                                        @endif

                                        @if($project->status === 'RFI REQUESTED' )
                                          <div class="flex flex-col">
                                            @if($project->rfi_request_date)
                                            <span class="text-gray-500 text-sm">
                                                 Request : {{ $project->rfi_request_date->format('M d, Y') }} 
                                            </span>
                                            @endif      
                                            @if($project->rfi_due_date)
                                            <span class=" text-gray-500 text-sm">
                                                 Due : {{ $project->rfi_due_date->format('M d, Y') }} (
                                                @if($project->daysUntilRFI() > 2)
                                                    <span class="text-yellow-600 text-xs">Due in {{ $project->daysUntilRFI() }} days</span>
                                                @elseif($project->daysUntilRFI() > 0 && $project->daysUntilRFI() <= 2)
                                                    <span class="text-red-500 font-bold text-xs">Due in {{ $project->daysUntilRFI() }} days</span>
                                                @elseif($project->daysUntilRFI() === 0) 
                                                    <span class="text-red-500 font-bold text-xs">Due today</span>
                                                @else
                                                    <span class="text-red-500 font-bold text-xs">Overdue</span>
                                                @endif
                                                    )
                                            </span>
                                            @endif
                                        </div>
                                        @endif
                                 
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($project->typeRecord)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" style="background-color: {{ $project->typeRecord->color }}20; color: {{ $project->typeRecord->color }};">
                                                {{ $project->type }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($project->assignedTo)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                                    {{ $project->assignedTo->initials() }}
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $project->assignedTo->name }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-sm">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($project->due_date)
                                        <div class="flex flex-col">
                                            <span class="{{ $project->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                                {{ $project->due_date->format('M d, Y') }}
                                            </span>
                                            @if($project->isOverdue())
                                                <span class="text-red-500 text-xs">Overdue</span>
                                            @elseif($project->daysUntilDue() !== null  && $project->daysUntilDue() >= 3)
                                                <span class="text-yellow-600 text-xs">Due in {{ $project->daysUntilDue() }} days</span>
                                            @elseif($project->daysUntilDue() !== null  && $project->daysUntilDue() > 0)
                                                <span class="text-red-500 text-xs font-bold">Due in {{ $project->daysUntilDue() }} days</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">No due date</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form method="POST" 
                                              action="{{ route('admin.projects.destroy', $project) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this project?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No projects found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $projects->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Initialize the display text when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateDisplayText();
        updateSelectAllState();
    });
    
    function toggleDropdown() {
        const menu = document.getElementById('dropdown-menu');
        const arrow = document.getElementById('dropdown-arrow');
        
        menu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }
    
    function updateDisplayText() {
        const checkboxes = document.querySelectorAll('input[name="status[]"]:checked');
        const displayText = document.getElementById('display-text');
        const totalCheckboxes = document.querySelectorAll('input[name="status[]"]').length;
        
        if (checkboxes.length === 0) {
            displayText.textContent = 'All Statuses';
            displayText.className = 'text-gray-500';
        } else if (checkboxes.length === 1) {
            displayText.textContent = checkboxes[0].value;
            displayText.className = 'text-gray-900 font-medium';
        } else if (checkboxes.length === totalCheckboxes) {
            displayText.textContent = 'All Statuses Selected';
            displayText.className = 'text-gray-900 font-medium';
        } else {
            displayText.textContent = `${checkboxes.length} Statuses Selected`;
            displayText.className = 'text-gray-900 font-medium';
        }
        
        // Update select all checkbox state
        updateSelectAllState();
    }
    
    function updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('select-all');
        const statusCheckboxes = document.querySelectorAll('.status-checkbox');
        const checkedStatusCheckboxes = document.querySelectorAll('.status-checkbox:checked');
        
        if (checkedStatusCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedStatusCheckboxes.length === statusCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
    
    function toggleAll() {
        const selectAllCheckbox = document.getElementById('select-all');
        const statusCheckboxes = document.querySelectorAll('.status-checkbox');
        
        statusCheckboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        updateDisplayText();
    }
    
    function clearAll() {
        const checkboxes = document.querySelectorAll('input[name="status[]"]');
        const selectAllCheckbox = document.getElementById('select-all');
        
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = false;
        });
        
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        
        updateDisplayText();
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.multi-select-dropdown');
        if (!dropdown.contains(event.target)) {
            const menu = document.getElementById('dropdown-menu');
            const arrow = document.getElementById('dropdown-arrow');
            
            menu.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    document.getElementById('dropdown-menu').addEventListener('click', function(event) {
        event.stopPropagation();
    });
    </script>
@endsection