<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles so Spatie can assign them
        $this->seed();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_teachers_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Teacher User',
            'email' => 'newteacher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'newteacher@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('teacher'));
        $this->assertNotNull($user->teacherProfile);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_school_admins_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'New School Admin User',
            'email' => 'newschool@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'school_admin',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'newschool@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('school_admin'));
        $this->assertNotNull($user->schoolProfile);
        $this->assertEquals('New School Admin User School', $user->schoolProfile->school_name);

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
