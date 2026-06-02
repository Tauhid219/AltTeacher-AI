<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credential extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'document_type',
        'document_path',
        'extracted_info',
        'verification_status',
        'expiry_date',
    ];

    protected $casts = [
        'extracted_info' => 'array',
        'expiry_date' => 'date',
    ];

    /**
     * Get the teacher profile that owns this credential.
     */
    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
