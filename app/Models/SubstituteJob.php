<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubstituteJob extends Model
{
    protected $fillable = [
        'school_profile_id',
        'subject',
        'grade_level',
        'start_time',
        'end_time',
        'date',
        'status',
        'description',
        'lesson_plan_file',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the school that posted this job.
     */
    public function schoolProfile(): BelongsTo
    {
        return $this->belongsTo(SchoolProfile::class);
    }

    /**
     * Get the current active booking for this job.
     */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    /**
     * Get all booking requests/history for this job.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
