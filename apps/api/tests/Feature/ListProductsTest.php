<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_public_products(): void
    {
        $published = $this->createProduct(
            'Produit publié',
            ProductStatus::Published,
            now()->subHour()
        );

        $sold = $this->createProduct(
            'Produit vendu',
            ProductStatus::Sold,
            now()->subHours(2)
        );

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.1.id', $sold->id);
    }

    public function test_public_list_excludes_private_products(): void
    {
        $published = $this->createProduct(
            'Produit publié',
            ProductStatus::Published,
            now()
        );

        $draft = $this->createProduct(
            'Produit brouillon',
            ProductStatus::Draft
        );

        $archived = $this->createProduct(
            'Produit archivé',
            ProductStatus::Archived,
            now()->subDay()
        );

        $deleted = $this->createProduct(
            'Produit supprimé',
            ProductStatus::Published,
            now()->subDays(2)
        );

        $deleted->delete();

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonMissing([
                'id' => $draft->id,
            ])
            ->assertJsonMissing([
                'id' => $archived->id,
            ])
            ->assertJsonMissing([
                'id' => $deleted->id,
            ]);
    }

    public function test_public_products_are_sorted_by_publication_date(): void
    {
        $oldest = $this->createProduct(
            'Produit ancien',
            ProductStatus::Published,
            now()->subDays(3)
        );

        $newest = $this->createProduct(
            'Produit récent',
            ProductStatus::Published,
            now()
        );

        $middle = $this->createProduct(
            'Produit intermédiaire',
            ProductStatus::Sold,
            now()->subDay()
        );

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $middle->id)
            ->assertJsonPath('data.2.id', $oldest->id);
    }

    public function test_public_product_list_is_paginated(): void
    {
        for ($index = 1; $index <= 16; $index++) {
            $this->createProduct(
                "Produit {$index}",
                ProductStatus::Published,
                now()->subMinutes($index)
            );
        }

        $firstPage = $this->getJson('/api/products');
        $secondPage = $this->getJson('/api/products?page=2');

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

    public function test_public_product_list_can_be_empty(): void
    {
        $this->createProduct(
            'Produit brouillon',
            ProductStatus::Draft
        );

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    private function createProduct(
        string $title,
        ProductStatus $status,
        mixed $publishedAt = null
    ): Product {
        $user = $this->createUser(
            'seller-' . uniqid() . '@example.fr'
        );

        return $user->products()->create([
            'title' => $title,
            'description' => 'Description du produit.',
            'price_cents' => 4999,
            'condition' => ProductCondition::Good,
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