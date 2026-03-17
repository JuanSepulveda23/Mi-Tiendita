const CACHE_NAME = 'tienda-pos-v1';
const STATIC_ASSETS = [
    '/',
    '/pos',
    '/dashboard',
    '/offline',
];

// Install: cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: network-first for API, cache-first for assets
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests (except sync)
    if (event.request.method !== 'GET') return;

    // For API/JSON requests: network first
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/pos/search') ||
        event.request.headers.get('Accept')?.includes('application/json')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // For page/asset requests: network first, fallback to cache
    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() =>
                caches.match(event.request).then(cached => cached || caches.match('/offline'))
            )
    );
});

// Listen for sync events to push offline sales
self.addEventListener('sync', event => {
    if (event.tag === 'sync-offline-sales') {
        event.waitUntil(syncOfflineSales());
    }
});

async function syncOfflineSales() {
    // This will be triggered by the main app
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({ type: 'SYNC_OFFLINE_SALES' });
    });
}
