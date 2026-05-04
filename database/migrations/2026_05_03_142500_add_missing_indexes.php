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
        // Customer groups: index for frequent lookups
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->index('slug');
            $table->index(['is_active', 'sort_order']);
        });

        // Customers: index for group filtering and search
        Schema::table('customers', function (Blueprint $table) {
            $table->index('customer_group_id');
            $table->index('gender');
        });

        // Users: indexes for search scopes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['name', 'email']);
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['is_active', 'sort_order']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customer_group_id']);
            $table->dropIndex(['gender']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name', 'email']);
            $table->dropIndex(['last_login_at']);
        });
    }
};
