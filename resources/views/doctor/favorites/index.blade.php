@extends('layouts.doctor')

@section('title', 'Favorite Medicines')

@push('styles')
<style>
    :root { --fv-brand:#4f46e5; --fv-ink:#0f172a; --fv-muted:#64748b; --fv-line:#e5e9f0; }

    .fv-hero { position:relative; overflow:hidden; border-radius:22px; padding:1.6rem 1.8rem; color:#fff;
        background:radial-gradient(1200px 300px at 90% -20%, rgba(255,255,255,.18), transparent 60%),
                   linear-gradient(135deg,#4f46e5 0%,#6d28d9 55%,#7c3aed 100%);
        box-shadow:0 14px 34px rgba(79,70,229,.28); margin-bottom:1.25rem; }
    .fv-hero::after { content:""; position:absolute; right:-60px; bottom:-80px; width:240px; height:240px; border-radius:50%; background:rgba(255,255,255,.07); }
    .fv-hero h1 { font-size:1.5rem; font-weight:800; letter-spacing:-.02em; margin:0; }
    .fv-hero p { margin:.35rem 0 0; opacity:.9; font-size:.88rem; max-width:640px; position:relative; z-index:1; }

    .fv-card { background:#fff; border:1px solid var(--fv-line); border-radius:18px; padding:1.25rem 1.35rem; box-shadow:0 1px 2px rgba(0,0,0,.03); }
    .fv-card h2 { font-size:1rem; font-weight:800; color:var(--fv-ink); margin:0; display:flex; align-items:center; gap:.55rem; }
    .fv-card h2 i { color:var(--fv-brand); }
    .fv-sub { font-size:.8rem; color:var(--fv-muted); margin:.35rem 0 1rem; }

    .fv-company { display:flex; align-items:center; gap:.7rem; padding:.6rem .75rem; border:1px solid var(--fv-line); border-radius:12px; background:#f8fafc; }
    .fv-company + .fv-company { margin-top:.5rem; }
    .fv-rank { width:26px; height:26px; border-radius:50%; background:#e0e7ff; color:#3730a3; font-size:.75rem; font-weight:800; display:grid; place-items:center; flex:none; }
    .fv-company .nm { font-weight:700; color:var(--fv-ink); flex:1; min-width:0; overflow-wrap:anywhere; }

    .fv-search { position:relative; }
    .fv-results { position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:1060; background:#fff; border:1px solid var(--fv-line); border-radius:12px; box-shadow:0 16px 40px rgba(15,23,42,.16); max-height:280px; overflow-y:auto; display:none; }
    .fv-results.show { display:block; }
    .fv-opt { width:100%; text-align:left; border:0; background:#fff; padding:.55rem .85rem; display:flex; justify-content:space-between; align-items:center; gap:.6rem; cursor:pointer; }
    .fv-opt + .fv-opt { border-top:1px solid #f1f5f9; }
    .fv-opt:hover, .fv-opt:focus { background:#eef2ff; outline:none; }
    .fv-opt .t { font-weight:700; color:var(--fv-ink); font-size:.9rem; }
    .fv-opt .s { font-size:.75rem; color:var(--fv-muted); }

    .fv-tag { display:inline-flex; align-items:center; gap:.3rem; font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:999px; white-space:nowrap; }
    .fv-tag.fav { background:#fef3c7; color:#92400e; }
    .fv-tag.def { background:#e0e7ff; color:#3730a3; }
    .fv-tag.brand { background:#d1fae5; color:#065f46; }

    .fv-table th { font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--fv-muted); font-weight:700; border-bottom:1px solid var(--fv-line); white-space:nowrap; }
    .fv-table td { vertical-align:middle; border-color:#f1f5f9; }
    .fv-med { font-weight:800; color:var(--fv-ink); }
    .fv-gen { font-size:.76rem; color:var(--fv-muted); }
    .fv-empty { text-align:center; color:var(--fv-muted); padding:2rem 1rem; font-size:.9rem; }
    .fv-empty i { font-size:1.8rem; color:#cbd5e1; display:block; margin-bottom:.5rem; }

    .fv-chip { border:1px solid var(--fv-line); background:#fff; border-radius:999px; padding:.2rem .65rem; font-size:.76rem; cursor:pointer; }
    .fv-chip:hover { background:#eef2ff; border-color:#c7d2fe; }
    .fv-label { font-size:.78rem; font-weight:700; color:#334155; margin-bottom:.3rem; display:block; }
    .fv-picked { background:#eef2ff; border:1px solid #c7d2fe; border-radius:12px; padding:.6rem .8rem; font-size:.88rem; display:none; }
    .fv-picked.show { display:block; }
    .fv-default-box { background:#f8fafc; border:1px dashed #cbd5e1; border-radius:14px; padding:1rem; display:none; }
    .fv-default-box.show { display:block; }
</style>
@endpush

@section('content')

<div class="fv-hero">
    <h1><i class="fa-solid fa-star me-2"></i>Favorite Medicines</h1>
    <p>Choose the brands you trust, and medicines you prescribe all the time. Favorite brands appear first when you search. "Add automatically" medicines are filled into every new prescription with your dose.</p>
</div>

<div class="row g-4">

    {{-- ================= Favorite brands ================= --}}
    <div class="col-lg-4">
        <div class="fv-card">
            <h2><i class="fa-solid fa-industry"></i> Favorite brands</h2>
            <p class="fv-sub">When you search any medicine, the medicines of these companies appear first. Other brands still appear below. You can choose more than one.</p>

            <form method="POST" action="{{ route('doctor.favorites.companies.store') }}" autocomplete="off" class="mb-3">
                @csrf
                <label class="fv-label" for="companyInput">Add a brand (company)</label>
                <div class="fv-search">
                    <div class="input-group">
                        <input type="text" id="companyInput" name="company" value="{{ old('company') }}" class="form-control @error('company') is-invalid @enderror" placeholder="e.g. Drug International" required>
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus"></i> Add</button>
                    </div>
                    <div class="fv-results" id="companyResults" role="listbox"></div>
                </div>
                @error('company') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">Start typing and pick the company from the list.</div>
            </form>

            @forelse($companies as $i => $c)
                <div class="fv-company">
                    <span class="fv-rank">{{ $i + 1 }}</span>
                    <span class="nm">{{ $c->company }}</span>
                    <form method="POST" action="{{ route('doctor.favorites.companies.destroy', $c->id) }}" class="m-0" onsubmit="return confirm('Remove this brand from favorites?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light border" aria-label="Remove {{ $c->company }}"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                </div>
            @empty
                <div class="fv-empty"><i class="fa-solid fa-industry"></i>No favorite brand yet.</div>
            @endforelse
        </div>
    </div>

    {{-- ================= Favorite medicines ================= --}}
    <div class="col-lg-8">
        <div class="fv-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                <h2><i class="fa-solid fa-capsules"></i> My favorite medicines</h2>
                <button type="button" class="btn btn-primary btn-sm px-3" id="fvAddBtn"><i class="fa-solid fa-plus me-1"></i> Add medicine</button>
            </div>
            <p class="fv-sub">
                <span class="fv-tag fav"><i class="fa-solid fa-star"></i> Favorite</span> ranks first in your search.
                <span class="fv-tag def"><i class="fa-solid fa-thumbtack"></i> Auto-added</span> goes into every new prescription with the dose you set. You can still delete it from a single prescription.
            </p>

            @if($rows->isEmpty())
                <div class="fv-empty"><i class="fa-regular fa-star"></i>No favorite medicine yet. Click "Add medicine" to start.</div>
            @else
                <div class="table-responsive">
                    <table class="table fv-table align-middle mb-0">
                        <thead>
                            <tr><th>Medicine</th><th>Status</th><th>Dose, duration and instruction</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            @php $m = $row->medicine; @endphp
                            <tr>
                                <td>
                                    <div class="fv-med">{{ $m->brand_name }}@if($m->strength) <span class="text-muted fw-semibold">({{ $m->strength }})</span>@endif</div>
                                    <div class="fv-gen">{{ $m->generic_name }}@if($m->company) &middot; {{ $m->company }}@endif</div>
                                </td>
                                <td>
                                    @if($row->is_favorite)<span class="fv-tag fav"><i class="fa-solid fa-star"></i> Favorite</span>@endif
                                    @if($row->is_default)<span class="fv-tag def"><i class="fa-solid fa-thumbtack"></i> Auto-added</span>@endif
                                </td>
                                <td class="small">
                                    @if($row->is_default)
                                        <div><strong>{{ $row->default_dose_text ?: $row->default_dose }}</strong></div>
                                        <div class="text-muted">{{ $row->default_duration ?: '—' }}@if($row->default_instruction) &middot; {{ $row->default_instruction }}@endif</div>
                                    @else
                                        <span class="text-muted">Not auto-added</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-light border fv-edit"
                                            data-id="{{ $row->id }}"
                                            data-label="{{ $m->brand_name }}{{ $m->strength ? ' ('.$m->strength.')' : '' }}"
                                            data-sub="{{ $m->generic_name }}{{ $m->company ? ' · '.$m->company : '' }}"
                                            data-form="{{ $m->dosage_form }}"
                                            data-fav="{{ $row->is_favorite ? 1 : 0 }}"
                                            data-default="{{ $row->is_default ? 1 : 0 }}"
                                            data-dose="{{ $row->default_dose }}"
                                            data-dose-text="{{ $row->default_dose_text }}"
                                            data-duration="{{ $row->default_duration }}"
                                            data-instruction="{{ $row->default_instruction }}">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('doctor.favorites.medicines.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Remove from favorites?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" aria-label="Remove"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ================= Add / Edit modal ================= --}}
<div class="modal fade" id="fvModal" tabindex="-1" aria-labelledby="fvModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="POST" id="fvForm" action="{{ route('doctor.favorites.medicines.store') }}">
            @csrf
            <input type="hidden" name="_method" id="fvMethod" value="PUT" disabled>
            <input type="hidden" name="_fv" id="fvMode" value="{{ old('_fv', 'add') }}">
            <input type="hidden" name="_fv_id" id="fvEditId" value="{{ old('_fv_id') }}">
            <input type="hidden" name="_fv_label" id="fvLabelField" value="{{ old('_fv_label') }}">
            <input type="hidden" name="_fv_sub" id="fvSubField" value="{{ old('_fv_sub') }}">
            <input type="hidden" name="_fv_form" id="fvFormField" value="{{ old('_fv_form') }}">
            <input type="hidden" name="medicine_id" id="fvMedicineId" value="{{ old('medicine_id') }}">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="fvModalTitle">Add favorite medicine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if($errors->any() && old('_fv'))
                    <div class="alert alert-danger py-2 small">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                {{-- Medicine --}}
                <div class="mb-3" id="fvPickWrap">
                    <label class="fv-label" for="fvSearch">Medicine</label>
                    <div class="fv-search">
                        <input type="text" id="fvSearch" class="form-control" placeholder="Type brand or generic name, e.g. Napa, Seclo" autocomplete="off">
                        <div class="fv-results" id="fvResults" role="listbox"></div>
                    </div>
                </div>
                <div class="fv-picked mb-3" id="fvPicked"></div>

                {{-- Favorite --}}
                <div class="form-check mb-3">
                    <input type="hidden" name="is_favorite" value="0">
                    <input class="form-check-input" type="checkbox" id="fvFav" name="is_favorite" value="1" @checked(old('_fv') ? old('is_favorite') : true)>
                    <label class="form-check-label" for="fvFav"><strong>Favorite</strong>: show this medicine first in my search results</label>
                </div>

                {{-- Auto-add --}}
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_default" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="fvDefault" name="is_default" value="1" @checked(old('is_default'))>
                    <label class="form-check-label" for="fvDefault"><strong>Add automatically to every new prescription</strong></label>
                </div>

                <div class="fv-default-box" id="fvDefaultBox">
                    <div class="row g-3">
                        <div class="col-md-6" id="fvDoseWrap">
                            <label class="fv-label" for="fvDose">Dose (Morning+Noon+Night)</label>
                            <input type="text" class="form-control" id="fvDose" name="default_dose" value="{{ old('default_dose') }}" placeholder="1+0+1" maxlength="40">
                            <div class="d-flex flex-wrap gap-1 mt-2" data-chips="fvDose">
                                @foreach(['1+0+0','0+1+0','0+0+1','1+0+1','1+1+0','0+1+1','1+1+1','2+0+2','0.5+0+0.5'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6" id="fvDoseTextWrap">
                            <label class="fv-label" for="fvDoseText">Dose in words (syrup, drops)</label>
                            <input type="text" class="form-control" id="fvDoseText" name="default_dose_text" value="{{ old('default_dose_text') }}" placeholder="২ চামচ" maxlength="120">
                        </div>
                        <div class="col-md-6">
                            <label class="fv-label" for="fvDuration">Duration</label>
                            <input type="text" class="form-control" id="fvDuration" name="default_duration" value="{{ old('default_duration') }}" placeholder="৭ দিন" maxlength="120">
                            <div class="d-flex flex-wrap gap-1 mt-2" data-chips="fvDuration">
                                @foreach(['৩ দিন','৫ দিন','৭ দিন','১০ দিন','১৪ দিন','১ মাস','চলবে','প্রয়োজনে'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="fv-label" for="fvInstruction">Instruction</label>
                            <input type="text" class="form-control" id="fvInstruction" name="default_instruction" value="{{ old('default_instruction') }}" placeholder="খাবার পরে" maxlength="255">
                            <div class="d-flex flex-wrap gap-1 mt-2" data-chips="fvInstruction">
                                @foreach(['খাবার পরে','খাবার আগে','সকালে খালি পেটে','রাতে ঘুমানোর আগে','প্রয়োজনে'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var MED_URL = @json(route('doctor.favorites.medicine-search'));
    var COMPANY_URL = @json(route('doctor.favorites.company-search'));
    var STORE_URL = @json(route('doctor.favorites.medicines.store'));
    var UPDATE_URL = @json(route('doctor.favorites.medicines.update', ['doctorMedicine' => '__ID__']));
    var REOPEN = @json((bool) ($errors->any() && old('_fv')));

    function $(id) { return document.getElementById(id); }
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
    function debounce(fn, ms) { var t; return function () { var a = arguments, c = this; clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms); }; }

    /* ---------- Brand (company) autocomplete ---------- */
    var cInput = $('companyInput'), cBox = $('companyResults');
    if (cInput) {
        cInput.addEventListener('input', debounce(function () {
            var term = cInput.value.trim();
            if (!term) { cBox.classList.remove('show'); return; }
            fetch(COMPANY_URL + '?term=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    if (!list.length) { cBox.innerHTML = '<div class="p-3 small text-muted">No company found.</div>'; cBox.classList.add('show'); return; }
                    cBox.innerHTML = list.map(function (c) {
                        return '<button type="button" class="fv-opt" data-v="' + esc(c) + '"><span class="t">' + esc(c) + '</span></button>';
                    }).join('');
                    cBox.classList.add('show');
                }).catch(function () { cBox.classList.remove('show'); });
        }, 200));
        cBox.addEventListener('click', function (e) {
            var b = e.target.closest('.fv-opt'); if (!b) return;
            cInput.value = b.getAttribute('data-v'); cBox.classList.remove('show');
        });
    }

    /* ---------- Modal ---------- */
    var modalEl = $('fvModal'), modal = new bootstrap.Modal(modalEl);
    var form = $('fvForm'), methodInput = $('fvMethod');
    var medId = $('fvMedicineId'), search = $('fvSearch'), results = $('fvResults'), picked = $('fvPicked'), pickWrap = $('fvPickWrap');
    var defSwitch = $('fvDefault'), defBox = $('fvDefaultBox');
    var doseWrap = $('fvDoseWrap'), doseTextWrap = $('fvDoseTextWrap');
    var current = { form: '' };

    function syncDefault() { defBox.classList.toggle('show', defSwitch.checked); }
    function syncDoseFields() {
        var syrupLike = current.form === 'Syrup';
        doseWrap.style.display = syrupLike ? 'none' : '';
        doseTextWrap.style.display = syrupLike ? '' : 'none';
    }
    defSwitch.addEventListener('change', syncDefault);

    function showPicked(label, sub) {
        picked.innerHTML = '<strong>' + esc(label) + '</strong>' + (sub ? '<div class="small text-muted">' + esc(sub) + '</div>' : '');
        picked.classList.add('show');
        $('fvLabelField').value = label; $('fvSubField').value = sub || ''; $('fvFormField').value = current.form || '';
    }

    function openAdd() {
        form.reset();
        form.action = STORE_URL; methodInput.disabled = true;
        $('fvMode').value = 'add'; $('fvEditId').value = '';
        $('fvModalTitle').textContent = 'Add favorite medicine';
        $('fvFav').checked = true; defSwitch.checked = false;
        ['fvDose', 'fvDoseText', 'fvDuration', 'fvInstruction'].forEach(function (id) { $(id).value = ''; });
        medId.value = ''; search.value = ''; current.form = '';
        picked.classList.remove('show'); picked.innerHTML = '';
        pickWrap.style.display = '';
        syncDefault(); syncDoseFields(); modal.show();
    }

    function openEdit(btn) {
        var d = btn.dataset;
        form.action = UPDATE_URL.replace('__ID__', d.id); methodInput.disabled = false;
        $('fvMode').value = 'edit'; $('fvEditId').value = d.id;
        $('fvModalTitle').textContent = 'Edit favorite medicine';
        current.form = d.form || '';
        medId.value = ''; pickWrap.style.display = 'none';
        showPicked(d.label, d.sub);
        $('fvFav').checked = d.fav === '1'; defSwitch.checked = d.default === '1';
        $('fvDose').value = d.dose || ''; $('fvDoseText').value = d.doseText || '';
        $('fvDuration').value = d.duration || ''; $('fvInstruction').value = d.instruction || '';
        syncDefault(); syncDoseFields(); modal.show();
    }

    $('fvAddBtn').addEventListener('click', openAdd);
    document.querySelectorAll('.fv-edit').forEach(function (b) { b.addEventListener('click', function () { openEdit(b); }); });

    /* ---------- Medicine search inside modal ---------- */
    search.addEventListener('input', debounce(function () {
        var term = search.value.trim();
        medId.value = '';
        if (term.length < 2) { results.classList.remove('show'); return; }
        fetch(MED_URL + '?term=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                if (!list.length) { results.innerHTML = '<div class="p-3 small text-muted">No medicine found.</div>'; results.classList.add('show'); return; }
                results.innerHTML = list.map(function (m, i) {
                    return '<button type="button" class="fv-opt" data-i="' + i + '"><span><span class="t">' + esc(m.label) + '</span><br><span class="s">' + esc(m.generic_name || '') + (m.company ? ' · ' + esc(m.company) : '') + '</span></span>' +
                        (m.is_favorite_company ? '<span class="fv-tag brand">Your brand</span>' : '') + '</button>';
                }).join('');
                results.classList.add('show');
                results._list = list;
            }).catch(function () { results.classList.remove('show'); });
    }, 220));

    results.addEventListener('click', function (e) {
        var b = e.target.closest('.fv-opt'); if (!b || !results._list) return;
        var m = results._list[parseInt(b.getAttribute('data-i'), 10)];
        medId.value = m.id; current.form = m.dosage_form || '';
        search.value = m.label; results.classList.remove('show');
        showPicked(m.label, (m.generic_name || '') + (m.company ? ' · ' + m.company : ''));
        syncDoseFields();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.fv-search')) { results.classList.remove('show'); if (cBox) cBox.classList.remove('show'); }
    });

    /* ---------- Quick chips ---------- */
    modalEl.addEventListener('click', function (e) {
        var chip = e.target.closest('.fv-chip'); if (!chip) return;
        var wrap = chip.closest('[data-chips]');
        $(wrap.getAttribute('data-chips')).value = chip.getAttribute('data-value');
    });

    /* ---------- Must pick a medicine when adding ---------- */
    form.addEventListener('submit', function (e) {
        if ($('fvMode').value === 'add' && !medId.value) {
            e.preventDefault();
            search.classList.add('is-invalid');
            search.focus();
        }
    });
    search.addEventListener('input', function () { search.classList.remove('is-invalid'); });

    /* ---------- Re-open after a validation error ---------- */
    if (REOPEN) {
        var mode = $('fvMode').value;
        current.form = $('fvFormField').value;
        if (mode === 'edit' && $('fvEditId').value) {
            form.action = UPDATE_URL.replace('__ID__', $('fvEditId').value); methodInput.disabled = false;
            pickWrap.style.display = 'none';
            $('fvModalTitle').textContent = 'Edit favorite medicine';
        } else if (medId.value) {
            search.value = $('fvLabelField').value;
        }
        if ($('fvLabelField').value) showPicked($('fvLabelField').value, $('fvSubField').value);
        syncDefault(); syncDoseFields(); modal.show();
    }
})();
</script>
@endpush
