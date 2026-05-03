<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CustomerController;
use Modules\CRM\Http\Controllers\CustomerGroupController;

/*
|--------------------------------------------------------------------------
| CRM Module Routes
|--------------------------------------------------------------------------
|
| Routes for customer and customer group management.
| Prefix 'admin' and name 'admin.' are applied by CRMServiceProvider.
|
*/

// Customers
Route::post('customers/bulk-action', [CustomerController::class, 'bulkAction'])->name('customers.bulk-action');
Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore')->withTrashed();
Route::delete('customers/{customer}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.force-delete')->withTrashed();
Route::resource('customers', CustomerController::class);

// Customer Groups
Route::post('customer-groups/bulk-action', [CustomerGroupController::class, 'bulkAction'])->name('customer-groups.bulk-action');
Route::get('customer-groups/export', [CustomerGroupController::class, 'export'])->name('customer-groups.export');
Route::post('customer-groups/import', [CustomerGroupController::class, 'import'])->name('customer-groups.import');
Route::post('customer-groups/{customer_group}/restore', [CustomerGroupController::class, 'restore'])->name('customer-groups.restore')->withTrashed();
Route::delete('customer-groups/{customer_group}/force-delete', [CustomerGroupController::class, 'forceDelete'])->name('customer-groups.force-delete')->withTrashed();
Route::resource('customer-groups', CustomerGroupController::class);
