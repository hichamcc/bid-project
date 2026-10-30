@php
    $statusClasses = [
        'open'     => 'bg-blue-100 text-blue-800',
        'closed'   => 'bg-gray-200 text-gray-800',
        'inactive' => 'bg-red-100 text-red-800',
        'on_hold'  => 'bg-yellow-100 text-yellow-800',
    ];
@endphp
@if($status && isset($statusClasses[$status]))
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$status] }}">
        {{ \App\Models\Proposal::STATUSES[$status] }}
    </span>
@else
    <span class="text-gray-400">-</span>
@endif
