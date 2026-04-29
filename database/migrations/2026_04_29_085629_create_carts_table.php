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
        Schema::create('carts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->nullable()->constrained();
            $table->string('session_id')->nullable()->index();
            $table->foreignUlid('currency_id')->constrained();
            $table->string('status')->default('active');
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('tax_total', 15, 4)->default(0);
            $table->decimal('discount_total', 15, 4)->default(0);
            $table->decimal('shipping_total', 15, 4)->default(0);
            $table->decimal('grand_total', 15, 4)->default(0);
            $table->foreignUlid('discount_id')->nullable()->constrained();
            $table->foreignUlid('shipping_address_id')->nullable()->constrained('addresses');
            $table->foreignUlid('billing_address_id')->nullable()->constrained('addresses');
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
