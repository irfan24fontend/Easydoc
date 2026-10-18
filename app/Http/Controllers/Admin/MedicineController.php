<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Services\MedicineService;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct(
        protected MedicineService $service
    ) {}

    public function index(Request $request)
    {
        $medicines = Medicine::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('brand_name', 'like', "%{$request->search}%")
                  ->orWhere('generic_name', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.medicines.index', compact('medicines'));
    }

    public function create()
    {
        return view('admin.medicines.create');
    }

    public function store(StoreMedicineRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine added successfully');
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', compact('medicine'));
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine)
    {
        $this->service->update($medicine, $request->validated());

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine updated successfully');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return back()->with('success', 'Medicine deleted successfully');
    }
}