@extends('layouts.doctor')

@section('title', 'Manage Public Profile')

@section('content')
@php
    $live = $profile->isLive();
    $shareText = urlencode(($doctor->displayName() ?? 'Doctor') . ' — ' . $profile->url());
@endphp

<div class="pp">
    <div class="pp-hero">
        <div class="pp-hero-ico"><i class="fa-solid fa-globe"></i></div>
        <div class="flex-grow-1">
            <h1>Manage public profile</h1>
            <p>রোগীরা আপনার শেয়ারযোগ্য পেজে যা দেখে তা এখান থেকে হালনাগাদ করুন।</p>
            <div class="pp-steps">
                @if ($live)
                    <span class="on"><i class="fa-solid fa-circle me-1" style="color:#34d399;font-size:9px"></i> Live · সবার জন্য উন্মুক্ত</span>
                @else
                    <span><i class="fa-solid fa-eye-slash me-1"></i> Hidden · শুধু আপনি প্রিভিউ দেখতে পারবেন</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Status + link + share --}}
    <div class="pp-status">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <div class="pp-status-title"><i class="fa-solid fa-link"></i> Your public link</div>
                @if ($live)
                    <span class="pp-badge ok">Live &middot; visible to everyone</span>
                @else
                    <span class="pp-badge off">Hidden &middot; only you can preview it</span>
                @endif
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ $profile->url() }}" target="_blank" rel="noopener" class="pp-btn ghost sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{ $live ? 'View page' : 'Preview' }}</a>
                <form method="POST" action="{{ route('doctor.public-profile.toggle') }}" class="m-0">
                    @csrf @method('PATCH')
                    <button type="submit" class="pp-btn sm {{ $profile->is_published ? 'ghost' : 'go' }}">
                        <i class="fa-solid {{ $profile->is_published ? 'fa-eye-slash' : 'fa-rocket' }}"></i>
                        {{ $profile->is_published ? 'Hide profile' : 'Publish profile' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="pp-linkbox">
            <code>{{ $profile->url() }}</code>
            <button type="button" class="pp-btn sm" data-copy="{{ $profile->url() }}"><i class="fa-regular fa-copy"></i> Copy</button>
        </div>

        <div class="pp-share">
            <span>শেয়ার করুন:</span>
            <a href="https://wa.me/?text={{ $shareText }}" target="_blank" rel="noopener" class="wa"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($profile->url()) }}" target="_blank" rel="noopener" class="fb"><i class="fa-brands fa-facebook-f"></i> Facebook</a>
            <a href="mailto:?subject={{ rawurlencode($doctor->displayName() ?? 'Doctor') }}&body={{ rawurlencode($profile->url()) }}" class="ml"><i class="fa-regular fa-envelope"></i> Email</a>
        </div>
    </div>

    @include('doctor.public-profile._form', ['doctor' => $doctor, 'profile' => $profile])
</div>
@endsection