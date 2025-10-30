<x-layouts.app :title="__('Dashboard')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $supervisor = $user->supervisor;
                $siwesSettings = \App\Models\SiwesSettings::getInstance();
                $totalActivities = $user->siwesActivityLogs()->count();
                $pendingActivities = $user->siwesActivityLogs()->where('approval_status', 'pending')->count();
                $approvedActivities = $user->siwesActivityLogs()->where('approval_status', 'approved')->count();
                $rejectedActivities = $user->siwesActivityLogs()->where('approval_status', 'rejected')->count();
            @endphp

            <!-- SIWES Status Alert -->
            @if(!$siwesSettings->is_active || !$siwesSettings->start_date)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                SIWES Period Not Started
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>The SIWES period has not been started by the administrator yet. You cannot log activities or access SIWES features until the period is activated. Please contact your supervisor or administrator for more information.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Welcome Section -->
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Welcome back, {{ $user->name }}!</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                                @if($user->matric_no)
                                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">{{ $user->matric_no }}</p>
                                @endif
                            </div>
                        </div>
                        @if($supervisor)
                            <div class="text-right">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Supervisor</p>
                                <p class="font-semibold">{{ $supervisor->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $supervisor->email }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SIWES Activity Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Activities</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $pendingActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Approved</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $approvedActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Rejected</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $rejectedActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activities -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold">Recent Activities</h4>
                                @if($totalActivities > 0)
                                    <a href="{{ route('siwes.activity-history') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        View All
                                    </a>
                                @endif
                            </div>
                            
                            @php
                                $recentActivities = $user->siwesActivityLogs()->latest()->take(5)->get();
                            @endphp
                            
                            @if($recentActivities->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentActivities as $activity)
                                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                            <div class="flex-1">
                                                <p class="font-medium">Week {{ $activity->week_number }}</p>
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ Str::limit($activity->activity_description, 60) }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $activity->activity_date->format('M d, Y') }}</p>
                                            </div>
                                            <div class="ml-4">
                                                @if($activity->approval_status === 'approved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Approved
                                                    </span>
                                                @elseif($activity->approval_status === 'rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        Pending
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No activities yet</h3>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by logging your first SIWES activity.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Info -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <h4 class="text-lg font-semibold mb-4">Quick Actions</h4>
                            <div class="space-y-3">
                                @if($siwesSettings->is_active && $siwesSettings->start_date)
                                    <a href="{{ route('siwes.log-activity') }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium">Log Activity</h5>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Record daily activities</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('siwes.activity-history') }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium">View History</h5>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Check activity history</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('siwes.weekly-summary') }}" class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium">Weekly Summary</h5>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">View progress summary</p>
                                        </div>
                                    </a>
                                @else
                                    <div class="flex items-center p-3 bg-zinc-100 dark:bg-zinc-700 rounded-lg opacity-50 cursor-not-allowed">
                                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-600 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-zinc-500">Log Activity</h5>
                                            <p class="text-sm text-zinc-400">SIWES not started</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-3 bg-zinc-100 dark:bg-zinc-700 rounded-lg opacity-50 cursor-not-allowed">
                                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-600 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-zinc-500">View History</h5>
                                            <p class="text-sm text-zinc-400">SIWES not started</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-3 bg-zinc-100 dark:bg-zinc-700 rounded-lg opacity-50 cursor-not-allowed">
                                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-600 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-zinc-500">Weekly Summary</h5>
                                            <p class="text-sm text-zinc-400">SIWES not started</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SIWES Information -->
                    @if($user->ppa_location)
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <h4 class="text-lg font-semibold mb-4">SIWES Information</h4>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">PPA Location</p>
                                    <p class="font-medium">{{ $user->ppa_location }}</p>
                                </div>
                                @if($user->department)
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Department</p>
                                    <p class="font-medium">{{ $user->department->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>