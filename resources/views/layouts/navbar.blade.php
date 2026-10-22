<nav class="topbar">
    <div class="topbar-left">
    <button type="button" class="topbar-toggle" data-sidebar-toggle aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="topbar-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search doctors, chambers, templates...">
    </div>
    </div>

    <div class="topbar-right">
        <button class="icon-pill" title="Notifications">
            <i class="fa-regular fa-bell"></i>
        </button>

        <div class="dropdown">
            <button class="profile-pill" data-bs-toggle="dropdown">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</div>
                <div class="d-none d-md-block text-start">
                    <div class="profile-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="profile-role">Super Admin</div>
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

    .topbar-search { position: relative; width: 340px; max-width: 45vw; }
    .topbar-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; }
    .topbar-search input {
        width: 100%; padding: 0.55rem 0.9rem 0.55rem 2.3rem; border-radius: 10px; border: 1px solid #e2e8f0;
        font-size: 0.85rem; background: #f8fafc;
    }
    .topbar-search input:focus { outline: none; border-color: #4f46e5; background: #fff; }

    .topbar-right { display: flex; align-items: center; gap: 10px; }

    .icon-pill {
        width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff;
        color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .icon-pill:hover { background: #f8fafc; }

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

    /* ---- responsive top bar ---- */
    .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
    .topbar-toggle {
        display: none; flex: none; width: 42px; height: 42px; border-radius: 10px;
        border: 1px solid #e2e8f0; background: #fff; color: #334155; font-size: 1.05rem;
        align-items: center; justify-content: center; cursor: pointer;
    }
    .topbar-toggle:hover { background: #f1f5f9; }
    .topbar-toggle:focus-visible { outline: 3px solid #c7d2fe; outline-offset: 2px; }

    @media (max-width: 991px) {
        .topbar { padding: 0.65rem 1rem; }
        .topbar-toggle { display: inline-flex; }
    }
    @media (max-width: 575px) {
        .topbar-search { display: none; }
        .topbar-right { gap: 6px; }
        .profile-pill .fa-chevron-down { display: none; }
    }
</style>