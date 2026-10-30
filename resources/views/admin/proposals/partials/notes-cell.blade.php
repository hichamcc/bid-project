{{-- Notes column content on the list: latest note on one line, or an "add" prompt. --}}
@if($proposal->latestNote)
    <span class="cell-truncate" style="max-width: 20rem;" title="{{ $proposal->latestNote->body }}">
        <span class="text-gray-500 text-xs">
            {{ $proposal->latestNote->user?->name ?? 'Deleted user' }},
            {{ $proposal->latestNote->created_at->format('M d g:i A') }}:
        </span>
        {{ $proposal->latestNote->body }}
    </span>
    @if($proposal->notes_count > 1)
        <span class="text-xs text-gray-400">+{{ $proposal->notes_count - 1 }} more</span>
    @endif
@else
    <span class="text-xs text-blue-600">+ Add note</span>
@endif
