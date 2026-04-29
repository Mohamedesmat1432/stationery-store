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
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('wishlist_id')->constrained();
            $table->foreignUlid('product_id')->constrained();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants');
            $table->text('notes')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['wishlist_id', 'product_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
