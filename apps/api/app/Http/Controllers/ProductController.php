<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $request
            ->user()
            ->products()
            ->create($request->validated());

        $product->refresh();

        return response()->json([
            'product' => new ProductResource($product),
        ], 201);
    }

    public function show(
        Request $request,
        Product $product
    ): JsonResponse {
        $isPublic = in_array(
            $product->status,
            [
                ProductStatus::Published,
                ProductStatus::Sold,
            ],
            true
        );

        $isOwner = $request->user()?->id === $product->user_id;

        abort_unless($isPublic || $isOwner, 404);

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }

    public function myProducts(
        Request $request
    ): AnonymousResourceCollection {
        $products = $request
            ->user()
            ->products()
            ->latest()
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->whereIn('status', [
                ProductStatus::Published->value,
                ProductStatus::Sold->value,
            ])
            ->orderByDesc('published_at')
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return response()->json([
            'product' => new ProductResource($product->refresh()),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return response()->json(null, 204);
    }

    
}