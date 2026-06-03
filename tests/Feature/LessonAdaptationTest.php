<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LessonPlan;
use App\Models\SubstituteJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonAdaptationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles, districts, users, profiles, and initial jobs
        $this->seed();
        Storage::fake('public');
    }

    /**
     * School Admin can post a substitute job with an optional lesson plan file.
     */
    public function test_school_admin_can_post_job_with_lesson_plan_file(): void
    {
        $schoolUser = User::where('email', 'skinner@springfield.edu')->first();
        $this->assertNotNull($schoolUser);

        $fakeFile = UploadedFile::fake()->create('lesson_plan_notes.pdf', 300, 'application/pdf');

        $response = $this->actingAs($schoolUser)->post(route('school.jobs.store'), [
            'subject' => 'Physics',
            'grade_level' => 'Grade 10',
            'date' => Carbon::now()->addDays(5)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'description' => 'Intro to Newtonian physics. Force and acceleration.',
            'lesson_plan_file' => $fakeFile,
        ]);

        $response->assertRedirect(route('school.dashboard'));

        // Assert job exists in database and has file path
        $job = SubstituteJob::where('subject', 'Physics')->first();
        $this->assertNotNull($job);
        $this->assertNotNull($job->lesson_plan_file);

        // Assert file exists in storage
        Storage::disk('public')->assertExists($job->lesson_plan_file);
    }

    /**
     * Booking a job automatically generates adapted lesson plan via Gemini AI (Mocked fallback).
     */
    public function test_booking_job_generates_adapted_lesson_plan(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacherUser);

        // We need an open job
        $openJob = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($openJob);

        // Ensure teacher is compliant and books the job
        $response = $this->actingAs($teacherUser)->post("/teacher/book/{$openJob->id}");
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Assert job status changed to filled
        $openJob->refresh();
        $this->assertEquals('filled', $openJob->status);

        // Assert Booking exists
        $booking = Booking::where('substitute_job_id', $openJob->id)
            ->where('teacher_profile_id', $teacherUser->teacherProfile->id)
            ->first();
        $this->assertNotNull($booking);

        // Assert LessonPlan exists and has generated details
        $lessonPlan = LessonPlan::where('booking_id', $booking->id)->first();
        $this->assertNotNull($lessonPlan);
        $this->assertNotEmpty($lessonPlan->ai_summary);

        $activities = $lessonPlan->ai_generated_activities;
        $this->assertIsArray($activities);
        $this->assertArrayHasKey('quizzes', $activities);
        $this->assertArrayHasKey('icebreakers', $activities);

        // Fallback generates exactly 10 quizzes and 3 icebreakers
        $this->assertCount(10, $activities['quizzes']);
        $this->assertCount(3, $activities['icebreakers']);
    }

    /**
     * Substitute Teacher can view/download the adapted lesson plan packet as PDF.
     */
    public function test_teacher_can_download_lesson_plan_pdf(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacherUser);

        // Let's find an existing booking that has a lesson plan
        // The seeder seeds a completed booking (booking 1) for Bob with a lesson plan
        $booking = Booking::where('teacher_profile_id', $teacherUser->teacherProfile->id)
            ->whereHas('lessonPlan')
            ->first();

        $this->assertNotNull($booking);
        $this->assertNotNull($booking->lessonPlan);

        // Request the PDF download
        $response = $this->actingAs($teacherUser)->get(route('teacher.lesson_plan.pdf', $booking->id));

        // Assert response is successful and has correct content headers
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=lesson_plan_booking_'.$booking->id.'.pdf');
    }
}
