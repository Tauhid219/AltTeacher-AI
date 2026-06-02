<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id',
        'district_id',
        'classroom_preferences',
        'hourly_rate',
        'availability',
        'onboarding_status',
    ];

    protected $casts = [
        'classroom_preferences' => 'array',
        'availability' => 'array',
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the district this teacher is onboarded to.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the teacher's uploaded credentials.
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    /**
     * Get the bookings completed or accepted by this teacher.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
