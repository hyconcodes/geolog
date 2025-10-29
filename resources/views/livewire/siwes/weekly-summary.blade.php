<?php

use function Livewire\Volt\{state, mount, computed, rules};
use App\Models\SiwesActivityLog;
use Carbon\Carbon;

state([
    'user' => null,
    'current_week' => 1,
    'weekly_summary' => '',
    'document' => null,
    'is_loading' => false,
    'location_captured' => false,
    'latitude' => null,
    'longitude' => null,
    'location_error' => '',
]);

rules([
    'weekly_summary' => 'required|string|min:50|max:2000',
    'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
]);

mount(function () {
    $this->user = auth()->user();
    
    if (!$this->user->hasPPALocation()) {
        return redirect()->route('siwes.ppa-setup');
    }
    
    if (!$this->user->isSiwesActive()) {
        session()->flash('error', 'SIWES period is not active or has ended.');
        return redirect()->route('siwes.dashboard');
    }
    
    $this->current_week = $this->user->getCurrentSiwesWeek();
    
    // Check if it's Saturday
    if (now()->dayOfWeek !== Carbon::SATURDAY) {
        session()->flash('error', 'Weekly summaries can only be submitted on Saturdays.');
        return redirect()->route('siwes.dashboard');
    }
    
    // Check if weekly summary already exists for current week
    $existingSummary = $this->user->siwesActivityLogs()
        ->where('week_number', $this->current_week)
        ->where('day_type', 'saturday')
        ->first();
    
    if ($existingSummary) {
        session()->flash('info', 'You have already submitted your weekly summary for Week ' . $this->current_week);
        return redirect()->route('siwes.dashboard');
    }
});

$weekActivities = computed(function () {
    return $this->user->siwesActivityLogs()
        ->where('week_number', $this->current_week)
        ->where('day_type', '!=', 'saturday')
        ->orderBy('activity_date')
        ->get();
});

$captureLocation = function () {
    $this->dispatch('capture-location');
};

$setLocation = function ($latitude, $longitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->location_captured = true;
    $this->location_error = '';
};

$locationError = function ($error) {
    $this->location_error = $error;
    $this->location_captured = false;
};

$submitWeeklySummary = function () {
    $this->validate();
    
    if (!$this->location_captured) {
        $this->addError('location', 'Please capture your current location first.');
        return;
    }
    
    // Verify location is within 30 meters of PPA
    if (!SiwesActivityLog::isWithinPPARadius(
        $this->latitude, 
        $this->longitude, 
        $this->user->ppa_latitude, 
        $this->user->ppa_longitude
    )) {
        $this->addError('location', 'You must be within 30 meters of your PPA location to submit weekly summary.');
        return;
    }
    
    $this->is_loading = true;
    
    try {
        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('siwes-documents', 'public');
        }
        
        SiwesActivityLog::create([
            'user_id' => $this->user->id,
            'activity_date' => now()->toDateString(),
            'week_number' => $this->current_week,
            'day_type' => 'saturday',
            'activity_description' => $this->weekly_summary,
            'document_path' => $documentPath,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_backdated' => false,
            'approval_status' => 'approved', // Weekly summaries are auto-approved
        ]);
        
        session()->flash('success', 'Weekly summary submitted successfully!');
        return redirect()->route('siwes.dashboard');
        
    } catch (\Exception $e) {
        $this->addError('submit', 'Failed to submit weekly summary. Please try again.');
    } finally {
        $this->is_loading = false;
    }
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Weekly Summary</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Submit your weekly summary for Week {{ $current_week }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('siwes.dashboard') }}" 
                       class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                    <form wire:submit="submitWeeklySummary" class="space-y-6">
                        <!-- Weekly Summary -->
                        <div>
                            <label for="weekly_summary" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Weekly Summary *
                            </label>
                            <textarea 
                                id="weekly_summary"
                                wire:model="weekly_summary"
                                rows="8"
                                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                                placeholder="Provide a comprehensive summary of your activities this week. Include key learnings, challenges faced, skills developed, and overall progress..."
                            ></textarea>
                            @error('weekly_summary')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                Minimum 50 characters, maximum 2000 characters
                            </p>
                        </div>

                        <!-- Document Upload -->
                        <div>
                            <label for="document" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Supporting Document (Optional)
                            </label>
                            <input 
                                type="file" 
                                id="document"
                                wire:model="document"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                            >
                            @error('document')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                Supported formats: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)
                            </p>
                        </div>

                        <!-- Location Verification -->
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-3">Location Verification</h3>
                            
                            @if(!$location_captured)
                                <div class="text-center py-6">
                                    <svg class="w-12 h-12 text-zinc-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                        Please verify your location at your PPA before submitting
                                    </p>
                                    <button 
                                        type="button"
                                        wire:click="captureLocation"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                                    >
                                        Capture Location
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center text-green-600 dark:text-green-400">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Location verified successfully</span>
                                </div>
                            @endif

                            @if($location_error)
                                <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <p class="text-red-600 dark:text-red-400 text-sm">{{ $location_error }}</p>
                                </div>
                            @endif

                            @error('location')
                                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        @error('submit')
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</p>
                            </div>
                        @enderror

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button 
                                type="submit"
                                wire:loading.attr="disabled"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-8 py-3 rounded-lg transition-colors flex items-center"
                            >
                                <span wire:loading.remove>Submit Weekly Summary</span>
                                <span wire:loading class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Week Info -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Week {{ $current_week }} Overview</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Activities Logged:</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->weekActivities->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Week Period:</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- This Week's Activities -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">This Week's Activities</h3>
                    
                    @if($this->weekActivities->count() > 0)
                        <div class="space-y-3">
                            @foreach($this->weekActivities as $activity)
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $activity->activity_date->format('l, M j') }}
                                        </span>
                                        @if($activity->approval_status === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                Approved
                                            </span>
                                        @elseif($activity->approval_status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                        {{ Str::limit($activity->activity_description, 100) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-8 h-8 text-zinc-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm">No activities logged this week</p>
                        </div>
                    @endif
                </div>

                <!-- Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">Weekly Summary Guidelines</h3>
                    <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Summarize key activities and learnings from the week
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Highlight challenges faced and how you overcame them
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Mention skills developed or improved
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Weekly summaries are automatically approved
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Can only be submitted on Saturdays
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('capture-location', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    Livewire.dispatch('setLocation', {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    });
                },
                function(error) {
                    let errorMessage = 'Unable to retrieve location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Location access denied by user.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location information unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'An unknown error occurred.';
                            break;
                    }
                    Livewire.dispatch('locationError', { error: errorMessage });
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            Livewire.dispatch('locationError', { 
                error: 'Geolocation is not supported by this browser.' 
            });
        }
    });
});
</script>