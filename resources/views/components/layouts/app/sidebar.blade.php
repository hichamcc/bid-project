<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            [x-cloak] { display: none !important; }

            /* Shrink sidebar width when collapsed */
            .sidebar-collapsed .\[grid-area\:sidebar\] {
                width: 4rem !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            /* Center icons when collapsed */
            .sidebar-collapsed .\[grid-area\:sidebar\] a {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            /* Center logo icon and hide logo text when collapsed */
            .sidebar-collapsed .\[grid-area\:sidebar\] a.flex.items-center {
                justify-content: center !important;
                margin-right: 0 !important;
            }
            .sidebar-collapsed .\[grid-area\:sidebar\] a .flex-1 {
                display: none !important;
            }
        </style>
    </head>
    <body class="layout sidebar min-h-screen bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
          x-data="{
              collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
              toggle() {
                  this.collapsed = !this.collapsed;
                  localStorage.setItem('sidebar-collapsed', this.collapsed);
              }
          }"
          :class="collapsed ? 'sidebar-collapsed' : ''">

        <x-sidebar sticky stashable
            class="border-r border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">

            <x-sidebar.toggle class="lg:hidden w-10 p-0">
                <x-phosphor-x aria-hidden="true" width="20" height="20" />
            </x-sidebar.toggle>

            <!-- Desktop collapse toggle -->
            <div class="hidden lg:flex" :class="collapsed ? 'justify-center' : 'justify-end'">
                <button @click="toggle()"
                        :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg x-show="!collapsed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM40,56H80V200H40ZM216,200H96V56H216Z"/>
                    </svg>
                    <svg x-show="collapsed" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM80,200H40V56H80ZM216,200H96V56H216Z"/>
                    </svg>
                </button>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->isHeadEstimator() || auth()->user()->isBidCoordinator())
            <a href="{{ route('admin.dashboard') }}" class="mr-5 flex items-center space-x-2">
                <x-app-logo />
            </a>

            <div x-show="!collapsed" x-cloak class="px-1 py-2">
                <div class="text-xs leading-none text-gray-500">{{ __('Platform') }}</div>
            </div>
            <div class="grid space-y-[2px]">
                <x-navlist.item before="phosphor-house-line" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')">
                    <span x-show="!collapsed" x-cloak>{{ __('Dashboard') }}</span>
                </x-navlist.item>
            </div>

            <div x-show="!collapsed" x-cloak class="px-1 py-2">
                <div class="text-xs leading-none text-gray-500">{{ __('Projects') }}</div>
            </div>
            <div class="grid space-y-[2px]">
                <x-navlist.item before="phosphor-list-checks" href="{{ route('admin.projects.index') }}" :current="request()->routeIs('admin.projects.index')">
                    <span x-show="!collapsed" x-cloak>{{ __('Projects') }}</span>
                </x-navlist.item>
                @if (auth()->user()->isAdmin() || auth()->user()->isBidCoordinator())
                <x-navlist.item before="phosphor-file-text" href="{{ route('admin.proposals.index') }}" :current="request()->routeIs('admin.proposals.*')">
                    <span x-show="!collapsed" x-cloak>{{ __('Proposals') }}</span>
                </x-navlist.item>
                @endif
                @if (auth()->user()->isAdmin() || auth()->user()->isBidCoordinator() || auth()->user()->isHeadEstimator())
                <x-navlist.item before="phosphor-chart-bar" href="{{ route('admin.progress.index') }}" :current="request()->routeIs('admin.progress.*')">
                    <span x-show="!collapsed" x-cloak>{{ __('Progress') }}</span>
                </x-navlist.item>
                @endif
            </div>
            @endif

            @if (auth()->user()->isAdmin())
                <div x-show="!collapsed" x-cloak class="px-1 py-2">
                    <div class="text-xs leading-none text-gray-500">{{ __('Admin') }}</div>
                </div>
                <div class="grid space-y-[2px]">
                    <x-navlist.item before="phosphor-user-list" href="{{ route('admin.users.index') }}" :current="request()->routeIs('admin.users.index')">
                        <span x-show="!collapsed" x-cloak>{{ __('Users') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-chart-pie" href="{{ route('admin.reports.index') }}" :current="request()->routeIs('admin.reports.*')">
                        <span x-show="!collapsed" x-cloak>{{ __('Reports') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-briefcase" href="{{ route('admin.workload.index') }}" :current="request()->routeIs('admin.workload.*')">
                        <span x-show="!collapsed" x-cloak>{{ __('Workload') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-intersect" href="{{ route('admin.allocation.index') }}" :current="request()->routeIs('admin.allocation.*')">
                        <span x-show="!collapsed" x-cloak>{{ __('Distribution') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-calendar-x" href="{{ route('admin.off-days.index') }}" :current="request()->routeIs('admin.off-days.*')">
                        <span x-show="!collapsed" x-cloak>{{ __('Off Days') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.statuses.index') }}" :current="request()->routeIs('admin.statuses.index')">
                        <span x-show="!collapsed" x-cloak>{{ __('Status') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.types.index') }}" :current="request()->routeIs('admin.types.index')">
                        <span x-show="!collapsed" x-cloak>{{ __('Type') }}</span>
                    </x-navlist.item>
                    <x-navlist.item before="phosphor-bookmark-simple" href="{{ route('admin.gcs.index') }}" :current="request()->routeIs('admin.gcs.index')">
                        <span x-show="!collapsed" x-cloak>{{ __('GC') }}</span>
                    </x-navlist.item>
                </div>
            @endif

            @if (auth()->user()->isEstimator())
            <a href="{{ route('estimator.dashboard') }}" class="mr-5 flex items-center space-x-2">
                <x-app-logo />
            </a>
            <div x-show="!collapsed" x-cloak class="px-1 py-2">
                <div class="text-xs leading-none text-gray-500">{{ __('Platform') }}</div>
            </div>
            <div class="grid space-y-[2px]">
                <x-navlist.item before="phosphor-house-line" :href="route('estimator.dashboard')" :current="request()->routeIs('estimator.dashboard')">
                    <span x-show="!collapsed" x-cloak>{{ __('Dashboard') }}</span>
                </x-navlist.item>
            </div>
            <div x-show="!collapsed" x-cloak class="px-1 py-2">
                <div class="text-xs leading-none text-gray-500">{{ __('Estimator') }}</div>
            </div>
            <div class="grid space-y-[2px]">
                <x-navlist.item before="phosphor-list-checks" href="{{ route('estimator.projects.index') }}" :current="request()->routeIs('estimator.projects.index')">
                    <span x-show="!collapsed" x-cloak>{{ __('Projects') }}</span>
                </x-navlist.item>
                <x-navlist.item before="phosphor-chart-bar" href="{{ route('estimator.progress.index') }}" :current="request()->routeIs('estimator.progress.*')">
                    <span x-show="!collapsed" x-cloak>{{ __('Progress') }}</span>
                </x-navlist.item>
                @php
                    $openJobsCount = \App\Models\Allocation::whereHas('estimators', fn($q) => $q->where('users.id', auth()->id())
                            ->where('allocation_user.status', 'open'))
                        ->count();
                @endphp
                <x-navlist.item before="phosphor-briefcase" href="{{ route('estimator.workload.index') }}" :current="request()->routeIs('estimator.workload.*')">
                    <span x-show="!collapsed" x-cloak class="flex items-center justify-between w-full">
                        {{ __('My Workload') }}
                        @if($openJobsCount > 0)
                            <span class="ml-2 px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">
                                {{ $openJobsCount }}
                            </span>
                        @endif
                    </span>
                </x-navlist.item>
            </div>
            @endif

            <x-spacer />

            <x-popover align="bottom" justify="left">
                <button type="button" class="w-full group flex items-center rounded-lg p-1 hover:bg-gray-800/5 dark:hover:bg-white/10">
                    <span class="shrink-0 size-8 bg-gray-200 rounded-sm overflow-hidden dark:bg-gray-700">
                        <span class="w-full h-full flex items-center justify-center text-sm">
                            {{ auth()->user()->initials() }}
                        </span>
                    </span>
                    <span x-show="!collapsed" x-cloak class="ml-2 text-sm text-gray-500 dark:text-white/80 group-hover:text-gray-800 dark:group-hover:text-white font-medium truncate">
                        {{ auth()->user()->name }}
                    </span>
                    <span x-show="!collapsed" x-cloak class="shrink-0 ml-auto size-8 flex justify-center items-center">
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
