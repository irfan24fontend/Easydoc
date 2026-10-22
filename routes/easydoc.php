<?php

use App\Http\Controllers\PublicPrescriptionController;
use Illuminate\Support\Facades\Route;

// Public (login lage na) - rugi ei link e prescription dekhbe + JPG/PDF download korbe
Route::get('/p/{token}', [PublicPrescriptionController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:60,1')
    ->name('prescription.public');

// Remote image proxy (JPG/PDF download e CORS problem na hoy)
Route::get('/easydoc/img', [PublicPrescriptionController::class, 'image'])
    ->middleware('throttle:120,1')
    ->name('prescription.img');

// Doctor: share link toiri / bondho
Route::middleware('auth')->group(function () {
    Route::post('/easydoc/share/{id}', [PublicPrescriptionController::class, 'share'])
        ->name('prescription.share.create');
    Route::post('/easydoc/share/{id}/revoke', [PublicPrescriptionController::class, 'revoke'])
        ->name('prescription.share.revoke');
});
