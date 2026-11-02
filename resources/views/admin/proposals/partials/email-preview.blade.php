{{-- Body of the attachment preview popup (loaded by emailPreview.open()).
     Params: $proposal, $followUp, $type, $fileUrl, $fileName, $email (parsed .eml/.msg or null) --}}
@php
    $followUpLabels = [1 => 'Follow-Up 1', 2 => 'Follow-Up 2', 3 => 'Follow-Up 3', 4 => 'Follow-Up 4'];
    $readable = $email && !$email['parse_error'];
    $isImage = in_array($type, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true);
    $from = $readable ? trim(($email['from_name'] ?? '') . ($email['from_email'] ? ' <' . $email['from_email'] . '>' : '')) : null;
    $to = $readable ? ($email['to_email'] ?: $email['to_name']) : null;
    if ($readable && $email['to_name'] && $email['to_email'] && $email['to_name'] !== $email['to_email']) {
        $to = $email['to_name'] . ' · ' . $email['to_email'];
    }
@endphp

<div data-email-preview-title class="hidden">{{ $proposal->project->name ?? 'Proposal' }} · {{ $followUpLabels[$followUp] ?? '' }}</div>

@if($readable)
    <div class="px-5 py-3 border-b border-gray-200 text-sm space-y-1">
        <div class="text-base font-semibold text-gray-900">{{ $email['subject'] ?: '(no subject)' }}</div>
        <div><span class="text-gray-500 inline-block" style="width:3.5rem">From</span> {{ $from ?: '-' }}</div>
        <div><span class="text-gray-500 inline-block" style="width:3.5rem">To</span> {{ $to ?: '-' }}</div>
        @if($email['cc'])
            <div><span class="text-gray-500 inline-block" style="width:3.5rem">Cc</span> {{ $email['cc'] }}</div>
        @endif
        <div><span class="text-gray-500 inline-block" style="width:3.5rem">Sent</span> {{ $email['sent_at']?->format('M d, Y g:i A') ?? '-' }}</div>
        @if($email['attachments'])
            <div class="flex items-start">
                <span class="text-gray-500 inline-block flex-shrink-0" style="width:3.5rem">Files</span>
                <span class="text-gray-700">{{ implode(', ', $email['attachments']) }}</span>
            </div>
        @endif
    </div>

    @if($email['body_html'])
        {{-- Sandboxed: no scripts, can't touch this page; links open in a new tab --}}
        <iframe sandbox="allow-popups allow-popups-to-escape-sandbox" class="w-full block" style="height: 60vh; border: 0;"
                srcdoc="{{ '<!doctype html><html><head><meta charset="utf-8"><base target="_blank"><style>body{font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111827;margin:16px;word-wrap:break-word}img{max-width:100%;height:auto}</style></head><body>' . $email['body_html'] . '</body></html>' }}"></iframe>
    @else
        <pre class="px-5 py-4 text-sm text-gray-800 overflow-auto" style="white-space: pre-wrap; max-height: 60vh; font-family: inherit;">{{ $email['body_text'] ?: '(empty message)' }}</pre>
    @endif
@elseif($email)
    <div class="px-5 py-6 text-sm text-red-600">
        Couldn't read this email file ({{ $email['parse_error'] }}). Download it to open it in Outlook.
    </div>
@elseif($type === 'pdf')
    <iframe src="{{ $fileUrl }}" class="w-full block" style="height: 70vh; border: 0;"></iframe>
@elseif($isImage)
    <div class="p-4 text-center overflow-auto" style="max-height: 70vh;">
        <img src="{{ $fileUrl }}" alt="{{ $fileName }}" style="max-width: 100%; height: auto; display: inline-block;">
    </div>
@else
    <div class="px-5 py-6 text-sm text-gray-600">This file type can't be previewed. Download it to open it.</div>
@endif

<div class="px-5 py-3 border-t border-gray-200 flex items-center justify-between text-sm">
    <span class="text-gray-500 truncate">{{ $fileName }}</span>
    <a href="{{ $fileUrl }}" download class="inline-flex items-center px-3 py-1.5 text-blue-600 bg-blue-50 rounded hover:bg-blue-100 hover:text-blue-800">
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        Download
    </a>
</div>
