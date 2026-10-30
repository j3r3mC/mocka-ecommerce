<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterUserTest extends TestCase
{
        use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $payload = [
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
            'password_confirmation' => 'MotDePasseUltraSecure123',
        ];

        $response = $this->postJson(
            '/api/auth/register',
            $payload
        );

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'jeremy@example.fr',
        ]);    
    }

    public function test_email_must_be_unique(): void
    {
        $payload = [
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
            'password_confirmation' => 'MotDePasseUltraSecure123',
        ];

        $this->postJson('/api/auth/register', $payload);

        $response = $this->postJson(
            '/api/auth/register',
            $payload
        );

        $response->assertStatus(422);
    }
}