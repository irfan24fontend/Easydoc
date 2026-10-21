@extends('layouts.doctor')

@section('title', 'News')

@push('styles')
@include('doctor.wallet._styles')
<style>
    .nw-card { background:#fff; border:1px solid var(--wl-line); border-radius:18px; padding:1.2rem 1.35rem; }
    .nw-card + .nw-card { margin-top:.9rem; }
    .nw-type { display:inline-flex; align-items:center; gap:.35rem; font-size:.7rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; padding:.25rem .65rem; border-radius:999px; }
    .nw-type.feature { background:#ede9fe; color:#6d28d9; }
    .nw-type.news { background:#e0f2fe; color:#0369a1; }
    .nw-type.notice { background:#fef3c7; color:#b45309; }
    .nw-title { font-size:1.1rem; font-weight:800; color:var(--wl-ink); margin:.55rem 0 .35rem; }
    .nw-body { color:#334155; white-space:pre-line; overflow-wrap:anywhere; }
    .nw-meta { font-size:.78rem; color:var(--wl-muted); margin-top:.7rem; }
</style>
@endpush

@section('content')
<div class="wl-hero">
    <h1><i class="fa-solid fa-newspaper me-2"></i>News</h1>
    <p>New features, updates and notices from the {{ config('easydoc.company.name', 'EasyDoc') }} team.</p>
</div>

@forelse($items as $item)
    <article class="nw-card">
        <span class="nw-type {{ $item->type }}">
            <i class="fa-solid {{ match($item->type) { 'feature' => 'fa-wand-magic-sparkles', 'news' => 'fa-newspaper', default => 'fa-bullhorn' } }}"></i>
            {{ $item->typeLabel() }}
        </span>
        <h2 class="nw-title">{{ $item->title }}</h2>
        <div class="nw-body">{{ $item->body }}</div>
        @if($item->link_url)
            <a href="{{ $item->link_url }}" class="btn btn-sm btn-outline-primary mt-3" @if(str_starts_with($item->link_url, 'http')) target="_blank" rel="noopener" @endif>
                Learn more <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        @endif
        <div class="nw-meta">{{ $item->created_at->format('d M Y, h:i A') }}</div>
    </article>
@empty
    <div class="wl-card"><div class="wl-empty"><i class="fa-regular fa-newspaper"></i>No news yet.</div></div>
@endforelse

<div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection
