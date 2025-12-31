importScripts("https://js.pusher.com/beams/service-worker.js");

const CACHE_NAME = "pos-offline-v1";
const OFFLINE_URL = "/offline";

// Essential POS assets to pre-cache
const POS_ASSETS = [
    '/js/pos-offline.js',
    '/sound/sound_beep-29.mp3',
    '/img/no-menu-image.jpg',
    '/img/no-menu-image.webp',
    // Add CSS files used by POS
    '/build/assets/app-*.css',
    '/build/assets/app-*.js',
];

// API endpoints to cache for offline use
const POS_API_CACHE = 'pos-api-cache-v1';
const API_CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

self.addEventListener("install", (event) => {
    event.waitUntil(
        Promise.all([
            // Cache app assets
            caches.open("app-cache").then(async (cache) => {
                return fetch("/manifest.json")
                    .then((response) => response.json())
                    .then((manifest) => {
                        const fullStartUrl =
                            manifest.start_url_base +
                            (manifest.query_params ? manifest.query_params : "");
                        return cache.add(fullStartUrl);
                    })
                    .catch((error) =>
                        console.error("Manifest fetch error:", error)
                    );
            }),
            // Pre-cache POS-specific assets
            caches.open(CACHE_NAME).then((cache) => {
                return cache.addAll([
                    '/js/pos-offline.js',
                    '/sound/sound_beep-29.mp3',
                ]).catch(err => console.warn('POS asset pre-cache failed:', err));
            })
        ])
    );
    // Force waiting service worker to become active
    self.skipWaiting();
});

self.addEventListener("push", (event) => {
    let options = {
        body: event.data.text(),
        icon: "/img/192x192.png",
        badge: "/icons/badge-72x72.png",
    };

    event.waitUntil(
        self.registration.showNotification("New Notification", options)
    );
});

self.addEventListener("fetch", (event) => {
    // Only cache GET requests from supported schemes
    if (event.request.method !== "GET") {
        return event.respondWith(fetch(event.request));
    }

    // Skip unsupported schemes (chrome-extension, moz-extension, etc.)
    const url = new URL(event.request.url);
    const supportedSchemes = ["http", "https"];
    if (!supportedSchemes.includes(url.protocol.replace(":", ""))) {
        return event.respondWith(fetch(event.request));
    }

    // Special handling for POS API endpoints - Network first with cache fallback
    if (url.pathname.startsWith('/api/pos/')) {
        event.respondWith(
            caches.open(POS_API_CACHE).then((cache) => {
                return fetch(event.request)
                    .then((response) => {
                        if (response && response.status === 200) {
                            // Clone and cache the response
                            cache.put(event.request, response.clone());
                        }
                        return response;
                    })
                    .catch(() => {
                        // Network failed, try cache
                        return cache.match(event.request);
                    });
            })
        );
        return;
    }

    // Special handling for static assets - Cache first
    const isStaticAsset = url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf|mp3)$/);
    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version, but update cache in background
                    fetch(event.request).then((response) => {
                        if (response && response.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, response);
                            });
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }
                // Not in cache, fetch from network
                return fetch(event.request).then((response) => {
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Default handling - Network first with cache fallback
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (
                    !response ||
                    response.status !== 200 ||
                    response.type !== "basic"
                ) {
                    return response; // Skip caching if the response is not valid
                }

                let responseClone = response.clone();
                caches.open("app-cache").then((cache) => {
                    cache.put(event.request, responseClone).catch((err) => {
                        console.warn("Cache Add Failed:", err);
                    });
                });

                return response;
            })
            .catch(() => caches.match(event.request)) // Serve from cache if offline
    );
});

// Activate Event - Clean up old caches
self.addEventListener("activate", (event) => {
    const cacheWhitelist = [CACHE_NAME, POS_API_CACHE, "app-cache"];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!cacheWhitelist.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Take control of all pages immediately
    self.clients.claim();
});

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CLEAR_POS_CACHE') {
        caches.delete(POS_API_CACHE).then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
