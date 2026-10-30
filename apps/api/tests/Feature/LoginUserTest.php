<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jeremy@example.fr',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'inconnu@example.fr',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Identifiants invalides.',
            ]);

        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jeremy@example.fr',
            'password' => 'MauvaisMotDePasse',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Identifiants invalides.',
            ]);

        $this->assertGuest();
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'jeremy@example.fr',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->assertGuest();
    }
}