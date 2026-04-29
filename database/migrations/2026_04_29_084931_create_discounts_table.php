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
        Schema::create('discounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('type'); // percentage, fixed_amount, buy_x_get_y, free_shipping
            $table->decimal('value', 15, 4);
            $table->string('applies_to')->default('all');
            $table->decimal('min_purchase_amount', 15, 4)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->boolean('free_shipping')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_auto_apply')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['is_active', 'start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
