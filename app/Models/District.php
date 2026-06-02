<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'name',
        'state',
        'compliance_rules',
    ];

    protected $casts = [
        'compliance_rules' => 'array',
    ];

    /**
     * Get the schools associated with this district.
     */
    public function schoolProfiles(): HasMany
    {
        return $this->hasMany(SchoolProfile::class);
    }

    /**
     * Get the teachers associated with this district.
     */
    public function teacherProfiles(): HasMany
    {
        return $this->hasMany(TeacherProfile::class);
    }
}
