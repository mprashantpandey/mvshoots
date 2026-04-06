<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BookingResultController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('landing');
Route::redirect('/login', '/admin/login');
Route::get('/admin', function () {
    return Auth::guard('admin')->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
});

Route::middleware('guest:admin')->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::get('/admin/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('admin.password.request');
    Route::post('/admin/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('admin.password.email');
    Route::get('/admin/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/admin/reset-password', [PasswordResetController::class, 'reset'])->name('admin.password.update');
});

Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('plans', PlanController::class);
    Route::resource('reels', ReelController::class);
    Route::resource('sliders', SliderController::class)->except(['show']);
    Route::resource('partners', PartnerController::class);
    Route::resource('owners', OwnerController::class);
    Route::post('/partners/{partner}/status', [PartnerController::class, 'updateStatus'])->name('partners.update-status');
    Route::post('/owners/{owner}/status', [OwnerController::class, 'updateStatus'])->name('owners.update-status');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/assign-partner', [BookingController::class, 'assignPartner'])->name('bookings.assign-partner');
    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::get('/booking-results', [BookingResultController::class, 'index'])->name('booking-results.index');
    Route::get('/booking-results/{booking}', [BookingResultController::class, 'show'])->name('booking-results.show');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/{notification}/read-state', [NotificationController::class, 'updateReadState'])->name('notifications.update-read-state');
    Route::get('/reports', ReportController::class)->name('reports.index');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
