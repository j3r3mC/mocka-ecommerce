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
        Storage::fake('media');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'avatar.jpg',
                    800,
                    600
                ),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.username', 'jeremy');

        $user->profile->refresh();

        $avatarPath = $user->profile->avatar_path;

        $this->assertNotNull($avatarPath);

        $this->assertStringStartsWith(
            "avatars/user-{$user->id}/",
            $avatarPath
        );

        $this->assertStringEndsWith(
            '.webp',
            $avatarPath
        );

        Storage::disk('media')->assertExists($avatarPath);

        $absolutePath = Storage::disk('media')->path($avatarPath);
        $dimensions = getimagesize($absolutePath);

        $this->assertNotFalse($dimensions);
        $this->assertSame(400, $dimensions[0]);
        $this->assertSame(400, $dimensions[1]);
        $this->assertSame('image/webp', $dimensions['mime']);

        $response
            ->assertJsonPath(
                'profile.avatar_path',
                $avatarPath
            )
            ->assertJsonPath(
                'profile.avatar_url',
                Storage::disk('media')->url($avatarPath)
            );
    }

    public function test_uploading_new_avatar_deletes_previous_avatar(): void
    {
        Storage::fake('media');

        $user = $this->createUserWithProfile();

        $oldPath = "avatars/user-{$user->id}/old-avatar.webp";

        Storage::disk('media')->put(
            $oldPath,
            'ancien-avatar'
        );

        $user->profile->update([
            'avatar_path' => $oldPath,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image(
                    'new-avatar.png',
                    600,
                    900
                ),
            ]);

        $response->assertOk();

        $user->profile->refresh();

        $newPath = $user->profile->avatar_path;

        $this->assertNotNull($newPath);
        $this->assertNotSame($oldPath, $newPath);

        $this->assertStringStartsWith(
            "avatars/user-{$user->id}/",
            $newPath
        );

        $this->assertStringEndsWith(
            '.webp',
            $newPath
        );

        Storage::disk('media')->assertMissing($oldPath);
        Storage::disk('media')->assertExists($newPath);

        $absolutePath = Storage::disk('media')->path($newPath);
        $dimensions = getimagesize($absolutePath);

        $this->assertNotFalse($dimensions);
        $this->assertSame(400, $dimensions[0]);
        $this->assertSame(400, $dimensions[1]);
        $this->assertSame('image/webp', $dimensions['mime']);
    }

    public function test_avatar_is_required(): void
    {
        Storage::fake('media');

        $user = $this->createUserWithProfile();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/profile/avatar', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);

        $this->assertNull($user->profile->avatar_path);

        $this->assertSame(
            [],
            Storage::disk('media')->allFiles()
        );
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('media');

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

        $this->assertSame(
            [],
            Storage::disk('media')->allFiles()
        );
    }

    public function test_avatar_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('media');

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

        $this->assertSame(
            [],
            Storage::disk('media')->allFiles()
        );
    }

    public function test_user_without_profile_cannot_upload_avatar(): void
    {
        Storage::fake('media');

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

        $this->assertSame(
            [],
            Storage::disk('media')->allFiles()
        );
    }

    public function test_guest_cannot_upload_avatar(): void
    {
        Storage::fake('media');

        $response = $this->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->image(
                'avatar.jpg',
                500,
                500
            ),
        ]);

        $response->assertUnauthorized();

        $this->assertGuest('web');

        $this->assertSame(
            [],
            Storage::disk('media')->allFiles()
        );
    }

    public function test_media_disk_uses_media_server_url(): void
    {
        $mediaUrl = rtrim(
            (string) config('filesystems.disks.media.url'),
            '/'
        );

        $this->assertSame(
            'http://localhost:8081',
            $mediaUrl
        );
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