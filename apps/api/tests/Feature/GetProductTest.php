<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_get_published_product(): void
    {
        $product = $this->createProduct(
            ProductStatus::Published,
            now()
        );

        $response = $this->getJson(
            "/api/products/{$product->id}"
        );

        $response
            ->assertOk()
            ->assertJson([
                'product' => [
                    'id' => $product->id,
                    'title' => 'Clavier mécanique',
                    'description' => 'Clavier mécanique en excellent état.',
                    'price_cents' => 12999,
                    'condition' => ProductCondition::LikeNew->value,
                    'status' => ProductStatus::Published->value,
                    'city' => 'Béziers',
                ],
            ]);
    }

    public function test_owner_can_get_own_draft_product(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $product = $this->createProduct(
            ProductStatus::Draft,
            null,
            $user
        );

        $response = $this
            ->actingAs($user, 'web')
            ->getJson("/api/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJsonPath('product.id', $product->id)
            ->assertJsonPath(
                'product.status',
                ProductStatus::Draft->value
            );
    }

    public function test_guest_cannot_get_draft_product(): void
    {
        $product = $this->createProduct(
            ProductStatus::Draft
        );

        $response = $this->getJson(
            "/api/products/{$product->id}"
        );

        $response->assertNotFound();
    }

    public function test_other_user_cannot_get_draft_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $otherUser = $this->createUser('alice@example.fr');

        $product = $this->createProduct(
            ProductStatus::Draft,
            null,
            $owner
        );

        $response = $this
            ->actingAs($otherUser, 'web')
            ->getJson("/api/products/{$product->id}");

        $response->assertNotFound();
    }

    public function test_sold_product_remains_publicly_visible(): void
    {
        $product = $this->createProduct(
            ProductStatus::Sold,
            now()
        );

        $response = $this->getJson(
            "/api/products/{$product->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.status',
                ProductStatus::Sold->value
            );
    }

    public function test_archived_product_is_visible_only_to_owner(): void
    {
        $owner = $this->createUser('jeremy@example.fr');

        $product = $this->createProduct(
            ProductStatus::Archived,
            now(),
            $owner
        );

        $this
            ->getJson("/api/products/{$product->id}")
            ->assertNotFound();

        $this
            ->actingAs($owner, 'web')
            ->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath(
                'product.status',
                ProductStatus::Archived->value
            );
    }

    public function test_soft_deleted_product_cannot_be_retrieved(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $product = $this->createProduct(
            ProductStatus::Published,
            now(),
            $user
        );

        $product->delete();

        $this
            ->actingAs($user, 'web')
            ->getJson("/api/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $this
            ->getJson('/api/products/999999')
            ->assertNotFound();
    }

    private function createProduct(
        ProductStatus $status,
        mixed $publishedAt = null,
        ?User $user = null
    ): Product {
        $user ??= $this->createUser(
            'seller-' . uniqid() . '@example.fr'
        );

        return $user->products()->create([
            'title' => 'Clavier mécanique',
            'description' => 'Clavier mécanique en excellent état.',
            'price_cents' => 12999,
            'condition' => ProductCondition::LikeNew,
            'status' => $status,
            'city' => 'Béziers',
            'published_at' => $publishedAt,
        ]);
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