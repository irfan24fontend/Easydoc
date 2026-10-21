@extends('layouts.doctor')

@section('title', 'Notifications')

@push('styles')
@include('doctor.wallet._styles')
<style>
    .nt-item { display:grid; grid-template-columns:44px 1fr auto; gap:.9rem; align-items:start; padding:.95rem 1rem; border:1px solid var(--wl-line); border-radius:14px; background:#fff; text-decoration:none; color:inherit; width:100%; text-align:left; font:inherit; cursor:pointer; }
    .nt-item + .nt-item { margin-top:.6rem; }
    .nt-item.unread { background:#f5f3ff; border-color:#ddd6fe; }
    .nt-item:focus-visible { outline:3px solid #c7d2fe; outline-offset:2px; }
    .nt-item:hover { border-color:#c7d2fe; color:inherit; }
    .nt-ico { width:44px; height:44px; border-radius:12px; display:grid; place-items:center; font-size:1.1rem; background:#e0e7ff; color:#4338ca; }
    .nt-ico.feature { background:#ede9fe; color:#6d28d9; }
    .nt-ico.news { background:#e0f2fe; color:#0369a1; }
    .nt-ico.recharge { background:#d1fae5; color:#047857; }
    .nt-ico.notice { background:#fef3c7; color:#b45309; }
    .nt-title { font-weight:800; color:var(--wl-ink); }
    .nt-body { font-size:.88rem; color:#475569; margin-top:.15rem; overflow-wrap:anywhere; }
    .nt-meta { font-size:.75rem; color:var(--wl-muted); margin-top:.35rem; }
    .nt-dot { width:9px; height:9px; border-radius:50%; background:#4f46e5; display:inline-block; margin-right:.35rem; }
</style>
@endpush

@section('content')
<div class="wl-hero">
    <h1><i class="fa-solid fa-bell me-2"></i>Notifications</h1>
    <p>Messages from the Super Admin and updates about your wallet. New ones also pop up on your screen with a sound.</p>
</div>

<div class="wl-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0"><i class="fa-solid fa-inbox"></i> Inbox
            @if($unread > 0)<span class="wl-badge pending">{{ $unread }} unread</span>@endif
        </h2>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="soundToggle"><i class="fa-solid fa-volume-high me-1"></i> <span>Sound: On</span></button>
            @if($unread > 0)
                <form method="POST" action="{{ route('doctor.notifications.read-all') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-check-double me-1"></i> Mark all as read</button>
                </form>
            @endif
        </div>
    </div>

    @forelse($notifications as $n)
        @php
            $__nt = [
                'id'    => $n->id,
                'type'  => $n->type,
                'label' => $n->typeLabel(),
                'title' => $n->title,
                'body'  => $n->body,
                'time'  => $n->created_at->format('d M Y, h:i A'),
                'link'  => $n->link_url,
            ];
        @endphp
        <button type="button" class="nt-item {{ $n->isRead() ? '' : 'unread' }}" data-nt="{{ json_encode($__nt, JSON_UNESCAPED_UNICODE) }}" data-id="{{ $n->id }}">
            <span class="nt-ico {{ $n->type }}">
                <i class="fa-solid {{ match($n->type) { 'feature' => 'fa-wand-magic-sparkles', 'news' => 'fa-newspaper', 'recharge' => 'fa-wallet', default => 'fa-bullhorn' } }}"></i>
            </span>
            <span>
                <span class="nt-title d-block">@unless($n->isRead())<span class="nt-dot"></span>@endunless{{ $n->title }}</span>
                <span class="nt-body d-block">{{ \Illuminate\Support\Str::limit($n->body, 220) }}</span>
                <span class="nt-meta d-block">{{ $n->typeLabel() }} &middot; {{ $n->created_at->diffForHumans() }}</span>
            </span>
            <i class="fa-solid fa-chevron-right text-muted small mt-2"></i>
        </button>
    @empty
        <div class="wl-empty"><i class="fa-regular fa-bell"></i>No notification yet.</div>
    @endforelse

    <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Click a notification: the full message opens in a window (and it is marked as read)
    document.querySelectorAll('.nt-item').forEach(function (item) {
        item.addEventListener('click', function () {
            var n; try { n = JSON.parse(item.getAttribute('data-nt')); } catch (e) { return; }
            if (window.edNotify) window.edNotify.open(n);
        });
    });
    document.addEventListener('ednotify:read', function (e) {
        var item = document.querySelector('.nt-item[data-id="' + e.detail.id + '"]');
        if (!item) return;
        item.classList.remove('unread');
        var dot = item.querySelector('.nt-dot'); if (dot) dot.remove();
    });
})();
(function () {
    var KEY = 'easydoc.notifySound', btn = document.getElementById('soundToggle');
    if (!btn) return;
    function on() { try { return localStorage.getItem(KEY) !== 'off'; } catch (e) { return true; } }
    function paint() {
        btn.querySelector('span').textContent = 'Sound: ' + (on() ? 'On' : 'Off');
        btn.querySelector('i').className = 'fa-solid ' + (on() ? 'fa-volume-high' : 'fa-volume-xmark') + ' me-1';
    }
    btn.addEventListener('click', function () { try { localStorage.setItem(KEY, on() ? 'off' : 'on'); } catch (e) {} paint(); });
    paint();
})();
</script>
@endpush
