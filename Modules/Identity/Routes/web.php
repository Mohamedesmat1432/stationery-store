<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Identity Module Routes
|--------------------------------------------------------------------------
|
| These routes handle the Access Control (Users & Roles) management.
| They are loaded by the IdentityServiceProvider within the
| 'auth' and 'verified' middleware group.
|
*/

// Roles
Route::post('roles/bulk-action', [RoleController::class, 'bulkDestroy'])->name('roles.bulk-action');
Route::resource('roles', RoleController::class)->except(['show']);

// Users
Route::post('users/bulk-action', [UserController::class, 'bulkDestroy'])->name('users.bulk-action');
Route::get('users/export', [UserController::class, 'export'])->name('users.export');
Route::post('users/import', [UserController::class, 'import'])->name('users.import');
Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->withTrashed();
Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete')->withTrashed();
Route::resource('users', UserController::class)->except(['show']);
