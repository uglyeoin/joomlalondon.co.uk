
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open('version1').then(function(cache) {
            return cache.addAll([
                'www.joomlalondon.co.uk'
                          ]).then(function() {
                return self.skipWaiting();
            });
        })
    );
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function(event) {
    console.log(event.request.url);

    event.respondWith(
        caches.match(event.request).then(function(response) {
            return response || fetch(event.request);
        })
    );
});