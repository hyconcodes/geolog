<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SiwesActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity_date',
        'week_number',
        'day_type',
        'activity_description',
        'document_path',
        'latitude',
        'longitude',
        'is_backdated',
        'backdate_reason',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'activity_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_backdated' => 'boolean',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the user that owns the activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supervisor who approved the log
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
ve      * Get the supervisor who rejected the log
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate week number based on SIWES start date
     */
    public static function calculateWeekNumber(Carbon $activityDate, Carbon $siwesStartDate): int
    {
        return $activityDate->diffInWeeks($siwesStartDate) + 1;
    }

    /**
     * Check if location is within 30 meters of PPA
     */
    public static function isWithinPPARadius(float $currentLat, float $currentLng, float $ppaLat, float $ppaLng): bool
    {
        $earthRadius = 6371000; // Earth's radius in meters
        
        $latDiff = deg2rad($ppaLat - $currentLat);
        $lngDiff = deg2rad($ppaLng - $currentLng);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($currentLat)) * cos(deg2rad($ppaLat)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance <= 30; // 30 meters radius
    }

    /**
     * Scope for pending approvals
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope for approved logs
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope for backdated logs
     */
    public function scopeBackdated($query)
    {
        return $query->where('is_backdated', true);
    }
}
