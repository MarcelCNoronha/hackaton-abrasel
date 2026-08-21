<?php

use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Owner\ClaimController as OwnerClaimController;
use App\Http\Controllers\Owner\CouponCampaignController as OwnerCouponCampaignController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MenuController as OwnerMenuController;
use App\Http\Controllers\Owner\QrCodeController as OwnerQrCodeController;
use App\Http\Controllers\Owner\RestaurantController as OwnerRestaurantController;
use App\Http\Controllers\Owner\ReviewReplyController as OwnerReviewReplyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\DiscoveryController;
use App\Http\Controllers\Public\RestaurantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::view('/', 'landing')->name('landing');
Route::get('/restaurantes', [DiscoveryController::class, 'index'])->name('discover');
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

    Route::get('/reivindicar', [OwnerClaimController::class, 'create'])->name('claims.create');
    Route::post('/reivindicar', [OwnerClaimController::class, 'store'])->name('claims.store');

    Route::get('/restaurantes/{restaurant}', [OwnerRestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::patch('/restaurantes/{restaurant}', [OwnerRestaurantController::class, 'update'])->name('restaurants.update');
    Route::put('/restaurantes/{restaurant}/horarios', [OwnerRestaurantController::class, 'updateHours'])->name('restaurants.hours.update');

    Route::post('/menus/{menu}/categorias', [OwnerMenuController::class, 'storeCategory'])->name('menu-categories.store');
    Route::patch('/menu-categorias/{menuCategory}', [OwnerMenuController::class, 'updateCategory'])->name('menu-categories.update');
    Route::delete('/menu-categorias/{menuCategory}', [OwnerMenuController::class, 'destroyCategory'])->name('menu-categories.destroy');
    Route::post('/menu-categorias/{menuCategory}/itens', [OwnerMenuController::class, 'storeItem'])->name('menu-items.store');
    Route::patch('/menu-itens/{menuItem}', [OwnerMenuController::class, 'updateItem'])->name('menu-items.update');
    Route::delete('/menu-itens/{menuItem}', [OwnerMenuController::class, 'destroyItem'])->name('menu-items.destroy');

    Route::post('/restaurantes/{restaurant}/qrcode', [OwnerQrCodeController::class, 'store'])->name('qrcode.store');

    Route::post('/restaurantes/{restaurant}/campanhas', [OwnerCouponCampaignController::class, 'store'])->name('coupon-campaigns.store');

    Route::post('/avaliacoes/{review}/resposta', [OwnerReviewReplyController::class, 'store'])->name('reviews.reply');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::patch('/reivindicacoes/{claim}/aprovar', [AdminClaimController::class, 'approve'])->name('claims.approve');
    Route::patch('/reivindicacoes/{claim}/rejeitar', [AdminClaimController::class, 'reject'])->name('claims.reject');

    Route::patch('/usuarios/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');

    Route::patch('/restaurantes/{restaurant}/status', [AdminRestaurantController::class, 'toggleActive'])->name('restaurants.toggle-active');
});

require __DIR__.'/auth.php';
