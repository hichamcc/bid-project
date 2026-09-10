@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Review Import — {{ $label }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ count($rows) }} row(s) found. Review, then import.</p>
            </div>
            <a href="{{ route("admin.reference.{$type}.index") }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
        </div>

        @if(count($rows) === 0)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">No usable rows found in that file.</div>
        @else
            <form method="POST" action="{{ route('admin.reference.import.commit', $type) }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                    @foreach($headers as $h)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($rows as $i => $row)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-400">{{ $i + 1 }}</td>
                                        @foreach($columns as $c)
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
                                                {{ $row[$c] ?? '' }}
                                                <input type="hidden" name="rows[{{ $i }}][{{ $c }}]" value="{{ $row[$c] ?? '' }}">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <a href="{{ route("admin.reference.{$type}.index") }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                        <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Import {{ count($rows) }} Row(s)</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
