<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor registration | {{ config('app.name', 'EasyDoc') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Self-contained: does not depend on the Vite build --}}
    <style>
        :root {
            --ink: #10262b; --ink-soft: #46606a; --teal: #0c8c84; --teal-deep: #0a6b65;
            --mint: #e3f3f0; --mist: #f3f7f7; --line: #d3e0e0; --amber: #f4b63f;
            --danger: #b3261e; --danger-bg: #fdecea;
            --display: "Bricolage Grotesque", "Hind Siliguri", system-ui, sans-serif;
            --body: "Hind Siliguri", system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font: 400 16px/1.6 var(--body); color: var(--ink); background: var(--mist); -webkit-font-smoothing: antialiased; }
        a { color: var(--teal-deep); }
        :focus-visible { outline: 3px solid var(--amber); outline-offset: 2px; }

        .shell { display: grid; grid-template-columns: 380px 1fr; min-height: 100vh; }

        /* Left: why + what you need */
        .aside { background: var(--ink); color: #e6f1f2; padding: 48px 40px; position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        .brand { display: flex; align-items: center; gap: 10px; font: 800 22px var(--display); color: #fff; text-decoration: none; letter-spacing: -0.03em; }
        .brand-mark { width: 34px; height: 34px; border-radius: 10px; background: var(--teal); display: grid; place-items: center; }
        .aside h1 { font: 800 34px/1.15 var(--display); letter-spacing: -0.02em; margin: 56px 0 14px; color: #fff; }
        .aside p { color: #b9cfd3; margin: 0; }
        .steps { list-style: none; padding: 0; margin: 36px 0 0; display: grid; gap: 18px; counter-reset: s; }
        .steps li { display: grid; grid-template-columns: 34px 1fr; gap: 14px; align-items: start; counter-increment: s; }
        .steps li::before { content: counter(s); width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.1); color: var(--amber); display: grid; place-items: center; font: 700 16px var(--display); }
        .steps strong { display: block; color: #fff; font-weight: 600; }
        .steps span { font-size: 14.5px; color: #9fb8bd; }
        .aside-foot { font-size: 14px; color: #9fb8bd; }
        .aside-foot a { color: #fff; }

        /* Right: form */
        .main { padding: 48px 56px 72px; }
        .form-wrap { max-width: 760px; margin: 0 auto; }
        .top-link { display: flex; justify-content: flex-end; font-size: 15px; margin-bottom: 8px; }
        .form-wrap h2 { font: 800 28px var(--display); letter-spacing: -0.02em; margin: 0 0 6px; }
        .lead { color: var(--ink-soft); margin: 0 0 28px; }

        fieldset { border: 0; padding: 0; margin: 0 0 34px; }
        legend { font: 700 13px var(--body); text-transform: uppercase; letter-spacing: .09em; color: var(--teal-deep); padding: 0; margin-bottom: 16px; width: 100%; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 18px; }
        .full { grid-column: 1 / -1; }
        label.lbl { display: block; font-weight: 600; font-size: 15px; margin-bottom: 6px; }
        .req { color: var(--danger); }
        .hint { font-size: 13.5px; color: var(--ink-soft); margin-top: 5px; }
        input[type=text], input[type=email], input[type=password], input[type=tel], input[type=number], input[type=date], select {
            width: 100%; font: 400 16px var(--body); color: var(--ink); background: #fff;
            border: 1.5px solid var(--line); border-radius: 10px; padding: 11px 14px; min-height: 46px;
        }
        input:focus, select:focus { border-color: var(--teal); outline: none; box-shadow: 0 0 0 4px rgba(12,140,132,.14); }
        .invalid { border-color: var(--danger) !important; }
        .err { color: var(--danger); font-size: 14px; margin-top: 5px; }

        .file { border: 1.5px dashed #9db9b9; border-radius: 12px; background: #fff; padding: 14px 16px; }
        .file input { width: 100%; font: 400 14px var(--body); }
        .file.invalid { border-color: var(--danger); }

        .terms { display: flex; gap: 12px; align-items: flex-start; background: var(--mint); border-radius: 12px; padding: 16px; }
        .terms input { width: 20px; height: 20px; margin-top: 3px; accent-color: var(--teal); flex: none; }
        .notice { background: #fff8e6; border: 1px solid #f1dca0; color: #6b4e00; border-radius: 12px; padding: 14px 16px; font-size: 15px; margin-bottom: 28px; }
        .alert { background: var(--danger-bg); color: var(--danger); border-radius: 12px; padding: 14px 16px; margin-bottom: 24px; font-size: 15px; }

        .btn { display: inline-flex; align-items: center; justify-content: center; font: 600 17px var(--body); padding: 14px 30px; border-radius: 999px; border: 0; background: var(--teal); color: #fff; cursor: pointer; min-height: 50px; }
        .btn:hover { background: var(--teal-deep); }
        .actions { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-top: 24px; }

        @media (max-width: 960px) {
            .shell { grid-template-columns: 1fr; }
            .aside { position: static; height: auto; padding: 28px 24px 32px; gap: 20px; }
            .aside h1 { margin-top: 28px; font-size: 28px; }
            .steps { display: none; }
            .main { padding: 28px 20px 56px; }
        }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="shell">

    <aside class="aside">
        <div>
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                {{ config('app.name', 'EasyDoc') }}
            </a>
            <h1>Join as a registered doctor</h1>
            <p>We check every doctor against their BMDC registration before the account goes live. This protects patients and every doctor on the platform.</p>

            <ol class="steps">
                <li><div><strong>Fill in your details</strong><span>Your BMDC number, degrees and a clear photo.</span></div></li>
                <li><div><strong>We review your application</strong><span>An administrator verifies your BMDC registration.</span></div></li>
                <li><div><strong>Start using {{ config('app.name', 'EasyDoc') }}</strong><span>Once approved you can log in, write prescriptions and publish your profile.</span></div></li>
            </ol>
        </div>
        <div class="aside-foot">Already registered? <a href="{{ route('login') }}">Log in</a></div>
    </aside>

    <main class="main">
        <div class="form-wrap">
            <div class="top-link">Already have an account?&nbsp;<a href="{{ route('login') }}">Log in</a></div>
            <h2>Create your doctor account</h2>
            <p class="lead">All fields are required. Your application will be reviewed before you can log in and use the system.</p>

            @if ($errors->any())
                <div class="alert" role="alert">Please fix the {{ $errors->count() === 1 ? 'highlighted field' : 'highlighted fields' }} below and submit again. Your files need to be selected again.</div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <fieldset>
                    <legend>1. Account</legend>
                    <div class="grid">
                        <div class="full">
                            <label class="lbl" for="name">Full name <span class="req">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required class="@error('name') invalid @enderror" placeholder="As on your BMDC certificate">
                            @error('name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="email">Email <span class="req">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="@error('email') invalid @enderror">
                            @error('email') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="phone">Mobile number <span class="req">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="numeric" required class="@error('phone') invalid @enderror" placeholder="01XXXXXXXXX">
                            @error('phone') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="password">Password <span class="req">*</span></label>
                            <input type="password" id="password" name="password" autocomplete="new-password" required class="@error('password') invalid @enderror">
                            <div class="hint">At least 8 characters.</div>
                            @error('password') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="password_confirmation">Confirm password <span class="req">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>2. Professional details</legend>
                    <div class="grid">
                        <div>
                            <label class="lbl" for="bmdc_number">BMDC registration number <span class="req">*</span></label>
                            <input type="text" id="bmdc_number" name="bmdc_number" value="{{ old('bmdc_number') }}" required class="@error('bmdc_number') invalid @enderror" placeholder="e.g. A-12345">
                            <div class="hint">Exactly as printed on your BMDC certificate.</div>
                            @error('bmdc_number') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="speciality">Speciality <span class="req">*</span></label>
                            <input type="text" id="speciality" name="speciality" value="{{ old('speciality') }}" required class="@error('speciality') invalid @enderror" placeholder="e.g. Medicine Specialist">
                            @error('speciality') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div class="full">
                            <label class="lbl" for="qualification">Degrees <span class="req">*</span></label>
                            <input type="text" id="qualification" name="qualification" value="{{ old('qualification') }}" required class="@error('qualification') invalid @enderror" placeholder="e.g. MBBS (DMC), FCPS (Medicine)">
                            @error('qualification') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="designation">Current designation <span class="req">*</span></label>
                            <input type="text" id="designation" name="designation" value="{{ old('designation') }}" required class="@error('designation') invalid @enderror" placeholder="e.g. Assistant Professor">
                            @error('designation') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="medical_college">Medical college <span class="req">*</span></label>
                            <input type="text" id="medical_college" name="medical_college" value="{{ old('medical_college') }}" required class="@error('medical_college') invalid @enderror" placeholder="Where you completed MBBS">
                            @error('medical_college') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="experience">Years of experience <span class="req">*</span></label>
                            <input type="number" id="experience" name="experience" value="{{ old('experience') }}" min="0" max="60" required class="@error('experience') invalid @enderror">
                            @error('experience') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>3. Personal</legend>
                    <div class="grid">
                        <div>
                            <label class="lbl" for="gender">Gender <span class="req">*</span></label>
                            <select id="gender" name="gender" required class="@error('gender') invalid @enderror">
                                <option value="">Select</option>
                                <option value="male" @selected(old('gender') === 'male')>Male</option>
                                <option value="female" @selected(old('gender') === 'female')>Female</option>
                                <option value="other" @selected(old('gender') === 'other')>Other</option>
                            </select>
                            @error('gender') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="date_of_birth">Date of birth <span class="req">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subYears(22)->toDateString() }}" required class="@error('date_of_birth') invalid @enderror">
                            @error('date_of_birth') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>4. Documents</legend>
                    <div class="grid">
                        <div>
                            <label class="lbl" for="photo">Profile photo <span class="req">*</span></label>
                            <div class="file @error('photo') invalid @enderror"><input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" required></div>
                            <div class="hint">Clear face photo. JPG, PNG or WebP, up to 2 MB. Shown on your public profile.</div>
                            @error('photo') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="lbl" for="bmdc_certificate">BMDC certificate <span class="req">*</span></label>
                            <div class="file @error('bmdc_certificate') invalid @enderror"><input type="file" id="bmdc_certificate" name="bmdc_certificate" accept="image/png,image/jpeg,application/pdf" required></div>
                            <div class="hint">Photo or PDF, up to 4 MB. Kept private and seen only by our administrator.</div>
                            @error('bmdc_certificate') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </fieldset>

                <div class="notice">After you submit, your account stays <strong>under review</strong>. You can log in to see the status, but you can use the system only after the administrator approves it.</div>

                <label class="terms">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                    <span>I confirm that the information and documents I provided are true and belong to me.</span>
                </label>
                @error('terms') <div class="err">{{ $message }}</div> @enderror

                <div class="actions">
                    <button type="submit" class="btn">Submit for review</button>
                    <a href="{{ url('/') }}">Back to home</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
