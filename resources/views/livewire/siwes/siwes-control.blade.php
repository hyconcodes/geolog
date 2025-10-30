<?php

use function Livewire\Volt\{state, mount};
use App\Models\SiwesSettings;
use App\Models\SiwesActivityLog;

state([
    'siwesSettings' => null,
    'showConfirmModal' => false,
    'confirmAction' => '',
]);

mount(function () {
    $this->siwesSettings = SiwesSettings::getInstance();
});

$toggleSiwes = function () {
    if (!$this->siwesSettings->is_active) {
        // Starting SIWES
        $this->confirmAction = 'start';
        $this->showConfirmModal = true;
    } else {
        // Stopping SIWES (only if allowed)
        if ($this->siwesSettings->canToggleOff()) {
            $this->confirmAction = 'stop';
            $this->showConfirmModal = true;
        } else {
            session()->flash('error', 'SIWES cannot be turned off after 24 weeks have completed.');
        }
    }
};

$confirmToggle = function () {
    if ($this->confirmAction === 'start') {
        $this->siwesSettings->startSiwes();
        session()->flash('message', 'SIWES has been started successfully! The 24-week period begins now.');
    } elseif ($this->confirmAction === 'stop') {
        $this->siwesSettings->update(['is_active' => false]);
        session()->flash('message', 'SIWES has been stopped.');
    }

    $this->siwesSettings->refresh();
    $this->showConfirmModal = false;
    $this->confirmAction = '';
};

$cancelToggle = function () {
    $this->showConfirmModal = false;
    $this->confirmAction = '';
};

$clearActivityLogs = function () {
    SiwesActivityLog::truncate();
    session()->flash('message', 'All SIWES activity logs have been cleared.');
};

?>

<div>
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- SIWES Control Panel -->
    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">SIWES Control Panel</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage the global SIWES period for all students</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $siwesSettings->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
                        {{ $siwesSettings->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            @if($siwesSettings->is_active)
                <!-- Active SIWES Info -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Start Date</p>
                            <p class="text-lg font-semibold text-blue-700 dark:text-blue-300">
                                {{ $siwesSettings->start_date ? \Carbon\Carbon::parse($siwesSettings->start_date)->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Current Week</p>
                            <p class="text-lg font-semibold text-blue-700 dark:text-blue-300">
                                Week {{ $siwesSettings->getCurrentWeek() ?? 0 }} of 24
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Progress</p>
                            <div class="mt-1">
                                @php
                                    $currentWeek = $siwesSettings->getCurrentWeek() ?? 0;
                                    $progress = $currentWeek > 0 ? min(($currentWeek / 24) * 100, 100) : 0;
                                @endphp
                                <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                    <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                </div>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">{{ number_format($progress, 1) }}% Complete</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button wire:click="toggleSiwes" 
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white {{ $siwesSettings->is_active ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($siwesSettings->is_active)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @endif
                    </svg>
                    @if($siwesSettings->is_active)
                        Stop SIWES
                    @else
                        Start SIWES
                    @endif
                </button>

                <button wire:click="clearActivityLogs" 
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-sm font-medium rounded-md text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clear Activity Logs
                </button>
            </div>

        </div>
    </div>

    <!-- Confirmation Modal -->
    <flux:modal
        name="siwes-confirm-modal"
        class="max-w-md"
        wire:model="showConfirmModal"
    >
        <div class="space-y-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $confirmAction === 'start' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                    @if($confirmAction === 'start')
                        <flux:icon.play class="h-6 w-6 text-green-600 dark:text-green-400" />
                    @else
                        <flux:icon.stop class="h-6 w-6 text-red-600 dark:text-red-400" />
                    @endif
                </div>
                <div class="flex-1">
                    <flux:heading size="lg">
                        {{ $confirmAction === 'start' ? 'Start SIWES' : 'Stop SIWES' }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        @if($confirmAction === 'start')
                            Are you sure you want to start the SIWES period? This will allow students to begin logging their activities.
                        @else
                            Are you sure you want to stop the SIWES period? This will prevent students from logging new activities.
                        @endif
                    </flux:text>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <flux:button 
                    wire:click="cancelToggle" 
                    variant="outline"
                    class="flex-1"
                >
                    Cancel
                </flux:button>
                <flux:button 
                    wire:click="confirmToggle" 
                    variant="{{ $confirmAction === 'start' ? 'primary' : 'danger' }}"
                    class="flex-1"
                >
                    {{ $confirmAction === 'start' ? 'Start SIWES' : 'Stop SIWES' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>