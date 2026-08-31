{{-- Append-able notes thread. Uses plain onclick/oninput handlers (not Alpine)
     so it can't double-bind when injected into the modal via innerHTML. --}}
@php $me = auth()->user(); @endphp
<div class="sr-notes"
     data-store-url="{{ route('scope-review.notes.store', $scopeReview) }}"
     data-base-url="{{ url('scope-review/' . $scopeReview->id . '/notes') }}"
     data-csrf="{{ csrf_token() }}">

    @if($scopeReview->noteEntries->isNotEmpty())
        <div class="space-y-3">
            @foreach($scopeReview->noteEntries as $note)
                <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-3">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $note->user?->name ?? 'Unknown' }}</span>
                            <span class="px-1.5 py-0.5 rounded {{ $note->context === 'admin' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' }}">
                                {{ $note->context === 'admin' ? 'Admin' : 'Estimator' }}
                            </span>
                            <span class="text-gray-400">{{ $note->created_at->copy()->setTimezone('America/New_York')->format('M d, Y g:i A') }} EST</span>
                            @if($note->updated_at->gt($note->created_at))
                                <span class="text-gray-400 italic">(edited)</span>
                            @endif
                        </div>
                        @if($note->editableBy($me))
                            <div class="flex items-center gap-2 text-xs flex-shrink-0" data-note-actions="{{ $note->id }}">
                                <button type="button" class="text-blue-600 dark:text-blue-400 hover:underline"
                                        onclick="srNotes.startEdit(this, {{ $note->id }})">Edit</button>
                                <button type="button" class="text-red-600 dark:text-red-400 hover:underline"
                                        onclick="srNotes.destroy(this, {{ $note->id }})">Delete</button>
                            </div>
                        @endif
                    </div>

                    {{-- Read view --}}
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line" data-note-read="{{ $note->id }}">{{ $note->body }}</p>

                    {{-- Inline edit view --}}
                    <div class="mt-1 hidden" data-note-edit="{{ $note->id }}">
                        <textarea rows="3" data-note-edit-input="{{ $note->id }}"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ $note->body }}</textarea>
                        <div class="mt-2 flex gap-2">
                            <button type="button" onclick="srNotes.saveEdit(this, {{ $note->id }})"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                            <button type="button" onclick="srNotes.cancelEdit(this, {{ $note->id }})"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">Cancel</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add a note --}}
    <div class="mt-3">
        <textarea rows="2" placeholder="Add a note..." data-note-new
                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"></textarea>
        <div class="mt-2 flex justify-end">
            <button type="button" onclick="srNotes.add(this)"
                    class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white">
                Add Note
            </button>
        </div>
    </div>
</div>
