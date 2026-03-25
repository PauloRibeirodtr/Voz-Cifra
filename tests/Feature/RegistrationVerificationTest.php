<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\VerificationCode;

class RegistrationVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_verification_code_and_redirects_to_verification()
    {
        $response = $this->post('/cadastro', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'phone' => '123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verification.code'));

        $this->assertDatabaseHas('users', ['email' => 'tester@example.com']);

        $user = User::where('email', 'tester@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse((bool) $user->is_verified);

        $this->assertDatabaseHas('verification_codes', ['user_id' => $user->id]);
    }
}
