<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssistantRequest;
use App\Http\Requests\UpdateAssistantRequest;
use App\Models\DoctorAssistant;
use App\Services\AssistantService;

class AssistantController extends Controller
{
    public function __construct(protected AssistantService $service) {}

    public function index()
    {
        $doctor = auth()->user()->doctor;
        $assistants = $doctor->assistants()->with('user')->latest()->get();

        return view('doctor.assistants.index', compact('assistants'));
    }

    public function store(StoreAssistantRequest $request)
    {
        $doctor = auth()->user()->doctor;

        $this->service->create($doctor, $request->validated());

        return redirect()->route('doctor.assistants.index')->with('success', 'সহকারী যোগ করা হয়েছে।');
    }

    public function update(UpdateAssistantRequest $request, DoctorAssistant $assistant)
    {
        abort_unless($assistant->doctor_id === auth()->user()->doctor->id, 403);

        $this->service->update($assistant, $request->validated());

        return redirect()->route('doctor.assistants.index')->with('success', 'সহকারীর তথ্য আপডেট হয়েছে।');
    }

    public function destroy(DoctorAssistant $assistant)
    {
        abort_unless($assistant->doctor_id === auth()->user()->doctor->id, 403);

        $this->service->delete($assistant);

        return redirect()->route('doctor.assistants.index')->with('success', 'সহকারী মুছে ফেলা হয়েছে।');
    }
}