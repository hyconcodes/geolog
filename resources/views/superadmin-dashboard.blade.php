<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Welcome Section -->
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-zinc-900 dark:text-zinc-100">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl sm:text-2xl font-bold truncate">Welcome back, {{ auth()->user()->name }}!</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base truncate">{{ auth()->user()->email }}</p>
                            <p class="text-xs sm:text-sm text-purple-600 dark:text-purple-400 font-medium">Super Administrator</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Users</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ \App\Models\User::count() }}</p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Students</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ \App\Models\User::role('student')->count() }}</p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Supervisors</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ \App\Models\User::role('supervisor')->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Departments</p>
                                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ \App\Models\Department::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supervisor Capacity Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                @php
                    $supervisors = \App\Models\User::role('supervisor')->withCount('students')->get();
                    $availableSupervisors = $supervisors->where('students_count', '<', 6)->count();
                    $almostFullSupervisors = $supervisors->whereBetween('students_count', [6, 7])->count();
                    $fullSupervisors = $supervisors->where('students_count', '>=', 8)->count();
                    $totalAssignedStudents = $supervisors->sum('students_count');
                @endphp

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Available</p>
                                <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $availableSupervisors }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">< 6 students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Almost Full</p>
                                <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $almostFullSupervisors }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">6-7 students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Full</p>
                                <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $fullSupervisors }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">≥ 8 students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Assigned Students</p>
                                <p class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ $totalAssignedStudents }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Total supervised</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Overview -->
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Department Overview</h3>
                    <div class="space-y-3">
                        @php
                            $departments = \App\Models\Department::withCount(['students', 'supervisors'])->get();
                        @endphp
                        
                        @foreach($departments as $department)
                            <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                {{ $department->code }}
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $department->name }}</h4>
                                            <div class="flex items-center space-x-4 mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                <span>{{ $department->students_count }} Students</span>
                                                <span>{{ $department->supervisors_count }} Supervisors</span>
                                                @php
                                                    $ratio = $department->supervisors_count > 0 ? round($department->students_count / $department->supervisors_count, 1) : 0;
                                                @endphp
                                                <span class="font-medium {{ $ratio > 8 ? 'text-red-600 dark:text-red-400' : ($ratio > 6 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                                    {{ $ratio }}:1 Ratio
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('admin.department.detail', $department->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-md transition-colors">
                                            View Details
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Supervisor Management</h3>
                        <div class="space-y-3">
                            <a href="{{ route('admin.supervisors') }}" 
                               class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">Manage Supervisors</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">View capacity and assignments</p>
                                </div>
                            </a>
                            
                            <a href="{{ route('admin.departments') }}" 
                               class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">Department Details</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage student assignments</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Assignment Overview</h3>
                        @php
                            $unassignedStudents = \App\Models\User::role('student')->whereNull('supervisor_id')->count();
                            $totalStudents = \App\Models\User::role('student')->count();
                            $assignmentPercentage = $totalStudents > 0 ? round((($totalStudents - $unassignedStudents) / $totalStudents) * 100, 1) : 0;
                        @endphp
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Assignment Progress</span>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $assignmentPercentage }}%</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $assignmentPercentage }}%"></div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalStudents - $unassignedStudents }}</p>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">Assigned</p>
                                </div>
                                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $unassignedStudents }}</p>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">Unassigned</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Recent Activity</h3>
                        <div class="space-y-3 sm:space-y-4">
                            @php
                                // Get recent supervisor assignments (students with supervisors assigned in the last 7 days)
                                $recentAssignments = \App\Models\User::role('student')
                                    ->whereNotNull('supervisor_id')
                                    ->where('updated_at', '>=', now()->subDays(7))
                                    ->with(['supervisor', 'department'])
                                    ->orderBy('updated_at', 'desc')
                                    ->limit(5)
                                    ->get();
                                
                                // Get supervisors who recently reached capacity limits
                                $fullSupervisors = \App\Models\User::role('supervisor')
                                    ->withCount('students')
                                    ->having('students_count', '>=', 8)
                                    ->limit(3)
                                    ->get();
                            @endphp

                            @forelse($recentAssignments as $assignment)
                                <div class="flex items-start space-x-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                            <span class="font-medium">{{ $assignment->name }}</span> 
                                            assigned to supervisor 
                                            <span class="font-medium text-blue-600 dark:text-blue-400">{{ $assignment->supervisor->name }}</span>
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $assignment->department->name }} • {{ $assignment->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="flex items-start space-x-3 p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                    <div class="w-2 h-2 bg-zinc-400 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">No recent supervisor assignments</p>
                                    </div>
                                </div>
                            @endforelse

                            @foreach($fullSupervisors as $supervisor)
                                <div class="flex items-start space-x-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                            <span class="font-medium text-red-600 dark:text-red-400">{{ $supervisor->name }}</span> 
                                            has reached maximum capacity
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $supervisor->students_count }}/8 students assigned
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                            @if($recentAssignments->isEmpty() && $fullSupervisors->isEmpty())
                                <div class="flex items-center space-x-3 text-sm">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">System running smoothly</span>
                                </div>
                                <div class="flex items-center space-x-3 text-sm">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">All supervisors within capacity limits</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('admin.supervisors') }}" 
                               class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-4 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Supervisor Management</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">View capacity and manage assignments</p>
                                </div>
                                <svg class="w-5 h-5 text-zinc-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            
                            <a href="{{ route('admin.departments') }}" 
                               class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-4 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Department Details</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage student assignments by department</p>
                                </div>
                                <svg class="w-5 h-5 text-zinc-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>