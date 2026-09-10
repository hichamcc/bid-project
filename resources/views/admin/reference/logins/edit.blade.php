@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 p-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Login</h2>
            <a href="{{ route('admin.reference.logins.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('admin.reference.logins.update', $login) }}" class="space-y-5">
                @csrf @method('PUT')
                @include('admin.reference.logins._fields', ['login' => $login])
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.reference.logins.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
