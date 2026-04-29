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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained();
            $table->foreignUlid('method_id')->constrained('payment_methods');
            $table->decimal('amount', 15, 4);
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->string('status');
            $table->string('transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_response')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignUlid('refunded_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index(['transaction_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
