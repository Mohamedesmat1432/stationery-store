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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number')->unique();
            $table->foreignUlid('user_id')->nullable()->constrained();
            $table->string('guest_email')->nullable();
            $table->foreignUlid('customer_id')->nullable()->constrained();
            $table->foreignUlid('currency_id')->constrained();
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->decimal('subtotal', 15, 4);
            $table->decimal('tax_total', 15, 4);
            $table->decimal('discount_total', 15, 4);
            $table->decimal('shipping_total', 15, 4);
            $table->decimal('grand_total', 15, 4);
            $table->string('status')->index();
            $table->string('payment_status')->default('pending');
            $table->string('fulfillment_status')->default('unfulfilled');
            $table->foreignUlid('discount_id')->nullable()->constrained();
            $table->string('coupon_code')->nullable();
            $table->foreignUlid('shipping_method_id')->nullable()->constrained();
            $table->foreignUlid('shipping_address_id')->constrained('addresses');
            $table->foreignUlid('billing_address_id')->constrained('addresses');
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('source')->default('web');
            $table->string('tracking_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['order_number']);
            $table->index(['payment_status']);
            $table->index(['fulfillment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
