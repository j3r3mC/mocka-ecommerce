<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('users', function (Blueprint $table): void {

        $table->dropColumn('name');

        $table->string('first_name', 100);

        $table->string('last_name', 100);

        $table->string('phone', 20)
            ->nullable();

    });
}

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {

            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
            ]);

            $table->string('name');

        });
    }
};
