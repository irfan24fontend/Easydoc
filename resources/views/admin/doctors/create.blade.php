@extends('layouts.admin')

@section('title', 'Create Doctor')

@section('content')
<!-- Custom Modern CSS Extensions -->
<style>
    .form-workspace {
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .modern-input, .modern-select {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        background-color: #ffffff;
        color: #1e293b;
        transition: all 0.2s ease;
    }
    .modern-input:focus, .modern-select:focus {
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
    }
    .image-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: #f8fafc;
    }
    .image-dropzone:hover {
        border-color: #4f46e5;
        background-color: rgba(79, 70, 229, 0.02);
    }
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white !important;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-gradient:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
    }
</style>

<div class="form-workspace py-5">
    <div class="container max-w-[1200px]">
        
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <span class="badge bg-indigo-50 text-indigo-700 text-uppercase tracking-wider px-2.5 py-1 mb-2 font-semibold" style="font-size: 0.65rem; background-color: #e0e7ff; color: #4338ca;">Medical Network</span>
                <h1 class="h2 fw-bold text-slate-900 m-0">Onboard Professional</h1>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-light border rounded-pill px-4 text-secondary small fw-medium transition-all">
                <i class="fas fa-arrow-left me-2"></i>Dismiss
            </a>
        </div>

        <form method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <!-- Main Form Inputs Column -->
                <div class="col-lg-8 space-y-4">
                    
                    <!-- Box 1: Profile Platform Identity -->
                    <div class="glass-card p-4 mb-4">
                        <div class="d-flex items-center gap-2 mb-4">
                            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-3" style="background-color: #e0e7ff; color: #4f46e5;">
                                <i class="fas fa-shield-alt fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold m-0 text-slate-800">Platform Credentials</h5>
                                <p class="text-muted small m-0">System access parameters and security variables.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Full Profile Name</label>
                                <input type="text" name="name" class="modern-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Dr. Abdullah Al Irfan">
                                @error('name') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Professional Email</label>
                                <input type="email" name="email" class="modern-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="irfan@easydoc.com">
                                @error('email') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Mobile Connection</label>
                                <input type="text" name="phone" class="modern-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+8801XXXXXXXXX">
                                @error('phone') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Secure Access Password</label>
                                <input type="password" name="password" class="modern-input @error('password') is-invalid @enderror" placeholder="••••••••••••">
                                @error('password') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Box 2: Professional Details -->
                    <div class="glass-card p-4">
                        <div class="d-flex items-center gap-2 mb-4">
                            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-3" style="background-color: #d1fae5; color: #10b981;">
                                <i class="fas fa-id-card fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold m-0 text-slate-800">Medical Practitioner Verification</h5>
                                <p class="text-muted small m-0">Government registration values and medical catalog mapping.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">BMDC Registry ID</label>
                                <input type="text" name="bmdc_number" class="modern-input @error('bmdc_number') is-invalid @enderror" value="{{ old('bmdc_number') }}" placeholder="A-XXXXX">
                                @error('bmdc_number') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Clinical Speciality</label>
                                <input type="text" name="speciality" class="modern-input @error('speciality') is-invalid @enderror" value="{{ old('speciality') }}" placeholder="Cardiologist / Software Architect">
                                @error('speciality') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Academic Qualifications</label>
                                <input type="text" name="qualification" class="modern-input @error('qualification') is-invalid @enderror" value="{{ old('qualification') }}" placeholder="MBBS, FCPS, MD">
                                @error('qualification') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Corporate Designation</label>
                                <input type="text" name="designation" class="modern-input @error('designation') is-invalid @enderror" value="{{ old('designation') }}" placeholder="Senior Consultant / Professor">
                                @error('designation') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Active Experience</label>
                                <input type="text" name="experience" class="modern-input @error('experience') is-invalid @enderror" value="{{ old('experience') }}" placeholder="e.g. 8+ Years">
                                @error('experience') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label-custom">Biological Gender</label>
                                <select name="gender" class="modern-select @error('gender') is-invalid @enderror">
                                    <option value="">Choose Path</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Media Upload and Panel Submission Actions -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 2rem; z-index: 10;">
                        
                        <!-- Premium Drag Box Upload Area -->
                        <div class="glass-card p-4 text-center mb-4">
                            <label class="form-label-custom text-start d-block mb-3">Identity Portrait</label>
                            
                            <div class="image-dropzone p-4 position-relative mb-3">
                                <input type="file" name="photo" id="avatarInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" style="z-index: 5;">
                                <div id="dropzoneContent">
                                    <div class="d-inline-flex p-3 bg-white rounded-circle shadow-sm mb-2 text-slate-400">
                                        <i class="fas fa-cloud-upload-alt fs-4 text-indigo-500"></i>
                                    </div>
                                    <p class="small fw-semibold text-slate-700 m-0">Upload new avatar</p>
                                    <p class="text-muted m-0" style="font-size: 11px;">Drag file here or browse</p>
                                </div>
                                <div id="previewContainer" class="d-none">
                                    <img id="avatarPreview" src="#" alt="Preview" class="img-fluid rounded-circle border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center gap-3 text-muted" style="font-size: 11px;">
                                <span><i class="fas fa-check-circle text-emerald-500 me-1"></i>PNG/JPG</span>
                                <span><i class="fas fa-check-circle text-emerald-500 me-1"></i>Max 2MB</span>
                            </div>
                            @error('photo') <div class="text-danger small mt-2 text-start font-semibold">{{ $message }}</div> @enderror
                        </div>

                        <!-- Panel Submission Actions Box -->
                        <div class="glass-card p-3">
                            <button type="submit" class="btn btn-gradient w-100 py-3 mb-2 shadow-sm d-flex items-center justify-content-center gap-2">
                                <i class="fas fa-plus-circle"></i> Complete Onboarding
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-light w-100 py-2.5 text-slate-500 fw-medium small rounded-lg">
                                Discard Record
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Avatar Update Hook Script -->
<script>
    document.getElementById('avatarInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
                document.getElementById('dropzoneContent').classList.add('d-none');
                document.getElementById('previewContainer').classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection