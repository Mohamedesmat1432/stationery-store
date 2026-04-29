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
        Schema::create('stocks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants');
            $table->foreignUlid('warehouse_id')->constrained();
            $table->foreignUlid('unit_id')->constrained();
            $table->decimal('available_quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0);
            $table->decimal('reorder_level', 15, 4)->default(0);
            $table->decimal('reorder_quantity', 15, 4)->default(0);
            $table->decimal('avg_cost', 15, 4)->default(0);
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'variant_id', 'warehouse_id', 'unit_id']);
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['warehouse_id', 'available_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
