<?php

use function Livewire\Volt\{state, mount, computed, with};
use App\Models\SiwesActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

state([
    'user' => null,
    'filters' => [
        'week' => '',
        'status' => '',
        'search' => '',
    ],
]);

mount(function () {
    $this->user = auth()->user();
    
    if (!$this->user->hasPPALocation()) {
        return redirect()->route('student.ppa-setup');
    }
});

$activities = computed(function () {
    $query = $this->user->siwesActivityLogs()
        ->with(['approvedBy', 'rejectedBy'])
        ->orderBy('activity_date', 'desc');
    
    if ($this->filters['week']) {
        $query->where('week_number', $this->filters['week']);
    }
    
    if ($this->filters['status']) {
            $query->where('approval_status', $this->filters['status']);
        }
    
    if ($this->filters['search']) {
        $query->where(function ($q) {
            $q->where('activity_description', 'like', '%' . $this->filters['search'] . '%')
              ->orWhere('backdate_reason', 'like', '%' . $this->filters['search'] . '%');
        });
    }
    
    return $query->paginate(10);
});

$weeks = computed(function () {
    return $this->user->siwesActivityLogs()
        ->select('week_number')
        ->distinct()
        ->orderBy('week_number')
        ->pluck('week_number');
});

$stats = computed(function () {
    $logs = $this->user->siwesActivityLogs();
    
    return [
        'total' => $logs->count(),
        'approved' => $logs->where('approval_status', 'approved')->count(),
        'pending' => $logs->where('approval_status', 'pending')->count(),
        'rejected' => $logs->where('approval_status', 'rejected')->count(),
    ];
});

$clearFilters = function () {
    $this->filters = [
        'week' => '',
        'status' => '',
        'search' => '',
    ];
};

$downloadDocument = function ($logId) {
    $log = SiwesActivityLog::findOrFail($logId);
    
    if ($log->user_id !== auth()->id()) {
        abort(403);
    }
    
    if (!$log->document_path) {
        session()->flash('error', 'No document available for download.');
        return;
    }
    
    return response()->download(storage_path('app/' . $log->document_path));
};

?>

<main>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Activity History</h1>
                <p class="text-zinc-600 dark:text-zinc-400">View and manage your SIWES activity logs</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Logs</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Approved</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['approved'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $this->stats['pending'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Rejected</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->stats['rejected'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <flux:input 
                            wire:model.live="filters.search" 
                            placeholder="Search activities..." 
                            icon="magnifying-glass"
                        />
                    </div>
                    
                    <div>
                        <flux:select wire:model.live="filters.week" placeholder="Filter by week">
                            <option value="">All Weeks</option>
                            @foreach($this->weeks as $week)
                                <option value="{{ $week }}">Week {{ $week }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    
                    <div>
                        <flux:select wire:model.live="filters.status" placeholder="Filter by status">
                            <option value="">All Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </flux:select>
                    </div>
                    
                    <div>
                        <flux:button wire:click="clearFilters" variant="outline" class="w-full">
                            Clear Filters
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Activity List -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow">
                @if($this->activities->count() > 0)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($this->activities as $activity)
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Week {{ $activity->week_number }}
                                            </span>
                                            
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                @if($activity->day_type === 'weekday') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @endif">
                                                {{ ucfirst($activity->day_type) }}
                                            </span>
                                            
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($activity->approval_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($activity->approval_status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                {{ ucfirst($activity->approval_status) }}
                            </span>
                                            
                                            @if($activity->is_backdated)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                    Backdated
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="mb-2">
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $activity->activity_date->format('M d, Y') }}
                                            </p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="text-zinc-900 dark:text-zinc-100">{{ $activity->activity_description }}</p>
                                        </div>
                                        
                                        @if($activity->backdate_reason)
                                            <div class="mb-2">
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    <strong>Backdate Reason:</strong> {{ $activity->backdate_reason }}
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @if($activity->rejection_reason)
                                            <div class="mb-2">
                                                <p class="text-sm text-red-600 dark:text-red-400">
                                                    <strong>Rejection Reason:</strong> {{ $activity->rejection_reason }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex flex-col space-y-2 ml-4">
                                        @if($activity->document_path)
                                            <flux:button 
                                                wire:click="downloadDocument({{ $activity->id }})" 
                                                size="sm" 
                                                variant="outline"
                                                icon="document-arrow-down"
                                            >
                                                Download
                                            </flux:button>
                                        @endif
                                        
                                        @if($activity->approval_status === 'approved' && ($activity->approvedBy || $activity->approved_at))
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                Approved {{ $activity->approved_at ? $activity->approved_at->diffForHumans() : '' }}
                                                @if($activity->approvedBy)
                                                    <br>by {{ $activity->approvedBy->name }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $this->activities->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">No activities found</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            @if(array_filter($this->filters))
                                Try adjusting your filters or 
                                <button wire:click="clearFilters" class="text-blue-600 hover:text-blue-500">clear all filters</button>
                            @else
                                Get started by logging your first activity.
                            @endif
                        </p>
                        @if(!array_filter($this->filters))
                            <div class="mt-6">
                                <flux:button :href="route('siwes.log-activity')" wire:navigate>
                                    Log First Activity
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>