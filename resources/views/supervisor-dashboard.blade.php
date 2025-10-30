<x-layouts.app :title="__('Supervisor Dashboard')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
            {{ __('Supervisor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $students = $user->students;
                $totalStudents = $students->count();
                $totalActivities = \App\Models\SiwesActivityLog::whereIn('user_id', $students->pluck('id'))->count();
                $approvedActivities = \App\Models\SiwesActivityLog::whereIn('user_id', $students->pluck('id'))->where('approval_status', 'approved')->count();
                $pendingActivities = \App\Models\SiwesActivityLog::whereIn('user_id', $students->pluck('id'))->where('approval_status', 'pending')->count();
            @endphp

            <!-- Welcome Section -->
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Welcome, {{ $user->name }}!</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                                <p class="text-sm text-green-600 dark:text-green-400 font-medium">SIWES Supervisor</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Department</p>
                            <p class="font-semibold">{{ $user->department->name ?? 'Not Assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">My Students</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalStudents }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending Review</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $pendingActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Assigned Students -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold">My Students</h4>
                                @if($totalStudents > 0)
                                    <a href="{{ route('supervisor.students') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        View All
                                    </a>
                                @endif
                            </div>
                            
                            @if($students->count() > 0)
                                <div class="space-y-3">
                                    @foreach($students->take(5) as $student)
                                        @php
                                            $studentPendingActivities = $student->siwesActivityLogs()->where('approval_status', 'pending')->count();
                                            $studentTotalActivities = $student->siwesActivityLogs()->count();
                                            $latestActivity = $student->siwesActivityLogs()->latest()->first();
                                        @endphp
                                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="font-medium">{{ $student->name }}</h5>
                                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $student->email }}</p>
                                                    @if($student->matric_no)
                                                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $student->matric_no }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Activities:</span>
                                                    <span class="text-sm font-medium">{{ $studentTotalActivities }}</span>
                                                </div>
                                                @if($studentPendingActivities > 0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        {{ $studentPendingActivities }} Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Up to date
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No students assigned</h3>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Students will appear here once they are assigned to you.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Recent Activity -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <h4 class="text-lg font-semibold mb-4">Quick Actions</h4>
                            <div class="space-y-3">
                                <a href="{{ route('supervisor.students') }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-medium">Manage Students</h5>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">View all assigned students</p>
                                    </div>
                                </a>

                                <a href="{{ route('supervisor.siwes-approvals') }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-medium">Review Activities</h5>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Approve student submissions</p>
                                    </div>
                                </a>

                                <a href="{{ route('supervisor.student-activities') }}" class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-medium">View Student Activities</h5>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Browse approved activities with documents</p>
                                    </div>
                                </a>

                                @if($pendingActivities > 0)
                                <a href="{{ route('supervisor.siwes-approvals') }}" class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-medium">Pending Reviews</h5>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $pendingActivities }} activities awaiting review</p>
                                    </div>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-zinc-900 dark:text-zinc-100">
                            <h4 class="text-lg font-semibold mb-4">Recent Activity</h4>
                            @php
                                $recentActivities = \App\Models\SiwesActivityLog::whereIn('user_id', $students->pluck('id'))
                                    ->with('user')
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            @endphp
                            
                            @if($recentActivities->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentActivities as $activity)
                                        <div class="p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="font-medium text-sm">{{ $activity->user->name }}</p>
                                                @if($activity->approval_status === 'approved')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Approved
                                                    </span>
                                                @elseif($activity->approval_status === 'rejected')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        Pending
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Week {{ $activity->week_number }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $activity->activity_date->format('M d, Y') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No recent activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>