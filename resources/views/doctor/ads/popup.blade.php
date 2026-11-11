<div id="doctorAdOverlay" class="doctor-ad-overlay" aria-hidden="true">
    <section class="doctor-ad-card" role="dialog" aria-modal="true" aria-labelledby="doctorAdTitle" aria-describedby="doctorAdHint">
        <div class="doctor-ad-topline"><span><i class="fa-solid fa-bullhorn"></i> {{ __('Sponsored medicine') }}</span><span class="doctor-ad-sponsored">{{ __('Advertisement') }}</span></div>
        <div class="doctor-ad-content">
            <div class="doctor-ad-image-wrap">
                <img id="doctorAdImage" alt="{{ __('Medicine advertisement') }}" hidden>
                <div id="doctorAdImageFallback" class="doctor-ad-image-fallback" hidden>
                    <i class="fa-regular fa-image"></i>
                    <span>{{ __('Ad image unavailable') }}</span>
                </div>
            </div>
            <div class="doctor-ad-copy">
                <h2 id="doctorAdTitle"></h2>
                <p id="doctorAdHint">{{ __('You can read the medicine information while this ad is displayed.') }}</p>
                <button type="button" id="doctorAdDetailsToggle" class="doctor-ad-details-toggle" aria-expanded="false">
                    <i class="fa-solid fa-book-medical"></i> <span id="doctorAdDetailsLabel">{{ __('Read medicine details') }}</span>
                </button>
                <div id="doctorAdDetails" class="doctor-ad-details" hidden></div>
            </div>
        </div>
        <div id="doctorAdProgress" class="doctor-ad-progress" role="progressbar" aria-label="{{ __('Ad progress') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <span id="doctorAdProgressFill"></span>
        </div>
        <div class="doctor-ad-footer">
            <span id="doctorAdWaitHint" aria-live="polite">{{ __('Advertisement is playing.') }}</span>
            <button type="button" id="doctorAdSkip" class="doctor-ad-skip" disabled hidden>
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                <span>{{ __('Skip ad') }}</span>
            </button>
        </div>
    </section>
</div>

<style>
    .doctor-ad-overlay { position:fixed; inset:0; z-index:25000; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.78); backdrop-filter:blur(5px); }
    .doctor-ad-overlay.is-visible { display:flex; }
    .doctor-ad-card { width:min(760px,100%); max-height:min(90vh,900px); overflow:auto; background:#fff; border:1px solid rgba(255,255,255,.5); border-radius:22px; box-shadow:0 28px 90px rgba(0,0,0,.34); animation:doctorAdEnter .28s ease-out; }
    .doctor-ad-topline { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:17px 22px; color:#0f766e; border-bottom:1px solid #e8f0ef; font-size:13px; font-weight:800; letter-spacing:.02em; }
    .doctor-ad-topline i { margin-right:7px; }
    .doctor-ad-sponsored { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
    .doctor-ad-content { display:grid; grid-template-columns:minmax(0,1.1fr) minmax(220px,.9fr); gap:24px; padding:24px; }
    .doctor-ad-image-wrap { min-height:240px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f1f5f9; border-radius:15px; }
    .doctor-ad-image-wrap img { display:block; width:100%; max-height:390px; object-fit:contain; }
    .doctor-ad-image-wrap img[hidden], .doctor-ad-image-fallback[hidden] { display:none !important; }
    .doctor-ad-image-fallback { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:24px; color:#64748b; font-size:13px; text-align:center; }
    .doctor-ad-image-fallback i { color:#94a3b8; font-size:32px; }
    .doctor-ad-copy { align-self:center; }
    .doctor-ad-copy h2 { margin:0 0 9px; color:#0f172a; font-size:clamp(22px,3vw,30px); font-weight:800; line-height:1.2; }
    .doctor-ad-copy p { margin:0 0 16px; color:#64748b; font-size:14px; line-height:1.6; }
    .doctor-ad-details-toggle { border:0; padding:0; background:transparent; color:#0c8c84; font-weight:800; font-size:14px; text-align:left; }
    .doctor-ad-details-toggle:hover { color:#075e59; text-decoration:underline; }
    .doctor-ad-details { margin-top:14px; padding:13px 14px; max-height:220px; overflow:auto; color:#334155; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; white-space:pre-wrap; font-size:13px; line-height:1.6; }
    .doctor-ad-progress { height:4px; overflow:hidden; background:#e2e8f0; }
    .doctor-ad-progress span { display:block; width:0; height:100%; background:#0c8c84; }
    .doctor-ad-footer { min-height:76px; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 22px; background:#f8fafc; border-top:1px solid #e8edf3; }
    .doctor-ad-footer span { color:#64748b; font-size:13px; }
    .doctor-ad-skip { min-width:132px; border:0; border-radius:10px; padding:11px 17px; background:#0c8c84; color:#fff; font-size:13px; font-weight:800; }
    .doctor-ad-skip[hidden] { display:none !important; }
    .doctor-ad-skip span { color:inherit; }
    .doctor-ad-skip:not(:disabled) { cursor:pointer; }
    .doctor-ad-skip:not(:disabled):hover { background:#0a6b65; }
    @keyframes doctorAdEnter { from { opacity:0; transform:translateY(10px) scale(.985); } to { opacity:1; transform:translateY(0) scale(1); } }
    @media (max-width:620px) {
        .doctor-ad-overlay { padding:12px; }
        .doctor-ad-card { border-radius:17px; }
        .doctor-ad-content { grid-template-columns:1fr; gap:17px; padding:17px; }
        .doctor-ad-image-wrap { min-height:170px; max-height:240px; }
        .doctor-ad-image-wrap img { max-height:240px; }
        .doctor-ad-topline { padding:14px 17px; }
        .doctor-ad-footer { padding:13px 17px; }
    }
    @media (prefers-reduced-motion:reduce) { .doctor-ad-card { animation:none; } }
</style>

<script>
(function () {
    const overlay = document.getElementById('doctorAdOverlay');
    if (!overlay) return;

    const image = document.getElementById('doctorAdImage');
    const imageFallback = document.getElementById('doctorAdImageFallback');
    const title = document.getElementById('doctorAdTitle');
    const details = document.getElementById('doctorAdDetails');
    const detailsToggle = document.getElementById('doctorAdDetailsToggle');
    const detailsLabel = document.getElementById('doctorAdDetailsLabel');
    const skip = document.getElementById('doctorAdSkip');
    const waitHint = document.getElementById('doctorAdWaitHint');
    const progress = document.getElementById('doctorAdProgress');
    const progressFill = document.getElementById('doctorAdProgressFill');
    const minimumMs = 10000;
    const intervalMsDefault = Math.max(1, Number(@json($adIntervalMinutes)) || 10) * 60000;
    const playingText = @json(__('Advertisement is playing.'));
    const skipReadyText = @json(__('Skip is available now.'));
    const readDetailsText = @json(__('Read medicine details'));
    const hideDetailsText = @json(__('Hide medicine details'));
    const key = 'easydoc.doctorAds.{{ auth()->id() }}.{{ $adCycleId }}';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let timer = null;
    let countdownTimer = null;
    let fetching = false;
    let state;

    function save() {
        try { sessionStorage.setItem(key, JSON.stringify(state)); } catch (e) {}
    }

    function load() {
        try {
            const saved = JSON.parse(sessionStorage.getItem(key) || 'null');
            if (saved && typeof saved === 'object') return saved;
        } catch (e) {}
        return null;
    }

    state = load() || { startedAt: Date.now(), nextAt: Date.now() + intervalMsDefault, lastAdId: 0, current: null, intervalMs: intervalMsDefault };
    state.intervalMs = intervalMsDefault;
    state.lastAdId = Math.max(0, Number(state.lastAdId) || 0);
    if (!state.current) {
        const scheduleAnchor = Number(state.lastClosedAt || state.startedAt) || Date.now();
        state.nextAt = scheduleAnchor + state.intervalMs;
    }
    save();

    function show(ad, shownAt) {
        title.textContent = ad.medicine_name || 'Sponsored medicine';
        image.removeAttribute('src');
        image.hidden = true;
        imageFallback.hidden = true;
        image.onload = function () { image.hidden = false; imageFallback.hidden = true; };
        image.onerror = function () { image.hidden = true; imageFallback.hidden = false; };
        if (ad.image_url) image.src = ad.image_url;
        else imageFallback.hidden = false;
        details.textContent = ad.medicine_details || 'No medicine details are available.';
        details.hidden = true;
        detailsToggle.setAttribute('aria-expanded', 'false');
        detailsLabel.textContent = readDetailsText;
        skip.disabled = true;
        skip.hidden = true;
        waitHint.textContent = playingText;
        overlay.classList.add('is-visible');
        overlay.setAttribute('aria-hidden', 'false');
        if (countdownTimer) window.clearInterval(countdownTimer);

        const elapsedMs = Math.min(minimumMs, Math.max(0, Date.now() - shownAt));
        const remainingMs = minimumMs - elapsedMs;
        progressFill.style.transition = 'none';
        progressFill.style.width = (elapsedMs / minimumMs * 100) + '%';
        progress.setAttribute('aria-valuenow', String(Math.round(elapsedMs / minimumMs * 100)));
        window.requestAnimationFrame(function () {
            progressFill.style.transition = 'width ' + remainingMs + 'ms linear';
            progressFill.style.width = '100%';
        });

        function updateCountdown() {
            const msLeft = Math.max(0, shownAt + minimumMs - Date.now());
            const progressValue = Math.min(100, Math.round((minimumMs - msLeft) / minimumMs * 100));
            progress.setAttribute('aria-valuenow', String(progressValue));
            if (msLeft > 0) {
                skip.hidden = true;
                waitHint.textContent = playingText;
            } else {
                skip.disabled = false;
                skip.hidden = false;
                waitHint.textContent = skipReadyText;
                if (countdownTimer) window.clearInterval(countdownTimer);
                countdownTimer = null;
            }
        }
        updateCountdown();
        if (Date.now() < shownAt + minimumMs) {
            countdownTimer = window.setInterval(updateCountdown, 250);
        }
    }

    function hideAndScheduleNext() {
        if (!state.current) return;
        state.current = null;
        state.lastClosedAt = Date.now();
        state.nextAt = Date.now() + state.intervalMs;
        save();
        overlay.classList.remove('is-visible');
        overlay.setAttribute('aria-hidden', 'true');
        if (countdownTimer) window.clearInterval(countdownTimer);
        countdownTimer = null;
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(check, state.intervalMs);
    }

    function restoreOrFetch() {
        if (state.current && state.current.ad) {
            show(state.current.ad, Number(state.current.shownAt) || Date.now());
            return;
        }

        if (fetching) return;
        fetching = true;
        fetch(@json($adNextUrl), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ after_id: state.lastAdId })
        }).then(function (response) {
            if (!response.ok) throw new Error('Ad request failed');
            return response.json();
        }).then(function (payload) {
            const nextInterval = Math.max(1, Number(payload.interval_minutes) || 10) * 60000;
            state.intervalMs = nextInterval;
            if (!payload.advertisement) {
                state.current = null;
                state.lastClosedAt = Date.now();
                state.nextAt = state.lastClosedAt + nextInterval;
                save();
                fetching = false;
                schedule();
                return;
            }
            state.lastAdId = Number(payload.advertisement.id) || state.lastAdId;
            state.current = { ad: payload.advertisement, shownAt: Date.now() };
            save();
            fetching = false;
            show(state.current.ad, state.current.shownAt);
        }).catch(function () {
            fetching = false;
            state.nextAt = Date.now() + Math.min(state.intervalMs, 60000);
            save();
            schedule();
        });
    }

    function schedule() {
        if (timer) window.clearTimeout(timer);
        const delay = Math.max(0, Number(state.nextAt) - Date.now());
        timer = window.setTimeout(check, delay);
    }

    function check() {
        if (document.visibilityState === 'hidden') {
            timer = window.setTimeout(check, 10000);
            return;
        }
        if (state.current && state.current.ad) {
            show(state.current.ad, Number(state.current.shownAt) || Date.now());
            return;
        }
        if (Date.now() < Number(state.nextAt)) {
            schedule();
            return;
        }
        restoreOrFetch();
    }

    detailsToggle.addEventListener('click', function () {
        const opening = details.hidden;
        details.hidden = !opening;
        detailsToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
        detailsLabel.textContent = opening ? hideDetailsText : readDetailsText;
    });
    skip.addEventListener('click', function () { if (!skip.disabled) hideAndScheduleNext(); });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && overlay.classList.contains('is-visible') && !skip.disabled) hideAndScheduleNext();
    });
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && !state.current) {
            if (timer) window.clearTimeout(timer);
            timer = null;
            check();
        }
    });

    if (state.current && state.current.ad) show(state.current.ad, Number(state.current.shownAt) || Date.now());
    else schedule();
})();
</script>
