{{-- Sortable column header link. Params: $field, $label --}}
@php
    $isSorted = request('sort_by') === $field;
    $isAsc = request('sort_direction', 'desc') === 'asc';
@endphp
<a href="{{ request()->fullUrlWithQuery(['sort_by' => $field, 'sort_direction' => $isSorted && $isAsc ? 'desc' : 'asc']) }}"
   class="group inline-flex items-center hover:text-gray-700">
    {{ $label }}
    @if($isSorted)
        @if($isAsc)
            <svg class="ml-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
            </svg>
        @else
            <svg class="ml-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
            </svg>
        @endif
    @endif
</a>
