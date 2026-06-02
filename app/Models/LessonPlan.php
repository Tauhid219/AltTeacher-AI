<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlan extends Model
{
    protected $fillable = [
        'booking_id',
        'original_plan_text',
        'ai_summary',
        'ai_generated_activities',
    ];

    protected $casts = [
        'ai_generated_activities' => 'array',
    ];

    /**
     * Get the booking associated with this lesson plan.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
