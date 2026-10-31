<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_current_user(): void
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
            ->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'first_name' => 'Jeremy',
                    'last_name' => 'Corbiere',
                    'email' => 'jeremy@example.fr',
                    'phone' => '0612345678',
                ],
            ])
            ->assertJsonMissing([
                'password' => true,
                'remember_token' => true,
            ]);
    }

    public function test_guest_cannot_get_current_user(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();

        $this->assertGuest('web');
    }
}