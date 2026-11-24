<?php
$pageTitle = 'Stok dan Transfer Persediaan';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-white/10">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Stok dan Transfer Persediaan</h2>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="openTransferModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-600 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                <i class="fa-solid fa-plus mr-1.5"></i>
                Transfer Persediaan Baru
            </button>
        </div>
    </div>

    <!-- Alert / Notice Info -->
    <div class="p-4 rounded-2xl bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/40 flex items-start gap-3">
        <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 text-sm mt-0.5">
            <i class="fa-solid fa-soap"></i>
        </span>
        <div class="text-xs text-blue-900 dark:text-blue-300">
            <span class="font-bold block text-sm mb-0.5">Pengelolaan Stok &amp; Mutasi Persediaan Operasional</span>
            Kelola jumlah stok bahan operasional laundry (sabun cair, deterjen, softener, pewangi parfum, kemasan plastik) di setiap cabang secara real-time, perbarui stok persediaan kapan saja melalui tombol Edit Stok, dan lakukan pengiriman antar cabang dengan pencatatan audit log lengkap.
        </div>
    </div>

    <!-- SECTION 1: Ringkasan Stok Persediaan di Seluruh Cabang -->
    <div class="bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-white/5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-blue-600 text-xs"></i>
                    Status Stok Persediaan per Cabang
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Pantau, perbarui stok, dan kirim persediaan antara Gudang Pusat dan Cabang Outlet</p>
            </div>
            <button onclick="loadConsumablesSummary()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                Segarkan Data
            </button>
        </div>

        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[660px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/70 dark:bg-white/[0.02]">
                        <th class="py-3 px-4">Nama Persediaan</th>
                        <?php foreach ($activeBranches as $b): ?>
                            <th class="py-3 px-4 text-center"><?= htmlspecialchars($b['name']) ?></th>
                        <?php endforeach; ?>
                        <th class="py-3 px-4 text-center">Total Jaringan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody id="consumables-summary-tbody" class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Memuat stok keperluan cuci...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: Riwayat Transfer Antar Cabang -->
    <div class="bg-white dark:bg-[#11182c] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-white/5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600 text-xs"></i>
                    Riwayat Pengiriman &amp; Transfer Stok
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Arsip surat jalan dan log perpindahan bahan antar outlet</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" id="filter-search-transfer" oninput="filterTransferTable()" placeholder="Cari No. Transfer..." class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[680px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/70 dark:bg-white/[0.02]">
                        <th class="py-3 px-4">No. Dokumen &amp; Tanggal</th>
                        <th class="py-3 px-4">Rute Pengiriman</th>
                        <th class="py-3 px-4">Rincian Barang Ditransfer</th>
                        <th class="py-3 px-4 text-center">Total Unit</th>
                        <th class="py-3 px-4">Petugas / Catatan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Surat Jalan</th>
                    </tr>
                </thead>
                <tbody id="transfer-history-tbody" class="divide-y divide-slate-100 dark:divide-white/5">
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Memuat riwayat transfer...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: Form Transfer Keperluan Cuci Baru -->
<div id="transfer-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-[#11182c] rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl space-y-5 my-8 border border-slate-200 dark:border-white/10">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-dolly"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base text-slate-900 dark:text-white">Form Pengiriman Keperluan Cuci</h4>
                    <p class="text-[11px] text-slate-400">Kirim sabun, deterjen, softener, dan packaging ke cabang tujuan</p>
                </div>
            </div>
            <button onclick="closeTransferModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
        </div>

        <form id="transfer-form" onsubmit="submitStockTransfer(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cabang Asal (Pengirim)</label>
                    <select id="modal-from-branch" onchange="onSourceBranchChanged()" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500" required>
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cabang Tujuan (Penerima)</label>
                    <select id="modal-to-branch" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500" required>
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pengiriman</label>
                    <input type="date" id="modal-transfer-date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                    <input type="text" id="modal-transfer-notes" placeholder="Contoh: Pasokan rutin sabun & deterjen mingguan" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Items Table -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Daftar Bahan Keperluan Cuci</label>
                    <button type="button" onclick="addTransferItemRow()" class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-100 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        Tambah Baris
                    </button>
                </div>

                <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-slate-50/50 dark:bg-white/[0.02]">
                    <div id="transfer-items-container" class="divide-y divide-slate-200 dark:divide-slate-700 p-2 space-y-2">
                        <!-- Dynamic Item Rows -->
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-white/10">
                <button type="button" onclick="closeTransferModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-submit-transfer" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Proses &amp; Kurangi Stok
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Stok Persediaan per Cabang -->
<div id="edit-stock-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-[#11182c] rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 my-8 border border-slate-200 dark:border-white/10">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">Edit Stok Persediaan</h4>
                    <p id="edit-stock-subtitle" class="text-[11px] text-slate-400">Perbarui jumlah stok terbaru di masing-masing cabang</p>
                </div>
            </div>
            <button type="button" onclick="closeEditStockModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
        </div>

        <form id="edit-stock-form" onsubmit="submitEditStock(event)" class="space-y-4">
            <input type="hidden" id="edit-stock-product-id" value="">

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/10 flex items-center justify-between">
                <div>
                    <div id="edit-stock-product-name" class="text-xs font-extrabold text-slate-900 dark:text-white">-</div>
                    <div id="edit-stock-product-meta" class="text-[11px] text-slate-400 font-mono mt-0.5">-</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Total Baru</div>
                    <div id="edit-stock-live-total" class="text-sm font-black font-mono text-indigo-600 dark:text-indigo-400">0</div>
                </div>
            </div>

            <div id="edit-stock-branches-container" class="space-y-2.5">
                <!-- Dynamic branch stock inputs -->
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Alasan Pembaruan</label>
                <input type="text" id="edit-stock-reason" placeholder="Contoh: Restok pembelian baru / Hasil stok opname" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-white/10">
                <button type="button" onclick="closeEditStockModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-save-edit-stock" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>Simpan Perubahan Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Surat Jalan / Bukti Transfer Cetak -->
<div id="surat-jalan-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#11182c] rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-white/10">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10">
            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">Surat Jalan Keperluan Cuci</h4>
            <button onclick="closeSuratJalanModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none cursor-pointer">&times;</button>
        </div>

        <div id="surat-jalan-content" class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs space-y-3 font-mono">
            <!-- Rendered by JS -->
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button onclick="closeSuratJalanModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300">Tutup</button>
            <button onclick="printSuratJalan()" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                Cetak Surat Jalan
            </button>
        </div>
    </div>
</div>

<script>
let consumablesList = [];
let summaryBranches = [];
let sourceStocks = {};
let allTransfers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadConsumablesSummary();
    loadTransferHistory();
});

async function loadConsumablesSummary() {
    const tbody = document.getElementById('consumables-summary-tbody');
    try {
        const res = await fetch('../api/transfers.php?action=consumables_summary');
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        consumablesList = data.summary;
        summaryBranches = data.branches || [];
        renderConsumablesSummary(data.summary, data.branches);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-rose-500 font-bold">Gagal memuat ringkasan stok bahan: ${e.message}</td></tr>`;
    }
}

function renderConsumablesSummary(summary, branches) {
    const tbody = document.getElementById('consumables-summary-tbody');
    tbody.innerHTML = '';

    if (!summary || summary.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-slate-400">Belum ada barang keperluan cuci terdaftar.</td></tr>`;
        return;
    }

    const activeBranchList = branches || window.APP_BRANCHES || [];

    summary.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors';

        let branchCellsHtml = '';
        let needsRestock = false;

        activeBranchList.forEach(b => {
            const stk = item.branch_stocks[b.id]?.stock || 0;
            const threshold = (b.id == 1) ? 10 : 5;
            if (stk < threshold) needsRestock = true;

            branchCellsHtml += `
                <td class="py-3 px-4 text-center font-mono font-bold ${stk < threshold ? 'text-amber-600' : 'text-slate-700 dark:text-slate-300'}">
                    ${stk} <span class="text-[10px] font-normal text-slate-400">${item.unit}</span>
                </td>
            `;
        });

        const total = item.total_stock;
        let statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aman</span>';
        if (needsRestock) {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Perlu Pasokan</span>';
        }

        tr.innerHTML = `
            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[11px] font-bold">
                        <i class="fa-solid fa-pump-soap text-[10px]"></i>
                    </span>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">${item.name}</div>
                        <div class="text-[10px] text-slate-400 font-mono">${item.code} &bull; Kemasan: ${item.unit}</div>
                    </div>
                </div>
            </td>
            ${branchCellsHtml}
            <td class="py-3 px-4 text-center font-mono font-black text-blue-600 dark:text-blue-400">
                ${total} <span class="text-[10px] font-normal text-slate-400">${item.unit}</span>
            </td>
            <td class="py-3 px-4 text-center">
                ${statusBadge}
            </td>
            <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center justify-end gap-1.5">
                    <button type="button" onclick="openEditStockModal(${item.product_id})" class="px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold transition-colors cursor-pointer inline-flex items-center gap-1" title="Edit Stok Persediaan">
                        <i class="fa-solid fa-pen text-[9px]"></i>
                        <span>Edit Stok</span>
                    </button>
                    <button type="button" onclick="quickTransferItem(${item.product_id})" class="px-2.5 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 text-blue-700 dark:text-blue-400 text-[11px] font-bold transition-colors cursor-pointer">
                        Kirim &rarr;
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function loadTransferHistory() {
    const tbody = document.getElementById('transfer-history-tbody');
    try {
        const res = await fetch('../api/transfers.php?action=list');
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        allTransfers = data.data;
        renderTransferHistory(data.data);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-6 text-center text-rose-500 font-bold">Gagal memuat riwayat: ${e.message}</td></tr>`;
    }
}

function renderTransferHistory(transfers) {
    const tbody = document.getElementById('transfer-history-tbody');
    tbody.innerHTML = '';

    if (!transfers || transfers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat transfer bahan keperluan cuci.</td></tr>`;
        return;
    }

    transfers.forEach(t => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors';

        let itemsBadge = '';
        if (t.items && t.items.length > 0) {
            itemsBadge = t.items.map(it => `
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 mr-1 mb-1 border border-slate-200/80 dark:border-white/5">
                    ${it.qty} ${it.unit || 'unit'} ${it.product_name}
                </span>
            `).join('');
        }

        tr.innerHTML = `
            <td class="py-3 px-4 font-mono">
                <span class="block text-slate-900 dark:text-white font-bold text-xs">${t.transfer_no}</span>
                <span class="text-slate-400 text-[11px]">${t.date}</span>
            </td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-1.5 text-xs font-semibold">
                    <span class="text-slate-800 dark:text-slate-200">${t.from_branch_name}</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-blue-500"></i>
                    <span class="text-blue-600 dark:text-blue-400 font-bold">${t.to_branch_name}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <div class="flex flex-wrap">${itemsBadge}</div>
            </td>
            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                ${t.total_qty}
            </td>
            <td class="py-3 px-4 text-xs">
                <div class="font-semibold text-slate-800 dark:text-slate-200">${t.creator_name || 'Admin'}</div>
                <div class="text-[10px] text-slate-400 italic">${t.notes || '-'}</div>
            </td>
            <td class="py-3 px-4 text-center">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    Selesai
                </span>
            </td>
            <td class="py-3 px-4 text-right">
                <button onclick="viewSuratJalan(${t.id})" class="px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-700 dark:text-slate-300 text-[11px] font-bold flex items-center gap-1 ml-auto cursor-pointer">
                    <i class="fa-solid fa-receipt text-[10px]"></i>
                    Surat Jalan
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterTransferTable() {
    const q = document.getElementById('filter-search-transfer').value.toLowerCase();
    const filtered = allTransfers.filter(t => 
        t.transfer_no.toLowerCase().includes(q) ||
        t.from_branch_name.toLowerCase().includes(q) ||
        t.to_branch_name.toLowerCase().includes(q) ||
        (t.notes && t.notes.toLowerCase().includes(q))
    );
    renderTransferHistory(filtered);
}

async function openTransferModal() {
    document.getElementById('transfer-modal').classList.remove('hidden');
    await onSourceBranchChanged();
    
    const container = document.getElementById('transfer-items-container');
    container.innerHTML = '';
    addTransferItemRow();
}

function closeTransferModal() {
    document.getElementById('transfer-modal').classList.add('hidden');
}

async function onSourceBranchChanged() {
    const fromBranchId = document.getElementById('modal-from-branch').value;
    try {
        const res = await fetch(`../api/transfers.php?action=branch_stocks&branch_id=${fromBranchId}&category=${encodeURIComponent('Bahan & Perlengkapan')}`);
        const data = await res.json();
        if (data.success) {
            sourceStocks = {};
            data.data.forEach(p => {
                sourceStocks[p.product_id] = p;
            });
            // Update all existing row selects
            document.querySelectorAll('.transfer-item-select').forEach(sel => {
                const currentVal = sel.value;
                populateItemSelect(sel, currentVal);
            });
        }
    } catch (e) {
        console.error('Error load source stocks:', e);
    }
}

function populateItemSelect(selectElem, selectedId = '') {
    selectElem.innerHTML = '<option value="">-- Pilih Keperluan Cuci --</option>';
    Object.values(sourceStocks).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.product_id;
        opt.textContent = `${item.name} (${item.code}) - Stok: ${item.branch_stock} ${item.unit}`;
        opt.dataset.stock = item.branch_stock;
        opt.dataset.unit = item.unit;
        opt.dataset.cost = item.cost_price;
        if (selectedId && Number(selectedId) === Number(item.product_id)) {
            opt.selected = true;
        }
        selectElem.appendChild(opt);
    });
}

function addTransferItemRow(preselectId = '') {
    const container = document.getElementById('transfer-items-container');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-2 items-center p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs transfer-row';

    row.innerHTML = `
        <div class="col-span-7">
            <select class="w-full p-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white font-medium transfer-item-select" onchange="onRowProductSelected(this)" required>
                <!-- Populated dynamically -->
            </select>
        </div>
        <div class="col-span-3">
            <div class="flex items-center gap-1">
                <input type="number" min="1" value="5" class="w-full p-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white font-mono font-bold text-right transfer-qty-input" required>
                <span class="text-[10px] text-slate-400 font-mono row-unit-badge">unit</span>
            </div>
        </div>
        <div class="col-span-2 text-right">
            <button type="button" onclick="this.closest('.transfer-row').remove()" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition-colors cursor-pointer" title="Hapus Baris">
                <i class="fa-solid fa-trash-can text-xs"></i>
            </button>
        </div>
    `;

    container.appendChild(row);
    const sel = row.querySelector('.transfer-item-select');
    populateItemSelect(sel, preselectId);
    if (preselectId) {
        onRowProductSelected(sel);
    }
}

function onRowProductSelected(selectElem) {
    const row = selectElem.closest('.transfer-row');
    const opt = selectElem.options[selectElem.selectedIndex];
    const unitBadge = row.querySelector('.row-unit-badge');
    const qtyInput = row.querySelector('.transfer-qty-input');

    if (opt && opt.dataset.unit) {
        unitBadge.textContent = opt.dataset.unit;
        const maxStock = Number(opt.dataset.stock || 0);
        qtyInput.max = maxStock;
        if (maxStock > 0 && Number(qtyInput.value) > maxStock) {
            qtyInput.value = maxStock;
        }
    }
}

async function quickTransferItem(productId) {
    await openTransferModal();
    const container = document.getElementById('transfer-items-container');
    container.innerHTML = '';
    addTransferItemRow(productId);
}

function openEditStockModal(productId) {
    const item = consumablesList.find(p => Number(p.product_id) === Number(productId));
    if (!item) {
        showToast('Data persediaan tidak ditemukan.', 'error');
        return;
    }

    const activeBranchList = (summaryBranches && summaryBranches.length > 0) ? summaryBranches : (window.APP_BRANCHES || []);

    document.getElementById('edit-stock-product-id').value = item.product_id;
    document.getElementById('edit-stock-product-name').innerText = item.name;
    document.getElementById('edit-stock-product-meta').innerText = `${item.code} • Kemasan: ${item.unit}`;
    document.getElementById('edit-stock-reason').value = '';

    const container = document.getElementById('edit-stock-branches-container');
    container.innerHTML = '';

    activeBranchList.forEach(b => {
        const currentStk = item.branch_stocks && item.branch_stocks[b.id] ? Number(item.branch_stocks[b.id].stock) : 0;
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between gap-3 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/70';
        row.innerHTML = `
            <div class="min-w-0">
                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">${b.name}</div>
                <div class="text-[10px] text-slate-400">Stok saat ini: <span class="font-mono font-bold text-slate-600 dark:text-slate-300">${currentStk} ${item.unit}</span></div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="stepEditStockInput(this, -1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs cursor-pointer">-</button>
                <input type="number" min="0" value="${currentStk}" data-branch-id="${b.id}" oninput="recalcEditStockTotal('${item.unit}')" class="w-20 p-1.5 text-xs text-center font-mono font-bold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 edit-stock-branch-input" required>
                <button type="button" onclick="stepEditStockInput(this, 1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs cursor-pointer">+</button>
                <span class="text-[10px] text-slate-400 font-mono w-10">${item.unit}</span>
            </div>
        `;
        container.appendChild(row);
    });

    recalcEditStockTotal(item.unit);
    document.getElementById('edit-stock-modal').classList.remove('hidden');
}

function stepEditStockInput(btn, delta) {
    const input = btn.parentElement.querySelector('.edit-stock-branch-input');
    if (!input) return;
    const nextVal = Math.max(0, (Number(input.value) || 0) + delta);
    input.value = nextVal;
    input.dispatchEvent(new Event('input'));
}

function recalcEditStockTotal(unitLabel = '') {
    let total = 0;
    document.querySelectorAll('.edit-stock-branch-input').forEach(inp => {
        total += Math.max(0, Number(inp.value) || 0);
    });
    const el = document.getElementById('edit-stock-live-total');
    if (el) el.innerText = `${total} ${unitLabel}`.trim();
}

function closeEditStockModal() {
    document.getElementById('edit-stock-modal').classList.add('hidden');
}

async function submitEditStock(e) {
    e.preventDefault();
    const productId = Number(document.getElementById('edit-stock-product-id').value);
    const reason = (document.getElementById('edit-stock-reason').value || '').trim() || 'Pembaruan Stok Persediaan';

    const stocks = [];
    document.querySelectorAll('.edit-stock-branch-input').forEach(inp => {
        stocks.push({
            branch_id: Number(inp.dataset.branchId),
            stock: Math.max(0, Number(inp.value) || 0)
        });
    });

    const btn = document.getElementById('btn-save-edit-stock');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin text-xs"></i><span>Menyimpan...</span>';

    try {
        const res = await fetch('../api/transfers.php?action=update_stock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: productId,
                reason: reason,
                stocks: stocks
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Stok persediaan berhasil diperbarui.', 'success');
            closeEditStockModal();
            await loadConsumablesSummary();
        } else {
            showToast(data.message || 'Gagal memperbarui stok persediaan.', 'error');
        }
    } catch (err) {
        showToast('Terjadi kesalahan jaringan saat menyimpan stok.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

async function submitStockTransfer(e) {
    e.preventDefault();
    const fromBranch = document.getElementById('modal-from-branch').value;
    const toBranch = document.getElementById('modal-to-branch').value;
    const date = document.getElementById('modal-transfer-date').value;
    const notes = document.getElementById('modal-transfer-notes').value;

    if (fromBranch === toBranch) {
        showToast('Cabang asal dan cabang tujuan tidak boleh sama!', 'error');
        return;
    }

    const rows = document.querySelectorAll('.transfer-row');
    const items = [];

    for (let r of rows) {
        const sel = r.querySelector('.transfer-item-select');
        const pId = sel.value;
        const qty = Number(r.querySelector('.transfer-qty-input').value);
        if (!pId || qty <= 0) continue;

        const opt = sel.options[sel.selectedIndex];
        const costPrice = Number(opt.dataset.cost || 0);
        const stockAvail = Number(opt.dataset.stock || 0);

        if (qty > stockAvail) {
            showToast(`Jumlah transfer melebihi stok yang tersedia (${stockAvail})!`, 'error');
            return;
        }

        items.push({
            product_id: pId,
            qty: qty,
            cost_price: costPrice
        });
    }

    if (items.length === 0) {
        showToast('Silakan pilih minimal 1 bahan keperluan cuci.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-submit-transfer');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Memproses...';

    try {
        const formData = new FormData();
        formData.append('from_branch_id', fromBranch);
        formData.append('to_branch_id', toBranch);
        formData.append('date', date);
        formData.append('notes', notes);
        formData.append('items', JSON.stringify(items));

        const res = await fetch('../api/transfers.php?action=create', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Transfer berhasil diproses', 'success');
            closeTransferModal();
            loadConsumablesSummary();
            loadTransferHistory();
        } else {
            showToast(data.message || 'Gagal memproses transfer stok', 'error');
        }
    } catch (err) {
        showToast('Terjadi kesalahan jaringan atau server', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Proses &amp; Kurangi Stok';
    }
}

function viewSuratJalan(transferId) {
    const t = allTransfers.find(item => item.id == transferId);
    if (!t) return;

    let itemsHtml = (t.items || []).map((it, idx) => `
        <div class="flex justify-between py-1 border-b border-dashed border-slate-300 dark:border-slate-700">
            <span>${idx + 1}. ${it.product_name}</span>
            <span class="font-bold">${it.qty} ${it.unit || 'unit'}</span>
        </div>
    `).join('');

    const content = `
        <div class="text-center pb-2 border-b border-slate-300 dark:border-slate-700">
            <div class="font-bold text-sm tracking-tight">PJBL KOMAK LAUNDRY</div>
            <div class="text-[10px] text-slate-500">SURAT JALAN TRANSFER BAHAN OPERASIONAL</div>
        </div>
        <div class="space-y-1 text-[11px] pt-1">
            <div class="flex justify-between"><span>No. Bukti</span><span class="font-bold">${t.transfer_no}</span></div>
            <div class="flex justify-between"><span>Tanggal</span><span>${t.date}</span></div>
            <div class="flex justify-between"><span>Dari</span><span class="font-bold">${t.from_branch_name}</span></div>
            <div class="flex justify-between"><span>Tujuan</span><span class="font-bold">${t.to_branch_name}</span></div>
            <div class="flex justify-between"><span>Pengirim</span><span>${t.creator_name || 'Admin'}</span></div>
        </div>
        <div class="pt-2">
            <div class="font-bold text-[11px] mb-1">Rincian Barang Keperluan Cuci:</div>
            ${itemsHtml}
        </div>
        <div class="pt-3 flex justify-between text-center text-[10px] text-slate-500">
            <div>Pengirim<br><br><br>( .................... )</div>
            <div>Penerima<br><br><br>( .................... )</div>
        </div>
    `;

    document.getElementById('surat-jalan-content').innerHTML = content;
    document.getElementById('surat-jalan-modal').classList.remove('hidden');
}

function closeSuratJalanModal() {
    document.getElementById('surat-jalan-modal').classList.add('hidden');
}

function printSuratJalan() {
    const printArea = document.getElementById('surat-jalan-content').innerHTML;
    const printWindow = window.open('', '_blank', 'width=600,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Surat Jalan Transfer Bahan Laundry</title>
            <style>
                body { font-family: monospace; font-size: 12px; padding: 20px; line-height: 1.4; }
                .border-b { border-bottom: 1px dashed #444; }
                .flex { display: flex; }
                .justify-between { justify-content: space-between; }
                .text-center { text-align: center; }
                .font-bold { font-weight: bold; }
                .pt-3 { padding-top: 15px; }
            </style>
        </head>
        <body>
            ${printArea}
            <script>window.onload = function() { window.print(); window.close(); }<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
