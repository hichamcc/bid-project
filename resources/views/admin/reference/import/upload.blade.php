@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 p-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Import {{ $label }}</h2>
            <a href="{{ route("admin.reference.{$type}.index") }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Upload an Excel (.xlsx/.xls) or CSV file. Expected columns (in order, headers optional):
                <span class="font-medium">{{ implode(' · ', $headers) }}</span>.
            </p>
            <form method="POST" action="{{ route('admin.reference.import.upload', $type) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                <div class="flex justify-end">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Upload &amp; Preview</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
