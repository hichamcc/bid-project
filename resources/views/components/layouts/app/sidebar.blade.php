<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="layout sidebar min-h-screen bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
        <x-sidebar sticky stashable class="border-r border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <x-sidebar.toggle class="lg:hidden w-10 p-0">
                <x-phosphor-x aria-hidden="true" width="20" height="20" />
            </x-sidebar.toggle>

            @if (auth()->user()->isAdmin() || auth()->user()->isHeadEstimator() || auth()->user()->isBidCoordinator())
            <a href="{{ route('admin.dashboard') }}" class="mr-5 flex items-center space-x-2">
                <x-app-logo />
            </a>
            <x-navlist>
                <x-navlist.group :heading="__('Platform')">
                    <x-navlist.item before="phosphor-house-line" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-navlist.item>
                </x-navlist.group>
            </x-navlist>
            <x-navlist.group :heading="__('Projects')">
                <x-navlist.item before="phosphor-list-checks" href="{{ route('admin.projects.index') }}" :current="request()->routeIs('admin.projects.index')">
                    {{ __('Projects') }}
                </x-navlist.item>
            </x-navlist.group>
            @endif
            @if (auth()->user()->isAdmin())
                <x-navlist.group :heading="__('Admin')">
                    <x-navlist.item before="phosphor-user-list" href="{{ route('admin.users.index') }}" :current="request()->routeIs('admin.users.index')">
                        {{ __('Users') }}
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.statuses.index') }}" :current="request()->routeIs('admin.statuses.index')">
                        {{ __('Status') }}
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.types.index') }}" :current="request()->routeIs('admin.types.index')">
                        {{ __('Type') }}
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.gcs.index') }}" :current="request()->routeIs('admin.gcs.index')">
                        {{ __('GC') }}
                    </x-navlist.item>
                </x-navlist.group>

            @endif
            @if (auth()->user()->isEstimator())
            <a href="{{ route('estimator.dashboard') }}" class="mr-5 flex items-center space-x-2">
                <x-app-logo />
            </a>
            <x-navlist.group :heading="__('Platform')">
                <x-navlist.item before="phosphor-house-line" :href="route('estimator.dashboard')" :current="request()->routeIs('estimator.dashboard')">
                    {{ __('Dashboard') }}
                </x-navlist.item>
            </x-navlist.group>
                <x-navlist.group :heading="__('Estimator')">
                    <x-navlist.item before="phosphor-list-checks" href="{{ route('estimator.projects.index') }}" :current="request()->routeIs('estimator.projects.index')">
                        {{ __('Projects') }}
                    </x-navlist.item>
                </x-navlist.group>
            @endif  

            <x-spacer />



            <x-popover align="bottom" justify="left">
                <button type="button" class="w-full group flex items-center rounded-lg p-1 hover:bg-gray-800/5 dark:hover:bg-white/10">
                    <span class="shrink-0 size-8 bg-gray-200 rounded-sm overflow-hidden dark:bg-gray-700">
                        <span class="w-full h-full flex items-center justify-center text-sm">
                            {{ auth()->user()->initials() }}
                        </span>
                    </span>
                    <span class="ml-2 text-sm text-gray-500 dark:text-white/80 group-hover:text-gray-800 dark:group-hover:text-white font-medium truncate">
                        {{ auth()->user()->name }}
                    </span>
                    <span class="shrink-0 ml-auto size-8 flex justify-center items-center">
                        <x-phosphor-caret-up-down aria-hidden="true" width="16" height="16" class="text-gray-400 dark:text-white/80 group-hover:text-gray-800 dark:group-hover:text-white" />
                    </span>
                </button>
                <x-slot:menu class="w-max">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-gray-200 text-black dark:bg-gray-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>

                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                    <x-popover.separator />
                    <x-popover.item before="phosphor-gear-fine" href="/settings/profile">{{ __('Settings') }}</x-popover.item>
                    <x-popover.separator />
                    <x-form method="post" action="{{ route('logout') }}" class="w-full flex">
                        <x-popover.item before="phosphor-sign-out">{{ __('Log Out') }}</x-popover.item>
                    </x-form>
                </x-slot:menu>
            </x-popover>
        </x-sidebar>

        <!-- Mobile User Menu -->
        <x-header class="lg:hidden">
            <x-container class="min-h-14 flex items-center">
                <x-sidebar.toggle class="lg:hidden w-10 p-0">
                    <x-phosphor-list aria-hidden="true" width="20" height="20" />
                </x-sidebar.toggle>

                <x-spacer />

                <x-popover align="top" justify="right">
                    <button type="button" class="w-full group flex items-center rounded-lg p-1 hover:bg-gray-800/5 dark:hover:bg-white/10">
                        <span class="shrink-0 size-8 bg-gray-200 rounded-sm overflow-hidden dark:bg-gray-700">
                            <span class="w-full h-full flex items-center justify-center text-sm">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>
                        <span class="shrink-0 ml-auto size-8 flex justify-center items-center">
                            <x-phosphor-caret-down width="16" height="16" class="text-gray-400 dark:text-white/80 group-hover:text-gray-800 dark:group-hover:text-white" />
                        </span>
                    </button>
                    <x-slot:menu>
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-gray-200 text-black dark:bg-gray-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                        <x-popover.separator />
                        <x-popover.item before="phosphor-gear-fine" href="/settings/profile">{{ __('Settings') }}</x-popover.item>
                        <x-popover.separator />
                        <x-form method="post" action="{{ route('logout') }}" class="w-full flex">
                            <x-popover.item before="phosphor-sign-out">{{ __('Log Out') }}</x-popover.item>
                        </x-form>
                    </x-slot:menu>
                </x-popover>
            </x-container>
        </x-header>

        {{ $slot }}

    </body>
</html>
