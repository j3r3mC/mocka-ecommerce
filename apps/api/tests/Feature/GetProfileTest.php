<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => '0612345678',
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $profile = $user->profile()->create([
            'username' => 'jeremy',
            'bio' => 'Développeur web.',
            'city' => 'Béziers',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/profile');

        $response
            ->assertOk()
            ->assertJson([
                'profile' => [
                    'id' => $profile->id,
                    'username' => 'jeremy',
                    'avatar_path' => null,
                    'bio' => 'Développeur web.',
                    'city' => 'Béziers',
                ],
            ])
            ->assertJsonMissing([
                'user_id' => true,
            ]);
    }

    public function test_authenticated_user_without_profile_gets_not_found(): void
    {
        $user = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => null,
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/profile');

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => 'Profil introuvable.',
            ]);
    }

    public function test_guest_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertUnauthorized();

        $this->assertGuest('web');
    }
}