<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\SiwesActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

state([
    'supervisor' => null,
    'filter_student' => 'all',
    'filter_week' => 'all',
    'search' => '',
    'selected_activity' => null,
    'show_activity_modal' => false,
    'show_image_zoom' => false,
    'zoom_image_url' => '',
]);

mount(function () {
    $this->supervisor = auth()->user();
    
    // Ensure user is a supervisor
    if (!$this->supervisor->hasRole('supervisor')) {
        abort(403, 'Access denied. Supervisor role required.');
    }
});

$approvedActivities = computed(function () {
    $query = SiwesActivityLog::with(['user'])
        ->whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'approved')
        ->orderBy('activity_date', 'desc');
    
    // Filter by student
    if ($this->filter_student !== 'all') {
        $query->where('user_id', $this->filter_student);
    }
    
    // Filter by week
    if ($this->filter_week !== 'all') {
        $query->where('week_number', $this->filter_week);
    }
    
    // Search in activity description
    if ($this->search) {
        $query->where('activity_description', 'like', '%' . $this->search . '%');
    }
    
    return $query->paginate(12);
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
        ->where('approval_status', 'approved')
        ->select('week_number')
        ->distinct()
        ->orderBy('week_number')
        ->pluck('week_number');
});

$stats = computed(function () {
    $total = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'approved')
        ->count();
        
    $withDocuments = SiwesActivityLog::whereHas('user', function ($q) {
            $q->where('supervisor_id', $this->supervisor->id);
        })
        ->where('approval_status', 'approved')
        ->whereNotNull('document_path')
        ->count();
    
    return compact('total', 'withDocuments');
});

$clearFilters = function () {
    $this->filter_student = 'all';
    $this->filter_week = 'all';
    $this->search = '';
};

$viewActivity = function ($activityId) {
    $this->selected_activity = SiwesActivityLog::with('user')->find($activityId);
    $this->show_activity_modal = true;
};

$closeModal = function () {
    $this->show_activity_modal = false;
    $this->selected_activity = null;
};

$showImageZoom = function ($imageUrl) {
    $this->zoom_image_url = $imageUrl;
    $this->show_image_zoom = true;
};

$closeImageZoom = function () {
    $this->show_image_zoom = false;
    $this->zoom_image_url = '';
};

$downloadDocument = function ($activityId) {
    $activity = SiwesActivityLog::find($activityId);
    
    if (!$activity || !$activity->document_path) {
        session()->flash('error', 'Document not found.');
        return;
    }
    
    $filePath = storage_path('app/public/' . $activity->document_path);
    
    if (!file_exists($filePath)) {
        session()->flash('error', 'Document file not found.');
        return;
    }
    
    return response()->download($filePath);
};

$isImage = function ($filePath) {
    if (!$filePath) return false;
    
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Student Activities</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        View approved activities and documents from your students
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

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Activities</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">With Documents</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['withDocuments'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Search</label>
                    <input 
                        type="text" 
                        id="search"
                        wire:model.live="search"
                        placeholder="Search activities..."
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                </div>

                <!-- Student Filter -->
                <div>
                    <label for="filter_student" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Student</label>
                    <select 
                        id="filter_student"
                        wire:model.live="filter_student"
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <option value="all">All Students</option>
                        @foreach($this->students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Week Filter -->
                <div>
                    <label for="filter_week" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Week</label>
                    <select 
                        id="filter_week"
                        wire:model.live="filter_week"
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
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
                        wire:click="clearFilters"
                        class="w-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow">
            @if($this->approvedActivities->count() > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->approvedActivities as $activity)
                        <div class="p-6 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="flex items-start space-x-4">
                                <!-- Student Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <span class="text-lg font-medium text-blue-600 dark:text-blue-400">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Main Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-3">
                                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $activity->user->name }}
                                            </h3>
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $activity->user->matric_no ?? 'N/A' }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Week {{ $activity->week_number }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $activity->activity_date->format('M j, Y') }}
                                            </span>
                                            <div class="flex space-x-2">
                                                <button 
                                                    wire:click="viewActivity({{ $activity->id }})"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors flex items-center"
                                                >
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Details
                                                </button>
                                                @if($activity->document_path)
                                                    <button 
                                                        wire:click="downloadDocument({{ $activity->id }})"
                                                        class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg text-sm transition-colors flex items-center"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Download
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Activity Description -->
                                    <div class="mb-4">
                                        <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                            {{ Str::limit($activity->activity_description, 200) }}
                                        </p>
                                    </div>

                                    <!-- Document Preview -->
                                    @if($activity->document_path)
                                        <div class="mt-4">
                                            @if($this->isImage($activity->document_path))
                                                <div class="flex items-start space-x-4">
                                                    <div class="flex-shrink-0">
                                                        <img 
                                                             src="{{ asset('storage/' . $activity->document_path) }}" 
                                                             alt="Activity document"
                                                             class="w-32 h-24 object-cover rounded-lg cursor-pointer border border-zinc-200 dark:border-zinc-600 hover:opacity-80 transition-opacity"
                                                             wire:click="showImageZoom('{{ asset('storage/' . $activity->document_path) }}')"
                                                         >
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Image Document</p>
                                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ pathinfo($activity->document_path, PATHINFO_BASENAME) }}
                                                        </p>
                                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                                            Click to view full size
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg border border-zinc-200 dark:border-zinc-600">
                                                    <svg class="w-8 h-8 text-zinc-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                            {{ pathinfo($activity->document_path, PATHINFO_BASENAME) }}
                                                        </p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ strtoupper(pathinfo($activity->document_path, PATHINFO_EXTENSION)) }} Document
                                                        </p>
                                                    </div>
                                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                        Click download to view
                                                    </span>
                                                </div>
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
                    {{ $this->approvedActivities->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-zinc-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No activities found</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">No approved activities match your current filters.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Activity Detail Modal -->
    @if($show_activity_modal && $selected_activity)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Activity Details
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
                    
                    <!-- Student Info -->
                    <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Student:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $selected_activity->user->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Matric Number:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $selected_activity->user->matric_no }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Date:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $selected_activity->activity_date->format('M j, Y') }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Week:</span>
                                <p class="text-zinc-900 dark:text-zinc-100">Week {{ $selected_activity->week_number }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Description -->
                    <div class="mb-6">
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Activity Description:</span>
                        <div class="mt-2 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                            <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-wrap">{{ $selected_activity->activity_description }}</p>
                        </div>
                    </div>
                    
                    <!-- Document Display -->
                    @if($selected_activity->document_path)
                        <div class="mb-6">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Attached Document:</span>
                            <div class="mt-2">
                                @if($this->isImage($selected_activity->document_path))
                                    <div class="text-center">
                                        <img 
                                            src="{{ asset('storage/' . $selected_activity->document_path) }}" 
                                            alt="Activity document"
                                            class="max-w-full h-auto rounded-lg shadow-lg cursor-pointer hover:opacity-80 transition-opacity"
                                            wire:click="showImageZoom('{{ asset('storage/' . $selected_activity->document_path) }}')"
                                        >
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Click to zoom</p>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-zinc-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-zinc-900 dark:text-zinc-100 font-medium">
                                                    {{ pathinfo($selected_activity->document_path, PATHINFO_BASENAME) }}
                                                </p>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ strtoupper(pathinfo($selected_activity->document_path, PATHINFO_EXTENSION)) }} Document
                                                </p>
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="downloadDocument({{ $selected_activity->id }})"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Close Button -->
                    <div class="flex justify-end">
                        <button 
                            wire:click="closeModal"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 px-6 py-2 rounded-lg transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Image Zoom Modal -->
     @if($show_image_zoom)
         <div class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center p-4 z-[60]" wire:click="closeImageZoom">
             <div class="relative max-w-[95vw] max-h-[95vh] overflow-hidden" onclick="event.stopPropagation()">
                 <button 
                     wire:click="closeImageZoom"
                     class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                 >
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                     </svg>
                 </button>
                 
                 <!-- Zoom Controls -->
                 <div class="absolute top-4 left-4 z-10 flex flex-col space-y-2">
                     <button 
                         onclick="zoomIn()"
                         class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                         title="Zoom In"
                     >
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                         </svg>
                     </button>
                     <button 
                         onclick="zoomOut()"
                         class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                         title="Zoom Out"
                     >
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path>
                         </svg>
                     </button>
                     <button 
                         onclick="resetZoom()"
                         class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                         title="Reset Zoom"
                     >
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                         </svg>
                     </button>
                 </div>
                 
                 <div id="zoom-container" class="w-full h-full overflow-auto cursor-grab active:cursor-grabbing">
                     <img 
                         id="zoom-image"
                         src="{{ $zoom_image_url }}" 
                         alt="Zoomed activity document"
                         class="max-w-full max-h-full object-contain rounded-lg shadow-2xl transition-transform duration-200"
                         style="max-width: 95vw; max-height: 95vh; transform-origin: center center;"
                         draggable="false"
                     >
                 </div>
                 
                 <!-- Instructions -->
                 <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm">
                     Use mouse wheel to zoom • Click and drag to pan
                 </div>
             </div>
         </div>
         
         <script>
             let currentZoom = 1;
             let isDragging = false;
             let startX, startY, scrollLeft, scrollTop;
             
             function zoomIn() {
                 currentZoom = Math.min(currentZoom * 1.2, 5);
                 updateZoom();
             }
             
             function zoomOut() {
                 currentZoom = Math.max(currentZoom / 1.2, 0.5);
                 updateZoom();
             }
             
             function resetZoom() {
                 currentZoom = 1;
                 updateZoom();
             }
             
             function updateZoom() {
                 const image = document.getElementById('zoom-image');
                 if (image) {
                     image.style.transform = `scale(${currentZoom})`;
                 }
             }
             
             // Mouse wheel zoom
             document.getElementById('zoom-container')?.addEventListener('wheel', function(e) {
                 e.preventDefault();
                 if (e.deltaY < 0) {
                     zoomIn();
                 } else {
                     zoomOut();
                 }
             });
             
             // Pan functionality
             const container = document.getElementById('zoom-container');
             if (container) {
                 container.addEventListener('mousedown', function(e) {
                     isDragging = true;
                     startX = e.pageX - container.offsetLeft;
                     startY = e.pageY - container.offsetTop;
                     scrollLeft = container.scrollLeft;
                     scrollTop = container.scrollTop;
                 });
                 
                 container.addEventListener('mouseleave', function() {
                     isDragging = false;
                 });
                 
                 container.addEventListener('mouseup', function() {
                     isDragging = false;
                 });
                 
                 container.addEventListener('mousemove', function(e) {
                     if (!isDragging) return;
                     e.preventDefault();
                     const x = e.pageX - container.offsetLeft;
                     const y = e.pageY - container.offsetTop;
                     const walkX = (x - startX) * 2;
                     const walkY = (y - startY) * 2;
                     container.scrollLeft = scrollLeft - walkX;
                     container.scrollTop = scrollTop - walkY;
                 });
             }
         </script>
     @endif
</div>