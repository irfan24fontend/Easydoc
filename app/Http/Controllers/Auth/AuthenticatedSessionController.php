<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (!$user->isActive()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। যোগাযোগ করুন।',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route($user->homeRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}