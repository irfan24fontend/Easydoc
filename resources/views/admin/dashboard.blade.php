@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<style>
    .page-head h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0; }

    .stat-card {
        background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1.3rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.03); display: flex; justify-content: space-between; align-items: flex-start;
    }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center;
        justify-content: center; font-size: 1.1rem;
    }
    .stat-num { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; margin-top: 0.6rem; }
    .stat-label { font-size: 0.78rem; color: #64748b; font-weight: 600; margin-top: 4px; }

    .panel-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .panel-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .panel-head h6 { margin: 0; font-weight: 800; color: #0f172a; font-size: 0.9rem; }
    .panel-head a { font-size: 0.78rem; font-weight: 700; color: #4f46e5; text-decoration: none; }

    .saas-table { width: 100%; }
    .saas-table thead th {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b;
        background: #f8fafc; padding: 0.75rem 1.4rem; text-align: left; border-bottom: 1px solid #eef0f4;
    }
    .saas-table tbody td { padding: 0.85rem 1.4rem; font-size: 0.85rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
    .saas-table tbody tr:last-child td { border-bottom: none; }

    .avatar-circle {
        width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg,#eef2ff,#e0e7ff);
        color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;
    }

    .quick-action {
        display: flex; align-items: center; gap: 12px; padding: 0.9rem 1.1rem; border: 1px solid #eef0f4;
        border-radius: 12px; text-decoration: none; color: #0f172a; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.6rem;
    }
    .quick-action:hover { border-color: #4f46e5; background: #fafbff; color: #0f172a; }
    .quick-action i { width: 36px; height: 36px; border-radius: 9px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; }
</style>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 page-head mb-4">
    <div>
        <h1>স্বাগতম, {{ auth()->user()->name }} 👋</h1>
        <p>আপনার EasyDoc প্ল্যাটফর্মের সার্বিক অবস্থা এক নজরে দেখুন।</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="fa-solid fa-user-doctor"></i></div>
                <div class="stat-num">{{ $stats['total_doctors'] }}</div>
                <div class="stat-label">মোট ডাক্তার</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="fa-solid fa-hospital"></i></div>
                <div class="stat-num">{{ $stats['active_chambers'] }}</div>
                <div class="stat-label">Active চেম্বার</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="fa-solid fa-file-medical"></i></div>
                <div class="stat-num">{{ $stats['total_templates'] }}</div>
                <div class="stat-label">প্রেসক্রিপশন টেমপ্লেট</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fa-solid fa-wallet"></i></div>
                <div class="stat-num">৳{{ number_format($stats['wallet_balance'], 2) }}</div>
                <div class="stat-label">মোট ওয়ালেট ব্যালেন্স</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-head">
                <h6>সাম্প্রতিক ডাক্তার</h6>
                <a href="{{ route('admin.doctors.index') }}">সব দেখুন →</a>
            </div>
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>ডাক্তার</th>
                        <th>Speciality</th>
                        <th>Doctor Code</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDoctors as $doctor)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle">{{ strtoupper(substr($doctor->user->name ?? 'DR', 0, 2)) }}</div>
                                    <span class="fw-semibold">{{ $doctor->user->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td>{{ $doctor->speciality ?? '—' }}</td>
                            <td class="text-muted">{{ $doctor->doctor_code }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">এখনো কোনো ডাক্তার যোগ করা হয়নি।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel-card p-3">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">Quick Actions</h6>

            <a href="{{ route('admin.doctors.create') }}" class="quick-action">
                <i class="fa-solid fa-user-plus"></i> নতুন ডাক্তার যোগ করুন
            </a>
            <a href="{{ route('admin.chambers.create') }}" class="quick-action">
                <i class="fa-solid fa-hospital"></i> নতুন চেম্বার তৈরি করুন
            </a>
            <a href="{{ route('admin.templates.create') }}" class="quick-action">
                <i class="fa-solid fa-file-medical"></i> টেমপ্লেট তৈরি করুন
            </a>
        </div>
    </div>
</div>
@endsection