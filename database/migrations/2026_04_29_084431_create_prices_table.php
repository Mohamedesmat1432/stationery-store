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
        Schema::create('prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->morphs('priceable');
            $table->decimal('amount', 15, 4);
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->foreignUlid('currency_id')->constrained();
            $table->string('type')->default('base'); // base, sale, wholesale, b2b
            $table->foreignUlid('customer_group_id')->nullable()->constrained();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['priceable_type', 'priceable_id', 'currency_id', 'type', 'start_at', 'end_at']);
            $table->index(['currency_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
