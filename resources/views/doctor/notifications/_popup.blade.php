{{--
    Notification pop-up, full-message window and sound for the doctor panel.
    Included once at the bottom of layouts/doctor.blade.php.

    - Every time a doctor logs in, every notification not yet READ pops up (each for 10 seconds).
    - "OK" only closes the pop-up: the notification stays unread and shows again at the next login.
    - "Read" (or clicking a notification in the list) opens the full message and marks it as read.
    - New broadcasts arrive while the doctor works: the page checks the server every 30 seconds.
    - A short two-tone chime plays with each pop-up (browsers allow sound after the first click/tap on a page).

    Other scripts can open a notification with:  window.edNotify.open({id, type, label, title, body, time, link})
--}}
@php $__popupDoctor = auth()->user()?->doctor; @endphp

@if ($__popupDoctor && $__popupDoctor->isApproved())
<div id="edPopStack" aria-live="assertive"></div>

{{-- Full message window --}}
<div id="edNotifyModal" class="ed-modal" role="dialog" aria-modal="true" aria-labelledby="edNmTitle" hidden>
    <div class="ed-modal-card">
        <div class="ed-modal-top">
            <span class="ed-modal-label" id="edNmLabel"></span>
            <button type="button" class="ed-modal-x" id="edNmX" aria-label="Close">&times;</button>
        </div>
        <h2 class="ed-modal-title" id="edNmTitle"></h2>
        <div class="ed-modal-time" id="edNmTime"></div>
        <div class="ed-modal-body" id="edNmBody"></div>
        <div class="ed-modal-actions">
            <a href="#" class="ed-btn primary" id="edNmLink" hidden>Open</a>
            <button type="button" class="ed-btn" id="edNmClose">Close</button>
        </div>
    </div>
</div>

<style>
    #edPopStack { position:fixed; top:76px; right:20px; z-index:2100; display:flex; flex-direction:column; gap:12px; width:min(380px, calc(100vw - 24px)); pointer-events:none; }
    .ed-pop { pointer-events:auto; background:#fff; border:1px solid #e5e9f0; border-left:5px solid #4f46e5; border-radius:16px; box-shadow:0 18px 44px rgba(15,23,42,.24); padding:14px 16px 0; overflow:hidden; animation:edPopIn .28s ease-out both; font-family:'Inter',system-ui,sans-serif; }
    .ed-pop.out { animation:edPopOut .25s ease-in both; }
    .ed-pop[data-type="feature"] { border-left-color:#7c3aed; }
    .ed-pop[data-type="news"] { border-left-color:#0ea5e9; }
    .ed-pop[data-type="recharge"] { border-left-color:#10b981; }
    .ed-pop[data-type="notice"] { border-left-color:#f59e0b; }
    .ed-pop-top { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px; }
    .ed-pop-label { font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:#4f46e5; }
    .ed-pop-secs { font-size:11px; color:#94a3b8; font-weight:600; }
    .ed-pop-title { font-size:15px; font-weight:800; color:#0f172a; line-height:1.3; }
    .ed-pop-body { font-size:13px; color:#475569; margin-top:4px; line-height:1.5; overflow-wrap:anywhere; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
    .ed-pop-actions { display:flex; gap:8px; margin:12px 0 12px; }
    .ed-pop-actions button { font:700 13px 'Inter',sans-serif; border-radius:10px; padding:8px 18px; cursor:pointer; border:1px solid transparent; }
    .ed-pop-actions .read { background:#4f46e5; color:#fff; }
    .ed-pop-actions .read:hover { background:#4338ca; }
    .ed-pop-actions .ok { background:#fff; color:#334155; border-color:#e2e8f0; }
    .ed-pop-actions .ok:hover { background:#f8fafc; }
    .ed-pop-bar { height:4px; background:#eef2ff; margin:0 -16px; }
    .ed-pop-bar i { display:block; height:100%; width:100%; background:#4f46e5; transform-origin:left; animation:edPopBar 10s linear forwards; }
    @keyframes edPopIn { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:none; } }
    @keyframes edPopOut { to { opacity:0; transform:translateX(24px); } }
    @keyframes edPopBar { to { transform:scaleX(0); } }

    .ed-modal { position:fixed; inset:0; z-index:2200; background:rgba(15,23,42,.55); display:flex; align-items:center; justify-content:center; padding:16px; font-family:'Inter',system-ui,sans-serif; }
    .ed-modal[hidden] { display:none; }
    .ed-modal-card { background:#fff; border-radius:20px; width:min(560px,100%); max-height:calc(100vh - 32px); overflow-y:auto; padding:22px 24px; box-shadow:0 30px 70px rgba(15,23,42,.4); animation:edPopIn .22s ease-out both; }
    .ed-modal-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .ed-modal-label { font-size:11px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:#4f46e5; background:#eef2ff; padding:4px 11px; border-radius:999px; }
    .ed-modal-x { border:0; background:transparent; font-size:28px; line-height:1; color:#64748b; cursor:pointer; padding:0 6px; }
    .ed-modal-title { font-size:20px; font-weight:800; color:#0f172a; margin:6px 0 4px; line-height:1.3; }
    .ed-modal-time { font-size:12.5px; color:#94a3b8; margin-bottom:14px; }
    .ed-modal-body { font-size:15px; line-height:1.7; color:#334155; white-space:pre-wrap; overflow-wrap:anywhere; }
    .ed-modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:22px; flex-wrap:wrap; }
    .ed-btn { font:700 14px 'Inter',sans-serif; border-radius:10px; padding:10px 22px; border:1px solid #e2e8f0; background:#fff; color:#334155; cursor:pointer; text-decoration:none; display:inline-block; }
    .ed-btn.primary { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .ed-btn[hidden] { display:none; }

    @media (max-width: 575px) { #edPopStack { top:64px; right:12px; left:12px; width:auto; } .ed-modal-card { padding:18px 16px; } }
    @media (prefers-reduced-motion: reduce) { .ed-pop, .ed-pop.out, .ed-modal-card { animation:none; } }
</style>

<script>
(function () {
    var POLL_URL = @json(route('doctor.notifications.poll'));
    var READ_URL = @json(route('doctor.notifications.read', ['notification' => '__ID__']));
    var CSRF = @json(csrf_token());
    var VISIBLE_MS = 10000;
    var SOUND_KEY = 'easydoc.notifySound';

    var stack = document.getElementById('edPopStack');
    var queue = [], showing = false;

    /* ---------- sound (generated in the browser, no audio file needed) ---------- */
    var audioCtx = null, soundWaiting = false;

    function soundEnabled() { try { return localStorage.getItem(SOUND_KEY) !== 'off'; } catch (e) { return true; } }
    function getCtx() {
        if (!audioCtx) {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return null;
            audioCtx = new AC();
        }
        return audioCtx;
    }
    function playTones(c) {
        var now = c.currentTime;
        [[880, 0], [1175, 0.16]].forEach(function (n) {
            var o = c.createOscillator(), g = c.createGain();
            o.type = 'sine'; o.frequency.value = n[0];
            g.gain.setValueAtTime(0.0001, now + n[1]);
            g.gain.exponentialRampToValueAtTime(0.22, now + n[1] + 0.03);
            g.gain.exponentialRampToValueAtTime(0.0001, now + n[1] + 0.42);
            o.connect(g); g.connect(c.destination);
            o.start(now + n[1]); o.stop(now + n[1] + 0.45);
        });
    }
    function chime() {
        if (!soundEnabled()) return;
        var c = getCtx(); if (!c) return;
        if (c.state === 'running') { playTones(c); return; }
        soundWaiting = true;            // browsers keep sound locked until one click/tap on the page
        try { c.resume(); } catch (e) {}
    }
    function unlock() {
        var c = getCtx(); if (!c) return;
        var go = function () { if (soundWaiting) { soundWaiting = false; playTones(c); } };
        if (c.state === 'running') go(); else c.resume().then(go).catch(function () {});
    }
    ['click', 'keydown', 'touchstart'].forEach(function (ev) { document.addEventListener(ev, unlock, { passive: true }); });

    /* ---------- mark as read ---------- */
    function markRead(id) {
        return fetch(READ_URL.replace('__ID__', id), {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () {
            document.dispatchEvent(new CustomEvent('ednotify:read', { detail: { id: id } }));
            poll();
        }).catch(function () {});
    }

    /* ---------- full message window ---------- */
    var modal = document.getElementById('edNotifyModal');
    var lastFocus = null;

    function openFull(n) {
        lastFocus = document.activeElement;
        document.getElementById('edNmLabel').textContent = n.label || 'Notification';
        document.getElementById('edNmTitle').textContent = n.title || '';
        document.getElementById('edNmTime').textContent = n.time || '';
        document.getElementById('edNmBody').textContent = n.body || '';

        var link = document.getElementById('edNmLink');
        if (n.link) { link.href = n.link; link.hidden = false; if (/^https?:/i.test(n.link)) { link.target = '_blank'; link.rel = 'noopener'; } else { link.removeAttribute('target'); } }
        else { link.hidden = true; }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        document.getElementById('edNmClose').focus();

        // Opening the full message = the doctor has read it
        markRead(n.id);
    }
    function closeFull() {
        modal.hidden = true;
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
    }
    document.getElementById('edNmClose').addEventListener('click', closeFull);
    document.getElementById('edNmX').addEventListener('click', closeFull);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeFull(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeFull(); });

    window.edNotify = { open: openFull, refresh: function () { poll(); } };

    /* ---------- pop-up cards ---------- */
    function snippet(text) { text = String(text || ''); return text.length > 160 ? text.slice(0, 157) + '...' : text; }

    function show(n) {
        showing = true;
        chime();

        var el = document.createElement('div');
        el.className = 'ed-pop'; el.setAttribute('role', 'alert'); el.setAttribute('data-type', n.type || 'notice');

        var top = document.createElement('div'); top.className = 'ed-pop-top';
        var label = document.createElement('span'); label.className = 'ed-pop-label'; label.textContent = n.label || 'Notification';
        var secs = document.createElement('span'); secs.className = 'ed-pop-secs'; secs.textContent = '10s';
        top.appendChild(label); top.appendChild(secs);

        var title = document.createElement('div'); title.className = 'ed-pop-title'; title.textContent = n.title;
        var body = document.createElement('div'); body.className = 'ed-pop-body'; body.textContent = snippet(n.body);

        var actions = document.createElement('div'); actions.className = 'ed-pop-actions';
        var read = document.createElement('button'); read.type = 'button'; read.className = 'read'; read.textContent = 'Read';
        var ok = document.createElement('button'); ok.type = 'button'; ok.className = 'ok'; ok.textContent = 'OK';
        actions.appendChild(read); actions.appendChild(ok);

        var bar = document.createElement('div'); bar.className = 'ed-pop-bar'; bar.appendChild(document.createElement('i'));

        [top, title, body, actions, bar].forEach(function (p) { el.appendChild(p); });
        stack.appendChild(el);

        var left = VISIBLE_MS / 1000;
        var ticker = setInterval(function () { left--; if (left >= 0) secs.textContent = left + 's'; }, 1000);
        var closed = false;
        var timer = setTimeout(function () { close(); }, VISIBLE_MS);

        function close() {
            if (closed) return; closed = true;
            clearTimeout(timer); clearInterval(ticker);
            el.classList.add('out');
            setTimeout(function () { el.remove(); showing = false; next(); }, 250);
        }
        ok.addEventListener('click', close);                       // only closes; stays unread
        read.addEventListener('click', function () { close(); openFull(n); });
    }

    function next() {
        if (showing || !queue.length) return;
        show(queue.shift());
    }

    /* ---------- polling ---------- */
    function setBadge(count) {
        document.querySelectorAll('.js-notif-count').forEach(function (b) {
            b.textContent = count > 99 ? '99+' : count;
            b.style.display = count > 0 ? '' : 'none';
        });
    }

    function poll() {
        fetch(POLL_URL, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                setBadge(d.unread || 0);
                (d.items || []).forEach(function (n) { queue.push(n); });
                next();
            })
            .catch(function () {});
    }

    poll();
    setInterval(function () { if (!document.hidden) poll(); }, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
</script>
@endif
