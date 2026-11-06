<div id="doctorSupportWidget"
     data-state-url="{{ route('doctor.support-chat.state') }}"
     data-send-url="{{ route('doctor.support-chat.send') }}">
    <section class="ed-support-panel" id="doctorSupportPanel" aria-label="EasyDoc support chat" hidden>
        <header class="ed-support-head">
            <div class="ed-support-head-icon"><i class="fa-solid fa-headset"></i></div>
            <div class="ed-support-head-copy">
                <strong>EasyDoc Support</strong>
                <small id="doctorSupportSubtitle">We’re here to help</small>
            </div>
            <button class="ed-support-close" id="doctorSupportClose" type="button" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="ed-support-thread" id="doctorSupportMessages" role="log" aria-live="polite">
            <div class="ed-support-empty">Send a message about any EasyDoc issue. Our admin team will reply here.</div>
        </div>
        <form class="ed-support-compose" id="doctorSupportForm">
            <textarea id="doctorSupportInput" rows="2" maxlength="2000" placeholder="Write your message…" aria-label="Your message" required></textarea>
            <button type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="ed-support-error" id="doctorSupportError" role="alert" hidden></div>
    </section>
    <button class="ed-support-launcher" id="doctorSupportLauncher" type="button" aria-expanded="false" aria-controls="doctorSupportPanel">
        <i class="fa-solid fa-comments"></i><span>Live support</span><b id="doctorSupportBadge" hidden></b>
    </button>
</div>

<style>
    #doctorSupportWidget { position: fixed; z-index: 1019; right: 22px; bottom: 20px; font-family: Inter,system-ui,sans-serif; }
    .ed-support-launcher { position: relative; display:flex; align-items:center; gap:9px; border:0; border-radius:999px; padding:13px 18px; color:#fff; background:linear-gradient(135deg,#0c8c84,#0a6b65); box-shadow:0 10px 28px rgba(10,107,101,.3); font-weight:750; }
    .ed-support-launcher:hover { filter:brightness(1.06); transform:translateY(-1px); }
    .ed-support-launcher b { min-width:19px; height:19px; display:grid; place-items:center; padding:0 5px; border-radius:999px; background:#ef4444; color:#fff; font-size:11px; }
    .ed-support-panel { position:absolute; right:0; bottom:64px; width:min(360px,calc(100vw - 28px)); height:min(490px,calc(100dvh - 112px)); min-height:330px; display:flex; flex-direction:column; overflow:hidden; border:1px solid #dbe5e5; border-radius:20px; background:#fff; box-shadow:0 18px 60px rgba(15,23,42,.2); }
    .ed-support-panel[hidden] { display:none; }
    .ed-support-head { display:flex; align-items:center; gap:11px; padding:14px 15px; color:#fff; background:linear-gradient(135deg,#163a3c,#0a6b65); }
    .ed-support-head-icon { width:37px; height:37px; display:grid; place-items:center; border-radius:12px; background:rgba(255,255,255,.15); }
    .ed-support-head-copy { min-width:0; display:flex; flex-direction:column; flex:1; }
    .ed-support-head-copy strong { font-size:14px; }
    .ed-support-head-copy small { color:#c4e6e2; font-size:11px; }
    .ed-support-close { width:34px; height:34px; border:0; border-radius:10px; color:#e8fffb; background:transparent; }
    .ed-support-close:hover { background:rgba(255,255,255,.13); }
    .ed-support-thread { flex:1; overflow-y:auto; padding:15px 13px; background:#f5f8f8; }
    .ed-support-empty { margin:10px 4px; padding:12px; border:1px solid #dcebea; border-radius:14px; background:#fff; color:#587078; font-size:13px; line-height:1.5; }
    .ed-support-msg { display:flex; margin:0 0 11px; }
    .ed-support-msg.doctor { justify-content:flex-end; }
    .ed-support-msg-inner { max-width:85%; }
    .ed-support-msg-meta { margin:0 3px 4px; color:#71848a; font-size:10px; }
    .ed-support-msg.doctor .ed-support-msg-meta { text-align:right; }
    .ed-support-msg-body { padding:9px 11px; border:1px solid #e1e9e9; border-radius:14px 14px 14px 4px; background:#fff; color:#17343a; font-size:13px; line-height:1.45; white-space:pre-wrap; overflow-wrap:anywhere; }
    .ed-support-msg.doctor .ed-support-msg-body { border-color:#0c8c84; border-radius:14px 14px 4px 14px; background:#0c8c84; color:#fff; }
    .ed-support-compose { display:flex; align-items:flex-end; gap:8px; padding:10px; border-top:1px solid #e6eded; background:#fff; }
    .ed-support-compose textarea { flex:1; min-height:42px; max-height:104px; resize:vertical; padding:10px 11px; border:1px solid #d6e1e1; border-radius:12px; outline:none; color:#17343a; font-size:13px; }
    .ed-support-compose textarea:focus { border-color:#0c8c84; box-shadow:0 0 0 3px rgba(12,140,132,.12); }
    .ed-support-compose button { width:42px; height:42px; flex:0 0 42px; border:0; border-radius:12px; background:#0c8c84; color:#fff; }
    .ed-support-compose button:disabled { opacity:.55; }
    .ed-support-error { padding:0 12px 10px; color:#b91c1c; font-size:12px; }
    @media(max-width:480px) { #doctorSupportWidget { right:12px; bottom:12px; } .ed-support-panel { bottom:60px; height:min(500px,calc(100dvh - 90px)); } }
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
    const badge = document.getElementById('doctorSupportBadge');
    const subtitle = document.getElementById('doctorSupportSubtitle');
    const error = document.getElementById('doctorSupportError');
    const token = @json(csrf_token());
    let opened = false;
    let conversationId = null;
    let lastId = 0;
    let polling = false;
    let sending = false;

    function clearLog() {
        log.replaceChildren();
        lastId = 0;
    }

    function appendMessage(message) {
        if (!message || Number(message.id) <= lastId) return;
        const row = document.createElement('div');
        row.className = 'ed-support-msg ' + (message.sender_type === 'doctor' ? 'doctor' : 'admin');
        row.dataset.messageId = message.id;
        const inner = document.createElement('div');
        inner.className = 'ed-support-msg-inner';
        const meta = document.createElement('div');
        meta.className = 'ed-support-msg-meta';
        meta.textContent = (message.sender_type === 'doctor' ? 'You' : 'EasyDoc Support') + ' · ' + message.time;
        const body = document.createElement('div');
        body.className = 'ed-support-msg-body';
        body.textContent = message.body;
        inner.append(meta, body);
        row.append(inner);
        log.append(row);
        lastId = Number(message.id);
    }

    function setUnread(count) {
        const n = Number(count || 0);
        badge.hidden = n < 1 || opened;
        badge.textContent = n > 9 ? '9+' : String(n);
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
                if (!(data.messages || []).length && !log.children.length) {
                    const empty = document.createElement('div');
                    empty.className = 'ed-support-empty';
                    empty.textContent = data.conversation
                        ? 'Chat with EasyDoc Support. Send a message if you need more help.'
                        : 'Send a message about any EasyDoc issue. Our admin team will reply here.';
                    log.append(empty);
                }
                if ((data.messages || []).length) log.scrollTop = log.scrollHeight;
            }
            subtitle.textContent = data.conversation?.status === 'closed'
                ? 'Previous chat closed · send a message to reopen support'
                : (data.conversation ? 'Our team will reply here' : 'We’re here to help');
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

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        error.hidden = true;
        const body = input.value.trim();
        if (!body || sending) return;
        sending = true;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(root.dataset.sendUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ body })
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
            subtitle.textContent = 'Our team will reply here';
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
    window.setInterval(refresh, 5000);
})();
</script>
