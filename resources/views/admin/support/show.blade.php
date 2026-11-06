@extends('layouts.admin')

@section('title', 'Support chat')

@section('content')
@php
    $doctorName = $conversation->doctor?->displayName() ?? 'Doctor';
    $lastId = (int) ($messages->last()?->id ?? 0);
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
        <a href="{{ route('admin.support.index') }}" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> All support chats</a>
        <h1 class="h3 fw-bold mt-2 mb-1">{{ $doctorName }}</h1>
        <div class="text-muted">{{ $conversation->doctor?->user?->email }} · {{ $conversation->subject }}</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill {{ $conversation->status === 'open' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($conversation->status) }}</span>
        @if($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.support.close', $conversation) }}" class="m-0">
                @csrf @method('PATCH')
                <button class="btn btn-outline-secondary" type="submit">Close chat</button>
            </form>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" id="adminSupportChat"
     data-messages-url="{{ route('admin.support.messages', $conversation) }}"
     data-reply-url="{{ route('admin.support.reply', $conversation) }}"
     data-last-id="{{ $lastId }}">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="fw-bold"><i class="fa-solid fa-headset text-success me-2"></i>EasyDoc Support</div>
        <div class="small text-muted" id="adminChatLiveStatus">Live updates on</div>
    </div>
    <div class="p-3 p-md-4 bg-light" id="adminChatMessages" style="height:min(58vh,560px);min-height:320px;overflow-y:auto" aria-live="polite">
        @foreach($messages as $message)
            <div class="d-flex mb-3 {{ $message->sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
                <div style="max-width:min(78%,640px)">
                    <div class="small text-muted mb-1 {{ $message->sender_type === 'admin' ? 'text-end' : '' }}">{{ $message->sender_type === 'admin' ? 'You · ' : ($message->sender?->name ?? $doctorName) . ' · ' }}{{ $message->created_at->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M, h:i A') }}</div>
                    <div class="rounded-4 px-3 py-2 {{ $message->sender_type === 'admin' ? 'bg-success text-white' : 'bg-white border' }}" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $message->body }}</div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer bg-white p-3 p-md-4">
        @if($conversation->status === 'open')
            <form id="adminChatReplyForm" class="d-flex gap-2 align-items-end">
                @csrf
                <textarea id="adminChatReply" class="form-control" rows="2" maxlength="2000" placeholder="Write a reply…" required></textarea>
                <button class="btn btn-success px-4" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Send</button>
            </form>
            <div class="small text-danger mt-2" id="adminChatError" hidden></div>
        @else
            <div class="text-muted small">This conversation is closed. The doctor can start a new support chat at any time.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('adminSupportChat');
    if (!root) return;
    const box = document.getElementById('adminChatMessages');
    const form = document.getElementById('adminChatReplyForm');
    const input = document.getElementById('adminChatReply');
    const error = document.getElementById('adminChatError');
    const token = @json(csrf_token());
    let lastId = Number(root.dataset.lastId || 0);
    let busy = false;

    function appendMessage(message) {
        if (!message || Number(message.id) <= lastId) return;
        const row = document.createElement('div');
        row.className = 'd-flex mb-3 ' + (message.sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start');
        row.dataset.messageId = message.id;
        const wrap = document.createElement('div');
        wrap.style.maxWidth = 'min(78%,640px)';
        const meta = document.createElement('div');
        meta.className = 'small text-muted mb-1 ' + (message.sender_type === 'admin' ? 'text-end' : '');
        meta.textContent = (message.sender_type === 'admin' ? 'You' : message.sender_name) + ' · ' + message.time;
        const body = document.createElement('div');
        body.className = 'rounded-4 px-3 py-2 ' + (message.sender_type === 'admin' ? 'bg-success text-white' : 'bg-white border');
        body.style.cssText = 'white-space:pre-wrap;overflow-wrap:anywhere';
        body.textContent = message.body;
        wrap.append(meta, body);
        row.append(wrap);
        box.append(row);
        lastId = Number(message.id);
        root.dataset.lastId = String(lastId);
    }

    async function poll() {
        if (busy) return;
        busy = true;
        try {
            const url = new URL(root.dataset.messagesUrl, window.location.origin);
            url.searchParams.set('after_id', String(lastId));
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Unable to refresh messages.');
            const data = await response.json();
            (data.messages || []).forEach(appendMessage);
            if ((data.messages || []).length) box.scrollTop = box.scrollHeight;
            if (data.status === 'closed') document.getElementById('adminChatLiveStatus').textContent = 'Conversation closed';
        } catch (e) {
            document.getElementById('adminChatLiveStatus').textContent = 'Reconnecting…';
        } finally { busy = false; }
    }

    if (form) form.addEventListener('submit', async function (event) {
        event.preventDefault();
        error.hidden = true;
        const body = input.value.trim();
        if (!body) return;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(root.dataset.replyUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ body })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Reply could not be sent.');
            appendMessage(data.message);
            input.value = '';
            box.scrollTop = box.scrollHeight;
        } catch (e) {
            error.textContent = e.message;
            error.hidden = false;
        } finally { button.disabled = false; }
    });

    box.scrollTop = box.scrollHeight;
    window.setInterval(poll, 4500);
})();
</script>
@endpush
