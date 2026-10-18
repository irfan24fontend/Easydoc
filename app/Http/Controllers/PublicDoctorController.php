<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use Illuminate\View\View;

class PublicDoctorController extends Controller
{
    public function show(string $slug): View
    {
        $profile = DoctorProfile::where('slug', $slug)
            ->with(['doctor.user', 'doctor.chambers' => fn ($q) => $q->operational()])
            ->firstOrFail();

        $doctor = $profile->doctor;
        $viewer = auth()->user();

        // Nobody else can see a hidden/unapproved profile; the owner and super admin can preview it.
        $isOwner = $viewer && $doctor && $viewer->id === $doctor->user_id;
        $isPreview = ! $profile->isLive();

        abort_if($isPreview && ! ($isOwner || ($viewer && $viewer->isSuperAdmin())), 404);

        return view('public.doctor', [
            'profile'   => $profile,
            'doctor'    => $doctor,
            'chambers'  => $doctor->chambers,
            'isPreview' => $isPreview,
        ]);
    }
}
