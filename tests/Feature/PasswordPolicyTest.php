<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_passwords_shorter_than_ten_characters(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Short Password Owner',
            'username' => 'shortpassowner',
            'business_name' => 'Short Password Catering',
            'email' => 'short-password@example.com',
            'password' => 'shortpass',
            'password_confirmation' => 'shortpass',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'short-password@example.com']);
    }

    public function test_password_reset_rejects_passwords_shorter_than_ten_characters(): void
    {
        $user = User::factory()->create([
            'email' => 'password-policy@example.com',
            'password' => 'old-password',
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'shortpass',
            'password_confirmation' => 'shortpass',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
