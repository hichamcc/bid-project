{{-- Proposals for projects where this GC is the primary GC. Filters work in the browser. --}}
@php
    $resultOptions = ['win' => 'Win', 'loss' => 'Loss', 'pending' => 'Pending'];
@endphp

<div data-filter-scope="proposals">
    <div class="flex items-center justify-between mb-2 text-sm">
        <span class="text-gray-600" data-filter-count></span>
        <button type="button" class="text-gray-600 hover:text-gray-900 underline" data-filter-clear style="display:none">Clear filters</button>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="gc-table">
            <thead>
                <tr>
                    <th>Submitted</th>
                    <th>Project</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Original</th>
                    <th>VE</th>
                    <th>GC Result</th>
                    <th>ART Result</th>
                    <th>Responded</th>
                    <th>Emails</th>
                    <th></th>
                </tr>
                <tr class="filters">
                    <th><input class="gc-filter" data-filter-col="0" placeholder="Filter…"></th>
                    <th><input class="gc-filter" data-filter-col="1" placeholder="Name or job #"></th>
                    <th>
                        <select class="gc-filter" data-filter-col="2" data-filter-name="type">
                            <option value="">All</option>
                            <option value="mu">MU</option>
                            <option value="non mu">NON MU</option>
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="3" data-filter-name="status">
                            <option value="">All</option>
                            @foreach(\App\Models\Proposal::STATUSES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                            <option value="none">Not set</option>
                        </select>
                    </th>
                    <th><input class="gc-filter" data-filter-col="4" placeholder="$"></th>
                    <th><input class="gc-filter" data-filter-col="5" placeholder="$"></th>
                    <th>
                        <select class="gc-filter" data-filter-col="6" data-filter-name="gc_result">
                            <option value="">All</option>
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="7" data-filter-name="art">
                            <option value="">All</option>
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>
                        <select class="gc-filter" data-filter-col="8" data-filter-name="responded">
                            <option value="">All</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposals as $proposal)
                    @php
                        $isMu = optional($proposal->project)->type === 'MULTIUNIT';
                        $gcResult = in_array($proposal->result_gc, ['win', 'loss']) ? $proposal->result_gc : 'pending';
                        $artResult = in_array($proposal->result_art, ['win', 'loss']) ? $proposal->result_art : 'pending';
                        $responded = $proposal->responded === 'yes' ? 'yes' : 'no';
                    @endphp
                    <tr data-row>
                        <td>{{ $proposal->submission_date?->format('m/d/Y') ?? '-' }}</td>
                        <td class="wrap">
                            <a href="{{ route('admin.proposals.show', $proposal) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $proposal->project->name ?? 'Project deleted' }}
                            </a>
                            @if($proposal->job_number)
                                <span class="text-gray-500 text-xs">#{{ $proposal->job_number }}</span>
                            @endif
                        </td>
                        <td data-value="{{ $isMu ? 'mu' : 'non mu' }}">{{ $isMu ? 'MU' : 'NON MU' }}</td>
                        <td data-value="{{ $proposal->status ?: 'none' }}">
                            @include('admin.proposals.partials.status-badge', ['status' => $proposal->status])
                        </td>
                        <td>{{ $proposal->price_original ? '$' . number_format($proposal->price_original, 2) : '-' }}</td>
                        <td>{{ $proposal->price_ve ? '$' . number_format($proposal->price_ve, 2) : '-' }}</td>
                        <td data-value="{{ $gcResult }}">@include('admin.proposals.partials.result-badge', ['result' => $proposal->result_gc])</td>
                        <td data-value="{{ $artResult }}">@include('admin.proposals.partials.result-badge', ['result' => $proposal->result_art])</td>
                        <td data-value="{{ $responded }}">
                            <span class="{{ $responded === 'yes' ? 'text-green-700' : 'text-red-700' }}">{{ ucfirst($responded) }}</span>
                        </td>
                        <td>
                            @if($proposal->emails_count)
                                <a class="text-blue-600 hover:text-blue-800 cursor-pointer"
                                   @click="tab = 'emails'; gcTableFilter.set('emails', { project: @js($proposal->project->name ?? '') })">{{ $proposal->emails_count }}</a>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.proposals.show', $proposal) }}" class="text-blue-600 hover:text-blue-800">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-gray-500 py-6">No proposals for this GC{{ $hasRange ? ' in this date range' : '' }}.</td></tr>
                @endforelse
                <tr data-empty-filtered style="display:none"><td colspan="11" class="text-center text-gray-500 py-6">No proposals match these filters.</td></tr>
            </tbody>
        </table>
    </div>
</div>
