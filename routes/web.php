<?php

use App\Http\Controllers\Web\AdminPortalController;
use App\Http\Controllers\Web\Auth\WebAuthController;
use App\Http\Controllers\Web\BuyerEngagementController;
use App\Http\Controllers\Web\BuyerPortalController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\PublicCatalogController;
use App\Http\Controllers\Web\SellerBillingController;
use App\Http\Controllers\Web\SellerOnboardingController;
use App\Http\Controllers\Web\SellerPortalController;
use App\Http\Controllers\Web\VehicleValuationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Web\HomeController::class, 'index'])->name('home');
Route::get('/inventario', [PublicCatalogController::class, 'index'])->name('catalog.index');
Route::get('/marcas', [App\Http\Controllers\Web\HomeController::class, 'brands'])->name('brands.index');
Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{newsPost:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/vehiculos/{vehicle:slug}', [PublicCatalogController::class, 'show'])->name('catalog.show');
Route::get('/tasador', [VehicleValuationController::class, 'index'])->name('valuation.index');
Route::post('/tasador', [VehicleValuationController::class, 'store'])->name('valuation.store');
Route::get('/tasador/evaluaciones/{token}', [VehicleValuationController::class, 'show'])->name('valuation.show');
Route::get('/vende-tu-auto', [SellerOnboardingController::class, 'create'])->name('seller.onboarding.create');
Route::post('/vende-tu-auto', [SellerOnboardingController::class, 'store'])->name('seller.onboarding.store');

Route::view('/legal/terminos', 'legal.page', [
    'pageTitle' => 'Terminos de servicio',
    'pageDescription' => 'Condiciones generales para publicar, vender y explorar vehiculos dentro de Movikaa en Costa Rica.',
])->name('legal.terms');
Route::view('/legal/privacidad', 'legal.page', [
    'pageTitle' => 'Politica de privacidad',
    'pageDescription' => 'Resumen de tratamiento de datos, autenticacion, contacto y trazabilidad comercial del marketplace.',
])->name('legal.privacy');
Route::view('/legal/cookies', 'legal.page', [
    'pageTitle' => 'Politica de cookies',
    'pageDescription' => 'Uso de cookies funcionales, de sesion y de rendimiento para mejorar la experiencia del usuario.',
])->name('legal.cookies');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebAuthController::class, 'create'])->name('login');
    Route::post('/login', [WebAuthController::class, 'store'])->name('login.store');
    Route::get('/register', [WebAuthController::class, 'register'])->name('register');
    Route::post('/register', [WebAuthController::class, 'registerStore'])->name('register.store');
    Route::get('/forgot-password', [WebAuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [WebAuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [WebAuthController::class, 'destroy'])->name('logout');

    Route::get('/buyer', [BuyerPortalController::class, 'index'])->name('buyer.dashboard');
    Route::get('/buyer/favorites', [BuyerPortalController::class, 'favorites'])->name('buyer.favorites.index');
    Route::get('/buyer/comparisons', [BuyerPortalController::class, 'comparisons'])->name('buyer.comparisons.index');
    Route::get('/buyer/searches', [BuyerPortalController::class, 'searches'])->name('buyer.searches.index');
    Route::get('/buyer/messages', [BuyerPortalController::class, 'messages'])->name('buyer.messages.index');
    Route::post('/buyer/favorites/{vehicle}', [BuyerEngagementController::class, 'favorite'])->name('buyer.favorites.store');
    Route::delete('/buyer/favorites/{vehicle}', [BuyerEngagementController::class, 'unfavorite'])->name('buyer.favorites.destroy');
    Route::post('/buyer/comparisons/{vehicle}', [BuyerEngagementController::class, 'addToComparison'])->name('buyer.comparisons.store');
    Route::delete('/buyer/comparisons/{vehicle}', [BuyerEngagementController::class, 'removeFromComparison'])->name('buyer.comparisons.destroy');
    Route::post('/buyer/saved-searches', [BuyerEngagementController::class, 'saveSearch'])->name('buyer.saved-searches.store');
    Route::delete('/buyer/saved-searches/{savedSearch}', [BuyerEngagementController::class, 'destroySavedSearch'])->name('buyer.saved-searches.destroy');
    Route::post('/buyer/conversations/{vehicle}', [BuyerEngagementController::class, 'contactSeller'])->name('buyer.conversations.store');

    Route::middleware('role:buyer,seller,dealer,admin')->group(function (): void {
        Route::get('/seller', [SellerPortalController::class, 'index'])->name('seller.dashboard');
        Route::get('/seller/listings', [SellerPortalController::class, 'listings'])->name('seller.listings');
        Route::get('/seller/create', [SellerPortalController::class, 'createPage'])->name('seller.create');
        Route::get('/seller/vehicles/{vehicle}/edit', [SellerPortalController::class, 'editPage'])->name('seller.vehicles.edit');
        Route::get('/seller/media', [SellerPortalController::class, 'mediaPage'])->name('seller.media');
        Route::get('/seller/billing', [SellerPortalController::class, 'billingPage'])->name('seller.billing');
        Route::get('/seller/messages', [SellerPortalController::class, 'messagesPage'])->name('seller.messages');
        Route::post('/seller/vehicles', [SellerPortalController::class, 'store'])->name('seller.vehicles.store');
        Route::put('/seller/vehicles/{vehicle}', [SellerPortalController::class, 'update'])->name('seller.vehicles.update');
        Route::patch('/seller/vehicles/{vehicle}/publish', [SellerPortalController::class, 'publish'])->name('seller.vehicles.publish');
        Route::patch('/seller/vehicles/{vehicle}/pause', [SellerPortalController::class, 'pause'])->name('seller.vehicles.pause');
        Route::patch('/seller/vehicles/{vehicle}/refresh-basic', [SellerPortalController::class, 'refreshBasic'])->name('seller.vehicles.refresh-basic');
        Route::delete('/seller/vehicles/{vehicle}', [SellerPortalController::class, 'destroy'])->name('seller.vehicles.destroy');
        Route::post('/seller/vehicles/{vehicle}/media', [SellerPortalController::class, 'uploadMedia'])->name('seller.vehicles.media.store');
        Route::post('/seller/vehicles/{vehicle}/media/{media}/replace', [SellerPortalController::class, 'replaceMedia'])->name('seller.vehicles.media.replace');
        Route::patch('/seller/vehicles/{vehicle}/media/{media}/primary', [SellerPortalController::class, 'makePrimary'])->name('seller.vehicles.media.primary');
        Route::delete('/seller/vehicles/{vehicle}/media/{media}', [SellerPortalController::class, 'destroyMedia'])->name('seller.vehicles.media.destroy');

        Route::post('/seller/billing/subscribe', [SellerBillingController::class, 'subscribeSandbox'])->name('seller.billing.subscribe');
        Route::post('/seller/billing/free', [SellerBillingController::class, 'activateFree'])->name('seller.billing.free');
        Route::post('/seller/billing/request-payment', [SellerBillingController::class, 'requestManualPayment'])->name('seller.billing.request-payment');
        Route::post('/seller/billing/paypal/create-order', [SellerBillingController::class, 'createPayPalOrder'])->name('seller.billing.paypal.create-order');
        Route::get('/seller/billing/paypal/return', [SellerBillingController::class, 'capturePayPalReturn'])->name('seller.billing.paypal.return');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', [AdminPortalController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/catalog', [AdminPortalController::class, 'catalog'])->name('admin.catalog');
        Route::get('/admin/payments', [AdminPortalController::class, 'payments'])->name('admin.payments');
        Route::get('/admin/users', [AdminPortalController::class, 'users'])->name('admin.users');
        Route::get('/admin/settings', [AdminPortalController::class, 'settings'])->name('admin.settings');
        Route::get('/admin/features', [AdminPortalController::class, 'features'])->name('admin.features');
        Route::get('/admin/plans', [AdminPortalController::class, 'plans'])->name('admin.plans');
        Route::get('/admin/news', [AdminPortalController::class, 'news'])->name('admin.news');
        Route::get('/admin/news/create', [AdminPortalController::class, 'createNews'])->name('admin.news.create');
        Route::post('/admin/news', [AdminPortalController::class, 'storeNews'])->name('admin.news.store');
        Route::get('/admin/news/{newsPost}/edit', [AdminPortalController::class, 'editNews'])->name('admin.news.edit');
        Route::put('/admin/news/{newsPost}', [AdminPortalController::class, 'updateNews'])->name('admin.news.update');
        Route::delete('/admin/news/{newsPost}', [AdminPortalController::class, 'destroyNews'])->name('admin.news.destroy');
        Route::post('/admin/exchange-rate/refresh', [AdminPortalController::class, 'refreshExchangeRate'])->name('admin.exchange-rate.refresh');
        Route::post('/admin/valuation-ai', [VehicleValuationController::class, 'updateAiSetting'])->name('admin.valuation-ai.update');
        Route::post('/admin/public-theme', [AdminPortalController::class, 'updatePublicTheme'])->name('admin.public-theme.update');
        Route::post('/admin/payment-methods', [AdminPortalController::class, 'updatePaymentMethods'])->name('admin.payment-methods.update');
        Route::post('/admin/integrations', [AdminPortalController::class, 'updateIntegrations'])->name('admin.integrations.update');
        Route::patch('/admin/payments/{transaction}/approve', [AdminPortalController::class, 'approvePayment'])->name('admin.payments.approve');
        Route::patch('/admin/payments/{transaction}/reject', [AdminPortalController::class, 'rejectPayment'])->name('admin.payments.reject');
        Route::post('/admin/catalog/entries', [AdminPortalController::class, 'storeCatalogEntry'])->name('admin.catalog.entries.store');
        Route::post('/admin/catalog/makes', [AdminPortalController::class, 'storeCatalogMake'])->name('admin.catalog.makes.store');
        Route::patch('/admin/catalog/makes/{vehicleMake}/toggle', [AdminPortalController::class, 'toggleCatalogMake'])->name('admin.catalog.makes.toggle');
        Route::post('/admin/catalog/models', [AdminPortalController::class, 'storeCatalogModel'])->name('admin.catalog.models.store');
        Route::patch('/admin/catalog/models/{vehicleModel}/toggle', [AdminPortalController::class, 'toggleCatalogModel'])->name('admin.catalog.models.toggle');
        Route::post('/admin/feature-options', [AdminPortalController::class, 'storeFeatureOption'])->name('admin.feature-options.store');
        Route::put('/admin/feature-options/{featureOption}', [AdminPortalController::class, 'updateFeatureOption'])->name('admin.feature-options.update');
        Route::patch('/admin/feature-options/{featureOption}/toggle', [AdminPortalController::class, 'toggleFeatureOption'])->name('admin.feature-options.toggle');
        Route::delete('/admin/feature-options/{featureOption}', [AdminPortalController::class, 'destroyFeatureOption'])->name('admin.feature-options.destroy');
    });
});


