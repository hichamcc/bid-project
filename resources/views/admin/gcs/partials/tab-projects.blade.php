{{-- Every project where this GC is the primary or an "other" GC. Filters work in the browser. --}}
@php
    $projectTypes = $projects->pluck('type')->filter()->unique()->sort()->values();
    $projectStatuses = $projects->pluck('status')->filter()->unique()->sort()->values();
    $assignees = $projects->map(fn ($p) => $p->assignedTo?->name)->filter()->unique()->sort()->values();
@endphp

<div data-filter-scope="projects">
    <div class="flex items-center justify-between mb-2 text-sm">
        <span class="text-gray-600" data-filter-count></span>
        <button type="button" class="text-gray-600 hover:text-gray-900 underline" data-filter-clear style="display:none">Clear filters</button>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="gc-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Role</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Due Date</th>
                    <th>Proposal</th>
                    <th></th>
                </tr>
                <tr class="filters">
                    <th><input class="gc-filter" data-filter-col="0" placeholder="Filter…"></th>
                    <th>
                        <select class="gc-filter" data-filter-col="1">
                            <option value="">All</option>
                            <option value="primary">Primary</option>
                            <option value="other">Other</option>
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="2">
                            <option value="">All</option>
                            @foreach($projectTypes as $type)
                                <option value="{{ strtolower($type) }}">{{ $type }}</option>
                            @endforeach
                            <option value="none">No Type</option>
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="3">
                            <option value="">All</option>
                            @foreach($projectStatuses as $status)
                                <option value="{{ strtolower($status) }}">{{ $status }}</option>
                            @endforeach
                            <option value="none">No Status</option>
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="4">
                            <option value="">All</option>
                            @foreach($assignees as $name)
                                <option value="{{ strtolower($name) }}">{{ $name }}</option>
                            @endforeach
                            <option value="none">Unassigned</option>
                        </select>
                    </th>
                    <th><input class="gc-filter" data-filter-col="5" placeholder="Filter…"></th>
                    <th>
                        <select class="gc-filter" data-filter-col="6">
                            <option value="">All</option>
                            <option value="yes">Has proposal</option>
                            <option value="no">No proposal</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    @php
                        $proposal = $project->proposals->first();
                        $isPrimary = $project->gc === $gc->name;
                    @endphp
                    <tr data-row>
                        <td class="wrap">
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">{{ $project->name }}</a>
                        </td>
                        <td data-value="{{ $isPrimary ? 'primary' : 'other' }}">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $isPrimary ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $isPrimary ? 'Primary' : 'Other' }}
                            </span>
                        </td>
                        <td data-value="{{ $project->type ? strtolower($project->type) : 'none' }}">{{ $project->type ?? '-' }}</td>
                        <td data-value="{{ $project->status ? strtolower($project->status) : 'none' }}">
                            @if($project->status)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $project->getStatusColor() }}-100 text-{{ $project->getStatusColor() }}-800">
                                    {{ $project->status }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td data-value="{{ $project->assignedTo ? strtolower($project->assignedTo->name) : 'none' }}">
                            {{ $project->assignedTo->name ?? '-' }}
                        </td>
                        <td>{{ $project->due_date?->format('m/d/Y') ?? '-' }}</td>
                        <td data-value="{{ $proposal ? 'yes' : 'no' }}">
                            @if($proposal)
                                <a href="{{ route('admin.proposals.show', $proposal) }}" class="text-blue-600 hover:text-blue-800 mr-1">View</a>
                                @include('admin.proposals.partials.result-badge', ['result' => $proposal->result_art])
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-gray-500 py-6">No projects for this GC{{ $hasRange ? ' in this date range' : '' }}.</td></tr>
                @endforelse
                <tr data-empty-filtered style="display:none"><td colspan="8" class="text-center text-gray-500 py-6">No projects match these filters.</td></tr>
            </tbody>
        </table>
    </div>
</div>
