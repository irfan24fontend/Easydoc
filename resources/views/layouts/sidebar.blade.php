<div class="sidebar-container">
    <div>
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fa-solid fa-stethoscope"></i></div>
            <div>
                <h4>EasyDoc</h4>
                <span>ADMIN SUITE</span>
            </div>
        </div>

        <div class="p-3">
            <span class="nav-section-label">Core Management</span>

            <nav class="nav flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="side-link {{ Request::is('admin/dashboard') || Request::is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                @php $pendingDoctors = \App\Models\Doctor::where('approval_status', 'pending')->count(); @endphp
                <a href="{{ route('admin.doctors.index') }}" class="side-link {{ Request::is('admin/doctors*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-doctor"></i>
                    <span>Doctors</span>
                    @if($pendingDoctors > 0)
                        <span title="Approval pending" style="margin-left:auto;font-size:11px;font-weight:800;background:#f59e0b;color:#0f172a;padding:1px 8px;border-radius:999px;">{{ $pendingDoctors }}</span>
                    @endif
                </a>

                {{-- Chambers (submenu: All Chambers / Chamber Requests) --}}
                @php
                    $pendingChambers = \App\Models\Chamber::where('request_status', 'waiting_request')->count();
                    $chamberOpen     = Request::is('admin/chambers*') || Request::is('admin/chamber-requests*');
                @endphp
                <a href="#" role="button"
                   class="side-link has-sub {{ $chamberOpen ? 'open-parent' : '' }}"
                   data-submenu="chamberSubmenu"
                   aria-expanded="{{ $chamberOpen ? 'true' : 'false' }}" aria-controls="chamberSubmenu">
                    <i class="fa-solid fa-hospital"></i>
                    <span>Chambers</span>
                    @if($pendingChambers > 0)
                        <span class="pending-badge" title="Review pending">{{ $pendingChambers }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-down caret"></i>
                </a>
                <div class="side-submenu {{ $chamberOpen ? 'open' : '' }}" id="chamberSubmenu">
                    <div class="sub-inner">
                        <div class="sub-links">
                            <a href="{{ route('admin.chambers.index') }}" class="side-sublink {{ Request::is('admin/chambers*') ? 'active' : '' }}">
                                <i class="fa-solid fa-list"></i>
                                <span>All Chambers</span>
                            </a>
                            <a href="{{ route('admin.chamber-requests.index') }}" class="side-sublink {{ Request::is('admin/chamber-requests*') ? 'active' : '' }}">
                                <i class="fa-solid fa-inbox"></i>
                                <span>Chamber Requests</span>
                                @if($pendingChambers > 0)
                                    <span class="pending-badge">{{ $pendingChambers }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.templates.index') }}" class="side-link {{ Request::is('admin/templates*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-medical"></i>
                    <span>Prescription Templates</span>
                </a>

                {{-- Wallet Management (submenu: All Wallets / Invoices) --}}
                @php
                    $walletOpen = Request::is('admin/wallets*') || Request::is('admin/invoices*');
                @endphp
                <a href="#" role="button"
                   class="side-link has-sub {{ $walletOpen ? 'open-parent' : '' }}"
                   data-submenu="walletSubmenu"
                   aria-expanded="{{ $walletOpen ? 'true' : 'false' }}" aria-controls="walletSubmenu">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Wallet Management</span>
                    <i class="fa-solid fa-chevron-down caret"></i>
                </a>
                <div class="side-submenu {{ $walletOpen ? 'open' : '' }}" id="walletSubmenu">
                    <div class="sub-inner">
                        <div class="sub-links">
                            <a href="{{ route('admin.wallets.index') }}" class="side-sublink {{ Request::is('admin/wallets*') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-doctor"></i>
                                <span>All Wallets</span>
                            </a>
                            <a href="{{ route('admin.invoices.index') }}" class="side-sublink {{ Request::is('admin/invoices*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Invoices</span>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.medicines.index') }}" class="side-link {{ Request::is('admin/medicines*') ? 'active' : '' }}">
                    <i class="fa-solid fa-pills"></i>
                    <span>Medicines</span>
                </a>

                <a href="#" class="side-link disabled">
                    <i class="fa-solid fa-user-injured"></i>
                    <span>Patients</span>
                    <span class="soon-badge">Soon</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-3 px-2 py-1">
            <div class="footer-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</div>
            <div class="overflow-hidden">
                <p class="footer-name">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="footer-email">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</div>

<style>
    .sidebar-container {
        width: 260px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030;
        display: flex; flex-direction: column; justify-content: space-between;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        box-shadow: 4px 0 20px rgba(0,0,0,.08);
        overflow-y: auto;
    }

    .sidebar-brand {
        padding: 1.4rem 1.25rem; display: flex; align-items: center; gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .brand-icon {
        width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg,#4f46e5,#4338ca);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.05rem;
        box-shadow: 0 4px 10px rgba(79,70,229,.3);
    }
    .sidebar-brand h4 { margin: 0; color: #fff; font-weight: 800; font-size: 1.1rem; letter-spacing: -0.02em; }
    .sidebar-brand span { color: #6366f1; font-size: 10px; font-weight: 700; letter-spacing: .1em; }

    .nav-section-label {
        display: block; text-transform: uppercase; font-size: 10.5px; font-weight: 700; letter-spacing: .08em;
        color: #64748b; padding: 0 0.5rem; margin-bottom: 0.6rem;
    }

    .side-link {
        display: flex; align-items: center; gap: 12px; padding: 0.65rem 0.85rem; border-radius: 10px;
        color: #cbd5e1; text-decoration: none; font-size: 0.85rem; font-weight: 500; margin-bottom: 2px;
        transition: all .15s ease; position: relative;
    }
    .side-link i { width: 18px; text-align: center; font-size: 0.9rem; }
    .side-link:hover { background: rgba(255,255,255,.06); color: #fff; }
    .side-link.active { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 4px 10px rgba(79,70,229,.25); }
    .side-link.disabled { opacity: 0.45; cursor: not-allowed; }
    .soon-badge { margin-left: auto; font-size: 9px; font-weight: 700; background: rgba(255,255,255,.1); padding: 2px 6px; border-radius: 999px; }
    .pending-badge { margin-left: auto; font-size: 11px; font-weight: 800; background: #f59e0b; color: #0f172a; padding: 1px 8px; border-radius: 999px; }
    .side-link .pending-badge + .caret { margin-left: 0; }

    /* ---- Submenus (Bootstrap JS chara kaj kore) ---- */
    .side-link.has-sub { cursor: pointer; user-select: none; }
    .side-link.has-sub .caret { margin-left: auto; width: auto; font-size: 0.65rem; transition: transform .2s ease; }
    .side-link.has-sub .pending-badge ~ .caret { margin-left: 8px; }
    .side-link.has-sub[aria-expanded="true"] .caret { transform: rotate(180deg); }
    .side-link.open-parent { background: rgba(255,255,255,.06); color: #fff; }

    .side-submenu { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .22s ease; }
    .side-submenu.open { grid-template-rows: 1fr; }
    .side-submenu > .sub-inner { overflow: hidden; min-height: 0; }

    .sub-links { margin: 2px 0 6px 20px; padding-left: 12px; border-left: 1px solid rgba(255,255,255,.12); }
    .side-sublink {
        display: flex; align-items: center; gap: 10px; padding: 0.5rem 0.75rem; border-radius: 8px;
        color: #cbd5e1; text-decoration: none; font-size: 0.8rem; font-weight: 500; margin-bottom: 2px;
        transition: all .15s ease;
    }
    .side-sublink i { width: 16px; text-align: center; font-size: 0.78rem; }
    .side-sublink:hover { background: rgba(255,255,255,.06); color: #fff; }
    .side-sublink.active { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 4px 10px rgba(79,70,229,.25); }

    .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,.08); background: rgba(0,0,0,.15); }
    .footer-avatar {
        width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,.1); color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;
    }
    .footer-name { margin: 0; color: #fff; font-weight: 600; font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .footer-email { margin: 0; color: #64748b; font-size: 10.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .logout-btn {
        width: 100%; background: rgba(220,38,38,.12); color: #f87171; border: 1px solid rgba(220,38,38,.25);
        border-radius: 8px; padding: 0.45rem; font-size: 0.75rem; font-weight: 600;
    }
    .logout-btn:hover { background: #dc2626; color: #fff; }

    .main-content { margin-left: 260px; min-height: 100vh; background-color: #f8fafc; }

    @media (max-width: 991px) {
        .sidebar-container { transform: translateX(-100%); }
        .main-content { margin-left: 0; }
    }
    @media (prefers-reduced-motion: reduce) { .side-submenu, .side-link.has-sub .caret { transition: none; } }
</style>

<script>
    (function () {
        document.querySelectorAll('.side-link[data-submenu]').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var menu = document.getElementById(toggle.getAttribute('data-submenu'));
                if (!menu) { return; }
                var open = menu.classList.toggle('open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    })();
</script>