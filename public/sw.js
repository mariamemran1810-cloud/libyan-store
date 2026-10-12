/* ============================================================
   المتجر الليبي — عامل الخدمة (Service Worker)
   يخزّن ملفات الثابتة (CSS/JS/الصور) ليعمل التطبيق بسرعة
   وحتى عند ضعف الشبكة. الصفحات تُجلب دائمًا من الخادم.
   ============================================================ */
const CACHE = 'libyan-store-v1';
const STATIC = ['/assets/css/app.css', '/assets/js/app.js', '/favicon.svg', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(STATIC)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    const isStatic = /\.(css|js|svg|png|jpg|webp|woff2?)(\?.*)?$/i.test(new URL(request.url).pathname);

    if (isStatic) {
        // ملفات ثابتة: من الكاش أولًا ثم الشبكة
        event.respondWith(
            caches.match(request).then((hit) => hit || fetch(request).then((response) => {
                const copy = response.clone();
                caches.open(CACHE).then((cache) => cache.put(request, copy));
                return response;
            }))
        );
        return;
    }

    // الصفحات: الشبكة أولًا (بيانات طازجة)، وإن تعذّر فالكاش
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
