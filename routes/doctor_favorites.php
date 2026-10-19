<?php

use App\Http\Controllers\Doctor\FavoriteMedicineController;
use Illuminate\Support\Facades\Route;

/*
| Favorite medicines + favorite brands (doctor panel).
| Loaded from routes/web.php with:  require __DIR__.'/doctor_favorites.php';
*/
Route::middleware(['auth', 'role:doctor', 'doctor.approved'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('favorites', [FavoriteMedicineController::class, 'index'])->name('favorites.index');

        Route::get('favorites/medicine-search', [FavoriteMedicineController::class, 'searchMedicines'])
            ->name('favorites.medicine-search');
        Route::get('favorites/company-search', [FavoriteMedicineController::class, 'searchCompanies'])
            ->name('favorites.company-search');

        Route::post('favorites/medicines', [FavoriteMedicineController::class, 'storeMedicine'])
            ->name('favorites.medicines.store');
        Route::put('favorites/medicines/{doctorMedicine}', [FavoriteMedicineController::class, 'updateMedicine'])
            ->whereNumber('doctorMedicine')
            ->name('favorites.medicines.update');
        Route::delete('favorites/medicines/{doctorMedicine}', [FavoriteMedicineController::class, 'destroyMedicine'])
            ->whereNumber('doctorMedicine')
            ->name('favorites.medicines.destroy');

        Route::post('favorites/companies', [FavoriteMedicineController::class, 'storeCompany'])
            ->name('favorites.companies.store');
        Route::delete('favorites/companies/{favoriteCompany}', [FavoriteMedicineController::class, 'destroyCompany'])
            ->whereNumber('favoriteCompany')
            ->name('favorites.companies.destroy');
    });
