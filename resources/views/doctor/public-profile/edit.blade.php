@extends('layouts.doctor')

@section('title', 'Manage Public Profile')

@section('content')
@php $live = $profile->isLive(); @endphp

<div class="mb-4">
    <h1 class="h4 fw-bold m-0">Manage public profile</h1>
    <p class="text-muted small m-0">Update what patients see on your shareable page.</p>
</div>

<div style="max-width: 920px;">

    {{-- Status + link --}}
    <div class="pp-status">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <div class="fw-bold mb-1">Your public link</div>
                @if ($live)
                    <span class="pp-badge ok">Live &middot; visible to everyone</span>
                @else
                    <span class="pp-badge off">Hidden &middot; only you can preview it</span>
                @endif
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ $profile->url() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ $live ? 'View page' : 'Preview' }}</a>
                <form method="POST" action="{{ route('doctor.public-profile.toggle') }}" class="m-0">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $profile->is_published ? 'btn-outline-secondary' : 'btn-success' }}">
                        {{ $profile->is_published ? 'Hide profile' : 'Publish profile' }}
                    </button>
                </form>
            </div>
        </div>
        <div class="pp-linkbox">
            <code>{{ $profile->url() }}</code>
            <button type="button" class="btn btn-sm btn-dark" data-copy="{{ $profile->url() }}"><i class="fa-regular fa-copy"></i> Copy</button>
        </div>
    </div>

    @include('doctor.public-profile._form', ['doctor' => $doctor, 'profile' => $profile])
</div>
@endsection
