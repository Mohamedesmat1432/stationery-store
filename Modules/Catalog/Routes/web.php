<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\Admin\BrandController;
use Modules\Catalog\Http\Controllers\Admin\CategoryController;
use Modules\Catalog\Http\Controllers\Admin\ProductController;

// Category Routes
Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');
Route::post('categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action');
Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore')->withTrashed();
Route::delete('categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete')->withTrashed();
Route::resource('categories', CategoryController::class);

// Product Routes
Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::post('products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore')->withTrashed();
Route::delete('products/{product}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete')->withTrashed();
Route::resource('products', ProductController::class);

// Brand Routes
Route::get('brands/export', [BrandController::class, 'export'])->name('brands.export');
Route::post('brands/import', [BrandController::class, 'import'])->name('brands.import');
Route::post('brands/bulk-action', [BrandController::class, 'bulkAction'])->name('brands.bulk-action');
Route::post('brands/{brand}/restore', [BrandController::class, 'restore'])->name('brands.restore')->withTrashed();
Route::delete('brands/{brand}/force-delete', [BrandController::class, 'forceDelete'])->name('brands.force-delete')->withTrashed();
Route::resource('brands', BrandController::class);
