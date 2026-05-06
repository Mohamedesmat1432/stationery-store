<?php

/**
 * Created At: 2026-05-06T04:25:30Z
 */

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
        Schema::table('customers', function (Blueprint $table) {
            $table->index('total_spent');
            $table->index('orders_count');
            $table->index('created_at');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('is_featured');
            $table->index('created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['total_spent']);
            $table->dropIndex(['orders_count']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
