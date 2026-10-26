<?php
$pageTitle = 'Laporan Keuangan';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Laporan Keuangan Terpadu</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Laporan Laba Rugi, Posisi Keuangan (Neraca), Arus Kas, Perubahan Ekuitas, dan Analisis Rasio.</p>
        </div>
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
            <button onclick="exportActiveReportToCSV()" class="w-full sm:w-auto justify-center inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </button>
            <button onclick="window.print()" class="w-full sm:w-auto justify-center inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Segmented Tab Selector (Pills) -->
    <div class="bg-slate-200/60 dark:bg-white/[0.06] p-1.5 rounded-2xl flex items-center space-x-1.5 overflow-x-auto max-w-full no-scrollbar touch-scroll">
        <button onclick="switchTab('income')" id="tab-btn-income" class="shrink-0 py-2 px-3.5 rounded-xl font-bold text-xs bg-white text-indigo-700 shadow-xs transition-all whitespace-nowrap text-center">
            Laba Rugi
        </button>
        <button onclick="switchTab('balance')" id="tab-btn-balance" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Posisi Keuangan (Neraca)
        </button>
        <button onclick="switchTab('cashflow')" id="tab-btn-cashflow" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Arus Kas
        </button>
        <button onclick="switchTab('equity')" id="tab-btn-equity" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Perubahan Ekuitas
        </button>
        <button onclick="switchTab('trialbalance')" id="tab-btn-trialbalance" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Neraca Saldo (Trial Balance)
        </button>
        <button onclick="switchTab('branchcompare')" id="tab-btn-branchcompare" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Komparasi Cabang
        </button>
        <button onclick="switchTab('calk')" id="tab-btn-calk" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            CALK (SAK-EMKM)
        </button>
        <button onclick="switchTab('postclosing')" id="tab-btn-postclosing" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Neraca Penutupan
        </button>
        <button onclick="switchTab('ratios')" id="tab-btn-ratios" class="shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center">
            Analisis Rasio
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#11182c] p-4 rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col md:flex-row md:items-end justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center gap-3 w-full md:w-auto">
            <div id="filter-date-range" class="grid grid-cols-2 gap-2 sm:col-span-2 lg:flex lg:items-center">
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" id="rep-start-date" value="<?= date('Y-m-01') ?>" onchange="reloadActiveReport()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" id="rep-end-date" value="<?= date('Y-m-d') ?>" onchange="reloadActiveReport()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>
            </div>

            <div id="filter-single-date" class="hidden w-full">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Posisi Per Tanggal</label>
                <input type="date" id="rep-as-of-date" value="<?= date('Y-m-d') ?>" onchange="reloadActiveReport()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>

            <div class="w-full sm:col-span-2 lg:col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Konsolidasi Cabang</label>
                <select id="rep-branch-select" onchange="reloadActiveReport()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Cabang (Konsolidasi)</option>
                    <?php foreach ($activeBranches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="w-full md:w-auto">
            <button onclick="reloadActiveReport()" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs justify-center flex items-center">
                Perbarui Laporan
            </button>
        </div>
    </div>

    <!-- TAB 1: LAPORAN LABA RUGI -->
    <div id="tab-content-income" class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200">
            <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Laporan Laba Rugi</h3>
            <p class="text-xs text-slate-500 mt-1 font-medium" id="income-period-text">Periode</p>
        </div>

        <div class="space-y-5 text-xs">
            <!-- 1. Pendapatan -->
            <div>
                <h4 class="font-extrabold text-slate-900 uppercase tracking-wider mb-2 text-[11px] text-indigo-700">1. Pendapatan Usaha</h4>
                <div id="income-revenue-list" class="space-y-1.5 pl-4"></div>
                <div class="flex items-center justify-between gap-3 font-bold pt-2 mt-2 border-t border-slate-200 text-slate-900 text-xs px-2">
                    <span>Total Pendapatan Usaha</span>
                    <span id="income-total-revenue" class="font-mono font-bold shrink-0 text-right">Rp 0</span>
                </div>
            </div>

            <!-- 2. HPP -->
            <div class="pt-2">
                <h4 class="font-extrabold text-slate-900 uppercase tracking-wider mb-2 text-[11px] text-slate-700">2. Beban Pokok Penjualan (HPP)</h4>
                <div id="income-cogs-list" class="space-y-1.5 pl-4"></div>
                <div class="flex items-center justify-between gap-3 font-bold pt-2 mt-2 border-t border-slate-200 text-slate-900 text-xs px-2">
                    <span>Total Beban Pokok Penjualan</span>
                    <span id="income-total-cogs" class="font-mono font-bold shrink-0 text-right">Rp 0</span>
                </div>
            </div>

            <!-- Laba Kotor -->
            <div class="p-3.5 bg-blue-50/80 rounded-2xl border border-blue-100 flex items-center justify-between gap-3 font-black text-blue-900 text-xs sm:text-sm">
                <span>LABA KOTOR (GROSS PROFIT)</span>
                <span id="income-gross-profit" class="font-mono shrink-0 text-right">Rp 0</span>
            </div>

            <!-- 3. Beban Operasional -->
            <div class="pt-2">
                <h4 class="font-extrabold text-slate-900 uppercase tracking-wider mb-2 text-[11px] text-rose-700">3. Beban Operasional & Administrasi</h4>
                <div id="income-expense-list" class="space-y-1.5 pl-4"></div>
                <div class="flex items-center justify-between gap-3 font-bold pt-2 mt-2 border-t border-slate-200 text-slate-900 text-xs px-2">
                    <span>Total Beban Operasional</span>
                    <span id="income-total-expenses" class="font-mono font-bold shrink-0 text-right">Rp 0</span>
                </div>
            </div>

            <!-- Laba Bersih -->
            <div class="p-3.5 sm:p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200/80 flex items-center justify-between gap-3 font-black text-emerald-900 text-xs sm:text-base">
                <span class="leading-tight">LABA BERSIH PERIODE BERJALAN (NET PROFIT)</span>
                <span id="income-net-profit" class="font-mono border-b-4 border-double border-emerald-800 pb-0.5 shrink-0 text-right">Rp 0</span>
            </div>
        </div>
    </div>

    <!-- TAB 2: LAPORAN NERACA -->
    <div id="tab-content-balance" class="hidden bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-5xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200">
            <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Laporan Posisi Keuangan (Neraca)</h3>
            <p class="text-xs text-slate-500 mt-1 font-medium" id="balance-as-of-text">Per Tanggal</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs">
            <!-- Sisi Kiri: ASET -->
            <div class="space-y-4">
                <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-2xl font-bold text-indigo-900 uppercase tracking-wider text-[11px]">
                    ASET / AKTIVA
                </div>
                <div id="balance-assets-list" class="space-y-2 pl-2"></div>
                <div class="p-3.5 bg-indigo-50/90 rounded-2xl border border-indigo-200 flex justify-between font-black text-indigo-900 text-sm">
                    <span>TOTAL ASET</span>
                    <span id="balance-total-assets" class="font-mono border-b-2 border-indigo-900">Rp 0</span>
                </div>
            </div>

            <!-- Sisi Kanan: LIABILITAS & EKUITAS -->
            <div class="space-y-4">
                <div>
                    <div class="p-3 bg-amber-50/70 border border-amber-100 rounded-2xl font-bold text-amber-900 uppercase tracking-wider text-[11px] mb-2">
                        LIABILITAS / KEWAJIBAN
                    </div>
                    <div id="balance-liabilities-list" class="space-y-2 pl-2"></div>
                    <div class="flex justify-between font-bold pt-2 mt-2 border-t border-slate-200 text-slate-800 px-2">
                        <span>Total Liabilitas</span>
                        <span id="balance-total-liabilities" class="font-mono font-bold">Rp 0</span>
                    </div>
                </div>

                <div class="pt-3">
                    <div class="p-3 bg-purple-50/70 border border-purple-100 rounded-2xl font-bold text-purple-900 uppercase tracking-wider text-[11px] mb-2">
                        EKUITAS / MODAL
                    </div>
                    <div id="balance-equities-list" class="space-y-2 pl-2"></div>
                    <div class="flex justify-between font-bold pt-2 mt-2 border-t border-slate-200 text-slate-800 px-2">
                        <span>Total Ekuitas</span>
                        <span id="balance-total-equities" class="font-mono font-bold">Rp 0</span>
                    </div>
                </div>

                <div class="p-3.5 bg-indigo-50/90 rounded-2xl border border-indigo-200 flex justify-between font-black text-indigo-900 text-sm">
                    <span>TOTAL LIABILITAS & EKUITAS</span>
                    <span id="balance-total-passiva" class="font-mono border-b-2 border-indigo-900">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Neraca Balance Confirmation Badge -->
        <div id="balance-check-container" class="p-3 rounded-2xl text-center text-xs font-bold transition-all"></div>
    </div>

    <!-- TAB 3: LAPORAN ARUS KAS (SAK EMKM METODE LANGSUNG) -->
    <div id="tab-content-cashflow" class="hidden bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200 dark:border-white/10">
            <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Laporan Arus Kas (Statement of Cash Flows)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium" id="cashflow-period-text">Periode</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800 font-sans">
                Standar SAK EMKM &bull; Metode Langsung (Direct Method)
            </span>
        </div>

        <div class="space-y-5 text-xs">
            <!-- 1. Aktivitas Operasi -->
            <div>
                <div class="p-3 bg-blue-50/80 dark:bg-blue-950/40 rounded-2xl font-bold text-blue-900 dark:text-blue-300 uppercase tracking-wider mb-2 text-[11px] flex justify-between items-center">
                    <span>1. Arus Kas dari Aktivitas Operasi</span>
                    <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 lowercase">operating activities</span>
                </div>
                <div class="space-y-2 pl-4">
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Penerimaan Kas dari Pelanggan (Penjualan Kasir POS & Jasa Laundry)</span>
                        <span id="cf-sales" class="font-bold text-slate-800 dark:text-slate-200 font-mono">Rp 0</span>
                    </div>
                    <div id="cf-row-other-in" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Penerimaan Kas Operasional Lain-lain</span>
                        <span id="cf-other-in" class="font-semibold text-slate-800 dark:text-slate-200 font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Kas kepada Pemasok & Pembelian Persediaan Bahan Cuci</span>
                        <span id="cf-suppliers" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                    <div id="cf-row-salaries" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Kas untuk Beban Gaji Karyawan & Kasir</span>
                        <span id="cf-salaries" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                    <div id="cf-row-utilities" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Kas untuk Beban Utilitas (Listrik, Air Bersih & Gas Boiler)</span>
                        <span id="cf-utilities" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                    <div id="cf-row-rent" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Kas untuk Beban Sewa Tempat Usaha</span>
                        <span id="cf-rent" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Kas untuk Beban Operasional & Pemeliharaan Lainnya</span>
                        <span id="cf-operations" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                </div>
                <div class="flex justify-between font-bold pt-2.5 mt-2 border-t border-slate-200 dark:border-white/10 text-slate-900 dark:text-white px-4">
                    <span>Arus Kas Bersih Dihasilkan dari (Digunakan untuk) Aktivitas Operasi</span>
                    <span id="cf-net-operating" class="font-mono font-black text-blue-900 dark:text-blue-300 text-sm">Rp 0</span>
                </div>
            </div>

            <!-- 2. Aktivitas Investasi -->
            <div>
                <div class="p-3 bg-purple-50/80 dark:bg-purple-950/40 rounded-2xl font-bold text-purple-900 dark:text-purple-300 uppercase tracking-wider mb-2 text-[11px] flex justify-between items-center">
                    <span>2. Arus Kas dari Aktivitas Investasi</span>
                    <span class="text-[10px] font-semibold text-purple-600 dark:text-purple-400 lowercase">investing activities</span>
                </div>
                <div class="space-y-2 pl-4">
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Perolehan / Pembelian Peralatan Toko, Mesin Kasir & Mesin Cuci</span>
                        <span id="cf-equipment" class="font-semibold text-purple-800 dark:text-purple-300 font-mono">(Rp 0)</span>
                    </div>
                    <div id="cf-row-asset-sales" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Hasil Penjualan / Pelepasan Aset Tetap</span>
                        <span id="cf-asset-sales" class="font-semibold text-purple-800 dark:text-purple-300 font-mono">Rp 0</span>
                    </div>
                </div>
                <div class="flex justify-between font-bold pt-2.5 mt-2 border-t border-slate-200 dark:border-white/10 text-slate-900 dark:text-white px-4">
                    <span>Arus Kas Bersih Dihasilkan dari (Digunakan untuk) Aktivitas Investasi</span>
                    <span id="cf-net-investing" class="font-mono font-black text-purple-900 dark:text-purple-300 text-sm">Rp 0</span>
                </div>
            </div>

            <!-- 3. Aktivitas Pendanaan -->
            <div>
                <div class="p-3 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-2xl font-bold text-emerald-900 dark:text-emerald-300 uppercase tracking-wider mb-2 text-[11px] flex justify-between items-center">
                    <span>3. Arus Kas dari Aktivitas Pendanaan</span>
                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 lowercase">financing activities</span>
                </div>
                <div class="space-y-2 pl-4">
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Penerimaan Setoran Modal Awal / Tambahan Pemilik</span>
                        <span id="cf-capital" class="font-bold text-emerald-800 dark:text-emerald-300 font-mono">Rp 0</span>
                    </div>
                    <div id="cf-row-loans-in" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Penerimaan Pinjaman Bank / Lembaga Keuangan Jangka Panjang</span>
                        <span id="cf-loans" class="font-semibold text-emerald-800 dark:text-emerald-300 font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pengambilan Prive oleh Pemilik</span>
                        <span id="cf-drawings" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                    <div id="cf-row-loans-out" class="flex justify-between py-1 border-b border-slate-50 dark:border-white/5">
                        <span class="text-slate-700 dark:text-slate-300">Pembayaran Pokok Pinjaman Bank</span>
                        <span id="cf-loan-repayments" class="font-semibold text-rose-700 dark:text-rose-400 font-mono">(Rp 0)</span>
                    </div>
                </div>
                <div class="flex justify-between font-bold pt-2.5 mt-2 border-t border-slate-200 dark:border-white/10 text-slate-900 dark:text-white px-4">
                    <span>Arus Kas Bersih Dihasilkan dari (Digunakan untuk) Aktivitas Pendanaan</span>
                    <span id="cf-net-financing" class="font-mono font-black text-emerald-900 dark:text-emerald-300 text-sm">Rp 0</span>
                </div>
            </div>

            <!-- 4. Ringkasan Kenaikan Kas & Rekonsiliasi Neraca -->
            <div class="p-5 bg-slate-50/90 dark:bg-slate-800/50 rounded-3xl border border-slate-200/90 dark:border-slate-700 space-y-3">
                <div class="flex justify-between text-slate-700 dark:text-slate-300 text-xs">
                    <span class="font-bold">Kenaikan (Penurunan) Bersih Kas & Bank</span>
                    <span id="cf-net-change" class="font-black font-mono text-sm">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-600 dark:text-slate-400 text-xs">
                    <span>Saldo Kas & Bank pada Awal Periode</span>
                    <span id="cf-beginning" class="font-bold font-mono">Rp 0</span>
                </div>
                <div class="flex justify-between pt-2.5 border-t border-slate-300 dark:border-slate-700 font-black text-slate-900 dark:text-white text-sm">
                    <span>Saldo Kas & Bank pada Akhir Periode</span>
                    <span id="cf-ending" class="text-emerald-700 dark:text-emerald-400 font-mono text-base font-black border-b-2 border-emerald-600">Rp 0</span>
                </div>
                <div class="flex justify-between pt-1 text-slate-500 dark:text-slate-400 text-[11px]">
                    <span>Saldo Kas & Bank di Laporan Posisi Keuangan (Neraca)</span>
                    <span id="cf-neraca-cash" class="font-bold font-mono text-slate-800 dark:text-slate-200">Rp 0</span>
                </div>
            </div>

            <!-- Badge Validasi Rekonsiliasi SAK -->
            <div id="cf-reconciliation-badge" class="p-3.5 rounded-2xl text-center text-xs font-bold transition-all"></div>
        </div>
    </div>

    <!-- TAB 4: LAPORAN PERUBAHAN EKUITAS -->
    <div id="tab-content-equity" class="hidden bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200">
            <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Laporan Perubahan Ekuitas</h3>
            <p class="text-xs text-slate-500 mt-1 font-medium" id="equity-period-text">Periode</p>
        </div>

        <div class="space-y-4 text-xs">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 text-slate-700">
                    <span class="font-bold text-slate-900 text-xs sm:text-sm">Saldo Modal Pemilik pada Awal Periode</span>
                    <span id="eq-beginning" class="font-mono font-bold text-slate-900 text-xs sm:text-sm shrink-0 text-right">Rp 0</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 text-slate-700 pl-2 sm:pl-4">
                    <span>(+) Tambahan Setoran Modal</span>
                    <span id="eq-additional" class="font-mono font-semibold text-slate-800 shrink-0 text-right">Rp 0</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 text-slate-700 pl-2 sm:pl-4">
                    <span>(+) Laba Bersih Periode Berjalan</span>
                    <span id="eq-net-income" class="font-mono font-semibold text-emerald-700 shrink-0 text-right">Rp 0</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 text-slate-700 pl-2 sm:pl-4">
                    <span>(-) Pengambilan Prive Pemilik</span>
                    <span id="eq-drawings" class="font-mono font-semibold text-rose-700 shrink-0 text-right">(Rp 0)</span>
                </div>
            </div>

            <div class="p-3.5 sm:p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between gap-3 font-extrabold text-emerald-900 text-xs sm:text-base">
                <span class="leading-tight">SALDO MODAL PEMILIK PADA AKHIR PERIODE</span>
                <span id="eq-ending" class="font-mono border-b-4 border-double border-emerald-800 pb-0.5 shrink-0 text-right">Rp 0</span>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl text-slate-500 text-[11px] leading-relaxed border border-slate-200/60">
                <p><strong>Catatan Akuntansi:</strong> Saldo modal akhir periode ini secara otomatis tersinkronisasi dan seimbang dengan pos Ekuitas pada Laporan Posisi Keuangan (Neraca) konsolidasi.</p>
            </div>
        </div>
    </div>

    <!-- TAB 5: NERACA SALDO SETELAH PENUTUPAN (POST-CLOSING TRIAL BALANCE) -->
    <div id="tab-content-postclosing" class="hidden bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200">
            <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Neraca Saldo Setelah Penutupan</h3>
            <p class="text-xs text-slate-500 mt-1 font-medium" id="postclosing-as-of-text">Per Tanggal</p>
        </div>

        <div class="p-3.5 bg-indigo-50/70 rounded-2xl border border-indigo-200/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">✓</div>
                <div>
                    <h5 class="font-bold text-indigo-950">Validasi Siklus Penutupan Buku (SAK-EMKM)</h5>
                    <p class="text-[11px] text-indigo-700">Hanya memuat akun riil (Aset, Liabilitas, Ekuitas). Seluruh akun nominal (Pendapatan & Beban) bersaldo Rp 0.</p>
                </div>
            </div>
            <div id="postclosing-proof-badge" class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-extrabold text-[11px] border border-emerald-300">
                Akun Nominal Nihil (Valid)
            </div>
        </div>

        <!-- Tabel Neraca Saldo Setelah Penutupan -->
        <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Nama Akun Riil</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3 text-right">Debit (Rp)</th>
                        <th class="py-2.5 px-3 text-right">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody id="postclosing-tbody" class="divide-y divide-slate-100 font-mono">
                    <!-- Populated via JS -->
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                    <tr>
                        <td colspan="3" class="py-3 px-3 text-slate-900 text-right uppercase tracking-wider text-[11px]">Total Neraca Saldo Penutupan:</td>
                        <td id="postclosing-total-debit" class="py-3 px-3 text-right text-indigo-900 font-black font-mono">Rp 0</td>
                        <td id="postclosing-total-credit" class="py-3 px-3 text-right text-indigo-900 font-black font-mono">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Status Balance & Indikator -->
        <div id="postclosing-check-container" class="p-3.5 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-center text-xs font-bold">
            <span class="mr-1.5">✓</span> KESEIMBANGAN SETELAH PENUTUPAN: SEIMBANG / BALANCED (Total Debit = Total Kredit)
        </div>

        <!-- Signature Block -->
        <div class="pt-8 border-t border-slate-200 mt-8 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <p class="text-slate-500 text-[11px] mb-12">Disiapkan Oleh,<br><strong class="text-slate-700">Bagian Akuntansi / SIA</strong></p>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1 w-44 mx-auto"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Akuntan Perusahaan') ?></p>
                <p class="text-[10px] text-slate-400">Staff Akuntansi & Keuangan</p>
            </div>
            <div>
                <p class="text-slate-500 text-[11px] mb-12">Disetujui Oleh,<br><strong class="text-slate-700">Pimpinan / Pemilik Usaha</strong></p>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1 w-44 mx-auto">( ............................................ )</p>
                <p class="text-[10px] text-slate-400">Direktur / Owner</p>
            </div>
        </div>
    </div>

    <!-- TAB 6: ANALISIS RASIO KEUANGAN -->
    <div id="tab-content-ratios" class="hidden bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200">
            <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Analisis Rasio Keuangan</h3>
            <p class="text-xs text-slate-500 mt-1 font-medium" id="ratios-period-text">Periode</p>
        </div>

        <!-- 3 Kelompok Rasio Utama (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Likuiditas -->
            <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-3">
                <div class="flex items-center space-x-2 border-b border-slate-200/70 pb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Rasio Likuiditas</h4>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Current Ratio:</span>
                        <span id="ratio-current" class="font-bold font-mono text-slate-900">0.00x</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Quick Ratio:</span>
                        <span id="ratio-quick" class="font-bold font-mono text-slate-900">0.00x</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Cash Ratio:</span>
                        <span id="ratio-cash" class="font-bold font-mono text-slate-900">0.00x</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-100">Kemampuan memenuhi kewajiban lancar dengan aset lancar.</p>
            </div>

            <!-- 2. Profitabilitas -->
            <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-3">
                <div class="flex items-center space-x-2 border-b border-slate-200/70 pb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Rasio Profitabilitas</h4>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Net Profit Margin:</span>
                        <span id="ratio-npm" class="font-bold font-mono text-slate-900">0.00%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Return on Assets (ROA):</span>
                        <span id="ratio-roa" class="font-bold font-mono text-slate-900">0.00%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Return on Equity (ROE):</span>
                        <span id="ratio-roe" class="font-bold font-mono text-slate-900">0.00%</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-100">Efisiensi menghasilkan laba bersih dari omzet, aset, dan modal.</p>
            </div>

            <!-- 3. Solvabilitas -->
            <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-3">
                <div class="flex items-center space-x-2 border-b border-slate-200/70 pb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Rasio Solvabilitas</h4>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Debt to Equity (DER):</span>
                        <span id="ratio-der" class="font-bold font-mono text-slate-900">0.00x</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Debt to Asset (DAR):</span>
                        <span id="ratio-dar" class="font-bold font-mono text-slate-900">0.00x</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-100">Keseimbangan struktur pendanaan utang terhadap ekuitas dan aset.</p>
            </div>
        </div>

        <!-- Tabel Interpretasi & Diagnosa Kesehatan Keuangan -->
        <div>
            <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider mb-2">Evaluasi & Interpretasi Standar Industri</h4>
            <div class="border border-slate-200 rounded-2xl overflow-x-auto no-scrollbar touch-scroll">
                <table class="w-full text-left text-xs min-w-[580px]">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200">
                            <th class="p-3">Indikator Rasio</th>
                            <th class="p-3 text-right">Nilai Aktual</th>
                            <th class="p-3">Standar / Acuan</th>
                            <th class="p-3 text-center">Status Kesehatan</th>
                            <th class="p-3">Catatan / Analisis</th>
                        </tr>
                    </thead>
                    <tbody id="ratios-interpretation-tbody" class="divide-y divide-slate-100">
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Memuat analisis rasio...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signature Block -->
        <div class="pt-8 border-t border-slate-200 mt-8 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <p class="text-slate-500 text-[11px] mb-12">Disiapkan Oleh,<br><strong class="text-slate-700">Bagian Akuntansi / SIA</strong></p>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1 w-44 mx-auto"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Akuntan Perusahaan') ?></p>
                <p class="text-[10px] text-slate-400">Staff Akuntansi & Keuangan</p>
            </div>
            <div>
                <p class="text-slate-500 text-[11px] mb-12">Disetujui Oleh,<br><strong class="text-slate-700">Pimpinan / Pemilik Usaha</strong></p>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1 w-44 mx-auto">( ............................................ )</p>
                <p class="text-[10px] text-slate-400">Direktur / Owner</p>
            </div>
        </div>
    </div>

    <!-- TAB: NERACA SALDO (TRIAL BALANCE) -->
    <div id="tab-content-trialbalance" class="hidden bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-5xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200 dark:border-white/10">
            <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Neraca Saldo (Trial Balance)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium" id="trialbalance-period-text">Periode</p>
        </div>

        <div id="trialbalance-balance-badge" class="p-3.5 rounded-2xl flex items-center justify-between text-xs font-bold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                <span>Memeriksa keseimbangan buku besar...</span>
            </div>
        </div>

        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[620px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/70 dark:bg-white/[0.02]">
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Nama Akun Rekening</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3 text-right">Mutasi Debit</th>
                        <th class="py-3 px-3 text-right">Mutasi Kredit</th>
                        <th class="py-3 px-3 text-right">Saldo Akhir Debit</th>
                        <th class="py-3 px-3 text-right">Saldo Akhir Kredit</th>
                    </tr>
                </thead>
                <tbody id="trialbalance-tbody" class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                    <tr><td colspan="7" class="py-8 text-center text-slate-400 font-sans">Memuat neraca saldo...</td></tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 dark:border-white font-mono font-black text-xs bg-slate-50 dark:bg-slate-800/50">
                        <td colspan="5" class="py-3 px-3 text-right font-sans font-extrabold">TOTAL SALDO AKHIR</td>
                        <td class="py-3 px-3 text-right text-indigo-700 dark:text-indigo-400" id="trialbalance-total-debit">Rp 0</td>
                        <td class="py-3 px-3 text-right text-indigo-700 dark:text-indigo-400" id="trialbalance-total-credit">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- TAB: KOMPARASI KINERJA MULTI-CABANG -->
    <div id="tab-content-branchcompare" class="hidden bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-8 md:p-10 shadow-xs space-y-6 max-w-5xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200 dark:border-white/10">
            <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">PJBL KOMAK &bull; SISTEM INFORMASI AKUNTANSI</span>
            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Laba Rugi Komparatif Multi-Cabang</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium" id="branchcompare-period-text">Periode</p>
        </div>

        <div id="branchcompare-cards-container" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Summary cards per branch -->
        </div>

        <div class="overflow-x-auto pt-2 no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[580px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/70 dark:bg-white/[0.02]">
                        <th class="py-3 px-4">Cabang Outlet</th>
                        <th class="py-3 px-4 text-right">Pendapatan Bersih</th>
                        <th class="py-3 px-4 text-right">Beban Pokok (HPP)</th>
                        <th class="py-3 px-4 text-right">Laba Kotor</th>
                        <th class="py-3 px-4 text-right">Beban Operasional</th>
                        <th class="py-3 px-4 text-right font-black">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody id="branchcompare-tbody" class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 font-sans">Memuat komparasi cabang...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: CATATAN ATAS LAPORAN KEUANGAN (CALK - SAK EMKM) -->
    <div id="tab-content-calk" class="hidden bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-8 md:p-10 shadow-xs space-y-7 max-w-4xl mx-auto">
        <div class="text-center pb-5 border-b border-slate-200 dark:border-white/10">
            <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">STANDAR SAK-EMKM &bull; KELENGKAPAN LAPORAN</span>
            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Catatan Atas Laporan Keuangan (CALK)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium" id="calk-asof-text">Posisi</p>
        </div>

        <!-- Section 1: Profil & Informasi Umum Entitas -->
        <div class="space-y-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center text-[10px]">1</span>
                Informasi Umum Entitas
            </h4>
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs space-y-1.5 text-slate-700 dark:text-slate-300">
                <p><strong>Nama Entitas:</strong> <span id="calk-entity-name">PJBL Komak Laundry &amp; Dry Cleaning</span></p>
                <p><strong>Bentuk Usaha:</strong> Jasa Binatu &amp; Perawatan Pakaian Modern (Kiloan, Satuan, Karpet, Sepatu, Helm &amp; Dry Cleaning)</p>
                <p><strong>Jaringan Operasional:</strong> 1 Kantor Pusat &amp; Gudang Logistik, 2 Cabang Outlet Kasir POS</p>
                <p><strong>Domisili:</strong> Surabaya, Jawa Timur, Indonesia</p>
            </div>
        </div>

        <!-- Section 2: Dasar Penyusunan & Kebijakan Akuntansi -->
        <div class="space-y-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center text-[10px]">2</span>
                Ikhtisar Kebijakan Akuntansi Penting
            </h4>
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs space-y-2 text-slate-700 dark:text-slate-300 leading-relaxed">
                <p><strong>a. Kepatuhan Standar:</strong> Laporan keuangan disusun dan disajikan berdasarkan Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK-EMKM) yang diterbitkan oleh Ikatan Akuntan Indonesia (IAI).</p>
                <p><strong>b. Dasar Pengukuran:</strong> Laporan keuangan menggunakan dasar akrual dengan pengukuran biaya historis (historical cost).</p>
                <p><strong>c. Pengakuan Pendapatan:</strong> Pendapatan jasa laundry diakui pada saat transaksi kasir POS diselesaikan atau saat pakaian diserahkan kepada pelanggan.</p>
                <p><strong>d. Persediaan Bahan Cuci &amp; Sabun:</strong> Persediaan bahan cuci (deterjen, softener, pewangi, packaging) dinilai berdasarkan harga perolehan dengan metode FIFO/Rata-rata tertimbang.</p>
                <p><strong>e. Aset Tetap:</strong> Mesin cuci, pengering (dryer), dan boiler diakui sebesar harga perolehan dan disusutkan menggunakan metode garis lurus (straight-line method).</p>
            </div>
        </div>

        <!-- Section 3: Rincian Pos-Pos Signifikan -->
        <div class="space-y-3">
            <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center text-[10px]">3</span>
                Penjelasan Rincian Akun Signifikan
            </h4>
            <div id="calk-notes-container" class="space-y-3 text-xs">
                <!-- Rendered dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
    let currentTab = 'income';

    document.addEventListener('DOMContentLoaded', () => {
        loadIncomeStatement();
    });

    function switchTab(tab) {
        currentTab = tab;
        const btnInc = document.getElementById('tab-btn-income');
        const btnBal = document.getElementById('tab-btn-balance');
        const btnCf = document.getElementById('tab-btn-cashflow');
        const btnEq = document.getElementById('tab-btn-equity');
        const btnTb = document.getElementById('tab-btn-trialbalance');
        const btnBc = document.getElementById('tab-btn-branchcompare');
        const btnCk = document.getElementById('tab-btn-calk');
        const btnPc = document.getElementById('tab-btn-postclosing');
        const btnRt = document.getElementById('tab-btn-ratios');

        const contentInc = document.getElementById('tab-content-income');
        const contentBal = document.getElementById('tab-content-balance');
        const contentCf = document.getElementById('tab-content-cashflow');
        const contentEq = document.getElementById('tab-content-equity');
        const contentTb = document.getElementById('tab-content-trialbalance');
        const contentBc = document.getElementById('tab-content-branchcompare');
        const contentCk = document.getElementById('tab-content-calk');
        const contentPc = document.getElementById('tab-content-postclosing');
        const contentRt = document.getElementById('tab-content-ratios');

        const rangeFilter = document.getElementById('filter-date-range');
        const singleFilter = document.getElementById('filter-single-date');

        // Reset classes
        [btnInc, btnBal, btnCf, btnEq, btnTb, btnBc, btnCk, btnPc, btnRt].forEach(b => {
            if (b) b.className = 'shrink-0 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-all whitespace-nowrap text-center';
        });
        [contentInc, contentBal, contentCf, contentEq, contentTb, contentBc, contentCk, contentPc, contentRt].forEach(c => {
            if (c) c.classList.add('hidden');
        });

        const activeClass = 'shrink-0 py-2 px-3.5 rounded-xl font-bold text-xs bg-white text-indigo-700 shadow-xs transition-all whitespace-nowrap text-center';

        if (tab === 'income') {
            btnInc.className = activeClass;
            contentInc.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadIncomeStatement();
        } else if (tab === 'balance') {
            btnBal.className = activeClass;
            contentBal.classList.remove('hidden');
            rangeFilter.classList.add('hidden');
            singleFilter.classList.remove('hidden');
            loadBalanceSheet();
        } else if (tab === 'cashflow') {
            btnCf.className = activeClass;
            contentCf.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadCashFlow();
        } else if (tab === 'equity') {
            btnEq.className = activeClass;
            contentEq.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadEquityChanges();
        } else if (tab === 'trialbalance') {
            btnTb.className = activeClass;
            contentTb.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadTrialBalance();
        } else if (tab === 'branchcompare') {
            btnBc.className = activeClass;
            contentBc.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadBranchComparison();
        } else if (tab === 'calk') {
            btnCk.className = activeClass;
            contentCk.classList.remove('hidden');
            rangeFilter.classList.add('hidden');
            singleFilter.classList.remove('hidden');
            loadCALK();
        } else if (tab === 'postclosing') {
            btnPc.className = activeClass;
            contentPc.classList.remove('hidden');
            rangeFilter.classList.add('hidden');
            singleFilter.classList.remove('hidden');
            loadPostClosingTrialBalance();
        } else if (tab === 'ratios') {
            btnRt.className = activeClass;
            contentRt.classList.remove('hidden');
            rangeFilter.classList.remove('hidden');
            singleFilter.classList.add('hidden');
            loadFinancialRatios();
        }
    }

    function reloadActiveReport() {
        if (currentTab === 'income') loadIncomeStatement();
        else if (currentTab === 'balance') loadBalanceSheet();
        else if (currentTab === 'cashflow') loadCashFlow();
        else if (currentTab === 'equity') loadEquityChanges();
        else if (currentTab === 'trialbalance') loadTrialBalance();
        else if (currentTab === 'branchcompare') loadBranchComparison();
        else if (currentTab === 'calk') loadCALK();
        else if (currentTab === 'postclosing') loadPostClosingTrialBalance();
        else if (currentTab === 'ratios') loadFinancialRatios();
    }

    async function loadTrialBalance() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const branch = document.getElementById('rep-branch-select').value;
        const tbody = document.getElementById('trialbalance-tbody');

        try {
            const res = await fetch(`../api/reports.php?type=trial_balance&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('trialbalance-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date}`;

            const badge = document.getElementById('trialbalance-balance-badge');
            if (result.is_balanced) {
                badge.className = 'p-3.5 rounded-2xl flex items-center justify-between text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40';
                badge.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>STATUS BUKU BESAR: SEIMBANG (BALANCED)</span>
                    </div>
                    <span class="font-mono">Selisih: Rp 0 (Total Debit = Total Kredit)</span>
                `;
            } else {
                badge.className = 'p-3.5 rounded-2xl flex items-center justify-between text-xs font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40';
                badge.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span>STATUS BUKU BESAR: TIDAK SEIMBANG</span>
                    </div>
                    <span class="font-mono">Selisih: Rp ${Number(result.difference).toLocaleString('id-ID')}</span>
                `;
            }

            tbody.innerHTML = result.accounts.map(a => `
                <tr class="hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="py-2.5 px-3 font-bold text-indigo-600 dark:text-indigo-400 font-mono">${a.code}</td>
                    <td class="py-2.5 px-3 font-sans text-slate-800 dark:text-slate-200 font-semibold">${a.name}</td>
                    <td class="py-2.5 px-3 font-sans text-slate-400 text-[11px]">${a.category}</td>
                    <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-400">${Number(a.mut_debit) > 0 ? 'Rp ' + Number(a.mut_debit).toLocaleString('id-ID') : '-'}</td>
                    <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-400">${Number(a.mut_credit) > 0 ? 'Rp ' + Number(a.mut_credit).toLocaleString('id-ID') : '-'}</td>
                    <td class="py-2.5 px-3 text-right font-bold ${Number(a.ending_debit) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600'}">${Number(a.ending_debit) > 0 ? 'Rp ' + Number(a.ending_debit).toLocaleString('id-ID') : '-'}</td>
                    <td class="py-2.5 px-3 text-right font-bold ${Number(a.ending_credit) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600'}">${Number(a.ending_credit) > 0 ? 'Rp ' + Number(a.ending_credit).toLocaleString('id-ID') : '-'}</td>
                </tr>
            `).join('') || '<tr><td colspan="7" class="py-8 text-center text-slate-400 font-sans">Tidak ada mutasi akun.</td></tr>';

            document.getElementById('trialbalance-total-debit').innerText = 'Rp ' + Number(result.total_debit).toLocaleString('id-ID');
            document.getElementById('trialbalance-total-credit').innerText = 'Rp ' + Number(result.total_credit).toLocaleString('id-ID');
        } catch (e) {
            console.error('Error load trial balance:', e);
        }
    }

    async function loadBranchComparison() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const cardsCont = document.getElementById('branchcompare-cards-container');
        const tbody = document.getElementById('branchcompare-tbody');

        try {
            const res = await fetch(`../api/reports.php?type=branch_comparison&start_date=${start}&end_date=${end}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('branchcompare-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date}`;

            // Render Cards
            cardsCont.innerHTML = result.comparisons.map(b => `
                <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-white/10 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-900 dark:text-white">${b.branch_name}</span>
                        <span class="text-[10px] font-mono font-bold text-slate-400">${b.branch_code}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 space-y-1 pt-1 border-t border-slate-100 dark:border-white/5">
                        <div class="flex justify-between"><span>Pendapatan:</span><span class="font-bold text-slate-700 dark:text-slate-300 font-mono">Rp ${Number(b.revenue).toLocaleString('id-ID')}</span></div>
                        <div class="flex justify-between"><span>HPP (Bahan):</span><span class="font-bold text-slate-700 dark:text-slate-300 font-mono">Rp ${Number(b.cogs).toLocaleString('id-ID')}</span></div>
                        <div class="flex justify-between"><span>Beban Operasional:</span><span class="font-bold text-slate-700 dark:text-slate-300 font-mono">Rp ${Number(b.expenses).toLocaleString('id-ID')}</span></div>
                        <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-white/10 font-bold ${Number(b.net_profit) >= 0 ? 'text-emerald-600' : 'text-rose-600'}">
                            <span>Laba Bersih:</span>
                            <span class="font-mono">Rp ${Number(b.net_profit).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </div>
            `).join('');

            // Render Table
            tbody.innerHTML = result.comparisons.map(b => `
                <tr class="hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="py-3 px-4 font-sans font-bold text-slate-900 dark:text-white">${b.branch_name} (${b.branch_code})</td>
                    <td class="py-3 px-4 text-right text-slate-800 dark:text-slate-200">Rp ${Number(b.revenue).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right text-slate-800 dark:text-slate-200">Rp ${Number(b.cogs).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-200">Rp ${Number(b.gross_profit).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right text-slate-800 dark:text-slate-200">Rp ${Number(b.expenses).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right font-black ${Number(b.net_profit) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}">Rp ${Number(b.net_profit).toLocaleString('id-ID')}</td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('Error load branch comparison:', e);
        }
    }

    async function loadCALK() {
        const asOf = document.getElementById('rep-as-of-date').value;
        const container = document.getElementById('calk-notes-container');

        try {
            const res = await fetch(`../api/reports.php?type=calk&as_of_date=${asOf}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('calk-asof-text').innerText = `Per Tanggal: ${result.as_of_date}`;
            document.getElementById('calk-entity-name').innerText = result.entity.name;

            const notes = result.notes;

            let html = `
                <!-- Note 1: Kas dan Setara Kas -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/40 space-y-2">
                    <div class="font-bold text-slate-800 dark:text-slate-200">3.1. Kas dan Setara Kas</div>
                    <p class="text-slate-500 text-[11px]">Rincian kas fisik pada masing-masing kasir outlet dan rekening operasional bank:</p>
                    <div class="pl-2 space-y-1 font-mono pt-1 text-xs">
                        ${notes.cash_and_banks.map(c => `
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-400 font-sans">[${c.code}] ${c.name}</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp ${Number(c.balance).toLocaleString('id-ID')}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Note 2: Persediaan Bahan & Perlengkapan -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/40 space-y-2">
                    <div class="font-bold text-slate-800 dark:text-slate-200">3.2. Persediaan Bahan Cuci &amp; Sabun Operasional</div>
                    <p class="text-slate-500 text-[11px]">Nilai persediaan bahan cuci (deterjen, softener, pewangi konsentrat, sabun khusus) dan perlengkapan toko:</p>
                    <div class="pl-2 space-y-1 font-mono pt-1 text-xs">
                        ${notes.inventories.map(i => `
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-400 font-sans">[${i.code}] ${i.name}</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp ${Number(i.balance).toLocaleString('id-ID')}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Note 3: Liabilitas Lancar & Pajak -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/40 space-y-2">
                    <div class="font-bold text-slate-800 dark:text-slate-200">3.3. Liabilitas &amp; Kewajiban Jangka Pendek</div>
                    <p class="text-slate-500 text-[11px]">Kewajiban jangka pendek lancar termasuk utang titipan PPN kasir dan utang supplier:</p>
                    <div class="pl-2 space-y-1 font-mono pt-1 text-xs">
                        ${notes.liabilities_and_equity.filter(l => l.balance > 0).map(l => `
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-400 font-sans">[${l.code}] ${l.name}</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp ${Number(l.balance).toLocaleString('id-ID')}</span>
                            </div>
                        `).join('') || '<div class="text-slate-400 italic font-sans">Tidak ada kewajiban lancar yang jatuh tempo.</div>'}
                    </div>
                </div>

                <!-- Note 4: Statistik Transaksi POS -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/40 space-y-2">
                    <div class="font-bold text-slate-800 dark:text-slate-200">3.4. Volume Layanan &amp; Transaksi Kasir POS</div>
                    <div class="grid grid-cols-2 gap-3 pt-1 text-center">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                            <span class="text-slate-400 text-[10px] block">Total Transaksi Kasir Selesai</span>
                            <span class="font-bold text-base text-slate-900 dark:text-white font-mono">${notes.statistics.total_trx} Nota</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                            <span class="text-slate-400 text-[10px] block">Akumulasi Omzet Layanan</span>
                            <span class="font-bold text-base text-indigo-600 dark:text-indigo-400 font-mono">Rp ${Number(notes.statistics.total_omzet).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = html;
        } catch (e) {
            console.error('Error load CALK:', e);
        }
    }

    async function loadIncomeStatement() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=income_statement&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('income-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date}`;

            // 1. Revenue
            const revList = document.getElementById('income-revenue-list');
            revList.innerHTML = result.revenues.map(r => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${r.code}] ${r.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(r.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('') || '<p class="text-slate-400 py-2">Tidak ada pendapatan</p>';
            document.getElementById('income-total-revenue').innerText = 'Rp ' + Number(result.total_revenue).toLocaleString('id-ID');

            // 2. COGS
            const cogsList = document.getElementById('income-cogs-list');
            cogsList.innerHTML = result.cogs.map(c => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${c.code}] ${c.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(c.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('') || '<p class="text-slate-400 py-2">Rp 0</p>';
            document.getElementById('income-total-cogs').innerText = 'Rp ' + Number(result.total_cogs).toLocaleString('id-ID');

            // Gross Profit
            document.getElementById('income-gross-profit').innerText = 'Rp ' + Number(result.gross_profit).toLocaleString('id-ID');

            // 3. Expenses
            const expList = document.getElementById('income-expense-list');
            expList.innerHTML = result.expenses.map(e => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${e.code}] ${e.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(e.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('') || '<p class="text-slate-400 py-2">Tidak ada beban operasional</p>';
            document.getElementById('income-total-expenses').innerText = 'Rp ' + Number(result.total_expenses).toLocaleString('id-ID');

            // Net Profit
            document.getElementById('income-net-profit').innerText = 'Rp ' + Number(result.net_profit).toLocaleString('id-ID');
        } catch (e) {
            console.error('Error load income statement:', e);
        }
    }

    async function loadBalanceSheet() {
        const asOf = document.getElementById('rep-as-of-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=balance_sheet&as_of_date=${asOf}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('balance-as-of-text').innerText = `Per Tanggal: ${result.as_of_date}`;

            // Assets
            document.getElementById('balance-assets-list').innerHTML = result.assets.map(a => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${a.code}] ${a.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(a.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('');
            document.getElementById('balance-total-assets').innerText = 'Rp ' + Number(result.total_assets).toLocaleString('id-ID');

            // Liabilities
            document.getElementById('balance-liabilities-list').innerHTML = result.liabilities.map(l => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${l.code}] ${l.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(l.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('') || '<p class="text-slate-400 py-2">Rp 0</p>';
            document.getElementById('balance-total-liabilities').innerText = 'Rp ' + Number(result.total_liabilities).toLocaleString('id-ID');

            // Equities
            document.getElementById('balance-equities-list').innerHTML = result.equities.map(eq => `
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-600 font-medium">[${eq.code}] ${eq.name}</span>
                    <span class="font-bold text-slate-800 font-mono">Rp ${Number(eq.amount).toLocaleString('id-ID')}</span>
                </div>
            `).join('');
            document.getElementById('balance-total-equities').innerText = 'Rp ' + Number(result.total_equities).toLocaleString('id-ID');

            // Total Passiva
            document.getElementById('balance-total-passiva').innerText = 'Rp ' + Number(result.total_passiva).toLocaleString('id-ID');

            // Check
            const checkEl = document.getElementById('balance-check-container');
            if (result.is_balanced) {
                checkEl.className = 'p-3.5 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-center text-xs font-bold';
                checkEl.innerHTML = '<span class="mr-1.5">✓</span> KESEIMBANGAN POSISI KEUANGAN: SEIMBANG / BALANCED (Aset = Liabilitas + Ekuitas)';
            } else {
                checkEl.className = 'p-3.5 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200 text-center text-xs font-bold';
                checkEl.innerHTML = `<span class="mr-1.5">⚠</span> NERACA TIDAK SEIMBANG (Selisih: Rp ${Number(result.diff).toLocaleString('id-ID')})`;
            }
        } catch (e) {
            console.error('Error load balance sheet:', e);
        }
    }

    async function loadCashFlow() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=cash_flow&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('cashflow-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date}`;

            // 1. Aktivitas Operasi
            document.getElementById('cf-sales').innerText = 'Rp ' + Number(result.operating.cash_from_sales || 0).toLocaleString('id-ID');
            if (document.getElementById('cf-other-in')) {
                document.getElementById('cf-other-in').innerText = 'Rp ' + Number(result.operating.cash_from_other || 0).toLocaleString('id-ID');
            }
            document.getElementById('cf-suppliers').innerText = '(Rp ' + Number(result.operating.cash_for_suppliers || 0).toLocaleString('id-ID') + ')';
            if (document.getElementById('cf-salaries')) {
                document.getElementById('cf-salaries').innerText = '(Rp ' + Number(result.operating.cash_for_salaries || 0).toLocaleString('id-ID') + ')';
            }
            if (document.getElementById('cf-utilities')) {
                document.getElementById('cf-utilities').innerText = '(Rp ' + Number(result.operating.cash_for_utilities || 0).toLocaleString('id-ID') + ')';
            }
            if (document.getElementById('cf-rent')) {
                document.getElementById('cf-rent').innerText = '(Rp ' + Number(result.operating.cash_for_rent || 0).toLocaleString('id-ID') + ')';
            }
            document.getElementById('cf-operations').innerText = '(Rp ' + Number(result.operating.cash_for_operations || 0).toLocaleString('id-ID') + ')';
            document.getElementById('cf-net-operating').innerText = (result.operating.net_operating < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.operating.net_operating)).toLocaleString('id-ID');

            // 2. Aktivitas Investasi
            document.getElementById('cf-equipment').innerText = '(Rp ' + Number(result.investing.cash_for_equipment || 0).toLocaleString('id-ID') + ')';
            if (document.getElementById('cf-asset-sales')) {
                document.getElementById('cf-asset-sales').innerText = 'Rp ' + Number(result.investing.cash_from_assets || 0).toLocaleString('id-ID');
            }
            document.getElementById('cf-net-investing').innerText = (result.investing.net_investing < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.investing.net_investing)).toLocaleString('id-ID');

            // 3. Aktivitas Pendanaan
            document.getElementById('cf-capital').innerText = 'Rp ' + Number(result.financing.cash_from_capital || 0).toLocaleString('id-ID');
            if (document.getElementById('cf-loans')) {
                document.getElementById('cf-loans').innerText = 'Rp ' + Number(result.financing.cash_from_loans || 0).toLocaleString('id-ID');
            }
            document.getElementById('cf-drawings').innerText = '(Rp ' + Number(result.financing.cash_for_drawings || 0).toLocaleString('id-ID') + ')';
            if (document.getElementById('cf-loan-repayments')) {
                document.getElementById('cf-loan-repayments').innerText = '(Rp ' + Number(result.financing.cash_for_loans || 0).toLocaleString('id-ID') + ')';
            }
            document.getElementById('cf-net-financing').innerText = (result.financing.net_financing < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.financing.net_financing)).toLocaleString('id-ID');

            // 4. Rekonsiliasi Kas Akhir & Neraca
            document.getElementById('cf-net-change').innerText = (result.net_change_in_cash < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.net_change_in_cash)).toLocaleString('id-ID');
            document.getElementById('cf-beginning').innerText = 'Rp ' + Number(result.beginning_cash || 0).toLocaleString('id-ID');
            document.getElementById('cf-ending').innerText = 'Rp ' + Number(result.ending_cash || 0).toLocaleString('id-ID');
            if (document.getElementById('cf-neraca-cash')) {
                document.getElementById('cf-neraca-cash').innerText = 'Rp ' + Number(result.balance_sheet_cash || 0).toLocaleString('id-ID');
            }

            // Reconciliation Badge
            const badgeEl = document.getElementById('cf-reconciliation-badge');
            if (badgeEl) {
                if (result.is_reconciled) {
                    badgeEl.className = 'p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-center text-xs font-bold';
                    badgeEl.innerHTML = '<span class="mr-1.5">✓</span> TERREKONSILIASI PENUH: Saldo Kas Akhir Periode Sesuai 100% dengan Laporan Posisi Keuangan (Neraca)';
                } else {
                    badgeEl.className = 'p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-center text-xs font-bold';
                    badgeEl.innerHTML = `<span class="mr-1.5">⚠</span> TERDAPAT SELISIH REKONSILIASI: Rp ${Number(Math.abs(result.diff || 0)).toLocaleString('id-ID')} dengan Kas di Neraca`;
                }
            }
        } catch (e) {
            console.error('Error load cash flow:', e);
        }
    }

    async function loadEquityChanges() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=equity_changes&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('equity-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date}`;

            document.getElementById('eq-beginning').innerText = 'Rp ' + Number(result.beginning_capital).toLocaleString('id-ID');
            document.getElementById('eq-additional').innerText = 'Rp ' + Number(result.additional_capital).toLocaleString('id-ID');
            document.getElementById('eq-net-income').innerText = (result.net_income < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.net_income)).toLocaleString('id-ID');
            document.getElementById('eq-drawings').innerText = '(Rp ' + Number(result.drawings).toLocaleString('id-ID') + ')';
            document.getElementById('eq-ending').innerText = 'Rp ' + Number(result.ending_capital).toLocaleString('id-ID');
        } catch (e) {
            console.error('Error load equity changes:', e);
        }
    }

    async function loadPostClosingTrialBalance() {
        const asOf = document.getElementById('rep-as-of-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=post_closing_trial_balance&as_of_date=${asOf}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('postclosing-as-of-text').innerText = `Per Tanggal: ${result.as_of_date}`;

            const tbody = document.getElementById('postclosing-tbody');
            tbody.innerHTML = '';

            if (result.real_accounts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-slate-400">Tidak ada saldo akun riil.</td></tr>';
            } else {
                result.real_accounts.forEach(acc => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-700">${acc.code}</td>
                        <td class="py-2.5 px-3 font-sans font-medium text-slate-900">${acc.name}</td>
                        <td class="py-2.5 px-3 font-sans text-slate-500">${acc.category}</td>
                        <td class="py-2.5 px-3 text-right font-black ${Number(acc.debit) > 0 ? 'text-indigo-900' : 'text-slate-300'}">${Number(acc.debit) > 0 ? 'Rp ' + Number(acc.debit).toLocaleString('id-ID') : '-'}</td>
                        <td class="py-2.5 px-3 text-right font-black ${Number(acc.credit) > 0 ? 'text-indigo-900' : 'text-slate-300'}">${Number(acc.credit) > 0 ? 'Rp ' + Number(acc.credit).toLocaleString('id-ID') : '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('postclosing-total-debit').innerText = 'Rp ' + Number(result.total_debit).toLocaleString('id-ID');
            document.getElementById('postclosing-total-credit').innerText = 'Rp ' + Number(result.total_credit).toLocaleString('id-ID');

            const checkEl = document.getElementById('postclosing-check-container');
            if (result.is_balanced) {
                checkEl.className = 'p-3.5 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-center text-xs font-bold';
                checkEl.innerHTML = '<span class="mr-1.5">✓</span> KESEIMBANGAN SETELAH PENUTUPAN: SEIMBANG / BALANCED (Total Debit = Total Kredit)';
            } else {
                checkEl.className = 'p-3.5 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200 text-center text-xs font-bold';
                checkEl.innerHTML = `<span class="mr-1.5">⚠</span> NERACA PENUTUPAN TIDAK SEIMBANG (Selisih: Rp ${Number(result.diff).toLocaleString('id-ID')})`;
            }

            const proofBadge = document.getElementById('postclosing-proof-badge');
            if (result.all_nominal_zero) {
                proofBadge.className = 'px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-extrabold text-[11px] border border-emerald-300';
                proofBadge.innerText = 'Akun Nominal Nihil (Valid SAK-EMKM)';
            } else {
                proofBadge.className = 'px-3 py-1 rounded-xl bg-amber-100 text-amber-800 font-extrabold text-[11px] border border-amber-300';
                proofBadge.innerText = 'Belum Ditutup Buku (Akun Nominal Masih Ada)';
            }
        } catch (e) {
            console.error('Error load post-closing trial balance:', e);
        }
    }

    async function loadFinancialRatios() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const branch = document.getElementById('rep-branch-select').value;

        try {
            const res = await fetch(`../api/reports.php?type=financial_ratios&start_date=${start}&end_date=${end}&as_of_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            document.getElementById('ratios-period-text').innerText = `Periode: ${result.period.start_date} s/d ${result.period.end_date} (Posisi Neraca: ${result.period.as_of_date})`;

            const liq = result.liquidity_ratios || {};
            const prof = result.profitability_ratios || {};
            const solv = result.solvency_ratios || {};

            document.getElementById('ratio-current').innerText = `${Number(liq.current_ratio || 0).toFixed(2)}x`;
            document.getElementById('ratio-quick').innerText = `${Number(liq.quick_ratio || 0).toFixed(2)}x`;
            document.getElementById('ratio-cash').innerText = `${Number(liq.cash_ratio || 0).toFixed(2)}x`;

            document.getElementById('ratio-npm').innerText = `${Number(prof.net_profit_margin || 0).toFixed(2)}%`;
            document.getElementById('ratio-roa').innerText = `${Number(prof.return_on_assets || 0).toFixed(2)}%`;
            document.getElementById('ratio-roe').innerText = `${Number(prof.return_on_equity || 0).toFixed(2)}%`;

            document.getElementById('ratio-der').innerText = `${Number(solv.debt_to_equity_ratio || 0).toFixed(2)}x`;
            document.getElementById('ratio-dar').innerText = `${Number(solv.debt_to_asset_ratio || 0).toFixed(2)}x`;

            const tbody = document.getElementById('ratios-interpretation-tbody');
            if (result.interpretation && result.interpretation.length > 0) {
                tbody.innerHTML = result.interpretation.map(row => {
                    let badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    if (row.status.includes('Kurang') || row.status.includes('Tinggi')) {
                        badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                    } else if (row.status.includes('Waspada') || row.status.includes('Rendah')) {
                        badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                    }
                    return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 font-bold text-slate-800">${row.ratio}</td>
                            <td class="p-3 text-right font-black font-mono text-slate-900">${row.value}</td>
                            <td class="p-3 font-mono text-slate-500 text-[11px]">${row.benchmark}</td>
                            <td class="p-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeClass}">
                                    ${row.status}
                                </span>
                            </td>
                            <td class="p-3 text-slate-600 text-[11px]">${row.note}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400">Data rasio belum tersedia untuk periode ini.</td></tr>';
            }
        } catch (e) {
            console.error('Error load financial ratios:', e);
        }
    }

    function exportActiveReportToCSV() {
        const start = document.getElementById('rep-start-date').value;
        const end = document.getElementById('rep-end-date').value;
        const asOf = document.getElementById('rep-as-of-date').value;
        let filename = `Laporan_${currentTab}_${start}_${end}.csv`;
        let csvRows = [];

        csvRows.push(['PJBL KOMAK - SISTEM INFORMASI AKUNTANSI']);
        csvRows.push([`Tipe Laporan: ${currentTab.toUpperCase()}`]);
        csvRows.push([`Periode / Tanggal: ${currentTab === 'balance' || currentTab === 'postclosing' ? asOf : start + ' s/d ' + end}`]);
        csvRows.push([]);

        if (currentTab === 'income') {
            csvRows.push(['Kategori', 'Kode Akun', 'Nama Akun', 'Jumlah (IDR)']);
            document.querySelectorAll('#income-revenue-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Pendapatan Usaha', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Total Pendapatan Usaha', '', '', document.getElementById('income-total-revenue')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            document.querySelectorAll('#income-cogs-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Beban Pokok Penjualan', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Laba Kotor', '', '', document.getElementById('income-gross-profit')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            document.querySelectorAll('#income-expense-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Beban Operasional', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Total Beban Operasional', '', '', document.getElementById('income-total-expenses')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            csvRows.push(['Laba Bersih Usaha', '', '', document.getElementById('income-net-profit')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
        } else if (currentTab === 'balance') {
            csvRows.push(['Klasifikasi', 'Kode Akun', 'Nama Akun', 'Saldo (IDR)']);
            document.querySelectorAll('#balance-assets-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Aset', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Total Aset', '', '', document.getElementById('balance-total-assets')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            document.querySelectorAll('#balance-liabilities-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Liabilitas', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Total Liabilitas', '', '', document.getElementById('balance-total-liabilities')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            document.querySelectorAll('#balance-equities-list > div').forEach(div => {
                const text = div.innerText.split('\n');
                csvRows.push(['Ekuitas', text[0] || '', '', text[1]?.replace(/[Rp. ]/g, '') || '0']);
            });
            csvRows.push(['Total Ekuitas', '', '', document.getElementById('balance-total-equities')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
            csvRows.push(['Total Pasiva (Liabilitas + Ekuitas)', '', '', document.getElementById('balance-total-passiva')?.innerText?.replace(/[Rp. ]/g, '') || '0']);
        } else if (currentTab === 'ratios') {
            csvRows.push(['Indikator Rasio', 'Nilai Aktual', 'Standar/Benchmark', 'Status', 'Catatan']);
            document.querySelectorAll('#ratios-interpretation-tbody tr').forEach(tr => {
                const cells = Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
                if (cells.length >= 5) csvRows.push(cells);
            });
        } else {
            const activeContainer = document.getElementById(`tab-content-${currentTab}`);
            if (activeContainer) {
                const tables = activeContainer.querySelectorAll('table');
                tables.forEach(table => {
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(r => {
                        const rowData = Array.from(r.querySelectorAll('th, td')).map(cell => cell.innerText.trim().replace(/,/g, ''));
                        csvRows.push(rowData);
                    });
                });
            }
        }

        const csvContent = '\uFEFF' + csvRows.map(r => r.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')).join('\r\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<style>
@media print {
    #main-sidebar, header, .segmented-tabs, #top-nav, button, select, input[type="date"], label {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .shadow-xs, .shadow-md, .shadow-2xl {
        box-shadow: none !important;
    }
    .border {
        border-color: #cbd5e1 !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
