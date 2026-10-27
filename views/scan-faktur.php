<?php
$pageTitle = 'Pemindaian Faktur & Auto-Jurnal';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<!-- Load Tesseract.js untuk OCR Nyata di Browser -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<div class="space-y-6 w-full max-w-full min-w-0">
    <!-- Header Page Formal -->
    <div class="pb-2 border-b border-slate-200/80">
        <div class="flex items-center space-x-2">
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pemindaian Faktur &amp; Auto-Jurnal</h2>
        </div>
    </div>



    <!-- Upload & Scanner Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 w-full max-w-full min-w-0">
        <!-- Area Upload Foto Faktur (5 Kolom) -->
        <div class="lg:col-span-5 bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4 min-w-0 max-w-full overflow-hidden">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">1. Unggah Faktur / Dokumen</h3>
                <span class="text-[10px] text-indigo-700 bg-indigo-50 font-bold px-2 py-0.5 rounded-full border border-indigo-200/60 font-mono">JPG, PNG, PDF</span>
            </div>
            
            <form id="upload-scan-form" onsubmit="handleScanUpload(event)" class="space-y-4">
                <div class="border-2 border-dashed border-indigo-200/80 hover:border-indigo-500 rounded-3xl p-6 text-center bg-indigo-50/20 hover:bg-indigo-50/40 transition-all cursor-pointer relative group"
                     onclick="document.getElementById('invoice-file-input').click()">
                    <input type="file" id="invoice-file-input" name="invoice_file" accept="image/*,application/pdf" class="hidden" onchange="previewFile(this)">
                    <div id="upload-placeholder" class="space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-xs font-bold text-slate-800">Klik untuk Pilih Foto atau File PDF Faktur</p>
                        <p class="text-[11px] text-slate-400 font-medium">Bisa foto struk belanja, nota toko, atau dokumen PDF resmi s/d 25MB</p>
                    </div>

                    <!-- Preview Foto Gambar -->
                    <img id="image-preview" class="hidden max-h-56 mx-auto rounded-2xl shadow-md border border-slate-200 object-contain" alt="Preview Faktur">

                    <!-- Preview Dokumen PDF -->
                    <div id="pdf-preview-box" class="hidden p-4 rounded-2xl bg-rose-50/70 border border-rose-200 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-6 4h4"/></svg>
                        </div>
                        <div>
                            <p id="pdf-preview-name" class="text-xs font-bold text-slate-900 truncate max-w-xs mx-auto">dokumen.pdf</p>
                            <span id="pdf-preview-size" class="text-[11px] font-mono text-slate-500">0 KB</span>
                            <span class="inline-block ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">PDF Terbaca</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Klik kotak untuk mengganti file dokumen</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bebankan ke Cabang:</label>
                    <select id="scan-branch-id" name="branch_id" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" id="btn-scan-ai" disabled
                        class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 disabled:bg-slate-300 disabled:from-slate-300 disabled:to-slate-300 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="scan-btn-text">Pindai & Ekstrak Nilai Faktur</span>
                </button>
            </form>
        </div>

        <!-- Area Hasil Analisis & Form Verifikasi (7 Kolom) -->
        <div class="lg:col-span-7 bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between min-w-0 max-w-full overflow-hidden">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">2. Hasil Ekstraksi & Form Verifikasi</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Periksa dan sesuaikan data sebelum diposting ke Jurnal Umum.</p>
                    </div>
                    <span id="ai-source-badge" class="hidden px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">Smart Engine</span>
                </div>

                <div id="ai-loading" class="hidden py-16 text-center space-y-3">
                    <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs font-bold text-indigo-900" id="ai-loading-text">Sedang membaca teks & nominal faktur...</p>
                    <p class="text-[11px] text-slate-400">Menganalisis vendor, tanggal, subtotal, dan total pengeluaran.</p>
                </div>

                <div id="ai-empty-state" class="py-16 text-center text-slate-400 text-xs">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="font-semibold text-slate-600 block">Belum Ada Faktur yang Dipindai</span>
                    <span class="text-[11px] text-slate-400">Unggah foto faktur di sebelah kiri atau pilih dari riwayat draft di bawah.</span>
                </div>

                <!-- Form Verifikasi Jurnal -->
                <div id="verification-form-container" class="hidden space-y-3 pt-3">
                    <input type="hidden" id="verified-scan-id">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Toko / Vendor</label>
                            <input type="text" id="res-vendor" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Faktur / Nota</label>
                            <input type="text" id="res-invoice-no" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Dokumen Nota</label>
                            <input type="date" id="res-date" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-emerald-700 mb-1">Tanggal Posting Jurnal (Periode Berjalan) *Wajib</label>
                            <input type="date" id="res-posting-date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 text-xs rounded-xl border border-emerald-400 bg-emerald-50/40 font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-indigo-700 mb-1">Total Nominal Bayar (Rp) *Wajib</label>
                            <input type="number" id="res-total" oninput="handleTotalChange()" class="w-full p-2.5 text-sm rounded-xl border-2 border-indigo-400 font-black text-indigo-900 bg-indigo-50/50 font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Subtotal (Rp)</label>
                            <input type="number" id="res-subtotal" class="w-full p-2 text-xs rounded-xl border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">PPN Pajak (Rp)</label>
                            <input type="number" id="res-tax" value="0" class="w-full p-2 text-xs rounded-xl border border-slate-200 font-mono">
                        </div>
                    </div>

                    <!-- Pemetaan Jurnal Akuntansi (Double Entry) -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <h4 class="text-xs font-extrabold text-slate-800">Pemetaan Akun Jurnal (Debit & Kredit):</h4>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                (D) Akun Debit - Beban Pengeluaran / Persediaan:
                            </label>
                            <select id="select-debit-account" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                                <!-- Populated dynamically -->
                            </select>
                            <p class="text-[10px] text-indigo-700 bg-indigo-50/80 p-2 rounded-xl mt-1.5 leading-relaxed border border-indigo-100">
                                💡 <strong>Panduan Laba Rugi vs Neraca:</strong> Pilih akun bertanda <strong>[Laba Rugi]</strong> (seperti <code>5101 Beban Pokok</code> atau <code>6104 Beban Operasional</code>) agar belanja faktur ini langsung muncul sebagai beban di <strong>Laporan Laba Rugi</strong>. Pilih akun <strong>[Neraca]</strong> (seperti <code>1105 Persediaan</code>) jika dicatat sebagai persediaan fisik yang masuk ke <strong>Neraca</strong>.
                            </p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                (K) Akun Kredit - Sumber Dana (Kas / Utang):
                            </label>
                            <select id="select-credit-account" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                                <!-- Populated dynamically -->
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan / Memo Jurnal:</label>
                            <input type="text" id="journal-memo" class="w-full p-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Approval (1-Klik Posting) -->
            <div id="approve-actions" class="hidden pt-4 border-t border-slate-100">
                <button onclick="approveDraftInvoice()" id="btn-approve"
                        class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition-all flex items-center justify-center space-x-2 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Verifikasi & Posting ke Jurnal Umum Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Riwayat Faktur & Kontrol Status Penjurnalan (Bisa Terima / Tolak Langsung) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 shadow-xs w-full max-w-full min-w-0 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Riwayat Faktur & Kontrol Status Penjurnalan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Anda dapat memverifikasi draf yang tertunda, menerima, atau menolak/menghapus faktur.</p>
            </div>
            <button onclick="loadScanHistory(); loadQuota();" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1.5 transition-colors self-start sm:self-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Segarkan Data</span>
            </button>
        </div>

        <div class="w-full max-w-full overflow-x-auto touch-scroll">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold tracking-wider">
                        <th class="pb-3 px-3">Tanggal</th>
                        <th class="pb-3 px-3">Vendor / Toko</th>
                        <th class="pb-3 px-3">No Faktur</th>
                        <th class="pb-3 px-3">Akun Debit</th>
                        <th class="pb-3 px-3">Akun Kredit</th>
                        <th class="pb-3 px-3 text-right">Nominal</th>
                        <th class="pb-3 px-3 text-center">Status</th>
                        <th class="pb-3 px-3 text-center">Aksi / Kontrol</th>
                    </tr>
                </thead>
                <tbody id="scan-history-table" class="divide-y divide-slate-100">
                    <tr><td colspan="8" class="py-8 text-center text-slate-400">Memuat riwayat faktur...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Verifikasi & Terima Faktur Langsung dari Riwayat -->
<div id="review-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Verifikasi & Terima Faktur</h4>
                <p class="text-[11px] text-slate-400">Tinjau draft sebelum diposting ke jurnal</p>
            </div>
            <div class="flex items-center space-x-2">
                <a id="modal-doc-link" target="_blank" class="hidden"></a>
                <button onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">&times;</button>
            </div>
        </div>

        <input type="hidden" id="modal-scan-id">

        <div class="space-y-3.5 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Toko / Vendor</label>
                <input type="text" id="modal-vendor" class="w-full p-2.5 rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No Faktur / Nota</label>
                    <input type="text" id="modal-invoice-no" class="w-full p-2.5 rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Dokumen Nota</label>
                    <input type="date" id="modal-date" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-emerald-700 mb-1">Tanggal Posting Jurnal (Periode Berjalan) *Wajib</label>
                <input type="date" id="modal-posting-date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 rounded-xl border border-emerald-400 bg-emerald-50/40 font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-indigo-700 mb-1">Total Nominal Bayar (Rp) *Wajib</label>
                <input type="number" id="modal-total" class="w-full p-2.5 text-sm rounded-xl border-2 border-indigo-400 font-black text-indigo-900 bg-indigo-50/50 font-mono focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">(D) Akun Debit - Pengeluaran / Beban:</label>
                <select id="modal-debit-account" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500"></select>
                <p class="text-[10px] text-indigo-700 bg-indigo-50/80 p-2 rounded-xl mt-1.5 leading-relaxed border border-indigo-100">
                    💡 Pilih akun <strong>[Laba Rugi]</strong> agar belanja ini masuk ke <strong>Laporan Laba Rugi</strong>, atau <strong>[Neraca]</strong> untuk aset persediaan.
                </p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">(K) Akun Kredit - Sumber Dana:</label>
                <select id="modal-credit-account" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500"></select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Jurnal:</label>
                <input type="text" id="modal-memo" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
            <button onclick="submitModalApprove()" id="btn-modal-approve" class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition-all">
                ✓ Setujui & Posting ke Jurnal Umum
            </button>
            <button onclick="closeReviewModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Batal
            </button>
        </div>
    </div>
</div>



<script>
    let rawScanData = null;
    let accountsList = [];

    document.addEventListener('DOMContentLoaded', async () => {
        await loadQuota();
        await loadAccounts();
        await loadScanHistory();
    });

    async function loadQuota() {
        try {
            const res = await fetch('../api/scan_invoice.php?action=quota');
            const result = await res.json();
            const quotaEl = document.getElementById('quota-remaining-text');
            if (result.success && quotaEl) {
                quotaEl.innerText = `${Number(result.remaining).toLocaleString('id-ID')} / ${Number(result.max_daily).toLocaleString('id-ID')} kali`;
            }
        } catch (e) {}
    }

    async function loadAccounts() {
        try {
            const res = await fetch('../api/accounts.php');
            const result = await res.json();
            if (result.success) {
                accountsList = result.data;
                populateAccountDropdowns();
            }
        } catch (e) {
            console.error('Error load accounts:', e);
        }
    }

    function populateAccountDropdowns() {
        const debitSelect = document.getElementById('select-debit-account');
        const creditSelect = document.getElementById('select-credit-account');
        const modalDebit = document.getElementById('modal-debit-account');
        const modalCredit = document.getElementById('modal-credit-account');

        debitSelect.innerHTML = '';
        creditSelect.innerHTML = '';
        if (modalDebit) modalDebit.innerHTML = '';
        if (modalCredit) modalCredit.innerHTML = '';

        // Grup Akun Debit
        const debitGroupDefs = {
            'Beban_Pokok': '📦 Beban Pokok Layanan / HPP (Masuk Laba Rugi)',
            'Beban_Operasional': '📋 Beban Operasional & Biaya (Masuk Laba Rugi)',
            'Aset': '🏢 Aset & Persediaan Fisik (Masuk Neraca)',
            'Liabilitas': '⚖️ Liabilitas & Utang (Masuk Neraca)'
        };

        const debitOGs = {};
        for (const k in debitGroupDefs) {
            debitOGs[k] = document.createElement('optgroup');
            debitOGs[k].label = debitGroupDefs[k];
        }

        // Grup Akun Kredit
        const creditOGKas = document.createElement('optgroup');
        creditOGKas.label = '💵 Kas Kasir & Bank (Sumber Dana Pembayaran)';
        const creditOGUtang = document.createElement('optgroup');
        creditOGUtang.label = '⚖️ Utang Usaha / Vendor (Belum Lunas / Kredit)';
        const creditOGLain = document.createElement('optgroup');
        creditOGLain.label = 'Pos Akun Lainnya';

        accountsList.forEach(a => {
            const optD = document.createElement('option');
            optD.value = a.id;
            const isIncomeStatement = (a.category === 'Beban_Pokok' || a.category === 'Beban_Operasional' || a.category === 'Pendapatan');
            const targetReport = isIncomeStatement ? '[Laba Rugi]' : '[Neraca]';
            optD.innerText = `[${a.code}] ${a.name} ${targetReport}`;

            if (debitOGs[a.category]) {
                debitOGs[a.category].appendChild(optD);
            } else {
                debitSelect.appendChild(optD);
            }

            const optC = document.createElement('option');
            optC.value = a.id;
            optC.innerText = `[${a.code}] ${a.name} (${a.category})`;

            if (a.category === 'Aset' && (a.code.startsWith('1101') || a.code.startsWith('1102') || a.code.startsWith('1103'))) {
                creditOGKas.appendChild(optC);
            } else if (a.category === 'Liabilitas') {
                creditOGUtang.appendChild(optC);
            } else {
                creditOGLain.appendChild(optC);
            }
        });

        // Masukkan optgroups ke select debit
        ['Beban_Pokok', 'Beban_Operasional', 'Aset', 'Liabilitas'].forEach(k => {
            if (debitOGs[k] && debitOGs[k].children.length > 0) {
                debitSelect.appendChild(debitOGs[k]);
                if (modalDebit) modalDebit.appendChild(debitOGs[k].cloneNode(true));
            }
        });

        // Masukkan optgroups ke select credit
        [creditOGKas, creditOGUtang, creditOGLain].forEach(og => {
            if (og && og.children.length > 0) {
                creditSelect.appendChild(og);
                if (modalCredit) modalCredit.appendChild(og.cloneNode(true));
            }
        });
    }

    function previewFile(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            const img = document.getElementById('image-preview');
            const pdfBox = document.getElementById('pdf-preview-box');
            const placeholder = document.getElementById('upload-placeholder');
            
            placeholder.classList.add('hidden');
            document.getElementById('btn-scan-ai').disabled = false;

            if (isPdf) {
                img.classList.add('hidden');
                pdfBox.classList.remove('hidden');
                document.getElementById('pdf-preview-name').innerText = file.name;
                const sizeKb = (file.size / 1024).toFixed(1);
                const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
                document.getElementById('pdf-preview-size').innerText = sizeStr;
            } else {
                pdfBox.classList.add('hidden');
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    }

    async function handleScanUpload(e) {
        e.preventDefault();
        const fileInput = document.getElementById('invoice-file-input');
        if (!fileInput.files || !fileInput.files[0]) {
            showToast('Silakan pilih foto faktur terlebih dahulu.', 'error');
            return;
        }

        const btn = document.getElementById('btn-scan-ai');
        const btnText = document.getElementById('scan-btn-text');
        const loading = document.getElementById('ai-loading');
        const loadingText = document.getElementById('ai-loading-text');
        const emptyState = document.getElementById('ai-empty-state');
        const formContainer = document.getElementById('verification-form-container');
        const approveActions = document.getElementById('approve-actions');

        btn.disabled = true;
        btnText.innerText = 'Sedang Memindai...';
        emptyState.classList.add('hidden');
        formContainer.classList.add('hidden');
        approveActions.classList.add('hidden');
        loading.classList.remove('hidden');

        try {
            loadingText.innerText = 'Sistem sedang membaca dokumen digital...';

            const previewBox = document.getElementById('image-preview')?.parentElement || document.getElementById('upload-placeholder')?.parentElement;
            let scanPromise = Promise.resolve();
            if (window.KomakFX && typeof window.KomakFX.scan === 'function' && previewBox) {
                scanPromise = window.KomakFX.scan(previewBox, 1800);
            }

            const formData = new FormData(document.getElementById('upload-scan-form'));

            const [res] = await Promise.all([
                fetch('../api/scan_invoice.php?action=upload_scan', {
                    method: 'POST',
                    body: formData
                }),
                scanPromise
            ]);
            const result = await res.json();

            loading.classList.add('hidden');
            btn.disabled = false;
            btnText.innerText = 'Pindai & Ekstrak Nilai Faktur';

            if (result.success && result.data) {
                rawScanData = result.data;
                showToast(result.message, 'success');

                const badge = document.getElementById('ai-source-badge');
                badge.innerText = result.data.ai_source || 'Smart OCR';
                badge.classList.remove('hidden');

                document.getElementById('verified-scan-id').value = result.data.id;

                if (result.data.debit_account_id) {
                    document.getElementById('select-debit-account').value = result.data.debit_account_id;
                }
                if (result.data.credit_account_id) {
                    document.getElementById('select-credit-account').value = result.data.credit_account_id;
                }

                formContainer.classList.remove('hidden');
                approveActions.classList.remove('hidden');

                if (window.KomakFX && typeof window.KomakFX.fill === 'function') {
                    await window.KomakFX.fill([
                        [document.getElementById('res-vendor'), result.data.vendor_name || ''],
                        [document.getElementById('res-invoice-no'), result.data.invoice_no || ''],
                        [document.getElementById('res-date'), result.data.invoice_date || ''],
                        [document.getElementById('res-total'), result.data.total_amount || 0],
                        [document.getElementById('res-subtotal'), result.data.subtotal || result.data.total_amount || 0],
                        [document.getElementById('res-tax'), result.data.tax_amount || 0],
                        [document.getElementById('journal-memo'), result.data.summary || `Pembayaran Faktur ${result.data.vendor_name || ''}`]
                    ]);
                } else {
                    document.getElementById('res-vendor').value = result.data.vendor_name || '';
                    document.getElementById('res-invoice-no').value = result.data.invoice_no || '';
                    document.getElementById('res-date').value = result.data.invoice_date || '';
                    document.getElementById('res-subtotal').value = result.data.subtotal || result.data.total_amount || 0;
                    document.getElementById('res-tax').value = result.data.tax_amount || 0;
                    document.getElementById('res-total').value = result.data.total_amount || 0;
                    document.getElementById('journal-memo').value = result.data.summary || `Pembayaran Faktur ${result.data.vendor_name || ''}`;
                }

                loadScanHistory();
                loadQuota();
            } else {
                showToast(result.message || 'Gagal memproses faktur.', 'error');
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            loading.classList.add('hidden');
            btn.disabled = false;
            btnText.innerText = 'Pindai & Ekstrak Nilai Faktur';
            showToast('Kesalahan saat memproses faktur.', 'error');
            emptyState.classList.remove('hidden');
        }
    }

    function handleTotalChange() {
        const total = Number(document.getElementById('res-total').value) || 0;
        const tax = Number(document.getElementById('res-tax').value) || 0;
        document.getElementById('res-subtotal').value = Math.max(0, total - tax);
    }

    async function approveDraftInvoice() {
        const scanId = document.getElementById('verified-scan-id').value;
        const debitId = document.getElementById('select-debit-account').value;
        const creditId = document.getElementById('select-credit-account').value;
        const memo = document.getElementById('journal-memo').value;
        
        const vendor = document.getElementById('res-vendor').value.trim();
        const invoiceNo = document.getElementById('res-invoice-no').value.trim();
        const invoiceDate = document.getElementById('res-date').value;
        const total = Number(document.getElementById('res-total').value) || 0;
        const tax = Number(document.getElementById('res-tax').value) || 0;

        if (total <= 0) {
            showToast('Nominal total bayar harus lebih dari Rp 0.', 'error');
            document.getElementById('res-total').focus();
            return;
        }

        const btn = document.getElementById('btn-approve');
        btn.disabled = true;
        btn.innerText = 'Memposting ke Jurnal Umum...';

        try {
            const formData = new FormData();
            const postingDate = document.getElementById('res-posting-date')?.value || new Date().toISOString().split('T')[0];
            formData.append('scan_id', scanId);
            formData.append('debit_account_id', debitId);
            formData.append('credit_account_id', creditId);
            formData.append('vendor_name', vendor);
            formData.append('invoice_no', invoiceNo);
            formData.append('invoice_date', invoiceDate);
            formData.append('posting_date', postingDate);
            formData.append('total_amount', total);
            formData.append('tax_amount', tax);
            formData.append('description', memo);

            const res = await fetch('../api/scan_invoice.php?action=approve', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                if (window.KomakFX && typeof window.KomakFX.stamp === 'function') {
                    window.KomakFX.stamp(document.getElementById('verification-form-container'), 'POSTED');
                }
                showToast(result.message, 'success');
                document.getElementById('approve-actions').innerHTML = `
                    <div class="p-4 bg-emerald-50 rounded-2xl text-center text-xs font-bold text-emerald-800 border border-emerald-200 space-y-2.5">
                        <div>✓ Berhasil Terposting ke Jurnal Umum (${result.journal_no}) senilai Rp ${Number(total).toLocaleString('id-ID')}</div>
                        <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                            <a href="jurnal.php?search=${encodeURIComponent(result.journal_no)}&start_date=2020-01-01" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-emerald-700 text-white hover:bg-emerald-800 font-bold text-xs transition-colors shadow-xs">
                                <span>Buka Jurnal Umum &rarr;</span>
                            </a>
                            <a href="buku-besar.php?account_id=${debitId}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 font-bold text-xs transition-colors shadow-xs">
                                <span>Buka Buku Besar &rarr;</span>
                            </a>
                            <a href="laporan.php" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-800 text-white hover:bg-slate-900 font-bold text-xs transition-colors shadow-xs">
                                <span>Buka Laporan Keuangan &rarr;</span>
                            </a>
                        </div>
                    </div>
                `;
                loadScanHistory();
            } else {
                showToast(result.message || 'Gagal posting jurnal.', 'error');
                btn.disabled = false;
                btn.innerText = 'Verifikasi & Posting ke Jurnal Umum Sekarang';
            }
        } catch (err) {
            showToast('Kesalahan server saat memposting jurnal.', 'error');
            btn.disabled = false;
            btn.innerText = 'Verifikasi & Posting ke Jurnal Umum Sekarang';
        }
    }

    async function loadScanHistory() {
        try {
            const res = await fetch('../api/scan_invoice.php?action=list');
            const result = await res.json();
            if (!result.success) return;

            const tbody = document.getElementById('scan-history-table');
            tbody.innerHTML = '';

            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-400">Belum ada riwayat faktur.</td></tr>';
                return;
            }

            result.data.forEach(s => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 transition-colors';
                
                const isApproved = s.status === 'approved';
                const statusBadge = isApproved 
                    ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">Terposting (${s.journal_no || 'JRN'})</span>`
                    : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">Draft Menunggu</span>`;

                let actionButtons = '';
                if (!isApproved) {
                    actionButtons = `
                        <div class="flex items-center justify-center space-x-1.5">
                            <button onclick="openReviewModal(${s.id})" title="Verifikasi & Terima Jurnal" class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition-colors shadow-xs">
                                Terima
                            </button>
                            <button onclick="deleteInvoice(${s.id})" title="Tolak & Hapus Faktur" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-bold text-[10px] transition-colors">
                                Tolak
                            </button>
                        </div>
                    `;
                } else {
                    actionButtons = `
                        <div class="flex items-center justify-center space-x-1.5">
                            <a href="jurnal.php?search=${encodeURIComponent(s.journal_no || '')}&start_date=2020-01-01" title="Buka Jurnal Transaksi" class="px-2 py-1 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[10px] font-bold border border-indigo-200/60 transition-colors">
                                Jurnal
                            </a>
                            <a href="buku-besar.php?account_id=${s.debit_account_id}" title="Buka Buku Besar Akun Debit" class="px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[10px] font-bold border border-emerald-200/60 transition-colors">
                                Buku Besar
                            </a>
                            <button onclick="deleteInvoice(${s.id})" title="Hapus Jurnal & Faktur Ini" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    `;
                }

                const docTypeBadge = s.is_pdf
                    ? `<a href="${s.image_url}" target="_blank" title="Buka Dokumen PDF" class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[10px] border border-rose-200/80 transition-colors ml-1.5 shrink-0">
                         <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                         <span>PDF</span>
                       </a>`
                    : `<a href="${s.image_url}" target="_blank" title="Lihat Foto Struk" class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10px] border border-indigo-200/80 transition-colors ml-1.5 shrink-0">
                         <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                         <span>Foto</span>
                       </a>`;

                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-slate-600 font-mono">${s.invoice_date || s.created_at.slice(0, 10)}</td>
                    <td class="py-2.5 px-3 font-bold text-slate-900 flex items-center justify-between">
                        <span class="truncate max-w-[140px]">${s.vendor_name}</span>
                        ${docTypeBadge}
                    </td>
                    <td class="py-2.5 px-3 text-slate-500 font-mono">${s.invoice_no || '-'}</td>
                    <td class="py-2.5 px-3 text-slate-700 font-medium">
                        ${s.debit_account_name ? `<a href="buku-besar.php?account_id=${s.debit_account_id}" title="Buka Buku Besar Akun [${s.debit_account_code}]" class="text-indigo-600 hover:underline font-semibold">[${s.debit_account_code}] ${s.debit_account_name}</a>` : '-'}
                    </td>
                    <td class="py-2.5 px-3 text-slate-700 font-medium">
                        ${s.credit_account_name ? `<a href="buku-besar.php?account_id=${s.credit_account_id}" title="Buka Buku Besar Akun [${s.credit_account_code}]" class="text-slate-800 hover:underline font-semibold">[${s.credit_account_code}] ${s.credit_account_name}</a>` : '-'}
                    </td>
                    <td class="py-2.5 px-3 text-right font-black text-slate-900 font-mono">Rp ${Number(s.total_amount).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-center">${statusBadge}</td>
                    <td class="py-2.5 px-3 text-center">${actionButtons}</td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {
            console.error('Error load history:', e);
        }
    }

    async function openReviewModal(scanId) {
        try {
            const res = await fetch(`../api/scan_invoice.php?action=get&id=${scanId}`);
            const result = await res.json();
            if (!result.success || !result.data) {
                showToast('Gagal memuat detail faktur.', 'error');
                return;
            }

            const d = result.data;
            document.getElementById('modal-scan-id').value = d.id;
            document.getElementById('modal-vendor').value = d.vendor_name || '';
            document.getElementById('modal-invoice-no').value = d.invoice_no || '';
            document.getElementById('modal-date').value = d.invoice_date || '';
            const modalPostingDate = document.getElementById('modal-posting-date');
            if (modalPostingDate) {
                modalPostingDate.value = new Date().toISOString().slice(0, 10);
            }
            document.getElementById('modal-total').value = d.total_amount || 0;
            document.getElementById('modal-memo').value = `Pembayaran Faktur ${d.vendor_name}`;

            if (d.debit_account_id) {
                document.getElementById('modal-debit-account').value = d.debit_account_id;
            }
            if (d.credit_account_id) {
                document.getElementById('modal-credit-account').value = d.credit_account_id;
            }

            // Atur link dokumen lampiran asli (PDF atau Foto)
            const docLink = document.getElementById('modal-doc-link');
            if (docLink && d.image_url) {
                docLink.href = d.image_url;
                docLink.className = d.is_pdf
                    ? "inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-[11px] font-bold border border-rose-200/80 transition-colors"
                    : "inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-bold border border-indigo-200/80 transition-colors";
                docLink.innerHTML = d.is_pdf
                    ? `<svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><span>Buka File PDF</span>`
                    : `<svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg><span>Lihat Foto Asli</span>`;
                docLink.classList.remove('hidden');
            }

            document.getElementById('review-modal').classList.remove('hidden');
        } catch (e) {
            showToast('Kesalahan saat memuat faktur.', 'error');
        }
    }

    function closeReviewModal() {
        document.getElementById('review-modal').classList.add('hidden');
    }

    async function submitModalApprove() {
        const scanId = document.getElementById('modal-scan-id').value;
        const debitId = document.getElementById('modal-debit-account').value;
        const creditId = document.getElementById('modal-credit-account').value;
        const memo = document.getElementById('modal-memo').value;
        const vendor = document.getElementById('modal-vendor').value.trim();
        const invoiceNo = document.getElementById('modal-invoice-no').value.trim();
        const invoiceDate = document.getElementById('modal-date').value;
        const postingDate = document.getElementById('modal-posting-date')?.value || new Date().toISOString().slice(0, 10);
        const total = Number(document.getElementById('modal-total').value) || 0;

        if (total <= 0) {
            showToast('Nominal total bayar harus lebih dari Rp 0.', 'error');
            return;
        }

        const btn = document.getElementById('btn-modal-approve');
        btn.disabled = true;
        btn.innerText = 'Memposting ke Jurnal Umum...';

        try {
            const formData = new FormData();
            formData.append('scan_id', scanId);
            formData.append('debit_account_id', debitId);
            formData.append('credit_account_id', creditId);
            formData.append('vendor_name', vendor);
            formData.append('invoice_no', invoiceNo);
            formData.append('invoice_date', invoiceDate);
            formData.append('posting_date', postingDate);
            formData.append('total_amount', total);
            formData.append('description', memo);

            const res = await fetch('../api/scan_invoice.php?action=approve', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            btn.disabled = false;
            btn.innerText = '✓ Setujui & Posting ke Jurnal Umum';

            if (result.success) {
                showToast(result.message, 'success');
                closeReviewModal();
                loadScanHistory();
            } else {
                showToast(result.message || 'Gagal posting jurnal.', 'error');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerText = '✓ Setujui & Posting ke Jurnal Umum';
            showToast('Kesalahan server saat memposting jurnal.', 'error');
        }
    }

    async function deleteInvoice(scanId) {
        const confirmed = await showConfirmDialog({
            title: 'Tolak & Hapus Faktur?',
            message: 'Apakah Anda yakin ingin menolak dan menghapus draf faktur ini dari sistem? Tindakan ini tidak dapat dibatalkan.',
            confirmText: 'Ya, Tolak & Hapus',
            cancelText: 'Batal',
            type: 'danger'
        });
        if (!confirmed) return;

        try {
            const formData = new FormData();
            formData.append('scan_id', scanId);

            const res = await fetch('../api/scan_invoice.php?action=delete', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                showToast(result.message, 'success');
                loadScanHistory();
                loadQuota();
            } else {
                showToast(result.message || 'Gagal menghapus faktur.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan saat menghapus faktur.', 'error');
        }
    }


</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
