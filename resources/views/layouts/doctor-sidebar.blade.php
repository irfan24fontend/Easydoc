@php
    $doctor = auth()->user()->doctor;
    $openQueue = $doctor
        ? \App\Models\Serial::where('doctor_id', $doctor->id)
            ->where('serial_date', \App\Services\SerialService::today())
            ->whereIn('status', ['waiting', 'in_progress'])
            ->count()
        : 0;

    // Create ar List alada alada highlight hobe
    $isCreateRx = Request::routeIs('doctor.prescriptions.create');
    $profileOpen = Request::is('doctor/public-profile*');
    $hasPublicProfile = (bool) $doctor?->profile()->exists();
    $isListRx   = Request::routeIs('doctor.prescriptions.index')
               || Request::routeIs('doctor.prescriptions.show')
               || Request::routeIs('doctor.prescriptions.edit');
@endphp

<div class="sidebar-container">
    <div class="sidebar-top">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fa-solid fa-stethoscope"></i></div>
            <div>
                <h4>EasyDoc</h4>
                <span>DOCTOR PANEL</span>
            </div>
        </div>

        <div class="p-3">
            <span class="nav-section-label">Main Menu</span>

            <nav class="nav flex-column gap-1">
                <a href="{{ route('doctor.dashboard') }}" class="side-link {{ Request::is('doctor/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('doctor.queue.index') }}" class="side-link {{ Request::is('doctor/queue*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol"></i>
                    <span>আজকের কিউ</span>
                    @if($openQueue > 0)
                        <span class="count-badge">{{ $openQueue }}</span>
                    @endif
                </a>

                <a href="{{ route('doctor.prescriptions.create') }}" class="side-link {{ $isCreateRx ? 'active' : '' }}">
                    <i class="fa-solid fa-file-medical"></i>
                    <span>Create Prescription</span>
                </a>

                <a href="{{ route('doctor.prescriptions.index') }}" class="side-link {{ $isListRx ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Prescription List</span>
                </a>

                <a href="{{ route('doctor.assistants.index') }}" class="side-link {{ Request::is('doctor/assistants*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i>
                    <span>আমার সহকারী</span>
                </a>

                <a href="{{ route('doctor.patients.index') }}" class="side-link {{ Request::is('doctor/patients*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-injured"></i>
                    <span>Patients</span>
                </a>

                <a href="{{ route('doctor.chambers.index') }}" class="side-link {{ Request::is('doctor/chambers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hospital"></i>
                    <span>My Chambers</span>
                </a>

                {{-- Public Profile (submenu: Create / Manage) --}}
                <a href="#"
                   class="side-link has-sub {{ $profileOpen ? 'open-parent' : '' }}"
                   data-submenu="publicProfileMenu" role="button"
                   aria-expanded="{{ $profileOpen ? 'true' : 'false' }}" aria-controls="publicProfileMenu">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Public Profile</span>
                    <i class="fa-solid fa-chevron-down caret"></i>
                </a>
                <div class="submenu {{ $profileOpen ? 'open' : '' }}" id="publicProfileMenu">
                    <div class="sub-links">
                        @if($hasPublicProfile)
                            <span class="side-sublink disabled" title="আপনার প্রোফাইল তৈরি করা আছে">
                                <i class="fa-solid fa-circle-plus"></i>
                                <span>Create</span>
                                <span class="soon-badge">Done</span>
                            </span>
                        @else
                            <a href="{{ route('doctor.public-profile.create') }}" class="side-sublink {{ Request::is('doctor/public-profile/create') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle-plus"></i>
                                <span>Create</span>
                            </a>
                        @endif
                        <a href="{{ route('doctor.public-profile.edit') }}" class="side-sublink {{ Request::is('doctor/public-profile') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Manage</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('doctor.favorites.index') }}" class="side-link {{ Request::is('doctor/favorites*') ? 'active' : '' }}">
                    <i class="fa-solid fa-star"></i>
                    <span>Favorite Medicines</span>
                </a>

                <a href="#" class="side-link disabled">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Wallet & Recharge</span>
                    <span class="soon-badge">Soon</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="sidebar-footer">
        {{-- Wallet quick view --}}
        <div class="wallet-mini mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="wallet-mini-label"><i class="fa-solid fa-wallet me-1"></i> <span class="wm-text">Balance</span></span>
                <span class="wallet-mini-amount">৳{{ number_format(optional($doctor?->wallet)->balance ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 px-2 py-1">
            <div class="footer-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'DR', 0, 2)) }}</div>
            <div class="overflow-hidden">
                <p class="footer-name">Dr. {{ auth()->user()->name ?? '' }}</p>
                <p class="footer-email">{{ $doctor->speciality ?? auth()->user()->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="logout-text">Logout</span>
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
        overflow: hidden;
        transition: width .2s ease, transform .25s ease;
    }

    /* Menu beshi hole upor-er ongsho scroll hobe, footer sobsomoy niche thakbe */
    .sidebar-top { flex: 1 1 auto; min-height: 0; overflow-y: auto; }
    .sidebar-top::-webkit-scrollbar { width: 4px; }
    .sidebar-top::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

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
        transition: all .15s ease;
    }
    .side-link i { width: 18px; text-align: center; font-size: 0.9rem; }
    .side-link:hover { background: rgba(255,255,255,.06); color: #fff; }
    .side-link.active { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 4px 10px rgba(79,70,229,.25); }
    .side-link.disabled { opacity: 0.45; cursor: not-allowed; }
    .soon-badge { margin-left: auto; font-size: 9px; font-weight: 700; background: rgba(255,255,255,.1); padding: 2px 6px; border-radius: 999px; }
    .count-badge { margin-left: auto; font-size: 11px; font-weight: 800; background: #f59e0b; color: #0f172a; padding: 1px 8px; border-radius: 999px; }

    /* Submenu (own toggle, does not depend on Bootstrap collapse) */
    .submenu { display: none; }
    .submenu.open { display: block; }
    .side-link.has-sub .caret { margin-left: auto; width: auto; font-size: 0.65rem; transition: transform .2s ease; }
    .side-link.has-sub[aria-expanded="true"] .caret { transform: rotate(180deg); }
    .side-link.open-parent { background: rgba(255,255,255,.06); color: #fff; }
    .sub-links { margin: 2px 0 6px 20px; padding-left: 12px; border-left: 1px solid rgba(255,255,255,.12); }
    .side-sublink {
        display: flex; align-items: center; gap: 10px; padding: 0.5rem 0.75rem; border-radius: 8px;
        color: #cbd5e1; text-decoration: none; font-size: 0.8rem; font-weight: 500; margin-bottom: 2px;
        transition: all .15s ease;
    }
    .side-sublink i { width: 16px; text-align: center; font-size: 0.78rem; }
    .side-sublink:hover { background: rgba(255,255,255,.06); color: #fff; }
    .side-sublink.active { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; box-shadow: 0 4px 10px rgba(79,70,229,.25); }
    .side-sublink.disabled { opacity: .5; cursor: default; }
    .side-sublink.disabled:hover { background: transparent; color: #cbd5e1; }

    .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,.08); background: rgba(0,0,0,.15); }

    .wallet-mini { background: rgba(79,70,229,.15); border: 1px solid rgba(99,102,241,.3); border-radius: 10px; padding: 0.7rem 0.9rem; }
    .wallet-mini-label { font-size: 0.72rem; color: #a5b4fc; font-weight: 600; }
    .wallet-mini-amount { font-size: 0.95rem; color: #fff; font-weight: 800; }

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

    .main-content { margin-left: 260px; min-height: 100vh; background-color: #f8fafc; transition: margin-left .2s ease; }

    .side-link { position: relative; }

    /* ---------- Overlay (mobile drawer) ---------- */
    .sidebar-overlay {
        display: none; position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 1025;
        backdrop-filter: blur(1px);
    }

    /* ---------- Desktop: collapsed (icon rail) ---------- */
    @media (min-width: 992px) {
        html.sb-collapsed .sidebar-container { width: 76px; }
        html.sb-collapsed .main-content { margin-left: 76px; }

        html.sb-collapsed .sidebar-brand { padding: 1.4rem 0; justify-content: center; }
        html.sb-collapsed .sidebar-brand > div:last-child,
        html.sb-collapsed .nav-section-label { display: none; }

        html.sb-collapsed .sidebar-top > .p-3 { padding-left: .6rem !important; padding-right: .6rem !important; }

        html.sb-collapsed .side-link { justify-content: center; padding: .7rem 0; gap: 0; }
        html.sb-collapsed .side-link i { width: auto; font-size: 1rem; }
        html.sb-collapsed .side-link > span:not(.count-badge) { display: none; }
        html.sb-collapsed .soon-badge { display: none; }
        html.sb-collapsed .side-link.has-sub .caret,
        html.sb-collapsed .sub-links { display: none; }
        html.sb-collapsed .count-badge {
            position: absolute; top: 2px; right: 6px; margin: 0; font-size: 9px; padding: 0 5px; line-height: 15px;
        }

        html.sb-collapsed .sidebar-footer { padding: .75rem .5rem; }
        html.sb-collapsed .wallet-mini { padding: .55rem 0; text-align: center; }
        html.sb-collapsed .wallet-mini .d-flex { justify-content: center !important; }
        html.sb-collapsed .wallet-mini-amount,
        html.sb-collapsed .wm-text { display: none; }
        html.sb-collapsed .wallet-mini-label i { margin: 0 !important; font-size: 1rem; }
        html.sb-collapsed .sidebar-footer .d-flex.align-items-center.gap-3 { justify-content: center; padding-left: 0 !important; padding-right: 0 !important; }
        html.sb-collapsed .sidebar-footer .overflow-hidden { display: none; }
        html.sb-collapsed .logout-text { display: none; }
    }

    /* ---------- Mobile / tablet: off-canvas drawer ---------- */
    @media (max-width: 991px) {
        .sidebar-container { transform: translateX(-100%); width: 270px; box-shadow: none; }
        html.sb-open .sidebar-container { transform: translateX(0); box-shadow: 8px 0 30px rgba(0,0,0,.25); }
        html.sb-open .sidebar-overlay { display: block; }
        .main-content { margin-left: 0; }
    }
</style>

<script>
    // Submenu toggle: stays open until the doctor closes it, and remembers the choice while navigating
    (function () {
        document.querySelectorAll('[data-submenu]').forEach(function (btn) {
            var box = document.getElementById(btn.getAttribute('data-submenu'));
            if (!box) return;

            var key = 'easydoc.submenu.' + box.id;
            var onThisSection = box.classList.contains('open');   // current page belongs to this submenu
            var saved = null;
            try { saved = sessionStorage.getItem(key); } catch (e) {}
            if (!onThisSection && saved === '1') box.classList.add('open');

            function sync() {
                var open = box.classList.contains('open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                btn.classList.toggle('open-parent', open);
            }
            sync();

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                box.classList.toggle('open');
                try { sessionStorage.setItem(key, box.classList.contains('open') ? '1' : '0'); } catch (er) {}
                sync();
            });
        });
    })();
</script>
