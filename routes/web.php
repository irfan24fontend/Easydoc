<?php

use App\Http\Controllers\Admin\ChamberController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MedicineController;
use App\Http\Controllers\Admin\PrescriptionTemplateController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Assistant\SerialController as AssistantSerialController;
use App\Http\Controllers\Doctor\ApprovalStatusController;
use App\Http\Controllers\Doctor\AssistantController;
use App\Http\Controllers\Doctor\ChamberController as DoctorChamberController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\PublicProfileController;
use App\Http\Controllers\Doctor\QueueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDoctorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

// Shareable public doctor profile (no login needed)
Route::get('/dr/{slug}', [PublicDoctorController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:120,1')
    ->name('public.doctor.show');

// Redirect users to their own dashboard after login.
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->homeRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// A stray GET /logout (old tab, bookmark, http->https redirect) must not show a 405 error page.
// Logging out itself stays POST-only (auth.php), so another site cannot log the user out.
Route::get('/logout', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRoute())
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Doctor registration review (approve / reject / private BMDC certificate)
        Route::patch('doctors/{doctor}/approve', [DoctorController::class, 'approve'])
            ->whereNumber('doctor')
            ->name('doctors.approve');
        Route::patch('doctors/{doctor}/reject', [DoctorController::class, 'reject'])
            ->whereNumber('doctor')
            ->name('doctors.reject');
        Route::get('doctors/{doctor}/certificate', [DoctorController::class, 'certificate'])
            ->whereNumber('doctor')
            ->name('doctors.certificate');

        Route::resource('doctors', DoctorController::class);
        Route::resource('templates', PrescriptionTemplateController::class);
        Route::resource('chambers', ChamberController::class);
        Route::resource('medicines', MedicineController::class);

        // Wallets
        Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
        Route::get('wallets/{wallet}/recharge', [WalletController::class, 'create'])->name('wallets.create');
        Route::post('wallets/{wallet}/recharge', [WalletController::class, 'store'])->name('wallets.store');

        // Recharge invoices
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])
            ->whereNumber('invoice')
            ->name('invoices.show');
    });

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
| Specific/static prescription routes are declared before routes containing
| {prescription}, so Laravel does not mistake a static path for a model ID.
*/

// Newly registered doctors can log in and see only this page until the Super Admin approves them.
Route::middleware(['auth', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('approval-status', [ApprovalStatusController::class, 'show'])->name('approval.status');
    });

Route::middleware(['auth', 'role:doctor', 'doctor.approved'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

        // Public profile (one per doctor): create + manage
        Route::get('public-profile/create', [PublicProfileController::class, 'create'])->name('public-profile.create');
        Route::post('public-profile', [PublicProfileController::class, 'store'])->name('public-profile.store');
        Route::get('public-profile', [PublicProfileController::class, 'edit'])->name('public-profile.edit');
        Route::put('public-profile', [PublicProfileController::class, 'update'])->name('public-profile.update');
        Route::patch('public-profile/publish', [PublicProfileController::class, 'toggle'])->name('public-profile.toggle');

        // Chambers (doctor shudhu nijer chamber dekhte pare; create/edit Super Admin kore)
        Route::get('chambers', [DoctorChamberController::class, 'index'])->name('chambers.index');
        Route::patch('chambers/{chamber}/toggle', [DoctorChamberController::class, 'toggle'])
            ->whereNumber('chamber')
            ->name('chambers.toggle');

        // Patients (static route-gulo {patient} route-er age)
        Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
        Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
        Route::get('patients/export', [PatientController::class, 'export'])->name('patients.export');
        Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
        Route::get('patients/{patient}', [PatientController::class, 'show'])
            ->whereNumber('patient')
            ->name('patients.show');
        Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])
            ->whereNumber('patient')
            ->name('patients.edit');
        Route::put('patients/{patient}', [PatientController::class, 'update'])
            ->whereNumber('patient')
            ->name('patients.update');

        // Queue
        Route::get('queue', [QueueController::class, 'index'])->name('queue.index');
        Route::patch('queue/{serial}/status', [QueueController::class, 'status'])
            ->whereNumber('serial')
            ->name('queue.status');
        Route::patch('queue/{serial}/start', [QueueController::class, 'start'])
            ->whereNumber('serial')
            ->name('queue.start');

        // Assistants
        Route::get('assistants', [AssistantController::class, 'index'])->name('assistants.index');
        Route::post('assistants', [AssistantController::class, 'store'])->name('assistants.store');
        Route::put('assistants/{assistant}', [AssistantController::class, 'update'])
            ->whereNumber('assistant')
            ->name('assistants.update');
        Route::delete('assistants/{assistant}', [AssistantController::class, 'destroy'])
            ->whereNumber('assistant')
            ->name('assistants.destroy');

        // Prescriptions: index/create/store
        Route::get('prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');

        // Prescription helper/AJAX endpoints (must be before {prescription} routes)
        Route::get('prescriptions/medicine-suggest', [PrescriptionController::class, 'suggestMedicine'])
            ->name('prescriptions.medicine-suggest');
        Route::get('prescriptions/lookup-patient', [PrescriptionController::class, 'lookupPatient'])
            ->name('prescriptions.lookup-patient');
        Route::get('prescriptions/top-medicines', [PrescriptionController::class, 'topMedicines'])
            ->name('prescriptions.top-medicines');
        Route::get('prescriptions/pattern-suggest', [PrescriptionController::class, 'patternSuggest'])
            ->name('prescriptions.pattern-suggest');

        // AI endpoints
        Route::post('prescriptions/ai/diagnosis', [PrescriptionController::class, 'aiDiagnosis'])
            ->name('prescriptions.ai-diagnosis');
        Route::post('prescriptions/ai/medicine', [PrescriptionController::class, 'aiMedicine'])
            ->name('prescriptions.ai-medicine');
        Route::post('prescriptions/check-interactions', [PrescriptionController::class, 'checkInteractions'])
            ->name('prescriptions.check-interactions');

        // Dynamic prescription routes: keep these after all static endpoints.
        Route::get('prescriptions/{prescription}', [PrescriptionController::class, 'show'])
            ->whereNumber('prescription')
            ->name('prescriptions.show');
        Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])
            ->whereNumber('prescription')
            ->name('prescriptions.edit');
        Route::put('prescriptions/{prescription}', [PrescriptionController::class, 'update'])
            ->whereNumber('prescription')
            ->name('prescriptions.update');
        Route::get('prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])
            ->whereNumber('prescription')
            ->name('prescriptions.print');
        Route::get('prescriptions/{prescription}/reprint', [PrescriptionController::class, 'reprint'])
            ->whereNumber('prescription')
            ->name('prescriptions.reprint');
    });

/*
|--------------------------------------------------------------------------
| Assistant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:assistant'])
    ->prefix('assistant')
    ->name('assistant.')
    ->group(function () {
        Route::get('serials', [AssistantSerialController::class, 'index'])->name('serials.index');
        Route::get('serials/lookup-patient', [AssistantSerialController::class, 'lookupPatient'])
            ->name('serials.lookup-patient');
        Route::post('serials', [AssistantSerialController::class, 'store'])->name('serials.store');
        Route::patch('serials/{serial}/status', [AssistantSerialController::class, 'status'])
            ->whereNumber('serial')
            ->name('serials.status');
    });