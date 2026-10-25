@php
    $editing = (bool) $profile;

    $val = fn (string $key, $default = null) => old($key, $profile->{$key} ?? $default);

    $eduRows = old('education', $profile->education ?? []);
    if (empty($eduRows)) { $eduRows = [['degree' => '', 'institution' => '', 'year' => '']]; }

    $expRows = old('work_experience', $profile->work_experience ?? []);

    $expertiseText = old('expertise', $profile ? implode(', ', $profile->expertise ?? []) : '');
    $languagesText = old('languages', $profile ? implode(', ', $profile->languages ?? []) : 'Bangla, English');

    $published = old('is_published', $profile->is_published ?? true);
    $slugBase = url('/dr');
@endphp

<form method="POST"
      action="{{ $editing ? route('doctor.public-profile.update') : route('doctor.public-profile.store') }}"
      enctype="multipart/form-data" novalidate id="ppForm">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="pp-alert" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div><strong>Please fix the highlighted fields.</strong> Choose your photo again if you were uploading one.</div>
        </div>
    @endif

    <div class="pp-layout">
        {{-- ===================== LEFT: FORM ===================== --}}
        <div class="pp-main">

            {{-- Verified information (read only) --}}
            <section class="pp-card">
                <div class="pp-card-head">
                    <h2><span class="pp-ico"><i class="fa-solid fa-shield-halved"></i></span> Your verified information</h2>
                    <span class="pp-badge ok"><i class="fa-solid fa-circle-check"></i> Approved</span>
                </div>
                <p class="pp-help mb-3">এই তথ্যগুলো আপনার অনুমোদিত রেজিস্ট্রেশন থেকে আসে এবং পাবলিক প্রোফাইলে দেখায়। পরিবর্তন করতে সাপোর্টে যোগাযোগ করুন।</p>
                <div class="pp-readonly">
                    <div><span>Name</span><strong>{{ $doctor->displayName() }}</strong></div>
                    <div><span>BMDC number</span><strong>{{ $doctor->bmdc_number ?? '—' }}</strong></div>
                    <div><span>Speciality</span><strong>{{ $doctor->speciality ?? '—' }}</strong></div>
                    <div><span>Degrees</span><strong>{{ $doctor->qualification ?? '—' }}</strong></div>
                    <div><span>Designation</span><strong>{{ $doctor->designation ?? '—' }}</strong></div>
                    <div><span>Experience</span><strong>{{ $doctor->experienceLabel() ?? '—' }}</strong></div>
                </div>
            </section>

            {{-- Link + photo --}}
            <section class="pp-card">
                <div class="pp-card-head"><h2><span class="pp-ico"><i class="fa-solid fa-link"></i></span> Profile link and photo</h2></div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <label class="pp-label" for="slug">Your profile link @unless($editing)<span class="req">*</span>@endunless</label>
                        @if ($editing)
                            <div class="pp-slug locked">
                                <span>{{ $slugBase }}/</span><strong>{{ $profile->slug }}</strong>
                                <i class="fa-solid fa-lock ms-auto me-3 text-muted"></i>
                            </div>
                            <div class="pp-help">লিংকটি স্থির, যাতে আগে শেয়ার করা লিংক সবসময় কাজ করে।</div>
                        @else
                            <div class="pp-slug">
                                <span>{{ $slugBase }}/</span>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $suggestedSlug ?? '') }}" maxlength="60" autocomplete="off"
                                       class="@error('slug') is-invalid @enderror" aria-describedby="slugHelp">
                            </div>
                            <div class="pp-help" id="slugHelp">ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন (-) চলবে। পরে বদলানো যাবে না।</div>
                            @error('slug') <div class="pp-error">{{ $message }}</div> @enderror
                        @endif
                    </div>
                    <div class="col-lg-5">
                        <label class="pp-label" for="photo">Profile photo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="pp-photo" id="ppPhotoBox">
                                @if ($doctor->photoUrl())
                                    <img src="{{ $doctor->photoUrl() }}" alt="Current photo">
                                @else
                                    <i class="fa-solid fa-user-doctor"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <label for="photo" class="pp-upload"><i class="fa-solid fa-cloud-arrow-up"></i> <span id="ppFileName">ছবি বাছুন</span></label>
                                <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" class="d-none">
                                <div class="pp-help">JPG, PNG বা WebP, সর্বোচ্চ 2 MB। খালি রাখলে বর্তমান ছবি থাকবে।</div>
                                @error('photo') <div class="pp-error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- About --}}
            <section class="pp-card">
                <div class="pp-card-head"><h2><span class="pp-ico"><i class="fa-solid fa-pen-to-square"></i></span> About you</h2></div>
                <div class="mb-3">
                    <label class="pp-label" for="tagline">Short headline <span class="req">*</span></label>
                    <input type="text" id="tagline" name="tagline" value="{{ $val('tagline') }}" maxlength="160"
                           class="form-control @error('tagline') is-invalid @enderror"
                           placeholder="e.g. Experienced diabetes and thyroid specialist in Dhaka">
                    <div class="pp-help d-flex justify-content-end"><span id="taglineCount">0 / 160</span></div>
                    @error('tagline') <div class="pp-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="pp-label" for="about">About <span class="req">*</span></label>
                    <textarea id="about" name="about" rows="6" maxlength="3000" class="form-control @error('about') is-invalid @enderror"
                              placeholder="Your approach to patients, the conditions you treat, and your experience.">{{ $val('about') }}</textarea>
                    <div class="pp-help d-flex justify-content-between"><span id="aboutHint">কমপক্ষে ৮০ অক্ষর লিখুন।</span><span id="aboutCount">0 / 3000</span></div>
                    <div class="pp-bar"><i id="aboutBar"></i></div>
                    @error('about') <div class="pp-error">{{ $message }}</div> @enderror
                </div>
            </section>

            {{-- Education --}}
            <section class="pp-card">
                <div class="pp-card-head">
                    <h2><span class="pp-ico"><i class="fa-solid fa-graduation-cap"></i></span> Education <span class="req">*</span></h2>
                    <button type="button" class="pp-btn ghost sm" data-add="education"><i class="fa-solid fa-plus"></i> Add degree</button>
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
                                    <button type="button" class="pp-x" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Experience --}}
            <section class="pp-card">
                <div class="pp-card-head">
                    <h2><span class="pp-ico"><i class="fa-solid fa-briefcase-medical"></i></span> Work experience</h2>
                    <button type="button" class="pp-btn ghost sm" data-add="work_experience"><i class="fa-solid fa-plus"></i> Add position</button>
                </div>
                <div class="pp-help mb-2">এখনো কাজ করলে "To" ঘরটি খালি রাখুন।</div>
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
                                    <button type="button" class="pp-x" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Expertise & languages --}}
            <section class="pp-card">
                <div class="pp-card-head"><h2><span class="pp-ico"><i class="fa-solid fa-stethoscope"></i></span> Expertise and languages</h2></div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <label class="pp-label">Areas of expertise <span class="req">*</span></label>
                        <div class="pp-chips" id="expertiseBox"><div class="pp-chip-list"></div><input type="text" class="pp-chip-input" placeholder="লিখে Enter চাপুন…" aria-label="Add expertise"></div>
                        <input type="hidden" id="expertise" name="expertise" value="{{ $expertiseText }}">
                        <div class="pp-sugs" id="expertiseSugs"></div>
                        @error('expertise') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-lg-5">
                        <label class="pp-label">Languages you speak</label>
                        <div class="pp-chips" id="languagesBox"><div class="pp-chip-list"></div><input type="text" class="pp-chip-input" placeholder="লিখে Enter চাপুন…" aria-label="Add language"></div>
                        <input type="hidden" id="languages" name="languages" value="{{ $languagesText }}">
                        <div class="pp-sugs" id="languagesSugs"></div>
                        @error('languages') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </section>

            {{-- Fee + links --}}
            <section class="pp-card">
                <div class="pp-card-head"><h2><span class="pp-ico"><i class="fa-solid fa-circle-info"></i></span> Fee and links</h2></div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="pp-label" for="consultation_fee">Consultation fee (BDT)</label>
                        <div class="pp-addon"><span>৳</span><input type="number" id="consultation_fee" name="consultation_fee" value="{{ $val('consultation_fee') }}" min="0" max="100000" class="form-control @error('consultation_fee') is-invalid @enderror"></div>
                        @error('consultation_fee') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="pp-label" for="website_url">Website</label>
                        <div class="pp-addon"><span><i class="fa-solid fa-globe"></i></span><input type="url" id="website_url" name="website_url" value="{{ $val('website_url') }}" placeholder="https://" class="form-control @error('website_url') is-invalid @enderror"></div>
                        @error('website_url') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="pp-label" for="facebook_url">Facebook page</label>
                        <div class="pp-addon"><span><i class="fa-brands fa-facebook-f"></i></span><input type="url" id="facebook_url" name="facebook_url" value="{{ $val('facebook_url') }}" placeholder="https://facebook.com/..." class="form-control @error('facebook_url') is-invalid @enderror"></div>
                        @error('facebook_url') <div class="pp-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </section>

            {{-- Privacy --}}
            <section class="pp-card">
                <div class="pp-card-head"><h2><span class="pp-ico"><i class="fa-solid fa-lock"></i></span> Privacy and visibility</h2></div>

                <div class="pp-toggle">
                    <div class="pp-toggle-ico"><i class="fa-solid fa-address-book"></i></div>
                    <div class="flex-grow-1">
                        <label for="show_contact" class="pp-toggle-t">পাবলিক পেজে মোবাইল, ইমেইল ও চেম্বারের ফোন নম্বর দেখান</label>
                        <div class="pp-help">ডিফল্টে বন্ধ। বন্ধ থাকলে রোগীরা চেম্বার, দিন ও সময় দেখবে কিন্তু ফোন বা ইমেইল নয়।</div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input type="hidden" name="show_contact" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="show_contact" name="show_contact" value="1" @checked(old('show_contact', $profile->show_contact ?? false))>
                    </div>
                </div>

                <div class="pp-toggle">
                    <div class="pp-toggle-ico"><i class="fa-solid fa-rocket"></i></div>
                    <div class="flex-grow-1">
                        <label for="is_published" class="pp-toggle-t">আমার প্রোফাইল পাবলিক করুন</label>
                        <div class="pp-help">বন্ধ করলে পেজটি লুকানো থাকবে। লিংকটি আপনার জন্যই সংরক্ষিত থাকবে।</div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input type="hidden" name="is_published" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1" @checked($published)>
                    </div>
                </div>
            </section>

            <div class="pp-actions">
                <button type="submit" class="pp-btn lg">
                    <i class="fa-solid fa-floppy-disk"></i> {{ $editing ? 'Save changes' : 'Create public profile' }}
                </button>
                <a href="{{ route('doctor.dashboard') }}" class="pp-btn ghost lg">Cancel</a>
            </div>
        </div>

        {{-- ===================== RIGHT: LIVE PREVIEW ===================== --}}
        <aside class="pp-side">
            <div class="pp-prev">
                <div class="pp-prev-cover"><span class="pp-prev-live" id="pvStatus"><i class="fa-solid fa-circle"></i> Live preview</span></div>
                <div class="pp-prev-body">
                    <div class="pp-prev-av" id="pvAvatar">
                        @if ($doctor->photoUrl())<img src="{{ $doctor->photoUrl() }}" alt="">@else<i class="fa-solid fa-user-doctor"></i>@endif
                    </div>
                    <div class="pp-prev-name">{{ $doctor->displayName() }} <i class="fa-solid fa-circle-check" title="Verified"></i></div>
                    <div class="pp-prev-sp">{{ $doctor->speciality ?? 'Speciality' }}@if($doctor->qualification) · {{ $doctor->qualification }}@endif</div>
                    <p class="pp-prev-tag" id="pvTag">আপনার হেডলাইন এখানে দেখাবে</p>
                    <div class="pp-prev-chips" id="pvChips"></div>
                    <div class="pp-prev-meta">
                        <div><i class="fa-solid fa-bangladeshi-taka-sign"></i> <span id="pvFee">—</span></div>
                        <div><i class="fa-solid fa-language"></i> <span id="pvLang">—</span></div>
                        @if($doctor->experienceLabel())<div><i class="fa-solid fa-award"></i> {{ $doctor->experienceLabel() }}</div>@endif
                        <div id="pvContact"><i class="fa-solid fa-lock"></i> <span>যোগাযোগ নম্বর লুকানো</span></div>
                    </div>
                    <div class="pp-prev-link"><i class="fa-solid fa-link"></i> <span id="pvLink">{{ $slugBase }}/{{ $editing ? $profile->slug : ($suggestedSlug ?? 'your-name') }}</span></div>
                </div>
            </div>

            <div class="pp-prog">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>প্রোফাইল সম্পূর্ণতা</strong><b id="pgPct">0%</b>
                </div>
                <div class="pp-bar big"><i id="pgBar"></i></div>
                <ul id="pgList"></ul>
            </div>
        </aside>
    </div>
</form>

{{-- Repeater row templates --}}
<template id="tpl-education">
    <div class="pp-row" data-row>
        <div class="row g-2">
            <div class="col-md-4"><input type="text" name="education[__I__][degree]" placeholder="Degree (e.g. MBBS)" class="form-control" aria-label="Degree"></div>
            <div class="col-md-5"><input type="text" name="education[__I__][institution]" placeholder="Institution" class="form-control" aria-label="Institution"></div>
            <div class="col-md-2"><input type="number" name="education[__I__][year]" placeholder="Year" min="1950" max="{{ date('Y') }}" class="form-control" aria-label="Year"></div>
            <div class="col-md-1 d-flex align-items-start"><button type="button" class="pp-x" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button></div>
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
            <div class="col-md-1 d-flex align-items-start"><button type="button" class="pp-x" data-remove aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button></div>
        </div>
    </div>
</template>

@push('styles')
<style>
    .pp { --p1:#7c3aed; --p2:#06b6d4; --p3:#ec4899; --glass:rgba(255,255,255,.72); --line:rgba(124,58,237,.16); position:relative; isolation:isolate; max-width:1240px; margin:0 auto; }
    .pp::before { content:''; position:absolute; inset:-16px -10px; z-index:-1; border-radius:32px;
        background: radial-gradient(560px 300px at 6% 0%, rgba(124,58,237,.18), transparent 62%), radial-gradient(480px 300px at 96% 6%, rgba(6,182,212,.18), transparent 62%), radial-gradient(520px 380px at 50% 100%, rgba(236,72,153,.10), transparent 62%); }
    @keyframes ppFlow { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    @keyframes ppIn { from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:none} }

    .pp-hero { background:linear-gradient(120deg,#4c1d95,#7c3aed,#2563eb,#06b6d4); background-size:220% 220%; animation:ppFlow 14s ease infinite; border-radius:26px; padding:24px 28px; color:#fff; position:relative; overflow:hidden; border:1px solid rgba(255,255,255,.25); box-shadow:0 18px 50px rgba(124,58,237,.33), inset 0 1px 0 rgba(255,255,255,.35); margin-bottom:20px; display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
    .pp-hero::after { content:''; position:absolute; width:300px; height:300px; right:-80px; top:-140px; border-radius:50%; background:radial-gradient(circle, rgba(255,255,255,.35), transparent 65%); pointer-events:none; }
    .pp-hero-ico { width:62px; height:62px; border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:26px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.5); backdrop-filter:blur(10px); box-shadow:0 0 0 6px rgba(255,255,255,.12); flex-shrink:0; }
    .pp-hero h1 { font-size:23px; font-weight:800; margin:0; letter-spacing:-.01em; text-shadow:0 2px 18px rgba(0,0,0,.25); }
    .pp-hero p { margin:4px 0 0; color:rgba(255,255,255,.88); font-size:14px; max-width:720px; }
    .req-w { color:#fde68a; font-weight:800; }
    .pp-steps { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
    .pp-steps span { display:inline-flex; align-items:center; gap:8px; font-size:12px; font-weight:600; padding:5px 13px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.3); backdrop-filter:blur(8px); }
    .pp-steps span.on { background:rgba(255,255,255,.28); }
    .pp-steps b { width:20px; height:20px; border-radius:50%; background:#fff; color:#6d28d9; display:inline-flex; align-items:center; justify-content:center; font-size:11px; }

    .pp-status { background:var(--glass); -webkit-backdrop-filter:blur(18px) saturate(165%); backdrop-filter:blur(18px) saturate(165%); border:1px solid rgba(255,255,255,.8); border-radius:24px; padding:20px 24px; margin-bottom:20px; box-shadow:0 10px 40px rgba(76,29,149,.09), inset 0 1px 0 rgba(255,255,255,.85); }
    .pp-status-title { font-weight:800; color:#0f172a; margin-bottom:6px; display:flex; align-items:center; gap:8px; }
    .pp-status-title i { color:var(--p1); }
    .pp-linkbox { display:flex; gap:.5rem; align-items:center; background:rgba(255,255,255,.8); border:1px dashed rgba(124,58,237,.35); border-radius:14px; padding:.4rem .4rem .4rem .9rem; }
    .pp-linkbox code { flex:1; font-size:.85rem; color:#4338ca; word-break:break-all; background:none; font-weight:600; }
    .pp-share { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:12px; font-size:12px; color:#64748b; font-weight:600; }
    .pp-share a { text-decoration:none; font-size:12px; font-weight:700; padding:6px 13px; border-radius:999px; color:#fff; display:inline-flex; align-items:center; gap:6px; transition:transform .15s, filter .15s; }
    .pp-share a:hover { transform:translateY(-1px); filter:brightness(1.08); color:#fff; }
    .pp-share .wa { background:linear-gradient(135deg,#16a34a,#22c55e); } .pp-share .fb { background:linear-gradient(135deg,#1d4ed8,#3b82f6); } .pp-share .ml { background:linear-gradient(135deg,#7c3aed,#a855f7); }

    .pp-layout { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:22px; align-items:start; }
    @media (max-width: 1099px) { .pp-layout { grid-template-columns:1fr; } .pp-side { order:-1; position:static !important; } }
    .pp-side { position:sticky; top:16px; display:grid; gap:18px; }

    .pp-card { background:var(--glass); -webkit-backdrop-filter:blur(18px) saturate(165%); backdrop-filter:blur(18px) saturate(165%); border:1px solid rgba(255,255,255,.8); border-radius:26px; padding:24px; margin-bottom:20px; box-shadow:0 10px 40px rgba(76,29,149,.09), inset 0 1px 0 rgba(255,255,255,.85); animation:ppIn .35s ease both; }
    .pp-card-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:1rem; flex-wrap:wrap; }
    .pp-card-head h2 { font-size:1.02rem; font-weight:800; color:#0f172a; margin:0; display:flex; align-items:center; gap:.7rem; letter-spacing:-.01em; }
    .pp-ico { width:36px; height:36px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:14px; background:linear-gradient(135deg,var(--p1),var(--p2)); box-shadow:0 8px 20px rgba(99,102,241,.35); flex-shrink:0; }
    .pp-label { display:block; font-size:.74rem; font-weight:700; color:#475569; margin-bottom:.45rem; text-transform:uppercase; letter-spacing:.04em; }
    .req { color:#e11d48; }
    .pp-help { font-size:.78rem; color:#64748b; margin-top:.4rem; }
    .pp-error { font-size:.8rem; color:#e11d48; margin-top:.3rem; font-weight:600; }
    .pp-alert { display:flex; gap:12px; align-items:center; background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; border-radius:18px; padding:14px 18px; margin-bottom:20px; font-size:14px; }

    .pp .form-control { background:rgba(255,255,255,.82); border:1px solid var(--line); border-radius:14px; padding:.65rem .9rem; font-size:.92rem; color:#0f172a; transition:border-color .15s, box-shadow .15s; }
    .pp .form-control:focus { border-color:var(--p1); box-shadow:0 0 0 4px rgba(124,58,237,.13), 0 0 28px rgba(6,182,212,.25); background:#fff; }
    .pp .form-control.is-invalid { border-color:#fb7185; background-image:none; }

    .pp-readonly { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
    .pp-readonly > div { background:linear-gradient(135deg,rgba(124,58,237,.06),rgba(6,182,212,.06)); border:1px solid var(--line); border-radius:16px; padding:12px 14px; }
    .pp-readonly span { display:block; font-size:.68rem; text-transform:uppercase; letter-spacing:.07em; color:#64748b; font-weight:700; }
    .pp-readonly strong { font-size:.92rem; color:#0f172a; }

    .pp-badge { font-size:.72rem; font-weight:700; padding:.32rem .75rem; border-radius:999px; display:inline-flex; align-items:center; gap:6px; }
    .pp-badge.ok { background:#d1fae5; color:#065f46; } .pp-badge.warn { background:#fef3c7; color:#92400e; } .pp-badge.off { background:#e2e8f0; color:#475569; }

    .pp-slug { display:flex; align-items:center; border:1px solid var(--line); border-radius:14px; overflow:hidden; background:rgba(255,255,255,.82); }
    .pp-slug span { background:rgba(124,58,237,.07); padding:.65rem .8rem; font-size:.82rem; color:#4338ca; white-space:nowrap; border-right:1px solid var(--line); font-weight:600; }
    .pp-slug input { border:0; outline:0; padding:.65rem .8rem; flex:1; min-width:0; font-size:.92rem; background:transparent; }
    .pp-slug:focus-within { border-color:var(--p1); box-shadow:0 0 0 4px rgba(124,58,237,.13), 0 0 28px rgba(6,182,212,.25); }
    .pp-slug.locked { background:rgba(248,250,252,.7); } .pp-slug.locked strong { padding:.65rem .8rem; font-size:.92rem; }

    .pp-photo { width:84px; height:84px; border-radius:24px; overflow:hidden; flex:none; background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(6,182,212,.12)); border:1px solid var(--line); display:grid; place-items:center; color:#a78bfa; font-size:1.8rem; box-shadow:0 0 0 5px rgba(124,58,237,.07); }
    .pp-photo img { width:100%; height:100%; object-fit:cover; }
    .pp-upload { display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:.82rem; font-weight:700; padding:.5rem .95rem; border-radius:12px; color:#4338ca; background:rgba(99,102,241,.1); border:1px dashed rgba(99,102,241,.5); max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; transition:background .15s; }
    .pp-upload:hover { background:rgba(99,102,241,.18); }

    .pp-bar { height:6px; border-radius:6px; background:rgba(148,163,184,.25); margin-top:8px; overflow:hidden; }
    .pp-bar.big { height:10px; }
    .pp-bar i { display:block; height:100%; width:0; border-radius:6px; background:linear-gradient(90deg,var(--p1),var(--p2)); transition:width .3s, background .3s; }

    .pp-rows { display:grid; gap:.7rem; }
    .pp-row { background:rgba(255,255,255,.55); border:1px solid var(--line); border-radius:16px; padding:.7rem; animation:ppIn .25s ease both; }
    .pp-x { width:100%; height:42px; border:1px solid var(--line); background:#fff; color:#94a3b8; border-radius:12px; transition:all .15s; }
    .pp-x:hover { background:#fff1f2; color:#e11d48; border-color:#fecdd3; }

    .pp-chips { display:flex; flex-wrap:wrap; gap:6px; align-items:center; background:rgba(255,255,255,.82); border:1px solid var(--line); border-radius:14px; padding:8px 10px; min-height:48px; cursor:text; transition:border-color .15s, box-shadow .15s; }
    .pp-chips:focus-within { border-color:var(--p1); box-shadow:0 0 0 4px rgba(124,58,237,.13), 0 0 28px rgba(6,182,212,.25); }
    .pp-chip-list { display:contents; }
    .pp-chip { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; font-weight:600; color:#4338ca; background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(6,182,212,.12)); border:1px solid rgba(124,58,237,.25); padding:4px 6px 4px 11px; border-radius:999px; }
    .pp-chip button { border:0; background:rgba(124,58,237,.14); color:#6d28d9; width:18px; height:18px; border-radius:50%; font-size:10px; line-height:1; display:inline-flex; align-items:center; justify-content:center; padding:0; }
    .pp-chip button:hover { background:#e11d48; color:#fff; }
    .pp-chip-input { flex:1; min-width:120px; border:0; outline:0; background:transparent; font-size:.9rem; padding:3px 4px; }
    .pp-sugs { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
    .pp-sugs button { border:1px dashed rgba(124,58,237,.35); background:transparent; color:#64748b; font-size:.76rem; font-weight:600; border-radius:999px; padding:3px 11px; transition:all .15s; }
    .pp-sugs button:hover { background:rgba(124,58,237,.1); color:#4338ca; }
    .pp-sugs button:disabled { display:none; }

    .pp-addon { display:flex; align-items:stretch; }
    .pp-addon span { display:flex; align-items:center; justify-content:center; min-width:42px; background:rgba(124,58,237,.08); border:1px solid var(--line); border-right:0; border-radius:14px 0 0 14px; color:#6d28d9; font-weight:700; }
    .pp-addon .form-control { border-radius:0 14px 14px 0; min-width:0; }

    .pp-toggle { display:flex; align-items:center; gap:14px; background:rgba(255,255,255,.55); border:1px solid var(--line); border-radius:18px; padding:14px 16px; }
    .pp-toggle + .pp-toggle { margin-top:12px; }
    .pp-toggle-ico { width:42px; height:42px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(124,58,237,.14),rgba(6,182,212,.14)); color:var(--p1); flex-shrink:0; }
    .pp-toggle-t { font-weight:700; font-size:.9rem; color:#0f172a; cursor:pointer; }
    .pp .form-switch .form-check-input { width:3em; height:1.5em; cursor:pointer; border-color:#cbd5e1; }
    .pp .form-switch .form-check-input:checked { background-color:#7c3aed; border-color:#7c3aed; box-shadow:0 0 16px rgba(124,58,237,.45); }
    .pp .form-switch .form-check-input:focus { box-shadow:0 0 0 4px rgba(124,58,237,.18); }

    .pp-btn { border:0; border-radius:14px; padding:.6rem 1.15rem; font-size:.85rem; font-weight:700; color:#fff !important; text-decoration:none; background:linear-gradient(135deg,#6d28d9,#4f46e5 55%,#0891b2); box-shadow:0 8px 22px rgba(99,102,241,.35); display:inline-flex; align-items:center; gap:8px; transition:transform .15s, filter .15s; }
    .pp-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }
    .pp-btn.sm { padding:.42rem .9rem; font-size:.78rem; border-radius:12px; }
    .pp-btn.lg { padding:.8rem 1.8rem; font-size:.95rem; }
    .pp-btn.ghost { background:#fff; color:#475569 !important; border:1px solid #e2e8f0; box-shadow:none; }
    .pp-btn.ghost:hover { background:#f5f3ff; color:#4338ca !important; }
    .pp-btn.go { background:linear-gradient(135deg,#059669,#06b6d4); box-shadow:0 8px 22px rgba(5,150,105,.35); }
    .pp-actions { display:flex; gap:.75rem; padding:14px 18px; margin-bottom:2rem; position:sticky; bottom:12px; z-index:20; background:rgba(255,255,255,.82); -webkit-backdrop-filter:blur(18px); backdrop-filter:blur(18px); border:1px solid rgba(255,255,255,.9); border-radius:20px; box-shadow:0 12px 40px rgba(76,29,149,.18); }

    /* ---- live preview ---- */
    .pp-prev { background:#fff; border:1px solid rgba(255,255,255,.9); border-radius:26px; overflow:hidden; box-shadow:0 18px 50px rgba(76,29,149,.18); }
    .pp-prev-cover { height:92px; background:linear-gradient(120deg,#4c1d95,#7c3aed,#2563eb,#06b6d4); background-size:220% 220%; animation:ppFlow 14s ease infinite; position:relative; }
    .pp-prev-live { position:absolute; top:12px; right:12px; font-size:10px; font-weight:700; color:#fff; background:rgba(0,0,0,.25); padding:3px 10px; border-radius:999px; backdrop-filter:blur(6px); }
    .pp-prev-live i { font-size:7px; color:#34d399; margin-right:4px; }
    .pp-prev-body { padding:0 20px 20px; text-align:center; }
    .pp-prev-av { width:86px; height:86px; border-radius:26px; margin:-43px auto 10px; overflow:hidden; border:4px solid #fff; background:linear-gradient(135deg,#ede9fe,#cffafe); display:grid; place-items:center; color:#a78bfa; font-size:1.8rem; box-shadow:0 10px 26px rgba(76,29,149,.25); }
    .pp-prev-av img { width:100%; height:100%; object-fit:cover; }
    .pp-prev-name { font-weight:800; font-size:1.05rem; color:#0f172a; } .pp-prev-name i { color:#3b82f6; font-size:.85rem; }
    .pp-prev-sp { font-size:.8rem; color:#6d28d9; font-weight:700; margin-top:2px; }
    .pp-prev-tag { font-size:.84rem; color:#64748b; margin:10px 0; min-height:20px; }
    .pp-prev-tag.ph { color:#cbd5e1; font-style:italic; }
    .pp-prev-chips { display:flex; flex-wrap:wrap; justify-content:center; gap:5px; margin-bottom:12px; }
    .pp-prev-chips span { font-size:.7rem; font-weight:600; color:#4338ca; background:#eef2ff; border-radius:999px; padding:3px 10px; }
    .pp-prev-meta { display:grid; gap:7px; text-align:left; background:linear-gradient(135deg,rgba(124,58,237,.06),rgba(6,182,212,.06)); border-radius:16px; padding:12px 14px; font-size:.8rem; color:#334155; }
    .pp-prev-meta i { width:18px; color:var(--p1); text-align:center; margin-right:4px; }
    .pp-prev-link { margin-top:12px; font-size:.7rem; color:#94a3b8; word-break:break-all; }

    .pp-prog { background:var(--glass); -webkit-backdrop-filter:blur(18px); backdrop-filter:blur(18px); border:1px solid rgba(255,255,255,.8); border-radius:22px; padding:18px 20px; box-shadow:0 10px 40px rgba(76,29,149,.09); font-size:.85rem; color:#0f172a; }
    .pp-prog b { background:linear-gradient(135deg,var(--p1),var(--p2)); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; font-size:1.1rem; }
    .pp-prog ul { list-style:none; margin:12px 0 0; padding:0; display:grid; gap:6px; }
    .pp-prog li { display:flex; align-items:center; gap:9px; font-size:.78rem; color:#94a3b8; }
    .pp-prog li i { width:18px; height:18px; border-radius:50%; border:1.5px solid #cbd5e1; display:inline-flex; align-items:center; justify-content:center; font-size:9px; color:transparent; }
    .pp-prog li.ok { color:#334155; } .pp-prog li.ok i { background:#10b981; border-color:#10b981; color:#fff; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var EDITING = @json($editing);
    var SLUG_BASE = @json($slugBase);
    var HAS_PHOTO = @json((bool) $doctor->photoUrl());

    // ---------- Repeater rows (education / work experience) ----------
    document.querySelectorAll('[data-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-add');
            var box = $(key + 'Rows');
            var tpl = $('tpl-' + key).innerHTML;
            var next = parseInt(box.getAttribute('data-next') || box.querySelectorAll('[data-row]').length, 10);
            var wrap = document.createElement('div');
            wrap.innerHTML = tpl.replace(/__I__/g, next).trim();
            box.appendChild(wrap.firstElementChild);
            box.setAttribute('data-next', next + 1);
            refresh();
        });
    });
    document.addEventListener('click', function (e) {
        var rm = e.target.closest('[data-remove]');
        if (!rm) return;
        var row = rm.closest('[data-row]');
        var box = row.parentElement;
        if (box.id === 'educationRows' && box.querySelectorAll('[data-row]').length === 1) {
            row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        } else { row.remove(); }
        refresh();
    });

    // ---------- Counters ----------
    var about = $('about'), tagline = $('tagline');
    function counters() {
        $('taglineCount').textContent = tagline.value.length + ' / 160';
        var n = about.value.length;
        $('aboutCount').textContent = n + ' / 3000';
        var pct = Math.min(100, Math.round(n / 80 * 100));
        $('aboutBar').style.width = pct + '%';
        $('aboutBar').style.background = n >= 80 ? '' : 'linear-gradient(90deg,#f97316,#f59e0b)';
        $('aboutHint').textContent = n >= 80 ? '✓ যথেষ্ট লেখা হয়েছে' : 'আরও ' + (80 - n) + ' অক্ষর লিখুন (কমপক্ষে ৮০)।';
        $('aboutHint').style.color = n >= 80 ? '#059669' : '';
    }

    // ---------- Chip inputs (expertise / languages) ----------
    function chipInput(boxId, hiddenId, sugId, suggestions) {
        var box = $(boxId), list = box.querySelector('.pp-chip-list'), input = box.querySelector('.pp-chip-input');
        var hidden = $(hiddenId), sugs = $(sugId);
        var items = hidden.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        function has(v) { return items.some(function (x) { return x.toLowerCase() === v.toLowerCase(); }); }
        function sync() { hidden.value = items.join(', '); }
        function draw() {
            list.innerHTML = '';
            items.forEach(function (v, i) {
                var s = document.createElement('span'); s.className = 'pp-chip'; s.textContent = v;
                var b = document.createElement('button'); b.type = 'button'; b.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                b.addEventListener('click', function (e) { e.stopPropagation(); items.splice(i, 1); sync(); draw(); refresh(); });
                s.appendChild(b); list.appendChild(s);
            });
            sugs.querySelectorAll('button').forEach(function (b) { b.disabled = has(b.getAttribute('data-v')); });
        }
        function add(v) { v = v.trim(); if (v && !has(v)) { items.push(v); sync(); draw(); refresh(); } }
        suggestions.forEach(function (v) {
            var b = document.createElement('button'); b.type = 'button'; b.setAttribute('data-v', v); b.textContent = '+ ' + v;
            b.addEventListener('click', function () { add(v); }); sugs.appendChild(b);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(input.value); input.value = ''; }
            else if (e.key === 'Backspace' && !input.value && items.length) { items.pop(); sync(); draw(); refresh(); }
        });
        input.addEventListener('blur', function () { if (input.value.trim()) { add(input.value); input.value = ''; } });
        box.addEventListener('click', function () { input.focus(); });
        draw();
        return { get: function () { return items.slice(); }, flush: function () { if (input.value.trim()) { add(input.value); input.value = ''; } } };
    }
    var expertise = chipInput('expertiseBox', 'expertise', 'expertiseSugs', ['Diabetes', 'Hypertension', 'Thyroid disorders', 'Asthma', 'Fever & infections', 'Skin diseases', 'Heart disease', 'Child health', "Women's health", 'Gastric problems', 'Back & joint pain', 'Kidney disease', 'Mental health', 'Allergy']);
    var languages = chipInput('languagesBox', 'languages', 'languagesSugs', ['Bangla', 'English', 'Hindi', 'Urdu', 'Arabic']);
    $('ppForm').addEventListener('submit', function () { expertise.flush(); languages.flush(); });

    // ---------- Photo preview ----------
    $('photo').addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (!f) return;
        $('ppFileName').textContent = f.name;
        var url = URL.createObjectURL(f);
        ['ppPhotoBox', 'pvAvatar'].forEach(function (id) { $(id).innerHTML = '<img src="' + url + '" alt="">'; });
        HAS_PHOTO = true; refresh();
    });

    // ---------- Slug cleaner ----------
    var slug = $('slug');
    if (slug) slug.addEventListener('input', function () {
        var p = slug.selectionStart;
        slug.value = slug.value.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-{2,}/g, '-');
        try { slug.setSelectionRange(p, p); } catch (e) {}
        refresh();
    });

    // ---------- Live preview + completeness ----------
    function eduDone() {
        return Array.prototype.some.call(document.querySelectorAll('#educationRows input[name$="[degree]"]'), function (i) { return i.value.trim(); });
    }
    function refresh() {
        counters();
        var t = tagline.value.trim(), tg = $('pvTag');
        tg.textContent = t || 'আপনার হেডলাইন এখানে দেখাবে'; tg.classList.toggle('ph', !t);

        var ex = expertise.get();
        $('pvChips').innerHTML = ex.slice(0, 6).map(function (x) { return '<span></span>'; }).join('');
        $('pvChips').querySelectorAll('span').forEach(function (s, i) { s.textContent = ex[i]; });
        if (ex.length > 6) { var more = document.createElement('span'); more.textContent = '+' + (ex.length - 6); $('pvChips').appendChild(more); }

        var fee = $('consultation_fee').value;
        $('pvFee').textContent = fee ? fee + ' BDT (consultation)' : 'ফি উল্লেখ নেই';
        var lg = languages.get();
        $('pvLang').textContent = lg.length ? lg.join(', ') : '—';

        var sc = $('show_contact').checked;
        $('pvContact').innerHTML = sc ? '<i class="fa-solid fa-phone"></i> <span>যোগাযোগ নম্বর দৃশ্যমান</span>' : '<i class="fa-solid fa-lock"></i> <span>যোগাযোগ নম্বর লুকানো</span>';

        var pub = $('is_published').checked;
        $('pvStatus').innerHTML = '<i class="fa-solid fa-circle" style="color:' + (pub ? '#34d399' : '#fbbf24') + '"></i> ' + (pub ? 'Live preview' : 'Hidden');

        if (slug) $('pvLink').textContent = SLUG_BASE + '/' + (slug.value || 'your-name');

        var checks = [
            ['প্রোফাইল ছবি', HAS_PHOTO],
            ['হেডলাইন (১০+ অক্ষর)', t.length >= 10],
            ['About (৮০+ অক্ষর)', about.value.length >= 80],
            ['শিক্ষাগত যোগ্যতা', eduDone()],
            ['কমপক্ষে ১টি Expertise', ex.length >= 1],
            ['ভাষা', lg.length >= 1],
            ['কনসালটেশন ফি', fee !== ''],
        ];
        if (!EDITING) checks.unshift(['প্রোফাইল লিংক', !!(slug && slug.value.length >= 3)]);
        var done = checks.filter(function (c) { return c[1]; }).length;
        var pct = Math.round(done / checks.length * 100);
        $('pgPct').textContent = pct + '%';
        $('pgBar').style.width = pct + '%';
        $('pgList').innerHTML = checks.map(function (c) { return '<li class="' + (c[1] ? 'ok' : '') + '"><i class="fa-solid fa-check"></i> ' + c[0] + '</li>'; }).join('');
    }
    document.getElementById('ppForm').addEventListener('input', refresh);
    document.getElementById('ppForm').addEventListener('change', refresh);
    refresh();

    // ---------- Copy public link ----------
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