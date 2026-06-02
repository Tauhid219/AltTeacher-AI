<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'substitute_job_id',
        'teacher_profile_id',
        'status',
    ];

    /**
     * Get the job associated with this booking.
     */
    public function substituteJob(): BelongsTo
    {
        return $this->belongsTo(SubstituteJob::class);
    }

    /**
     * Get the teacher who accepted this booking.
     */
    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    /**
     * Get the timesheet record for this booking.
     */
    public function timesheet(): HasOne
    {
        return $this->hasOne(Timesheet::class);
    }

    /**
     * Get the lesson plan document for this booking.
     */
    public function lessonPlan(): HasOne
    {
        return $this->hasOne(LessonPlan::class);
    }
}
