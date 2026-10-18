@extends('layouts.admin')

@section('title', 'Doctors Roster')

@section('content')
<style>
    .roster-workspace { 
        background-color: #f8fafc; 
        min-height: 100vh; 
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .saas-table th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        background-color: #f1f5f9;
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }
    .saas-table td {
        font-size: 0.875rem;
        color: #334155;
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .saas-table tr:hover td { 
        background-color: #f8fafc; 
    }
    .btn-action-text {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }
    .btn-action-text.view { color: #4f46e5; background-color: #eeebff; border: 1px solid #e0e7ff; }
    .btn-action-text.view:hover { background-color: #4f46e5; color: #ffffff; }
    
    .btn-action-text.edit { color: #b45309; background-color: #fef3c7; border: 1px solid #fde68a; }
    .btn-action-text.edit:hover { background-color: #d97706; color: #ffffff; }
    
    .btn-action-text.delete { color: #dc2626; background-color: #fee2e2; border: 1px solid #fecaca; }
    .btn-action-text.delete:hover { background-color: #dc2626; color: #ffffff; }
</style>

<div class="roster-workspace py-4">
    <div class="container-fluid px-4">

        <!-- Top Header Panel -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Doctors</h1>
                <p class="text-muted small m-0">Manage active medical personnel records.</p>
            </div>
            <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary px-4 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
                + Add Doctor
            </a>
        </div>

        <!-- Approval filter -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            @php
                $tabs = [
                    ['key' => null,       'label' => 'All',      'count' => $counts['all']],
                    ['key' => 'pending',  'label' => 'Pending review', 'count' => $counts['pending']],
                    ['key' => 'approved', 'label' => 'Approved', 'count' => $counts['approved']],
                    ['key' => 'rejected', 'label' => 'Rejected', 'count' => $counts['rejected']],
                ];
            @endphp
            @foreach($tabs as $tab)
                <a href="{{ route('admin.doctors.index', $tab['key'] ? ['status' => $tab['key']] : []) }}"
                   class="btn btn-sm rounded-pill px-3 {{ ($status ?? null) === $tab['key'] ? 'btn-dark' : 'btn-light border' }}">
                    {{ $tab['label'] }}
                    <span class="badge {{ $tab['key'] === 'pending' && $tab['count'] > 0 ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }} ms-1">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Roster Data Grid -->
        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table saas-table m-0 border-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Speciality</th>
                            <th>BMDC ID</th>
                            <th>Status</th>
                            <th width="220" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                            <tr>
                                <td class="text-muted fw-bold">
                                    {{ $loop->iteration }}
                                </td>
                                
                                <td>
                                    @if($doctor->photo)
                                        <img src="{{ asset('storage/'.$doctor->photo) }}" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-circle border d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 40px; height: 40px; font-size: 11px;">
                                            N/A
                                        </div>
                                    @endif
                                </td>

                                <td class="fw-bold text-dark">
                                    {{ $doctor->user->name }}
                                    <div class="text-muted fw-normal" style="font-size: 0.75rem;">{{ $doctor->user->email }}</div>
                                </td>

                                <td class="text-secondary fw-medium">
                                    {{ $doctor->user->phone ?? '—' }}
                                </td>

                                <td>
                                    <!-- High contrast, readable badge color -->
                                    <span class="badge bg-secondary text-white px-2.5 py-1.5 rounded" style="font-size: 0.8rem; background-color: #64748b !important;">
                                        {{ $doctor->speciality ?? 'General' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="font-monospace fw-bold text-primary px-2 py-1 rounded" style="font-size: 0.85rem; background-color: #eff6ff; color: #1d4ed8 !important;">
                                        {{ $doctor->bmdc_number ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    @if($doctor->isPending())
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 600;">Pending review</span>
                                    @elseif($doctor->isRejected())
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 600;">Rejected</span>
                                    @else
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 600;">Approved</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn-action-text view">
                                            {{ $doctor->isPending() ? 'Review' : 'View' }}
                                        </a>

                                        <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn-action-text edit">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete Doctor?')" class="btn-action-text delete border-0">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted fw-medium">
                                    No Doctor Found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($doctors->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $doctors->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection