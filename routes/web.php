<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
