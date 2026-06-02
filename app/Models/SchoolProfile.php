<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolProfile extends Model
{
    protected $fillable = [
        'user_id',
        'district_id',
        'school_name',
        'address',
    ];

    /**
     * Get the user that owns the school profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the district that the school belongs to.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the substitute jobs posted by this school.
     */
    public function substituteJobs(): HasMany
    {
        return $this->hasMany(SubstituteJob::class);
    }
}
