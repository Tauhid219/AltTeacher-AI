<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\SubstituteJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database
        $this->seed();
    }

    /**
     * Test that districts are seeded and have correct schema.
     */
    public function test_districts_are_seeded(): void
    {
        $this->assertDatabaseHas('districts', [
            'name' => 'Springfield School District 401',
            'state' => 'IL',
        ]);

        $district = District::where('name', 'Springfield School District 401')->first();
        $this->assertNotNull($district);
        $this->assertIsArray($district->compliance_rules);
        $this->assertEquals(30, $district->compliance_rules['max_weekly_hours']);
    }

    /**
     * Test user and profiles relations.
     */
    public function test_user_has_profiles_relations(): void
    {
        $schoolUser = User::where('email', 'skinner@springfield.edu')->first();
        $this->assertNotNull($schoolUser);
        $this->assertNotNull($schoolUser->schoolProfile);
        $this->assertEquals('Springfield Elementary School', $schoolUser->schoolProfile->school_name);

        $teacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacherUser);
        $this->assertNotNull($teacherUser->teacherProfile);
        $this->assertEquals(35.00, $teacherUser->teacherProfile->hourly_rate);
    }

    /**
     * Test jobs, bookings, and timesheets relations.
     */
    public function test_jobs_bookings_and_timesheets_relations(): void
    {
        $job = SubstituteJob::where('subject', 'Mathematics')->first();
        $this->assertNotNull($job);
        $this->assertEquals('completed', $job->status);

        $this->assertNotNull($job->booking);
        $booking = $job->booking;
        $this->assertEquals('completed', $booking->status);

        $this->assertNotNull($booking->timesheet);
        $this->assertEquals('approved', $booking->timesheet->status);
        $this->assertEquals(285.60, $booking->timesheet->calculated_pay);

        $this->assertNotNull($booking->lessonPlan);
        $this->assertNotNull($booking->lessonPlan->ai_summary);
    }
}
