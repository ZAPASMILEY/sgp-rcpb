const CACHE_NAME = 'sgp-rcpb-v1';
const ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    'icons/icon-192x192.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});