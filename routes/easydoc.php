<?php

use App\Http\Controllers\PublicPrescriptionController;
use Illuminate\Support\Facades\Route;

// Public (login lage na)
Route::get('/p/{token}', [PublicPrescriptionController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:60,1')
    ->name('prescription.public');

// Doctor: link bondho/revoke
Route::post('/easydoc/share/{id}/revoke', [PublicPrescriptionController::class, 'revoke'])
    ->middleware('auth')
    ->name('prescription.share.revoke');
