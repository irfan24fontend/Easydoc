<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DoctorRegistrationRequest;
use App\Services\DoctorRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected DoctorRegistrationService $registration
    ) {
    }

    /**
     * Display the doctor registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming doctor registration request.
     * The account is created as "pending" and needs Super Admin approval.
     */
    public function store(DoctorRegistrationRequest $request): RedirectResponse
    {
        $user = $this->registration->register($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('doctor.approval.status');
    }
}
