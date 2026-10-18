<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChamberRequest;
use App\Http\Requests\UpdateChamberRequest;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\PrescriptionTemplate;
use App\Services\ChamberService;

class ChamberController extends Controller
{
    public function __construct(
        protected ChamberService $service
    ) {}

    public function index()
    {
        $chambers = Chamber::with(['doctor.user', 'template'])
            ->latest()
            ->paginate(10);

        return view('admin.chambers.index', compact('chambers'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->get();
        $templates = PrescriptionTemplate::where('status', true)->get();

        return view('admin.chambers.create', compact('doctors', 'templates'));
    }

    public function store(StoreChamberRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.chambers.index')
            ->with('success', 'Chamber created successfully');
    }

    public function edit(Chamber $chamber)
    {
        $doctors = Doctor::with('user')->get();
        $templates = PrescriptionTemplate::where('status', true)->get();

        return view('admin.chambers.edit', compact('chamber', 'doctors', 'templates'));
    }

    public function update(UpdateChamberRequest $request, Chamber $chamber)
    {
        $this->service->update($chamber, $request->validated());

        return redirect()
            ->route('admin.chambers.index')
            ->with('success', 'Chamber updated successfully');
    }

    public function destroy(Chamber $chamber)
    {
        $chamber->delete();

        return back()->with('success', 'Chamber deleted successfully');
    }
}