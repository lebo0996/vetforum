self.addEventListener('install', (e) => {
    console.log('[Service Worker] Instalado exitosamente.');
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    console.log('[Service Worker] Activado.');
    return self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    // Dejamos pasar todas las peticiones al servidor para que el foro siempre esté en vivo
    e.respondWith(fetch(e.request).catch(() => {
        return new Response('Estás sin conexión a internet.');
    }));
});