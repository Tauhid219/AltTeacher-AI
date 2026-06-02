<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolProfile;
use App\Models\TeacherProfile;
use App\Models\SubstituteJob;
use App\Models\Booking;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TimesheetPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles, districts, users, profiles, and initial jobs
        $this->seed();
    }

    /**
     * Teacher can clock in to their booking successfully.
     */
    public function test_teacher_can_clock_in(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacherUser);

        // Find an open job and book it
        $job = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($job);

        // Book the job
        $this->actingAs($teacherUser)->post("/teacher/book/{$job->id}");

        $booking = Booking::where('substitute_job_id', $job->id)
            ->where('teacher_profile_id', $teacherUser->teacherProfile->id)
            ->first();
        $this->assertNotNull($booking);
        $this->assertNull($booking->timesheet);

        // Clock In
        $checkInTimeStr = Carbon::now()->subHours(8)->toDateTimeString();
        $response = $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-in", [
            'check_in_time' => $checkInTimeStr
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Assert timesheet exists in database
        $this->assertDatabaseHas('timesheets', [
            'booking_id' => $booking->id,
            'status' => 'pending',
        ]);

        $timesheet = Timesheet::where('booking_id', $booking->id)->first();
        $this->assertNotNull($timesheet);
        $this->assertEquals($checkInTimeStr, $timesheet->check_in_time->toDateTimeString());
    }

    /**
     * Teacher can clock out, calculating hours, pay, and completing job status.
     */
    public function test_teacher_can_clock_out_with_calculations(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $teacherUser->teacherProfile;

        // Find open job and book it
        $job = SubstituteJob::where('status', 'open')->first();
        $this->actingAs($teacherUser)->post("/teacher/book/{$job->id}");
        
        $booking = Booking::where('substitute_job_id', $job->id)->first();

        // Clock in at specific time (e.g., 8.5 hours ago)
        $checkInTime = Carbon::now()->subMinutes(510); // 8.5 hours
        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-in", [
            'check_in_time' => $checkInTime->toDateTimeString()
        ]);

        // Clock out now
        $checkOutTime = Carbon::now();
        $response = $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-out", [
            'check_out_time' => $checkOutTime->toDateTimeString()
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Verify calculations
        $timesheet = Timesheet::where('booking_id', $booking->id)->first();
        $this->assertNotNull($timesheet);
        $this->assertEquals(8.50, (float)$timesheet->calculated_hours);

        // Bob's hourly rate is $35.00
        $expectedPay = round(8.50 * $teacherProfile->hourly_rate, 2);
        $this->assertEquals($expectedPay, (float)$timesheet->calculated_pay);

        // Verify job and booking status completed
        $booking->refresh();
        $job->refresh();
        $this->assertEquals('completed', $booking->status);
        $this->assertEquals('completed', $job->status);
    }

    /**
     * School Admin can approve timesheets.
     */
    public function test_school_admin_can_approve_timesheet(): void
    {
        $schoolAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $teacherUser = User::where('email', 'bob@example.com')->first();

        // Get an open job, book it, clock in, clock out to generate a complete timesheet
        $job = SubstituteJob::where('school_profile_id', $schoolAdmin->schoolProfile->id)
            ->where('status', 'open')
            ->first();
        $this->assertNotNull($job);

        $this->actingAs($teacherUser)->post("/teacher/book/{$job->id}");
        $booking = Booking::where('substitute_job_id', $job->id)->first();

        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-in", [
            'check_in_time' => Carbon::now()->subHours(6)->toDateTimeString()
        ]);
        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-out", [
            'check_out_time' => Carbon::now()->toDateTimeString()
        ]);

        $timesheet = Timesheet::where('booking_id', $booking->id)->first();
        $this->assertNotNull($timesheet);
        $this->assertEquals('pending', $timesheet->status);

        // Approve timesheet as school admin
        $response = $this->actingAs($schoolAdmin)->post("/school/timesheet/{$timesheet->id}/approve");

        $response->assertRedirect(route('school.dashboard'));
        $response->assertSessionHas('success');

        $timesheet->refresh();
        $this->assertEquals('approved', $timesheet->status);
    }

    /**
     * School Admin can reject timesheets.
     */
    public function test_school_admin_can_reject_timesheet(): void
    {
        $schoolAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $teacherUser = User::where('email', 'bob@example.com')->first();

        $job = SubstituteJob::where('school_profile_id', $schoolAdmin->schoolProfile->id)
            ->where('status', 'open')
            ->first();

        $this->actingAs($teacherUser)->post("/teacher/book/{$job->id}");
        $booking = Booking::where('substitute_job_id', $job->id)->first();

        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-in", [
            'check_in_time' => Carbon::now()->subHours(6)->toDateTimeString()
        ]);
        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-out", [
            'check_out_time' => Carbon::now()->toDateTimeString()
        ]);

        $timesheet = Timesheet::where('booking_id', $booking->id)->first();

        // Reject timesheet as school admin
        $response = $this->actingAs($schoolAdmin)->post("/school/timesheet/{$timesheet->id}/reject");

        $response->assertRedirect(route('school.dashboard'));
        $response->assertSessionHas('error');

        $timesheet->refresh();
        $this->assertEquals('rejected', $timesheet->status);
    }

    /**
     * Unauthorized users cannot approve timesheets.
     */
    public function test_unauthorized_users_cannot_approve_timesheets(): void
    {
        $otherSchoolAdmin = User::where('email', 'miller@shelbyville.edu')->first();
        $teacherUser = User::where('email', 'bob@example.com')->first();

        // Create a timesheet for a Springfield job
        $springfieldAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $job = SubstituteJob::where('school_profile_id', $springfieldAdmin->schoolProfile->id)
            ->where('status', 'open')
            ->first();

        $this->actingAs($teacherUser)->post("/teacher/book/{$job->id}");
        $booking = Booking::where('substitute_job_id', $job->id)->first();

        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-in", [
            'check_in_time' => Carbon::now()->subHours(6)->toDateTimeString()
        ]);
        $this->actingAs($teacherUser)->post("/teacher/booking/{$booking->id}/clock-out", [
            'check_out_time' => Carbon::now()->toDateTimeString()
        ]);

        $timesheet = Timesheet::where('booking_id', $booking->id)->first();

        // Try to approve with Shelbyville admin, should result in 404/not found since query filters by school profile
        $response = $this->actingAs($otherSchoolAdmin)->post("/school/timesheet/{$timesheet->id}/approve");
        $response->assertStatus(404);

        $timesheet->refresh();
        $this->assertEquals('pending', $timesheet->status);
    }
}
