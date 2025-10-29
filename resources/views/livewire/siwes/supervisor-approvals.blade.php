<?php

use function Livewire\Volt\{state, mount, computed, rules};
use App\Models\SiwesActivityLog;
use App\Models\User;

state([
    'supervisor' => null,
    'filter_status' => 'pending',
    'filter_student' => '',
    'search' => '',
    'selected_log' => null,
    'approval_comment' => '',
    'rejection_reason' => '',
    'show_modal' => false,
    'modal_type' => '', // 'approve' or 'reject'
]);

rules([
    'approval_comment' => 'nullable|string|max:500',
    'rejection_reason' => 'required_if:modal_type,reject|string|max:500',
]);

mount(function () {
    $this->supervisor = auth()->user();
    
    if (!$this->supervisor->hasRole('supervisor')) {
        abort(403, 'Access denied. Supervisor role required.');
    }
});

$pendingLogs = computed(function () {
    $query = SiwesActivityLog::with(['user'])
        ->whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('is_backdated', true);
    
    if ($this->filter_status !== 'all') {
        $query->where('approval_status', $this->filter_status);
    }
    
    if ($this->filter_student) {
        $query->whereHas('user', function ($q) {
            $q->where('id', $this->filter_student);
        });
    }
    
    if ($this->search) {
        $query->where(function ($q) {
            $q->where('activity_description', 'like', '%' . $this->search . '%')
              ->orWhere('backdate_reason', 'like', '%' . $this->search . '%')
              ->orWhereHas('user', function ($userQuery) {
                  $userQuery->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('matric_number', 'like', '%' . $this->search . '%');
              });
        });
    }
    
    return $query->orderBy('created_at', 'desc')->paginate(10);
});

$supervisedStudents = computed(function () {
    return User::where('supervisor_id', $this->supervisor->id)
        ->whereNotNull('siwes_start_date')
        ->orderBy('name')
        ->get();
});

$stats = computed(function () {
    $baseQuery = SiwesActivityLog::whereHas('user', function ($q) {
        $q->where('supervisor_id', $this->supervisor->id);
    })->where('is_backdated', true);
    
    return [
        'total' => (clone $baseQuery)->count(),
        'pending' => (clone $baseQuery)->where('approval_status', 'pending')->count(),
        'approved' => (clone $baseQuery)->where('approval_status', 'approved')->count(),
        'rejected' => (clone $baseQuery)->where('approval_status', 'rejected')->count(),
    ];
});

$clearFilters = function () {
    $this->filter_status = 'pending';
    $this->filter_student = '';
    $this->search = '';
};

$openApprovalModal = function ($logId, $type) {
    $this->selected_log = SiwesActivityLog::with('user')->find($logId);
    $this->modal_type = $type;
    $this->show_modal = true;
    $this->approval_comment = '';
    $this->rejection_reason = '';
};

$closeModal = function () {
    $this->show_modal = false;
    $this->selected_log = null;
    $this->modal_type = '';
    $this->approval_comment = '';
    $this->rejection_reason = '';
    $this->resetErrorBag();
};

$processApproval = function () {
    $this->validate();
    
    if (!$this->selected_log) {
        return;
    }
    
    try {
        if ($this->modal_type === 'approve') {
            $this->selected_log->update([
                'approval_status' => 'approved',
                'approved_by' => $this->supervisor->id,
                'approved_at' => now(),
                'approval_comment' => $this->approval_comment,
            ]);
            
            session()->flash('success', 'Activity log approved successfully.');
        } else {
            $this->selected_log->update([
                'approval_status' => 'rejected',
                'approved_by' => $this->supervisor->id,
                'approved_at' => now(),
                'rejection_reason' => $this->rejection_reason,
            ]);
            
            session()->flash('success', 'Activity log rejected successfully.');
        }
        
        $this->closeModal();
        
    } catch (\Exception $e) {
        session()->flash('error', 'Failed to process approval. Please try again.');
    }
};

$downloadDocument = function ($logId) {
    $log = SiwesActivityLog::find($logId);
    
    if (!$log || !$log->document_path) {
        session()->flash('error', 'Document not found.');
        return;
    }
    
    return response()->download(storage_path('app/public/' . $log->document_path));
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
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Backdated</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pending</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Approved</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['approved'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Rejected</p>
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
                    <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Search</label>
                    <input 
                        type="text" 
                        id="search"
                        wire:model.live="search"
                        placeholder="Search activities, students..."
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="filter_status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Status</label>
                    <select 
                        id="filter_status"
                        wire:model.live="filter_status"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Student Filter -->
                <div>
                    <label for="filter_student" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Student</label>
                    <select 
                        id="filter_student"
                        wire:model.live="filter_student"
                        class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <option value="">All Students</option>
                        @foreach($this->supervisedStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->matric_number }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button 
                        wire:click="clearFilters"
                        class="w-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm">
            @if($this->pendingLogs->count() > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->pendingLogs as $log)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $log->user->name }}
                                            </span>
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                ({{ $log->user->matric_number }})
                                            </span>
                                        </div>
                                        
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">•</span>
                                        
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                            Week {{ $log->week_number }}
                                        </span>
                                        
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">•</span>
                                        
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $log->activity_date->format('M j, Y') }}
                                        </span>
                                        
                                        @if($log->approval_status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                Pending Approval
                                            </span>
                                        @elseif($log->approval_status === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                Approved
                                            </span>
                                        @elseif($log->approval_status === 'rejected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">Activity Description:</h4>
                                        <p class="text-zinc-700 dark:text-zinc-300">{{ $log->activity_description }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">Backdate Reason:</h4>
                                        <p class="text-zinc-700 dark:text-zinc-300">{{ $log->backdate_reason }}</p>
                                    </div>
                                    
                                    @if($log->approval_comment)
                                        <div class="mb-3">
                                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">Approval Comment:</h4>
                                            <p class="text-zinc-700 dark:text-zinc-300">{{ $log->approval_comment }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($log->rejection_reason)
                                        <div class="mb-3">
                                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">Rejection Reason:</h4>
                                            <p class="text-red-600 dark:text-red-400">{{ $log->rejection_reason }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center space-x-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        <span>Submitted: {{ $log->created_at->format('M j, Y g:i A') }}</span>
                                        @if($log->document_path)
                                            <button 
                                                wire:click="downloadDocument({{ $log->id }})"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download Document
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($log->approval_status === 'pending')
                                    <div class="flex space-x-2 ml-4">
                                        <button 
                                            wire:click="openApprovalModal({{ $log->id }}, 'approve')"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve
                                        </button>
                                        <button 
                                            wire:click="openApprovalModal({{ $log->id }}, 'reject')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject
                                        </button>
                                    </div>
                                @endif
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
                    <svg class="w-12 h-12 text-zinc-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No activity logs found</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">No backdated activity logs match your current filters.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Approval/Rejection Modal -->
    @if($show_modal && $selected_log)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $modal_type === 'approve' ? 'Approve' : 'Reject' }} Activity Log
                        </h3>
                        <button 
                            wire:click="closeModal"
                            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Log Details -->
                    <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Student:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $selected_log->user->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Date:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $selected_log->activity_date->format('M j, Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Activity:</span>
                            <p class="text-zinc-900 dark:text-zinc-100 mt-1">{{ $selected_log->activity_description }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Backdate Reason:</span>
                            <p class="text-zinc-900 dark:text-zinc-100 mt-1">{{ $selected_log->backdate_reason }}</p>
                        </div>
                    </div>
                    
                    <form wire:submit="processApproval">
                        @if($modal_type === 'approve')
                            <div class="mb-6">
                                <label for="approval_comment" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Approval Comment (Optional)
                                </label>
                                <textarea 
                                    id="approval_comment"
                                    wire:model="approval_comment"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                                    placeholder="Add any comments about this approval..."
                                ></textarea>
                                @error('approval_comment')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="mb-6">
                                <label for="rejection_reason" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Rejection Reason *
                                </label>
                                <textarea 
                                    id="rejection_reason"
                                    wire:model="rejection_reason"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                                    placeholder="Please provide a clear reason for rejecting this activity log..."
                                ></textarea>
                                @error('rejection_reason')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        
                        <div class="flex justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="closeModal"
                                class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-6 py-2 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="{{ $modal_type === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white px-6 py-2 rounded-lg transition-colors"
                            >
                                {{ $modal_type === 'approve' ? 'Approve' : 'Reject' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>