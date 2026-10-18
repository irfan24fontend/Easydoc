@php
    $editing = (bool) $profile;

    $val = fn (string $key, $default = null) => old($key, $profile->{$key} ?? $default);

    $eduRows = old('education', $profile->education ?? []);
    if (empty($eduRows)) { $eduRows = [['degree' => '', 'institution' => '', 'year' => '']]; }

    $expRows = old('work_experience', $profile->work_experience ?? []);

    $expertiseText = old('expertise', $profile ? implode(', ', $profile->expertise ?? []) : '');
    $languagesText = old('languages', $profile ? implode(', ', $profile->languages ?? []) : 'Bangla, English');

    $published = old('is_published', $profile->is_published ?? true);
@endphp

<form method="POST"
      action="{{ $editing ? route('doctor.public-profile.update') : route('doctor.public-profile.store') }}"
      enctype="multipart/form-data" novalidate>
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please fix the highlighted fields.</strong> Choose your photo again if you were uploading one.
        </div>
    @endif

    {{-- Verified information (read only) --}}
    <div class="pp-card">
        <div class="pp-card-head">
            <h2><i class="fa-solid fa-shield-halved"></i> Your verified information</h2>
            <span class="pp-badge ok">Approved</span>
        </div>
        <p class="pp-help mb-3">These details come from your approved registration and are shown on your public profile. To change any of them, contact support.</p>
        <div class="row g-3 pp-readonly">
            <div class="col-md-4"><span>Name</span><strong>{{ $doctor->displayName() }}</strong></div>
            <div class="col-md-4"><span>BMDC number</span><strong>{{ $doctor->bmdc_number ?? '—' }}</strong></div>
            <div class="col-md-4"><span>Speciality</span><strong>{{ $doctor->speciality ?? '—' }}</strong></div>
            <div class="col-md-4"><span>Degrees</span><strong>{{ $doctor->qualification ?? '—' }}</strong></div>
            <div class="col-md-4"><span>Designation</span><strong>{{ $doctor->designation ?? '—' }}</strong></div>
            <div class="col-md-4"><span>Experience</span><strong>{{ $doctor->experienceLabel() ?? '—' }}</strong></div>
        </div>
    </div>

    {{-- Link + photo --}}
    <div class="pp-card">
        <div class="pp-card-head"><h2><i class="fa-solid fa-link"></i> Profile link and photo</h2></div>
        <div class="row g-4">
            <div class="col-lg-7">
                <label class="pp-label" for="slug">Your profile link <span class="req">*</span></label>
                @if ($editing)
                    <div class="pp-slug locked">
                        <span>{{ url('/dr') }}/</span><strong>{{ $profile->slug }}</strong>
                    </div>
                    <div class="pp-help">The link is fixed so that links you already shared keep working.</div>
                @else
                    <div class="pp-slug">
                        <span>{{ url('/dr') }}/</span>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $suggestedSlug ?? '') }}" maxlength="60" autocomplete="off"
                               class="@error('slug') is-invalid @enderror" aria-describedby="slugHelp">
                    </div>
                    <div class="pp-help" id="slugHelp">Small letters, numbers and hyphens only. You cannot change it later.</div>
                    @error('slug') <div class="pp-error">{{ $message }}</div> @enderror
                @endif
            </div>
            <div class="col-lg-5">
                <label class="pp-label" for="photo">Profile photo</label>
                <div class="d-flex align-items-center gap-3">
                    @if ($doctor->photoUrl())
                        <img src="{{ $doctor->photoUrl() }}" alt="Current photo" class="pp-photo">
                    @else
                        <div class="pp-photo ph"><i class="fa-solid fa-user-doctor"></i></div>
                    @endif
                    <div class="flex-grow-1">
                        <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm @error('photo') is-invalid @enderror">
                        <div class="pp-help">JPG, PNG or WebP, up to 2 MB. Leave empty to keep the current one.</div>
                        @error('photo') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- About --}}
    <div class="pp-card">
        <div class="pp-card-head"><h2><i class="fa-solid fa-pen-to-square"></i> About you</h2></div>
        <div class="mb-3">
            <label class="pp-label" for="tagline">Short headline <span class="req">*</span></label>
            <input type="text" id="tagline" name="tagline" value="{{ $val('tagline') }}" maxlength="160"
                   class="form-control @error('tagline') is-invalid @enderror"
                   placeholder="e.g. Experienced diabetes and thyroid specialist in Dhaka">
            @error('tagline') <div class="pp-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="pp-label" for="about">About <span class="req">*</span></label>
            <textarea id="about" name="about" rows="6" maxlength="3000" class="form-control @error('about') is-invalid @enderror"
                      placeholder="Your approach to patients, the conditions you treat, and your experience.">{{ $val('about') }}</textarea>
            <div class="pp-help d-flex justify-content-between"><span>At least 80 characters.</span><span id="aboutCount">0 / 3000</span></div>
            @error('about') <div class="pp-error">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Education --}}
    <div class="pp-card">
        <div class="pp-card-head">
            <h2><i class="fa-solid fa-graduation-cap"></i> Education <span class="req">*</span></h2>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add="education"><i class="fa-solid fa-plus"></i> Add degree</button>
        </div>
        @error('education') <div class="pp-error mb-2">{{ $message }}</div> @enderror
        <div id="educationRows" class="pp-rows">
            @foreach ($eduRows as $i => $row)
                <div class="pp-row" data-row>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="education[{{ $i }}][degree]" value="{{ $row['degree'] ?? '' }}" placeholder="Degree (e.g. MBBS)"
                                   class="form-control @if($errors->has("education.$i.degree")) is-invalid @endif" aria-label="Degree">
                            @if($errors->has("education.$i.degree"))<div class="pp-error">{{ $errors->first("education.$i.degree") }}</div>@endif
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="education[{{ $i }}][institution]" value="{{ $row['institution'] ?? '' }}" placeholder="Institution"
                                   class="form-control @if($errors->has("education.$i.institution")) is-invalid @endif" aria-label="Institution">
                            @if($errors->has("education.$i.institution"))<div class="pp-error">{{ $errors->first("education.$i.institution") }}</div>@endif
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="education[{{ $i }}][year]" value="{{ $row['year'] ?? '' }}" placeholder="Year" min="1950" max="{{ date('Y') }}"
                                   class="form-control @if($errors->has("education.$i.year")) is-invalid @endif" aria-label="Year">
                            @if($errors->has("education.$i.year"))<div class="pp-error">{{ $errors->first("education.$i.year") }}</div>@endif
                        </div>
                        <div class="col-md-1 d-flex align-items-start">
                            <button type="button" class="btn btn-light border w-100" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Experience --}}
    <div class="pp-card">
        <div class="pp-card-head">
            <h2><i class="fa-solid fa-briefcase-medical"></i> Work experience</h2>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add="work_experience"><i class="fa-solid fa-plus"></i> Add position</button>
        </div>
        <div class="pp-help mb-2">Leave "To" empty if you still work there.</div>
        <div id="work_experienceRows" class="pp-rows">
            @foreach ($expRows as $i => $row)
                <div class="pp-row" data-row>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="work_experience[{{ $i }}][position]" value="{{ $row['position'] ?? '' }}" placeholder="Position"
                                   class="form-control @if($errors->has("work_experience.$i.position")) is-invalid @endif" aria-label="Position">
                            @if($errors->has("work_experience.$i.position"))<div class="pp-error">{{ $errors->first("work_experience.$i.position") }}</div>@endif
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="work_experience[{{ $i }}][organization]" value="{{ $row['organization'] ?? '' }}" placeholder="Hospital / organization"
                                   class="form-control @if($errors->has("work_experience.$i.organization")) is-invalid @endif" aria-label="Organization">
                            @if($errors->has("work_experience.$i.organization"))<div class="pp-error">{{ $errors->first("work_experience.$i.organization") }}</div>@endif
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="work_experience[{{ $i }}][from]" value="{{ $row['from'] ?? '' }}" placeholder="From" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="From year">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="work_experience[{{ $i }}][to]" value="{{ $row['to'] ?? '' }}" placeholder="To" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="To year">
                        </div>
                        <div class="col-md-1 d-flex align-items-start">
                            <button type="button" class="btn btn-light border w-100" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Expertise & languages --}}
    <div class="pp-card">
        <div class="pp-card-head"><h2><i class="fa-solid fa-stethoscope"></i> Expertise and languages</h2></div>
        <div class="row g-3">
            <div class="col-lg-7">
                <label class="pp-label" for="expertise">Areas of expertise <span class="req">*</span></label>
                <textarea id="expertise" name="expertise" rows="3" class="form-control @error('expertise') is-invalid @enderror"
                          placeholder="Separate with commas: Diabetes, Thyroid disorders, Hypertension">{{ $expertiseText }}</textarea>
                @error('expertise') <div class="pp-error">{{ $message }}</div> @enderror
            </div>
            <div class="col-lg-5">
                <label class="pp-label" for="languages">Languages you speak</label>
                <input type="text" id="languages" name="languages" value="{{ $languagesText }}" class="form-control @error('languages') is-invalid @enderror" placeholder="Bangla, English">
                @error('languages') <div class="pp-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Fee + links --}}
    <div class="pp-card">
        <div class="pp-card-head"><h2><i class="fa-solid fa-circle-info"></i> Fee and links</h2></div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="pp-label" for="consultation_fee">Consultation fee (BDT)</label>
                <input type="number" id="consultation_fee" name="consultation_fee" value="{{ $val('consultation_fee') }}" min="0" max="100000" class="form-control @error('consultation_fee') is-invalid @enderror">
                @error('consultation_fee') <div class="pp-error">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="pp-label" for="website_url">Website</label>
                <input type="url" id="website_url" name="website_url" value="{{ $val('website_url') }}" placeholder="https://" class="form-control @error('website_url') is-invalid @enderror">
                @error('website_url') <div class="pp-error">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="pp-label" for="facebook_url">Facebook page</label>
                <input type="url" id="facebook_url" name="facebook_url" value="{{ $val('facebook_url') }}" placeholder="https://facebook.com/..." class="form-control @error('facebook_url') is-invalid @enderror">
                @error('facebook_url') <div class="pp-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Privacy --}}
    <div class="pp-card">
        <div class="pp-card-head"><h2><i class="fa-solid fa-lock"></i> Privacy and visibility</h2></div>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="show_contact" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="show_contact" name="show_contact" value="1" @checked(old('show_contact', $profile->show_contact ?? false))>
            <label class="form-check-label" for="show_contact">Show my mobile number, email and chamber phone numbers on the public page</label>
            <div class="pp-help">Off by default. When off, patients see your chambers, days and timing but no phone or email.</div>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="is_published" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1" @checked($published)>
            <label class="form-check-label" for="is_published">Make my profile public</label>
            <div class="pp-help">Switch off to hide the page. The link stays reserved for you.</div>
        </div>
    </div>

    <div class="pp-actions">
        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $editing ? 'Save changes' : 'Create public profile' }}
        </button>
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-light border px-4 py-2">Cancel</a>
    </div>
</form>

{{-- Repeater row templates --}}
<template id="tpl-education">
    <div class="pp-row" data-row>
        <div class="row g-2">
            <div class="col-md-4"><input type="text" name="education[__I__][degree]" placeholder="Degree (e.g. MBBS)" class="form-control" aria-label="Degree"></div>
            <div class="col-md-5"><input type="text" name="education[__I__][institution]" placeholder="Institution" class="form-control" aria-label="Institution"></div>
            <div class="col-md-2"><input type="number" name="education[__I__][year]" placeholder="Year" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="Year"></div>
            <div class="col-md-1 d-flex align-items-start"><button type="button" class="btn btn-light border w-100" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button></div>
        </div>
    </div>
</template>
<template id="tpl-work_experience">
    <div class="pp-row" data-row>
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="work_experience[__I__][position]" placeholder="Position" class="form-control" aria-label="Position"></div>
            <div class="col-md-4"><input type="text" name="work_experience[__I__][organization]" placeholder="Hospital / organization" class="form-control" aria-label="Organization"></div>
            <div class="col-md-2"><input type="number" name="work_experience[__I__][from]" placeholder="From" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="From year"></div>
            <div class="col-md-2"><input type="number" name="work_experience[__I__][to]" placeholder="To" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="To year"></div>
            <div class="col-md-1 d-flex align-items-start"><button type="button" class="btn btn-light border w-100" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button></div>
        </div>
    </div>
</template>

@push('styles')
<style>
    .pp-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:1.5rem; margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(15,23,42,.04); }
    .pp-card-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:1rem; flex-wrap:wrap; }
    .pp-card-head h2 { font-size:1rem; font-weight:700; color:#0f172a; margin:0; display:flex; align-items:center; gap:.6rem; }
    .pp-card-head h2 i { color:#4f46e5; width:18px; text-align:center; }
    .pp-label { display:block; font-size:.8rem; font-weight:700; color:#334155; margin-bottom:.4rem; }
    .req { color:#dc2626; }
    .pp-help { font-size:.78rem; color:#64748b; margin-top:.35rem; }
    .pp-error { font-size:.8rem; color:#dc2626; margin-top:.3rem; }
    .pp-readonly span { display:block; font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; font-weight:700; }
    .pp-readonly strong { font-size:.92rem; color:#0f172a; }
    .pp-badge { font-size:.72rem; font-weight:700; padding:.3rem .7rem; border-radius:999px; }
    .pp-badge.ok { background:#d1fae5; color:#065f46; }
    .pp-badge.warn { background:#fef3c7; color:#92400e; }
    .pp-badge.off { background:#e2e8f0; color:#475569; }
    .pp-slug { display:flex; align-items:center; border:1px solid #cbd5e1; border-radius:10px; overflow:hidden; background:#fff; }
    .pp-slug span { background:#f1f5f9; padding:.6rem .75rem; font-size:.82rem; color:#475569; white-space:nowrap; border-right:1px solid #cbd5e1; }
    .pp-slug input { border:0; outline:0; padding:.6rem .75rem; flex:1; min-width:0; font-size:.92rem; }
    .pp-slug:focus-within { border-color:#4f46e5; box-shadow:0 0 0 4px rgba(79,70,229,.12); }
    .pp-slug.locked { background:#f8fafc; }
    .pp-slug.locked strong { padding:.6rem .75rem; font-size:.92rem; }
    .pp-photo { width:72px; height:72px; border-radius:16px; object-fit:cover; border:1px solid #e2e8f0; flex:none; }
    .pp-photo.ph { background:#f1f5f9; display:grid; place-items:center; color:#94a3b8; font-size:1.6rem; }
    .pp-rows { display:grid; gap:.6rem; }
    .pp-row { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:.6rem; }
    .pp-actions { display:flex; gap:.75rem; padding-bottom:2rem; }
    .pp-status { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; }
    .pp-linkbox { display:flex; gap:.5rem; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:.4rem .4rem .4rem .8rem; }
    .pp-linkbox code { flex:1; font-size:.85rem; color:#334155; word-break:break-all; background:none; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        // Repeater rows (education / work experience)
        document.querySelectorAll('[data-add]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-add');
                var box = document.getElementById(key + 'Rows');
                var tpl = document.getElementById('tpl-' + key).innerHTML;
                var next = parseInt(box.getAttribute('data-next') || box.querySelectorAll('[data-row]').length, 10);
                var wrap = document.createElement('div');
                wrap.innerHTML = tpl.replace(/__I__/g, next).trim();
                box.appendChild(wrap.firstElementChild);
                box.setAttribute('data-next', next + 1);
            });
        });

        document.addEventListener('click', function (e) {
            var rm = e.target.closest('[data-remove]');
            if (!rm) return;
            var row = rm.closest('[data-row]');
            var box = row.parentElement;
            // keep at least one education row on screen
            if (box.id === 'educationRows' && box.querySelectorAll('[data-row]').length === 1) {
                row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
                return;
            }
            row.remove();
        });

        // Counter for About
        var about = document.getElementById('about');
        var count = document.getElementById('aboutCount');
        if (about && count) {
            var update = function () { count.textContent = about.value.length + ' / 3000'; };
            about.addEventListener('input', update); update();
        }

        // Copy public link
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = btn.getAttribute('data-copy'), original = btn.innerHTML;
                function done() { btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied'; setTimeout(function () { btn.innerHTML = original; }, 1800); }
                if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done); }
                else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (er) {} document.body.removeChild(t); }
            });
        });
    })();
</script>
@endpush
