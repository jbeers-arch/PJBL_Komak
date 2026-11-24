/**
 * PJBL Komak - Offline-First Service Worker (PWA)
 * Memungkinkan halaman Kasir POS & aset utama tetap bisa dibuka dan dipakai saat internet mati.
 */

const CACHE_NAME = 'pjbl-komak-offline-v1';

const CORE_ASSETS = [
    './manifest.json',
    './favicon.svg?v=4.0',
    './favicon.png?v=4.0',
    './apple-touch-icon.png?v=4.0',
    './assets/css/styles.css',
    './assets/js/pos-offline.js',
    './assets/js/komak-fx.js',
    './assets/js/komak-mascot.js',
    './assets/js/komak-mobile.js',
    './assets/js/komak-motion.js'
];

// CDN eksternal yang dicache saat online agar tampilan CSS/ikon tetap utuh saat offline
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
            for (const asset of CORE_ASSETS) {
                try {
                    await cache.add(asset);
                } catch (e) {
                    // Abaikan aset opsional jika gagal di-precache
                }
            }
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

// Terima pesan dari halaman untuk segera menyimpan halaman aktif ke cache
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CACHE_CURRENT_PAGE' && event.data.url) {
        event.waitUntil(
            caches.open(CACHE_NAME).then(async (cache) => {
                try {
                    const res = await fetch(event.data.url, { credentials: 'include' });
                    if (res && res.ok && !res.redirected) {
                        await cache.put(event.data.url, res.clone());
                        const cleanUrl = new URL(event.data.url);
                        cleanUrl.search = '';
                        await cache.put(cleanUrl.toString(), res.clone());
                    }
                } catch (e) {}
            })
        );
    }
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const isSameOrigin = url.origin === self.location.origin;
    const isAllowedCdn = CACHEABLE_CDN_HOSTS.includes(url.hostname);

    if (!isSameOrigin && !isAllowedCdn) return;

    // 1. Navigasi halaman HTML/PHP (Network-First, fallback ke Cache saat Offline)
    if (req.mode === 'navigate' || (isSameOrigin && url.pathname.endsWith('.php') && !url.pathname.includes('/api/'))) {
        event.respondWith(handlePageNavigation(req));
        return;
    }

    // 2. API GET (Network-First, fallback ke Cache JSON terakhir saat Offline)
    if (isSameOrigin && url.pathname.includes('/api/')) {
        event.respondWith(handleApiGet(req));
        return;
    }

    // 3. Aset statis lokal & CDN (Cache-First / Stale-While-Revalidate)
    event.respondWith(handleStaticAsset(req));
});

async function handlePageNavigation(req) {
    const cache = await caches.open(CACHE_NAME);
    const cleanUrl = new URL(req.url);
    cleanUrl.search = '';

    try {
        const networkRes = await fetch(req);
        // Jangan cache halaman redirect login jika sesi habis; hanya cache halaman yang benar-benar dimuat
        if (networkRes && networkRes.ok && !networkRes.redirected) {
            cache.put(req, networkRes.clone()).catch(() => {});
            cache.put(cleanUrl.toString(), networkRes.clone()).catch(() => {});
        }
        return networkRes;
    } catch (err) {
        // Offline: cari halaman persis di cache
        const exactMatch = await cache.match(req);
        if (exactMatch) return exactMatch;

        const pathMatch = await cache.match(cleanUrl.toString());
        if (pathMatch) return pathMatch;

        // Fallback otomatis ke Kasir POS yang sudah ter-cache
        const posUrl = new URL('./views/pos.php', self.registration.scope).toString();
        const posMatch = await cache.match(posUrl);
        if (posMatch) return posMatch;

        // Jika belum pernah membuka halaman saat online
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
    <p>Koneksi internet sedang terputus dan halaman ini belum pernah dibuka saat online. Buka halaman <strong>Kasir POS</strong> minimal sekali saat online agar selalu siap dipakai tanpa internet.</p>
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
            cache.put(req, networkRes.clone()).catch(() => {});
        }
        return networkRes;
    } catch (err) {
        const cached = await cache.match(req);
        if (cached) return cached;
        return new Response(
            JSON.stringify({ success: false, offline: true, message: 'Sedang offline, menggunakan data lokal.' }),
            { status: 200, headers: { 'Content-Type': 'application/json; charset=utf-8' } }
        );
    }
}

async function handleStaticAsset(req) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(req, { ignoreSearch: false });

    const fetchPromise = fetch(req)
        .then((networkRes) => {
            if (networkRes && (networkRes.ok || networkRes.type === 'opaque')) {
                cache.put(req, networkRes.clone()).catch(() => {});
            }
            return networkRes;
        })
        .catch(() => null);

    if (cached) {
        return cached;
    }

    const networkRes = await fetchPromise;
    if (networkRes) return networkRes;

    // Fallback tanpa query string untuk aset lokal seperti styles.css?v=...
    const cleanUrl = new URL(req.url);
    cleanUrl.search = '';
    const cleanCached = await cache.match(cleanUrl.toString());
    if (cleanCached) return cleanCached;

    return new Response('', { status: 504 });
}
