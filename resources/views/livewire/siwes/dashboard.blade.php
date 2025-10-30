<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\SiwesActivityLog;
use App\Models\SiwesSettings;
use Carbon\Carbon;

state([
    'user' => null,
    'current_week' => 0,
    'remaining_weeks' => 24,
    'total_logs' => 0,
    'this_week_logs' => 0,
    'recent_logs' => [],
]);

mount(function () {
    $this->user = auth()->user();
    
    // Check if superadmin has started SIWES
    $siwesSettings = SiwesSettings::getInstance();
    if (!$siwesSettings->is_active || !$siwesSettings->start_date) {
        session()->flash('error', 'SIWES period has not been started by the administrator. Please contact your supervisor or administrator.');
        return redirect()->route('dashboard');
    }
    
    if (!$this->user->hasPPALocation()) {
        return redirect()->route('siwes.ppa-setup');
    }
    
    $this->loadDashboardData();
});

$loadDashboardData = function () {
    $this->current_week = $this->user->getCurrentSiwesWeek() ?? 0;
    $this->remaining_weeks = $this->user->getRemainingWeeks();
    
    $this->total_logs = $this->user->approvedSiwesLogs()->count();
    
    // Get this week's logs using global SIWES settings
    $siwesSettings = SiwesSettings::getInstance();
    if ($siwesSettings->start_date && $this->current_week > 0) {
        $weekStart = Carbon::parse($siwesSettings->start_date)
            ->addWeeks($this->current_week - 1)
            ->startOfWeek();
        
        $this->this_week_logs = $this->user->approvedSiwesLogs()
            ->whereBetween('activity_date', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->count();
    } else {
        $this->this_week_logs = 0;
    }
    
    // Get recent logs
    $this->recent_logs = $this->user->approvedSiwesLogs()
        ->with('user')
        ->orderBy('activity_date', 'desc')
        ->limit(5)
        ->get();
};

$progress_percentage = computed(function () {
    if ($this->current_week <= 0) return 0;
    return min(100, ($this->current_week / 24) * 100);
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">SIWES Dashboard</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        {{ $user->ppa_company_name ?? 'Your Industrial Training Progress' }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">Week</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $current_week }}/24
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Overview -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">SIWES Progress</h2>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $remaining_weeks }} weeks remaining
                </span>
            </div>
            
            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-3 mb-4">
                <div 
                    class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500"
                    style="width: {{ $this->progress_percentage }}%"
                ></div>
            </div>
            
            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                <span>Started: {{ $user->siwes_start_date?->format('M d, Y') ?? 'Not started' }}</span>
                <span>{{ number_format($this->progress_percentage, 1) }}% Complete</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Logs -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Activity Logs</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $total_logs }}</p>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">This Week's Logs</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this_week_logs }}/6</p>
                    </div>
                </div>
            </div>

            <!-- Current Week -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Current Week</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Week {{ $current_week }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Log Daily Activity -->
            <a href="{{ route('siwes.log-activity') }}" 
               class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl p-6 transition-all transform hover:scale-[1.02] group">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Log Daily Activity</h3>
                        <p class="text-blue-100 text-sm">Record your daily work activities</p>
                    </div>
                    <svg class="w-5 h-5 ml-auto group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- Weekly Summary (Saturday only) -->
            @if(now()->dayOfWeek === 6)
                <a href="{{ route('siwes.weekly-summary') }}" 
                   class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl p-6 transition-all transform hover:scale-[1.02] group">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Weekly Summary</h3>
                            <p class="text-purple-100 text-sm">Submit your weekly summary (Saturday only)</p>
                        </div>
                        <svg class="w-5 h-5 ml-auto group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @else
                <div class="bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 opacity-60">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-zinc-200 dark:bg-zinc-700 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-500 dark:text-zinc-400">Weekly Summary</h3>
                            <p class="text-zinc-400 dark:text-zinc-500 text-sm">Available on Saturdays only</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- View Activity History -->
            <a href="{{ route('siwes.activity-history') }}" 
               class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 rounded-xl p-6 transition-all transform hover:scale-[1.02] group">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Activity History</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm">View all your logged activities</p>
                    </div>
                    <svg class="w-5 h-5 ml-auto group-hover:translate-x-1 transition-transform text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Recent Activity</h2>
            
            @if($recent_logs->count() > 0)
                <div class="space-y-4">
                    @foreach($recent_logs as $log)
                        <div class="flex items-start space-x-4 p-4 bg-zinc-50 dark:bg-zinc-700/50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        Week {{ $log->week_number }} - {{ $log->day_type === 'saturday' ? 'Weekly Summary' : 'Daily Activity' }}
                                    </p>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $log->activity_date->format('M d, Y') }}
                                    </span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-2">
                                    {{ Str::limit($log->activity_description, 100) }}
                                </p>
                                @if($log->document_path)
                                    <div class="flex items-center mt-2">
                                        <svg class="w-4 h-4 text-zinc-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Document attached</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-zinc-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-zinc-500 dark:text-zinc-400">No activity logs yet</p>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">Start logging your daily activities to track your progress</p>
                </div>
            @endif
        </div>
    </div>
</div>