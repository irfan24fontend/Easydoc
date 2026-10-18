@extends('layouts.doctor')

@section('title', 'সহকারী')

@section('content')
<style>
    .page-head h1 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: .85rem; margin: .2rem 0 0; }
    .cardx { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .field-label { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
    .avatar-circle { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg,#eef2ff,#e0e7ff); color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; }
</style>

<div class="page-head mb-4">
    <h1>আমার সহকারী</h1>
    <p>সহকারী নিজের লগইন দিয়ে ঢুকে রোগীর সিরিয়াল দিতে পারবে। প্রেসক্রিপশন, ওয়ালেট বা অন্য কিছু সে দেখতে পাবে না।</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-4">

    <div class="col-lg-4">
        <div class="cardx p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i>নতুন সহকারী যোগ করুন</h6>

            <form method="POST" action="{{ route('doctor.assistants.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="field-label">নাম *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="field-label">মোবাইল</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="01XXXXXXXXX">
                </div>
                <div class="mb-3">
                    <label class="field-label">লগইন ইমেইল *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="rahim.assistant@gmail.com" required>
                    <div class="form-text">আসল ইমেইল না থাকলে যেকোনো unique ঠিকানা দিন।</div>
                </div>
                <div class="mb-3">
                    <label class="field-label">পাসওয়ার্ড * (কমপক্ষে ৬ অক্ষর)</label>
                    <input type="text" name="password" class="form-control" required minlength="6" autocomplete="off">
                </div>
                <button class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-plus me-1"></i> সহকারী যোগ করুন</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="cardx p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="small text-muted text-uppercase">
                            <th>সহকারী</th><th>লগইন ইমেইল</th><th>মোবাইল</th><th>অবস্থা</th><th class="text-end">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assistants as $a)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle">{{ strtoupper(substr($a->user->name, 0, 2)) }}</div>
                                        <span class="fw-bold">{{ $a->user->name }}</span>
                                    </div>
                                </td>
                                <td class="small">{{ $a->user->email }}</td>
                                <td class="small text-muted">{{ $a->user->phone ?: '—' }}</td>
                                <td>
                                    @if($a->user->status === 'inactive')
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">নিষ্ক্রিয়</span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis">সক্রিয়</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAssistant{{ $a->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('doctor.assistants.destroy', $a->id) }}" onsubmit="return confirm('{{ $a->user->name }} কে মুছে ফেলবেন?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">এখনো কোনো সহকারী যোগ করা হয়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($assistants as $a)
    <div class="modal fade" id="editAssistant{{ $a->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('doctor.assistants.update', $a->id) }}" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">সহকারী এডিট করুন</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="field-label">নাম *</label>
                        <input type="text" name="name" value="{{ $a->user->name }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">মোবাইল</label>
                        <input type="text" name="phone" value="{{ $a->user->phone }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="field-label">লগইন ইমেইল *</label>
                        <input type="email" name="email" value="{{ $a->user->email }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">নতুন পাসওয়ার্ড</label>
                        <input type="text" name="password" class="form-control" minlength="6" autocomplete="off" placeholder="বদলাতে না চাইলে খালি রাখুন">
                    </div>
                    <div class="mb-1">
                        <label class="field-label">অবস্থা</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $a->user->status !== 'inactive' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ $a->user->status === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয় (লগইন বন্ধ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button class="btn btn-primary fw-bold">সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection