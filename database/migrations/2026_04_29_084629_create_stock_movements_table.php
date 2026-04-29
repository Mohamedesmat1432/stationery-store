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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants');
            $table->foreignUlid('warehouse_id')->constrained();
            $table->foreignUlid('unit_id')->constrained();
            $table->decimal('quantity', 15, 4);
            $table->decimal('before_quantity', 15, 4);
            $table->decimal('after_quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->string('type'); // purchase, sale, adjustment, transfer_in, transfer_out, return
            $table->string('lot_number')->nullable();
            $table->text('notes')->nullable();
            $table->nullableMorphs('reference');
            $table->foreignUlid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'warehouse_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
