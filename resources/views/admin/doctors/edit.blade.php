@extends('layouts.admin')

@section('title', 'Edit Doctor')

@section('content')
<style>
    .form-workspace { 
        background-color: #f8fafc; 
        min-height: 100vh; 
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .modern-input, .modern-select {
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        padding: 0.625rem 0.875rem; 
        font-size: 0.875rem; 
        transition: all 0.2s ease;
        color: #334155;
    }
    .modern-input:focus, .modern-select:focus { 
        border-color: #0c8c84;
        box-shadow: 0 0 0 4px rgba(12, 140, 132, 0.1);
        outline: none; 
    }
    .form-label-custom { 
        font-size: 0.75rem; 
        font-weight: 700; 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        color: #475569; 
        margin-bottom: 0.5rem; 
    }
    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
</style>

<div class="form-workspace py-4">
    <div class="container-fluid px-4">
        
        <!-- Header Panel -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Edit Doctor</h1>
                <p class="text-muted small m-0">Modify professional and platform details for this practitioner.</p>
            </div>
            <a href="{{ route('admin.doctors.index') }}" class="btn btn-light border px-4 fw-medium text-secondary">
                Back to Roster
            </a>
        </div>

        <form method="POST" action="{{ route('admin.doctors.update', $doctor->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Main Form Inputs Panel -->
                <div class="col-lg-8">
                    <div class="glass-card p-4">
                        
                        <!-- Block 1: Account Context -->
                        <div class="section-title">Platform Access & Credentials</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Full Name</label>
                                <input type="text" name="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name', $doctor->user->name) }}">
                                @error('name') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Email Address</label>
                                <input type="email" name="email" class="form-control modern-input @error('email') is-invalid @enderror" value="{{ old('email', $doctor->user->email) }}">
                                @error('email') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Phone Number</label>
                                <input type="text" name="phone" class="form-control modern-input @error('phone') is-invalid @enderror" value="{{ old('phone', $doctor->user->phone) }}">
                                @error('phone') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Biological Gender</label>
                                <select name="gender" class="form-select modern-select @error('gender') is-invalid @enderror">
                                    <option value="Male" @if(old('gender', $doctor->gender) == 'Male') selected @endif>Male</option>
                                    <option value="Female" @if(old('gender', $doctor->gender) == 'Female') selected @endif>Female</option>
                                </select>
                                @error('gender') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Block 2: Medical Context -->
                        <div class="section-title">Professional Profiling</div>
                        <div class="row g-3">
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">BMDC Number</label>
                                <input type="text" name="bmdc_number" class="form-control modern-input @error('bmdc_number') is-invalid @enderror" value="{{ old('bmdc_number', $doctor->bmdc_number) }}">
                                @error('bmdc_number') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Speciality Focus</label>
                                <input type="text" name="speciality" class="form-control modern-input @error('speciality') is-invalid @enderror" value="{{ old('speciality', $doctor->speciality) }}">
                                @error('speciality') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Academic Qualification</label>
                                <input type="text" name="qualification" class="form-control modern-input @error('qualification') is-invalid @enderror" value="{{ old('qualification', $doctor->qualification) }}">
                                @error('qualification') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Designation</label>
                                <input type="text" name="designation" class="form-control modern-input @error('designation') is-invalid @enderror" value="{{ old('designation', $doctor->designation) }}">
                                @error('designation') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Years of Experience</label>
                                <input type="text" name="experience" class="form-control modern-input @error('experience') is-invalid @enderror" value="{{ old('experience', $doctor->experience) }}">
                                @error('experience') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Sidebar Sticky Controls / File Management -->
                <div class="col-lg-4">
                    <!-- Image Context Panel -->
                    <div class="glass-card p-4 text-center mb-4">
                        <label class="form-label-custom text-start d-block mb-3">Profile Photo Asset</label>
                        
                        <div class="mb-3 d-flex justify-content-center">
                            @if($doctor->photo)
                                <img src="{{ asset('storage/'.$doctor->photo) }}" class="rounded-circle border p-1 bg-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle border d-flex align-items-center justify-content-center text-muted fw-bold shadow-sm" style="width: 100px; height: 100px; font-size: 14px;">
                                    NO PHOTO
                                </div>
                            @endif
                        </div>

                        <div class="text-start">
                            <label class="form-label-custom">Replace Photo File</label>
                            <input type="file" name="photo" class="form-control modern-input @error('photo') is-invalid @enderror">
                            @error('photo') <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Actions Trigger Box -->
                    <div class="glass-card p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2.5 mb-2 fw-semibold shadow-sm" style="background-color: #0c8c84; border-color: #0c8c84;">
                            Save Changes
                        </button>
                        <a href="{{ route('admin.doctors.index') }}" class="btn btn-light border w-100 py-2.5 text-secondary fw-medium small">
                            Discard Edits
                        </a>
                    </div>
                </div>
            </div>

        </form>

        <div class="glass-card p-4 mt-4">
            <div class="mb-3">
                <h2 class="h5 fw-bold mb-1">Doctor specific charges</h2>
                <p class="text-muted small mb-0">Leave a field empty to use the platform default. Enter 0 to make that action free.</p>
            </div>
            <form method="POST" action="{{ route('admin.doctors.charges.update', $doctor) }}">
                @csrf
                @method('PATCH')
                @php
                    $chargeFields = [
                        'prescription_charge' => ['Prescription', 'default_prescription_charge'],
                        'investigation_advice_charge' => ['Investigation advice', 'investigation_advice_charge'],
                        'ai_diagnosis_charge' => ['AI diagnosis', 'ai_diagnosis_charge'],
                        'ai_medicine_charge' => ['AI medicine suggestion', 'ai_medicine_charge'],
                        'ai_interaction_charge' => ['AI interaction check', 'ai_interaction_charge'],
                        'chamber_request_charge' => ['Chamber request', 'chamber_request_charge'],
                    ];
                @endphp
                <div class="row g-3">
                    @foreach($chargeFields as $field => [$label, $configKey])
                        <div class="col-sm-6 col-lg-4">
                            <label class="form-label-custom" for="{{ $field }}">{{ $label }} (৳)</label>
                            <input id="{{ $field }}" type="number" step="0.01" min="0" name="{{ $field }}"
                                   class="form-control modern-input @error($field) is-invalid @enderror"
                                   value="{{ old($field, $doctor->{$field}) }}" placeholder="Default: ৳{{ number_format((float) config('easydoc.' . $configKey, 0), 2) }}">
                            @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Current rate: ৳{{ number_format($doctor->getCharge($field === 'prescription_charge' ? 'prescription' : str_replace('_charge', '', $field)), 2) }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> Prescription reprints are always free.</p>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">Save charge rates</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
