{{-- Notes thread shown in the list's notes popup. Uses plain onclick handlers
     because it is injected via innerHTML. The <template data-notes-cell> carries
     the refreshed list cell so the row updates without a page reload. --}}
@php $me = auth()->user(); @endphp
<div class="pn-notes"
     data-store-url="{{ route('admin.proposals.notes.store', $proposal) }}"
     data-base-url="{{ url('admin/proposals/' . $proposal->id . '/notes') }}"
     data-proposal-id="{{ $proposal->id }}">

    <template data-notes-cell>@include('admin.proposals.partials.notes-cell', ['proposal' => $proposal])</template>

    @if($proposal->notes->isNotEmpty())
        <div class="space-y-3 overflow-y-auto" style="max-height: 50vh;">
            @foreach($proposal->notes as $note)
                <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="text-xs">
                            <span class="font-semibold text-gray-800">{{ $note->user?->name ?? 'Deleted user' }}</span>
                            <span class="text-gray-400">· {{ $note->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        @if($note->editableBy($me))
                            <button type="button" class="text-xs text-red-600 hover:underline"
                                    onclick="pnNotes.destroy(this, {{ $note->id }})">Delete</button>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $note->body }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400">No notes yet.</p>
    @endif

    <div class="mt-3">
        <textarea rows="3" placeholder="Add a note..." data-note-new
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        <div class="mt-2 flex justify-end">
            <button type="button" onclick="pnNotes.add(this)"
                    class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white">
                Add Note
            </button>
        </div>
    </div>
</div>
