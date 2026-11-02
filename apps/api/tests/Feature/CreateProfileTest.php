<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_profile(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile', [
                'username' => 'jeremy',
                'bio' => 'Développeur web.',
                'city' => 'Béziers',
            ]);

        $response
            ->assertCreated()
            ->assertJson([
                'profile' => [
                    'username' => 'jeremy',
                    'avatar_path' => null,
                    'bio' => 'Développeur web.',
                    'city' => 'Béziers',
                ],
            ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'username' => 'jeremy',
            'bio' => 'Développeur web.',
            'city' => 'Béziers',
        ]);
    }

    public function test_user_cannot_create_multiple_profiles(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $user->profile()->create([
            'username' => 'jeremy',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile', [
                'username' => 'jeremy-bis',
            ]);

        $response
            ->assertConflict()
            ->assertJson([
                'message' => 'Un profil existe déjà pour cet utilisateur.',
            ]);

        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_username_must_be_unique(): void
    {
        $firstUser = $this->createUser('jeremy@example.fr');
        $secondUser = $this->createUser('alice@example.fr');

        $firstUser->profile()->create([
            'username' => 'jeremy',
        ]);

        $response = $this
            ->actingAs($secondUser, 'web')
            ->postJson('/api/profile', [
                'username' => 'jeremy',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);

        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_username_is_required(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);

        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_guest_cannot_create_profile(): void
    {
        $response = $this->postJson('/api/profile', [
            'username' => 'jeremy',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('profiles', 0);
        $this->assertGuest('web');
    }

    private function createUser(string $email): User
    {
        return User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => $email,
            'phone' => null,
            'password' => 'MotDePasseUltraSecure123',
        ]);
    }
}