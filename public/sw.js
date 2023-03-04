// The activate handler takes care of cleaning up old caches.
var cacheVersion = 2;
var currentCache = {
  offline: 'Yo!kart-offline' + cacheVersion
};
const offlineUrl = '/offline.html';
const cacheName = currentCache.offline;

self.addEventListener('install', (event) => {
  event.waitUntil(
    // Cache the offline page when installing the service worker
    fetch(offlineUrl, { credentials: 'include' }).then(response =>
      caches.open(cacheName).then(cache => cache.put(offlineUrl, response)),
    ),
  );
});

self.addEventListener('fetch', function(event){
if (event.request.mode === 'navigate') {
  return event.respondWith(
    fetch(event.request).catch(() => caches.match(offlineUrl))
  );
}    
});