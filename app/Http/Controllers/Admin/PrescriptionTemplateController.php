<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionTemplateRequest;
use App\Http\Requests\UpdatePrescriptionTemplateRequest;
use App\Models\PrescriptionTemplate;
use App\Services\PrescriptionTemplateService;

class PrescriptionTemplateController extends Controller
{
    public function __construct(
        protected PrescriptionTemplateService $service
    ) {}

    public function index()
    {
        $templates = PrescriptionTemplate::latest()->paginate(10);

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(StorePrescriptionTemplateRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template Created Successfully');
    }

    public function edit(PrescriptionTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(
        UpdatePrescriptionTemplateRequest $request,
        PrescriptionTemplate $template
    ) {

        $this->service->update(
            $template,
            $request->validated()
        );

        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template Updated Successfully');
    }

    public function destroy(PrescriptionTemplate $template)
    {
        $template->delete();

        return back()->with(
            'success',
            'Template Deleted Successfully'
        );
    }
}