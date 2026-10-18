<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDoctorProfileRequest;
use App\Models\DoctorProfile;
use App\Services\DoctorProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorProfileController extends Controller
{
    public function __construct(protected DoctorProfileService $service) {}

    /** Profile List */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $profiles = DoctorProfile::with('doctor.user')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->query('q'));
                $q->where(function ($w) use ($term) {
                    $w->where('slug', 'like', "%{$term}%")
                        ->orWhere('display_name', 'like', "%{$term}%")
                        ->orWhere('speciality', 'like', "%{$term}%")
                        ->orWhereHas('doctor', fn ($d) => $d->where('bmdc_number', 'like', "%{$term}%")
                            ->orWhere('doctor_code', 'like', "%{$term}%"))
                        ->orWhereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
                });
            })
            ->when(in_array($status, ['draft', 'in_review', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $counts = DoctorProfile::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.profiles.index', compact('profiles', 'counts'));
    }

    /** Profile Requests: review-er opekkhay thaka profile */
    public function requests()
    {
        $profiles = DoctorProfile::with('doctor.user')
            ->where('status', DoctorProfile::IN_REVIEW)
            ->orderBy('submitted_at')
            ->get();

        return view('admin.profiles.requests', compact('profiles'));
    }

    public function show(DoctorProfile $profile)
    {
        $profile->load('doctor.user', 'reviewer');
        $stats = $this->service->stats($profile);
        $completeness = $profile->completeness();

        return view('admin.profiles.show', compact('profile', 'stats', 'completeness'));
    }

    public function edit(DoctorProfile $profile)
    {
        $profile->load('doctor.user');

        return view('admin.profiles.edit', [
            'profile' => $profile,
            'doctor' => $profile->doctor,
            'c' => $profile->content(true),
            'suggestedSlug' => $profile->slug,
        ]);
    }

    public function update(SaveDoctorProfileRequest $request, DoctorProfile $profile)
    {
        $this->service->update($profile, $request->validated(), $request->file('photo'), true);

        return redirect()->route('admin.profiles.show', $profile)->with('success', 'প্রোফাইল আপডেট হয়েছে।');
    }

    public function approve(DoctorProfile $profile)
    {
        $blockers = $this->service->blockers($profile);
        if ($blockers) {
            return back()->with('error', 'অনুমোদনের আগে প্রোফাইলে এগুলো থাকতে হবে: ' . implode('، ', $blockers));
        }

        $this->service->approve($profile, auth()->user());

        return redirect()->route('admin.profiles.requests')
            ->with('success', ($profile->display_name ?: $profile->doctor->user->name) . '-এর প্রোফাইল অনুমোদিত হয়েছে, এখন লাইভ।');
    }

    public function reject(Request $request, DoctorProfile $profile)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']], [
            'reason.required' => 'বাতিলের কারণ লিখুন, যাতে ডাক্তার ঠিক করতে পারেন।',
            'reason.min' => 'কারণটি আরেকটু বিস্তারিত লিখুন।',
        ]);

        $this->service->reject($profile, auth()->user(), trim($data['reason']));

        return redirect()->route('admin.profiles.requests')->with('success', 'প্রোফাইল ফেরত পাঠানো হয়েছে।');
    }

    /** Live profile offline kore dei (link-e gele 404) */
    public function offline(Request $request, DoctorProfile $profile)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']], [
            'reason.required' => 'অফলাইন করার কারণ লিখুন।',
        ]);

        $this->service->reject($profile, auth()->user(), trim($data['reason']), true);

        return back()->with('success', 'প্রোফাইল অফলাইন করা হয়েছে।');
    }

    public function slugCheck(Request $request)
    {
        $slug = Str::slug((string) $request->query('slug'));
        $exceptId = $request->integer('except') ?: null;

        return response()->json([
            'slug' => $slug,
            'available' => strlen($slug) >= 3 && $this->service->slugAvailable($slug, $exceptId),
        ]);
    }
}