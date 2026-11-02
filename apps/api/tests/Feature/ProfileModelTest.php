<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_a_profile(): void
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

        $this->assertTrue($profile->is($user->profile));
        $this->assertTrue($user->is($profile->user));

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'username' => 'jeremy',
            'bio' => 'Développeur web.',
            'city' => 'Béziers',
        ]);
    }

    public function test_user_cannot_have_multiple_profiles(): void
    {
        $user = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => null,
            'password' => 'MotDePasseUltraSecure123',
        ]);

        Profile::create([
            'user_id' => $user->id,
            'username' => 'jeremy',
        ]);

        $this->expectException(QueryException::class);

        Profile::create([
            'user_id' => $user->id,
            'username' => 'jeremy-bis',
        ]);
    }

    public function test_username_must_be_unique(): void
    {
        $firstUser = User::create([
            'first_name' => 'Jeremy',
            'last_name' => 'Corbiere',
            'email' => 'jeremy@example.fr',
            'phone' => null,
            'password' => 'MotDePasseUltraSecure123',
        ]);

        $secondUser = User::create([
            'first_name' => 'Alice',
            'last_name' => 'Martin',
            'email' => 'alice@example.fr',
            'phone' => null,
            'password' => 'AutreMotDePasse123',
        ]);

        Profile::create([
            'user_id' => $firstUser->id,
            'username' => 'jeremy',
        ]);

        $this->expectException(QueryException::class);

        Profile::create([
            'user_id' => $secondUser->id,
            'username' => 'jeremy',
        ]);
    }
}