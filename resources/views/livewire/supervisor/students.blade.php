<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $statusFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function with(): array
    {
        $query = auth()->user()->students()
            ->whereHas('roles', function (Builder $q) {
                $q->where('name', 'student');
            })
            ->with(['department', 'siwesActivityLogs']);

        // Apply search filter
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('matric_number', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->whereHas('siwesActivityLogs', function (Builder $q) {
                $q->where('approval_status', $this->statusFilter);
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $students = $query->paginate(10);

        // Calculate statistics for each student
        $students->getCollection()->transform(function ($student) {
            $student->total_activities = $student->siwesActivityLogs->count();
            $student->pending_activities = $student->siwesActivityLogs->where('approval_status', 'pending')->count();
            $student->approved_activities = $student->siwesActivityLogs->where('approval_status', 'approved')->count();
            $student->rejected_activities = $student->siwesActivityLogs->where('approval_status', 'rejected')->count();
            $student->latest_activity = $student->siwesActivityLogs->sortByDesc('activity_date')->first();
            return $student;
        });

        return [
            'students' => $students,
            'totalStudents' => auth()->user()->students()->whereHas('roles', function (Builder $q) {
                $q->where('name', 'student');
            })->count(),
        ];
    }
}; ?>

<main :title="__('My Students')">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Students') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Total: {{ $totalStudents }} students
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search Students
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    id="search"
                                    wire:model.live.debounce.300ms="search"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Search by name, email, or matric number..."
                                >
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Filter by Activity Status
                            </label>
                            <select 
                                id="statusFilter"
                                wire:model.live="statusFilter"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="all">All Students</option>
                                <option value="pending">With Pending Activities</option>
                                <option value="approved">With Approved Activities</option>
                                <option value="rejected">With Rejected Activities</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($students->count() > 0)
                        <!-- Table Header -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left">
                                            <button 
                                                wire:click="sortBy('name')"
                                                class="flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100"
                                            >
                                                <span>Student</span>
                                                @if($sortBy === 'name')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($sortDirection === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Department
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Activities
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left">
                                            <button 
                                                wire:click="sortBy('created_at')"
                                                class="flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100"
                                            >
                                                <span>Last Activity</span>
                                                @if($sortBy === 'created_at')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($sortDirection === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($students as $student)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <!-- Student Info -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $student->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $student->email }}
                                                        </div>
                                                        @if($student->matric_number)
                                                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                                                {{ $student->matric_number }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Department -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $student->department->name ?? 'Not assigned' }}
                                                </div>
                                            </td>

                                            <!-- Activities -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $student->total_activities }} Total
                                                    </span>
                                                    @if($student->pending_activities > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                            {{ $student->pending_activities }} Pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Status -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    @if($student->approved_activities > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                            {{ $student->approved_activities }} Approved
                                                        </span>
                                                    @endif
                                                    @if($student->rejected_activities > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                            {{ $student->rejected_activities }} Rejected
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Last Activity -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($student->latest_activity)
                                                    <div>
                                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                                            {{ \Carbon\Carbon::parse($student->latest_activity->activity_date)->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            Week {{ $student->latest_activity->week_number }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">No activities</span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('supervisor.siwes-approvals') }}?student={{ $student->id }}" 
                                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                        View Activities
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $students->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No students found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($search)
                                    No students match your search criteria.
                                @else
                                    You don't have any students assigned yet.
                                @endif
                            </p>
                            @if($search)
                                <div class="mt-6">
                                    <button 
                                        wire:click="$set('search', '')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Clear search
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>