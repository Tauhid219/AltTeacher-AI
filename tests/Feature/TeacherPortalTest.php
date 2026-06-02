<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolProfile;
use App\Models\TeacherProfile;
use App\Models\SubstituteJob;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles, districts, users, profiles, and initial jobs
        $this->seed();
    }

    /**
     * Unauthenticated users are redirected to login.
     */
    public function test_guest_cannot_access_teacher_dashboard(): void
    {
        $response = $this->get('/teacher/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * School admins cannot access teacher dashboard.
     */
    public function test_school_admin_cannot_access_teacher_dashboard(): void
    {
        $schoolAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $this->assertNotNull($schoolAdmin);

        $response = $this->actingAs($schoolAdmin)->get('/teacher/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Approved teacher can access dashboard and see matching jobs.
     */
    public function test_approved_teacher_can_access_dashboard_and_see_matching_jobs(): void
    {
        $approvedTeacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($approvedTeacherUser);

        $response = $this->actingAs($approvedTeacherUser)->get('/teacher/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Profile Active');
        
        // Assert they see the seeded open job (Science Grade 5) matching their preferences
        $response->assertSee('Science');
        $response->assertSee('Grade 5');
        $response->assertSee('Springfield Elementary School');
    }

    /**
     * Pending teacher sees onboarding pending message and no job listings.
     */
    public function test_pending_teacher_sees_pending_message_and_no_job_listings(): void
    {
        $pendingTeacherUser = User::where('email', 'krusty@example.com')->first();
        $this->assertNotNull($pendingTeacherUser);

        $response = $this->actingAs($pendingTeacherUser)->get('/teacher/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Onboarding Review Pending');
        $response->assertSee('Your profile must be approved to view and book jobs');
    }

    /**
     * Teacher can update their hourly rate and classroom preferences.
     */
    public function test_teacher_can_update_preferences(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $teacherUser->teacherProfile;

        $school = SchoolProfile::first();
        $this->assertNotNull($school);

        $prefData = [
            'hourly_rate' => 45.50,
            'grades' => ['Grade 9', 'Grade 10'],
            'subjects' => ['Physics', 'Chemistry'],
            'preferred_schools' => [$school->id],
        ];

        $response = $this->actingAs($teacherUser)->post('/teacher/preferences', $prefData);
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        $teacherProfile->refresh();
        $this->assertEquals(45.50, $teacherProfile->hourly_rate);
        $this->assertEquals(['Grade 9', 'Grade 10'], $teacherProfile->classroom_preferences['grades']);
        $this->assertEquals(['Physics', 'Chemistry'], $teacherProfile->classroom_preferences['subjects']);
        $this->assertEquals([$school->id], $teacherProfile->classroom_preferences['preferred_schools']);
    }

    /**
     * Approved teacher can book an open matching job.
     */
    public function test_approved_teacher_can_book_job(): void
    {
        $approvedTeacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $approvedTeacherUser->teacherProfile;

        // Fetch an open job
        $job = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($job);

        $response = $this->actingAs($approvedTeacherUser)->post("/teacher/book/{$job->id}");
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Assert job status is now filled
        $job->refresh();
        $this->assertEquals('filled', $job->status);

        // Assert booking record is created in database
        $this->assertDatabaseHas('bookings', [
            'substitute_job_id' => $job->id,
            'teacher_profile_id' => $teacherProfile->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Pending teacher is blocked from booking jobs.
     */
    public function test_pending_teacher_cannot_book_job(): void
    {
        $pendingTeacherUser = User::where('email', 'krusty@example.com')->first();
        
        $job = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($job);

        $response = $this->actingAs($pendingTeacherUser)->post("/teacher/book/{$job->id}");
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');

        // Assert job is still open
        $job->refresh();
        $this->assertEquals('open', $job->status);

        // Assert no bookings were made
        $this->assertDatabaseMissing('bookings', [
            'substitute_job_id' => $job->id,
        ]);
    }
}
