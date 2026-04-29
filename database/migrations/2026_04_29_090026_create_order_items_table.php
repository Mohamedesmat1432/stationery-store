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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained();
            $table->foreignUlid('product_id')->constrained();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants');
            $table->foreignUlid('unit_id')->constrained();
            $table->foreignUlid('price_id')->constrained();
            $table->string('name');
            $table->string('sku');
            $table->string('image')->nullable();
            $table->decimal('weight', 10, 4)->default(0);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);
            $table->decimal('subtotal', 15, 4);
            $table->decimal('total', 15, 4);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
