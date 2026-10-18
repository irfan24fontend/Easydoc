@extends('layouts.admin')

@section('title', 'Medicines')

@section('content')

<style>
    .page-head h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0; }

    .btn-brand {
        background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff !important; border: none; border-radius: 10px;
        font-weight: 600; font-size: 0.875rem; padding: 0.65rem 1.25rem; box-shadow: 0 4px 10px rgba(79,70,229,.25);
    }
    .btn-brand:hover { filter: brightness(1.05); color: #fff; }

    .stat-card { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 1.1rem 1.3rem; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .stat-num { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .stat-label { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 2px; }

    .table-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .table-toolbar { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .search-box { position: relative; width: 300px; max-width: 100%; }
    .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; }
    .search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.1rem; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem; background: #f8fafc; }
    .search-box input:focus { outline: none; border-color: #4f46e5; background: #fff; }

    .saas-table { width: 100%; border-collapse: collapse; }
    .saas-table thead th { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; background: #f8fafc; padding: 0.85rem 1.1rem; border-bottom: 1px solid #eef0f4; text-align: left; }
    .saas-table tbody td { padding: 0.9rem 1.1rem; font-size: 0.85rem; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .saas-table tbody tr:hover { background: #fafbff; }
    .saas-table tbody tr:last-child td { border-bottom: none; }

    .med-name { font-weight: 700; color: #0f172a; font-size: 0.85rem; }
    .med-generic { font-size: 0.75rem; color: #94a3b8; }

    .pill { font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.7rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px; }
    .pill-active { background: #dcfce7; color: #15803d; }
    .pill-inactive { background: #f1f5f9; color: #64748b; }

    .sponsor-badge {
        font-size: 0.7rem; font-weight: 700; background: linear-gradient(135deg,#fef3c7,#fde68a); color: #92400e;
        padding: 0.3rem 0.6rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 4px;
    }
    .not-sponsored { font-size: 0.75rem; color: #cbd5e1; }

    .icon-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; display: inline-flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.8rem; transition: .15s; }
    .icon-btn:hover { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .icon-btn.danger:hover { background: #dc2626; border-color: #dc2626; }

    .empty-state { text-align: center; padding: 4rem 1rem; }
    .empty-state i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem; }
    .empty-state p { color: #94a3b8; font-size: 0.9rem; margin: 0; }
</style>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 page-head mb-4">
    <div>
        <h1>ঔষধ ব্যবস্থাপনা <span class="text-muted fw-normal" style="font-size:1rem;">/ Medicines</span></h1>
        <p>Master medicine list এবং sponsored ব্র্যান্ড নিয়ন্ত্রণ করুন।</p>
    </div>
    <a href="{{ route('admin.medicines.create') }}" class="btn-brand">
        <i class="fa-solid fa-plus me-1"></i> নতুন মেডিসিন যোগ করুন
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius:10px;">
        <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="fa-solid fa-pills"></i></div>
            <div>
                <div class="stat-num">{{ $medicines->total() }}</div>
                <div class="stat-label">মোট মেডিসিন</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="fa-solid fa-star"></i></div>
            <div>
                <div class="stat-num">{{ $medicines->where('is_sponsored', true)->count() }}</div>
                <div class="stat-label">Sponsored (এই পেজে)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="fa-solid fa-toggle-on"></i></div>
            <div>
                <div class="stat-num">{{ $medicines->where('status', true)->count() }}</div>
                <div class="stat-label">Active (এই পেজে)</div>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-toolbar">
        <form method="GET" class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Brand বা Generic নাম দিয়ে খুঁজুন..." onchange="this.form.submit()">
        </form>
        <span class="text-muted small">{{ $medicines->total() }} টি রেকর্ড</span>
    </div>

    <div class="table-responsive">
        <table class="saas-table">
            <thead>
                <tr>
                    <th>মেডিসিন</th>
                    <th>Strength</th>
                    <th>Company</th>
                    <th>Form</th>
                    <th>Shortcut</th>
                    <th>Sponsorship</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $medicine)
                    <tr>
                        <td>
                            <div class="med-name">{{ $medicine->brand_name }}</div>
                            <div class="med-generic">{{ $medicine->generic_name }}</div>
                        </td>
                        <td>{{ $medicine->strength ?? '—' }}</td>
                        <td class="text-secondary">{{ $medicine->company ?? '—' }}</td>
                        <td><span class="text-secondary small">{{ $medicine->dosage_form }}</span></td>
                        <td>
                            @if($medicine->shortcut)
                                <code class="small">{{ $medicine->shortcut }}</code>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($medicine->is_sponsored)
                                <span class="sponsor-badge" title="{{ $medicine->sponsor_company }}">
                                    <i class="fa-solid fa-star"></i> Sponsored
                                </span>
                            @else
                                <span class="not-sponsored">— Not sponsored</span>
                            @endif
                        </td>
                        <td>
                            @if($medicine->status)
                                <span class="pill pill-active">Active</span>
                            @else
                                <span class="pill pill-inactive">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.medicines.edit', $medicine->id) }}" class="icon-btn" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.medicines.destroy', $medicine->id) }}" method="POST" onsubmit="return confirm('মেডিসিনটি ডিলিট করতে চান?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn danger border-0" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fa-solid fa-pills"></i>
                                <p>এখনো কোনো মেডিসিন যোগ করা হয়নি।</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($medicines->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $medicines->links() }}
        </div>
    @endif
</div>
@endsection