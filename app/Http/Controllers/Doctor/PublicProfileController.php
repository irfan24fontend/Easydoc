<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\DoctorProfileRequest;
use App\Models\Doctor;
use App\Models\DoctorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    /** Create form. A doctor can only ever have one profile. */
    public function create(): View|RedirectResponse
    {
        $doctor = $this->doctor();

        if ($doctor->profile) {
            return redirect()
                ->route('doctor.public-profile.edit')
                ->with('success', 'আপনার Public Profile আগেই তৈরি করা আছে। এখান থেকে এডিট করুন।');
        }

        return view('doctor.public-profile.create', [
            'doctor'        => $doctor,
            'profile'       => null,
            'suggestedSlug' => $this->suggestSlug($doctor),
        ]);
    }

    public function store(DoctorProfileRequest $request): RedirectResponse
    {
        $doctor = $this->doctor();

        if ($doctor->profile) {
            return redirect()->route('doctor.public-profile.edit');
        }

        $this->savePhoto($request, $doctor);

        DoctorProfile::create(['doctor_id' => $doctor->id, 'slug' => $request->validated('slug')]
            + $this->payload($request));

        return redirect()
            ->route('doctor.public-profile.edit')
            ->with('success', 'Public Profile তৈরি হয়েছে। নিচের লিংকটি যে কাউকে শেয়ার করতে পারেন।');
    }

    /** Manage form. */
    public function edit(): View|RedirectResponse
    {
        $doctor = $this->doctor();

        if (! $doctor->profile) {
            return redirect()->route('doctor.public-profile.create');
        }

        return view('doctor.public-profile.edit', [
            'doctor'  => $doctor,
            'profile' => $doctor->profile,
        ]);
    }

    public function update(DoctorProfileRequest $request): RedirectResponse
    {
        $doctor = $this->doctor();

        if (! $doctor->profile) {
            return redirect()->route('doctor.public-profile.create');
        }

        $this->savePhoto($request, $doctor);

        $doctor->profile->update($this->payload($request));

        return redirect()
            ->route('doctor.public-profile.edit')
            ->with('success', 'Public Profile আপডেট হয়েছে।');
    }

    /** Publish / unpublish without touching the content. */
    public function toggle(): RedirectResponse
    {
        $profile = $this->doctor()->profile;

        if (! $profile) {
            return redirect()->route('doctor.public-profile.create');
        }

        $profile->update(['is_published' => ! $profile->is_published]);

        return back()->with(
            'success',
            $profile->is_published
                ? 'প্রোফাইল এখন সবার জন্য দৃশ্যমান।'
                : 'প্রোফাইল লুকানো হয়েছে। লিংকটি এখন কারও কাছে খুলবে না।'
        );
    }

    // ------------------------------------------------------------------

    protected function doctor(): Doctor
    {
        return auth()->user()->doctor()->with(['user', 'profile'])->firstOrFail();
    }

    /** Turn the validated request into columns (comma lists -> arrays). */
    protected function payload(DoctorProfileRequest $request): array
    {
        $v = $request->validated();

        $education = collect($v['education'])
            ->map(fn ($r) => [
                'degree'      => trim($r['degree']),
                'institution' => trim($r['institution']),
                'year'        => $r['year'] ?? null,
            ])->values()->all();

        $experience = collect($v['work_experience'] ?? [])
            ->map(fn ($r) => [
                'position'     => trim($r['position']),
                'organization' => trim($r['organization']),
                'from'         => $r['from'] ?? null,
                'to'           => $r['to'] ?? null,   // empty = still working
            ])->values()->all();

        return [
            'tagline'          => trim($v['tagline']),
            'about'            => trim($v['about']),
            'education'        => $education,
            'work_experience'  => $experience,
            'expertise'        => $this->splitList($v['expertise']),
            'languages'        => $this->splitList($v['languages'] ?? ''),
            'consultation_fee' => $v['consultation_fee'] ?? null,
            'website_url'      => $v['website_url'] ?? null,
            'facebook_url'     => $v['facebook_url'] ?? null,
            'show_contact'     => (bool) ($v['show_contact'] ?? false),
            'is_published'     => (bool) ($v['is_published'] ?? true),
        ];
    }

    protected function splitList(?string $value): array
    {
        return collect(preg_split('/[,\n،]+/u', (string) $value))
            ->map(fn ($i) => trim($i))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    protected function savePhoto(DoctorProfileRequest $request, Doctor $doctor): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        $new = $request->file('photo')->store('doctors', 'public');

        if ($doctor->photo) {
            Storage::disk('public')->delete($doctor->photo);
        }

        $doctor->update(['photo' => $new]);
    }

    protected function suggestSlug(Doctor $doctor): string
    {
        $base = Str::slug($doctor->user->name) ?: strtolower($doctor->doctor_code);
        $base = Str::limit('dr-' . preg_replace('/^dr-/', '', $base), 50, '');
        $slug = $base;
        $i = 2;

        while (DoctorProfile::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
