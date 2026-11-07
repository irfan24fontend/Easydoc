@php
    $doctor = auth()->user()->doctor;
    // Super Admin Active + doctor nije chalu rekheche, duitoi hole chamber "chalu"
    $activeChamber = $doctor?->activeChamber();
@endphp

<nav class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="icon-pill sidebar-toggle" data-sidebar-toggle aria-label="{{ __('Sidebar toggle') }}" aria-expanded="true" title="{{ __('Sidebar toggle') }}">
            <i class="fa-solid fa-bars"></i>
        </button>

        @if($activeChamber)
            <span class="chamber-chip">
                <i class="fa-solid fa-hospital me-1"></i> {{ $activeChamber->chamber_name }}
            </span>
        @else
            <span class="chamber-chip chip-warning">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('No Active Chamber') }}
            </span>
        @endif
    </div>

    <div class="topbar-right">
        @include('layouts.locale-switcher')
        <button class="icon-pill" title="{{ __('Notifications') }}">
            <i class="fa-regular fa-bell"></i>
        </button>

        <div class="dropdown">
            <button class="profile-pill" data-bs-toggle="dropdown">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'DR', 0, 2)) }}</div>
                <div class="d-none d-md-block text-start">
                    <div class="profile-name">Dr. {{ auth()->user()->name ?? '' }}</div>
                    <div class="profile-role">{{ $doctor->speciality ?? __('Doctor') }}</div>
                </div>
                <i class="fa-solid fa-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user me-2"></i>{{ __('Profile') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>{{ __('Logout') }}</button>
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
        font-size: 0.8rem; font-weight: 700; background: #e3f3f0; color: #0a6b65;
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
    .sidebar-toggle:hover { color: #0c8c84; border-color: #9bd2cc; background: #e3f3f0; }

    .profile-pill {
        display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 999px; padding: 0.35rem 0.9rem 0.35rem 0.35rem;
    }
    .profile-avatar {
        width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#0c8c84,#0a6b65);
        color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;
    }
    .profile-name { font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
    .profile-role { font-size: 0.7rem; color: #94a3b8; }

    @media (max-width: 575px) {
        .topbar { padding: 0.65rem 0.75rem; flex-wrap: wrap; }
        .topbar-right { margin-left: auto; gap: 6px; }
        .chamber-chip { max-width: calc(100vw - 85px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    }
</style>
