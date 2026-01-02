/**
 * Service Worker for Field Operations
 *
 * Enables offline functionality for field operations
 *
 * @package CampaignPress
 * @subpackage Premium/FieldOperations
 * @since 2.0.0
 */

const CACHE_VERSION = 'cp-field-ops-v1';
const CACHE_ASSETS = [
    '/assets/css/field-ops.css',
    '/assets/css/field-ops-admin.css',
    '/assets/js/field-ops.js',
    '/assets/js/field-ops-offline.js',
    '/assets/js/field-ops-admin.js'
];

// Install event - cache static assets
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_VERSION).then(function (cache) {
            return cache.addAll(CACHE_ASSETS);
        })
    );

    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (cacheName !== CACHE_VERSION) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );

    return self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', function (event) {
    const requestUrl = new URL(event.request.url);

    // Only handle same-origin requests
    if (requestUrl.origin !== location.origin) {
        return;
    }

    // Network-first strategy for API requests
    if (requestUrl.pathname.includes('/wp-json/') || requestUrl.pathname.includes('/wp-admin/admin-ajax.php')) {
        event.respondWith(
            fetch(event.request)
                .then(function (response) {
                    // Clone response to cache it
                    const responseToCache = response.clone();

                    caches.open(CACHE_VERSION).then(function (cache) {
                        cache.put(event.request, responseToCache);
                    });

                    return response;
                })
                .catch(function () {
                    // If network fails, try cache
                    return caches.match(event.request);
                })
        );
        return;
    }

    // Cache-first strategy for static assets
    event.respondWith(
        caches.match(event.request).then(function (response) {
            if (response) {
                return response;
            }

            return fetch(event.request).then(function (response) {
                // Don't cache non-successful responses
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                const responseToCache = response.clone();

                caches.open(CACHE_VERSION).then(function (cache) {
                    cache.put(event.request, responseToCache);
                });

                return response;
            });
        })
    );
});

// Background sync for offline data
self.addEventListener('sync', function (event) {
    if (event.tag === 'sync-field-ops-data') {
        event.waitUntil(syncFieldOpsData());
    }
});

/**
 * Sync field operations data when connection is available
 */
function syncFieldOpsData() {
    // This would be called from the offline.js file
    // to sync IndexedDB data to the server
    return Promise.resolve();
}

// Handle messages from clients
self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(CACHE_VERSION).then(function (cache) {
                return cache.addAll(event.data.urls);
            })
        );
    }
});
