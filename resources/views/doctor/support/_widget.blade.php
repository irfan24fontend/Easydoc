<div id="doctorSupportWidget"
     data-state-url="{{ route('doctor.support-chat.state') }}"
     data-send-url="{{ route('doctor.support-chat.send') }}"
     data-typing-url="{{ route('doctor.support-chat.typing') }}"
     data-doctor-avatar="{{ auth()->user()?->doctor?->photoUrl() }}">
    <section class="ed-support-panel" id="doctorSupportPanel" aria-label="EasyDoc support chat" hidden>
        <header class="ed-support-head">
            <span class="ed-support-head-icon"><i class="fa-solid fa-headset"></i></span>
            <div class="ed-support-head-copy">
                <strong>EasyDoc Support</strong>
                <small id="doctorSupportSubtitle" role="status">We’re here to help</small>
            </div>
            <button class="ed-support-close" id="doctorSupportClose" type="button" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="ed-support-thread" id="doctorSupportMessages" role="log" aria-live="polite">
            <div class="ed-support-empty">Send a message about any EasyDoc issue. Our admin team will reply here.</div>
        </div>
        <form class="ed-support-compose" id="doctorSupportForm">
            <label class="ed-support-attach" for="doctorSupportImage" aria-label="Attach image" title="Attach image"><i class="fa-regular fa-image"></i></label>
            <input id="doctorSupportImage" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="visually-hidden">
            <div class="ed-support-input-wrap">
                <div class="ed-support-image-preview" id="doctorSupportImagePreview" hidden>
                    <img alt="Selected image preview">
                    <span></span>
                    <button type="button" id="doctorSupportImageRemove" aria-label="Remove image"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <textarea id="doctorSupportInput" rows="1" maxlength="2000" placeholder="Write your message…" aria-label="Your message"></textarea>
            </div>
            <button class="ed-support-send" type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="ed-support-error" id="doctorSupportError" role="alert" hidden></div>
    </section>
    <button class="ed-support-launcher" id="doctorSupportLauncher" type="button" aria-expanded="false" aria-controls="doctorSupportPanel" aria-label="Open support chat" title="Support chat">
        <i class="fa-solid fa-comments"></i><b id="doctorSupportBadge" hidden></b>
    </button>
</div>

<style>
    #doctorSupportWidget { position:fixed;z-index:1019;right:max(14px,env(safe-area-inset-right));bottom:max(14px,env(safe-area-inset-bottom));font-family:Inter,system-ui,sans-serif; }
    .ed-support-launcher { position:relative;width:58px;height:58px;display:grid;place-items:center;border:0;border-radius:50%;padding:0;color:#fff;background:#0a786f;box-shadow:0 8px 24px rgba(10,107,101,.3);font-size:22px; }
    .ed-support-launcher:hover,.ed-support-launcher:focus-visible { background:#075e58; }
    .ed-support-launcher:focus-visible,.ed-support-close:focus-visible,.ed-support-send:focus-visible,.ed-support-attach:focus-visible { outline:3px solid rgba(12,140,132,.28);outline-offset:2px; }
    .ed-support-launcher b { position:absolute;top:-3px;right:-3px;min-width:20px;height:20px;display:grid;place-items:center;padding:0 5px;border:2px solid #fff;border-radius:999px;background:#ef4444;color:#fff;font-size:10px; }
    .ed-support-panel { position:absolute;right:0;bottom:72px;width:min(380px,calc(100vw - 24px));height:min(540px,calc(100dvh - 112px));min-height:min(320px,calc(100dvh - 112px));display:flex;flex-direction:column;overflow:hidden;border:1px solid #dbe5e5;border-radius:18px;background:#fff;box-shadow:0 14px 48px rgba(15,23,42,.2); }
    .ed-support-panel[hidden] { display:none; }
    .ed-support-head { display:flex;align-items:center;gap:10px;flex:0 0 auto;padding:12px 14px;color:#fff;background:#105b57; }
    .ed-support-head-icon { width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:50%;background:rgba(255,255,255,.16); }
    .ed-support-head-copy { min-width:0;display:flex;flex-direction:column;flex:1; }
    .ed-support-head-copy strong { font-size:14px;line-height:1.25; }
    .ed-support-head-copy small { min-height:15px;color:#c8e4e1;font-size:11px;line-height:1.35; }
    .ed-support-close { width:34px;height:34px;flex:0 0 34px;border:0;border-radius:50%;color:#e8fffb;background:transparent; }
    .ed-support-close:hover { background:rgba(255,255,255,.14); }
    .ed-support-thread { min-height:0;flex:1;overflow-y:auto;overscroll-behavior:contain;padding:12px;background:#f4f7f7; }
    .ed-support-empty { margin:8px 2px;padding:12px;border:1px solid #dcebea;border-radius:13px;background:#fff;color:#587078;font-size:13px;line-height:1.5; }
    .ed-support-msg { display:flex;align-items:flex-end;gap:7px;margin:0 0 11px; }
    .ed-support-msg.doctor { justify-content:flex-end; }
    .ed-support-avatar { width:28px;height:28px;flex:0 0 28px;display:grid;place-items:center;object-fit:cover;border-radius:50%;background:#dcefeb;color:#08766d;font-size:12px; }
    .ed-support-avatar.admin { background:#dcefeb;color:#08766d; }
    .ed-support-avatar.doctor { background:#dbeafe;color:#1d4ed8; }
    .ed-support-msg-inner { min-width:0;max-width:calc(100% - 36px); }
    .ed-support-msg-meta { margin:0 3px 3px;color:#71848a;font-size:10px;overflow-wrap:anywhere; }
    .ed-support-msg.doctor .ed-support-msg-meta { text-align:right; }
    .ed-support-msg-body { max-width:100%;padding:8px 10px;border:1px solid #e1e9e9;border-radius:14px 14px 14px 4px;background:#fff;color:#17343a;font-size:13px;line-height:1.45;white-space:pre-wrap;overflow-wrap:anywhere; }
    .ed-support-msg.doctor .ed-support-msg-body { border-color:#0c8c84;border-radius:14px 14px 4px 14px;background:#0c8c84;color:#fff; }
    .ed-support-msg-image { display:block;width:auto;max-width:100%;max-height:240px;margin-top:4px;border-radius:9px;object-fit:contain; }
    .ed-support-typing { display:flex;align-items:center;gap:8px;margin:0 0 10px 35px;color:#65787d;font-size:11px; }
    .ed-support-dots { display:flex;gap:3px;padding:9px 10px;border:1px solid #e1e9e9;border-radius:12px;background:#fff; }
    .ed-support-dots i { width:5px;height:5px;border-radius:50%;background:#73858a;animation:edTyping 1s infinite ease-in-out; }
    .ed-support-dots i:nth-child(2) { animation-delay:.15s; }.ed-support-dots i:nth-child(3) { animation-delay:.3s; }
    @keyframes edTyping { 0%,60%,100% { opacity:.35;transform:translateY(0); } 30% { opacity:1;transform:translateY(-3px); } }
    .ed-support-compose { display:flex;align-items:flex-end;gap:7px;flex:0 0 auto;padding:9px;border-top:1px solid #e6eded;background:#fff; }
    .ed-support-attach,.ed-support-send { width:40px;height:40px;flex:0 0 40px;display:grid;place-items:center;border-radius:12px; }
    .ed-support-attach { margin:0;border:1px solid #d6e1e1;background:#fff;color:#0c7c73;cursor:pointer; }
    .ed-support-send { border:0;background:#0c8c84;color:#fff; }
    .ed-support-send:disabled { opacity:.55; }
    .ed-support-input-wrap { min-width:0;flex:1; }
    .ed-support-input-wrap textarea { display:block;width:100%;min-height:40px;max-height:105px;resize:none;padding:9px 10px;border:1px solid #d6e1e1;border-radius:12px;outline:none;color:#17343a;font:inherit;font-size:13px;line-height:1.4; }
    .ed-support-input-wrap textarea:focus { border-color:#0c8c84;box-shadow:0 0 0 2px rgba(12,140,132,.11); }
    .ed-support-image-preview { position:relative;display:flex;align-items:center;gap:7px;padding:4px 30px 4px 4px;margin-bottom:5px;border:1px solid #dce7e7;border-radius:10px;color:#50666a;font-size:11px;overflow-wrap:anywhere; }
    .ed-support-image-preview[hidden] { display:none!important; }
    .ed-support-image-preview img { width:40px;height:40px;flex:0 0 40px;object-fit:cover;border-radius:7px; }
    .ed-support-image-preview button { position:absolute;right:4px;top:4px;width:23px;height:23px;border:0;border-radius:50%;background:#edf2f2;color:#334; }
    .ed-support-error { padding:0 12px 8px;color:#b91c1c;font-size:12px;overflow-wrap:anywhere; }
    @media(max-width:480px) {
        #doctorSupportWidget { right:max(12px,env(safe-area-inset-right));bottom:max(12px,env(safe-area-inset-bottom)); }
        .ed-support-launcher { width:54px;height:54px; }
        .ed-support-panel { position:fixed;right:max(12px,env(safe-area-inset-right));bottom:calc(76px + env(safe-area-inset-bottom));width:calc(100vw - 24px);height:min(540px,calc(100dvh - 96px - env(safe-area-inset-bottom)));min-height:min(280px,calc(100dvh - 96px - env(safe-area-inset-bottom))); }
    }
</style>

<script>
(function () {
    const root = document.getElementById('doctorSupportWidget');
    if (!root) return;
    const panel = document.getElementById('doctorSupportPanel');
    const launcher = document.getElementById('doctorSupportLauncher');
    const close = document.getElementById('doctorSupportClose');
    const log = document.getElementById('doctorSupportMessages');
    const form = document.getElementById('doctorSupportForm');
    const input = document.getElementById('doctorSupportInput');
    const fileInput = document.getElementById('doctorSupportImage');
    const imagePreview = document.getElementById('doctorSupportImagePreview');
    const badge = document.getElementById('doctorSupportBadge');
    const subtitle = document.getElementById('doctorSupportSubtitle');
    const error = document.getElementById('doctorSupportError');
    const token = @json(csrf_token());
    let opened = false;
    let conversationId = null;
    let lastId = 0;
    let polling = false;
    let sending = false;
    let typingTimer = null;
    let previewUrl = null;

    function clearLog() {
        log.replaceChildren();
        lastId = 0;
    }

    function avatar(type) {
        const photo = type === 'doctor' ? root.dataset.doctorAvatar : '';
        if (photo) {
            const image = document.createElement('img');
            image.className = 'ed-support-avatar doctor';
            image.src = photo;
            image.alt = '';
            return image;
        }
        const icon = document.createElement('span');
        icon.className = 'ed-support-avatar ' + (type === 'doctor' ? 'doctor' : 'admin');
        const glyph = document.createElement('i');
        glyph.className = type === 'doctor' ? 'fa-solid fa-user-doctor' : 'fa-solid fa-headset';
        icon.append(glyph);
        return icon;
    }

    function appendMessage(message) {
        if (!message || Number(message.id) <= lastId) return;
        const isDoctor = message.sender_type === 'doctor';
        const row = document.createElement('div');
        row.className = 'ed-support-msg ' + (isDoctor ? 'doctor' : 'admin');
        row.dataset.messageId = message.id;
        if (!isDoctor) row.append(avatar('admin'));
        const inner = document.createElement('div');
        inner.className = 'ed-support-msg-inner';
        const meta = document.createElement('div');
        meta.className = 'ed-support-msg-meta';
        meta.textContent = (isDoctor ? 'You' : 'EasyDoc Support') + ' · ' + message.time;
        const body = document.createElement('div');
        body.className = 'ed-support-msg-body';
        if (message.body) body.textContent = message.body;
        if (message.image_url) {
            const link = document.createElement('a');
            link.href = message.image_url;
            link.target = '_blank';
            link.rel = 'noopener';
            const image = document.createElement('img');
            image.className = 'ed-support-msg-image';
            image.src = message.image_url;
            image.alt = message.image_name || 'Image attachment';
            image.loading = 'lazy';
            link.append(image);
            body.append(link);
        }
        inner.append(meta, body);
        row.append(inner);
        if (isDoctor) row.append(avatar('doctor'));
        log.append(row);
        lastId = Number(message.id);
    }

    function showTyping(value) {
        let indicator = document.getElementById('doctorSupportTyping');
        if (value && !indicator) {
            indicator = document.createElement('div');
            indicator.id = 'doctorSupportTyping';
            indicator.className = 'ed-support-typing';
            const dots = document.createElement('span');
            dots.className = 'ed-support-dots';
            for (let i = 0; i < 3; i++) dots.append(document.createElement('i'));
            const label = document.createElement('span');
            label.textContent = 'EasyDoc Support is typing…';
            indicator.append(dots, label);
            log.append(indicator);
        } else if (!value && indicator) {
            indicator.remove();
        }
    }

    function setUnread(count) {
        const n = Number(count || 0);
        badge.hidden = n < 1 || opened;
        badge.textContent = n > 9 ? '9+' : String(n);
    }

    async function pingTyping() {
        try {
            const body = new FormData();
            if (conversationId) body.append('conversation_id', String(conversationId));
            await fetch(root.dataset.typingUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body
            });
        } catch (e) {}
    }

    async function refresh() {
        if (polling) return;
        polling = true;
        try {
            const url = new URL(root.dataset.stateUrl, window.location.origin);
            if (!opened) url.searchParams.set('peek', '1');
            else if (lastId > 0) url.searchParams.set('after_id', String(lastId));
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Support chat is temporarily unavailable.');
            const data = await response.json();
            const nextId = data.conversation ? Number(data.conversation.id) : null;
            if (nextId !== conversationId) {
                conversationId = nextId;
                clearLog();
            }
            if (opened) {
                (data.messages || []).forEach(appendMessage);
                let empty = log.querySelector('.ed-support-empty');
                if ((data.messages || []).length && empty) empty.remove();
                if (!log.querySelector('.ed-support-msg') && !empty) {
                    empty = document.createElement('div');
                    empty.className = 'ed-support-empty';
                    empty.textContent = data.conversation
                        ? 'Chat with EasyDoc Support. Send a message if you need more help.'
                        : 'Send a message about any EasyDoc issue. Our admin team will reply here.';
                    log.append(empty);
                }
                showTyping(Boolean(data.admin_typing));
                if ((data.messages || []).length) log.scrollTop = log.scrollHeight;
            }
            subtitle.textContent = data.conversation?.status === 'closed'
                ? 'Previous chat closed · send a message to reopen support'
                : (data.admin_typing ? 'Support is typing…' : (data.admin_online ? 'Support is online' : 'We usually reply soon'));
            setUnread(data.unread);
        } catch (e) {
            if (opened) {
                error.textContent = e.message;
                error.hidden = false;
            }
        } finally { polling = false; }
    }

    function setOpen(value) {
        opened = value;
        panel.hidden = !value;
        launcher.setAttribute('aria-expanded', value ? 'true' : 'false');
        if (value) {
            error.hidden = true;
            clearLog();
            setUnread(0);
            refresh();
            window.setTimeout(() => input.focus(), 40);
        } else {
            refresh();
        }
    }

    launcher.addEventListener('click', () => setOpen(!opened));
    close.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && opened) setOpen(false); });

    input.addEventListener('input', function () {
        if (!input.value.trim()) return;
        window.clearTimeout(typingTimer);
        typingTimer = window.setTimeout(pingTyping, 350);
    });

    fileInput.addEventListener('change', function () {
        const file = fileInput.files[0];
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        if (!file) { imagePreview.hidden = true; return; }
        previewUrl = URL.createObjectURL(file);
        imagePreview.querySelector('img').src = previewUrl;
        imagePreview.querySelector('span').textContent = file.name;
        imagePreview.hidden = false;
    });
    document.getElementById('doctorSupportImageRemove').addEventListener('click', function () {
        fileInput.value = '';
        imagePreview.hidden = true;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        error.hidden = true;
        if ((!input.value.trim() && !fileInput.files.length) || sending) {
            if (!sending) {
                error.textContent = 'Write a message or attach an image.';
                error.hidden = false;
            }
            return;
        }
        sending = true;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        const payload = new FormData();
        payload.append('body', input.value.trim());
        if (fileInput.files[0]) payload.append('image', fileInput.files[0]);
        try {
            const response = await fetch(root.dataset.sendUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: payload
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Message could not be sent. Please try again.');
            if (Number(data.conversation.id) !== conversationId) {
                conversationId = Number(data.conversation.id);
                clearLog();
            }
            const empty = log.querySelector('.ed-support-empty');
            if (empty) empty.remove();
            appendMessage(data.message);
            input.value = '';
            fileInput.value = '';
            imagePreview.hidden = true;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            subtitle.textContent = 'Message sent';
            log.scrollTop = log.scrollHeight;
        } catch (e) {
            error.textContent = e.message;
            error.hidden = false;
        } finally {
            sending = false;
            button.disabled = false;
            input.focus();
        }
    });

    refresh();
    window.setInterval(refresh, 3000);
})();
</script>
