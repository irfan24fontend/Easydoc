@extends('layouts.doctor')

@section('title', 'Patients')

@push('styles')
<style>
    .glass-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .stat-tile { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 1rem 1.15rem; display: flex; align-items: center; gap: 14px; height: 100%; }
    .stat-tile .ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .stat-tile .num { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .stat-tile .lbl { font-size: .74rem; color: #64748b; font-weight: 600; }
    a.stat-tile { text-decoration: none; transition: all .15s; }
    a.stat-tile:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px rgba(79,70,229,.08); }

    .saas-table th { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #475569; background: #f1f5f9; border-bottom: 2px solid #e2e8f0; padding: .9rem 1.1rem; white-space: nowrap; }
    .saas-table td { font-size: .86rem; color: #334155; padding: .8rem 1.1rem; vertical-align: middle; border-bottom: 1px solid #eef0f4; }
    .saas-table tr:hover td { background: #f8fafc; }

    .pid-badge { display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; font-size: .75rem; padding: .18rem .5rem; border-radius: 6px; background: #eef2ff; color: #4338ca; border: 1px solid #e0e7ff; white-space: nowrap; }
    .avatar { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .8rem; flex-shrink: 0; }
    .av-male { background: #e0e7ff; color: #3730a3; }
    .av-female { background: #fce7f3; color: #9d174d; }
    .av-other { background: #e2e8f0; color: #334155; }
    .pending-pill { font-size: .68rem; font-weight: 800; background: #fef3c7; color: #92400e; border-radius: 999px; padding: .2rem .55rem; white-space: nowrap; }

    .btn-act { font-size: .72rem; font-weight: 700; padding: .38rem .7rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; border: 1px solid transparent; transition: all .15s; }
    .btn-act.view { color: #475569; background: #f1f5f9; border-color: #e2e8f0; }
    .btn-act.view:hover { background: #475569; color: #fff; }
    .btn-act.rx { color: #fff; background: #4f46e5; }
    .btn-act.rx:hover { background: #4338ca; color: #fff; }
    .btn-act.edit { color: #b45309; background: #fef3c7; border-color: #fde68a; }
    .btn-act.edit:hover { background: #b45309; color: #fff; }

    .flabel { font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .25rem; display: block; }
</style>
@endpush

@section('content')
@php
    $filterKeys = ['q', 'gender', 'blood_group', 'age_group', 'pending', 'visit_from', 'visit_to'];
    $hasFilter = collect($filterKeys)->contains(fn ($k) => request()->filled($k));
    $tz = config('easydoc.timezone', 'Asia/Dhaka');
@endphp

<div class="py-2">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark m-0">Patients</h1>
            <p class="text-muted small m-0">আপনার সব রোগী — নাম, মোবাইল বা Patient ID (যেমন PT-0007) দিয়ে খুঁজুন।</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('doctor.patients.export', request()->query()) }}" class="btn btn-outline-secondary fw-semibold">
                <i class="fa-solid fa-file-csv me-1"></i> CSV Export
            </a>
            <a href="{{ route('doctor.patients.create') }}" class="btn btn-primary fw-semibold px-4" style="background:#4f46e5;border-color:#4f46e5;">
                <i class="fa-solid fa-user-plus me-1"></i> নতুন রোগী
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('doctor.patients.index') }}" class="stat-tile">
                <div class="ico" style="background:#eef2ff;color:#4f46e5;"><i class="fa-solid fa-users"></i></div>
                <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">মোট রোগী</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('doctor.patients.index', ['sort' => 'newest']) }}" class="stat-tile">
                <div class="ico" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-user-plus"></i></div>
                <div><div class="num">{{ $stats['new_month'] }}</div><div class="lbl">এই মাসে নতুন</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('doctor.patients.index', ['visit_from' => now()->toDateString(), 'visit_to' => now()->toDateString()]) }}" class="stat-tile">
                <div class="ico" style="background:#e0f2fe;color:#0284c7;"><i class="fa-solid fa-stethoscope"></i></div>
                <div><div class="num">{{ $stats['today'] }}</div><div class="lbl">আজ দেখা হয়েছে</div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('doctor.patients.index', ['pending' => 1]) }}" class="stat-tile">
                <div class="ico" style="background:#fef3c7;color:#b45309;"><i class="fa-solid fa-flask-vial"></i></div>
                <div><div class="num">{{ $stats['pending'] }}</div><div class="lbl">রিপোর্টের অপেক্ষায়</div></div>
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('doctor.patients.index') }}" class="glass-card p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="flabel">খুঁজুন</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="নাম / মোবাইল / PT-0007">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">সব</option>
                    @foreach(['Male', 'Female', 'Other'] as $g)
                        <option value="{{ $g }}" @selected(request('gender') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">বয়স</label>
                <select name="age_group" class="form-select">
                    <option value="">সব</option>
                    <option value="child" @selected(request('age_group') === 'child')>শিশু (০–১২)</option>
                    <option value="teen" @selected(request('age_group') === 'teen')>কিশোর (১৩–১৯)</option>
                    <option value="adult" @selected(request('age_group') === 'adult')>প্রাপ্তবয়স্ক (২০–৫৯)</option>
                    <option value="senior" @selected(request('age_group') === 'senior')>বয়স্ক (৬০+)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">সব</option>
                    @foreach($bloodGroups as $bg)
                        <option value="{{ $bg }}" @selected(request('blood_group') === $bg)>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">সাজান</label>
                <select name="sort" class="form-select">
                    <option value="" @selected(!request('sort'))>সর্বশেষ ভিজিট</option>
                    <option value="name" @selected(request('sort') === 'name')>নাম (A–Z)</option>
                    <option value="visits" @selected(request('sort') === 'visits')>বেশি ভিজিট</option>
                    <option value="newest" @selected(request('sort') === 'newest')>নতুন রোগী আগে</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Patient ID অনুযায়ী</option>
                </select>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">ভিজিট (থেকে)</label>
                <input type="date" name="visit_from" value="{{ request('visit_from') }}" class="form-control">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="flabel">ভিজিট (পর্যন্ত)</label>
                <input type="date" name="visit_to" value="{{ request('visit_to') }}" class="form-control">
            </div>
            <div class="col-12 col-md-6 col-lg-4 d-flex align-items-center">
                <div class="form-check ms-1">
                    <input class="form-check-input" type="checkbox" name="pending" value="1" id="fPending" @checked(request()->boolean('pending'))>
                    <label class="form-check-label small fw-semibold" for="fPending">শুধু রিপোর্টের অপেক্ষায় থাকা রোগী</label>
                </div>
            </div>
            <div class="col-12 col-lg-4 d-flex gap-2 justify-content-lg-end">
                <button class="btn btn-primary fw-semibold px-4" style="background:#4f46e5;border-color:#4f46e5;"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                @if($hasFilter || request('sort'))
                    <a href="{{ route('doctor.patients.index') }}" class="btn btn-outline-secondary fw-semibold">Reset</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table saas-table mb-0">
                <thead>
                    <tr>
                        <th>রোগী</th>
                        <th>মোবাইল</th>
                        <th>বয়স / লিঙ্গ</th>
                        <th>Blood</th>
                        <th class="text-center">ভিজিট</th>
                        <th>শেষ ভিজিট</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $p)
                        @php
                            $avClass = $p->gender === 'Female' ? 'av-female' : ($p->gender === 'Male' ? 'av-male' : 'av-other');
                            $last = $p->last_visit_at ? \Carbon\Carbon::parse($p->last_visit_at) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar {{ $avClass }}">{{ $p->initials }}</div>
                                    <div style="min-width:0;">
                                        <a href="{{ route('doctor.patients.show', $p) }}" class="fw-bold text-dark text-decoration-none">{{ $p->name }}</a>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="pid-badge">{{ $p->patient_code }}</span>
                                            @if($p->pending_reports_count > 0)
                                                <span class="pending-pill"><i class="fa-solid fa-hourglass-half me-1"></i>রিপোর্টের অপেক্ষায়</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $p->mobile }}</td>
                            <td>
                                {{ $p->age !== null ? $p->age . ' বছর' : '—' }}
                                @if($p->gender)<span class="text-muted"> · {{ $p->gender }}</span>@endif
                                @if($p->weight)<div class="small text-muted">{{ rtrim(rtrim(number_format((float) $p->weight, 1), '0'), '.') }} kg</div>@endif
                            </td>
                            <td>{{ $p->blood_group ?: '—' }}</td>
                            <td class="text-center"><span class="fw-bold">{{ $p->visits_count }}</span></td>
                            <td>
                                @if($last)
                                    {{ $last->format('d M Y') }}
                                    <div class="small text-muted">{{ $last->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">এখনো আসেননি</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('doctor.patients.show', $p) }}" class="btn-act view"><i class="fa-solid fa-id-card-clip"></i> প্রোফাইল</a>
                                    <a href="{{ route('doctor.prescriptions.create', ['patient' => $p->id]) }}" class="btn-act rx"><i class="fa-solid fa-file-medical"></i> Rx</a>
                                    <a href="{{ route('doctor.patients.edit', $p) }}" class="btn-act edit"><i class="fa-solid fa-pen"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-solid fa-user-slash d-block mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                                @if($hasFilter)
                                    এই ফিল্টারে কোনো রোগী পাওয়া যায়নি। <a href="{{ route('doctor.patients.index') }}">Reset করুন</a>
                                @else
                                    এখনো কোনো রোগী নেই। প্রেসক্রিপশন লিখলে বা "নতুন রোগী" চাপলে এখানে দেখা যাবে।
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">{{ $patients->firstItem() }}–{{ $patients->lastItem() }} / মোট {{ $patients->total() }} জন</div>
                {{ $patients->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection