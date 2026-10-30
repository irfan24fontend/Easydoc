/**
 * sw.js — শুধু prescription create পেজ আর তার static asset-গুলো অফলাইনে reload করার জন্য।
 * কোনো POST/API কল এখানে ধরা হয় না — সেটা rx-offline.js নিজে IndexedDB দিয়ে সামলায়।
 */
const CACHE = 'easydoc-rx-v3';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return; // ফর্ম POST কখনো এখানে ধরা হয় না

    const url = new URL(req.url);
    if (url.origin !== location.origin) return;

    // Prescription create পেজ — আগে নেট দিয়ে চেষ্টা (সবসময় সবচেয়ে ফ্রেশ ডেটা), ব্যর্থ হলে cache
    if (url.pathname.indexOf('/doctor/prescriptions/create') !== -1) {   // sub-folder (/~user/...) hole-o kaj korbe
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(req, copy));
                    return res;
                })
                .catch(() =>
                    caches.match(req).then((cached) => cached || caches.match(new URL('doctor/prescriptions/create', self.registration.scope).href))
                )
        );
        return;
    }

    // স্থির ফাইল (css/js/font/icon) — cache-first, সাথে ব্যাকগ্রাউন্ডে আপডেট
    if (/\.(?:css|js|woff2?|ttf|png|jpg|jpeg|svg|ico)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(req).then((cached) => {
                const network = fetch(req)
                    .then((res) => {
                        caches.open(CACHE).then((c) => c.put(req, res.clone()));
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
    }
});