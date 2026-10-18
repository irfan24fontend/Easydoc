@php
    $authUser = auth()->user();
    $assistantDoctor = $authUser->assistantProfile?->doctor;
    $assistantChamber = $assistantDoctor?->activeChamber();
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | EasyDoc</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .as-topbar {
            background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;
            padding: 0.8rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
        }
        .as-brand { display: flex; align-items: center; gap: 10px; }
        .as-brand-icon {
            width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg,#4f46e5,#4338ca);
            display: flex; align-items: center; justify-content: center;
        }
        .as-brand h6 { margin: 0; font-weight: 800; }
        .as-brand small { color: #a5b4fc; font-weight: 600; }
        .as-chip { background: rgba(255,255,255,.1); border-radius: 999px; padding: .35rem .85rem; font-size: .78rem; font-weight: 600; }
        .as-logout { background: rgba(220,38,38,.15); color: #fca5a5; border: 1px solid rgba(220,38,38,.3); border-radius: 8px; font-size: .78rem; font-weight: 600; padding: .35rem .8rem; }
        .as-logout:hover { background: #dc2626; color: #fff; }
        .cardx { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    </style>
    @stack('styles')
</head>
<body>

<div class="as-topbar">
    <div class="as-brand">
        <div class="as-brand-icon"><i class="fa-solid fa-stethoscope"></i></div>
        <div>
            <h6>EasyDoc</h6>
            <small>ASSISTANT PANEL</small>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if($assistantDoctor)
            <span class="as-chip"><i class="fa-solid fa-user-doctor me-1"></i> Dr. {{ $assistantDoctor->user->name ?? '' }}</span>
        @endif
        @if($assistantChamber)
            <span class="as-chip"><i class="fa-solid fa-hospital me-1"></i> {{ $assistantChamber->chamber_name }}</span>
        @endif
        <span class="as-chip"><i class="fa-solid fa-user me-1"></i> {{ $authUser->name }}</span>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="as-logout"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout</button>
        </form>
    </div>
</div>

<div class="container-fluid p-3 p-lg-4">
    @include('layouts.flash')

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>