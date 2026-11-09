<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_products(): void
    {
        $user = $this->createUser();

        $product = $user->products()->create([
            'title' => 'Clavier mécanique',
            'description' => 'Clavier mécanique en excellent état.',
            'price_cents' => 12999,
            'condition' => ProductCondition::LikeNew,
            'city' => 'Béziers',
        ]);

        $this->assertTrue($product->is($user->products->first()));
        $this->assertTrue($user->is($product->user));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $user->id,
            'title' => 'Clavier mécanique',
            'price_cents' => 12999,
        ]);
    }

    public function test_product_attributes_are_cast_correctly(): void
    {
        $user = $this->createUser();

        $product = $user->products()->create([
            'title' => 'Souris sans fil',
            'description' => 'Souris en bon état.',
            'price_cents' => 2999,
            'condition' => ProductCondition::Good,
            'status' => ProductStatus::Published,
            'city' => 'Béziers',
            'published_at' => '2026-10-08 10:00:00',
        ]);

        $product->refresh();

        $this->assertSame(2999, $product->price_cents);
        $this->assertSame(ProductCondition::Good, $product->condition);
        $this->assertSame(ProductStatus::Published, $product->status);
        $this->assertNotNull($product->published_at);
    }

    public function test_product_has_draft_status_by_default(): void
    {
        $user = $this->createUser();

        $product = $user->products()->create([
            'title' => 'Écran 27 pouces',
            'description' => 'Écran en très bon état.',
            'price_cents' => 18999,
            'condition' => ProductCondition::LikeNew,
            'city' => 'Béziers',
        ]);

        $product->refresh();

        $this->assertSame(ProductStatus::Draft, $product->status);
        $this->assertNull($product->published_at);
    }

    public function test_product_is_soft_deleted(): void
    {
        $user = $this->createUser();

        $product = $user->products()->create([
            'title' => 'Casque audio',
            'description' => 'Casque audio en bon état.',
            'price_cents' => 4999,
            'condition' => ProductCondition::Good,
            'city' => 'Béziers',
        ]);

        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertNull(Product::find($product->id));

        $deletedProduct = Product::withTrashed()->find($product->id);

        $this->assertNotNull($deletedProduct);
        $this->assertTrue($deletedProduct->trashed());
    }

    public function test_soft_deleted_product_can_be_restored(): void
    {
        $user = $this->createUser();

        $product = $user->products()->create([
            'title' => 'Webcam',
            'description' => 'Webcam Full HD.',
            'price_cents' => 3999,
            'condition' => ProductCondition::Good,
            'city' => 'Béziers',
        ]);

        $product->delete();
        $product->restore();

        $this->assertNotSoftDeleted($product);
        $this->assertNotNull(Product::find($product->id));
        $this->assertFalse($product->fresh()->trashed());
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