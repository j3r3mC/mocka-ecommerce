<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'title' => 'Nouveau titre',
                'description' => 'Nouvelle description du produit.',
                'price_cents' => 15999,
                'condition' => ProductCondition::Good->value,
                'city' => 'Montpellier',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'product' => [
                    'id' => $product->id,
                    'title' => 'Nouveau titre',
                    'description' => 'Nouvelle description du produit.',
                    'price_cents' => 15999,
                    'condition' => ProductCondition::Good->value,
                    'status' => ProductStatus::Draft->value,
                    'city' => 'Montpellier',
                    'published_at' => null,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $owner->id,
            'title' => 'Nouveau titre',
            'description' => 'Nouvelle description du produit.',
            'price_cents' => 15999,
            'condition' => ProductCondition::Good->value,
            'status' => ProductStatus::Draft->value,
            'city' => 'Montpellier',
        ]);
    }

    public function test_product_can_be_partially_updated(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'price_cents' => 9999,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('product.title', 'Clavier mécanique')
            ->assertJsonPath('product.price_cents', 9999)
            ->assertJsonPath('product.city', 'Béziers');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Clavier mécanique',
            'price_cents' => 9999,
            'city' => 'Béziers',
        ]);
    }

    public function test_other_user_cannot_update_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $otherUser = $this->createUser('alice@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($otherUser, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'title' => 'Titre interdit',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $owner->id,
            'title' => 'Clavier mécanique',
        ]);
    }

    public function test_guest_cannot_update_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this->patchJson(
            "/api/products/{$product->id}",
            [
                'title' => 'Titre interdit',
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Clavier mécanique',
        ]);

        $this->assertGuest('web');
    }

    public function test_update_validates_product_data(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'title' => '',
                'description' => '',
                'price_cents' => 0,
                'condition' => 'invalid_condition',
                'city' => '',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'description',
                'price_cents',
                'condition',
                'city',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Clavier mécanique',
            'price_cents' => 12999,
        ]);
    }

    public function test_owner_cannot_change_protected_product_fields(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $otherUser = $this->createUser('alice@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'title' => 'Titre autorisé',
                'user_id' => $otherUser->id,
                'status' => ProductStatus::Published->value,
                'published_at' => now()->toDateTimeString(),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('product.title', 'Titre autorisé')
            ->assertJsonPath(
                'product.status',
                ProductStatus::Draft->value
            )
            ->assertJsonPath('product.published_at', null);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $owner->id,
            'title' => 'Titre autorisé',
            'status' => ProductStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function test_soft_deleted_product_cannot_be_updated(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $product->delete();

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson("/api/products/{$product->id}", [
                'title' => 'Nouveau titre',
            ]);

        $response->assertNotFound();

        $this->assertSoftDeleted($product);
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $owner = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($owner, 'web')
            ->patchJson('/api/products/999999', [
                'title' => 'Nouveau titre',
            ]);

        $response->assertNotFound();
    }

    private function createProduct(User $user): Product
    {
        return $user->products()->create([
            'title' => 'Clavier mécanique',
            'description' => 'Clavier mécanique en excellent état.',
            'price_cents' => 12999,
            'condition' => ProductCondition::LikeNew,
            'status' => ProductStatus::Draft,
            'city' => 'Béziers',
            'published_at' => null,
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
