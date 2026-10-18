<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyDoc — ডাক্তারদের জন্য স্মার্ট প্রেসক্রিপশন প্ল্যাটফর্ম</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }
        .gradient-hero {
            background: radial-gradient(circle at top right, #eef2ff 0%, #ffffff 45%);
        }
        .fade-up { animation: fadeUp .6s ease both; }
        @keyframes fadeUp { from { opacity:0; transform: translateY(14px);} to { opacity:1; transform: translateY(0);} }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-3">
                <div class="bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-200">ED</div>
                <div>
                    <h1 class="font-extrabold text-lg text-slate-900 leading-tight">EasyDoc</h1>
                    <span class="text-[10px] text-indigo-500 font-semibold tracking-wide">DOCTOR'S PRESCRIPTION PLATFORM</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold text-slate-600">
                <a href="#features" class="hover:text-indigo-600 transition-colors">ফিচারসমূহ</a>
                <a href="#how-it-works" class="hover:text-indigo-600 transition-colors">কীভাবে কাজ করে</a>
                <a href="#faq" class="hover:text-indigo-600 transition-colors">সাধারণ জিজ্ঞাসা</a>
                <a href="#contact" class="hover:text-indigo-600 transition-colors">যোগাযোগ</a>
            </nav>

            <div class="flex items-center space-x-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-200 transition-all">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-200 transition-all">
                        <i class="fa-solid fa-right-to-bracket"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="gradient-hero">
        <div class="max-w-7xl mx-auto px-6 pt-16 pb-20 md:pt-24 md:pb-28 grid md:grid-cols-2 gap-12 items-center">
            <div class="fade-up">
                <span class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 text-xs font-bold px-3 py-1.5 rounded-full mb-5">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> AI-Powered Prescription Assistant
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-5">
                    ডিজিটাল প্রেসক্রিপশন এখন <span class="text-indigo-600">দ্রুত ও ঝামেলাহীন</span>
                </h1>
                <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                    EasyDoc দিয়ে রোগীর তথ্য, ডায়াগনোসিস ও ওষুধ সাজেশন — সব একই জায়গায়। AI দিয়ে দ্রুত ডায়াগনোসিস ও মেডিসিন সাজেস্ট করুন, নিজের ব্র্যান্ডেড প্রেসক্রিপশন প্রিন্ট বা PDF করুন — মাত্র কয়েক ক্লিকে।
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-7 py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all">
                        <i class="fa-solid fa-right-to-bracket"></i> Login করুন
                    </a>
                    <a href="#features" class="inline-flex items-center gap-2 text-slate-700 font-bold px-7 py-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition-all">
                        ফিচার দেখুন <i class="fa-solid fa-arrow-down"></i>
                    </a>
                </div>

                <div class="flex items-center gap-6 mt-10 text-sm text-slate-500">
                    <div class="flex items-center gap-2"><i class="fa-solid fa-shield-halved text-indigo-500"></i> Secure Cloud</div>
                    <div class="flex items-center gap-2"><i class="fa-solid fa-bolt text-indigo-500"></i> Fast & Simple</div>
                    <div class="flex items-center gap-2"><i class="fa-solid fa-mobile-screen text-indigo-500"></i> Any Device</div>
                </div>
            </div>

            <div class="fade-up" style="animation-delay:.1s">
                <div class="relative bg-white rounded-2xl shadow-2xl shadow-indigo-100 border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600"><i class="fa-solid fa-user-doctor"></i></div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">ডাঃ সাইদুর রহমান</p>
                                <p class="text-[11px] text-slate-400">Cardiology Specialist</p>
                            </div>
                        </div>
                        <span class="text-[10px] bg-emerald-50 text-emerald-600 font-bold px-2.5 py-1 rounded-full">Active</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3">
                            <span class="text-xs font-semibold text-slate-500">Patient</span>
                            <span class="text-xs font-bold text-slate-800">Abdullah Al Irfan</span>
                        </div>
                        <div class="flex items-center justify-between bg-indigo-50 rounded-xl px-4 py-3">
                            <span class="text-xs font-semibold text-indigo-500 flex items-center gap-1.5"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Diagnosis</span>
                            <span class="text-xs font-bold text-indigo-700">Acute Cholecystitis</span>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-xl px-4 py-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">℞ Medicines</p>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="font-semibold text-slate-700">Paracetamol 500mg</span><span class="text-slate-400">1+1+1</span></div>
                                <div class="flex justify-between"><span class="font-semibold text-slate-700">Omeprazole 20mg</span><span class="text-slate-400">1+0+0</span></div>
                            </div>
                        </div>
                    </div>

                    <button class="w-full mt-4 bg-slate-900 text-white text-xs font-bold py-3 rounded-xl flex items-center justify-center gap-2">
                        <i class="fa-solid fa-print"></i> Print / Save as PDF
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- TRUST STRIP -->
    <section class="border-y border-slate-100 bg-slate-50/60">
        <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <i class="fa-solid fa-brain text-indigo-500 text-xl mb-2"></i>
                <p class="text-sm font-bold text-slate-700">AI ডায়াগনোসিস সহায়তা</p>
            </div>
            <div>
                <i class="fa-solid fa-file-shield text-indigo-500 text-xl mb-2"></i>
                <p class="text-sm font-bold text-slate-700">নিরাপদ ক্লাউড স্টোরেজ</p>
            </div>
            <div>
                <i class="fa-solid fa-pen-ruler text-indigo-500 text-xl mb-2"></i>
                <p class="text-sm font-bold text-slate-700">নিজস্ব প্রেসক্রিপশন টেমপ্লেট</p>
            </div>
            <div>
                <i class="fa-solid fa-wallet text-indigo-500 text-xl mb-2"></i>
                <p class="text-sm font-bold text-slate-700">সহজ ওয়ালেট বিলিং</p>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="max-w-7xl mx-auto px-6 py-20 md:py-28">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-indigo-600 font-bold text-xs uppercase tracking-widest">Features</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-3 mb-4">যা যা পাচ্ছেন EasyDoc-এ</h2>
            <p class="text-slate-500">রোগী দেখা থেকে শুরু করে প্রেসক্রিপশন প্রিন্ট পর্যন্ত — পুরো প্রসেসটা সহজ করে দিয়েছে EasyDoc।</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">AI ডায়াগনোসিস ও ওষুধ সাজেশন</h3>
                <p class="text-sm text-slate-500 leading-relaxed">রোগীর সমস্যা লিখলেই AI সম্ভাব্য ডায়াগনোসিস ও উপযুক্ত ওষুধ সাজেস্ট করবে — সময় বাঁচবে, decision নেওয়া সহজ হবে।</p>
            </div>
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-file-pen"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">কাস্টম প্রেসক্রিপশন টেমপ্লেট</h3>
                <p class="text-sm text-slate-500 leading-relaxed">নিজের চেম্বার, লোগো আর ডিজাইন অনুযায়ী প্রেসক্রিপশন টেমপ্লেট সেট করুন — প্রতিটা প্রিন্ট দেখাবে আপনার নিজের ব্র্যান্ডে।</p>
            </div>
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-user-injured"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">রোগীর তথ্য দ্রুত লুকআপ</h3>
                <p class="text-sm text-slate-500 leading-relaxed">মোবাইল নাম্বার দিয়ে পুরনো রোগীর তথ্য মুহূর্তেই খুঁজে বের করুন — বারবার একই তথ্য টাইপ করতে হবে না।</p>
            </div>
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-print"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">Print ও PDF সাপোর্ট</h3>
                <p class="text-sm text-slate-500 leading-relaxed">এক ক্লিকে প্রেসক্রিপশন প্রিন্ট করুন অথবা PDF আকারে সেভ করে রোগীকে হোয়াটসঅ্যাপ/ইমেইলে পাঠিয়ে দিন।</p>
            </div>
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-wallet"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">ওয়ালেট ভিত্তিক বিলিং</h3>
                <p class="text-sm text-slate-500 leading-relaxed">সহজ ওয়ালেট রিচার্জ সিস্টেম — প্রতিটা প্রেসক্রিপশনের হিসাব স্বচ্ছভাবে ট্র্যাক করুন, কোনো ঝামেলা ছাড়াই।</p>
            </div>
            <div class="p-7 rounded-2xl border border-slate-100 hover:shadow-xl hover:shadow-slate-100 transition-all">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-5"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">যেকোনো জায়গা থেকে অ্যাক্সেস</h3>
                <p class="text-sm text-slate-500 leading-relaxed">সব ডেটা ক্লাউডে নিরাপদে সংরক্ষিত — চেম্বার, বাসা বা যেকোনো জায়গা থেকে যেকোনো ডিভাইসে লগইন করে কাজ করুন।</p>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how-it-works" class="bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-6 py-20 md:py-28">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-indigo-400 font-bold text-xs uppercase tracking-widest">How it works</span>
                <h2 class="text-3xl md:text-4xl font-extrabold mt-3 mb-4">মাত্র ৩ ধাপে প্রেসক্রিপশন তৈরি</h2>
                <p class="text-slate-400">জটিল কিছু নেই — লগইন করুন, রোগীর তথ্য দিন, প্রিন্ট করুন।</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="relative bg-slate-800/60 border border-slate-700 rounded-2xl p-7">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-extrabold mb-5">১</div>
                    <h3 class="font-bold text-lg mb-2">লগইন করুন</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">আপনার ডাক্তার অ্যাকাউন্ট দিয়ে EasyDoc-এ লগইন করুন এবং নিজের ড্যাশবোর্ডে চলে যান।</p>
                </div>
                <div class="relative bg-slate-800/60 border border-slate-700 rounded-2xl p-7">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-extrabold mb-5">২</div>
                    <h3 class="font-bold text-lg mb-2">রোগীর তথ্য ও AI সহায়তা</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">রোগীর সমস্যা লিখুন, AI থেকে ডায়াগনোসিস ও ওষুধ সাজেশন নিন, প্রয়োজন মতো এডিট করুন।</p>
                </div>
                <div class="relative bg-slate-800/60 border border-slate-700 rounded-2xl p-7">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-extrabold mb-5">৩</div>
                    <h3 class="font-bold text-lg mb-2">প্রিন্ট বা PDF করুন</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">নিজের ব্র্যান্ডেড টেমপ্লেটে প্রেসক্রিপশন প্রিন্ট করুন অথবা PDF আকারে সেভ করুন।</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="max-w-4xl mx-auto px-6 py-20 md:py-28">
        <div class="text-center mb-14">
            <span class="text-indigo-600 font-bold text-xs uppercase tracking-widest">FAQ</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-3">সাধারণ জিজ্ঞাসা</h2>
        </div>

        <div class="space-y-4">
            <details class="group bg-slate-50 rounded-xl p-5 open:shadow-md transition-all">
                <summary class="flex items-center justify-between cursor-pointer font-bold text-slate-800">
                    EasyDoc ব্যবহার করতে কি কোনো টেকনিক্যাল জ্ঞান লাগবে?
                    <i class="fa-solid fa-chevron-down text-indigo-500 text-sm group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-sm text-slate-500 mt-3 leading-relaxed">না, EasyDoc সম্পূর্ণ সহজভাবে ডিজাইন করা — যেকোনো ডাক্তার সহজেই ব্যবহার করতে পারবেন, কোনো টেকনিক্যাল অভিজ্ঞতা ছাড়াই।</p>
            </details>
            <details class="group bg-slate-50 rounded-xl p-5 open:shadow-md transition-all">
                <summary class="flex items-center justify-between cursor-pointer font-bold text-slate-800">
                    AI সাজেশন কি সবসময় সঠিক ডায়াগনোসিস দেবে?
                    <i class="fa-solid fa-chevron-down text-indigo-500 text-sm group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-sm text-slate-500 mt-3 leading-relaxed">AI শুধুমাত্র একটি সহায়ক টুল হিসেবে সম্ভাব্য ডায়াগনোসিস ও ওষুধ সাজেস্ট করে — চূড়ান্ত সিদ্ধান্ত সবসময় ডাক্তারের পেশাদার বিচার-বিবেচনার উপরেই নির্ভর করে।</p>
            </details>
            <details class="group bg-slate-50 rounded-xl p-5 open:shadow-md transition-all">
                <summary class="flex items-center justify-between cursor-pointer font-bold text-slate-800">
                    আমার নিজের প্রেসক্রিপশন টেমপ্লেট কি ব্যবহার করা যাবে?
                    <i class="fa-solid fa-chevron-down text-indigo-500 text-sm group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-sm text-slate-500 mt-3 leading-relaxed">হ্যাঁ, নিজের চেম্বার/হাসপাতালের নাম, লোগো ও ডিজাইন দিয়ে কাস্টম টেমপ্লেট সেট করা যায়, যা প্রতিটা প্রিন্টে ব্যবহার হবে।</p>
            </details>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="max-w-7xl mx-auto px-6 pb-20 md:pb-28">
        <div class="bg-indigo-600 rounded-3xl px-8 py-14 md:py-16 text-center relative overflow-hidden">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">আজই EasyDoc-এ লগইন করুন</h2>
            <p class="text-indigo-100 mb-8 max-w-xl mx-auto">প্রেসক্রিপশন লেখা এখন আরও দ্রুত, পরিষ্কার এবং ঝামেলাহীন — শুরু করুন এখনই।</p>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white text-indigo-600 font-bold px-8 py-3.5 rounded-xl shadow-lg hover:bg-indigo-50 transition-all">
                <i class="fa-solid fa-right-to-bracket"></i> Login করুন
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="contact" class="border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-indigo-600 text-white w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm">ED</div>
                    <h3 class="font-extrabold text-slate-900">EasyDoc</h3>
                </div>
                <p class="text-sm text-slate-500 leading-relaxed">ডাক্তারদের জন্য AI-সহায়ক ডিজিটাল প্রেসক্রিপশন প্ল্যাটফর্ম — সহজ, দ্রুত এবং নিরাপদ।</p>
            </div>
            <div>
                <h4 class="font-bold text-slate-800 mb-4 text-sm uppercase tracking-wide">লিংক</h4>
                <ul class="space-y-2 text-sm text-slate-500">
                    <li><a href="#features" class="hover:text-indigo-600">ফিচারসমূহ</a></li>
                    <li><a href="#how-it-works" class="hover:text-indigo-600">কীভাবে কাজ করে</a></li>
                    <li><a href="#faq" class="hover:text-indigo-600">সাধারণ জিজ্ঞাসা</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-indigo-600">Login</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-slate-800 mb-4 text-sm uppercase tracking-wide">যোগাযোগ</h4>
                <ul class="space-y-2 text-sm text-slate-500">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-envelope text-indigo-500"></i> support@easydoc.app</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-phone text-indigo-500"></i> +8801XXXXXXXXX</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-indigo-500"></i> Dhaka, Bangladesh</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100 py-5 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} EasyDoc. All rights reserved.
        </div>
    </footer>

</body>
</html>