@extends('layouts.admin')

@section('title', 'Edit Prescription Template')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/eclipse.min.css">

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Edit Prescription Template</h3>
            <p class="text-muted mb-0">প্রয়োজনে নতুন স্টার্টার লোড করুন, অথবা বিদ্যমান HTML/CSS এডিট করুন।</p>
        </div>
        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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

    <form method="POST" action="{{ route('admin.templates.update', $template->id) }}" id="templateForm">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ============ LEFT SIDE ============ --}}
            <div class="col-xl-7">

                {{-- BASIC INFO --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="name" value="{{ old('name', $template->name) }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Example: Modern A4 Prescription">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Paper Size</label>
                                <select name="paper_size" id="paperSize" class="form-select @error('paper_size') is-invalid @enderror">
                                    <option value="A4" {{ old('paper_size', $template->paper_size) == 'A4' ? 'selected' : '' }}>A4</option>
                                    <option value="A5" {{ old('paper_size', $template->paper_size) == 'A5' ? 'selected' : '' }}>A5</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ old('status', (int) $template->status) === 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', (int) $template->status) === 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STARTER TEMPLATE GALLERY (optional reset) --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">🎨 নতুন Starter দিয়ে রিপ্লেস করতে চাইলে</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2" id="starterGallery">
                            <div class="col-4">
                                <div class="starter-card" data-starter="classic">
                                    <div class="starter-swatch swatch-classic"></div>
                                    <div class="starter-name">Classic<br><small>বাংলা + ইংরেজি</small></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="starter-card" data-starter="minimal">
                                    <div class="starter-swatch swatch-minimal"></div>
                                    <div class="starter-name">Minimal<br><small>Clean & Simple</small></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="starter-card" data-starter="modern">
                                    <div class="starter-swatch swatch-modern"></div>
                                    <div class="starter-name">Modern Card<br><small>রঙিন হেডার</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABS: Variables / HTML / CSS --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs card-header-tabs px-2 pt-2" id="editorTabs">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" data-tab="variables">🏷️ Variables</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-tab="html">HTML</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-tab="css">CSS</button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">

                        <div class="tab-pane-item" data-pane="variables">
                            <p class="text-muted small mb-3">যেকোনো ভ্যারিয়েবলে ক্লিক করলে সেটা HTML editor-এর কার্সর পজিশনে বসে যাবে।</p>
                            <div class="variable-container">
                                @php
                                    $vars = ['doctor_name','doctor_degree','doctor_bmdc','chamber_name','hospital_name','hospital_address','hospital_phone','date','patient_name','patient_age','patient_gender','patient_weight','blood_group','patient_address'];
                                    $vars2 = ['diagnosis','clinical_complaints','risk_factors','investigations','medicine_table','advice'];
                                @endphp
                                @foreach($vars as $v)
                                    <button type="button" class="btn btn-outline-primary btn-sm variable-btn" data-variable="&#123;&#123;{{ $v }}&#125;&#125;">&#123;&#123;{{ $v }}&#125;&#125;</button>
                                @endforeach
                                @foreach($vars2 as $v)
                                    <button type="button" class="btn btn-outline-success btn-sm variable-btn" data-variable="&#123;&#123;{{ $v }}&#125;&#125;">&#123;&#123;{{ $v }}&#125;&#125;</button>
                                @endforeach
                                <button type="button" class="btn btn-outline-secondary btn-sm variable-btn" data-variable="&#123;&#123;logo&#125;&#125;">&#123;&#123;logo&#125;&#125;</button>
                            </div>
                        </div>

                        <div class="tab-pane-item d-none" data-pane="html">
                            <textarea name="html" id="htmlEditor" class="d-none">{{ old('html', $template->html) }}</textarea>
                            @error('html') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div class="tab-pane-item d-none" data-pane="css">
                            <textarea name="css" id="cssEditor" class="d-none">{{ old('css', $template->css) }}</textarea>
                            @error('css') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>

                {{-- SAVE --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Template</button>
                    </div>
                </div>

            </div>

            {{-- ============ RIGHT SIDE PREVIEW ============ --}}
            <div class="col-xl-5">
                <div class="card shadow-sm preview-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Live Preview</h5>
                        <button type="button" id="refreshPreview" class="btn btn-sm btn-outline-primary">↻ Refresh</button>
                    </div>
                    <div class="card-body preview-background">
                        <div id="previewWrapper">
                            <div id="previewContainer"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<style>
.variable-container { display: flex; flex-wrap: wrap; gap: 7px; }
.variable-btn { cursor: pointer; font-size: 12px; }

.nav-tabs .nav-link { cursor: pointer; color: #64748b; font-weight: 600; font-size: 0.85rem; border: none; border-bottom: 2px solid transparent; }
.nav-tabs .nav-link.active { color: #4f46e5; border-bottom-color: #4f46e5; background: transparent; }

.starter-card { border: 2px solid #e2e8f0; border-radius: 10px; padding: 10px; text-align: center; cursor: pointer; transition: all .15s ease; }
.starter-card:hover { border-color: #4f46e5; box-shadow: 0 4px 10px rgba(79,70,229,.12); }
.starter-swatch { height: 55px; border-radius: 6px; margin-bottom: 6px; }
.swatch-classic { background: linear-gradient(135deg,#43a63b,#f39a19); }
.swatch-minimal { background: linear-gradient(135deg,#e2e8f0,#3b82f6); }
.swatch-modern  { background: linear-gradient(135deg,#4f46e5,#ec4899); }
.starter-name { font-size: 12px; font-weight: 600; color: #334155; }

.CodeMirror { height: 520px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; }

.preview-card { position: sticky; top: 20px; }
.preview-background { background: #e9ecef; min-height: 800px; overflow: auto; }
#previewWrapper { width: 100%; display: flex; justify-content: center; overflow-x: auto; padding: 10px; }
#previewContainer { width: 794px; min-height: 1123px; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,.15); flex-shrink: 0; overflow: hidden; }
#previewContainer.paper-a5 { width: 559px; min-height: 794px; }
#previewContainer img { max-width: 100%; }
@media(max-width: 1199px) { .preview-card { position: static; } }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function token(name) { return '{' + '{' + name + '}' + '}'; }

    /* ================= CODEMIRROR INIT (loads existing template content) ================= */
    const htmlCM = CodeMirror.fromTextArea(document.getElementById('htmlEditor'), {
        mode: 'htmlmixed', theme: 'eclipse', lineNumbers: true, lineWrapping: true, tabSize: 4,
    });
    const cssCM = CodeMirror.fromTextArea(document.getElementById('cssEditor'), {
        mode: 'css', theme: 'eclipse', lineNumbers: true, lineWrapping: true, tabSize: 4,
    });

    const paperSize = document.getElementById('paperSize');
    const previewContainer = document.getElementById('previewContainer');

    /* ================= TABS ================= */
    document.querySelectorAll('#editorTabs .nav-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#editorTabs .nav-link').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const tab = this.dataset.tab;
            document.querySelectorAll('.tab-pane-item').forEach(p => p.classList.add('d-none'));
            document.querySelector('.tab-pane-item[data-pane="' + tab + '"]').classList.remove('d-none');
            if (tab === 'html') setTimeout(() => htmlCM.refresh(), 10);
            if (tab === 'css') setTimeout(() => cssCM.refresh(), 10);
        });
    });

    /* ================= DEMO DATA (for preview only) ================= */
    const demoData = {
        doctor_name: 'Dr. Mobarok Ali', doctor_degree: 'MBBS, MS (Obstetrics & Gynaecology)',
        doctor_bmdc: '123456', chamber_name: 'Main Chamber', hospital_name: 'CHAND CLINIC',
        hospital_address: 'North Bongobigaon bus stand, Chapaguri Road', hospital_phone: '+880 1700-000000',
        date: '13 September 2026', patient_name: 'Abdullah Al Irfan', patient_age: '25',
        patient_gender: 'Male', patient_weight: '65 kg', blood_group: 'B+', patient_address: 'Dhaka, Bangladesh',
        diagnosis: 'Example diagnosis', clinical_complaints: 'Fever, headache and general weakness.',
        risk_factors: 'No significant risk factors.', investigations: 'CBC, Blood Sugar',
        advice: 'Take medicines regularly and follow the doctor\u2019s instructions.',
    };

    const medicineTable = `<table class="medicine-table"><thead><tr><th>SL</th><th>Medicine</th><th>Dose</th><th>Duration</th></tr></thead><tbody>
        <tr><td>1</td><td>Paracetamol 500mg</td><td>1+1+1</td><td>5 Days</td></tr>
        <tr><td>2</td><td>Omeprazole 20mg</td><td>1+0+0</td><td>5 Days</td></tr>
        <tr><td>3</td><td>Vitamin D3</td><td>0+0+1</td><td>30 Days</td></tr>
        </tbody></table>`;

    const demoLogo = `<div class="demo-logo"><div class="logo-circle">+</div></div>`;

    /* ================= STARTER TEMPLATES (only used if admin clicks a card) ================= */
    const templates = {
        classic: {
            html: `<div class="prescription-page">
  <div class="doctor-header">
    <div class="doctor-left">
      <div class="doctor-name">${token('doctor_name')}</div>
      <div class="doctor-degree">${token('doctor_degree')}</div>
      <div class="doctor-contact">Registration no.: ${token('doctor_bmdc')}</div>
      <div class="doctor-contact">Mobile: ${token('hospital_phone')}</div>
    </div>
  </div>
  <div class="patient-info">
    <div><strong>Name:</strong> ${token('patient_name')}</div>
    <div><strong>Age:</strong> ${token('patient_age')}</div>
    <div><strong>Wt:</strong> ${token('patient_weight')}</div>
    <div><strong>Date:</strong> ${token('date')}</div>
  </div>
  <div class="prescription-body">
    <div class="left-panel">
      <div class="section-box"><div class="section-title">Dx-</div><div class="section-content">${token('diagnosis')}</div></div>
      <div class="section-box"><div class="section-title">Clinical Complaints:</div><div class="section-content">${token('clinical_complaints')}</div></div>
      <div class="section-box"><div class="section-title">Investigations:</div><div class="section-content">${token('investigations')}</div></div>
    </div>
    <div class="right-panel">
      <div class="rx-symbol">Rx</div>
      <div class="medicine-area">${token('medicine_table')}</div>
      <div class="advice-area"><div class="advice-title">Advice</div><div>${token('advice')}</div></div>
    </div>
  </div>
  <div class="clinic-footer">
    <div class="clinic-logo">${token('logo')}</div>
    <div class="clinic-information">
      <div class="clinic-name">${token('hospital_name')}</div>
      <div class="clinic-address">${token('hospital_address')}</div>
      <div class="clinic-phone">Phone: ${token('hospital_phone')}</div>
    </div>
  </div>
</div>`,
            css: `* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; color: #202020; }
.prescription-page { width: 100%; min-height: 1123px; background: #fff; }
.doctor-header { min-height: 150px; padding: 40px 50px 20px; border-bottom: 2px solid #f39a19; }
.doctor-name { font-size: 28px; font-weight: 700; color: #43a63b; margin-bottom: 6px; }
.doctor-degree { font-size: 15px; font-weight: 600; margin-bottom: 5px; }
.doctor-contact { font-size: 14px; margin-top: 4px; }
.patient-info { min-height: 48px; border-bottom: 2px solid #f39a19; display: flex; gap: 30px; align-items: center; padding: 0 50px; font-size: 16px; }
.prescription-body { min-height: 650px; display: grid; grid-template-columns: 32% 68%; }
.left-panel { border-right: 2px solid #f39a19; padding: 25px 30px 20px 50px; }
.section-box { margin-bottom: 40px; }
.section-title { font-size: 17px; font-weight: 700; margin-bottom: 12px; }
.section-content { font-size: 15px; line-height: 1.6; }
.right-panel { padding: 25px 40px; }
.rx-symbol { font-family: Georgia, serif; font-size: 32px; font-weight: 700; margin-bottom: 20px; }
.medicine-table { width: 100%; border-collapse: collapse; }
.medicine-table th, .medicine-table td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
.advice-area { margin-top: 30px; font-size: 15px; line-height: 1.6; }
.advice-title { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
.clinic-footer { min-height: 130px; border-top: 2px solid #f39a19; display: flex; align-items: center; padding: 25px 50px; }
.clinic-logo { width: 90px; margin-right: 20px; }
.clinic-name { font-size: 18px; font-weight: 700; color: #f28c00; }
.clinic-address, .clinic-phone { font-size: 14px; }
.demo-logo { display: flex; justify-content: center; }
.logo-circle { width: 55px; height: 55px; border-radius: 50%; border: 5px solid #43a63b; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; color: #f28c00; }
@media print { .prescription-page { width: 210mm; min-height: 297mm; } @page { size: A4; margin: 0; } }`,
        },

        minimal: {
            html: `<div class="rx-page">
  <div class="rx-top">
    <div>
      <h1>${token('doctor_name')}</h1>
      <p>${token('doctor_degree')} &middot; Reg: ${token('doctor_bmdc')}</p>
    </div>
    <div class="rx-clinic">
      <h2>${token('hospital_name')}</h2>
      <p>${token('hospital_address')}</p>
      <p>${token('hospital_phone')}</p>
    </div>
  </div>
  <div class="rx-patient">
    <span>নাম: ${token('patient_name')}</span>
    <span>বয়স: ${token('patient_age')}</span>
    <span>ওজন: ${token('patient_weight')}</span>
    <span>তারিখ: ${token('date')}</span>
  </div>
  <div class="rx-body">
    <div class="rx-left">
      <h4>Dx</h4><p>${token('diagnosis')}</p>
      <h4>Complaints</h4><p>${token('clinical_complaints')}</p>
      <h4>Investigations</h4><p>${token('investigations')}</p>
    </div>
    <div class="rx-right">
      <div class="rx-symbol">&#8478;</div>
      ${token('medicine_table')}
      <h4>Advice</h4><p>${token('advice')}</p>
    </div>
  </div>
  <div class="rx-footer">${token('logo')} <span>${token('hospital_name')}</span></div>
</div>`,
            css: `* { box-sizing: border-box; }
body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; }
.rx-page { width: 100%; min-height: 1123px; background: #fff; padding: 40px 50px; }
.rx-top { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 15px; }
.rx-top h1 { font-size: 24px; margin: 0 0 4px; color: #1d4ed8; }
.rx-top p { font-size: 13px; margin: 2px 0; color: #475569; }
.rx-clinic { text-align: right; }
.rx-clinic h2 { font-size: 16px; margin: 0 0 4px; }
.rx-patient { display: flex; gap: 25px; font-size: 14px; background: #f1f5f9; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; }
.rx-body { display: grid; grid-template-columns: 30% 70%; gap: 20px; }
.rx-left h4 { font-size: 14px; margin: 15px 0 5px; color: #1d4ed8; }
.rx-left p { font-size: 13px; margin: 0; line-height: 1.5; }
.rx-right { border-left: 1px solid #e2e8f0; padding-left: 20px; }
.rx-symbol { font-size: 26px; font-weight: 700; color: #3b82f6; margin-bottom: 10px; }
.medicine-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.medicine-table th { background: #eff6ff; font-size: 12px; padding: 8px; text-align: left; }
.medicine-table td { font-size: 13px; padding: 8px; border-bottom: 1px solid #e2e8f0; }
.rx-footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 10px; }
@media print { .rx-page { width: 210mm; min-height: 297mm; } @page { size: A4; margin: 0; } }`,
        },

        modern: {
            html: `<div class="mc-page">
  <div class="mc-header">
    <div>
      <h1>${token('doctor_name')}</h1>
      <p>${token('doctor_degree')} — BMDC: ${token('doctor_bmdc')}</p>
    </div>
    <div class="mc-badge">${token('logo')}</div>
  </div>
  <div class="mc-clinicbar">${token('hospital_name')} &nbsp;|&nbsp; ${token('hospital_address')} &nbsp;|&nbsp; ${token('hospital_phone')}</div>
  <div class="mc-patientcard">
    <div><b>রোগী:</b> ${token('patient_name')}</div>
    <div><b>বয়স:</b> ${token('patient_age')}</div>
    <div><b>ওজন:</b> ${token('patient_weight')}</div>
    <div><b>তারিখ:</b> ${token('date')}</div>
  </div>
  <div class="mc-grid">
    <div class="mc-col">
      <div class="mc-box"><h5>Diagnosis</h5><p>${token('diagnosis')}</p></div>
      <div class="mc-box"><h5>Complaints</h5><p>${token('clinical_complaints')}</p></div>
      <div class="mc-box"><h5>Investigations</h5><p>${token('investigations')}</p></div>
    </div>
    <div class="mc-col mc-rx">
      <div class="mc-rxsign">℞</div>
      ${token('medicine_table')}
      <div class="mc-box"><h5>Advice</h5><p>${token('advice')}</p></div>
    </div>
  </div>
</div>`,
            css: `* { box-sizing: border-box; }
body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; }
.mc-page { width: 100%; min-height: 1123px; background: #fff; }
.mc-header { background: linear-gradient(135deg,#4f46e5,#7c3aed); color: #fff; padding: 30px 50px; display: flex; justify-content: space-between; align-items: center; }
.mc-header h1 { margin: 0 0 4px; font-size: 24px; }
.mc-header p { margin: 0; font-size: 13px; opacity: .9; }
.mc-badge { width: 55px; height: 55px; border-radius: 50%; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; }
.mc-clinicbar { background: #f5f3ff; color: #4f46e5; font-size: 12px; padding: 8px 50px; font-weight: 600; }
.mc-patientcard { margin: 20px 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 20px; display: flex; gap: 25px; font-size: 13px; box-shadow: 0 2px 6px rgba(0,0,0,.04); }
.mc-grid { display: grid; grid-template-columns: 32% 68%; gap: 20px; padding: 0 50px 40px; }
.mc-box { background: #f8fafc; border-radius: 10px; padding: 14px 16px; margin-bottom: 14px; }
.mc-box h5 { margin: 0 0 6px; font-size: 13px; color: #4f46e5; }
.mc-box p { margin: 0; font-size: 13px; line-height: 1.5; }
.mc-rxsign { font-size: 30px; font-weight: 700; color: #7c3aed; margin-bottom: 10px; }
.medicine-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #fff; border-radius: 8px; overflow: hidden; }
.medicine-table th { background: #ede9fe; font-size: 12px; padding: 8px; text-align: left; }
.medicine-table td { font-size: 13px; padding: 8px; border-bottom: 1px solid #f1f5f9; }
@media print { .mc-page { width: 210mm; min-height: 297mm; } @page { size: A4; margin: 0; } }`,
        },
    };

    /* ================= LOAD STARTER (asks confirmation since content already exists) ================= */
    document.querySelectorAll('.starter-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const key = this.dataset.starter;
            if (!confirm('বর্তমান HTML/CSS মুছে গিয়ে "' + key + '" টেমপ্লেট লোড হবে। এগোবেন?')) {
                return;
            }
            htmlCM.setValue(templates[key].html);
            cssCM.setValue(templates[key].css);
            updatePreview();
        });
    });

    /* ================= REPLACE VARIABLES (for preview) ================= */
    function replaceVariables(html) {
        Object.keys(demoData).forEach(function (key) {
            html = html.split(token(key)).join(demoData[key]);
        });
        html = html.split(token('medicine_table')).join(medicineTable);
        html = html.split(token('logo')).join(demoLogo);
        return html;
    }

    /* ================= UPDATE PREVIEW ================= */
    function updatePreview() {
        let html = htmlCM.getValue();
        let css = cssCM.getValue();
        if (!html.trim() && !css.trim()) return;

        html = replaceVariables(html);
        previewContainer.innerHTML = '<style>' + css + '</style>' + html;

        previewContainer.classList.remove('paper-a5');
        if (paperSize && paperSize.value === 'A5') {
            previewContainer.classList.add('paper-a5');
        }
    }

    htmlCM.on('change', updatePreview);
    cssCM.on('change', updatePreview);
    if (paperSize) paperSize.addEventListener('change', updatePreview);
    document.getElementById('refreshPreview').addEventListener('click', updatePreview);

    /* ================= INSERT VARIABLE ================= */
    document.querySelectorAll('.variable-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelector('#editorTabs .nav-link[data-tab="html"]').click();
            htmlCM.replaceSelection(this.getAttribute('data-variable'));
            htmlCM.focus();
            updatePreview();
        });
    });

    /* ================= SYNC CODEMIRROR -> TEXTAREA BEFORE SUBMIT ================= */
    document.getElementById('templateForm').addEventListener('submit', function () {
        htmlCM.save();
        cssCM.save();
    });

    /* ================= INITIAL LOAD: shows the EXISTING template, no auto-replace ================= */
    updatePreview();
});
</script>

@endsection