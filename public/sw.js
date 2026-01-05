const CACHE_NAME = 'resta-static-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Interceptar imágenes y fuentes
    if (
        event.request.destination === 'image' || 
        event.request.destination === 'font' ||
        event.request.url.match(/\.(jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|otf|eot)$/i)
    ) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                // Si está en caché, devolverlo (Velocidad máxima)
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // Si no, buscar en la red y guardarlo en caché para la próxima
                return fetch(event.request).then((networkResponse) => {
                    if (!networkResponse || networkResponse.status !== 200) {
                        return networkResponse;
                    }
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                    return networkResponse;
                });
            })
        );
    }
});
