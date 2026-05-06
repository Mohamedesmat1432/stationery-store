<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\Storefront\CategoryController;

Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
