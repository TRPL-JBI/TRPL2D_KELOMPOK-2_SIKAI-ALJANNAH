// Service Worker untuk RKM Al-Jannah
// Caching strategy: preloader shell + page caching + image caching
var CACHE_SHELL = 'rkm-shell-v5';
var CACHE_PAGES = 'rkm-pages-v6';
var CACHE_IMAGES = 'rkm-images-v5';

var STATIC_ASSETS = [
    '/preloader.html',
    '/images/logo-al-jannah.png',
    '/images/ranting.png',
    '/images/pohon.png',
    '/images/bottom-ornament.png',
];

// Install: cache shell + static assets
self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(CACHE_SHELL).then(function (cache) {
            return cache.addAll(STATIC_ASSETS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// Activate: clean old caches, claim all clients
self.addEventListener('activate', function (e) {
    var cachesToKeep = [CACHE_SHELL, CACHE_PAGES, CACHE_IMAGES];
    e.waitUntil(
        caches.keys().then(function (names) {
            return Promise.all(names.map(function (n) {
                if (cachesToKeep.indexOf(n) === -1) return caches.delete(n);
            }));
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// Fetch: navigation, images, static assets
self.addEventListener('fetch', function (e) {
    var url = new URL(e.request.url);

    // Only handle same-origin GET requests
    if (e.request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;

    // Bypass parameter — fetch fresh from network (used by preloader.html hydration)
    if (url.searchParams.has('__preloader_bypass')) {
        e.respondWith(fetch(e.request));
        return;
    }

    // Skip non-HTML downloads — let browser handle directly
    if (isDownloadRequest(e.request)) return;

    // Navigation requests (HTML pages)
    if (e.request.mode === 'navigate') {
        e.respondWith(
            caches.match('/preloader.html').then(function (preloader) {
                if (preloader) {
                    // Fetch real page from network, cache for offline, show preloader while loading
                    var fetchPromise = fetch(e.request).then(function (res) {
                        if (res.ok) {
                            var clone = res.clone();
                            caches.open(CACHE_PAGES).then(function (cache) {
                                cache.put(e.request, clone);
                            });
                        }
                        return res;
                    }).catch(function () {
                        return caches.match(e.request);
                    });
                    e.waitUntil(fetchPromise);
                    return preloader;
                }
                return fetch(e.request).then(function (res) {
                    if (res.ok) {
                        var clone = res.clone();
                        caches.open(CACHE_PAGES).then(function (cache) {
                            cache.put(e.request, clone);
                        });
                    }
                    return res;
                });
            })
        );
        return;
    }

    // Image requests
    if (isImageRequest(e.request)) {
        e.respondWith(
            caches.match(e.request).then(function (cached) {
                if (cached) return cached;
                return fetch(e.request).then(function (res) {
                    if (res.ok) {
                        var clone = res.clone();
                        caches.open(CACHE_IMAGES).then(function (cache) {
                            cache.put(e.request, clone);
                        });
                    }
                    return res;
                }).catch(function () {
                    return caches.match(e.request);
                });
            })
        );
        return;
    }
});

function isImageRequest(request) {
    var url = new URL(request.url);
    var path = url.pathname.toLowerCase();
    var exts = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.ico', '.avif'];
    return exts.some(function (e) { return path.endsWith(e); });
}

function isDownloadRequest(request) {
    var url = new URL(request.url);
    var path = url.pathname.toLowerCase();
    var exts = ['.xlsx', '.xls', '.pdf', '.docx', '.doc', '.zip', '.csv', '.ods'];
    if (exts.some(function (e) { return path.endsWith(e); })) return true;
    if (path.endsWith('/pdf')) return true;
    if (path.indexOf('/export') !== -1) return true;
    if (path.indexOf('/template') !== -1) return true;
    return false;
}
