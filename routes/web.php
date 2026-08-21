<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\DiscoveryController;
use App\Http\Controllers\Public\RestaurantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DiscoveryController::class, 'index'])->name('discover');
Route::get('/restaurantes/{restaurant:slug}', [RestaurantController::class, 'show'])->name('restaurants.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:owner'])->prefix('gestor')->name('owner.')->group(function () {
    Route::get('/', [OwnerDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
