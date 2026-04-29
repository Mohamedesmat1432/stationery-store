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
        Schema::create('discountables', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('discount_id')->constrained();
            $table->morphs('discountable');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['discount_id', 'discountable_type', 'discountable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discountables');
    }
};
