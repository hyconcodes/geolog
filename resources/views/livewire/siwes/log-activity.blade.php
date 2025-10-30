<?php

use function Livewire\Volt\{state, mount, rules, computed, usesFileUploads};
use App\Models\SiwesActivityLog;
use App\Models\SiwesSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

usesFileUploads();

state([
    'user' => null,
    'activity_description' => '',
    'document' => null,
    'selected_date' => null,
    'selected_week' => 1,
    'is_backdated' => false,
    'backdate_reason' => '',
    'current_latitude' => null,
    'current_longitude' => null,
    'location_verified' => false,
    'location_error' => '',
    'loading' => false,
    'current_week' => 0,
    'day_type' => 'weekday',
]);

rules([
    'activity_description' => 'required|string|min:10|max:2000',
    'document' => 'nullable|file|max:10240', // 10MB max
    'selected_date' => 'required|date|before_or_equal:today',
    'selected_week' => 'required|integer|min:1|max:24',
    'backdate_reason' => 'required_if:is_backdated,true|string|max:500',
    'current_latitude' => 'required|numeric',
    'current_longitude' => 'required|numeric',
]);

mount(function () {
    $this->user = auth()->user();
    
    // Check if superadmin has started SIWES
    $siwesSettings = SiwesSettings::getInstance();
    if (!$siwesSettings->is_active || !$siwesSettings->start_date) {
        session()->flash('error', 'SIWES period has not been started by the administrator. Please contact your supervisor or administrator.');
        return redirect()->route('siwes.dashboard');
    }
    
    if (!$this->user->hasPPALocation()) {
        return redirect()->route('siwes.ppa-setup');
    }
    
    if (!$this->user->isSiwesActive()) {
        session()->flash('error', 'Your SIWES period has not started or has ended.');
        return redirect()->route('siwes.dashboard');
    }
    
    $this->selected_date = now()->toDateString();
    $this->current_week = $this->user->getCurrentSiwesWeek() ?? 1;
    $this->selected_week = $this->current_week; // Default to current week
    $this->checkIfBackdated();
    $this->calculateWeekAndDayType();
});

$checkIfBackdated = function () {
    $selectedDate = Carbon::parse($this->selected_date);
    $this->is_backdated = $selectedDate->lt(now()->startOfDay());
};

$calculateWeekAndDayType = function () {
    $selectedDate = Carbon::parse($this->selected_date);
    $this->current_week = $this->user->getCurrentSiwesWeek();
    
    // Determine day type
    if ($selectedDate->isSaturday()) {
        $this->day_type = 'saturday';
    } else if ($selectedDate->isWeekday() && !$selectedDate->isSunday()) {
        $this->day_type = 'weekday';
    } else {
        $this->day_type = 'invalid';
    }
};

$updatedSelectedDate = function () {
    $this->checkIfBackdated();
    $this->calculateWeekAndDayType();
    $this->location_verified = false;
};

$captureLocation = function () {
    $this->loading = true;
    $this->location_error = '';
    $this->dispatch('capture-current-location');
};

$setCurrentLocation = function ($latitude, $longitude) {
    $this->current_latitude = $latitude;
    $this->current_longitude = $longitude;
    
    // Verify location is within 30 meters of PPA
    $distance = $this->calculateDistance(
        $this->user->ppa_latitude,
        $this->user->ppa_longitude,
        $latitude,
        $longitude
    );
    
    // Always show coordinates for transparency
    $coordinateInfo = "Your current location: {$latitude}, {$longitude}";
    
    if ($distance <= 30) {
        $this->location_verified = true;
        $this->location_error = "{$coordinateInfo} - Location verified! You are {$distance}m from your PPA.";
    } else {
        $this->location_verified = false;
        $this->location_error = "{$coordinateInfo} - You are {$distance}m away from your PPA. You must be within 30 meters to log activities.";
    }
    
    $this->loading = false;
};

$locationError = function ($error) {
    $this->loading = false;
    $this->location_error = $error;
    $this->location_verified = false;
};

$calculateDistance = function ($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Earth's radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return round($earthRadius * $c);
};

$canLogForDate = computed(function () {
    $selectedDate = Carbon::parse($this->selected_date);
    
    // Check if it's a valid day (Monday-Saturday)
    if ($this->day_type === 'invalid') {
        return false;
    }
    
    // Check if SIWES is active globally
    $siwesSettings = SiwesSettings::getInstance();
    if (!$siwesSettings->isSiwesActive()) {
        return false;
    }
    
    // Check if it's within SIWES period
    if (!$siwesSettings->start_date) {
        return false;
    }
    
    $siwesStart = Carbon::parse($siwesSettings->start_date);
    $siwesEnd = $siwesStart->copy()->addWeeks(24);
    
    return $selectedDate->between($siwesStart, $siwesEnd) && $selectedDate->lte(now());
});

$existingLog = computed(function () {
    return SiwesActivityLog::where('user_id', $this->user->id)
        ->where('activity_date', $this->selected_date)
        ->first();
});

$availableWeeks = computed(function () {
    $siwesSettings = SiwesSettings::getInstance();
    return $siwesSettings->getAvailableWeeks();
});

$saveActivity = function () {
    // Check if superadmin has started SIWES before allowing activity submission
    $siwesSettings = SiwesSettings::getInstance();
    if (!$siwesSettings->is_active || !$siwesSettings->start_date) {
        $this->addError('activity_description', 'SIWES period has not been started by the administrator. Cannot log activities at this time.');
        return;
    }
    
    $this->validate();
    
    if (!$this->canLogForDate) {
        $this->addError('selected_date', 'Cannot log activity for this date.');
        return;
    }
    
    if (!$this->location_verified) {
        $this->addError('current_latitude', 'Please verify your location first.');
        return;
    }
    
    // Check if log already exists
    if ($this->existingLog) {
        $this->addError('selected_date', 'Activity already logged for this date.');
        return;
    }
    
    try {
        $documentPath = null;
        
        if ($this->document) {
            $documentPath = $this->document->store('siwes-documents', 'public');
        }
        
        // Always set approval status to pending as per requirement
        $approvalStatus = 'pending';
        
        SiwesActivityLog::create([
            'user_id' => $this->user->id,
            'activity_date' => $this->selected_date,
            'week_number' => $this->selected_week, // Use selected week instead of calculated
            'day_type' => $this->day_type,
            'activity_description' => $this->activity_description,
            'document_path' => $documentPath,
            'latitude' => $this->current_latitude,
            'longitude' => $this->current_longitude,
            'is_backdated' => $this->is_backdated,
            'backdate_reason' => $this->is_backdated ? $this->backdate_reason : null,
            'approval_status' => $approvalStatus,
        ]);
        
        $message = 'Activity logged successfully! It will be reviewed by your supervisor.';
            
        session()->flash('success', $message);
        return redirect()->route('siwes.dashboard');
        
    } catch (\Exception $e) {
        $this->addError('activity_description', 'Failed to save activity. Please try again.');
    }
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Log Activity</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Record your daily work activities at {{ $user->ppa_company_name }}
                    </p>
                </div>
                <a href="{{ route('siwes.dashboard') }}" 
                   class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                    <form wire:submit="saveActivity" class="space-y-6">
                        <!-- Date Selection -->
                        <div>
                            <label for="selected_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Activity Date
                            </label>
                            <input 
                                type="date" 
                                id="selected_date"
                                wire:model.live="selected_date"
                                max="{{ now()->toDateString() }}"
                                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                            >
                            @error('selected_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            
                            @if($day_type === 'invalid')
                                <p class="text-red-500 text-sm mt-1">Activities can only be logged Monday-Saturday</p>
                            @elseif($day_type === 'saturday')
                                <p class="text-blue-600 dark:text-blue-400 text-sm mt-1">Saturday: Weekly summary activity</p>
                            @elseif($is_backdated)
                                <p class="text-amber-600 dark:text-amber-400 text-sm mt-1">This is a backdated entry and will require supervisor approval</p>
                            @endif
                        </div>

                        <!-- Week Number Selection -->
                        <div>
                            <label for="selected_week" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Week Number
                            </label>
                            <select 
                                id="selected_week"
                                wire:model.live="selected_week"
                                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100"
                            >
                                @foreach($this->availableWeeks as $week)
                                    <option value="{{ $week }}" {{ $week == $current_week ? 'selected' : '' }}>
                                        Week {{ $week }} {{ $week == $current_week ? '(Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selected_week')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">
                                Select the week number for this activity (1-24)
                            </p>
                        </div>

                        <!-- Backdate Reason (if backdated) -->
                        @if($is_backdated)
                            <div>
                                <label for="backdate_reason" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Reason for Backdated Entry *
                                </label>
                                <textarea 
                                    id="backdate_reason"
                                    wire:model="backdate_reason"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100 resize-none"
                                    placeholder="Explain why you're logging this activity after the fact..."
                                    required
                                ></textarea>
                                @error('backdate_reason')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Activity Description -->
                        <div>
                            <label for="activity_description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                @if($day_type === 'saturday')
                                    Weekly Summary
                                @else
                                    Activity Description
                                @endif
                                *
                            </label>
                            <textarea 
                                id="activity_description"
                                wire:model="activity_description"
                                rows="6"
                                class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100 resize-none"
                                placeholder="{{ $day_type === 'saturday' ? 'Summarize your activities and learnings for this week...' : 'Describe the activities you performed today in detail...' }}"
                                required
                            ></textarea>
                            <div class="flex justify-between text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                <span>Minimum 10 characters</span>
                                <span>{{ strlen($activity_description) }}/2000</span>
                            </div>
                            @error('activity_description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Document Upload -->
                        <div>
                            <label for="document" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Supporting Document (Optional)
                            </label>
                            <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6">
                                <input 
                                    type="file" 
                                    id="document"
                                    wire:model="document"
                                    class="hidden"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                                >
                                <label for="document" class="cursor-pointer block text-center">
                                    <svg class="w-12 h-12 text-zinc-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    @if($document)
                                        <p class="text-green-600 dark:text-green-400 font-medium">{{ $document->getClientOriginalName() }}</p>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($document->getSize() / 1024, 1) }} KB</p>
                                    @else
                                        <p class="text-zinc-600 dark:text-zinc-400">Click to upload a document</p>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">PDF, DOC, DOCX, JPG, PNG, TXT (Max: 10MB)</p>
                                    @endif
                                </label>
                            </div>
                            @error('document')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location Verification -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Location Verification
                            </label>
                            
                            @if(!$location_verified)
                                <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                Verify you're at your PPA location
                                            </p>
                                            @if($location_error)
                                                <p class="text-sm mt-1 {{ $location_verified ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                                                    {{ $location_error }}
                                                </p>
                                            @endif
                                        </div>
                                        <button 
                                            type="button"
                                            wire:click="captureLocation"
                                            wire:loading.attr="disabled"
                                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                                        >
                                            <span wire:loading.remove wire:target="captureLocation">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                </svg>
                                                Verify Location
                                            </span>
                                            <span wire:loading wire:target="captureLocation" class="flex items-center">
                                                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Verifying...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-green-800 dark:text-green-200 font-medium">Location verified - You're at your PPA!</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            :disabled="!$wire.location_verified || !$wire.canLogForDate"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-zinc-400 disabled:to-zinc-500 text-white py-3 px-4 rounded-lg font-medium transition-all transform hover:scale-[1.02] disabled:hover:scale-100 disabled:cursor-not-allowed"
                        >
                            @if($is_backdated)
                                Submit for Supervisor Approval
                            @else
                                Log Activity
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Week Info -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Current Week</h3>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $current_week }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">of 24 weeks</div>
                    </div>
                </div>

                <!-- Existing Log Warning -->
                @if($this->existingLog)
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="font-medium text-amber-800 dark:text-amber-200">Activity Already Logged</h4>
                                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    You have already logged an activity for this date.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                    <h3 class="font-medium text-blue-800 dark:text-blue-200 mb-3">Activity Guidelines</h3>
                    <ul class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Log activities Monday-Friday for daily work
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Saturday entries are weekly summaries
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Must be within 30m of PPA location
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Backdated entries need supervisor approval
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('capture-current-location', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $wire.setCurrentLocation(
                        position.coords.latitude,
                        position.coords.longitude
                    );
                },
                function(error) {
                    let errorMessage = 'Unable to get your location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Please allow location access.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'Unknown error occurred.';
                            break;
                    }
                    $wire.locationError(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            $wire.locationError('Geolocation not supported by this browser.');
        }
    });
</script>
@endscript