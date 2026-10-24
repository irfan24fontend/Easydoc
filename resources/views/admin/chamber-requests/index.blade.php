@extends('layouts.admin')

@section('content')
<style>
    .cr-head h1 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; }
    .cr-head p { color: #64748b; font-size: .85rem; margin: .25rem 0 0; }

    .cr-tabs { display: flex; flex-wrap: wrap; gap: .5rem; }
    .cr-tab {
        display: inline-flex; align-items: center; gap: 8px; border: 1px solid #e8ebf1; background: #fff;
        color: #475569; font-size: .83rem; font-weight: 700; padding: .55rem .95rem; border-radius: 12px;
        text-decoration: none; transition: all .15s;
    }
    .cr-tab:hover { border-color: #c7d2fe; background: #fafbff; color: #4338ca; }
    .cr-tab .c { background: #f1f5f9; color: #64748b; border-radius: 999px; padding: .05rem .55rem; font-size: .74rem; font-weight: 800; }
    .cr-tab.active { background: linear-gradient(135deg,#4f46e5,#4338ca); border-color: transparent; color: #fff; box-shadow: 0 6px 14px rgba(79,70,229,.25); }
    .cr-tab.active .c { background: rgba(255,255,255,.22); color: #fff; }

    .cr-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); overflow: hidden; }
    .cr-table thead th { background: #f8fafc; color: #64748b; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #eef0f4; padding: .85rem 1.1rem; }
    .cr-table td { padding: .9rem 1.1rem; vertical-align: middle; font-size: .87rem; border-bottom: 1px solid #f3f5f8; }
    .cr-table tbody tr:last-child td { border-bottom: 0; }
    .cr-table tbody tr:hover { background: #fafbff; }
    .cr-doc { font-weight: 700; color: #0f172a; }
    .cr-chamber { font-weight: 700; color: #0f172a; }
    .cr-hospital { font-size: .76rem; color: #94a3b8; }
    .cr-review {
        display: inline-flex; align-items: center; gap: 6px; font-size: .8rem; font-weight: 800; color: #4f46e5;
        text-decoration: none; background: #eef2ff; padding: .4rem .85rem; border-radius: 10px; transition: background .15s;
    }
    .cr-review:hover { background: #e0e7ff; color: #4338ca; }
    .cr-empty { padding: 3rem 1rem; text-align: center; color: #94a3b8; }
    .cr-empty i { font-size: 2rem; display: block; margin-bottom: .6rem; color: #cbd5e1; }
</style>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 cr-head mb-4">
    <div>
        <h1>Chamber Requests</h1>
        <p>Doctor-দের পাঠানো নতুন চেম্বার রিকোয়েস্ট রিভিউ করুন, প্রেসক্রিপশন প্যাড দেখে টেমপ্লেট সেট করুন।</p>
    </div>
    <a href="{{ route('admin.chambers.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
        <i class="fa-solid fa-list me-1"></i> All Chambers
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:14px;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:14px;">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

{{-- Filter tabs --}}
<div class="cr-tabs mb-4">
    @foreach(['waiting_request' => ['Waiting request', 'fa-hourglass-half'], 'active' => ['Active', 'fa-circle-check'], 'inactive' => ['Inactive', 'fa-power-off'], 'rejected' => ['Rejected', 'fa-ban'], 'all' => ['All', 'fa-layer-group']] as $key => $meta)
        <a href="{{ route('admin.chamber-requests.index', ['filter' => $key]) }}" class="cr-tab {{ $filter === $key ? 'active' : '' }}">
            <i class="fa-solid {{ $meta[1] }}"></i> {{ $meta[0] }} <span class="c">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</div>

<div class="cr-card">
    <div class="table-responsive">
        <table class="table cr-table mb-0">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Chamber</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th class="text-end">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chambers as $chamber)
                    <tr>
                        <td class="cr-doc">{{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}</td>
                        <td>
                            <div class="cr-chamber">{{ $chamber->chamber_name }}</div>
                            <div class="cr-hospital">{{ $chamber->hospital_name }}</div>
                        </td>
                        <td class="text-muted">{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</td>
                        <td>@include('partials.chamber-status-badge', ['status' => $chamber->display_status])</td>
                        <td class="text-end">
                            <a href="{{ route('admin.chamber-requests.show', $chamber) }}" class="cr-review">
                                {{ $chamber->request_status === 'waiting_request' ? 'Review' : 'View' }} <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="cr-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                এই ভিউতে কোনো চেম্বার নেই।
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $chambers->links() }}</div>
@endsection