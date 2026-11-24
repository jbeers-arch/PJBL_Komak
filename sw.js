/**
 * PJBL Komak - Offline-First Service Worker (PWA v2)
 * Menyimpan halaman Kasir POS, aset lokal, dan CDN agar web tetap bisa dibuka penuh saat offline.
 */

const CACHE_NAME = 'pjbl-komak-offline-v2';

const CORE_ASSETS = [
    './manifest.json',
    './favicon.svg',
    './favicon.png',
    './apple-touch-icon.png',
    './assets/css/styles.css',
    './assets/js/pos-offline.js',
    './assets/js/komak-fx.js',
    './assets/js/komak-mascot.js',
    './assets/js/komak-mobile.js',
    './assets/js/komak-motion.js'
];

const CACHEABLE_CDN_HOSTS = [
    'cdn.tailwindcss.com',
    'cdnjs.cloudflare.com',
    'fonts.googleapis.com',
    'fonts.gstatic.com',
    'cdn.jsdelivr.net'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.all(
                CORE_ASSETS.map((asset) =>
                    cache.add(asset).catch(() => {})
                )
            );
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('pjbl-komak-offline-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const isSameOrigin = url.origin === self.location.origin;
    const isAllowedCdn = CACHEABLE_CDN_HOSTS.includes(url.hostname);

    if (!isSameOrigin && !isAllowedCdn) return;

    // 1. Navigasi halaman HTML/PHP (Network-First + Fallback ke Cache saat Offline)
    if (req.mode === 'navigate' || (isSameOrigin && url.pathname.endsWith('.php') && !url.pathname.includes('/api/'))) {
        event.respondWith(handlePageNavigation(req));
        return;
    }

    // 2. API GET (Network-First + Fallback ke Cache JSON terakhir saat Offline)
    if (isSameOrigin && url.pathname.includes('/api/')) {
        event.respondWith(handleApiGet(req));
        return;
    }

    // 3. Aset statis lokal & CDN (Cache-First + Update di background)
    event.respondWith(handleStaticAsset(req));
});

async function handlePageNavigation(req) {
    const cache = await caches.open(CACHE_NAME);
    const cleanUrl = new URL(req.url);
    cleanUrl.search = '';

    try {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 6000);
        const networkRes = await fetch(req, { signal: controller.signal });
        clearTimeout(timer);
        if (networkRes && networkRes.ok && !networkRes.redirected) {
            cache.put(req.url, networkRes.clone()).catch(() => {});
            cache.put(cleanUrl.toString(), networkRes.clone()).catch(() => {});
        }
        return networkRes;
    } catch (err) {
        // Offline: cari di Cache Storage
        const exactMatch = await cache.match(req.url);
        if (exactMatch) return exactMatch;

        const pathMatch = await cache.match(cleanUrl.toString());
        if (pathMatch) return pathMatch;

        const ignoreSearchMatch = await cache.match(req, { ignoreSearch: true });
        if (ignoreSearchMatch) return ignoreSearchMatch;

        // Fallback ke halaman Kasir POS yang tersimpan di cache
        const posUrl = new URL('./views/pos.php', self.registration.scope).toString();
        const posMatch = await cache.match(posUrl, { ignoreSearch: true });
        if (posMatch) return posMatch;

        return new Response(
            `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mode Offline - PJBL Komak</title>
<style>
  body { font-family: system-ui, -apple-system, sans-serif; background: #f8f9fe; color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
  .card { max-width: 420px; width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px; text-align: center; box-shadow: 0 10px 30px rgba(15,23,42,.06); }
  .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
  h1 { font-size: 18px; margin: 0 0 8px; }
  p { font-size: 13px; color: #475569; line-height: 1.55; margin: 0 0 20px; }
  .btns { display: flex; gap: 10px; justify-content: center; }
  a, button { padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; }
  .primary { background: #0100b8; color: #fff; }
  .secondary { background: #f1f5f9; color: #334155; }
</style>
</head>
<body>
  <div class="card">
    <div class="badge">MODE OFFLINE</div>
    <h1>Halaman Belum Tersimpan di Cache</h1>
    <p>Koneksi internet sedang mati dan halaman ini belum tersimpan di cache browser. Buka halaman <strong>Kasir POS</strong> sekali saat online hingga muncul tanda <strong>Siap Offline</strong>.</p>
    <div class="btns">
      <a class="primary" href="./views/pos.php">Buka Kasir POS</a>
      <button class="secondary" onclick="location.reload()">Coba Lagi</button>
    </div>
  </div>
</body>
</html>`,
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
    }
}

async function handleApiGet(req) {
    const cache = await caches.open(CACHE_NAME);
    try {
        const networkRes = await fetch(req);
        if (networkRes && networkRes.ok) {
            cache.put(req.url, networkRes.clone()).catch(() => {});
        }
        return networkRes;
    } catch (err) {
        const cached = await cache.match(req, { ignoreSearch: false }) || await cache.match(req, { ignoreSearch: true });
        if (cached) return cached;
        return new Response(
            JSON.stringify({ success: false, offline: true, message: 'Sedang offline, menggunakan data lokal.' }),
            { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } }
        );
    }
}

async function handleStaticAsset(req) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(req.url) || await cache.match(req, { ignoreSearch: true });

    const fetchPromise = fetch(req)
        .then((networkRes) => {
            if (networkRes && (networkRes.ok || networkRes.type === 'opaque')) {
                cache.put(req.url, networkRes.clone()).catch(() => {});
            }
            return networkRes;
        })
        .catch(() => null);

    if (cached) {
        return cached;
    }

    const networkRes = await fetchPromise;
    if (networkRes) return networkRes;

    return new Response('', { status: 504 });
}
