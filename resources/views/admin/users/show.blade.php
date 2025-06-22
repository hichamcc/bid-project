@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-900">User Details</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit User
                        </a>
                        <a href="{{ route('admin.users.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                            {{ $user->initials() }}
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Email Status
                                    </label>
                                    <div class="mt-1 flex items-center space-x-2">
                                        @if($user->email_verified_at)
                                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                                Verified
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                on {{ $user->email_verified_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                                Unverified
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </label>
                                    <div class="mt-1">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                            @if($user->isAdmin()) bg-red-100 text-red-800
                                            @elseif($user->isHeadEstimator()) bg-purple-100 text-purple-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- System Information -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        User ID
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">#{{ $user->id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Created At
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Last Updated
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Account Age
                                    </label>
                                    <p class="mt-1 text-lg text-gray-900">{{ $user->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Permissions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Permissions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            @if($user->isAdmin())
                                <div class="h-3 w-3 bg-green-400 rounded-full"></div>
                            @else
                                <div class="h-3 w-3 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        <span class="text-sm text-gray-700">Admin Access</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            @if($user->isHeadEstimator())
                                <div class="h-3 w-3 bg-green-400 rounded-full"></div>
                            @else
                                <div class="h-3 w-3 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        <span class="text-sm text-gray-700">Head Estimator</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            @if($user->isEstimator())
                                <div class="h-3 w-3 bg-green-400 rounded-full"></div>
                            @else
                                <div class="h-3 w-3 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        <span class="text-sm text-gray-700">Estimator</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit User
                    </a>
                    
                    @if(!($user->isAdmin() && App\Models\User::where('role', 'admin')->count() <= 1))
                        <form method="POST" 
                              action="{{ route('admin.users.destroy', $user) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete User
                            </button>
                        </form>
                    @else
                        <span class="text-gray-500 text-sm">Cannot delete the last admin user</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection