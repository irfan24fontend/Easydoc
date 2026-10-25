@php
    $u = auth()->user();
    $layout = $u->isSuperAdmin() ? 'layouts.admin' : ($u->isAssistant() ? 'layouts.assistant' : 'layouts.doctor');
    $roleLabel = $u->isSuperAdmin() ? 'Super Admin' : ($u->isAssistant() ? 'সহকারী' : 'ডাক্তার');
    $parts = preg_split('/\s+/u', trim($user->name)) ?: [];
    $initials = mb_strtoupper(mb_substr($parts[0] ?? 'U', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    $verified = ! ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) || $user->hasVerifiedEmail();
@endphp
@extends($layout)

@section('title', 'Profile')

@push('styles')
<style>
    .pf { --p1:#7c3aed; --p2:#06b6d4; --p3:#ec4899; --glass:rgba(255,255,255,.7); --line:rgba(124,58,237,.16); position:relative; isolation:isolate; max-width:1180px; margin:0 auto; }
    .pf::before { content:''; position:absolute; inset:-16px -10px; z-index:-1; border-radius:32px;
        background: radial-gradient(560px 300px at 6% 0%, rgba(124,58,237,.18), transparent 62%), radial-gradient(480px 300px at 96% 6%, rgba(6,182,212,.18), transparent 62%), radial-gradient(520px 380px at 50% 100%, rgba(236,72,153,.10), transparent 62%); }
    @keyframes pfFlow { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    @keyframes pfIn { from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:none} }

    .pf-hero { background:linear-gradient(120deg,#4c1d95,#7c3aed,#2563eb,#06b6d4); background-size:220% 220%; animation:pfFlow 14s ease infinite; border-radius:26px; padding:26px 28px; color:#fff; position:relative; overflow:hidden; border:1px solid rgba(255,255,255,.25); box-shadow:0 18px 50px rgba(124,58,237,.33), inset 0 1px 0 rgba(255,255,255,.35); margin-bottom:22px; display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
    .pf-hero::after { content:''; position:absolute; width:300px; height:300px; right:-80px; top:-140px; border-radius:50%; background:radial-gradient(circle, rgba(255,255,255,.35), transparent 65%); pointer-events:none; }
    .pf-avatar { width:78px; height:78px; border-radius:24px; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800; letter-spacing:.02em; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.5); backdrop-filter:blur(10px); box-shadow:0 0 0 6px rgba(255,255,255,.12), 0 10px 30px rgba(0,0,0,.2); flex-shrink:0; }
    .pf-hero h1 { font-size:24px; font-weight:800; margin:0; letter-spacing:-.01em; text-shadow:0 2px 18px rgba(0,0,0,.25); }
    .pf-hero p { margin:2px 0 0; color:rgba(255,255,255,.85); font-size:14px; word-break:break-all; }
    .pf-badges { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .pf-badge { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; padding:5px 12px; border-radius:999px; background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.35); backdrop-filter:blur(8px); }

    .pf-grid { display:grid; grid-template-columns:320px 1fr; gap:22px; align-items:start; }
    @media (max-width: 991px) { .pf-grid { grid-template-columns:1fr; } }

    .pf-card { background:var(--glass); -webkit-backdrop-filter:blur(18px) saturate(165%); backdrop-filter:blur(18px) saturate(165%); border:1px solid rgba(255,255,255,.8); border-radius:26px; padding:24px; box-shadow:0 10px 40px rgba(76,29,149,.09), inset 0 1px 0 rgba(255,255,255,.85); animation:pfIn .35s ease both; }
    .pf-card + .pf-card { margin-top:22px; }
    .pf-title { display:flex; align-items:center; gap:12px; margin-bottom:4px; }
    .pf-ico { width:40px; height:40px; border-radius:14px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px; background:linear-gradient(135deg,var(--p1),var(--p2)); box-shadow:0 8px 20px rgba(99,102,241,.35); flex-shrink:0; }
    .pf-ico.warn { background:linear-gradient(135deg,#f43f5e,#f97316); box-shadow:0 8px 20px rgba(244,63,94,.35); }
    .pf-title h2 { font-size:17px; font-weight:800; color:#0f172a; margin:0; letter-spacing:-.01em; }
    .pf-sub { color:#64748b; font-size:13px; margin:0 0 18px 52px; }
    @media (max-width: 575px) { .pf-sub { margin-left:0; margin-top:8px; } }

    .pf-label { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
    .pf-field { position:relative; margin-bottom:16px; }
    .pf-input { width:100%; background:rgba(255,255,255,.8); border:1px solid var(--line); border-radius:14px; padding:12px 14px 12px 42px; font-size:14px; color:#0f172a; transition:border-color .15s, box-shadow .15s; outline:none; }
    .pf-input.pr { padding-right:46px; }
    .pf-input:focus { border-color:var(--p1); box-shadow:0 0 0 4px rgba(124,58,237,.13), 0 0 28px rgba(6,182,212,.25); }
    .pf-input.is-bad { border-color:#fb7185; }
    .pf-fi { position:absolute; left:14px; top:38px; color:#a78bfa; font-size:14px; pointer-events:none; }
    .pf-eye { position:absolute; right:8px; top:30px; width:34px; height:34px; border:0; background:transparent; color:#94a3b8; border-radius:10px; }
    .pf-eye:hover { background:rgba(124,58,237,.08); color:var(--p1); }
    .pf-err { color:#e11d48; font-size:12px; margin-top:5px; font-weight:600; }
    .pf-hint { font-size:12px; margin-top:6px; font-weight:600; }

    .pf-btn { border:0; border-radius:14px; padding:11px 22px; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#6d28d9,#4f46e5 55%,#0891b2); box-shadow:0 8px 22px rgba(99,102,241,.38); display:inline-flex; align-items:center; gap:8px; transition:transform .15s, filter .15s; }
    .pf-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }
    .pf-btn.danger { background:linear-gradient(135deg,#e11d48,#f97316); box-shadow:0 8px 22px rgba(225,29,72,.35); }
    .pf-btn.ghost { background:#fff; color:#475569; box-shadow:none; border:1px solid #e2e8f0; }

    .pf-meter { display:flex; gap:5px; margin-top:8px; }
    .pf-meter span { flex:1; height:6px; border-radius:6px; background:#e2e8f0; transition:background .2s; }

    .pf-row { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:11px 0; border-bottom:1px dashed rgba(124,58,237,.18); font-size:13px; }
    .pf-row:last-child { border-bottom:0; }
    .pf-row span { color:#64748b; } .pf-row b { color:#0f172a; text-align:right; }
    .pf-links a { display:flex; align-items:center; gap:12px; padding:11px 12px; border-radius:14px; color:#334155; text-decoration:none; font-size:14px; font-weight:600; transition:background .15s, transform .15s; }
    .pf-links a:hover { background:linear-gradient(90deg,rgba(124,58,237,.1),rgba(6,182,212,.08)); transform:translateX(3px); color:#4338ca; }
    .pf-links a i { width:30px; height:30px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#eef2ff; color:#6366f1; font-size:13px; }
    .pf-tip { background:linear-gradient(135deg,rgba(124,58,237,.08),rgba(6,182,212,.08)); border:1px solid var(--line); border-radius:18px; padding:14px 16px; font-size:12.5px; color:#475569; line-height:1.6; }

    .pf-danger { border-color:rgba(251,113,133,.45); background:linear-gradient(135deg,rgba(255,241,242,.75),rgba(255,247,237,.7)); }
    .pf-warnbox { background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; border-radius:14px; padding:12px 14px; font-size:13px; margin-bottom:14px; }
    .pf-verify { background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:14px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .pf-verify button { border:0; background:none; color:#b45309; font-weight:700; text-decoration:underline; padding:0; }

    #pfToast { position:fixed; right:22px; bottom:26px; z-index:9999; background:linear-gradient(135deg,#1e1b4b,#4c1d95); color:#fff; padding:12px 18px; border-radius:16px; font-size:13px; font-weight:600; box-shadow:0 14px 36px rgba(76,29,149,.45); display:flex; align-items:center; gap:10px; transform:translateY(20px); opacity:0; pointer-events:none; transition:all .3s; }
    #pfToast.show { transform:none; opacity:1; }
    #pfToast i { color:#34d399; }

    .pf-modal .modal-content { border:1px solid rgba(255,255,255,.8); border-radius:26px; background:rgba(255,255,255,.92); backdrop-filter:blur(20px); box-shadow:0 30px 80px rgba(76,29,149,.3); padding:8px; }
</style>
@endpush

@section('content')
<div class="pf">

    {{-- ===== HERO ===== --}}
    <div class="pf-hero">
        <div class="pf-avatar">{{ $initials }}</div>
        <div>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <div class="pf-badges">
                <span class="pf-badge"><i class="fa-solid fa-shield-halved"></i> {{ $roleLabel }}</span>
                <span class="pf-badge">
                    @if($verified)<i class="fa-solid fa-circle-check"></i> Email verified
                    @else<i class="fa-solid fa-triangle-exclamation"></i> Email unverified @endif
                </span>
                <span class="pf-badge"><i class="fa-regular fa-calendar"></i> {{ optional($user->created_at)->format('d M Y') }} থেকে সদস্য</span>
            </div>
        </div>
    </div>

    <div class="pf-grid">

        {{-- ===== LEFT: summary + quick links ===== --}}
        <aside>
            <div class="pf-card">
                <div class="pf-title"><div class="pf-ico"><i class="fa-solid fa-id-card"></i></div><h2>অ্যাকাউন্ট</h2></div>
                <div class="mt-3">
                    <div class="pf-row"><span>নাম</span><b>{{ $user->name }}</b></div>
                    <div class="pf-row"><span>ভূমিকা</span><b>{{ $roleLabel }}</b></div>
                    <div class="pf-row"><span>স্ট্যাটাস</span><b>{{ ucfirst($user->status ?? 'active') }}</b></div>
                    <div class="pf-row"><span>সর্বশেষ আপডেট</span><b>{{ optional($user->updated_at)->diffForHumans() }}</b></div>
                </div>
            </div>

            <div class="pf-card">
                <div class="pf-title"><div class="pf-ico"><i class="fa-solid fa-bolt"></i></div><h2>দ্রুত লিংক</h2></div>
                <div class="pf-links mt-3">
                    <a href="{{ route($user->homeRoute()) }}"><i class="fa-solid fa-house"></i> ড্যাশবোর্ড</a>
                    @if($u->isDoctor())
                        @if(\Illuminate\Support\Facades\Route::has('doctor.profile.index'))
                            <a href="{{ route('doctor.profile.index') }}"><i class="fa-solid fa-user-doctor"></i> ডাক্তার প্রোফাইল</a>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('doctor.public-profile.edit'))
                            <a href="{{ route('doctor.public-profile.edit') }}"><i class="fa-solid fa-globe"></i> পাবলিক প্রোফাইল</a>
                        @endif
                    @endif
                </div>
            </div>

            <div class="pf-card" style="padding:16px;">
                <div class="pf-tip"><i class="fa-solid fa-lightbulb" style="color:#f59e0b"></i>
                    <b>নিরাপত্তা টিপ:</b> কমপক্ষে ১০ অক্ষরের পাসওয়ার্ড দিন — বড়-ছোট হাতের অক্ষর, সংখ্যা ও চিহ্ন মিশিয়ে। অন্য কোনো সাইটের পাসওয়ার্ড এখানে ব্যবহার করবেন না।</div>
            </div>
        </aside>

        {{-- ===== RIGHT: forms ===== --}}
        <main>

            {{-- Profile information --}}
            <section class="pf-card">
                <div class="pf-title"><div class="pf-ico"><i class="fa-solid fa-user-pen"></i></div><h2>প্রোফাইল তথ্য</h2></div>
                <p class="pf-sub">আপনার নাম ও ইমেইল ঠিকানা হালনাগাদ করুন।</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="pf-field">
                        <label class="pf-label" for="name">নাম</label>
                        <i class="fa-regular fa-user pf-fi"></i>
                        <input id="name" name="name" type="text" class="pf-input @error('name') is-bad @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="email">ইমেইল</label>
                        <i class="fa-regular fa-envelope pf-fi"></i>
                        <input id="email" name="email" type="email" class="pf-input @error('email') is-bad @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="pf-verify">
                            আপনার ইমেইল এখনো যাচাই করা হয়নি।
                            <button form="send-verification">যাচাই লিংক আবার পাঠান</button>
                            @if (session('status') === 'verification-link-sent')
                                <div class="mt-1" style="color:#047857;font-weight:700">নতুন যাচাই লিংক আপনার ইমেইলে পাঠানো হয়েছে।</div>
                            @endif
                        </div>
                    @endif

                    <button type="submit" class="pf-btn"><i class="fa-solid fa-floppy-disk"></i> সেভ করুন</button>
                </form>
            </section>

            {{-- Password --}}
            <section class="pf-card">
                <div class="pf-title"><div class="pf-ico"><i class="fa-solid fa-key"></i></div><h2>পাসওয়ার্ড পরিবর্তন</h2></div>
                <p class="pf-sub">অ্যাকাউন্ট নিরাপদ রাখতে দীর্ঘ ও এলোমেলো পাসওয়ার্ড ব্যবহার করুন।</p>

                <form method="post" action="{{ route('password.update') }}" id="pfPassForm">
                    @csrf
                    @method('put')

                    <div class="pf-field">
                        <label class="pf-label" for="update_password_current_password">বর্তমান পাসওয়ার্ড</label>
                        <i class="fa-solid fa-lock pf-fi"></i>
                        <input id="update_password_current_password" name="current_password" type="password" class="pf-input pr @if($errors->updatePassword->has('current_password')) is-bad @endif" autocomplete="current-password">
                        <button type="button" class="pf-eye" data-eye><i class="fa-regular fa-eye"></i></button>
                        @foreach($errors->updatePassword->get('current_password') as $m)<div class="pf-err">{{ $m }}</div>@endforeach
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="update_password_password">নতুন পাসওয়ার্ড</label>
                        <i class="fa-solid fa-lock pf-fi"></i>
                        <input id="update_password_password" name="password" type="password" class="pf-input pr @if($errors->updatePassword->has('password')) is-bad @endif" autocomplete="new-password">
                        <button type="button" class="pf-eye" data-eye><i class="fa-regular fa-eye"></i></button>
                        <div class="pf-meter" id="pfMeter"><span></span><span></span><span></span><span></span></div>
                        <div class="pf-hint" id="pfStrength" style="color:#94a3b8">পাসওয়ার্ডের শক্তি দেখা যাবে</div>
                        @foreach($errors->updatePassword->get('password') as $m)<div class="pf-err">{{ $m }}</div>@endforeach
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="update_password_password_confirmation">নতুন পাসওয়ার্ড আবার লিখুন</label>
                        <i class="fa-solid fa-lock pf-fi"></i>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="pf-input pr" autocomplete="new-password">
                        <button type="button" class="pf-eye" data-eye><i class="fa-regular fa-eye"></i></button>
                        <div class="pf-hint" id="pfMatch"></div>
                        @foreach($errors->updatePassword->get('password_confirmation') as $m)<div class="pf-err">{{ $m }}</div>@endforeach
                    </div>

                    <button type="submit" class="pf-btn"><i class="fa-solid fa-shield-halved"></i> পাসওয়ার্ড আপডেট করুন</button>
                </form>
            </section>

            {{-- Danger zone --}}
            <section class="pf-card pf-danger">
                <div class="pf-title"><div class="pf-ico warn"><i class="fa-solid fa-triangle-exclamation"></i></div><h2 style="color:#9f1239">অ্যাকাউন্ট মুছে ফেলুন</h2></div>
                <p class="pf-sub">অ্যাকাউন্ট মুছলে এর সব তথ্য স্থায়ীভাবে হারিয়ে যাবে। মুছে ফেলার আগে প্রয়োজনীয় তথ্য সংরক্ষণ করে নিন।</p>
                <button type="button" class="pf-btn danger" data-bs-toggle="modal" data-bs-target="#pfDeleteModal"><i class="fa-solid fa-trash-can"></i> অ্যাকাউন্ট মুছুন</button>
            </section>
        </main>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade pf-modal" id="pfDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content p-3">
            @csrf
            @method('delete')
            <div class="pf-title"><div class="pf-ico warn"><i class="fa-solid fa-triangle-exclamation"></i></div><h2>সত্যিই মুছে ফেলবেন?</h2></div>
            <p class="pf-sub" style="margin-left:0;margin-top:10px">অ্যাকাউন্ট মুছলে সব তথ্য স্থায়ীভাবে মুছে যাবে। নিশ্চিত করতে পাসওয়ার্ড দিন।</p>

            <div class="pf-field">
                <label class="pf-label" for="pfDelPass">পাসওয়ার্ড</label>
                <i class="fa-solid fa-lock pf-fi"></i>
                <input id="pfDelPass" name="password" type="password" class="pf-input @if($errors->userDeletion->has('password')) is-bad @endif" placeholder="আপনার পাসওয়ার্ড" autocomplete="current-password">
                @foreach($errors->userDeletion->get('password') as $m)<div class="pf-err">{{ $m }}</div>@endforeach
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="pf-btn ghost" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="pf-btn danger"><i class="fa-solid fa-trash-can"></i> হ্যাঁ, মুছে দিন</button>
            </div>
        </form>
    </div>
</div>

<div id="pfToast"><i class="fa-solid fa-circle-check"></i> <span></span></div>
@endsection

@push('scripts')
<script>
(function () {
    // ---- show / hide password ----
    document.querySelectorAll('[data-eye]').forEach(function (b) {
        b.addEventListener('click', function () {
            var inp = b.parentNode.querySelector('input');
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            b.innerHTML = '<i class="fa-regular ' + (show ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
        });
    });

    // ---- strength meter + match hint ----
    var pw = document.getElementById('update_password_password');
    var cf = document.getElementById('update_password_password_confirmation');
    var bars = document.querySelectorAll('#pfMeter span');
    var label = document.getElementById('pfStrength');
    var match = document.getElementById('pfMatch');
    var COL = ['#f43f5e', '#f97316', '#eab308', '#10b981'];
    var TXT = ['খুব দুর্বল', 'দুর্বল', 'মোটামুটি', 'শক্তিশালী'];

    function score(v) {
        var s = 0;
        if (v.length >= 8) s++;
        if (v.length >= 12) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return Math.min(s, 4);
    }
    function paint() {
        var v = pw.value, s = v ? Math.max(1, score(v)) : 0;
        bars.forEach(function (b, i) { b.style.background = i < s ? COL[Math.min(s, 4) - 1] : '#e2e8f0'; });
        label.textContent = v ? TXT[s - 1] : 'পাসওয়ার্ডের শক্তি দেখা যাবে';
        label.style.color = v ? COL[s - 1] : '#94a3b8';
        if (!cf.value) { match.textContent = ''; return; }
        var ok = cf.value === v;
        match.textContent = ok ? '✓ দুটো পাসওয়ার্ড মিলেছে' : '✕ পাসওয়ার্ড মিলছে না';
        match.style.color = ok ? '#059669' : '#e11d48';
    }
    pw.addEventListener('input', paint);
    cf.addEventListener('input', paint);

    // ---- toast after save ----
    @php $st = session('status'); @endphp
    @if(in_array($st, ['profile-updated', 'password-updated']))
        var t = document.getElementById('pfToast');
        t.querySelector('span').textContent = '{{ $st === 'password-updated' ? 'পাসওয়ার্ড আপডেট হয়েছে' : 'প্রোফাইল সেভ হয়েছে' }}';
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 2800);
    @endif

    // ---- reopen delete modal when the password was wrong ----
    @if($errors->userDeletion->isNotEmpty())
        window.addEventListener('load', function () {
            new bootstrap.Modal(document.getElementById('pfDeleteModal')).show();
        });
    @endif
})();
</script>
@endpush