<?php

use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
use App\Http\Controllers\Admin\CouponCampaignController as AdminCouponCampaignController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InviteController as AdminInviteController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');
Route::get('/restaurantes', [DiscoveryController::class, 'index'])->name('discover');
Route::get('/restaurantes/{restaurant:slug}', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/visitas/{visit}/avaliacao', [ReviewController::class, 'store'])->name('reviews.store');

    // Fora do grupo role:owner de proposito -- reivindicar um restaurante e' o unico jeito de
    // um consumidor comum virar gestor (ver ClaimController@approve), entao a propria tela de
    // reivindicacao nao pode exigir esse role de quem ainda nao o tem.
    Route::get('/gestor/reivindicar', [OwnerClaimController::class, 'create'])->name('owner.claims.create');
    Route::post('/gestor/reivindicar', [OwnerClaimController::class, 'store'])->name('owner.claims.store');
});

Route::middleware(['auth', 'role:owner'])->prefix('gestor')->name('owner.')->group(function () {
    Route::get('/', [OwnerDashboardController::class, 'index'])->name('dashboard');

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
    Route::patch('/campanhas/{couponCampaign}/aceitar', [OwnerCouponCampaignController::class, 'accept'])->name('coupon-campaigns.accept');
    Route::delete('/campanhas/{couponCampaign}/rejeitar', [OwnerCouponCampaignController::class, 'reject'])->name('coupon-campaigns.reject');

    Route::post('/avaliacoes/{review}/resposta', [OwnerReviewReplyController::class, 'store'])->name('reviews.reply');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::patch('/reivindicacoes/{claim}/aprovar', [AdminClaimController::class, 'approve'])->name('claims.approve');
    Route::patch('/reivindicacoes/{claim}/rejeitar', [AdminClaimController::class, 'reject'])->name('claims.reject');

    Route::patch('/usuarios/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');

    Route::post('/restaurantes', [AdminRestaurantController::class, 'store'])->name('restaurants.store');
    Route::patch('/restaurantes/{restaurant}', [AdminRestaurantController::class, 'update'])->name('restaurants.update');
    Route::patch('/restaurantes/{restaurant}/status', [AdminRestaurantController::class, 'toggleActive'])->name('restaurants.toggle-active');
    Route::post('/restaurantes/{restaurant}/convidar', [AdminInviteController::class, 'store'])->name('restaurants.invite');
    Route::delete('/restaurantes/{restaurant}/gestores/{user}', [AdminInviteController::class, 'destroy'])->name('restaurants.owners.destroy');
    Route::post('/restaurantes/{restaurant}/campanhas', [AdminCouponCampaignController::class, 'store'])->name('coupon-campaigns.suggest');
});

require __DIR__.'/auth.php';
