const CACHE_NAME = 'padel-tournament-v1';
const STATIC_CACHE = 'padel-static-v1';
const DYNAMIC_CACHE = 'padel-dynamic-v1';

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/offline.html',
];

// API routes that should use network-first strategy
const API_ROUTES = [
    '/api/tournaments',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
                        .map((name) => caches.delete(name))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - handle requests with appropriate strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // API requests - Network first, fall back to cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Static assets - Cache first
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // HTML pages - Stale while revalidate
    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Default - Network first
    event.respondWith(networkFirst(request));
});

// Check if URL is a static asset
function isStaticAsset(pathname) {
    const staticExtensions = ['.js', '.css', '.png', '.jpg', '.jpeg', '.svg', '.ico', '.woff', '.woff2'];
    return staticExtensions.some(ext => pathname.endsWith(ext));
}

// Cache first strategy
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.error('[SW] Cache first failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page for HTML requests
        if (request.headers.get('Accept')?.includes('text/html')) {
            return caches.match('/offline.html');
        }

        return new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Stale while revalidate strategy
async function staleWhileRevalidate(request) {
    const cachedResponse = await caches.match(request);

    const fetchPromise = fetch(request)
        .then((networkResponse) => {
            if (networkResponse.ok) {
                const cache = caches.open(DYNAMIC_CACHE);
                cache.then(c => c.put(request, networkResponse.clone()));
            }
            return networkResponse;
        })
        .catch(() => {
            // Return cached or offline page
            return cachedResponse || caches.match('/offline.html');
        });

    return cachedResponse || fetchPromise;
}

// Handle sync events for offline score submissions
self.addEventListener('sync', (event) => {
    console.log('[SW] Sync event:', event.tag);

    if (event.tag === 'sync-scores') {
        event.waitUntil(syncScores());
    }
});

// Sync offline scores
async function syncScores() {
    console.log('[SW] Syncing offline scores...');

    // This will be handled by the IndexedDB sync manager in the app
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({ type: 'SYNC_SCORES' });
    });
}

// Handle push notifications (future feature)
self.addEventListener('push', (event) => {
    console.log('[SW] Push received:', event);

    const options = {
        body: event.data?.text() || 'New update available',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-72.png',
        vibrate: [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification('Rezimainpadel Versus', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
