<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AppConfigController;
use App\Http\Controllers\API\V1\BookingController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\NotificationController;
use App\Http\Controllers\API\V1\OwnerDashboardController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\PartnerController;
use App\Http\Controllers\API\V1\PlanController;
use App\Http\Controllers\API\V1\ReelController;
use App\Http\Controllers\API\V1\SliderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/app-config', [AppConfigController::class, 'show']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/user/sync', [AuthController::class, 'syncUser']);
        Route::post('/partner/sync', [AuthController::class, 'syncPartner']);
        Route::post('/owner/login', [AuthController::class, 'ownerLogin']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/user/me', [AuthController::class, 'userProfile']);
            Route::put('/user/profile', [AuthController::class, 'updateUserProfile']);
            Route::delete('/user/account', [AuthController::class, 'deleteUserAccount']);
            Route::get('/partner/me', [AuthController::class, 'partnerProfile']);
            Route::put('/partner/profile', [AuthController::class, 'updatePartnerProfile']);
            Route::post('/owner/logout', [AuthController::class, 'ownerLogout']);
            Route::get('/owner/me', [AuthController::class, 'ownerProfile']);
            Route::put('/owner/profile', [AuthController::class, 'updateOwnerProfile']);
        });
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}/plans', [CategoryController::class, 'plans']);
    Route::get('/plans/{plan}', [PlanController::class, 'show']);
    Route::get('/reels', [ReelController::class, 'index']);
    Route::get('/reels/{reel}', [ReelController::class, 'show']);
    Route::get('/sliders', [SliderController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'show']);
        Route::get('/partners', [PartnerController::class, 'index']);

        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/assign-partner', [BookingController::class, 'assignPartner']);
        Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
        Route::post('/bookings/{booking}/upload-results', [BookingController::class, 'uploadResults']);

        Route::post('/payments/advance-intent', [PaymentController::class, 'advanceIntent']);
        Route::post('/payments/final-intent', [PaymentController::class, 'finalIntent']);
        Route::post('/payments/advance', [PaymentController::class, 'payAdvance']);
        Route::post('/payments/final', [PaymentController::class, 'payFinal']);
        Route::post('/payments/final/cash-collect', [PaymentController::class, 'collectFinalCash']);
        Route::get('/payments/{booking}', [PaymentController::class, 'show']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/register-device-token', [NotificationController::class, 'registerDeviceToken']);
    });
});
