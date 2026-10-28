/**
 * rx-offline.js — বিদ্যুৎ/নেট না থাকলেও প্রেসক্রিপশন তৈরি ও প্রিন্ট করার জন্য।
 *
 * কীভাবে কাজ করে (সংক্ষেপে):
 *  1) পেজ অনলাইনে লোড হওয়ার সময় সার্ভার থেকে পাওয়া ব্যালেন্স/চার্জ localStorage-এ ক্যাশ হয়।
 *  2) নেট চলে গেলে window.RxOffline.queueFromForm() ফর্ম-ডেটা IndexedDB-তে জমা রাখে এবং
 *     ব্রাউজারেই একটা সাধারণ প্রিন্ট-ভিউ খুলে দেয় (সার্ভারের রঙিন letterhead টেমপ্লেট ছাড়া, সাদাসিধা)।
 *  3) নেট ফিরলে (online ইভেন্ট + পর্যায়ক্রমিক চেক) জমে থাকা সবগুলো সার্ভারে POST হয়ে যায়।
 *     প্রতিটা আলাদাভাবে — একটা ব্যর্থ হলে (যেমন আসল ব্যালেন্স তখন কম) বাকিগুলো থেমে যায় না,
 *     আর ব্যর্থ item queue-তেই থেকে যায়, হারিয়ে যায় না।
 *
 * নির্ভরতা: window.RX_OFFLINE_CTX (create.blade.php থেকে)। এটা না থাকলে কিছুই করে না।
 */
(function () {
    'use strict';

    var ctx = window.RX_OFFLINE_CTX;
    if (!ctx) return;

    var DB_NAME = 'rx-offline-v1';
    var STORE = 'queue';
    var LS_BALANCE = 'rxOfflineBalance';
    var dbPromise = null;

    /* ================= IndexedDB helpers ================= */
    function openDb() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise(function (resolve, reject) {
            if (!window.indexedDB) { reject(new Error('IndexedDB নেই')); return; }
            var req = indexedDB.open(DB_NAME, 1);
            req.onupgradeneeded = function () {
                var db = req.result;
                if (!db.objectStoreNames.contains(STORE)) {
                    db.createObjectStore(STORE, { keyPath: 'client_id' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
        return dbPromise;
    }
    function withStore(mode, fn) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(STORE, mode);
                var store = tx.objectStore(STORE);
                var result;
                fn(store, function (r) { result = r; });
                tx.oncomplete = function () { resolve(result); };
                tx.onerror = function () { reject(tx.error); };
            });
        });
    }
    function putItem(item) {
        return withStore('readwrite', function (store, done) { done(store.put(item)); });
    }
    function deleteItem(clientId) {
        return withStore('readwrite', function (store, done) { done(store.delete(clientId)); });
    }
    function allItems() {
        return withStore('readonly', function (store, done) {
            var req = store.getAll();
            req.onsuccess = function () { done(req.result || []); };
        });
    }

    /* ================= Balance cache ================= */
    function cacheBalance(balance, fullCharge, invCharge) {
        try {
            localStorage.setItem(LS_BALANCE, JSON.stringify({
                balance: balance, full: fullCharge, inv: invCharge, at: Date.now(),
            }));
        } catch (e) {}
    }
    function getCachedBalance() {
        try {
            var raw = localStorage.getItem(LS_BALANCE);
            return raw ? JSON.parse(raw) : null;
        } catch (e) { return null; }
    }
    // ক্যাশ করা ব্যালেন্স থেকে এখনো-sync-না-হওয়া সব item-এর চার্জ বাদ দিলে যা থাকে —
    // অর্থাৎ "অফলাইনে আসলে এখন কত খরচ করার সুযোগ আছে"।
    function effectiveBalance() {
        return allItems().then(function (items) {
            var cached = getCachedBalance();
            if (!cached) return null;
            var used = items.reduce(function (s, it) { return s + (Number(it.charge) || 0); }, 0);
            return cached.balance - used;
        });
    }

    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0, v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }
    function money(n) {
        n = Number(n) || 0;
        return '৳' + n.toLocaleString('en-US', { maximumFractionDigits: 2 });
    }
    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ================= FormData -> nested object (medicines[0][dose_morning] সহ) ================= */
    function formToNested(form) {
        var fd = new FormData(form);
        var out = {};
        fd.forEach(function (value, key) {
            if (key === '_token') return;
            var path = key.replace(/\]/g, '').split('[');
            var ref = out;
            for (var i = 0; i < path.length - 1; i++) {
                var k = path[i];
                var nextIsIndex = /^\d+$/.test(path[i + 1]);
                if (!(k in ref)) ref[k] = nextIsIndex ? [] : {};
                ref = ref[k];
            }
            ref[path[path.length - 1]] = value;
        });
        if (Array.isArray(out.medicines)) {
            out.medicines = out.medicines.filter(function (m) { return m && m.medicine_name; });
        }
        return out;
    }

    /* ================= Connectivity ================= */
    var online = navigator.onLine;
    function isOnline() { return online; }

    function probe() {
        if (!ctx.pingUrl) return Promise.resolve(navigator.onLine);
        var url = ctx.pingUrl + (ctx.pingUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        return fetch(url, { method: 'HEAD', cache: 'no-store', credentials: 'omit' })
            .then(function () { return true; })
            .catch(function () { return false; });
    }

    function setOnline(v) {
        var changed = online !== v;
        online = v;
        if (changed) renderBanner();
        if (v) trySync();
    }

    window.addEventListener('online', function () { setOnline(true); });
    window.addEventListener('offline', function () { setOnline(false); });
    // navigator.onLine কখনো কখনো ভুল বলে (wifi-তে আছে কিন্তু নেট নেই) — তাই পর্যায়ক্রমিক বাস্তব চেক
    setInterval(function () {
        probe().then(setOnline);
    }, 15000);

    /* ================= CSRF (sync POST-এর জন্য) ================= */
    function csrfToken() {
        var m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : (window.CSRF_TOKEN || '');
    }

    /* ================= Offline print (সাদাসিধা, letterhead ছাড়া) ================= */
    function doseLabel(m) {
        if (m.dose_text) return m.dose_text;
        var n = [m.dose_morning || 0, m.dose_noon || 0, m.dose_night || 0];
        return n.join('+');
    }
    function renderOfflinePrint(data, meta) {
        var meds = (data.medicines || []).map(function (m, i) {
            return '<tr><td>' + (i + 1) + '. ' + escHtml(m.medicine_name) +
                (m.medicine_type ? ' <small>(' + escHtml(m.medicine_type) + ')</small>' : '') + '</td>' +
                '<td>' + escHtml(doseLabel(m)) + '</td>' +
                '<td>' + escHtml(m.duration || '') + '</td>' +
                '<td>' + escHtml(m.instruction || '') + '</td></tr>';
        }).join('');

        var chamber = ctx.chamber;
        var html = '' +
            '<!DOCTYPE html><html lang="bn"><head><meta charset="utf-8">' +
            '<title>Prescription (Offline)</title>' +
            '<style>' +
            'body{font-family:"Hind Siliguri",system-ui,sans-serif;max-width:760px;margin:20px auto;color:#0f172a;padding:0 16px}' +
            '.badge{display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:8px;padding:6px 12px;font-weight:700;font-size:13px;margin-bottom:14px}' +
            'header{border-bottom:2px solid #0f172a;padding-bottom:10px;margin-bottom:14px}' +
            'header h1{margin:0;font-size:20px}header p{margin:2px 0;font-size:13px;color:#334155}' +
            '.grid{display:grid;grid-template-columns:1fr 1fr;gap:4px 18px;font-size:13.5px;margin-bottom:14px}' +
            'table{width:100%;border-collapse:collapse;margin-top:6px}' +
            'th,td{border:1px solid #cbd5e1;padding:6px 8px;font-size:13px;text-align:left;vertical-align:top}' +
            'th{background:#f1f5f9}' +
            '.sec{margin-top:14px}.sec b{display:block;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:3px}' +
            '.sign{margin-top:50px;text-align:right;font-size:13px}' +
            '@media print{.badge{display:none}}' +
            '</style></head><body>' +
            '<div class="badge">⚡ অফলাইন কপি — এটা অস্থায়ী, নেট ফিরলে আসল Prescription ID/Patient ID সহ রেকর্ড সার্ভারে সেভ হবে।</div>' +
            '<header><h1>' + escHtml(ctx.doctor.name) + '</h1>' +
            '<p>' + escHtml(ctx.doctor.qualification || '') + (ctx.doctor.designation ? ' · ' + escHtml(ctx.doctor.designation) : '') + '</p>' +
            (ctx.doctor.bmdc ? '<p>BMDC: ' + escHtml(ctx.doctor.bmdc) + '</p>' : '') +
            (chamber ? '<p>' + escHtml(chamber.name) + (chamber.hospital ? ', ' + escHtml(chamber.hospital) : '') + '</p><p>' + escHtml(chamber.address || '') + (chamber.phone ? ' · ' + escHtml(chamber.phone) : '') + '</p>' : '') +
            '</header>' +
            '<div class="grid">' +
            '<div><b>রোগী:</b> ' + escHtml(data.patient_name) + '</div>' +
            '<div><b>তারিখ:</b> ' + new Date(meta.createdAt).toLocaleString('bn-BD') + '</div>' +
            '<div><b>বয়স/লিঙ্গ:</b> ' + escHtml(data.patient_age || '-') + ' / ' + escHtml(data.patient_gender || '-') + '</div>' +
            '<div><b>মোবাইল:</b> ' + escHtml(data.patient_mobile || '-') + '</div>' +
            (data.bp || data.pulse || data.temperature ? '<div><b>Vitals:</b> BP ' + escHtml(data.bp || '-') + ', Pulse ' + escHtml(data.pulse || '-') + ', Temp ' + escHtml(data.temperature || '-') + '</div>' : '') +
            '</div>' +
            (data.clinical_complaints ? '<div class="sec"><b>Complaints</b>' + escHtml(data.clinical_complaints) + '</div>' : '') +
            (data.diagnosis ? '<div class="sec"><b>Diagnosis</b>' + escHtml(data.diagnosis) + '</div>' : '') +
            (data.investigations ? '<div class="sec"><b>Investigations</b>' + escHtml(data.investigations) + '</div>' : '') +
            (meds ? '<div class="sec"><b>Rx</b><table><tr><th>ঔষধ</th><th>ডোজ</th><th>মেয়াদ</th><th>নির্দেশ</th></tr>' + meds + '</table></div>' : '') +
            (data.advice ? '<div class="sec"><b>Advice</b>' + escHtml(data.advice) + '</div>' : '') +
            (data.followup_date ? '<div class="sec"><b>Follow-up</b>' + escHtml(data.followup_date) + '</div>' : '') +
            '<div class="sign">' + escHtml(ctx.doctor.name) + '<br>স্বাক্ষর</div>' +
            '<script>window.onload=function(){setTimeout(function(){window.print();},200);};<\/script>' +
            '</body></html>';

        var w = window.open('', '_blank');
        if (!w) { alert('প্রিন্ট উইন্ডো ব্লক হয়েছে — পপআপ অনুমতি দিন।'); return; }
        w.document.open();
        w.document.write(html);
        w.document.close();
    }

    /* ================= Queue from the visible form ================= */
    function queueFromForm(form, charge, mode) {
        var cached = getCachedBalance();
        if (!cached) {
            return Promise.resolve({
                ok: false,
                error: 'এখনো ব্যালেন্স ক্যাশ করা হয়নি। প্রথমবার নেট চালু থাকা অবস্থায় এই পেজটা একবার খুলুন, তারপর থেকে অফলাইনেও কাজ করবে।',
            });
        }
        return effectiveBalance().then(function (eff) {
            if (eff === null || eff < charge) {
                return {
                    ok: false,
                    error: 'অফলাইন ব্যালেন্স (ক্যাশ অনুযায়ী) অপর্যাপ্ত — আছে ' + money(eff || 0) + ', প্রয়োজন ' + money(charge) + '। আগে ব্যালেন্স রিচার্জ করে একবার নেটে এসে এই পেজ খুলে নিন।',
                };
            }
            var data = formToNested(form);
            var clientId = uuid();
            var item = {
                client_id: clientId,
                charge: charge,
                mode: mode,
                created_at: Date.now(),
                patient_label: data.patient_name || '(নাম নেই)',
                data: data,
                last_error: null,
            };
            return putItem(item).then(function () {
                renderOfflinePrint(data, { createdAt: item.created_at });
                renderBanner();
                return { ok: true, client_id: clientId };
            });
        });
    }

    /* ================= Sync ================= */
    var syncing = false;
    function trySync() {
        if (syncing || !online) return Promise.resolve();
        return allItems().then(function (items) {
            if (!items.length) return;
            syncing = true;
            return fetch(ctx.syncUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    items: items.map(function (it) {
                        var payload = Object.assign({}, it.data, { client_id: it.client_id });
                        return payload;
                    }),
                }),
            })
                .then(function (res) {
                    if (res.status === 401 || res.status === 419) {
                        showNeedLogin();
                        return null;
                    }
                    if (!res.ok) return null;
                    return res.json();
                })
                .then(function (json) {
                    if (!json) return;
                    var tasks = [];
                    (json.results || []).forEach(function (r) {
                        if (r.ok) {
                            tasks.push(deleteItem(r.client_id));
                            toast('✓ Sync হয়েছে — ' + (r.patient_code || '#' + r.prescription_id));
                        } else {
                            tasks.push(putItem(Object.assign({}, items.filter(function (i) { return i.client_id === r.client_id; })[0] || {}, {
                                last_error: r.error || 'অজানা সমস্যা',
                            })));
                        }
                    });
                    if (typeof json.balance === 'number') {
                        var c = getCachedBalance() || {};
                        cacheBalance(json.balance, c.full, c.inv);
                    }
                    return Promise.all(tasks);
                })
                .catch(function () { /* নেট আবার গেলে পরের চেষ্টায় হবে */ })
                .then(function () {
                    syncing = false;
                    renderBanner();
                    renderOutbox();
                });
        });
    }
    function showNeedLogin() {
        toast('⚠️ সেশন শেষ হয়ে গেছে — লগইন করুন, ডেটা মুছে যায়নি');
    }
    setInterval(trySync, 30000);

    /* ================= UI ================= */
    var banner, outboxPanel;
    function toast(msg) {
        var t = document.getElementById('rxToast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._h);
        t._h = setTimeout(function () { t.classList.remove('show'); }, 2600);
    }

    function renderBanner() {
        banner = banner || document.getElementById('rxOfflineBanner');
        if (!banner) return;
        allItems().then(function (items) {
            var count = items.length;
            banner.classList.toggle('online', online);
            banner.classList.toggle('show', !online || count > 0);
            document.body.classList.toggle('rxo-has-banner', !online || count > 0);
            banner.querySelector('.rxo-count').textContent = count;
            var text;
            if (!online) {
                effectiveBalance().then(function (eff) {
                    banner.querySelector('.rxo-text').textContent =
                        '📴 অফলাইন — ক্যাশ ব্যালেন্স ' + money(eff === null ? 0 : eff) + (count ? ', ' + count + 'টা sync বাকি' : '');
                });
            } else if (count > 0) {
                text = '🟢 অনলাইন — ' + count + 'টা অফলাইন প্রেসক্রিপশন sync হচ্ছে...';
                banner.querySelector('.rxo-text').textContent = text;
            }
        });
    }

    function renderOutbox() {
        outboxPanel = outboxPanel || document.getElementById('rxOfflineOutbox');
        if (!outboxPanel) return;
        var list = outboxPanel.querySelector('.rxo-list');
        allItems().then(function (items) {
            items.sort(function (a, b) { return b.created_at - a.created_at; });
            if (!items.length) {
                list.innerHTML = '<div class="rxo-empty">কোনো অফলাইন প্রেসক্রিপশন জমা নেই।</div>';
                return;
            }
            list.innerHTML = items.map(function (it) {
                var time = new Date(it.created_at).toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' });
                return '<div class="rxo-item">' +
                    '<div class="rxo-item-top"><span class="rxo-name">' + escHtml(it.patient_label) + '</span><span class="rxo-charge">' + money(it.charge) + '</span></div>' +
                    '<div class="rxo-meta">' + time + ' · ' + (it.mode === 'investigation_only' ? 'Investigation Advice' : 'প্রেসক্রিপশন') + '</div>' +
                    (it.last_error ? '<div class="rxo-err">' + escHtml(it.last_error) + '</div>' : '<span class="rxo-pill">Sync-এর অপেক্ষায়</span>') +
                    '<br><button type="button" class="rxo-print-btn" data-print="' + escHtml(it.client_id) + '"><i class="fa-solid fa-print"></i> আবার প্রিন্ট</button>' +
                    '</div>';
            }).join('');
        });
    }

    function bindUI() {
        banner = document.getElementById('rxOfflineBanner');
        outboxPanel = document.getElementById('rxOfflineOutbox');
        if (!banner || !outboxPanel) return;

        banner.querySelector('.rxo-outbox-btn').addEventListener('click', function () {
            outboxPanel.classList.add('show');
            renderOutbox();
        });
        outboxPanel.querySelector('.rxo-close').addEventListener('click', function () {
            outboxPanel.classList.remove('show');
        });
        outboxPanel.querySelector('.rxo-sync-btn').addEventListener('click', function () {
            probe().then(function (v) { setOnline(v); if (v) trySync(); else toast('এখনো অফলাইন মনে হচ্ছে'); });
        });
        outboxPanel.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-print]');
            if (!btn) return;
            allItems().then(function (items) {
                var it = items.filter(function (i) { return i.client_id === btn.getAttribute('data-print'); })[0];
                if (it) renderOfflinePrint(it.data, { createdAt: it.created_at });
            });
        });
    }

    /* ================= Init ================= */
    document.addEventListener('DOMContentLoaded', function () {
        bindUI();
        // পেজ অনলাইনে লোড হয়েছে মানে এই মুহূর্তের ব্যালেন্স সার্ভার থেকেই এসেছে — ফ্রেশ ক্যাশ করে রাখো
        cacheBalance(ctx.balance, ctx.fullCharge, ctx.invCharge);
        renderBanner();
        probe().then(function (v) {
            setOnline(v);
            if (v) trySync();
        });
    });

    window.RxOffline = {
        isOnline: isOnline,
        queueFromForm: queueFromForm,
        sync: trySync,
    };
})();
