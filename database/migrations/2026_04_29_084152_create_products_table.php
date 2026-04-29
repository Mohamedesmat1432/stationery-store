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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->foreignUlid('category_id')->constrained();
            $table->foreignUlid('brand_id')->nullable()->constrained();
            $table->foreignUlid('vendor_id')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_taxable')->default(true);
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable()->index();
            $table->string('mpn')->nullable();
            $table->string('gtin')->nullable();
            $table->decimal('weight', 10, 4)->nullable();
            $table->json('dimensions')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('inventory_policy')->default('deny');
            $table->unsignedInteger('view_count')->default(0);
            $table->decimal('avg_rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active', 'created_at']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['slug', 'is_active']);
            $table->fullText(['name', 'description', 'short_description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
