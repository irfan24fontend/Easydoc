@extends('layouts.admin')

@section('title', 'Chambers')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .workspace { background-color: #f8fafc; min-height: 100vh; }

    .page-head { margin-bottom: 1.75rem; }
    .page-head h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0; }

    .btn-brand {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: #fff !important; border: none; border-radius: 10px;
        font-weight: 600; font-size: 0.875rem; padding: 0.65rem 1.25rem;
        box-shadow: 0 4px 10px rgba(79,70,229,.25);
    }
    .btn-brand:hover { filter: brightness(1.05); color: #fff; }

    /* Stat cards */
    .stat-card {
        background: #fff; border: 1px solid #eef0f4; border-radius: 14px;
        padding: 1.1rem 1.3rem; display: flex; align-items: center; gap: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,.03);
    }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px; display: flex;
        align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    }
    .stat-num { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .stat-label { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 2px; }

    /* Table card */
    .table-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .table-toolbar { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .search-box { position: relative; width: 280px; max-width: 100%; }
    .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; }
    .search-box input {
        width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.1rem; border-radius: 10px;
        border: 1px solid #e2e8f0; font-size: 0.85rem; background: #f8fafc;
    }
    .search-box input:focus { outline: none; border-color: #4f46e5; background: #fff; }

    .saas-table { width: 100%; border-collapse: collapse; }
    .saas-table thead th {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #64748b; background: #f8fafc; padding: 0.85rem 1.25rem; border-bottom: 1px solid #eef0f4; text-align: left;
    }
    .saas-table tbody td { padding: 0.95rem 1.25rem; font-size: 0.875rem; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .saas-table tbody tr:hover { background: #fafbff; }
    .saas-table tbody tr:last-child td { border-bottom: none; }

    .avatar-circle {
        width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg,#eef2ff,#e0e7ff);
        color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;
    }
    .doctor-name { font-weight: 700; color: #0f172a; font-size: 0.85rem; }
    .doctor-sub { font-size: 0.75rem; color: #94a3b8; }

    .pill { font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.7rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px; }
    .pill-active { background: #dcfce7; color: #15803d; }
    .pill-inactive { background: #f1f5f9; color: #64748b; }
    .pill-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .tpl-badge { font-size: 0.72rem; font-weight: 600; background: #f5f3ff; color: #6d28d9; padding: 0.25rem 0.6rem; border-radius: 6px; }

    .icon-btn {
        width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;
        display: inline-flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.8rem; transition: .15s;
    }
    .icon-btn:hover { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .icon-btn.danger:hover { background: #dc2626; border-color: #dc2626; }

    .empty-state { text-align: center; padding: 4rem 1rem; }
    .empty-state i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem; }
    .empty-state p { color: #94a3b8; font-size: 0.9rem; margin: 0; }
</style>

<div class="workspace py-4">
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 page-head">
            <div>
                <h1>চেম্বার ব্যবস্থাপনা <span class="text-muted fw-normal" style="font-size:1rem;">/ Chambers</span></h1>
                <p>ডাক্তারদের সব চেম্বার ও প্রেসক্রিপশন টেমপ্লেট এক জায়গা থেকে নিয়ন্ত্রণ করুন।</p>
            </div>
            <a href="{{ route('admin.chambers.create') }}" class="btn btn-brand">
                <i class="fa-solid fa-plus me-1"></i> নতুন চেম্বার যোগ করুন
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius:10px;">
                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="fa-solid fa-hospital"></i></div>
                    <div>
                        <div class="stat-num">{{ $chambers->total() }}</div>
                        <div class="stat-label">মোট চেম্বার</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="fa-solid fa-toggle-on"></i></div>
                    <div>
                        <div class="stat-num">{{ $chambers->where('status', true)->count() }}</div>
                        <div class="stat-label">Active চেম্বার (এই পেজে)</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="fa-solid fa-file-medical"></i></div>
                    <div>
                        <div class="stat-num">{{ $chambers->whereNotNull('prescription_template_id')->count() }}</div>
                        <div class="stat-label">টেমপ্লেট অ্যাসাইন করা</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="chamberSearch" placeholder="ডাক্তার বা চেম্বারের নাম দিয়ে খুঁজুন...">
                </div>
                <span class="text-muted small">{{ $chambers->total() }} টি রেকর্ড</span>
            </div>

            <div class="table-responsive">
                <table class="saas-table" id="chamberTable">
                    <thead>
                        <tr>
                            <th>ডাক্তার</th>
                            <th>চেম্বার</th>
                            <th>হাসপাতাল/ক্লিনিক</th>
                            <th>টেমপ্লেট</th>
                            <th>ফোন</th>
                            <th>স্ট্যাটাস</th>
                            <th class="text-end">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chambers as $chamber)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle">
                                            {{ strtoupper(substr($chamber->doctor->user->name ?? 'DR', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="doctor-name">{{ $chamber->doctor->user->name ?? '—' }}</div>
                                            <div class="doctor-sub">{{ $chamber->doctor->doctor_code ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="fw-semibold text-dark">{{ $chamber->chamber_name }}</td>

                                <td class="text-secondary">{{ $chamber->hospital_name }}</td>

                                <td>
                                    @if($chamber->template)
                                        <span class="tpl-badge"><i class="fa-solid fa-file-lines me-1"></i>{{ $chamber->template->name }}</span>
                                    @else
                                        <span class="text-muted small">অ্যাসাইন করা হয়নি</span>
                                    @endif
                                </td>

                                <td class="text-secondary">{{ $chamber->phone }}</td>

                                <td>
                                    @if($chamber->status)
                                        <span class="pill pill-active"><span class="pill-dot"></span> Active</span>
                                    @else
                                        <span class="pill pill-inactive"><span class="pill-dot"></span> Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.chambers.edit', $chamber->id) }}" class="icon-btn" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.chambers.destroy', $chamber->id) }}" method="POST" onsubmit="return confirm('এই চেম্বারটি ডিলিট করতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn danger border-0" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-hospital-user"></i>
                                        <p>এখনো কোনো চেম্বার যোগ করা হয়নি।</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($chambers->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $chambers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.getElementById('chamberSearch').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#chamberTable tbody tr').forEach(function (row) {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endsection