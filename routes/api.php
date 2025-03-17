<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ContentController;
use App\Http\Controllers\API\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Subscription Routes
    Route::prefix('subscriptions')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::post('/upgrade', [SubscriptionController::class, 'upgrade']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::put('/update-payment', [SubscriptionController::class, 'updatePayment']);
    });

    // Content Routes
    Route::prefix('content')->group(function () {
        Route::get('/', [ContentController::class, 'index']);
        Route::get('/{content}', [ContentController::class, 'show']);
        Route::get('/recommendations', [ContentController::class, 'recommendations']);
    });

    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
