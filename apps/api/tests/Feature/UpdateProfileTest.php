<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $profile = $user->profile()->create([
            'username' => 'jeremy',
            'bio' => 'Ancienne biographie.',
            'city' => 'Béziers',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'username' => 'jeremy-dev',
                'bio' => 'Nouvelle biographie.',
                'city' => 'Montpellier',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'profile' => [
                    'id' => $profile->id,
                    'username' => 'jeremy-dev',
                    'avatar_path' => null,
                    'bio' => 'Nouvelle biographie.',
                    'city' => 'Montpellier',
                ],
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'user_id' => $user->id,
            'username' => 'jeremy-dev',
            'bio' => 'Nouvelle biographie.',
            'city' => 'Montpellier',
        ]);
    }

    public function test_profile_can_be_partially_updated(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $profile = $user->profile()->create([
            'username' => 'jeremy',
            'bio' => 'Ancienne biographie.',
            'city' => 'Béziers',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'bio' => 'Nouvelle biographie.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.username', 'jeremy')
            ->assertJsonPath('profile.bio', 'Nouvelle biographie.')
            ->assertJsonPath('profile.city', 'Béziers');

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'username' => 'jeremy',
            'bio' => 'Nouvelle biographie.',
            'city' => 'Béziers',
        ]);
    }

    public function test_profile_can_keep_current_username(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $user->profile()->create([
            'username' => 'jeremy',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'username' => 'jeremy',
                'city' => 'Béziers',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'username' => 'jeremy',
            'city' => 'Béziers',
        ]);
    }

    public function test_username_cannot_be_used_by_another_profile(): void
    {
        $firstUser = $this->createUser('jeremy@example.fr');
        $secondUser = $this->createUser('alice@example.fr');

        $firstUser->profile()->create([
            'username' => 'jeremy',
        ]);

        $secondUser->profile()->create([
            'username' => 'alice',
        ]);

        $response = $this
            ->actingAs($secondUser, 'web')
            ->patchJson('/api/profile', [
                'username' => 'jeremy',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $secondUser->id,
            'username' => 'alice',
        ]);
    }

    public function test_user_without_profile_gets_not_found(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'bio' => 'Nouvelle biographie.',
            ]);

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => 'Profil introuvable.',
            ]);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->patchJson('/api/profile', [
            'bio' => 'Nouvelle biographie.',
        ]);

        $response->assertUnauthorized();

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