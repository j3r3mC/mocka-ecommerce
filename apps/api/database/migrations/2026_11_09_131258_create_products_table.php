<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title', 150);

            $table->text('description');

            $table->unsignedBigInteger('price_cents');

            $table->string('condition', 20);

            $table
                ->string('status', 20)
                ->default('draft');

            $table->string('city', 100);

            $table
                ->timestamp('published_at')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'published_at',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};