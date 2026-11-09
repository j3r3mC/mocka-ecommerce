<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($owner, 'web')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted($product);
        $this->assertNull(Product::find($product->id));

        $deletedProduct = Product::withTrashed()->find($product->id);

        $this->assertNotNull($deletedProduct);
        $this->assertTrue($deletedProduct->trashed());
    }

    public function test_other_user_cannot_delete_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $otherUser = $this->createUser('alice@example.fr');
        $product = $this->createProduct($owner);

        $response = $this
            ->actingAs($otherUser, 'web')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertForbidden();

        $this->assertNotSoftDeleted($product);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $owner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_guest_cannot_delete_product(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $response = $this->deleteJson(
            "/api/products/{$product->id}"
        );

        $response->assertUnauthorized();

        $this->assertNotSoftDeleted($product);
        $this->assertGuest('web');
    }

    public function test_soft_deleted_product_cannot_be_deleted_again(): void
    {
        $owner = $this->createUser('jeremy@example.fr');
        $product = $this->createProduct($owner);

        $product->delete();

        $response = $this
            ->actingAs($owner, 'web')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertNotFound();

        $this->assertSoftDeleted($product);
        $this->assertNotNull(
            Product::withTrashed()->find($product->id)
        );
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $owner = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($owner, 'web')
            ->deleteJson('/api/products/999999');

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
