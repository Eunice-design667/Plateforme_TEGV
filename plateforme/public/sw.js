// Service worker minimal : met en cache les images et les fichiers de l'interface
// pour que l'application s'ouvre vite, même avec une connexion faible.
const CACHE = 'tegv-v2';
const OFFLINE_PAGE = '/hors-ligne.html';
const PRECACHE = [OFFLINE_PAGE, '/images/logo-rond.png', '/images/eglise/culte.jpg', '/icons/icon-192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Page change without network: show the offline page instead of the browser error.
    if (event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_PAGE)));
        return;
    }

    const isStatic = url.origin === location.origin && /^\/(build|images|icons)\//.test(url.pathname);

    if (event.request.method !== 'GET' || !isStatic) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
            const copy = response.clone();
            caches.open(CACHE).then((cache) => cache.put(event.request, copy));
            return response;
        })),
    );
});
