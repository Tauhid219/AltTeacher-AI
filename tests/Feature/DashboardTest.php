<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and initial DB structure
        $this->seed();
    }

    /**
     * Verify school admin can access school dashboard and post a job.
     */
    public function test_school_admin_can_access_dashboard_and_post_job(): void
    {
        $schoolAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $this->assertNotNull($schoolAdmin);

        $response = $this->actingAs($schoolAdmin)->get('/school/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Springfield Elementary School');

        // Test posting a job
        $jobData = [
            'subject' => 'Physics',
            'grade_level' => 'Grade 10',
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'description' => 'Intro to Quantum Physics.',
        ];

        $postResponse = $this->actingAs($schoolAdmin)->post('/school/jobs', $jobData);
        $postResponse->assertRedirect(route('school.dashboard'));

        $this->assertDatabaseHas('substitute_jobs', [
            'subject' => 'Physics',
            'grade_level' => 'Grade 10',
        ]);
    }

    /**
     * Verify district admin can access dashboard and approve/reject teachers.
     */
    public function test_district_admin_can_access_dashboard_and_approve_teacher(): void
    {
        $districtAdmin = User::where('email', 'chalmers@springfield.edu')->first();
        $this->assertNotNull($districtAdmin);

        $response = $this->actingAs($districtAdmin)->get('/district/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Superintendent Gary Chalmers');

        // Verify we can find the pending teacher
        $pendingTeacher = TeacherProfile::where('onboarding_status', 'pending')->first();
        $this->assertNotNull($pendingTeacher);

        // Approve teacher onboarding
        $approveResponse = $this->actingAs($districtAdmin)->post("/district/approve/{$pendingTeacher->id}");
        $approveResponse->assertRedirect(route('district.dashboard'));

        $this->assertEquals('approved', $pendingTeacher->fresh()->onboarding_status);
    }

    /**
     * Verify unauthorized users are blocked.
     */
    public function test_unauthorized_users_are_blocked_from_dashboards(): void
    {
        $teacher = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacher);

        // Teacher cannot access school dashboard
        $response1 = $this->actingAs($teacher)->get('/school/dashboard');
        $response1->assertStatus(403);

        // Teacher cannot access district dashboard
        $response2 = $this->actingAs($teacher)->get('/district/dashboard');
        $response2->assertStatus(403);
    }

    /**
     * Verify super admin can access admin dashboard, users list, and roles list.
     */
    public function test_super_admin_can_access_dashboard_and_manage_users_and_roles(): void
    {
        $superAdmin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($superAdmin);

        // Access dashboard
        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('System Administrator Dashboard');

        // Access User Management Index
        $userIndexResponse = $this->actingAs($superAdmin)->get('/admin/users');
        $userIndexResponse->assertStatus(200);
        $userIndexResponse->assertSee('User Management');
        $userIndexResponse->assertSee('chalmers@springfield.edu'); // should list seeded users

        // Access Role Management Index
        $roleIndexResponse = $this->actingAs($superAdmin)->get('/admin/roles');
        $roleIndexResponse->assertStatus(200);
        $roleIndexResponse->assertSee('Role Management');
        $roleIndexResponse->assertSee('super_admin');
        $roleIndexResponse->assertSee('teacher');
    }

    /**
     * Verify non-super admin users are blocked from admin resources.
     */
    public function test_non_super_admins_are_blocked_from_admin_resources(): void
    {
        $schoolAdmin = User::where('email', 'skinner@springfield.edu')->first();
        $this->assertNotNull($schoolAdmin);

        // School Admin cannot access admin dashboard
        $response1 = $this->actingAs($schoolAdmin)->get('/admin/dashboard');
        $response1->assertStatus(403);

        // School Admin cannot access user CRUD index
        $response2 = $this->actingAs($schoolAdmin)->get('/admin/users');
        $response2->assertStatus(403);
    }

    /**
     * Verify super admin dashboard redirect.
     */
    public function test_super_admin_redirects_to_admin_dashboard(): void
    {
        $superAdmin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($superAdmin);

        $response = $this->actingAs($superAdmin)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));
    }
}
