<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use App\Http\Controllers\Api\V1\Billing\PayPalController;
use App\Http\Controllers\Api\V1\Billing\PlanController;
use App\Http\Controllers\Api\V1\Billing\SubscriptionController;
use App\Http\Controllers\Api\V1\Vehicle\VehicleController;
use App\Http\Controllers\Api\V1\Vehicle\VehicleMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/meta', function () {
        return response()->json([
            'app' => config('app.name'),
            'tagline' => 'Marketplace automotriz C2C para Costa Rica',
            'currency' => 'USD',
            'country' => 'CR',
            'modules' => [
                'marketplace',
                'subscriptions',
                'chat',
                'saved-searches',
                'vehicle-comparison',
                'registry-checks',
                'credit-pre-approval',
                'trade-in',
            ],
        ]);
    });

    Route::get('/plans', [PlanController::class, 'index']);
    if (config('app.enable_payments')) {
        Route::post('/paypal/webhook', [PayPalController::class, 'webhook']);
    }

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,10');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::delete('/logout-all', [AuthController::class, 'logoutAll']);
            Route::get('/tokens', [TokenController::class, 'index']);
            Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy']);
        });
    });

    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{vehicle:slug}', [VehicleController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/portal/buyer', fn () => response()->json(['message' => 'Portal de comprador activo.']))->middleware('role:buyer');
        Route::get('/portal/seller', fn () => response()->json(['message' => 'Portal de vendedor activo.']))->middleware('role:seller,dealer,admin');
        Route::get('/portal/dealer', fn () => response()->json(['message' => 'Portal de agencia activo.']))->middleware('role:dealer,admin');
        Route::get('/portal/admin', fn () => response()->json(['message' => 'Portal de administracion activo.']))->middleware('role:admin');

        Route::prefix('my')->group(function (): void {
            Route::get('/subscription', [SubscriptionController::class, 'current'])->middleware('role:seller,dealer,admin');
            Route::post('/subscription', [SubscriptionController::class, 'store'])->middleware('role:seller,dealer,admin');
            if (config('app.enable_payments')) {
                Route::post('/subscription/paypal/create-order', [PayPalController::class, 'createOrder'])->middleware('role:seller,dealer,admin');
                Route::post('/subscription/paypal/capture-order', [PayPalController::class, 'captureOrder'])->middleware('role:seller,dealer,admin');
            }
        });

        Route::prefix('my')->middleware('role:seller,dealer,admin')->group(function (): void {
            Route::get('/publication-capabilities', [VehicleController::class, 'capabilities']);
            Route::get('/vehicles', [VehicleController::class, 'myIndex']);
            Route::post('/vehicles', [VehicleController::class, 'store']);
            Route::get('/vehicles/{vehicle}', [VehicleController::class, 'myShow']);
            Route::match(['put', 'patch'], '/vehicles/{vehicle}', [VehicleController::class, 'update']);
            Route::patch('/vehicles/{vehicle}/publish', [VehicleController::class, 'publish']);
            Route::patch('/vehicles/{vehicle}/pause', [VehicleController::class, 'pause']);
            Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
            Route::post('/vehicles/{vehicle}/media', [VehicleMediaController::class, 'store']);
            Route::patch('/vehicles/{vehicle}/media/reorder', [VehicleMediaController::class, 'reorder']);
            Route::patch('/vehicles/{vehicle}/media/{media}/primary', [VehicleMediaController::class, 'makePrimary']);
            Route::delete('/vehicles/{vehicle}/media/{media}', [VehicleMediaController::class, 'destroy']);
        });
    });
});

