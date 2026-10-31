<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/auth/logout');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Déconnexion réussie.',
            ]);

        $this->assertGuest('web');
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();

        $this->assertGuest('web');
    }
}