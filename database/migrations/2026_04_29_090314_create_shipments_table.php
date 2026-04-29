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
        Schema::create('shipments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained();
            $table->foreignUlid('shipping_method_id')->constrained();
            $table->foreignUlid('shipping_rate_id')->nullable()->constrained('shipping_rates');
            $table->string('status');
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->string('label_url')->nullable();
            $table->decimal('weight', 10, 4)->nullable();
            $table->json('dimensions')->nullable();
            $table->decimal('shipping_cost', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id']);
            $table->index(['tracking_number']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
