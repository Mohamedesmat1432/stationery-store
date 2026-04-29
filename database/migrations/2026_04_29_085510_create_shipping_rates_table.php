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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('zone_id')->constrained('shipping_zones');
            $table->foreignUlid('method_id')->constrained('shipping_methods');
            $table->decimal('min_weight', 10, 4)->nullable();
            $table->decimal('max_weight', 10, 4)->nullable();
            $table->decimal('min_order_amount', 15, 4)->nullable();
            $table->decimal('max_order_amount', 15, 4)->nullable();
            $table->decimal('price', 15, 4);
            $table->boolean('is_free_shipping')->default(false);
            $table->decimal('free_shipping_threshold', 15, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['zone_id', 'method_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
