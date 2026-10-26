<?php
$pageTitle = 'Dashboard Monitoring Pusat';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header Page with Executive Welcome & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2.5">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dashboard Monitoring SIA Pusat</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pemantauan omset penjualan kasir cabang, likuiditas kas, dan otomasi akuntansi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="scan-faktur.php" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Scan Faktur AI
            </a>
            <a href="jurnal-penyesuaian.php" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Jurnal Penyesuaian
            </a>
            <a href="produk.php" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 shadow-2xs transition-all duration-200">
                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Katalog & Stok
            </a>
        </div>
    </div>

    <!-- 4 Modern Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Penjualan Hari Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penjualan Hari Ini</span>
                <span class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 id="stat-today-sales" data-kfx-roll class="text-2xl font-black text-slate-900 tracking-tight font-mono">Rp 0</h3>
                <div class="flex items-center space-x-1.5 mt-2">
                    <span class="inline-flex items-center text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">Live POS</span>
                    <span class="text-[11px] text-slate-400">Semua cabang aktif</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Penjualan Bulan Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omset Bulan Ini</span>
                <span class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 id="stat-month-sales" data-kfx-roll class="text-2xl font-black text-slate-900 tracking-tight font-mono">Rp 0</h3>
                <div class="flex items-center space-x-1.5 mt-2">
                    <span class="inline-flex items-center text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">Bulan Berjalan</span>
                    <span class="text-[11px] text-slate-400">Pendapatan kotor</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Kas & Likuiditas -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-violet-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kas & Bank</span>
                <span class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 id="stat-total-cash" data-kfx-roll class="text-2xl font-black text-indigo-900 tracking-tight font-mono">Rp 0</h3>
                <div class="flex items-center space-x-1.5 mt-2">
                    <span class="inline-flex items-center text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">Likuid</span>
                    <span class="text-[11px] text-slate-400">Kas kasir + Rekening</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Draft Faktur Menunggu Verifikasi -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 group relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Draft Faktur OCR</span>
                <span class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="flex items-center space-x-2">
                    <h3 id="stat-pending-scans" data-kfx-roll class="text-2xl font-black text-amber-600 tracking-tight font-mono">0</h3>
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-md">Perlu Verifikasi</span>
                </div>
                <div class="mt-2">
                    <a href="scan-faktur.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold inline-flex items-center space-x-1 group-hover:underline">
                        <span>Verifikasi draf jurnal</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Charts Section (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Tren Omset 7 Hari -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Tren Penjualan 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-400">Total omset kasir gabungan per hari dalam Rupiah.</p>
                </div>
                <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/60 px-2.5 py-1 rounded-full flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span>Live Graph</span>
                </span>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Komposisi Omset Cabang -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Distribusi Omset per Cabang</h3>
                <p class="text-xs text-slate-400 mt-0.5">Proporsi kontribusi penjualan bulan ini.</p>
            </div>
            <div class="h-56 relative flex items-center justify-center my-auto">
                <canvas id="branchPieChart"></canvas>
            </div>
            <div class="pt-3 border-t border-slate-100 text-center">
                <span class="text-[11px] text-slate-400 font-medium">Data diperbarui otomatis dari transaksi POS</span>
            </div>
        </div>
    </div>

    <!-- Monitoring Cabang & Kasir Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monitoring Status Cabang -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Monitoring Outlet & Kasir Multi-Cabang</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Sinkronisasi status transaksi dan performa penjualan.</p>
                </div>
                <a href="cabang.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                    Kelola Cabang &rarr;
                </a>
            </div>
            <div class="overflow-x-auto no-scrollbar touch-scroll">
                <table class="w-full text-left text-xs min-w-[480px]">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold tracking-wider">
                            <th class="pb-3">Cabang Outlet</th>
                            <th class="pb-3">Status Jaringan</th>
                            <th class="pb-3">Volume Transaksi</th>
                            <th class="pb-3 text-right">Omset Bulan Ini</th>
                        </tr>
                    </thead>
                    <tbody id="branch-performance-table" class="divide-y divide-slate-100">
                        <tr><td colspan="4" class="py-6 text-center text-slate-400">Memuat data cabang...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rincian Saldo Kas Kasir & Bank -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-slate-100 mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Posisi Kasir & Kas Utama</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Saldo real-time di masing-masing kasir cabang.</p>
                </div>
                <div id="cash-accounts-list" class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    <p class="text-xs text-slate-400">Memuat data kas...</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="buku-besar.php" class="w-full block text-center py-2.5 px-4 rounded-2xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-700 text-xs font-bold transition-all">
                    Buka Buku Besar Kas &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let trendChartInstance = null;
    let branchPieInstance = null;

    document.addEventListener('DOMContentLoaded', loadDashboardData);

    async function loadDashboardData() {
        try {
            const res = await fetch('../api/reports.php?type=dashboard');
            const result = await res.json();
            if (!result.success) return;

            const d = result.data;
            document.getElementById('stat-today-sales').innerText = 'Rp ' + Number(d.today_sales).toLocaleString('id-ID');
            document.getElementById('stat-month-sales').innerText = 'Rp ' + Number(d.month_sales).toLocaleString('id-ID');
            document.getElementById('stat-total-cash').innerText = 'Rp ' + Number(d.total_cash).toLocaleString('id-ID');
            document.getElementById('stat-pending-scans').innerText = d.pending_scans;

            // Render table cabang
            const tbody = document.getElementById('branch-performance-table');
            tbody.innerHTML = '';
            d.branch_performances.forEach(b => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/80 transition-colors';
                tr.innerHTML = `
                    <td class="py-3 font-semibold text-slate-800">
                        <div class="font-bold text-slate-900">${b.name}</div>
                        <span class="text-[10px] text-slate-400 font-mono font-medium">${b.code}</span>
                    </td>
                    <td class="py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Aktif & Terhubung
                        </span>
                    </td>
                    <td class="py-3 text-slate-600 font-medium">${b.trx_count} transaksi</td>
                    <td class="py-3 text-right font-black text-slate-900 font-mono">Rp ${Number(b.total_sales).toLocaleString('id-ID')}</td>
                `;
                tbody.appendChild(tr);
            });

            // Render cash accounts
            const cashList = document.getElementById('cash-accounts-list');
            cashList.innerHTML = '';
            d.cash_accounts.forEach(ca => {
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-3 rounded-2xl bg-slate-50/80 border border-slate-100 hover:bg-slate-100/60 transition-colors';
                item.innerHTML = `
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800 leading-tight">${ca.name}</span>
                            <span class="block text-[10px] text-slate-400 font-mono">Kode: ${ca.code}</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-slate-900 font-mono">Rp ${Number(ca.balance).toLocaleString('id-ID')}</span>
                `;
                cashList.appendChild(item);
            });

            // Render Visual Chart 1: Tren Penjualan 7 Hari
            if (d.chart_trend) {
                const ctxTrend = document.getElementById('trendChart').getContext('2d');
                if (trendChartInstance) trendChartInstance.destroy();

                const gradientBg = ctxTrend.createLinearGradient(0, 0, 0, 250);
                gradientBg.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
                gradientBg.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

                trendChartInstance = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: d.chart_trend.labels,
                        datasets: [{
                            label: 'Omset Penjualan (Rp)',
                            data: d.chart_trend.sales,
                            borderColor: '#4f46e5',
                            backgroundColor: gradientBg,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4f46e5',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ' Omset: Rp ' + Number(context.raw).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(226, 232, 240, 0.8)' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(val) {
                                        if (val >= 1000000) return 'Rp ' + (val/1000000) + 'jt';
                                        if (val >= 1000) return 'Rp ' + (val/1000) + 'rb';
                                        return 'Rp ' + val;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Render Visual Chart 2: Komposisi Cabang (Doughnut)
            if (d.chart_branches) {
                const ctxPie = document.getElementById('branchPieChart').getContext('2d');
                if (branchPieInstance) branchPieInstance.destroy();
                branchPieInstance = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: d.chart_branches.labels,
                        datasets: [{
                            data: d.chart_branches.values,
                            backgroundColor: [
                                '#4f46e5', // Indigo
                                '#10b981', // Emerald
                                '#8b5cf6', // Violet
                                '#f59e0b', // Amber
                                '#06b6d4'  // Cyan
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { size: 11, weight: '600' }, padding: 12 }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.label}: Rp ${Number(context.raw).toLocaleString('id-ID')}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

        } catch (e) {
            console.error('Error dashboard:', e);
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
