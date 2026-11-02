{{-- Emails read from the follow-up attachments of this GC's proposals (later also Outlook sync).
     PDF / image uploads have no email details, so only the date, project and file show. --}}
@php
    $followUpLabels = [1 => 'Follow-Up 1', 2 => 'Follow-Up 2', 3 => 'Follow-Up 3', 4 => 'Follow-Up 4'];
@endphp

<div data-filter-scope="emails">
    <div class="flex items-center justify-between mb-2 text-sm">
        <span class="text-gray-600" data-filter-count></span>
        <button type="button" class="text-gray-600 hover:text-gray-900 underline" data-filter-clear style="display:none">Clear filters</button>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="gc-table">
            <thead>
                <tr>
                    <th>Sent On</th>
                    <th>From</th>
                    <th>From Email</th>
                    <th>To</th>
                    <th>To Email</th>
                    <th>Subject</th>
                    <th>Body Preview</th>
                    <th>Attachment</th>
                    <th>Project</th>
                </tr>
                <tr class="filters">
                    @foreach(['date', 'from', 'from_email', 'to', 'to_email', 'subject', 'body', 'attachment', 'project'] as $i => $name)
                        <th><input class="gc-filter" data-filter-col="{{ $i }}" data-filter-name="{{ $name }}" placeholder="Filter…"></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($emails as $email)
                    <tr data-row>
                        <td>
                            {{ $email->display_date?->format('m/d/Y') ?? '-' }}
                            @if($email->sent_at)
                                <div class="text-xs text-gray-500">{{ $email->sent_at->format('g:i A') }}</div>
                            @endif
                        </td>
                        <td>{{ $email->from_name ?? '-' }}</td>
                        <td>
                            @if($email->from_email)
                                <a href="mailto:{{ $email->from_email }}" class="text-blue-600 hover:text-blue-800">{{ $email->from_email }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="wrap">{{ $email->to_name ?? '-' }}</td>
                        <td class="wrap">{{ $email->to_email ?? '-' }}</td>
                        <td class="wrap">
                            @if($email->subject)
                                {{ $email->subject }}
                            @elseif($email->parse_error)
                                <span class="text-red-600 text-xs" title="{{ $email->parse_error }}">Couldn't read this email file</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            @if($email->body_preview)
                                <div class="preview" title="Click to expand" onclick="this.classList.toggle('open')">{{ $email->body_preview }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="wrap">
                            @if($email->file_url)
                                <a href="{{ $email->file_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    {{ strtoupper($email->file_type ?: 'file') }}
                                </a>
                            @endif
                            @if($email->attachments)
                                <div class="text-xs text-gray-500">{{ implode(', ', $email->attachments) }}</div>
                            @endif
                        </td>
                        <td class="wrap">
                            @if($email->proposal)
                                <a href="{{ route('admin.proposals.show', $email->proposal) }}" class="text-blue-600 hover:text-blue-800">{{ $email->proposal->project->name ?? 'Proposal #' . $email->proposal_id }}</a>
                                @if($email->follow_up)
                                    <div class="text-xs text-gray-500">{{ $followUpLabels[$email->follow_up] ?? '' }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-gray-500 py-6">
                        No emails{{ $hasRange ? ' in this date range' : '' }}. Emails come from the follow-up attachments uploaded on this GC's proposals.
                    </td></tr>
                @endforelse
                <tr data-empty-filtered style="display:none"><td colspan="9" class="text-center text-gray-500 py-6">No emails match these filters.</td></tr>
            </tbody>
        </table>
    </div>
</div>
