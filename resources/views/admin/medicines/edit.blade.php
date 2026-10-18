@extends('layouts.admin')

@section('title', 'Edit Medicine')

@section('content')

<style>
    .page-head h1 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.85rem; margin: 0.2rem 0 0; }

    .btn-ghost { background: #fff; border: 1px solid #e2e8f0; color: #475569 !important; border-radius: 10px; font-weight: 600; font-size: 0.85rem; padding: 0.6rem 1.1rem; }
    .btn-ghost:hover { background: #f8fafc; }
    .btn-brand { background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff !important; border: none; border-radius: 10px; font-weight: 700; font-size: 0.875rem; padding: 0.7rem 1.6rem; box-shadow: 0 4px 10px rgba(79,70,229,.25); }
    .btn-brand:hover { filter: brightness(1.05); color: #fff; }

    .section-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); margin-bottom: 1.25rem; overflow: hidden; }
    .section-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
    .section-head i { width: 32px; height: 32px; border-radius: 9px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
    .section-head.sponsor-head i { background: #fef3c7; color: #b45309; }
    .section-head h6 { margin: 0; font-weight: 800; color: #0f172a; font-size: 0.9rem; }
    .section-head span { display: block; font-size: 0.75rem; color: #94a3b8; }
    .section-body { padding: 1.4rem; }

    .field-label { font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem; display: block; }
    .modern-input, .modern-select { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.65rem 0.9rem; font-size: 0.875rem; color: #1e293b; width: 100%; background: #fff; }
    .modern-input:focus, .modern-select:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79,70,229,.1); outline: none; }

    .toggle-row { display: flex; align-items: center; justify-content: space-between; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 1rem 1.2rem; margin-bottom: 1rem; }
    .toggle-row strong { font-size: 0.85rem; color: #0f172a; }
    .toggle-row span { font-size: 0.75rem; color: #92400e; }
    .switch { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background: #e2e8f0; border-radius: 999px; transition: .2s; }
    .slider::before { content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
    .switch input:checked + .slider { background: #d97706; }
    .switch input:checked + .slider::before { transform: translateX(20px); }

    #sponsorFields { display: none; }

    .sticky-footer { position: sticky; bottom: 0; background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1rem 1.4rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 12px rgba(0,0,0,.04); }
</style>

<div class="container" style="max-width: 880px;">

    <div class="d-flex justify-content-between align-items-center page-head mb-4">
        <div>
            <span class="text-uppercase fw-bold" style="font-size:0.7rem; color:#4f46e5; letter-spacing:.06em;">Medicine Database</span>
            <h1>মেডিসিন এডিট করুন</h1>
            <p>{{ $medicine->brand_name }} এর তথ্য হালনাগাদ করুন।</p>
        </div>
        <a href="{{ route('admin.medicines.index') }}" class="btn-ghost"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius:10px;">
            <strong><i class="fa-solid fa-triangle-exclamation me-1"></i> নিচের সমস্যাগুলো ঠিক করুন:</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.medicines.update', $medicine->id) }}">
        @csrf
        @method('PUT')

        <div class="section-card">
            <div class="section-head">
                <i class="fa-solid fa-pills"></i>
                <div>
                    <h6>মেডিসিনের তথ্য</h6>
                    <span>Brand ও Generic নাম সঠিকভাবে দিন — suggestion এখান থেকেই কাজ করবে</span>
                </div>
            </div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="field-label">Brand Name</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $medicine->brand_name) }}" class="modern-input @error('brand_name') is-invalid @enderror">
                        @error('brand_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Generic Name</label>
                        <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}" class="modern-input @error('generic_name') is-invalid @enderror">
                        @error('generic_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Strength</label>
                        <input type="text" name="strength" value="{{ old('strength', $medicine->strength) }}" class="modern-input @error('strength') is-invalid @enderror">
                        @error('strength') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Company</label>
                        <input type="text" name="company" value="{{ old('company', $medicine->company) }}" class="modern-input @error('company') is-invalid @enderror">
                        @error('company') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Dosage Form</label>
                        <select name="dosage_form" class="modern-select @error('dosage_form') is-invalid @enderror">
                            @foreach(['Tablet','Capsule','Syrup','Injection','Drop','Cream','Inhaler','Other'] as $form)
                                <option value="{{ $form }}" {{ old('dosage_form', $medicine->dosage_form) == $form ? 'selected' : '' }}>{{ $form }}</option>
                            @endforeach
                        </select>
                        @error('dosage_form') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Category</label>
                        <input type="text" name="category" value="{{ old('category', $medicine->category) }}" class="modern-input @error('category') is-invalid @enderror">
                        @error('category') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Shortcut (ঐচ্ছিক)</label>
                        <input type="text" name="shortcut" value="{{ old('shortcut', $medicine->shortcut) }}" class="modern-input @error('shortcut') is-invalid @enderror">
                        @error('shortcut') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-head sponsor-head">
                <i class="fa-solid fa-star"></i>
                <div>
                    <h6>Sponsorship Settings</h6>
                    <span>এই ব্র্যান্ডটা sponsor করা হলে, একই generic এর অন্য যেকোনো সার্চেই এটা ⭐ সহ উপরে দেখাবে</span>
                </div>
            </div>
            <div class="section-body">
                <div class="toggle-row">
                    <div>
                        <strong>এই মেডিসিনটি Sponsored হিসেবে মার্ক করুন</strong><br>
                        <span>যেমন: Square কোম্পানি sponsor করলে তাদের Maxpro, সব Esomeprazole সার্চে (Seclo, Nexcap ইত্যাদি) উপরে দেখাবে</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="sponsorToggle" name="is_sponsored" value="1" {{ old('is_sponsored', $medicine->is_sponsored) ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div id="sponsorFields" class="row g-3">
                    <div class="col-md-6">
                        <label class="field-label">Sponsor Company</label>
                        <input type="text" name="sponsor_company" value="{{ old('sponsor_company', $medicine->sponsor_company) }}" class="modern-input" placeholder="Square Pharmaceuticals">
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Priority</label>
                        <input type="number" name="sponsor_priority" value="{{ old('sponsor_priority', $medicine->sponsor_priority) }}" class="modern-input" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-body">
                <label class="field-label">Status</label>
                <select name="status" class="modern-select" style="max-width: 220px;">
                    <option value="1" {{ old('status', (int) $medicine->status) === 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', (int) $medicine->status) === 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="sticky-footer">
            <a href="{{ route('admin.medicines.index') }}" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-brand"><i class="fa-solid fa-floppy-disk me-1"></i> Update Medicine</button>
        </div>
    </form>

</div>

<script>
    const sponsorToggle = document.getElementById('sponsorToggle');
    const sponsorFields = document.getElementById('sponsorFields');

    function toggleSponsorFields() {
        sponsorFields.style.display = sponsorToggle.checked ? 'flex' : 'none';
    }

    sponsorToggle.addEventListener('change', toggleSponsorFields);
    toggleSponsorFields();
</script>
@endsection