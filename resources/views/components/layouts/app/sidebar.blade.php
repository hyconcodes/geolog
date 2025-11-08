<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        @role('superadmin')
            @php $dashboardRoute = 'superadmin.dashboard'; @endphp
            @elserole('supervisor')
            @php $dashboardRoute = 'supervisor.dashboard'; @endphp
            @elserole('hod')
            @php $dashboardRoute = 'hod.dashboard'; @endphp
        @else
            @php $dashboardRoute = 'student.dashboard'; @endphp
        @endrole

        <a href="{{ route($dashboardRoute) }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route($dashboardRoute)"
                    :current="request()->routeIs(['student.dashboard', 'supervisor.dashboard', 'superadmin.dashboard', 'hod.dashboard'])"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>

            @role('student')
                <flux:navlist.group :heading="__('SIWES')" class="grid">
                    @if(auth()->user()->ppa_longitude || auth()->user()->ppa_latitude)
                        <flux:navlist.item icon="chart-bar" :href="route('siwes.dashboard')"
                            :current="request()->routeIs('siwes.dashboard')" wire:navigate>{{ __('SIWES Dashboard') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="plus-circle" :href="route('siwes.log-activity')"
                            :current="request()->routeIs('siwes.log-activity')" wire:navigate>{{ __('Log Activity') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="clock" :href="route('siwes.activity-history')"
                            :current="request()->routeIs('siwes.activity-history')" wire:navigate>{{ __('Activity History') }}
                        </flux:navlist.item>
                        @if(now()->dayOfWeek === 6)
                            <flux:navlist.item icon="document-text" :href="route('siwes.weekly-summary')"
                                :current="request()->routeIs('siwes.weekly-summary')" wire:navigate>{{ __('Weekly Summary') }}
                            </flux:navlist.item>
                        @endif
                        <flux:navlist.item icon="document-text" :href="route('siwes.final-report')"
                            :current="request()->routeIs('siwes.final-report')" wire:navigate>{{ __('Final Report') }}
                        </flux:navlist.item>
                    @else
                        <flux:navlist.item icon="map-pin" :href="route('siwes.ppa-setup')"
                            :current="request()->routeIs('siwes.ppa-setup')" wire:navigate>{{ __('PPA Setup') }}
                        </flux:navlist.item>
                    @endif
                </flux:navlist.group>
            @endrole

            @role('supervisor')
                <flux:navlist.group :heading="__('SIWES')" class="grid">
                    <flux:navlist.item icon="check-circle" :href="route('supervisor.siwes-approvals')"
                        :current="request()->routeIs('supervisor.siwes-approvals')" wire:navigate>{{ __('SIWES Approvals') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('supervisor.students')"
                        :current="request()->routeIs('supervisor.students')" wire:navigate>{{ __('My Students') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('supervisor.student-activities')"
                        :current="request()->routeIs('supervisor.student-activities')" wire:navigate>{{ __('Student Activities') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('supervisor.student-reports')"
                        :current="request()->routeIs('supervisor.student-reports')" wire:navigate>{{ __('Student Reports') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endrole

            @role('superadmin')
                <flux:navlist.group :heading="__('Administration')" class="grid">
                    <flux:navlist.item icon="user-group" :href="route('admin.roles')"
                        :current="request()->routeIs('admin.roles')" wire:navigate>{{ __('Manage Roles') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.accounts')"
                        :current="request()->routeIs('admin.accounts')" wire:navigate>{{ __('Account Management') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.users.index')"
                        :current="request()->routeIs('admin.users.index')" wire:navigate>{{ __('Manage Admin') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="academic-cap" :href="route('admin.supervisors')"
                        :current="request()->routeIs('admin.supervisors')" wire:navigate>{{ __('Supervisor Management') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="building-office" :href="route('admin.departments')"
                        :current="request()->routeIs('admin.departments')" wire:navigate>{{ __('Department') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('admin.student-reports')"
                        :current="request()->routeIs('admin.student-reports')" wire:navigate>{{ __('Student Reports') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endrole

            @role('hod')
                {{-- <flux:navlist.group :heading="__('Administration')" class="grid"> --}}
                    <flux:navlist.item icon="building-office" :href="route('hod.department.detail', Auth::user()->department_id)"
                        :current="request()->routeIs('hod.department.detail', Auth::user()->department_id)" wire:navigate>{{ __('Supervisor assignment') }}
                    </flux:navlist.item>
                {{-- </flux:navlist.group> --}}
            @endrole
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline" class="hidden">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                :avatar="auth()->user()->avatar_url" icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                    class="h-full w-full object-cover rounded-lg"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span
                                    class="hidden h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                </flux:radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" :avatar="auth()->user()->avatar_url"
                icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                    class="h-full w-full object-cover rounded-lg"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span
                                    class="hidden h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
