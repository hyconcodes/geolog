<x-layouts.app title="hod Dashboard">
    @php
        $user = auth()->user();
        $departmentId = $user->department_id ?? null;
        $department = $departmentId ? \App\Models\Department::find($departmentId) : null;

        $studentsCount = \App\Models\User::role('student')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->count();
        $supervisorsCount = \App\Models\User::role('supervisor')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->count();
        $totalUsersInDept = \App\Models\User::when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->count();
    @endphp

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">HOD Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Welcome, {{ $user->name }}
                    @if($department)
                        — Department of {{ $department->name }}
                    @endif
                </p>
            </div>
            <div>
                @if($departmentId)
                    <a href="{{ route('hod.department.detail', $departmentId) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Manage Assignments
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500">Students</p>
                        <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $studentsCount }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">Dept</span>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500">Supervisors</p>
                        <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $supervisorsCount }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-violet-100 px-2 py-1 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-200">Dept</span>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500">Total Accounts</p>
                        <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalUsersInDept }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">Dept</span>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Department Overview</h2>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="text-sm text-zinc-500">Students in Department</p>
                        <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white">{{ $studentsCount }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="text-sm text-zinc-500">Supervisors in Department</p>
                        <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white">{{ $supervisorsCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Quick Notes</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    This dashboard provides a quick overview of your department.
                    You can navigate using the sidebar for other features.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>