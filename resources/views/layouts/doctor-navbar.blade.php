@php
    $doctor = auth()->user()->doctor;
    // Super Admin Active + doctor nije chalu rekheche, duitoi hole chamber "chalu"
    $activeChamber = $doctor?->activeChamber();
@endphp

<nav class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="icon-pill sidebar-toggle" data-sidebar-toggle aria-label="Sidebar toggle" aria-expanded="true" title="Sidebar খুলুন/বন্ধ করুন">
            <i class="fa-solid fa-bars"></i>
        </button>

        @if($activeChamber)
            <span class="chamber-chip">
                <i class="fa-solid fa-hospital me-1"></i> {{ $activeChamber->chamber_name }}
            </span>
        @else
            <span class="chamber-chip chip-warning">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> No Active Chamber
            </span>
        @endif
    </div>

    <div class="topbar-right">
        <button class="icon-pill" title="Notifications">
            <i class="fa-regular fa-bell"></i>
        </button>

        <div class="dropdown">
            <button class="profile-pill" data-bs-toggle="dropdown">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'DR', 0, 2)) }}</div>
                <div class="d-none d-md-block text-start">
                    <div class="profile-name">Dr. {{ auth()->user()->name ?? '' }}</div>
                    <div class="profile-role">{{ $doctor->speciality ?? 'Doctor' }}</div>
                </div>
                <i class="fa-solid fa-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .topbar {
        background: #fff; border-bottom: 1px solid #eef0f4; padding: 0.85rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        position: sticky; top: 0; z-index: 1020;
    }

    .chamber-chip {
        font-size: 0.8rem; font-weight: 700; background: #eef2ff; color: #4338ca;
        padding: 0.45rem 0.9rem; border-radius: 999px;
    }
    .chamber-chip.chip-warning { background: #fef3c7; color: #92400e; }

    .topbar-right { display: flex; align-items: center; gap: 10px; }

    .icon-pill {
        width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff;
        color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .icon-pill:hover { background: #f8fafc; }
    .sidebar-toggle { flex-shrink: 0; }
    .sidebar-toggle:hover { color: #4f46e5; border-color: #c7d2fe; background: #eef2ff; }

    .profile-pill {
        display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 999px; padding: 0.35rem 0.9rem 0.35rem 0.35rem;
    }
    .profile-avatar {
        width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#4f46e5,#4338ca);
        color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;
    }
    .profile-name { font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
    .profile-role { font-size: 0.7rem; color: #94a3b8; }
</style>