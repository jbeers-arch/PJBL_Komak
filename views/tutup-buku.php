<?php
$pageTitle = 'Tutup Buku & Siklus Akuntansi';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header Page Formal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Tutup Buku &amp; Siklus Akuntansi</h2>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="laporan.php" class="inline-flex items-center px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Lihat Laporan Keuangan</span>
            </a>
        </div>
    </div>

    <!-- Peta Siklus Akuntansi 9 Tahapan (SAK-EMKM Standard Stepper) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Alur Siklus Akuntansi Lengkap</h3>
            <span class="text-[11px] font-semibold text-emerald-600 flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                9 Tahapan Sesuai Standar
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-2 pt-1 text-center">
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">01</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Bukti Transaksi</span>
                <span class="text-[9px] text-emerald-600 font-semibold">POS & Scan</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">02</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Jurnal Umum</span>
                <span class="text-[9px] text-emerald-600 font-semibold">Double-Entry</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">03</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Buku Besar</span>
                <span class="text-[9px] text-emerald-600 font-semibold">Mutasi Akun</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">04</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Neraca Saldo</span>
                <span class="text-[9px] text-emerald-600 font-semibold">Pra-Sesuaian</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">05</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Penyesuaian</span>
                <span class="text-[9px] text-amber-600 font-semibold">Jurnal AJE</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">06</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Lap. Keuangan</span>
                <span class="text-[9px] text-indigo-600 font-semibold">4 Laporan</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-indigo-50 border-2 border-indigo-500 shadow-xs relative">
                <span class="block text-[10px] font-black text-indigo-600">07</span>
                <span class="block text-[11px] font-extrabold text-indigo-950 leading-tight mt-0.5">Tutup Buku</span>
                <span class="text-[9px] text-indigo-700 font-bold">Jurnal Penutup</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-indigo-50 border border-indigo-200/60">
                <span class="block text-[10px] font-extrabold text-indigo-400">08</span>
                <span class="block text-[11px] font-bold text-indigo-900 leading-tight mt-0.5">Kunci Periode</span>
                <span class="text-[9px] text-indigo-600 font-semibold">Anti-Tampering</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200/60">
                <span class="block text-[10px] font-extrabold text-slate-400">09</span>
                <span class="block text-[11px] font-bold text-slate-700 leading-tight mt-0.5">Neraca Pasca</span>
                <span class="text-[9px] text-slate-500 font-semibold">Saldo Bersih</span>
            </div>
        </div>
    </div>

    <!-- Status Periode Akuntansi & Executive Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Card 1: Status Penguncian -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Penguncian</span>
                <span id="period-lock-indicator" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <h3 id="period-lock-status" class="text-base font-extrabold text-slate-900 mt-2">Terbuka (Aktif)</h3>
        </div>

        <!-- Card 2: Penguncian Terakhir -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tutup Buku Terakhir</span>
            <h3 id="latest-closed-period" class="text-base font-extrabold text-slate-900 mt-2">-</h3>
        </div>

        <!-- Card 3: Pendapatan Berjalan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pendapatan Berjalan</span>
            <h3 id="current-revenue-stat" data-kfx-roll class="text-base font-black text-slate-900 mt-2 font-mono">Rp 0</h3>
        </div>

        <!-- Card 4: Estimasi Laba Bersih -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Estimasi Laba Bersih</span>
            <h3 id="current-profit-stat" data-kfx-roll class="text-base font-black text-emerald-600 mt-2 font-mono">Rp 0</h3>
        </div>
    </div>

    <!-- Area Konfigurasi Penutupan Buku & Preview Jurnal Penutup -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Panel Kiri: Form Parameter Tutup Buku (4 Kolom) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="pb-2 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Parameter Tutup Buku</h3>
            </div>

            <form id="closing-config-form" onsubmit="generateClosingPreview(event)" class="space-y-3.5 text-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Periode Penutupan *</label>
                    <input type="text" id="closing-period-name" required placeholder="Contoh: Tutup Buku September 2026"
                           class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Mulai *</label>
                        <input type="date" id="closing-start-date" required
                               class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Selesai *</label>
                        <input type="date" id="closing-end-date" required
                               class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Akun Penampung Laba Bersih *</label>
                    <select id="closing-target-equity" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                        <option value="3201">[3201] Laba Ditahan (Retained Earnings)</option>
                        <option value="3101">[3101] Modal Pemilik (Owner's Equity)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Dokumen / Notulen</label>
                    <textarea id="closing-notes" rows="2" placeholder="Catatan opsional penutupan buku..."
                              class="w-full p-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <button type="submit" id="btn-preview-closing"
                        class="w-full py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Simulasikan Jurnal Penutup</span>
                </button>
            </form>
        </div>

        <!-- Panel Kanan: Pratinjau Jurnal Penutup & Eksekusi (8 Kolom) -->
        <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Pratinjau Jurnal Penutup (Closing Entries)</h3>
                </div>
                <span id="preview-balance-badge" class="hidden px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">Balanced</span>
            </div>

            <!-- Empty State jika belum disimulasikan -->
            <div id="preview-empty-state" class="py-16 text-center text-slate-400 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-600">Belum Ada Simulasi Jurnal Penutup</p>
                <p class="text-[11px] text-slate-400 max-w-sm mx-auto">Pilih tanggal periode di sebelah kiri lalu klik tombol "Simulasikan Jurnal Penutup" untuk memeriksa draft jurnal.</p>
            </div>

            <!-- Container Hasil Pratinjau -->
            <div id="preview-result-container" class="hidden space-y-4">
                <!-- KPI Mini Hasil Simulasi -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs p-3.5 bg-slate-50/70 rounded-2xl border border-slate-200/60">
                    <div>
                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Pendapatan Ditutup</span>
                        <span id="prev-rev" class="font-extrabold text-slate-900 font-mono">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Beban Ditutup</span>
                        <span id="prev-exp" class="font-extrabold text-slate-900 font-mono">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Laba/Rugi Periode</span>
                        <span id="prev-net" class="font-extrabold font-mono text-emerald-600">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Prive Ditutup</span>
                        <span id="prev-prive" class="font-extrabold text-slate-900 font-mono">Rp 0</span>
                    </div>
                </div>

                <!-- Tabel Item Jurnal Penutup -->
                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 sticky top-0 border-b border-slate-200">
                            <tr class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-2.5 px-3">Tahap</th>
                                <th class="py-2.5 px-3">Kode & Nama Akun</th>
                                <th class="py-2.5 px-3 text-right">Debit (Rp)</th>
                                <th class="py-2.5 px-3 text-right">Kredit (Rp)</th>
                                <th class="py-2.5 px-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="preview-items-body" class="divide-y divide-slate-100 font-mono">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                            <tr>
                                <td colspan="2" class="py-2.5 px-3 text-slate-900 text-right">TOTAL KESEIMBANGAN:</td>
                                <td id="prev-total-debit" class="py-2.5 px-3 text-right text-indigo-700 font-mono">Rp 0</td>
                                <td id="prev-total-credit" class="py-2.5 px-3 text-right text-indigo-700 font-mono">Rp 0</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span id="prev-status-text" class="text-emerald-700 font-bold text-[10px]">SEIMBANG</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Tombol Eksekusi Tutup Buku -->
                <div class="pt-2">
                    <button onclick="executeClosing()" id="btn-execute-closing"
                            class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition-all flex items-center justify-center space-x-2 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Konfirmasi Tutup Buku & Kunci Periode Akuntansi</span>
                    </button>
                    <p class="text-[10px] text-slate-400 text-center mt-2 leading-tight">
                        *Perhatian: Setelah ditutup buku, periode ini akan dikunci secara permanen untuk mencegah perubahan data lampau. Jurnal Penutup otomatis diposting.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Periode Terkunci & Cetak Dokumen Berita Acara -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Riwayat Penguncian Periode (Tutup Buku)</h3>
            </div>
            <button onclick="loadPeriodStatus()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Segarkan</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold tracking-wider">
                        <th class="pb-3 px-3">Periode</th>
                        <th class="pb-3 px-3">Rentang Tanggal</th>
                        <th class="pb-3 px-3 text-right">Pendapatan</th>
                        <th class="pb-3 px-3 text-right">Total Beban</th>
                        <th class="pb-3 px-3 text-right">Laba/Rugi Bersih</th>
                        <th class="pb-3 px-3">No Jurnal Penutup</th>
                        <th class="pb-3 px-3">Ditutup Oleh</th>
                        <th class="pb-3 px-3 text-center">Status</th>
                        <th class="pb-3 px-3 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody id="period-history-body" class="divide-y divide-slate-100">
                    <tr><td colspan="9" class="py-8 text-center text-slate-400">Memuat riwayat periode...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Berita Acara Tutup Buku Formal (Printable) -->
<div id="berita-acara-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-8 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">Berita Acara Penutupan Buku</h4>
                <p class="text-[11px] text-slate-400">Dokumen Kepatuhan Akuntansi SAK-EMKM</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all">
                    Cetak Berita Acara
                </button>
                <button onclick="closeBeritaAcaraModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
            </div>
        </div>

        <div id="berita-acara-content" class="text-xs space-y-4 text-slate-700 leading-relaxed font-serif">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

<script>
    let currentPreviewData = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadPeriodStatus();
    });

    async function loadPeriodStatus() {
        try {
            const res = await fetch('../api/closing.php?action=status');
            const result = await res.json();
            if (!result.success) return;

            const d = result.data;

            // Form Defaults
            document.getElementById('closing-start-date').value = d.suggested_start;
            document.getElementById('closing-end-date').value = d.suggested_end;
            
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const curDate = new Date(d.suggested_end);
            document.getElementById('closing-period-name').value = `Tutup Buku ${monthNames[curDate.getMonth()]} ${curDate.getFullYear()}`;

            // KPI Cards
            document.getElementById('current-revenue-stat').innerText = 'Rp ' + Number(d.current_revenue).toLocaleString('id-ID');
            const profitEl = document.getElementById('current-profit-stat');
            profitEl.innerText = (d.current_profit < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(d.current_profit)).toLocaleString('id-ID');
            profitEl.className = d.current_profit >= 0 ? 'text-base font-black text-emerald-600 mt-2 font-mono' : 'text-base font-black text-rose-600 mt-2 font-mono';

            if (d.latest_lock) {
                document.getElementById('latest-closed-period').innerText = d.latest_lock.period_name;
                const dateEl = document.getElementById('latest-closed-date');
                if (dateEl) dateEl.innerText = `Per: ${d.latest_lock.period_end}`;
            } else {
                document.getElementById('latest-closed-period').innerText = 'Belum Ada';
                const dateEl = document.getElementById('latest-closed-date');
                if (dateEl) dateEl.innerText = '';
            }

            // Render History Table
            const tbody = document.getElementById('period-history-body');
            tbody.innerHTML = '';

            if (!d.history || d.history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-slate-400">Belum ada periode yang ditutup.</td></tr>';
                return;
            }

            d.history.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';

                const netProfit = Number(p.net_profit_loss);
                const profitClass = netProfit >= 0 ? 'text-emerald-700' : 'text-rose-600';

                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-bold text-slate-900">${p.period_name}</td>
                    <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">${p.period_start} s/d ${p.period_end}</td>
                    <td class="py-2.5 px-3 text-right font-mono">Rp ${Number(p.total_revenue).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-right font-mono">Rp ${Number(p.total_expense).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-right font-black font-mono ${profitClass}">Rp ${netProfit.toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 font-mono text-indigo-700 font-bold">${p.closing_journal_no || '-'}</td>
                    <td class="py-2.5 px-3 text-slate-600">${p.closed_by_name || 'Akuntan'}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">Terkunci</span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <button onclick='showBeritaAcara(${JSON.stringify(p)})' class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 font-bold text-[10px] transition-colors">
                            Berita Acara
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {
            console.error('Error load period status:', e);
        }
    }

    async function generateClosingPreview(e) {
        e.preventDefault();
        const start = document.getElementById('closing-start-date').value;
        const end = document.getElementById('closing-end-date').value;
        const targetEquity = document.getElementById('closing-target-equity').value;

        const btn = document.getElementById('btn-preview-closing');
        btn.disabled = true;
        btn.innerText = 'Menghitung Draf Penutup...';

        try {
            const res = await fetch(`../api/closing.php?action=preview&period_start=${start}&period_end=${end}&target_equity=${targetEquity}`);
            const result = await res.json();

            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg><span>Simulasikan Jurnal Penutup</span>`;

            if (!result.success) {
                showToast(result.message || 'Gagal membuat pratinjau tutup buku.', 'error');
                return;
            }

            currentPreviewData = result;

            document.getElementById('preview-empty-state').classList.add('hidden');
            document.getElementById('preview-result-container').classList.remove('hidden');

            // Render KPI
            document.getElementById('prev-rev').innerText = 'Rp ' + Number(result.summary.total_revenue).toLocaleString('id-ID');
            document.getElementById('prev-exp').innerText = 'Rp ' + Number(result.summary.total_expense).toLocaleString('id-ID');
            document.getElementById('prev-net').innerText = (result.summary.net_profit_loss < 0 ? '-' : '') + 'Rp ' + Number(Math.abs(result.summary.net_profit_loss)).toLocaleString('id-ID');
            document.getElementById('prev-prive').innerText = 'Rp ' + Number(result.summary.prive_amount).toLocaleString('id-ID');

            // Render Table Rows
            const tbody = document.getElementById('preview-items-body');
            tbody.innerHTML = '';

            result.closing_entries.forEach(it => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 transition-colors';

                const isDebit = Number(it.debit) > 0;
                const indentClass = isDebit ? 'font-bold text-slate-800' : 'pl-6 text-slate-700 italic';

                tr.innerHTML = `
                    <td class="py-2 px-3 text-[10px] font-sans font-bold text-slate-400">${it.step}</td>
                    <td class="py-2 px-3 ${indentClass}">[${it.code}] ${it.name}</td>
                    <td class="py-2 px-3 text-right font-black ${isDebit ? 'text-indigo-900' : 'text-slate-300'}">${Number(it.debit) > 0 ? 'Rp ' + Number(it.debit).toLocaleString('id-ID') : '-'}</td>
                    <td class="py-2 px-3 text-right font-black ${!isDebit ? 'text-indigo-900' : 'text-slate-300'}">${Number(it.credit) > 0 ? 'Rp ' + Number(it.credit).toLocaleString('id-ID') : '-'}</td>
                    <td class="py-2 px-3 text-[10px] font-sans text-slate-500">${it.memo}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('prev-total-debit').innerText = 'Rp ' + Number(result.total_debit).toLocaleString('id-ID');
            document.getElementById('prev-total-credit').innerText = 'Rp ' + Number(result.total_credit).toLocaleString('id-ID');

            const badge = document.getElementById('preview-balance-badge');
            badge.classList.remove('hidden');

            const btnExec = document.getElementById('btn-execute-closing');
            btnExec.disabled = !result.can_close;
            if (!result.can_close) {
                showToast('Tidak ada transaksi nominal yang perlu ditutup pada rentang tanggal ini.', 'info');
            } else {
                showToast('Simulasi Jurnal Penutup berhasil disusun dan seimbang (Balanced).', 'success');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerText = 'Simulasikan Jurnal Penutup';
            showToast('Kesalahan saat memuat simulasi.', 'error');
        }
    }

    async function executeClosing() {
        if (!currentPreviewData || !currentPreviewData.can_close) {
            showToast('Silakan simulasikan draf jurnal penutup terlebih dahulu.', 'error');
            return;
        }

        const periodName = document.getElementById('closing-period-name').value.trim();
        const start = document.getElementById('closing-start-date').value;
        const end = document.getElementById('closing-end-date').value;
        const targetEquity = document.getElementById('closing-target-equity').value;
        const notes = document.getElementById('closing-notes').value.trim();

        const conf = await showConfirmDialog({
            title: 'Eksekusi Tutup Buku Periode?',
            message: `Periode: <strong>${periodName}</strong> (${start} s/d ${end})<br><br>Setelah dieksekusi, semua transaksi pada periode ini dan sebelumnya akan <strong>DIKUNCI</strong> dan saldo akun nominal pendapatan & beban akan dipindahkan ke Ekuitas.`,
            confirmText: 'Ya, Tutup Buku Sekarang',
            cancelText: 'Batal & Periksa Kembali',
            type: 'danger'
        });
        if (!conf) return;

        const btn = document.getElementById('btn-execute-closing');
        btn.disabled = true;
        btn.innerText = 'Memproses Tutup Buku & Mengunci Periode...';

        try {
            const formData = new FormData();
            formData.append('period_name', periodName);
            formData.append('period_start', start);
            formData.append('period_end', end);
            formData.append('target_equity', targetEquity);
            formData.append('notes', notes);

            const res = await fetch('../api/closing.php?action=execute', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            btn.disabled = false;
            btn.innerText = 'Konfirmasi Tutup Buku & Kunci Periode Akuntansi';

            if (result.success) {
                if (window.KomakFX && typeof window.KomakFX.lockClose === 'function') {
                    await window.KomakFX.lockClose('Periode ' + periodName + ' Berhasil Dikunci');
                }
                showToast(result.message, 'success');
                document.getElementById('preview-result-container').classList.add('hidden');
                document.getElementById('preview-empty-state').classList.remove('hidden');
                currentPreviewData = null;
                loadPeriodStatus();
            } else {
                showToast(result.message || 'Gagal melakukan tutup buku.', 'error');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerText = 'Konfirmasi Tutup Buku & Kunci Periode Akuntansi';
            showToast('Kesalahan server saat memproses tutup buku.', 'error');
        }
    }

    function showBeritaAcara(item) {
        const modal = document.getElementById('berita-acara-modal');
        const content = document.getElementById('berita-acara-content');

        content.innerHTML = `
            <div class="text-center pb-4 border-b border-slate-300">
                <h2 class="text-base font-bold uppercase tracking-wider text-slate-900">BERITA ACARA PENUTUPAN BUKU PERIODIK</h2>
                <p class="text-xs text-slate-500 font-sans mt-0.5">NOMOR DOKUMEN: BA-TB/${item.period_end.replace(/-/g, '')}/${item.id}</p>
            </div>

            <p>Pada hari ini, pembukuan keuangan entitas <strong>PJBL KOMAK MULTI-CABANG</strong> telah resmi melaksanakan proses Tutup Buku (Closing Entries) untuk periode akuntansi:</p>

            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 font-sans space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nama Periode:</span>
                    <span class="font-bold text-slate-900">${item.period_name}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Rentang Waktu:</span>
                    <span class="font-mono text-slate-900">${item.period_start} sampai ${item.period_end}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Pendapatan Usaha:</span>
                    <span class="font-mono font-bold text-slate-900">Rp ${Number(item.total_revenue).toLocaleString('id-ID')}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Beban Usaha & Operasional:</span>
                    <span class="font-mono font-bold text-slate-900">Rp ${Number(item.total_expense).toLocaleString('id-ID')}</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-slate-200">
                    <span class="font-bold text-slate-900">Laba Bersih Dipindahkan ke Ekuitas:</span>
                    <span class="font-mono font-black text-emerald-700">Rp ${Number(item.net_profit_loss).toLocaleString('id-ID')}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Jurnal Penutup (General Ledger):</span>
                    <span class="font-mono font-bold text-indigo-700">${item.closing_journal_no || '-'}</span>
                </div>
            </div>

            <p>Dengan diselesaikannya penutupan buku ini, seluruh akun nominal (Pendapatan dan Beban) telah dinolkan kembali melalui akun perantara Ikhtisar Laba Rugi dan saldo laba bersih telah dibukukan ke pos Ekuitas sesuai Pedoman Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK-EMKM).</p>

            <p>Sistem secara otomatis mengaktifkan status <strong>LOCKED (TERKUNCI)</strong> terhadap periode transaksi tersebut demi menjaga integritas data dan mencegah perubahan retrospektif.</p>

            <div class="grid grid-cols-2 pt-6 text-center font-sans text-xs">
                <div>
                    <p class="text-slate-500">Disusun & Ditutup Oleh:</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-900 underline">${item.closed_by_name || 'Akuntan Keuangan'}</p>
                    <p class="text-[10px] text-slate-400">Bagian Akuntansi & Pembukuan</p>
                </div>
                <div>
                    <p class="text-slate-500">Diketahui & Disahkan Oleh:</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-900 underline">Direktur / Manajemen Pusat</p>
                    <p class="text-[10px] text-slate-400">PJBL Komak Enterprise</p>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
    }

    function closeBeritaAcaraModal() {
        document.getElementById('berita-acara-modal').classList.add('hidden');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
