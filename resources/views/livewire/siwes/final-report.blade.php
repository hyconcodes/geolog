<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\SiwesActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    public $activities = [];
    public $selectedActivities = [];
    public $reportFile;
    public $isGenerating = false;
    public $isSubmitting = false;
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->activities = SiwesActivityLog::where('user_id', auth()->id())
            ->orderBy('activity_date')
            ->get()
            ->map(function ($activity) {
                $activity->formatted_date = $activity->activity_date->format('F j, Y');
                $activity->is_selected = true;
                return $activity;
            });
            
        $this->selectedActivities = $this->activities->pluck('id')->toArray();
    }

    public function generateReport()
    {
        $this->isGenerating = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            $user = Auth::user();
            $activities = $this->activities->whereIn('id', $this->selectedActivities);
            
            $pdf = Pdf::loadView('pdf.siwes-final-report', [
                'user' => $user,
                'activities' => $activities,
                'generatedAt' => now(),
            ]);

            $filename = 'siwes-final-report-' . Str::slug($user->name) . '-' . now()->format('Y-m-d') . '.pdf';
            
            // Store the PDF temporarily
            Storage::disk('public')->put('temp/' . $filename, $pdf->output());
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error generating report: ' . $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function submitReport()
    {
        $this->validate([
            'reportFile' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $this->isSubmitting = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            $user = Auth::user();
            
            // Delete old report if exists
            if ($user->final_report_path) {
                Storage::disk('public')->delete($user->final_report_path);
            }
            
            // Store the new report
            $path = $this->reportFile->store('siwes/final-reports', 'public');
            
            // Update user record
            $user->update([
                'final_report_path' => $path,
                'report_submitted_at' => now(),
            ]);
            
            $this->successMessage = 'Your final report has been submitted successfully!';
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error submitting report: ' . $e->getMessage();
        } finally {
            $this->isSubmitting = false;
        }
    }
}; ?>

<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">Final SIWES Report Submission</h2>
    
    @if($successMessage)
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ $successMessage }}
        </div>
    @endif
    
    @if($errorMessage)
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $errorMessage }}
        </div>
    @endif
    
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Your Activity Logs</h3>
        <div class="bg-gray-50 p-4 rounded-lg max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" 
                                   wire:model.live="selectAll"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activities as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       wire:model.live="selectedActivities"
                                       value="{{ $activity->id }}"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $activity->formatted_date }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $activity->activity_description }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            <button 
                wire:click="generateReport"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <span wire:loading.remove>Generate Report (PDF)</span>
                <span wire:loading>Generating...</span>
            </button>
        </div>
    </div>
    
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold mb-4">Submit Final Report</h3>
        <p class="text-gray-600 mb-4">
            After generating your final report, please upload the signed PDF version for submission.
        </p>
        
        <form wire:submit.prevent="submitReport">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload Signed Final Report (PDF)
                </label>
                <input type="file" 
                       wire:model="reportFile"
                       accept=".pdf"
                       class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100"
                       required>
                @error('reportFile')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex justify-end">
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    <span wire:loading.remove>Submit Final Report</span>
                    <span wire:loading>Submitting...</span>
                </button>
            </div>
        </form>
    </div>
    
    @if(auth()->user()->final_report_path)
        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">Your Submitted Report</h3>
            <p class="text-blue-700">
                You submitted your final report on {{ auth()->user()->report_submitted_at->format('F j, Y \a\t g:i A') }}.
            </p>
            <div class="mt-2">
                <a href="{{ route('siwes.view-report') }}" 
                   target="_blank"
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-file-pdf mr-1"></i> View Submitted Report
                </a>
            </div>
        </div>
    @endif
</div>
