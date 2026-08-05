@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Select Sheet to Import</h2>
                <a href="{{ route('scope-review.import.create') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    This workbook has multiple sheets. Choose which one to import.
                </p>

                <form method="GET" action="{{ route('scope-review.import.review') }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="space-y-2">
                        @foreach($sheetNames as $name)
                            <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="sheet" value="{{ $name }}" {{ $loop->first ? 'checked' : '' }} class="text-blue-600">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
