<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SiwesSettings extends Model
{
    protected $fillable = [
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the singleton instance of SIWES settings
     */
    public static function getInstance()
    {
        return static::first() ?? static::create([
            'is_active' => false,
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    /**
     * Check if SIWES is currently active
     */
    public function isSiwesActive(): bool
    {
        return $this->is_active && $this->start_date && now()->gte($this->start_date);
    }

    /**
     * Check if SIWES period has ended (24 weeks completed)
     */
    public function isSiwesEnded(): bool
    {
        return $this->end_date && now()->gt($this->end_date);
    }

    /**
     * Get current SIWES week number
     */
    public function getCurrentWeek(): ?int
    {
        if (!$this->isSiwesActive()) {
            return null;
        }

        return now()->diffInWeeks($this->start_date) + 1;
    }

    /**
     * Get available week numbers for selection
     */
    public function getAvailableWeeks(): array
    {
        if (!$this->start_date) {
            return [];
        }

        $currentWeek = $this->getCurrentWeek() ?? 1;
        $maxWeek = min(24, $currentWeek);
        
        return range(1, $maxWeek);
    }

    /**
     * Start SIWES period
     */
    public function startSiwes(): void
    {
        $this->update([
            'is_active' => true,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeeks(24)->toDateString(),
        ]);
    }

    /**
     * Check if SIWES can be turned off (not after 24 weeks)
     */
    public function canToggleOff(): bool
    {
        return !$this->isSiwesEnded();
    }
}
