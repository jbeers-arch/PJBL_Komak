/**
 * Offline-First POS Engine (IndexedDB Local-First Storage & Auto-Sync)
 * Proyek: PJBL_Komak
 */

class PosOfflineEngine {
    constructor() {
        this.dbName = 'PJBL_Komak_POS_DB';
        this.dbVersion = 1;
        this.db = null;
        this.isForcedOffline = localStorage.getItem('pos_force_offline') === 'true';
        this.isSyncing = false;
        this.init();
    }

    async init() {
        this.registerServiceWorker();
        await this.openDatabase();
        this.setupNetworkListeners();
        this.updateNetworkBadge();
        
        // Auto sync interval setiap 15 detik jika online
        setInterval(() => {
            if (this.isOnline() && !this.isSyncing) {
                this.syncPendingTransactions();
            }
        }, 15000);
    }

    registerServiceWorker() {
        const badgeText = document.getElementById('pwa-offline-ready-text');
        const setStatus = (text, cls) => {
            if (!badgeText) return;
            badgeText.innerText = text;
            badgeText.className = cls;
        };

        if (!('serviceWorker' in navigator)) {
            setStatus('Browser tidak mendukung', 'text-amber-600');
            return;
        }

        const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (!isSecure) {
            setStatus('Wajib buka via HTTPS', 'text-rose-600 font-bold');
            return;
        }

        const runSetup = async () => {
            try {
                const swUrl = new URL('../sw.js', window.location.href).href;
                const scopeUrl = new URL('../', window.location.href).href;
                const reg = await navigator.serviceWorker.register(swUrl, { scope: scopeUrl });
                reg.update().catch(() => {});

                // Langsung simpan halaman aktif, Kasir POS, serta aset CDN ke Cache Storage dari konteks halaman
                if ('caches' in window && navigator.onLine) {
                    const cache = await caches.open('pjbl-komak-offline-v2');

                    // 1. Simpan HTML bersih halaman aktif ke Cache Storage
                    const cleanCurrentUrl = window.location.href.split('?')[0];
                    let pageCached = false;
                    try {
                        const freshRes = await fetch(window.location.href, { credentials: 'include' });
                        if (freshRes && freshRes.ok && !freshRes.redirected) {
                            await cache.put(window.location.href, freshRes.clone());
                            await cache.put(cleanCurrentUrl, freshRes.clone());
                            pageCached = true;
                        }
                    } catch (e) {}

                    if (!pageCached) {
                        const htmlDoc = '<!DOCTYPE html>\n' + document.documentElement.outerHTML;
                        const pageRes = new Response(htmlDoc, {
                            status: 200,
                            headers: { 'Content-Type': 'text/html; charset=utf-8' }
                        });
                        await cache.put(window.location.href, pageRes.clone()).catch(() => {});
                        await cache.put(cleanCurrentUrl, pageRes.clone()).catch(() => {});
                    }

                    // 2. Pastikan views/pos.php juga selalu ter-cache meski sedang membuka dashboard/menu lain
                    const posUrl = new URL('../views/pos.php', window.location.href).href;
                    if (!cleanCurrentUrl.endsWith('/views/pos.php')) {
                        fetch(posUrl, { credentials: 'include' })
                            .then((r) => {
                                if (r && r.ok && !r.redirected) {
                                    cache.put(posUrl, r.clone()).catch(() => {});
                                }
                            })
                            .catch(() => {});
                    }

                    // 3. Simpan Tailwind & FontAwesome CDN agar tampilan tidak rusak saat offline
                    const cdnUrls = [
                        'https://cdn.tailwindcss.com?plugins=forms,container-queries',
                        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
                    ];
                    cdnUrls.forEach((u) => {
                        fetch(u, { mode: 'no-cors' })
                            .then((r) => {
                                if (r) cache.put(u, r.clone()).catch(() => {});
                            })
                            .catch(() => {});
                    });
                }

                await navigator.serviceWorker.ready;
                setStatus('Siap Offline ✓', 'text-emerald-600 dark:text-emerald-400 font-bold');
            } catch (err) {
                console.warn('Gagal mendaftarkan Service Worker offline:', err);
                setStatus('Gagal aktif', 'text-rose-500 font-bold');
            }
        };

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            runSetup();
        } else {
            document.addEventListener('DOMContentLoaded', runSetup, { once: true });
        }
    }

    openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Store master produk lokal
                if (!db.objectStoreNames.contains('products')) {
                    db.createObjectStore('products', { keyPath: 'id' });
                }

                // Store transaksi penjualan lokal (Offline Queue)
                if (!db.objectStoreNames.contains('offline_transactions')) {
                    const trxStore = db.createObjectStore('offline_transactions', { keyPath: 'transaction_no' });
                    trxStore.createIndex('sync_status', 'sync_status', { unique: false });
                    trxStore.createIndex('created_at', 'created_at', { unique: false });
                }
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                resolve(this.db);
            };

            request.onerror = (event) => {
                console.error('IndexedDB Error:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    isOnline() {
        return navigator.onLine && !this.isForcedOffline;
    }

    toggleForceOffline(force) {
        this.isForcedOffline = force;
        localStorage.setItem('pos_force_offline', force ? 'true' : 'false');
        this.updateNetworkBadge();
        if (this.isOnline()) {
            this.syncPendingTransactions();
        }
    }

    setupNetworkListeners() {
        window.addEventListener('online', () => {
            this.updateNetworkBadge();
            this.syncPendingTransactions();
        });

        window.addEventListener('offline', () => {
            this.updateNetworkBadge();
        });
    }

    updateNetworkBadge() {
        const badge = document.getElementById('connection-status-badge');
        const text = document.getElementById('connection-status-text');
        const toggle = document.getElementById('toggle-force-offline');

        if (toggle) {
            toggle.checked = this.isForcedOffline;
        }

        if (badge && text) {
            if (this.isOnline()) {
                badge.className = 'inline-flex items-center px-2 sm:px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40 shadow-xs transition-all shrink-0';
                badge.innerHTML = `<span class="relative flex h-2 w-2 sm:mr-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span><span id="connection-status-text" class="hidden sm:inline tracking-wide">ONLINE</span>`;
                badge.title = 'Online (Terhubung Pusat)';
            } else {
                badge.className = 'inline-flex items-center px-2 sm:px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40 shadow-xs transition-all shrink-0';
                badge.innerHTML = `<span class="relative flex h-2 w-2 sm:mr-1.5"><span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span></span><span id="connection-status-text" class="hidden sm:inline tracking-wide">${this.isForcedOffline ? 'OFFLINE' : 'OFFLINE'}</span>`;
                badge.title = this.isForcedOffline ? 'Mode Simulasi Offline' : 'Mode Offline Lokal';
            }
        }
        this.refreshPendingCounter();
    }

    // Cache products from server to local IndexedDB
    async cacheProducts(products) {
        if (!this.db) await this.openDatabase();
        const tx = this.db.transaction('products', 'readwrite');
        const store = tx.objectStore('products');
        store.clear();
        for (const p of products) {
            store.put(p);
        }
        return new Promise((resolve) => tx.oncomplete = () => resolve());
    }

    // Get products from local IndexedDB
    async getLocalProducts() {
        if (!this.db) await this.openDatabase();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('products', 'readonly');
            const store = tx.objectStore('products');
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    // Save transaction locally in IndexedDB
    async saveTransactionLocally(trxData) {
        if (!this.db) await this.openDatabase();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline_transactions', 'readwrite');
            const store = tx.objectStore('offline_transactions');
            
            trxData.sync_status = 'pending';
            trxData.created_at = new Date().toISOString();

            const req = store.put(trxData);
            req.onsuccess = () => {
                this.refreshPendingCounter();
                resolve(trxData);
            };
            req.onerror = () => reject(req.error);
        });
    }

    // Get all pending transactions
    async getPendingTransactions() {
        if (!this.db) await this.openDatabase();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline_transactions', 'readonly');
            const store = tx.objectStore('offline_transactions');
            const req = store.getAll();
            req.onsuccess = () => {
                const pendings = req.result.filter(t => t.sync_status === 'pending');
                resolve(pendings);
            };
            req.onerror = () => reject(req.error);
        });
    }

    // Update status to synced
    async markAsSynced(trxNos) {
        if (!this.db) await this.openDatabase();
        const tx = this.db.transaction('offline_transactions', 'readwrite');
        const store = tx.objectStore('offline_transactions');
        for (const no of trxNos) {
            const getReq = store.get(no);
            getReq.onsuccess = () => {
                if (getReq.result) {
                    const item = getReq.result;
                    item.sync_status = 'synced';
                    item.synced_at = new Date().toISOString();
                    store.put(item);
                }
            };
        }
        return new Promise((resolve) => {
            tx.oncomplete = () => {
                this.refreshPendingCounter();
                resolve();
            };
        });
    }

    async refreshPendingCounter() {
        const counterEl = document.getElementById('pending-sync-count');
        const bannerEl = document.getElementById('pending-sync-banner');
        if (!counterEl) return;

        const pendings = await this.getPendingTransactions();
        counterEl.innerText = pendings.length;

        if (bannerEl) {
            if (pendings.length > 0) {
                bannerEl.classList.remove('hidden');
            } else {
                bannerEl.classList.add('hidden');
            }
        }
    }

    // SINKRONISASI OTOMATIS KE SERVER PUSAT
    async syncPendingTransactions() {
        if (!this.isOnline() || this.isSyncing) return;

        const pendings = await this.getPendingTransactions();
        if (pendings.length === 0) return;

        this.isSyncing = true;
        const syncStatusEl = document.getElementById('sync-status-indicator');
        if (syncStatusEl) syncStatusEl.innerHTML = '<span class="text-xs text-blue-600 animate-spin">⌛ Menyinkronkan...</span>';

        try {
            const response = await fetch('../api/pos_sync.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ transactions: pendings })
            });

            const result = await response.json();
            if (result.success && result.synced_transaction_nos) {
                await this.markAsSynced(result.synced_transaction_nos);
                if (typeof showToast === 'function') {
                    showToast(`Sinkronisasi Sukses: ${result.synced_transaction_nos.length} transaksi di-upload & otomatis dijurnal di SIA Pusat!`, 'success');
                }
            }
        } catch (err) {
            console.warn('Gagal sync ke server pusat (mungkin offline):', err);
        } finally {
            this.isSyncing = false;
            if (syncStatusEl) syncStatusEl.innerHTML = '';
            this.refreshPendingCounter();
        }
    }
}

// Global instance
window.posEngine = new PosOfflineEngine();
