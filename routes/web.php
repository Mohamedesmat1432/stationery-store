<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');

        // Access Control
        Route::post('roles/bulk-action', [RoleController::class, 'bulkDestroy'])->name('roles.bulk-action');
        Route::resource('roles', RoleController::class)->except(['show']);

        Route::post('users/bulk-action', [UserController::class, 'bulkDestroy'])->name('users.bulk-action');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->withTrashed();
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete')->withTrashed();
        Route::resource('users', UserController::class)->except(['show']);

        // CRM
        Route::post('customers/bulk-action', [CustomerController::class, 'bulkDestroy'])->name('customers.bulk-action');
        Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore')->withTrashed();
        Route::delete('customers/{customer}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.force-delete')->withTrashed();
        Route::resource('customers', CustomerController::class);

        Route::post('customer-groups/bulk-action', [CustomerGroupController::class, 'bulkDestroy'])->name('customer-groups.bulk-action');
        Route::post('customer-groups/{customer_group}/restore', [CustomerGroupController::class, 'restore'])->name('customer-groups.restore')->withTrashed();
        Route::delete('customer-groups/{customer_group}/force-delete', [CustomerGroupController::class, 'forceDelete'])->name('customer-groups.force-delete')->withTrashed();
        Route::resource('customer-groups', CustomerGroupController::class);
    });
});

require __DIR__.'/settings.php';
