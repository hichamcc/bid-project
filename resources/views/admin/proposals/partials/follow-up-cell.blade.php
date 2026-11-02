{{-- One follow-up as a single coloured date: green = GC responded, red = no response.
     Params: $date, $respond, $attachment, $previewUrl (opens the attachment popup) --}}
@if($date)
    <span class="inline-flex items-center gap-1">
        <span class="px-2 py-0.5 text-xs rounded {{ $respond === 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $date->format('M d, Y') }}
        </span>
        @if($attachment)
            <a href="{{ Storage::url($attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View attachment"
               @isset($previewUrl) data-email-preview="{{ $previewUrl }}" @endisset>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
        @endif
    </span>
@else
    <span class="text-gray-400">-</span>
@endif
