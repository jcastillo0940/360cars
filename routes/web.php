<?php

use App\Http\Controllers\Web\AdminPortalController;
use App\Http\Controllers\Web\Auth\WebAuthController;
use App\Http\Controllers\Web\BuyerEngagementController;
use App\Http\Controllers\Web\BuyerPortalController;
use App\Http\Controllers\Web\PublicCatalogController;
use App\Http\Controllers\Web\SellerBillingController;
use App\Http\Controllers\Web\SellerOnboardingController;
use App\Http\Controllers\Web\SellerPortalController;
use App\Http\Controllers\Web\VehicleValuationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Web\HomeController::class, 'index'])->name('home');
Route::get('/inventario', [PublicCatalogController::class, 'index'])->name('catalog.index');
Route::get('/vehiculos/{vehicle:slug}', [PublicCatalogController::class, 'show'])->name('catalog.show');
Route::get('/tasador', [VehicleValuationController::class, 'index'])->name('valuation.index');
Route::post('/tasador', [VehicleValuationController::class, 'store'])->name('valuation.store');
Route::get('/tasador/evaluaciones/{token}', [VehicleValuationController::class, 'show'])->name('valuation.show');
Route::get('/vende-tu-auto', [SellerOnboardingController::class, 'create'])->name('seller.onboarding.create');
Route::post('/vende-tu-auto', [SellerOnboardingController::class, 'store'])->name('seller.onboarding.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebAuthController::class, 'create'])->name('login');
    Route::post('/login', [WebAuthController::class, 'store'])->name('login.store');
    Route::get('/register', [WebAuthController::class, 'register'])->name('register');
    Route::post('/register', [WebAuthController::class, 'registerStore'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [WebAuthController::class, 'destroy'])->name('logout');

    Route::middleware('role:buyer')->group(function (): void {
        Route::get('/buyer', [BuyerPortalController::class, 'index'])->name('buyer.dashboard');
        Route::post('/buyer/favorites/{vehicle}', [BuyerEngagementController::class, 'favorite'])->name('buyer.favorites.store');
        Route::delete('/buyer/favorites/{vehicle}', [BuyerEngagementController::class, 'unfavorite'])->name('buyer.favorites.destroy');
        Route::post('/buyer/comparisons/{vehicle}', [BuyerEngagementController::class, 'addToComparison'])->name('buyer.comparisons.store');
        Route::delete('/buyer/comparisons/{vehicle}', [BuyerEngagementController::class, 'removeFromComparison'])->name('buyer.comparisons.destroy');
        Route::post('/buyer/saved-searches', [BuyerEngagementController::class, 'saveSearch'])->name('buyer.saved-searches.store');
        Route::delete('/buyer/saved-searches/{savedSearch}', [BuyerEngagementController::class, 'destroySavedSearch'])->name('buyer.saved-searches.destroy');
        Route::post('/buyer/conversations/{vehicle}', [BuyerEngagementController::class, 'contactSeller'])->name('buyer.conversations.store');
    });

    Route::middleware('role:seller,dealer,admin')->group(function (): void {
        Route::get('/seller', [SellerPortalController::class, 'index'])->name('seller.dashboard');
        Route::post('/seller/vehicles', [SellerPortalController::class, 'store'])->name('seller.vehicles.store');
        Route::put('/seller/vehicles/{vehicle}', [SellerPortalController::class, 'update'])->name('seller.vehicles.update');
        Route::patch('/seller/vehicles/{vehicle}/publish', [SellerPortalController::class, 'publish'])->name('seller.vehicles.publish');
        Route::patch('/seller/vehicles/{vehicle}/pause', [SellerPortalController::class, 'pause'])->name('seller.vehicles.pause');
        Route::patch('/seller/vehicles/{vehicle}/refresh-basic', [SellerPortalController::class, 'refreshBasic'])->name('seller.vehicles.refresh-basic');
        Route::delete('/seller/vehicles/{vehicle}', [SellerPortalController::class, 'destroy'])->name('seller.vehicles.destroy');
        Route::post('/seller/vehicles/{vehicle}/media', [SellerPortalController::class, 'uploadMedia'])->name('seller.vehicles.media.store');
        Route::patch('/seller/vehicles/{vehicle}/media/{media}/primary', [SellerPortalController::class, 'makePrimary'])->name('seller.vehicles.media.primary');
        Route::delete('/seller/vehicles/{vehicle}/media/{media}', [SellerPortalController::class, 'destroyMedia'])->name('seller.vehicles.media.destroy');

        Route::post('/seller/billing/subscribe', [SellerBillingController::class, 'subscribeSandbox'])->name('seller.billing.subscribe');
        Route::post('/seller/billing/paypal/create-order', [SellerBillingController::class, 'createPayPalOrder'])->name('seller.billing.paypal.create-order');
        Route::get('/seller/billing/paypal/return', [SellerBillingController::class, 'capturePayPalReturn'])->name('seller.billing.paypal.return');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', [AdminPortalController::class, 'index'])->name('admin.dashboard');
        Route::post('/admin/exchange-rate/refresh', [AdminPortalController::class, 'refreshExchangeRate'])->name('admin.exchange-rate.refresh');
        Route::post('/admin/valuation-ai', [VehicleValuationController::class, 'updateAiSetting'])->name('admin.valuation-ai.update');
        Route::post('/admin/public-theme', [AdminPortalController::class, 'updatePublicTheme'])->name('admin.public-theme.update');
        Route::post('/admin/feature-options', [AdminPortalController::class, 'storeFeatureOption'])->name('admin.feature-options.store');
        Route::patch('/admin/feature-options/{featureOption}/toggle', [AdminPortalController::class, 'toggleFeatureOption'])->name('admin.feature-options.toggle');
    });
});
