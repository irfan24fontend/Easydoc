@extends('layouts.admin')

@section('title', 'Edit Chamber')

@section('content')
<style>
    .form-workspace { background-color: #f8fafc; min-height: 100vh; }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .modern-input, .modern-select, .modern-textarea {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: #1e293b;
        width: 100%;
    }
    .modern-input:focus, .modern-select:focus, .modern-textarea:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    .form-label-custom {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: block;
    }
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white !important;
        border: none;
        border-radius: 10px;
        font-weight: 600;
    }
</style>

<div class="form-workspace py-5">
    <div class="container max-w-[1000px]">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <span class="badge mb-2" style="font-size: 0.65rem; background-color: #e0e7ff; color: #4338ca;">CHAMBER SETUP</span>
                <h1 class="h2 fw-bold text-slate-900 m-0">Edit Chamber</h1>
            </div>
            <a href="{{ route('admin.chambers.index') }}" class="btn btn-light border rounded-pill px-4 text-secondary small fw-medium">
                ← Back
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.chambers.update', $chamber->id) }}">
            @csrf
            @method('PUT')

            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4">Chamber Information</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Doctor</label>
                        <select name="doctor_id" class="modern-select @error('doctor_id') is-invalid @enderror">
                            <option value="">Select Doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $chamber->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->user->name }} ({{ $doctor->doctor_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Prescription Template</label>
                        <select name="prescription_template_id" class="modern-select @error('prescription_template_id') is-invalid @enderror">
                            <option value="">Select Template (Optional)</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('prescription_template_id', $chamber->prescription_template_id) == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ $template->paper_size }})
                                </option>
                            @endforeach
                        </select>
                        @error('prescription_template_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Chamber Name</label>
                        <input type="text" name="chamber_name" value="{{ old('chamber_name', $chamber->chamber_name) }}" class="modern-input @error('chamber_name') is-invalid @enderror">
                        @error('chamber_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Hospital / Clinic Name</label>
                        <input type="text" name="hospital_name" value="{{ old('hospital_name', $chamber->hospital_name) }}" class="modern-input @error('hospital_name') is-invalid @enderror">
                        @error('hospital_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $chamber->phone) }}" class="modern-input @error('phone') is-invalid @enderror">
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Email (Optional)</label>
                        <input type="email" name="email" value="{{ old('email', $chamber->email) }}" class="modern-input @error('email') is-invalid @enderror">
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label-custom">Address</label>
                        <textarea name="address" rows="2" class="modern-textarea @error('address') is-invalid @enderror">{{ old('address', $chamber->address) }}</textarea>
                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Visiting Days</label>
                        <input type="text" name="visiting_days" value="{{ old('visiting_days', $chamber->visiting_days) }}" class="modern-input @error('visiting_days') is-invalid @enderror">
                        @error('visiting_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Visiting Time</label>
                        <input type="text" name="visiting_time" value="{{ old('visiting_time', $chamber->visiting_time) }}" class="modern-input @error('visiting_time') is-invalid @enderror">
                        @error('visiting_time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Status</label>
                        <select name="status" class="modern-select @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', (int) $chamber->status) === 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', (int) $chamber->status) === 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <p class="text-muted small mt-1 mb-0">একটা ডাক্তারের শুধু একটা চেম্বার Active থাকতে পারবে — Active করলে বাকিগুলো নিজে থেকে Inactive হয়ে যাবে।</p>
                    </div>
                </div>
            </div>

            <div class="glass-card p-3 d-flex justify-content-between">
                <a href="{{ route('admin.chambers.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-gradient px-4">Update Chamber</button>
            </div>
        </form>

    </div>
</div>
@endsection