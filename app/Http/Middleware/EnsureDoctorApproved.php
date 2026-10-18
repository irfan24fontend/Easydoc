<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Doctors who registered themselves can log in, but can only see the
 * "application status" page until the Super Admin approves them.
 */
class EnsureDoctorApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = $request->user()?->doctor;

        if (! $doctor || ! $doctor->isApproved()) {
            return redirect()->route('doctor.approval.status');
        }

        return $next($request);
    }
}
