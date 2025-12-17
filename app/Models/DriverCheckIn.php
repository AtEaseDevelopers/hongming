<?php
// app/Models/DriverCheckIn.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverCheckIn extends Model
{
    use HasFactory;

    const TYPE_CHECK_IN = 'check_in';
    const TYPE_CHECK_OUT = 'check_out';

    protected $fillable = [
        'driver_id',
        'lorry_id',
        'type',
        'latitude',
        'longitude',
        'check_time',
        'notes',
    ];

    protected $casts = [
        'check_time' => 'datetime',
    ];

    /**
     * Get the user that performed the check-in/check-out
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the task associated with the check-in/check-out
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope to get check-ins only
     */
    public function scopeCheckIns($query)
    {
        return $query->where('type', self::TYPE_CHECK_IN);
    }

    /**
     * Scope to get check-outs only
     */
    public function scopeCheckOuts($query)
    {
        return $query->where('type', self::TYPE_CHECK_OUT);
    }

    /**
     * Scope to get records for a specific task
     */
    /**
     * Scope to get records for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get records within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_time', [$startDate, $endDate]);
    }

    /**
     * Check if this record is a check-in
     */
    public function isCheckIn(): bool
    {
        return $this->type === self::TYPE_CHECK_IN;
    }

    /**
     * Check if this record is a check-out
     */
    public function isCheckOut(): bool
    {
        return $this->type === self::TYPE_CHECK_OUT;
    }

}