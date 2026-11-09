<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMyProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_own_products(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $draft = $this->createProduct(
            $user,
            'Produit brouillon',
            ProductStatus::Draft,
            now()->subHours(4)
        );

        $published = $this->createProduct(
            $user,
            'Produit publié',
            ProductStatus::Published,
            now()->subHours(3)
        );

        $sold = $this->createProduct(
            $user,
            'Produit vendu',
            ProductStatus::Sold,
            now()->subHours(2)
        );

        $archived = $this->createProduct(
            $user,
            'Produit archivé',
            ProductStatus::Archived,
            now()->subHour()
        );

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products');

        $response
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.id', $archived->id)
            ->assertJsonPath('data.1.id', $sold->id)
            ->assertJsonPath('data.2.id', $published->id)
            ->assertJsonPath('data.3.id', $draft->id);
    }

    public function test_personal_list_excludes_other_users_products(): void
    {
        $user = $this->createUser('jeremy@example.fr');
        $otherUser = $this->createUser('alice@example.fr');

        $ownProduct = $this->createProduct(
            $user,
            'Mon produit',
            ProductStatus::Draft
        );

        $otherProduct = $this->createProduct(
            $otherUser,
            'Produit d’un autre utilisateur',
            ProductStatus::Published
        );

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownProduct->id)
            ->assertJsonMissing([
                'id' => $otherProduct->id,
            ]);
    }

    public function test_personal_list_excludes_soft_deleted_products(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $activeProduct = $this->createProduct(
            $user,
            'Produit actif',
            ProductStatus::Draft
        );

        $deletedProduct = $this->createProduct(
            $user,
            'Produit supprimé',
            ProductStatus::Published
        );

        $deletedProduct->delete();

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeProduct->id)
            ->assertJsonMissing([
                'id' => $deletedProduct->id,
            ]);
    }

    public function test_personal_product_list_is_paginated(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        for ($index = 1; $index <= 16; $index++) {
            $this->createProduct(
                $user,
                "Produit {$index}",
                ProductStatus::Draft,
                now()->subMinutes($index)
            );
        }

        $firstPage = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products');

        $secondPage = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products?page=2');

        $firstPage
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.last_page', 2);

        $secondPage
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_authenticated_user_can_have_empty_product_list(): void
    {
        $user = $this->createUser('jeremy@example.fr');

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/my/products');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_guest_cannot_list_personal_products(): void
    {
        $response = $this->getJson('/api/my/products');

        $response->assertUnauthorized();
        $this->assertGuest('web');
    }

    private function createProduct(
        User $user,
        string $title,
        ProductStatus $status,
        mixed $createdAt = null
    ): Product {
        $product = $user->products()->create([
            'title' => $title,
            'description' => 'Description du produit.',
            'price_cents' => 4999,
            'condition' => ProductCondition::Good,
            'status' => $status,
            'city' => 'Béziers',
            'published_at' => $status === ProductStatus::Published
                ? now()
                : null,
        ]);

        if ($createdAt !== null) {
            $product->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->saveQuietly();
        }

        return $product->refresh();
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
