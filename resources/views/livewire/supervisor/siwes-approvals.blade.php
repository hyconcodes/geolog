<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\SiwesActivityLog;
use App\Models\User;

state([
    'supervisor' => null,
    'filter_student' => 'all',
    'filter_week' => 'all',
    'search' => '',
    'selected_log' => null,
    'rejection_reason' => '',
    'show_rejection_modal' => false,
]);

mount(function () {
    $this->supervisor = auth()->user();
    
    // Ensure user is a supervisor
    if (!$this->supervisor->hasRole('supervisor')) {
        abort(403, 'Access denied. Supervisor role required.');
    }
});

$pendingLogs = computed(function () {
    $query = SiwesActivityLog::with(['user'])
        ->whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'pending')
        ->orderBy('created_at', 'desc');
    
    // Filter by student
    if ($this->filter_student !== 'all') {
        $query->where('user_id', $this->filter_student);
    }
    
    // Filter by week
    if ($this->filter_week !== 'all') {
        $query->where('week_number', $this->filter_week);
    }
    
    // Search in activity description or backdate reason
    if ($this->search) {
        $query->where(function ($q) {
            $q->where('activity_description', 'like', '%' . $this->search . '%')
              ->orWhere('backdate_reason', 'like', '%' . $this->search . '%');
        });
    }
    
    return $query->paginate(10);
});

$students = computed(function () {
    return User::where('supervisor_id', $this->supervisor->id)
        ->role('student')
        ->orderBy('name')
        ->get();
});

$weeks = computed(function () {
    return SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'pending')
        ->select('week_number')
        ->distinct()
        ->orderBy('week_number')
        ->pluck('week_number');
});

$stats = computed(function () {
    $total = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->count();
        
    $pending = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'pending')
        ->count();
        
    $approved = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'approved')
        ->count();
        
    $rejected = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'rejected')
        ->count();
    
    return compact('total', 'pending', 'approved', 'rejected');
});

$approveLog = function ($logId) {
    // Check permission
    if (!auth()->user()->can('siwes.activity.approve')) {
        abort(403, 'You do not have permission to approve activities.');
    }
    
    $log = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('id', $logId)
        ->where('approval_status', 'pending')
        ->firstOrFail();
    
    $log->update([
        'approval_status' => 'approved',
        'approved_by' => $this->supervisor->id,
        'approved_at' => now(),
        'rejection_reason' => null,
    ]);
    
    session()->flash('success', 'Activity log approved successfully.');
};

$showRejectModal = function ($logId) {
    $this->selected_log = $logId;
    $this->rejection_reason = '';
    $this->show_rejection_modal = true;
};

$rejectLog = function () {
    // Check permission
    if (!auth()->user()->can('siwes.activity.approve')) {
        abort(403, 'You do not have permission to reject activities.');
    }
    
    $this->validate([
        'rejection_reason' => 'required|string|min:10|max:500',
    ]);
    
    $log = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('id', $this->selected_log)
        ->where('approval_status', 'pending')
        ->firstOrFail();
    
    $log->update([
        'approval_status' => 'rejected',
        'approved_by' => $this->supervisor->id,
        'approved_at' => now(),
        'rejection_reason' => $this->rejection_reason,
    ]);
    
    $this->show_rejection_modal = false;
    $this->selected_log = null;
    $this->rejection_reason = '';
    
    session()->flash('success', 'Activity log rejected with reason provided.');
};

$closeRejectModal = function () {
    $this->show_rejection_modal = false;
    $this->selected_log = null;
    $this->rejection_reason = '';
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">SIWES Approvals</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Review and approve backdated activity logs from your students
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('supervisor.dashboard') }}" 
                       class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Total Backdated</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Pending Review</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Approved</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['approved'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Rejected</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['rejected'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Search Activities
                    </label>
                    <input 
                        type="text" 
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                        placeholder="Search activities or reasons..."
                    >
                </div>

                <!-- Student Filter -->
                <div>
                    <label for="filter_student" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Filter by Student
                    </label>
                    <select 
                        id="filter_student"
                        wire:model.live="filter_student"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <option value="all">All Students</option>
                        @foreach($this->students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Week Filter -->
                <div>
                    <label for="filter_week" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Filter by Week
                    </label>
                    <select 
                        id="filter_week"
                        wire:model.live="filter_week"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <option value="all">All Weeks</option>
                        @foreach($this->weeks as $week)
                            <option value="{{ $week }}">Week {{ $week }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button 
                        wire:click="$set('search', ''); $set('filter_student', 'all'); $set('filter_week', 'all')"
                        class="w-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Logs -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
            @if($this->pendingLogs->count() > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->pendingLogs as $log)
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $log->user->name }}
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                            Week {{ $log->week_number }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                            Backdated Entry
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
                                        Activity Date: {{ $log->activity_date->format('l, F j, Y') }}
                                    </p>
                                </div>
                                
                                <div class="text-right text-sm text-zinc-500 dark:text-zinc-400">
                                    <div>Submitted: {{ $log->created_at->format('M j, Y') }}</div>
                                    <div>{{ $log->created_at->format('g:i A') }}</div>
                                </div>
                            </div>

                            <!-- Activity Description -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Activity Description:</h4>
                                <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed bg-zinc-50 dark:bg-zinc-700/50 p-3 rounded-lg">
                                    {{ $log->activity_description }}
                                </p>
                            </div>

                            <!-- Backdate Reason -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Reason for Backdating:</h4>
                                <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 rounded-lg">
                                    {{ $log->backdate_reason }}
                                </p>
                            </div>

                            <!-- Document -->
                            @if($log->document_path)
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Attached Document:</h4>
                                    <a href="{{ asset('storage/' . $log->document_path) }}" 
                                       target="_blank"
                                       class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        View Document
                                    </a>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex space-x-3">
                                @can('siwes.activity.approve')
                                    <button 
                                        wire:click="approveLog({{ $log->id }})"
                                        wire:confirm="Are you sure you want to approve this backdated activity log?"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Approve
                                    </button>
                                    
                                    <button 
                                        wire:click="showRejectModal({{ $log->id }})"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Reject
                                    </button>
                                @else
                                    <p class="text-zinc-500 dark:text-zinc-400 italic">You don't have permission to approve/reject activities.</p>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $this->pendingLogs->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-zinc-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No pending approvals</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">
                        @if($search || $filter_student !== 'all' || $filter_week !== 'all')
                            No backdated logs match your current filters.
                        @else
                            All backdated activity logs have been reviewed.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Rejection Modal -->
    @if($show_rejection_modal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        Reject Activity Log
                    </h3>
                    
                    <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                        Please provide a reason for rejecting this backdated activity log. This will help the student understand what needs to be corrected.
                    </p>
                    
                    <div class="mb-6">
                        <label for="rejection_reason" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Rejection Reason *
                        </label>
                        <textarea 
                            id="rejection_reason"
                            wire:model="rejection_reason"
                            rows="4"
                            class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                            placeholder="Explain why this activity log is being rejected..."
                        ></textarea>
                        @error('rejection_reason')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button 
                            wire:click="rejectLog"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                        >
                            Reject Log
                        </button>
                        <button 
                            wire:click="closeRejectModal"
                            class="flex-1 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>