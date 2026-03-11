const CACHE_NAME = 'bgn-attendance-v1';

self.addEventListener('install', (event) => {
    console.log('[Service Worker] Install');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activate');
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Only cache GET requests, mostly just to satisfy PWA criteria.
    // We want attendance to happen online, but this allows standalone mode.
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
