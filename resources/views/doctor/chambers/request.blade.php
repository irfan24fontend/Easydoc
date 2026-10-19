@extends('layouts.doctor')

@section('title', 'নতুন চেম্বার রিকোয়েস্ট')

@push('styles')
<style>
    :root { --rq-brand: #4f46e5; --rq-ink: #0f172a; --rq-muted: #64748b; --rq-line: #e5e9f0; }

    /* ---------- Hero ---------- */
    .rq-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 1.5rem 1.8rem; color: #fff;
        background: radial-gradient(1200px 300px at 90% -20%, rgba(255,255,255,.18), transparent 60%),
                    linear-gradient(135deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
        box-shadow: 0 14px 34px rgba(79,70,229,.28);
    }
    .rq-hero::after { content: ""; position: absolute; right: -60px; bottom: -80px; width: 240px; height: 240px; border-radius: 50%; background: rgba(255,255,255,.07); }
    .rq-hero h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
    .rq-hero p { margin: .3rem 0 0; opacity: .85; font-size: .88rem; max-width: 560px; }
    .rq-back { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; font-weight: 700; font-size: .82rem; padding: .5rem .95rem; border-radius: 12px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28); transition: background .15s; }
    .rq-back:hover { background: rgba(255,255,255,.24); color: #fff; }

    /* ---------- Section cards ---------- */
    .rq-card { background: #fff; border: 1px solid var(--rq-line); border-radius: 20px; padding: 1.35rem 1.4rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rq-card + .rq-card { margin-top: 1.25rem; }
    .rq-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 1.1rem; }
    .rq-card-head .ico { width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .rq-card-head h2 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--rq-ink); }
    .rq-card-head p { margin: 0; font-size: .78rem; color: var(--rq-muted); }

    .rq-card .form-label { font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: .35rem; }
    .rq-card .form-label .opt { font-weight: 500; color: #94a3b8; }
    .rq-card .form-control { border: 1px solid #e2e8f0; border-radius: 12px; padding: .6rem .9rem; font-size: .9rem; background: #f8fafc; transition: all .15s; }
    .rq-card .form-control:focus { background: #fff; border-color: var(--rq-brand); box-shadow: 0 0 0 4px rgba(79,70,229,.1); }
    .rq-card .form-control.is-invalid { border-color: #f87171; background: #fffafa; }
    .rq-card .form-control.is-invalid:focus { box-shadow: 0 0 0 4px rgba(248,113,113,.15); }

    /* ---------- Day chips ---------- */
    .rq-days { display: flex; flex-wrap: wrap; gap: .5rem; }
    .rq-day { position: relative; margin: 0; cursor: pointer; }
    .rq-day input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; margin: 0; cursor: pointer; }
    .rq-day span { display: inline-block; padding: .45rem .95rem; border-radius: 999px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: .82rem; font-weight: 700; transition: all .15s; }
    .rq-day:hover span { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .rq-day input:checked + span { background: var(--rq-brand); border-color: var(--rq-brand); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,.25); }
    .rq-day input:focus-visible + span { box-shadow: 0 0 0 4px rgba(79,70,229,.25); }

    /* ---------- Dropzone ---------- */
    .rq-drop { display: block; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 1.8rem 1rem; text-align: center; background: #f8fafc; cursor: pointer; transition: all .15s; margin: 0; }
    .rq-drop:hover, .rq-drop.drag { border-color: var(--rq-brand); background: #eef2ff; }
    .rq-drop.is-invalid { border-color: #f87171; background: #fffafa; }
    .rq-drop .big { width: 52px; height: 52px; border-radius: 16px; background: #eef2ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: .6rem; }
    .rq-drop .t1 { font-weight: 800; color: var(--rq-ink); font-size: .92rem; }
    .rq-drop .t2 { font-size: .76rem; color: var(--rq-muted); margin-top: .15rem; }
    .rq-drop input { display: none; }

    .rq-preview { display: none; align-items: flex-start; gap: 14px; border: 1px solid var(--rq-line); border-radius: 16px; padding: .75rem; background: #fff; }
    .rq-preview.show { display: flex; }
    .rq-preview img { width: 120px; height: 150px; object-fit: cover; border-radius: 12px; border: 1px solid #e2e8f0; background: #f1f5f9; }
    .rq-preview .meta { min-width: 0; flex: 1; padding-top: .2rem; }
    .rq-preview .nm { font-weight: 700; font-size: .86rem; color: var(--rq-ink); word-break: break-all; }
    .rq-preview .sz { font-size: .76rem; color: var(--rq-muted); }
    .rq-preview .rm { margin-top: .6rem; border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; font-size: .76rem; font-weight: 700; padding: .3rem .7rem; border-radius: 9px; cursor: pointer; }
    .rq-preview .rm:hover { background: #fee2e2; }

    /* ---------- Sidebar ---------- */
    .rq-side { position: sticky; top: 88px; }
    .rq-wallet { border-radius: 20px; padding: 1.25rem 1.3rem; background: #fff; border: 1px solid var(--rq-line); box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rq-wallet.low { border-color: #fecaca; background: #fffafa; }
    .rq-wallet .lbl { font-size: .72rem; font-weight: 700; color: var(--rq-muted); text-transform: uppercase; letter-spacing: .06em; }
    .rq-wallet .bal { font-size: 1.7rem; font-weight: 800; color: var(--rq-ink); line-height: 1.1; margin-top: .15rem; }
    .rq-wallet.low .bal { color: #b91c1c; }
    .rq-lines { margin: 1rem 0 0; padding: .85rem 0 0; border-top: 1px dashed #e2e8f0; }
    .rq-line { display: flex; justify-content: space-between; font-size: .84rem; color: #475569; padding: .22rem 0; }
    .rq-line b { color: var(--rq-ink); }
    .rq-line.total { border-top: 1px solid #e2e8f0; margin-top: .35rem; padding-top: .6rem; font-weight: 800; }
    .rq-line.total b.ok { color: #15803d; }
    .rq-line.total b.bad { color: #b91c1c; }
    .rq-warn { margin-top: .9rem; font-size: .8rem; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: .6rem .75rem; }

    .rq-submit { width: 100%; margin-top: 1rem; border: 0; border-radius: 14px; padding: .8rem 1rem; font-weight: 800; font-size: .92rem; color: #fff; background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 10px 22px rgba(79,70,229,.3); transition: transform .15s, box-shadow .15s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .rq-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(79,70,229,.36); }
    .rq-submit:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; }
    .rq-submit .spin { display: none; width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; animation: rqSpin .7s linear infinite; }
    .rq-submit.busy .spin { display: inline-block; }
    @keyframes rqSpin { to { transform: rotate(360deg); } }

    .rq-steps { margin-top: 1.25rem; background: #fff; border: 1px solid var(--rq-line); border-radius: 20px; padding: 1.15rem 1.3rem; }
    .rq-steps h3 { font-size: .86rem; font-weight: 800; color: var(--rq-ink); margin: 0 0 .8rem; }
    .rq-step { display: flex; gap: 11px; position: relative; padding-bottom: .9rem; }
    .rq-step:last-child { padding-bottom: 0; }
    .rq-step:not(:last-child)::before { content: ""; position: absolute; left: 13px; top: 28px; bottom: 2px; width: 2px; background: #e0e7ff; }
    .rq-step .n { width: 28px; height: 28px; border-radius: 50%; background: #eef2ff; color: #4338ca; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rq-step .tx { font-size: .8rem; color: #475569; line-height: 1.45; padding-top: 2px; }
    .rq-step .tx b { color: var(--rq-ink); display: block; }

    .rq-alert { border: 0; border-radius: 14px; }

    @media (max-width: 991.98px) { .rq-side { position: static; } }
    @media (prefers-reduced-motion: reduce) { .rq-submit .spin { animation: none; } }
</style>
@endpush

@section('content')
@php
    $balanceNum = (float) $balance;
    $feeNum     = (float) $fee;
    $after      = $balanceNum - $feeNum;
    $short      = $feeNum - $balanceNum;
    $dayOptions = ['শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি', 'শুক্র'];
@endphp

<div class="py-2">
    <div class="container-fluid px-0">

        {{-- ================= Hero ================= --}}
        <div class="rq-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
                <div>
                    <h1>নতুন চেম্বার রিকোয়েস্ট</h1>
                    <p>চেম্বারের তথ্য ও প্রেসক্রিপশন প্যাডের ছবি দিন। Super Admin রিভিউ করে প্যাড অনুযায়ী টেমপ্লেট সেট করে চেম্বার Active করবেন।</p>
                </div>
                <a href="{{ route('doctor.chambers.index') }}" class="rq-back"><i class="fa-solid fa-arrow-left"></i> আমার চেম্বার</a>
            </div>
        </div>

        {{-- ================= Errors ================= --}}
        @if($errors->any())
            <div class="alert alert-danger rq-alert shadow-sm d-flex align-items-start gap-2 mb-4">
                <i class="fa-solid fa-circle-exclamation mt-1"></i>
                <div>
                    @if($errors->has('balance'))
                        <b>{{ $errors->first('balance') }}</b>
                    @else
                        <b>কিছু তথ্য ঠিক নেই।</b> নিচে লাল চিহ্নিত ঘরগুলো ঠিক করে আবার পাঠান।
                    @endif
                </div>
            </div>
        @endif

        <form id="rqForm" method="POST" action="{{ route('doctor.chambers.request.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">

                {{-- ================= Left: form ================= --}}
                <div class="col-lg-8">

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-hospital"></i></div>
                            <div><h2>চেম্বারের তথ্য</h2><p>যে নামে চেম্বার প্রেসক্রিপশনে দেখাবে।</p></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="chamber_name">চেম্বারের নাম</label>
                                <input type="text" id="chamber_name" name="chamber_name" value="{{ old('chamber_name') }}"
                                       class="form-control @error('chamber_name') is-invalid @enderror" placeholder="যেমন: সিটি কেয়ার চেম্বার" required>
                                @error('chamber_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="hospital_name">হাসপাতাল / ক্লিনিকের নাম</label>
                                <input type="text" id="hospital_name" name="hospital_name" value="{{ old('hospital_name') }}"
                                       class="form-control @error('hospital_name') is-invalid @enderror" required>
                                @error('hospital_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-location-dot"></i></div>
                            <div><h2>যোগাযোগ ও ঠিকানা</h2><p>রোগীরা এই তথ্য দেখে যোগাযোগ করবে।</p></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="phone">ফোন নম্বর</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel"
                                       class="form-control @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">ইমেইল <span class="opt">(ঐচ্ছিক)</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="address">ঠিকানা</label>
                                <textarea id="address" name="address" rows="2"
                                          class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-calendar-days"></i></div>
                            <div><h2>বসার দিন ও সময়</h2><p>যে দিনগুলোতে চেম্বারে বসেন সেগুলো বেছে নিন।</p></div>
                        </div>

                        <label class="form-label">বসার দিন</label>
                        <div class="rq-days" id="rqDays">
                            @foreach($dayOptions as $day)
                                <label class="rq-day">
                                    <input type="checkbox" name="visiting_days[]" value="{{ $day }}" @checked(in_array($day, old('visiting_days', [])))>
                                    <span>{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="text-danger small mt-2 {{ $errors->has('visiting_days') ? '' : 'd-none' }}" id="rqDaysErr">
                            {{ $errors->first('visiting_days') ?: 'অন্তত একটি দিন বেছে নিন।' }}
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="visiting_time">বসার সময়</label>
                            <input type="text" id="visiting_time" name="visiting_time" value="{{ old('visiting_time') }}"
                                   class="form-control @error('visiting_time') is-invalid @enderror" placeholder="বিকাল ৫:০০ - রাত ৯:০০" required>
                            @error('visiting_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-file-image"></i></div>
                            <div><h2>প্রেসক্রিপশন প্যাডের ছবি</h2><p>আপনার বর্তমান প্যাডের পরিষ্কার ছবি দিন। এই ছবি দেখে টেমপ্লেট বানানো হবে।</p></div>
                        </div>

                        <label class="rq-drop @error('pad_image') is-invalid @enderror" id="rqDrop" for="pad_image">
                            <div class="big"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="t1">ছবি বেছে নিন বা এখানে টেনে আনুন</div>
                            <div class="t2">JPG, PNG বা WEBP, সর্বোচ্চ ৪ MB</div>
                            <input type="file" id="pad_image" name="pad_image" accept="image/jpeg,image/png,image/webp" required>
                        </label>
                        @error('pad_image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        <div class="text-danger small mt-2 d-none" id="rqFileErr"></div>

                        <div class="rq-preview mt-3" id="rqPreview">
                            <img id="rqPreviewImg" alt="প্যাডের প্রিভিউ">
                            <div class="meta">
                                <div class="nm" id="rqFileName"></div>
                                <div class="sz" id="rqFileSize"></div>
                                <button type="button" class="rm" id="rqRemove"><i class="fa-solid fa-trash-can me-1"></i> সরিয়ে দিন</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= Right: wallet + submit ================= --}}
                <div class="col-lg-4">
                    <div class="rq-side">

                        <div class="rq-wallet {{ $hasBalance ? '' : 'low' }}">
                            <div class="lbl">বর্তমান ব্যালেন্স</div>
                            <div class="bal">৳ {{ number_format($balanceNum, 2) }}</div>

                            <div class="rq-lines">
                                <div class="rq-line"><span>চেম্বার রিকোয়েস্ট ফি</span><b>− ৳ {{ number_format($feeNum, 2) }}</b></div>
                                <div class="rq-line total">
                                    <span>ফি কাটার পর ব্যালেন্স</span>
                                    <b class="{{ $hasBalance ? 'ok' : 'bad' }}">৳ {{ number_format(max($after, 0), 2) }}</b>
                                </div>
                            </div>

                            @unless($hasBalance)
                                <div class="rq-warn">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    ব্যালেন্স কম। আরও <b>৳{{ number_format($short, 2) }}</b> লাগবে। Super Admin-এর কাছ থেকে রিচার্জ করিয়ে নিন।
                                </div>
                            @endunless

                            <button type="submit" class="rq-submit" id="rqSubmit" {{ $hasBalance ? '' : 'disabled' }}>
                                <span class="spin"></span>
                                <i class="fa-solid fa-paper-plane"></i>
                                <span id="rqSubmitText">রিকোয়েস্ট পাঠান</span>
                            </button>
                        </div>

                        <div class="rq-steps">
                            <h3>এরপর কী হবে</h3>
                            <div class="rq-step"><div class="n">১</div><div class="tx"><b>রিকোয়েস্ট জমা</b>ফি কাটা হবে এবং চেম্বার "অপেক্ষমান" থাকবে।</div></div>
                            <div class="rq-step"><div class="n">২</div><div class="tx"><b>Super Admin রিভিউ</b>প্যাড দেখে আপনার জন্য টেমপ্লেট তৈরি করবেন।</div></div>
                            <div class="rq-step"><div class="n">৩</div><div class="tx"><b>চেম্বার Active</b>অনুমোদন হলে "আমার চেম্বার" থেকে চালু করে প্রেসক্রিপশন লিখুন। বাতিল হলে ফি ফেরত পাবেন।</div></div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const MAX = 4 * 1024 * 1024;
    const form = document.getElementById('rqForm');
    const input = document.getElementById('pad_image');
    const drop = document.getElementById('rqDrop');
    const preview = document.getElementById('rqPreview');
    const img = document.getElementById('rqPreviewImg');
    const nameEl = document.getElementById('rqFileName');
    const sizeEl = document.getElementById('rqFileSize');
    const fileErr = document.getElementById('rqFileErr');
    const submitBtn = document.getElementById('rqSubmit');
    const submitText = document.getElementById('rqSubmitText');
    const daysErr = document.getElementById('rqDaysErr');

    function fmt(bytes) { return bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB'; }

    function showError(msg) { fileErr.textContent = msg; fileErr.classList.toggle('d-none', !msg); drop.classList.toggle('is-invalid', !!msg); }

    function clearFile() {
        input.value = '';
        preview.classList.remove('show');
        if (img.src) { URL.revokeObjectURL(img.src); img.removeAttribute('src'); }
    }

    function handle(file) {
        if (!file) { clearFile(); return; }
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { clearFile(); showError('শুধু JPG, PNG বা WEBP ছবি দিন।'); return; }
        if (file.size > MAX) { clearFile(); showError('ছবির আকার ৪ MB এর বেশি হতে পারবে না।'); return; }
        showError('');
        img.src = URL.createObjectURL(file);
        nameEl.textContent = file.name;
        sizeEl.textContent = fmt(file.size);
        preview.classList.add('show');
    }

    input.addEventListener('change', function () { handle(input.files[0]); });
    document.getElementById('rqRemove').addEventListener('click', function () { clearFile(); showError(''); });

    ['dragenter', 'dragover'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('drag'); });
    });
    drop.addEventListener('drop', function (e) {
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            input.files = e.dataTransfer.files;
            handle(input.files[0]);
        }
    });

    // Duplicate submit thamate (double click korle duibar 500 tk kete jete pare)
    let sending = false;
    form.addEventListener('submit', function (e) {
        if (sending) { e.preventDefault(); return; }

        if (!document.querySelector('#rqDays input:checked')) {
            e.preventDefault();
            daysErr.classList.remove('d-none');
            daysErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        daysErr.classList.add('d-none');

        sending = true;
        submitBtn.classList.add('busy');
        submitBtn.disabled = true;
        submitText.textContent = 'পাঠানো হচ্ছে...';
    });
})();
</script>
@endpush
