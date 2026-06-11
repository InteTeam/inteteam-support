// Collector Service Worker for offline support
const CACHE_NAME = 'collector-v1';
const STATIC_CACHE = 'collector-static-v1';

// Assets to cache immediately
const PRECACHE_ASSETS = [
  '/collector/dashboard',
  '/collector/collections',
  '/collector/locations',
  '/collector/loans',
  '/collector/wishlist',
  '/collector/settings',
  '/collector/team',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Collector SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      console.log('[Collector SW] Precaching static assets');
      return cache.addAll(PRECACHE_ASSETS.map(url => new Request(url, { cache: 'reload' })));
    })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Collector SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => cacheName !== CACHE_NAME && cacheName !== STATIC_CACHE)
          .map((cacheName) => {
            console.log('[Collector SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Only handle collector routes
  if (!url.pathname.startsWith('/collector')) {
    return;
  }

  // Skip API requests and non-GET requests
  if (event.request.method !== 'GET' || url.pathname.startsWith('/api/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      // Return cached response if available
      if (cachedResponse) {
        console.log('[Collector SW] Serving from cache:', url.pathname);
        return cachedResponse;
      }

      // Otherwise, fetch from network
      return fetch(event.request).then((networkResponse) => {
        // Cache successful responses
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            console.log('[Collector SW] Caching new response:', url.pathname);
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      }).catch(() => {
        // Network failed, try to serve from cache even if stale
        console.log('[Collector SW] Network failed, trying offline fallback');
        return caches.match(event.request);
      });
    })
  );
});

// Message event - handle messages from clients
self.addEventListener('message', (event) => {
  console.log('[Collector SW] Received message:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => caches.delete(cacheName))
      );
    }).then(() => {
      event.ports[0].postMessage({ type: 'CACHE_CLEARED' });
    });
  }
});
