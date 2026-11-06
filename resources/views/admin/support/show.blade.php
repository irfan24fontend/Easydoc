@extends('layouts.admin')

@section('title', 'Support chat')

@section('content')
@php
    $doctorName = $conversation->doctor?->displayName() ?? 'Doctor';
    $doctorPhoto = $conversation->doctor?->photoUrl();
    $lastId = (int) ($messages->last()?->id ?? 0);
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div class="min-w-0">
        <a href="{{ route('admin.support.index') }}" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> All support chats</a>
        <h1 class="h3 fw-bold mt-2 mb-1">{{ $doctorName }}</h1>
        <div class="text-muted text-break">{{ $conversation->doctor?->user?->email }} · {{ $conversation->subject }}</div>
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
     data-typing-url="{{ route('admin.support.typing', $conversation) }}"
     data-doctor-avatar="{{ $doctorPhoto }}"
     data-doctor-name="{{ $doctorName }}"
     data-last-id="{{ $lastId }}">
    <div class="card-header bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="admin-chat-brand"><i class="fa-solid fa-headset"></i></span>
            <div class="fw-bold">EasyDoc Support <small class="d-block fw-normal text-muted" id="adminChatLiveStatus">Live updates on</small></div>
        </div>
        <span class="small text-success" id="adminChatTyping" hidden>{{ $doctorName }} is typing…</span>
    </div>
    <div class="admin-chat-thread p-3 p-md-4 bg-light" id="adminChatMessages" aria-live="polite">
        @foreach($messages as $message)
            @php $isAdmin = $message->sender_type === 'admin'; @endphp
            <div class="admin-chat-row {{ $isAdmin ? 'is-admin' : 'is-doctor' }}" data-message-id="{{ $message->id }}">
                @if(! $isAdmin)
                    @if($doctorPhoto)
                        <img class="admin-chat-avatar" src="{{ $doctorPhoto }}" alt="">
                    @else
                        <span class="admin-chat-avatar doctor-fallback"><i class="fa-solid fa-user-doctor"></i></span>
                    @endif
                @endif
                <div class="admin-chat-message">
                    <div class="admin-chat-meta">{{ $isAdmin ? 'EasyDoc Support' : $doctorName }} · {{ $message->created_at->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M, h:i A') }}</div>
                    <div class="admin-chat-bubble {{ $isAdmin ? 'admin-bubble' : 'doctor-bubble' }}">
                        @if($message->body !== '')<div class="message-text">{{ $message->body }}</div>@endif
                        @if($message->image_path)
                            <a href="{{ route('admin.support.image', $message) }}" target="_blank" rel="noopener">
                                <img class="admin-chat-image" src="{{ route('admin.support.image', $message) }}" alt="{{ $message->image_name ?: 'Image attachment' }}" loading="lazy">
                            </a>
                        @endif
                    </div>
                </div>
                @if($isAdmin)<span class="admin-chat-avatar support-fallback"><i class="fa-solid fa-headset"></i></span>@endif
            </div>
        @endforeach
        <div class="admin-chat-typing-bubble" id="adminChatTypingBubble" hidden><span></span><span></span><span></span></div>
    </div>
    <div class="card-footer bg-white p-2 p-md-3">
        @if($conversation->status === 'open')
            <form id="adminChatReplyForm" class="admin-chat-compose">
                @csrf
                <label class="admin-chat-attach" for="adminChatImage" aria-label="Attach image" title="Attach image"><i class="fa-regular fa-image"></i></label>
                <input id="adminChatImage" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="visually-hidden">
                <div class="admin-chat-input-wrap">
                    <div class="admin-chat-image-preview" id="adminChatImagePreview" hidden>
                        <img alt="Selected image preview">
                        <button type="button" id="adminChatImageRemove" aria-label="Remove image"><i class="fa-solid fa-xmark"></i></button>
                        <span></span>
                    </div>
                    <textarea id="adminChatReply" class="form-control" rows="1" maxlength="2000" placeholder="Write a reply…" aria-label="Write a reply"></textarea>
                </div>
                <button class="btn btn-success admin-chat-send" type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            <div class="small text-danger mt-2 px-2" id="adminChatError" hidden></div>
        @else
            <div class="text-muted small p-2">This conversation is closed. The doctor can start a new support chat at any time.</div>
        @endif
    </div>
</div>
<style>
    .admin-chat-brand,.admin-chat-avatar { width:36px;height:36px;flex:0 0 36px;border-radius:50%;display:grid;place-items:center;object-fit:cover; }
    .admin-chat-brand,.support-fallback { background:#e0f2ef;color:#08766d; }
    .doctor-fallback { background:#dbeafe;color:#1d4ed8; }
    .admin-chat-thread { height:min(58vh,560px);min-height:280px;overflow-y:auto;overscroll-behavior:contain; }
    .admin-chat-row { display:flex;align-items:flex-end;gap:8px;margin:0 0 14px; }
    .admin-chat-row.is-admin { justify-content:flex-end; }
    .admin-chat-message { max-width:min(78%,640px);min-width:0; }
    .admin-chat-meta { margin:0 4px 4px;color:#718096;font-size:11px;overflow-wrap:anywhere; }
    .is-admin .admin-chat-meta { text-align:right; }
    .admin-chat-bubble { padding:9px 12px;border-radius:16px;line-height:1.45;overflow-wrap:anywhere; }
    .doctor-bubble { border:1px solid #e2e8f0;border-bottom-left-radius:5px;background:#fff;color:#17343a; }
    .admin-bubble { border-bottom-right-radius:5px;background:#0c8c84;color:#fff; }
    .admin-chat-image { display:block;max-width:min(100%,360px);max-height:280px;border-radius:10px;object-fit:contain;margin-top:5px; }
    .message-text { white-space:pre-wrap; }
    .admin-chat-compose { display:flex;align-items:flex-end;gap:8px; }
    .admin-chat-attach,.admin-chat-send { width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:12px; }
    .admin-chat-attach { cursor:pointer;border:1px solid #d7e0e0;color:#18776f;background:#fff;margin:0; }
    .admin-chat-input-wrap { flex:1;min-width:0; }
    .admin-chat-input-wrap textarea { min-height:42px;max-height:120px;resize:none;border-radius:13px;padding:10px 12px; }
    .admin-chat-image-preview { position:relative;display:flex;align-items:center;gap:8px;padding:5px 32px 5px 6px;margin-bottom:6px;border:1px solid #dce7e7;border-radius:12px;color:#50666a;font-size:12px; }
    .admin-chat-image-preview[hidden],.admin-chat-typing-bubble[hidden] { display:none!important; }
    .admin-chat-image-preview img { width:44px;height:44px;object-fit:cover;border-radius:8px; }
    .admin-chat-image-preview button { position:absolute;right:5px;top:5px;width:25px;height:25px;border:0;border-radius:50%;background:#edf2f2;color:#334; }
    .admin-chat-typing-bubble { width:54px;padding:11px 12px;border:1px solid #e2e8f0;border-radius:15px;background:#fff; }
    .admin-chat-typing-bubble span { display:inline-block;width:6px;height:6px;margin:0 2px;border-radius:50%;background:#718096;animation:adminTyping 1s infinite ease-in-out; }
    .admin-chat-typing-bubble span:nth-child(2) { animation-delay:.15s; }.admin-chat-typing-bubble span:nth-child(3) { animation-delay:.3s; }
    @keyframes adminTyping { 0%,60%,100% { opacity:.35;transform:translateY(0); } 30% { opacity:1;transform:translateY(-3px); } }
    @media(max-width:575px) { .admin-chat-thread { height:min(62dvh,560px);min-height:240px;padding:12px!important; }.admin-chat-message { max-width:calc(100% - 44px); }.admin-chat-avatar { width:30px;height:30px;flex-basis:30px; }.admin-chat-image { max-width:100%; }.admin-chat-compose { gap:6px; }.admin-chat-attach,.admin-chat-send { width:40px;height:40px;flex-basis:40px; } }
</style>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('adminSupportChat');
    if (!root) return;
    const box = document.getElementById('adminChatMessages');
    const form = document.getElementById('adminChatReplyForm');
    const input = document.getElementById('adminChatReply');
    const fileInput = document.getElementById('adminChatImage');
    const preview = document.getElementById('adminChatImagePreview');
    const error = document.getElementById('adminChatError');
    const typing = document.getElementById('adminChatTyping');
    const typingBubble = document.getElementById('adminChatTypingBubble');
    const token = @json(csrf_token());
    let lastId = Number(root.dataset.lastId || 0);
    let busy = false;
    let typingTimer = null;
    let previewUrl = null;

    function makeAvatar(isAdmin) {
        if (isAdmin) {
            const avatar = document.createElement('span');
            avatar.className = 'admin-chat-avatar support-fallback';
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-headset';
            avatar.append(icon);
            return avatar;
        }
        const photo = root.dataset.doctorAvatar;
        if (photo) {
            const avatar = document.createElement('img');
            avatar.className = 'admin-chat-avatar';
            avatar.src = photo;
            avatar.alt = '';
            return avatar;
        }
        const avatar = document.createElement('span');
        avatar.className = 'admin-chat-avatar doctor-fallback';
        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-user-doctor';
        avatar.append(icon);
        return avatar;
    }

    function appendMessage(message) {
        if (!message || Number(message.id) <= lastId) return;
        const isAdmin = message.sender_type === 'admin';
        const row = document.createElement('div');
        row.className = 'admin-chat-row ' + (isAdmin ? 'is-admin' : 'is-doctor');
        row.dataset.messageId = message.id;
        if (!isAdmin) row.append(makeAvatar(false));
        const wrap = document.createElement('div');
        wrap.className = 'admin-chat-message';
        const meta = document.createElement('div');
        meta.className = 'admin-chat-meta';
        meta.textContent = (isAdmin ? 'EasyDoc Support' : (message.sender_name || root.dataset.doctorName)) + ' · ' + message.time;
        const bubble = document.createElement('div');
        bubble.className = 'admin-chat-bubble ' + (isAdmin ? 'admin-bubble' : 'doctor-bubble');
        if (message.body) {
            const body = document.createElement('div');
            body.className = 'message-text';
            body.textContent = message.body;
            bubble.append(body);
        }
        if (message.image_url) {
            const link = document.createElement('a');
            link.href = message.image_url;
            link.target = '_blank';
            link.rel = 'noopener';
            const image = document.createElement('img');
            image.className = 'admin-chat-image';
            image.src = message.image_url;
            image.alt = message.image_name || 'Image attachment';
            image.loading = 'lazy';
            link.append(image);
            bubble.append(link);
        }
        wrap.append(meta, bubble);
        row.append(wrap);
        if (isAdmin) row.append(makeAvatar(true));
        typingBubble.parentNode.insertBefore(row, typingBubble);
        lastId = Number(message.id);
        root.dataset.lastId = String(lastId);
    }

    function showTyping(value) {
        typing.hidden = !value;
        typingBubble.hidden = !value;
    }

    async function pingTyping() {
        if (!form) return;
        try {
            await fetch(root.dataset.typingUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            });
        } catch (e) {}
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
            showTyping(Boolean(data.doctor_typing));
            if (data.status === 'closed') document.getElementById('adminChatLiveStatus').textContent = 'Conversation closed';
        } catch (e) {
            document.getElementById('adminChatLiveStatus').textContent = 'Reconnecting…';
        } finally { busy = false; }
    }

    if (form) {
        input.addEventListener('input', function () {
            if (!input.value.trim()) return;
            window.clearTimeout(typingTimer);
            typingTimer = window.setTimeout(pingTyping, 350);
        });
        input.addEventListener('blur', function () { window.clearTimeout(typingTimer); });
        fileInput.addEventListener('change', function () {
            const file = fileInput.files[0];
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            if (!file) { preview.hidden = true; return; }
            previewUrl = URL.createObjectURL(file);
            preview.querySelector('img').src = previewUrl;
            preview.querySelector('span').textContent = file.name;
            preview.hidden = false;
        });
        document.getElementById('adminChatImageRemove').addEventListener('click', function () {
            fileInput.value = '';
            preview.hidden = true;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        });
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            error.hidden = true;
            if (!input.value.trim() && !fileInput.files.length) {
                error.textContent = 'Write a message or attach an image.';
                error.hidden = false;
                return;
            }
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            const payload = new FormData();
            payload.append('body', input.value.trim());
            if (fileInput.files[0]) payload.append('image', fileInput.files[0]);
            try {
                const response = await fetch(root.dataset.replyUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: payload
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Message could not be sent.');
                appendMessage(data.message);
                input.value = '';
                fileInput.value = '';
                preview.hidden = true;
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = null;
                box.scrollTop = box.scrollHeight;
            } catch (e) {
                error.textContent = e.message;
                error.hidden = false;
            } finally { button.disabled = false; }
        });
    }

    box.scrollTop = box.scrollHeight;
    window.setInterval(poll, 3000);
})();
</script>
@endpush
