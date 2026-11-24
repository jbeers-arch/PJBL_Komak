<?php
/**
 * Sidebar Navigation (Institutional Academic & Fintech Theme)
 * Disesuaikan otomatis dengan role (Akuntan vs Kasir vs Superadmin)
 * Proyek: PJBL_Komak
 */

$role = $_SESSION['user']['role'] ?? 'kasir';
$currentScript = basename($_SERVER['PHP_SELF']);
$userName = $_SESSION['user']['name'] ?? 'User';
$userInitials = strtoupper(substr($userName, 0, 2));
$branchName = $_SESSION['user']['branch_name'] ?? 'Pusat';
?>

<!-- Mobile Sidebar Backdrop -->
<div id="mobile-sidebar-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

<aside id="main-sidebar" class="fixed md:static inset-y-0 left-0 top-14 sm:top-16 md:top-auto md:inset-auto z-50 md:z-20 w-64 h-[calc(100dvh-3.5rem)] sm:h-[calc(100dvh-4rem)] md:h-full bg-white dark:bg-[#0d1322] border-r border-slate-200/80 dark:border-white/[0.08] flex flex-col justify-between py-4 px-2 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-lg md:shadow-none shrink-0 overflow-y-auto">
    <div class="space-y-4">
        <!-- User Profile Card -->
        <div class="mx-2 p-3 rounded-2xl bg-slate-50/90 dark:bg-white/[0.04] border border-slate-200/80 dark:border-white/[0.07] flex items-center justify-between overflow-hidden shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#000079] to-[#0100b8] border border-blue-200/40 flex items-center justify-center text-white text-xs font-bold uppercase shrink-0 shadow-xs">
                    <span><?= htmlspecialchars($userInitials) ?></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-slate-800 dark:text-slate-200 text-xs font-bold truncate leading-tight"><?= htmlspecialchars($userName) ?></div>
                    <div class="text-slate-500 dark:text-slate-400 text-[11px] truncate leading-tight mt-0.5 font-medium">
                        <?= ucfirst(htmlspecialchars($role)) ?> &bull; <?= htmlspecialchars($branchName) ?>
                    </div>
                </div>
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 ml-1.5 shadow-[0_0_8px_rgba(16,185,129,0.7)]" title="Online"></span>
        </div>

        <?php if ($role === 'akuntan' || $role === 'superadmin'): ?>
            <!-- Group 1: Pusat & Otomasi -->
            <div>
                <div class="nav-section">Pusat &amp; Otomasi</div>
                <nav class="space-y-1">
                    <a href="dashboard.php" class="nav-item <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="scan-faktur.php" class="nav-item <?= $currentScript === 'scan-faktur.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                        <span>Scan Faktur</span>
                    </a>

                    <a href="audit-log.php" class="nav-item <?= $currentScript === 'audit-log.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Jejak Audit</span>
                    </a>
                </nav>
            </div>

            <!-- Group 2: Pembukuan & Akuntansi -->
            <div>
                <div class="nav-section">Pembukuan &amp; Jurnal</div>
                <nav class="space-y-1">
                    <a href="coa.php" class="nav-item <?= $currentScript === 'coa.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Bagan Akun (COA)</span>
                    </a>

                    <a href="jurnal.php" class="nav-item <?= $currentScript === 'jurnal.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-teal-500 dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Jurnal Umum</span>
                    </a>

                    <a href="jurnal-penyesuaian.php" class="nav-item <?= $currentScript === 'jurnal-penyesuaian.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Jurnal Penyesuaian</span>
                    </a>

                    <a href="buku-besar.php" class="nav-item <?= $currentScript === 'buku-besar.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        <span>Buku Besar</span>
                    </a>

                    <a href="laporan.php" class="nav-item <?= $currentScript === 'laporan.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Laporan Keuangan</span>
                    </a>

                    <a href="tutup-buku.php" class="nav-item <?= $currentScript === 'tutup-buku.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Tutup Buku Periodik</span>
                    </a>
                </nav>
            </div>

            <!-- Group 3: Master Data & Cabang -->
            <div>
                <div class="nav-section">Layanan, Bahan &amp; Cabang</div>
                <nav class="space-y-1">
                    <a href="produk.php" class="nav-item <?= $currentScript === 'produk.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span class="truncate">Katalog Layanan &amp; Bahan</span>
                    </a>

                    <a href="transfer-stok.php" class="nav-item <?= $currentScript === 'transfer-stok.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Stok &amp; Transfer Persediaan</span>
                    </a>

                    <a href="cabang.php" class="nav-item <?= $currentScript === 'cabang.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-cyan-500 dark:text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Cabang &amp; Kasir</span>
                    </a>

                    <a href="supplier.php" class="nav-item <?= $currentScript === 'supplier.php' ? 'active' : '' ?>">
                        <svg class="w-4 h-4 shrink-0 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Master Supplier</span>
                    </a>
                </nav>
            </div>
        <?php endif; ?>

        <!-- Group POS Kasir (Akses Kasir & Akuntan) -->
        <div>
            <div class="nav-section">Kasir &amp; Penerimaan Cucian</div>
            <nav class="space-y-1">
                <a href="pos.php" class="nav-item <?= $currentScript === 'pos.php' ? 'active' : '' ?>">
                    <svg class="w-4 h-4 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kasir POS Laundry</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Offline Simulation Card at Sidebar Bottom -->
    <div class="pt-3 border-t border-slate-100 dark:border-white/[0.08]">
        <div class="p-3 bg-slate-50/90 dark:bg-white/[0.03] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
            <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Simulasi Offline</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggle-force-offline" onchange="window.posEngine?.toggleForceOffline(this.checked)" class="sr-only peer">
                    <div class="w-8 h-4.5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-3.5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Uji kasir laundry saat internet padam. Data nota tersimpan di browser &amp; sinkron otomatis saat online.</p>
        </div>
    </div>
</aside>

<script>
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const backdrop = document.getElementById('mobile-sidebar-backdrop');
        if (!sidebar || !backdrop) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }
</script>
