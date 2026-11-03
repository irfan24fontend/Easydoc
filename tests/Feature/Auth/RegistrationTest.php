<?php

namespace Tests\Feature\Auth;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '01712345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'bmdc_number' => 'A-12345',
            'speciality' => 'General Medicine',
            'qualification' => 'MBBS',
            'designation' => 'Medical Officer',
            'medical_college' => 'Test Medical College',
            'experience' => 2,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'photo' => UploadedFile::fake()->create('doctor.jpg', 100, 'image/jpeg'),
            'bmdc_certificate' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('doctor.approval.status', absolute: false));
    }
}
