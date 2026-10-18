<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{

    public function __construct(
        protected DoctorService $doctorService
    ) {
    }


    public function index(Request $request)
    {
        $status = $request->query('status');

        $doctors = Doctor::with('user')
            ->when(
                in_array($status, [Doctor::STATUS_PENDING, Doctor::STATUS_APPROVED, Doctor::STATUS_REJECTED], true),
                fn ($q) => $q->where('approval_status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'all'      => Doctor::count(),
            'pending'  => Doctor::where('approval_status', Doctor::STATUS_PENDING)->count(),
            'approved' => Doctor::where('approval_status', Doctor::STATUS_APPROVED)->count(),
            'rejected' => Doctor::where('approval_status', Doctor::STATUS_REJECTED)->count(),
        ];

        return view(
            'admin.doctors.index',
            compact('doctors', 'counts', 'status')
        );
    }



    public function create()
    {
        return view(
            'admin.doctors.create'
        );
    }



    public function store(StoreDoctorRequest $request)
    {

        $this->doctorService->create(
            $request->validated()
        );


        return redirect()
            ->route('admin.doctors.index')
            ->with(
                'success',
                'Doctor created successfully'
            );

    }



    public function show(Doctor $doctor)
    {

        $doctor->load('user');


        return view(
            'admin.doctors.show',
            compact('doctor')
        );

    }



    public function edit(Doctor $doctor)
    {

        $doctor->load('user');


        return view(
            'admin.doctors.edit',
            compact('doctor')
        );

    }




    public function update(
        UpdateDoctorRequest $request,
        Doctor $doctor
    )
    {

        $doctor->user->update([

            'name'=>$request->name,

            'email'=>$request->email,

            'phone'=>$request->phone,

        ]);



        $doctor->update([

            'bmdc_number'=>$request->bmdc_number,

            'speciality'=>$request->speciality,

            'qualification'=>$request->qualification,

            'designation'=>$request->designation,

            'experience'=>$request->experience,

            'gender'=>$request->gender,

        ]);



        return redirect()
            ->route('admin.doctors.index')
            ->with(
                'success',
                'Doctor updated successfully'
            );

    }




    public function destroy(Doctor $doctor)
    {

        $doctor->user()->delete();


        return redirect()
            ->route('admin.doctors.index')
            ->with(
                'success',
                'Doctor deleted successfully'
            );

    }


    // ------------------------------------------------------------------
    // Registration review
    // ------------------------------------------------------------------

    public function approve(Doctor $doctor)
    {
        $doctor->update([
            'approval_status'  => Doctor::STATUS_APPROVED,
            'approved_at'      => now(),
            'approved_by'      => auth()->id(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Doctor approved. এখন তিনি সিস্টেম ব্যবহার করতে পারবেন।');
    }

    public function reject(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $doctor->update([
            'approval_status'  => Doctor::STATUS_REJECTED,
            'approved_at'      => null,
            'approved_by'      => auth()->id(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Doctor-এর আবেদন বাতিল (reject) করা হয়েছে।');
    }

    /** BMDC certificate is stored privately; only the Super Admin can open it. */
    public function certificate(Doctor $doctor)
    {
        abort_unless(
            $doctor->bmdc_certificate && Storage::disk('local')->exists($doctor->bmdc_certificate),
            404
        );

        return Storage::disk('local')->response($doctor->bmdc_certificate);
    }

}
