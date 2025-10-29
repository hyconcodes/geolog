<?php

use function Livewire\Volt\{state, mount, rules};
use Illuminate\Support\Facades\DB;

state([
    'ppa_company_name' => '',
    'ppa_address' => '',
    'latitude' => null,
    'longitude' => null,
    'location_captured' => false,
    'loading' => false,
    'error_message' => '',
]);

rules([
    'ppa_company_name' => 'required|string|max:255',
    'ppa_address' => 'required|string|max:500',
    'latitude' => 'required|numeric|between:-90,90',
    'longitude' => 'required|numeric|between:-180,180',
]);

mount(function () {
    $user = auth()->user();
    
    // If user already has PPA location, redirect to dashboard
    if ($user->hasPPALocation()) {
        return redirect()->route('siwes.dashboard');
    }
});

$captureLocation = function () {
    $this->loading = true;
    $this->error_message = '';
    
    // This will be handled by JavaScript
    $this->dispatch('capture-location');
};

$setLocation = function ($latitude, $longitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->location_captured = true;
    $this->loading = false;
    $this->error_message = '';
};

$locationError = function ($error) {
    $this->loading = false;
    $this->error_message = $error;
};

$savePPALocation = function () {
    $this->validate();
    
    try {
        DB::transaction(function () {
            $user = auth()->user();
            
            $user->update([
                'ppa_company_name' => $this->ppa_company_name,
                'ppa_address' => $this->ppa_address,
                'ppa_latitude' => $this->latitude,
                'ppa_longitude' => $this->longitude,
                'siwes_start_date' => now()->toDateString(),
            ]);
        });
        
        session()->flash('success', 'PPA location saved successfully! Your SIWES journey begins now.');
        return redirect()->route('siwes.dashboard');
        
    } catch (\Exception $e) {
        $this->error_message = 'Failed to save PPA location. Please try again.';
    }
};

?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-zinc-900 dark:to-zinc-800 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Setup Your PPA Location</h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-2">
                We need to capture your Place of Primary Assignment (PPA) location to begin your SIWES journey.
            </p>
        </div>

        <!-- Setup Form -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-6">
            <form wire:submit="savePPALocation" class="space-y-6">
                <!-- Company Name -->
                <div>
                    <label for="ppa_company_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Company/Organization Name
                    </label>
                    <input 
                        type="text" 
                        id="ppa_company_name"
                        wire:model="ppa_company_name"
                        class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100 transition-colors"
                        placeholder="Enter your PPA company name"
                        required
                    >
                    @error('ppa_company_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="ppa_address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Company Address
                    </label>
                    <textarea 
                        id="ppa_address"
                        wire:model="ppa_address"
                        rows="3"
                        class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-zinc-100 transition-colors resize-none"
                        placeholder="Enter the full address of your PPA"
                        required
                    ></textarea>
                    @error('ppa_address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location Capture -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Current Location
                    </label>
                    
                    @if(!$location_captured)
                        <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-zinc-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                Please capture your current location at your PPA
                            </p>
                            <button 
                                type="button"
                                wire:click="captureLocation"
                                wire:loading.attr="disabled"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-2 rounded-lg transition-colors flex items-center mx-auto"
                            >
                                <span wire:loading.remove wire:target="captureLocation">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Capture Location
                                </span>
                                <span wire:loading wire:target="captureLocation" class="flex items-center">
                                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Capturing...
                                </span>
                            </button>
                        </div>
                    @else
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-green-800 dark:text-green-200 font-medium">Location captured successfully!</span>
                            </div>
                            <p class="text-green-700 dark:text-green-300 text-sm mt-1">
                                Coordinates: {{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}
                            </p>
                        </div>
                    @endif

                    @if($error_message)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mt-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-800 dark:text-red-200 text-sm">{{ $error_message }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    :disabled="!$wire.location_captured"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-zinc-400 disabled:to-zinc-500 text-white py-3 px-4 rounded-lg font-medium transition-all transform hover:scale-[1.02] disabled:hover:scale-100 disabled:cursor-not-allowed"
                >
                    Start My SIWES Journey
                </button>
            </form>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-blue-800 dark:text-blue-200 text-sm">
                    <p class="font-medium mb-1">Important Information:</p>
                    <ul class="space-y-1 text-blue-700 dark:text-blue-300">
                        <li>• Your location will be used to verify future activity logs</li>
                        <li>• You must be within 30 meters of this location to log activities</li>
                        <li>• This starts your 24-week SIWES period</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('capture-location', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $wire.setLocation(
                        position.coords.latitude,
                        position.coords.longitude
                    );
                },
                function(error) {
                    let errorMessage = 'Unable to retrieve your location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Please allow location access and try again.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'An unknown error occurred.';
                            break;
                    }
                    $wire.locationError(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            $wire.locationError('Geolocation is not supported by this browser.');
        }
    });
</script>
@endscript