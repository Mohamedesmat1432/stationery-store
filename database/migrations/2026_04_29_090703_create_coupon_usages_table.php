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
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('discount_id')->constrained();
            $table->foreignUlid('user_id')->nullable()->constrained();
            $table->foreignUlid('order_id')->nullable()->constrained();
            $table->foreignUlid('cart_id')->nullable()->constrained();
            $table->decimal('amount_saved', 15, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['discount_id', 'user_id']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
