<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\RechargeRequestController;
use App\Http\Controllers\Doctor\NotificationController;
use App\Http\Controllers\Doctor\WalletController as DoctorWalletController;
use Illuminate\Support\Facades\Route;

/*
| Doctor wallet recharge (manual bKash), invoices, wallet log,
| notifications + news, and the Super Admin side of them.
| Loaded from routes/web.php with:  require __DIR__.'/wallet_notifications.php';
*/

// ---------------- Super Admin ----------------
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('recharge-requests', [RechargeRequestController::class, 'index'])
            ->name('recharge-requests.index');
        Route::patch('recharge-requests/{rechargeRequest}/approve', [RechargeRequestController::class, 'approve'])
            ->whereNumber('rechargeRequest')
            ->name('recharge-requests.approve');
        Route::patch('recharge-requests/{rechargeRequest}/reject', [RechargeRequestController::class, 'reject'])
            ->whereNumber('rechargeRequest')
            ->name('recharge-requests.reject');

        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->whereNumber('announcement')
            ->name('announcements.destroy');
    });

// ---------------- Doctor ----------------
Route::middleware(['auth', 'role:doctor', 'doctor.approved'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('wallet/recharge', [DoctorWalletController::class, 'recharge'])->name('wallet.recharge');
        Route::post('wallet/recharge', [DoctorWalletController::class, 'storeRecharge'])->name('wallet.recharge.store');
        Route::get('wallet/invoices', [DoctorWalletController::class, 'invoices'])->name('wallet.invoices');
        Route::get('wallet/invoices/{invoice}', [DoctorWalletController::class, 'invoice'])
            ->whereNumber('invoice')
            ->name('wallet.invoices.show');
        Route::get('wallet/log', [DoctorWalletController::class, 'log'])->name('wallet.log');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/poll', [NotificationController::class, 'poll'])
            ->middleware('throttle:90,1')
            ->name('notifications.poll');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/{notification}/open', [NotificationController::class, 'open'])
            ->whereNumber('notification')
            ->name('notifications.open');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])
            ->whereNumber('notification')
            ->name('notifications.read');

        Route::get('news', [NotificationController::class, 'news'])->name('news.index');
    });
