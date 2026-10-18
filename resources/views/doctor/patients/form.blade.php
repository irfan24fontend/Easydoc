@php
    $isEdit = isset($patient) && $patient->exists;
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $curBlood = old('blood_group', $patient->blood_group ?? '');
    $curGender = old('gender', $patient->gender ?? '');
@endphp

<form method="POST" action="{{ $isEdit ? route('doctor.patients.update', $patient) : route('doctor.patients.store') }}" class="glass-card p-4" style="max-width:760px;">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-bold text-uppercase text-secondary">নাম *</label>
            <input type="text" name="name" value="{{ old('name', $patient->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-bold text-uppercase text-secondary">মোবাইল *</label>
            <input type="text" name="mobile" value="{{ old('mobile', $patient->mobile ?? '') }}" inputmode="numeric" placeholder="017XXXXXXXX" class="form-control @error('mobile') is-invalid @enderror" required>
            <div class="form-text">একই মোবাইলে একাধিক রোগী থাকতে পারে (পরিবারের সদস্য), শুধু নাম আলাদা হতে হবে।</div>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-uppercase text-secondary">বয়স</label>
            <input type="number" min="0" max="150" name="age" value="{{ old('age', $patient->age ?? '') }}" class="form-control @error('age') is-invalid @enderror">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-uppercase text-secondary">ওজন (kg)</label>
            <input type="number" step="0.1" min="0" name="weight" value="{{ old('weight', isset($patient->weight) ? rtrim(rtrim(number_format((float) $patient->weight, 2, '.', ''), '0'), '.') : '') }}" class="form-control @error('weight') is-invalid @enderror">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-uppercase text-secondary">লিঙ্গ</label>
            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                <option value="">—</option>
                @foreach(['Male', 'Female', 'Other'] as $g)
                    <option value="{{ $g }}" @selected($curGender === $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-uppercase text-secondary">Blood Group</label>
            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                <option value="">—</option>
                @foreach($bloods as $b)
                    <option value="{{ $b }}" @selected($curBlood === $b)>{{ $b }}</option>
                @endforeach
                @if($curBlood && !in_array($curBlood, $bloods, true))
                    <option value="{{ $curBlood }}" selected>{{ $curBlood }}</option>
                @endif
            </select>
        </div>

        <div class="col-12">
            <label class="form-label small fw-bold text-uppercase text-secondary">ঠিকানা</label>
            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="ঐচ্ছিক">{{ old('address', $patient->address ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary fw-bold px-4" style="background:#4f46e5;border-color:#4f46e5;">
            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $isEdit ? 'আপডেট করুন' : 'রোগী যোগ করুন' }}
        </button>
        <a href="{{ $isEdit ? route('doctor.patients.show', $patient) : route('doctor.patients.index') }}" class="btn btn-outline-secondary fw-semibold">বাতিল</a>
    </div>
</form>