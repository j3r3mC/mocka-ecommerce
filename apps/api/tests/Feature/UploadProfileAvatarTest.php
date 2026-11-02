<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'avatar.jpg',
                    500,
                    500
                ),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.username', 'jeremy');

        $user->profile->refresh();

        $this->assertNotNull($user->profile->avatar_path);

        Storage::disk('public')->assertExists(
            $user->profile->avatar_path
        );
    }

    public function test_uploading_new_avatar_deletes_previous_avatar(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithProfile();

        $oldPath = UploadedFile::fake()
            ->image('old-avatar.jpg', 500, 500)
            ->store('avatars', 'public');

        $user->profile->update([
            'avatar_path' => $oldPath,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'new-avatar.jpg',
                    500,
                    500
                ),
            ]);

        $response->assertOk();

        $user->profile->refresh();

        Storage::disk('public')->assertMissing($oldPath);

        Storage::disk('public')->assertExists(
            $user->profile->avatar_path
        );
    }

    public function test_avatar_is_required(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);

        $this->assertNull($user->profile->avatar_path);
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->create(
                    'document.pdf',
                    100,
                    'application/pdf'
                ),
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);

        $this->assertNull($user->profile->avatar_path);
    }

    public function test_avatar_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'avatar.jpg',
                    500,
                    500
                )->size(2049),
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);

        $this->assertNull($user->profile->avatar_path);
    }

    public function test_user_without_profile_cannot_upload_avatar(): void
    {
        Storage::fake('public');

        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'avatar.jpg',
                    500,
                    500
                ),
            ]);

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => 'Profil introuvable.',
            ]);

        Storage::disk('public')->assertDirectoryEmpty('avatars');
    }

    public function test_guest_cannot_upload_avatar(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->image(
                'avatar.jpg',
                500,
                500
            ),
        ]);

        $response->assertUnauthorized();

        $this->assertGuest('web');
        Storage::disk('public')->assertDirectoryEmpty('avatars');
    }

    private function createUserWithProfile(): User
    {
        $user = $this->createUser();

        $user->profile()->create([
            'username' => 'jeremy',
        ]);

        return $user;
    }

    private function createUser(): User
    {
        return User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => null,
            'password' => 'MotDePasseUltraSecure123',
        ]);
    }
}