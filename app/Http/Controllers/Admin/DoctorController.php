<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{

    public function __construct(
        protected DoctorService $doctorService
    ) {
    }


    public function index()
    {
        $doctors = Doctor::with('user')
            ->latest()
            ->paginate(10);


        return view(
            'admin.doctors.index',
            compact('doctors')
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


}