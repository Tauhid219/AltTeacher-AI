<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolProfile;
use App\Models\TeacherProfile;
use App\Models\SubstituteJob;
use App\Models\Credential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class CredentialVerificationTest extends TestCase
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
     * Unauthenticated users cannot upload credentials.
     */
    public function test_guest_cannot_upload_credentials(): void
    {
        $response = $this->post('/teacher/credentials', [
            'document_type' => 'state_teaching_license',
            'document' => UploadedFile::fake()->create('license.pdf', 200),
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated teacher can upload a valid teaching license which is parsed and verified by AI.
     */
    public function test_teacher_can_upload_and_verify_license(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($teacherUser);
        $teacherProfile = $teacherUser->teacherProfile;

        // Count initial credentials
        $initialCount = $teacherProfile->credentials()->count();

        $response = $this->actingAs($teacherUser)->post('/teacher/credentials', [
            'document_type' => 'state_teaching_license',
            'document' => UploadedFile::fake()->create('license.pdf', 500, 'application/pdf'),
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        $newCredential = $teacherProfile->credentials()->where('document_type', 'state_teaching_license')->first();
        $this->assertNotNull($newCredential);
        Storage::disk('public')->assertExists($newCredential->document_path);

        // Assert database record exists and is verified
        $this->assertDatabaseHas('credentials', [
            'teacher_profile_id' => $teacherProfile->id,
            'document_type' => 'state_teaching_license',
            'verification_status' => 'verified',
        ]);

        $newCredential = $teacherProfile->credentials()->where('document_type', 'state_teaching_license')->first();
        $this->assertNotNull($newCredential->expiry_date);
        $this->assertTrue(Carbon::parse($newCredential->expiry_date)->isFuture());
    }

    /**
     * Uploaded expired document gets automatically flagged as rejected.
     */
    public function test_expired_document_automatically_flagged_rejected(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $teacherUser->teacherProfile;

        $response = $this->actingAs($teacherUser)->post('/teacher/credentials', [
            'document_type' => 'state_teaching_license',
            'document' => UploadedFile::fake()->create('expired_license.pdf', 500, 'application/pdf'),
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error'); // should show error indicating flagged expired

        $this->assertDatabaseHas('credentials', [
            'teacher_profile_id' => $teacherProfile->id,
            'document_type' => 'state_teaching_license',
            'verification_status' => 'rejected',
        ]);
    }

    /**
     * Non-compliant teacher is blocked from booking jobs.
     */
    public function test_non_compliant_teacher_is_blocked_from_booking(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $teacherUser->teacherProfile;

        // Springfield district requires both state_teaching_license and background_check.
        // Let's delete the background check credential of Bob to make him non-compliant
        $teacherProfile->credentials()->where('document_type', 'background_check')->delete();

        $openJob = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($openJob);

        $response = $this->actingAs($teacherUser)->post("/teacher/book/{$openJob->id}");
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Booking blocked: You are non-compliant', session('error'));

        // Assert job status remains open
        $openJob->refresh();
        $this->assertEquals('open', $openJob->status);
    }

    /**
     * Compliant teacher is allowed to book jobs.
     */
    public function test_compliant_teacher_can_book_successfully(): void
    {
        $teacherUser = User::where('email', 'bob@example.com')->first();
        $teacherProfile = $teacherUser->teacherProfile;

        // Bob has both verified teaching license and background check pre-seeded in the database and they are not expired.
        // Thus he is compliant.
        
        $openJob = SubstituteJob::where('status', 'open')->first();
        $this->assertNotNull($openJob);

        $response = $this->actingAs($teacherUser)->post("/teacher/book/{$openJob->id}");
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Assert job is booked and status updated to filled
        $openJob->refresh();
        $this->assertEquals('filled', $openJob->status);
    }
}
