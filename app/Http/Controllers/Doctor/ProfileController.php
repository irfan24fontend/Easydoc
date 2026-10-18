<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDoctorProfileRequest;
use App\Models\Doctor;
use App\Models\DoctorProfile;
use App\Services\DoctorProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected DoctorProfileService $service) {}

    protected function doctor(): Doctor
    {
        $doctor = auth()->user()->doctor;
        abort_unless($doctor, 403);

        return $doctor;
    }

    /** Manage Profile (hub): status, link, share, visit statistics */
    public function index()
    {
        $doctor = $this->doctor();
        $profile = $doctor->profile;

        if (!$profile) {
            return view('doctor.profile.index', ['profile' => null, 'doctor' => $doctor]);
        }

        $stats = $this->service->stats($profile);
        $completeness = $profile->completeness();
        $blockers = $this->service->blockers($profile);

        return view('doctor.profile.index', compact('doctor', 'profile', 'stats', 'completeness', 'blockers'));
    }

    public function create()
    {
        $doctor = $this->doctor();

        if ($doctor->profile) {
            return redirect()->route('doctor.profile.edit');
        }

        return view('doctor.profile.create', [
            'doctor' => $doctor,
            'profile' => null,
            'c' => $this->defaults($doctor),
            'suggestedSlug' => $this->service->makeSlug($doctor),
        ]);
    }

    public function store(SaveDoctorProfileRequest $request)
    {
        $doctor = $this->doctor();

        if ($doctor->profile) {
            return redirect()->route('doctor.profile.edit');
        }

        $profile = $this->service->create($doctor, $request->validated(), $request->file('photo'));

        return $this->afterSave($request, $profile, 'প্রোফাইল তৈরি হয়েছে।');
    }

    public function edit()
    {
        $doctor = $this->doctor();
        $profile = $doctor->profile;

        if (!$profile) {
            return redirect()->route('doctor.profile.create');
        }

        return view('doctor.profile.edit', [
            'doctor' => $doctor,
            'profile' => $profile,
            'c' => $profile->content(true),
            'suggestedSlug' => $profile->slug,
        ]);
    }

    public function update(SaveDoctorProfileRequest $request)
    {
        $profile = $this->doctor()->profile;
        abort_unless($profile, 404);

        $wasLive = $profile->live_snapshot !== null;
        $profile = $this->service->update($profile, $request->validated(), $request->file('photo'));

        $msg = $wasLive
            ? 'পরিবর্তন সেভ হয়েছে এবং Super Admin-এর রিভিউয়ের জন্য পাঠানো হয়েছে। অনুমোদন না হওয়া পর্যন্ত আগের প্রোফাইলই লাইভ থাকবে।'
            : 'প্রোফাইল সেভ হয়েছে।';

        return $this->afterSave($request, $profile, $msg, $wasLive);
    }

    /** "Save & Submit for Review" button */
    protected function afterSave(Request $request, DoctorProfile $profile, string $message, bool $alreadySubmitted = false)
    {
        if ($alreadySubmitted) {
            return redirect()->route('doctor.profile.index')->with('success', $message);
        }

        if ($request->boolean('submit_review')) {
            $blockers = $this->service->blockers($profile);

            if ($blockers) {
                return redirect()->route('doctor.profile.edit')
                    ->with('error', 'প্রোফাইল সেভ হয়েছে, কিন্তু রিভিউয়ে পাঠানোর আগে এগুলো পূরণ করতে হবে: ' . implode('، ', $blockers));
            }

            $this->service->submit($profile);

            return redirect()->route('doctor.profile.index')
                ->with('success', 'প্রোফাইল রিভিউয়ের জন্য Super Admin-এর কাছে পাঠানো হয়েছে। অনুমোদন পেলে এটি লাইভ হবে।');
        }

        return redirect()->route('doctor.profile.index')->with('success', $message);
    }

    /** Index page theke "Submit for Review" */
    public function submit()
    {
        $profile = $this->doctor()->profile;
        abort_unless($profile, 404);

        if ($profile->status === DoctorProfile::IN_REVIEW) {
            return back()->with('error', 'প্রোফাইল ইতিমধ্যে রিভিউয়ে আছে।');
        }

        $blockers = $this->service->blockers($profile);
        if ($blockers) {
            return redirect()->route('doctor.profile.edit')
                ->with('error', 'রিভিউয়ে পাঠানোর আগে এগুলো পূরণ করুন: ' . implode('، ', $blockers));
        }

        $this->service->submit($profile);

        return back()->with('success', 'প্রোফাইল রিভিউয়ের জন্য পাঠানো হয়েছে।');
    }

    /** Live profile-ke nije lukano / abar dekhano */
    public function visibility()
    {
        $profile = $this->doctor()->profile;
        abort_unless($profile, 404);

        $profile->update(['is_visible' => !$profile->is_visible]);

        return back()->with('success', $profile->is_visible
            ? 'প্রোফাইল আবার সবার জন্য দৃশ্যমান।'
            : 'প্রোফাইল সাময়িকভাবে লুকানো হয়েছে। লিংকে গেলে কেউ দেখতে পাবে না।');
    }

    public function slugCheck(Request $request)
    {
        $slug = \Illuminate\Support\Str::slug((string) $request->query('slug'));
        $profile = $this->doctor()->profile;

        return response()->json([
            'slug' => $slug,
            'available' => strlen($slug) >= 3 && $this->service->slugAvailable($slug, $profile?->id),
        ]);
    }

    /** Create form-er prothom bar prefill (doctor-er ager tothyo theke) */
    protected function defaults(Doctor $doctor): array
    {
        $c = (new DoctorProfile())->content(true);

        $c['display_name'] = $doctor->user->name;
        $c['speciality'] = $doctor->speciality;
        $c['about'] = $doctor->about;
        $c['experience_years'] = is_numeric($doctor->experience) ? (int) $doctor->experience : null;
        $c['public_phone'] = $doctor->user->phone ?? null;
        $c['public_email'] = $doctor->user->email ?? null;

        if (filled($doctor->qualification)) {
            $c['qualifications'] = [['degree' => $doctor->qualification, 'institute' => '', 'year' => '']];
        }

        return $c;
    }
}