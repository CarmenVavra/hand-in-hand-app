/*
 * HandInHand Service Worker (Mindestumfang für die PWA-Installation)
 *
 * - Seiten (Navigation): immer zuerst das Netz; ohne Netz die Offline-Seite.
 *   Keine HTML-Seiten im Cache, damit nie veraltete CSRF-Token oder Inhalte erscheinen.
 * - Build-Assets (/build/…, Schrift, Icons): Cache zuerst (Dateinamen enthalten einen Hash).
 * - Medien, Livewire und alle POST-Anfragen gehen nie durch den Cache.
 */
const CACHE = 'hand-in-hand-v1';
const OFFLINE_URL = '/offline';

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.add(OFFLINE_URL)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/app-icons/')) {
        event.respondWith(
            caches.match(request).then(
                (cached) =>
                    cached ||
                    fetch(request).then((response) => {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                        return response;
                    }),
            ),
        );
    }
});
