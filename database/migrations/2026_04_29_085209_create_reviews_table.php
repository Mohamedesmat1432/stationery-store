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
        Schema::create('reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained();
            $table->foreignUlid('order_id')->nullable()->constrained();
            $table->foreignUlid('user_id')->nullable()->constrained();
            $table->string('author_name');
            $table->string('author_email');
            $table->unsignedTinyInteger('rating');
            $table->text('title')->nullable();
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_verified_purchase')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_approved', 'created_at']);
            $table->index(['user_id']);
            $table->index(['rating', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
