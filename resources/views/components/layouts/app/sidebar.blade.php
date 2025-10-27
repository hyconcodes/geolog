<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-gradient-to-br from-green-50 via-yellow-50 to-amber-50 dark:from-green-900 dark:via-yellow-900 dark:to-amber-900">
    <flux:sidebar sticky stashable class="border-e border-green-200/40 dark:border-green-700/40 backdrop-blur-xl bg-gradient-to-b from-green-100/30 via-yellow-100/20 to-amber-100/30 dark:bg-gradient-to-b dark:from-green-900/30 dark:via-yellow-900/20 dark:to-amber-900/30 shadow-2xl shadow-green-500/10 dark:shadow-green-900/20">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <!-- Logo Section with Glassmorphism -->
        <div class="flex items-center justify-between p-6 backdrop-blur-sm bg-gradient-to-r from-green-200/40 via-yellow-200/30 to-amber-200/40 dark:from-green-800/40 dark:via-yellow-800/30 dark:to-amber-800/40 border-b border-green-200/30 dark:border-green-700/30">
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>
        </div>

        <flux:navlist variant="outline" class="space-y-2 p-4">
             <flux:navlist.group :heading="__('Platform')" class="grid space-y-2">
                 <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                     wire:navigate 
                     class="rounded-xl backdrop-blur-sm transition-all duration-300 hover:bg-green-100/30 dark:hover:bg-green-800/30 data-[current]:bg-gradient-to-r data-[current]:from-green-200/50 data-[current]:to-yellow-200/50 dark:data-[current]:from-green-800/50 dark:data-[current]:to-yellow-800/50 data-[current]:text-green-700 dark:data-[current]:text-green-300 data-[current]:shadow-lg data-[current]:shadow-green-500/20">
                     {{ __('Dashboard') }}
                 </flux:navlist.item>
             </flux:navlist.group>

            @can('role.view')
                 <flux:navlist.group :heading="__('Administration')" class="grid space-y-2">
                     <flux:navlist.item icon="user-group" :href="route('admin.roles')"
                         :current="request()->routeIs('admin.roles')" wire:navigate
                         class="rounded-xl backdrop-blur-sm transition-all duration-300 hover:bg-yellow-100/30 dark:hover:bg-yellow-800/30 data-[current]:bg-gradient-to-r data-[current]:from-yellow-200/50 data-[current]:to-amber-200/50 dark:data-[current]:from-yellow-800/50 dark:data-[current]:to-amber-800/50 data-[current]:text-yellow-700 dark:data-[current]:text-yellow-300 data-[current]:shadow-lg data-[current]:shadow-yellow-500/20">
                         {{ __('Role Management') }}
                     </flux:navlist.item>
                 </flux:navlist.group>
             @endcan
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
                 icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" 
                 class="backdrop-blur-sm bg-gradient-to-r from-green-100/40 to-yellow-100/40 dark:from-green-800/40 dark:to-yellow-800/40 hover:from-green-200/50 hover:to-yellow-200/50 dark:hover:from-green-700/50 dark:hover:to-yellow-700/50 border border-green-200/30 dark:border-green-700/30 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/10 dark:shadow-green-900/20" />

             <flux:menu class="w-[220px] backdrop-blur-xl bg-green-50/90 dark:bg-green-900/90 border border-green-200/40 dark:border-green-700/40 rounded-2xl shadow-2xl shadow-green-500/20 dark:shadow-green-900/30">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
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
                     <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate
                         class="hover:bg-green-100/30 dark:hover:bg-green-800/30 rounded-lg transition-all duration-200">
                         {{ __('Settings') }}
                     </flux:menu.item>
                 </flux:menu.radio.group>

                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" 
                     class="backdrop-blur-sm bg-green-100/20 dark:bg-green-800/20 rounded-xl border border-green-200/30 dark:border-green-700/30 p-1">
                     <flux:radio value="light" icon="sun" 
                         class="data-[checked]:bg-green-200/50 dark:data-[checked]:bg-green-700/50 data-[checked]:text-green-700 dark:data-[checked]:text-green-300">
                         {{ __('Light') }}
                     </flux:radio>
                     <flux:radio value="dark" icon="moon"
                         class="data-[checked]:bg-green-200/50 dark:data-[checked]:bg-green-700/50 data-[checked]:text-green-700 dark:data-[checked]:text-green-300">
                         {{ __('Dark') }}
                     </flux:radio>
                 </flux:radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                     @csrf
                     <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full hover:bg-red-100/30 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg transition-all duration-200"
                         data-test="logout-button">
                         {{ __('Log Out') }}
                     </flux:menu.item>
                 </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
     <flux:header class="lg:hidden backdrop-blur-xl bg-gradient-to-r from-green-100/80 via-yellow-100/60 to-amber-100/80 dark:from-green-900/80 dark:via-yellow-900/60 dark:to-amber-900/80 border-b border-green-200/40 dark:border-green-700/40 shadow-lg shadow-green-500/10 dark:shadow-green-900/20">
         <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

         <flux:spacer />

         <flux:dropdown position="top" align="end">
             <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" 
                 class="backdrop-blur-sm bg-gradient-to-r from-green-100/40 to-yellow-100/40 dark:from-green-800/40 dark:to-yellow-800/40 hover:from-green-200/50 hover:to-yellow-200/50 dark:hover:from-green-700/50 dark:hover:to-yellow-700/50 border border-green-200/30 dark:border-green-700/30 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/10 dark:shadow-green-900/20" />

             <flux:menu class="backdrop-blur-xl bg-green-50/90 dark:bg-green-900/90 border border-green-200/40 dark:border-green-700/40 rounded-2xl shadow-2xl shadow-green-500/20 dark:shadow-green-900/30">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
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
                     <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate
                         class="hover:bg-green-100/30 dark:hover:bg-green-800/30 rounded-lg transition-all duration-200">
                         {{ __('Settings') }}
                     </flux:menu.item>
                 </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                     @csrf
                     <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full hover:bg-red-100/30 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg transition-all duration-200"
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
