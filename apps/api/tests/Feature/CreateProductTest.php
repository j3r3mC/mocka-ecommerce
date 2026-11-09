<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_product(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Clavier mécanique',
                'description' => 'Clavier mécanique en excellent état.',
                'price_cents' => 12999,
                'condition' => ProductCondition::LikeNew->value,
                'city' => 'Béziers',
            ]);

        $response
            ->assertCreated()
            ->assertJson([
                'product' => [
                    'title' => 'Clavier mécanique',
                    'description' => 'Clavier mécanique en excellent état.',
                    'price_cents' => 12999,
                    'condition' => ProductCondition::LikeNew->value,
                    'status' => ProductStatus::Draft->value,
                    'city' => 'Béziers',
                    'published_at' => null,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'title' => 'Clavier mécanique',
            'description' => 'Clavier mécanique en excellent état.',
            'price_cents' => 12999,
            'condition' => ProductCondition::LikeNew->value,
            'status' => ProductStatus::Draft->value,
            'city' => 'Béziers',
            'published_at' => null,
        ]);
    }

    public function test_product_is_created_as_draft(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Souris sans fil',
                'description' => 'Souris sans fil en bon état.',
                'price_cents' => 2999,
                'condition' => ProductCondition::Good->value,
                'city' => 'Béziers',
                'status' => ProductStatus::Published->value,
                'published_at' => now()->toDateTimeString(),
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'product.status',
                ProductStatus::Draft->value
            )
            ->assertJsonPath('product.published_at', null);

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'title' => 'Souris sans fil',
            'status' => ProductStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function test_title_is_required(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'description' => 'Produit en excellent état.',
                'price_cents' => 12999,
                'condition' => ProductCondition::LikeNew->value,
                'city' => 'Béziers',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_description_is_required(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Clavier mécanique',
                'price_cents' => 12999,
                'condition' => ProductCondition::LikeNew->value,
                'city' => 'Béziers',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_price_must_be_positive(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Clavier mécanique',
                'description' => 'Clavier mécanique en excellent état.',
                'price_cents' => 0,
                'condition' => ProductCondition::LikeNew->value,
                'city' => 'Béziers',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price_cents']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_condition_must_be_valid(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Clavier mécanique',
                'description' => 'Clavier mécanique en excellent état.',
                'price_cents' => 12999,
                'condition' => 'invalid_condition',
                'city' => 'Béziers',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['condition']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_city_is_required(): void
    {
        $user = $this->createUser();

        $response = $this
            ->actingAs($user, 'web')
            ->postJson('/api/products', [
                'title' => 'Clavier mécanique',
                'description' => 'Clavier mécanique en excellent état.',
                'price_cents' => 12999,
                'condition' => ProductCondition::LikeNew->value,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_guest_cannot_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Clavier mécanique',
            'description' => 'Clavier mécanique en excellent état.',
            'price_cents' => 12999,
            'condition' => ProductCondition::LikeNew->value,
            'city' => 'Béziers',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('products', 0);
        $this->assertGuest('web');
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