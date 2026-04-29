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
        Schema::create('refunds', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained();
            $table->foreignUlid('payment_id')->constrained();
            $table->foreignUlid('order_item_id')->nullable()->constrained();
            $table->decimal('amount', 15, 4);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('status');
            $table->string('gateway_refund_id')->nullable();
            $table->foreignUlid('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
