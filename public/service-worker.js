const CACHE_VERSION = 'voz-cifra-public-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const PRIVATE_PREFIXES = ['/login', '/logout', '/igreja', '/admin', '/membro', '/coordenacao', '/contexto'];

const isPrivatePath = (pathname) => PRIVATE_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));
const isStatusPath = (pathname) => pathname.endsWith('/status') || pathname.includes('/status/');

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(['/logo/final.png', '/site.webmanifest', '/offline.html']))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key.startsWith('voz-cifra-public-') && ![STATIC_CACHE, PAGE_CACHE].includes(key))
                .map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || isPrivatePath(url.pathname) || isStatusPath(url.pathname)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const response = await fetch(request);
                if (response.ok && (response.headers.get('content-type') || '').includes('text/html')) {
                    const cache = await caches.open(PAGE_CACHE);
                    await cache.put(request, response.clone());
                }
                return response;
            } catch (error) {
                const cache = await caches.open(PAGE_CACHE);
                return (await cache.match(request))
                    || (await cache.match(new Request(`${url.origin}${url.pathname}`)))
                    || (await caches.match('/offline.html'))
                    || Response.error();
            }
        })());
        return;
    }

    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        event.respondWith((async () => {
            const cached = await caches.match(request);
            if (cached) return cached;

            const response = await fetch(request);
            if (response.ok) {
                const cache = await caches.open(STATIC_CACHE);
                await cache.put(request, response.clone());
            }
            return response;
        })());
    }
});
