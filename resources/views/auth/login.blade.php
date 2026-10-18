<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EasyDoc</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }
        .brand-gradient {
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.12) 0%, transparent 45%),
                        linear-gradient(160deg, #4338ca 0%, #4f46e5 45%, #6366f1 100%);
        }
        .fade-up { animation: fadeUp .5s ease both; }
        @keyframes fadeUp { from { opacity:0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
    </style>
</head>
<body class="bg-slate-50 antialiased">

    <div class="min-h-screen flex">

        <!-- LEFT: BRAND PANEL -->
        <div class="hidden lg:flex lg:w-1/2 brand-gradient text-white relative overflow-hidden flex-col justify-between p-12">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-32 -left-16 w-80 h-80 bg-white/5 rounded-full"></div>

            <a href="{{ url('/') }}" class="flex items-center space-x-3 relative z-10">
                <div class="bg-white text-indigo-600 w-11 h-11 rounded-xl flex items-center justify-center font-extrabold text-lg shadow-lg">ED</div>
                <div>
                    <h1 class="font-extrabold text-lg leading-tight">EasyDoc</h1>
                    <span class="text-[10px] text-indigo-200 font-semibold tracking-wide">DOCTOR'S PRESCRIPTION PLATFORM</span>
                </div>
            </a>

            <div class="relative z-10 max-w-md">
                <span class="inline-flex items-center gap-2 bg-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-full mb-6 backdrop-blur">
                    <i class="fa-solid fa-user-doctor"></i> ডাক্তারদের জন্য তৈরি
                </span>
                <h2 class="text-3xl font-extrabold leading-snug mb-4">
                    আপনার প্র্যাকটিসকে করুন আরও স্মার্ট ও গোছানো
                </h2>
                <p class="text-indigo-100 text-sm leading-relaxed mb-8">
                    AI-সহায়ক ডায়াগনোসিস, নিজস্ব ব্র্যান্ডেড প্রেসক্রিপশন টেমপ্লেট আর নিরাপদ ক্লাউড স্টোরেজ — সব একসাথে, এক প্ল্যাটফর্মে।
                </p>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                        <div>
                            <p class="font-bold text-sm">নিরাপদ ও এনক্রিপ্টেড</p>
                            <p class="text-indigo-200 text-xs">আপনার রোগীর তথ্য পুরোপুরি সুরক্ষিত ক্লাউডে সংরক্ষিত।</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-wand-magic-sparkles text-sm"></i></div>
                        <div>
                            <p class="font-bold text-sm">AI ডায়াগনোসিস সহায়তা</p>
                            <p class="text-indigo-200 text-xs">দ্রুত সিদ্ধান্ত নিতে সহায়ক স্মার্ট সাজেশন।</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-mobile-screen text-sm"></i></div>
                        <div>
                            <p class="font-bold text-sm">যেকোনো ডিভাইস থেকে অ্যাক্সেস</p>
                            <p class="text-indigo-200 text-xs">চেম্বার, বাসা বা পথে — যেকোনো জায়গা থেকে কাজ করুন।</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-indigo-200 text-xs">&copy; {{ date('Y') }} EasyDoc. All rights reserved.</p>
        </div>

        <!-- RIGHT: LOGIN FORM -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md fade-up">

                <!-- Mobile logo (shown only when left panel hidden) -->
                <div class="flex lg:hidden items-center justify-center space-x-3 mb-8">
                    <div class="bg-indigo-600 text-white w-11 h-11 rounded-xl flex items-center justify-center font-extrabold text-lg shadow-md">ED</div>
                    <div>
                        <h1 class="font-extrabold text-lg text-slate-900 leading-tight">EasyDoc</h1>
                        <span class="text-[10px] text-indigo-500 font-semibold tracking-wide">DOCTOR'S PRESCRIPTION PLATFORM</span>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2">স্বাগতম, ডাক্তার 👋</h2>
                    <p class="text-slate-500 text-sm">আপনার অ্যাকাউন্টে লগইন করে চালিয়ে যান</p>
                </div>

                <!-- Session status (e.g. password reset link sent) -->
                @if (session('status'))
                    <div class="mb-5 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium px-4 py-3 rounded-xl">
                        <i class="fa-solid fa-circle-check mt-0.5"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- General / validation errors -->
                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-3 rounded-xl">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-1.5">ইমেইল</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   placeholder="doctor@example.com"
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-bold text-slate-700">পাসওয়ার্ড</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                    পাসওয়ার্ড ভুলে গেছেন?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i id="toggle-icon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-600 font-medium">আমাকে মনে রাখুন</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Login করুন
                    </button>
                </form>

                @if (Route::has('register'))
                    <p class="text-center text-sm text-slate-500 mt-8">
                        নতুন এখানে? <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">অ্যাকাউন্ট তৈরি করুন</a>
                    </p>
                @endif

                <a href="{{ url('/') }}" class="flex items-center justify-center gap-2 text-slate-400 hover:text-slate-600 text-xs font-medium mt-6">
                    <i class="fa-solid fa-arrow-left"></i> হোমপেজে ফিরে যান
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggle-icon');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
        }
    </script>
</body>
</html>