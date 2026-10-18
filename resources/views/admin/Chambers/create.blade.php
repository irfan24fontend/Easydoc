@extends('layouts.admin')

@section('title', 'Create Chamber')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .workspace { background-color: #f8fafc; min-height: 100vh; }

    .page-head h1 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.85rem; margin: 0.2rem 0 0; }

    .btn-ghost { background: #fff; border: 1px solid #e2e8f0; color: #475569 !important; border-radius: 10px; font-weight: 600; font-size: 0.85rem; padding: 0.6rem 1.1rem; }
    .btn-ghost:hover { background: #f8fafc; }
    .btn-brand {
        background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff !important; border: none; border-radius: 10px;
        font-weight: 700; font-size: 0.875rem; padding: 0.7rem 1.6rem; box-shadow: 0 4px 10px rgba(79,70,229,.25);
    }
    .btn-brand:hover { filter: brightness(1.05); color: #fff; }

    .section-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); margin-bottom: 1.25rem; overflow: hidden; }
    .section-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
    .section-head i { width: 32px; height: 32px; border-radius: 9px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
    .section-head h6 { margin: 0; font-weight: 800; color: #0f172a; font-size: 0.9rem; }
    .section-head span { display: block; font-size: 0.75rem; color: #94a3b8; }
    .section-body { padding: 1.4rem; }

    .field-label { font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem; display: block; }
    .field-hint { font-size: 0.72rem; color: #94a3b8; margin-top: 0.35rem; }

    .modern-input, .modern-select, .modern-textarea {
        border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.65rem 0.9rem; font-size: 0.875rem;
        color: #1e293b; width: 100%; background: #fff;
    }
    .modern-input:focus, .modern-select:focus, .modern-textarea:focus {
        border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79,70,229,.1); outline: none;
    }

    /* Status toggle */
    .toggle-row { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #eef0f4; border-radius: 12px; padding: 1rem 1.2rem; }
    .toggle-row strong { font-size: 0.85rem; color: #0f172a; }
    .toggle-row span { font-size: 0.75rem; color: #94a3b8; }
    .switch { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background: #e2e8f0; border-radius: 999px; transition: .2s; }
    .slider::before { content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
    .switch input:checked + .slider { background: #4f46e5; }
    .switch input:checked + .slider::before { transform: translateX(20px); }

    .sticky-footer {
        position: sticky; bottom: 0; background: #fff; border: 1px solid #eef0f4; border-radius: 16px;
        padding: 1rem 1.4rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 12px rgba(0,0,0,.04);
    }
</style>

<div class="workspace py-4">
    <div class="container" style="max-width: 880px;">

        <div class="d-flex justify-content-between align-items-center page-head mb-4">
            <div>
                <span class="text-uppercase fw-bold" style="font-size:0.7rem; color:#4f46e5; letter-spacing:.06em;">Chamber Setup</span>
                <h1>নতুন চেম্বার যোগ করুন</h1>
                <p>ডাক্তার নির্বাচন করে চেম্বারের তথ্য ও প্রেসক্রিপশন টেমপ্লেট সেট করুন।</p>
            </div>
            <a href="{{ route('admin.chambers.index') }}" class="btn-ghost">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
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

        <form method="POST" action="{{ route('admin.chambers.store') }}">
            @csrf

            {{-- Section 1: Doctor & Template --}}
            <div class="section-card">
                <div class="section-head">
                    <i class="fa-solid fa-user-doctor"></i>
                    <div>
                        <h6>ডাক্তার ও টেমপ্লেট</h6>
                        <span>এই চেম্বারটি কোন ডাক্তারের এবং কোন প্রেসক্রিপশন ডিজাইনে প্রিন্ট হবে</span>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">ডাক্তার নির্বাচন করুন</label>
                            <select name="doctor_id" class="modern-select @error('doctor_id') is-invalid @enderror">
                                <option value="">-- Select Doctor --</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name }} ({{ $doctor->doctor_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">প্রেসক্রিপশন টেমপ্লেট</label>
                            <select name="prescription_template_id" class="modern-select @error('prescription_template_id') is-invalid @enderror">
                                <option value="">-- Optional --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('prescription_template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ $template->paper_size }})
                                    </option>
                                @endforeach
                            </select>
                            @error('prescription_template_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Chamber & Hospital info --}}
            <div class="section-card">
                <div class="section-head">
                    <i class="fa-solid fa-hospital"></i>
                    <div>
                        <h6>চেম্বার ও হাসপাতালের তথ্য</h6>
                        <span>রোগীরা প্রেসক্রিপশনে এই তথ্যগুলো দেখতে পাবেন</span>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">চেম্বারের নাম</label>
                            <input type="text" name="chamber_name" value="{{ old('chamber_name') }}" class="modern-input @error('chamber_name') is-invalid @enderror" placeholder="যেমন: Main Chamber">
                            @error('chamber_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">হাসপাতাল / ক্লিনিকের নাম</label>
                            <input type="text" name="hospital_name" value="{{ old('hospital_name') }}" class="modern-input @error('hospital_name') is-invalid @enderror" placeholder="যেমন: Popular Diagnostic Center">
                            @error('hospital_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">ফোন নাম্বার</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="modern-input @error('phone') is-invalid @enderror" placeholder="+8801XXXXXXXXX">
                            @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">ইমেইল (ঐচ্ছিক)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="modern-input @error('email') is-invalid @enderror" placeholder="chamber@example.com">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="field-label">ঠিকানা</label>
                            <textarea name="address" rows="2" class="modern-textarea @error('address') is-invalid @enderror" placeholder="সম্পূর্ণ ঠিকানা লিখুন">{{ old('address') }}</textarea>
                            @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Schedule --}}
            <div class="section-card">
                <div class="section-head">
                    <i class="fa-solid fa-calendar-days"></i>
                    <div>
                        <h6>ভিজিটিং সময়সূচি</h6>
                        <span>রোগীরা কবে এবং কখন দেখা করতে পারবেন</span>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">ভিজিটিং ডে</label>
                            <input type="text" name="visiting_days" value="{{ old('visiting_days') }}" class="modern-input @error('visiting_days') is-invalid @enderror" placeholder="শনি - বৃহস্পতি">
                            @error('visiting_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">ভিজিটিং সময়</label>
                            <input type="text" name="visiting_time" value="{{ old('visiting_time') }}" class="modern-input @error('visiting_time') is-invalid @enderror" placeholder="বিকাল ৫টা - রাত ৯টা">
                            @error('visiting_time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: Status --}}
            <div class="section-card">
                <div class="section-body">
                    <div class="toggle-row">
                        <div>
                            <strong>এই চেম্বারটি এখনই Active করুন</strong><br>
                            <span>একজন ডাক্তারের শুধু একটাই চেম্বার Active থাকতে পারে — Active করলে বাকি চেম্বারগুলো নিজে থেকে Inactive হয়ে যাবে।</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sticky-footer">
                <a href="{{ route('admin.chambers.index') }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-brand"><i class="fa-solid fa-floppy-disk me-1"></i> Save Chamber</button>
            </div>
        </form>

    </div>
</div>
@endsection