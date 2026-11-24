<?php
$pageTitle = 'Jurnal Umum (General Journal)';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Jurnal Umum (General Journal)</h2>
            </div>
        </div>
        <button onclick="openManualJournalModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Jurnal Manual
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#11182c] p-4 rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col md:flex-row md:items-end justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center gap-3 w-full lg:w-auto">
            <div class="w-full sm:col-span-2 lg:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari No Bukti / Deskripsi</label>
                <div class="relative">
                    <input type="text" id="filter-search" placeholder="Contoh: JRN-..." onkeydown="if(event.key==='Enter') loadJournals();" class="p-2 pl-7 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 w-full">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:flex lg:items-center">
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" id="filter-start-date" value="<?= date('Y-m-01') ?>" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" id="filter-end-date" value="<?= date('Y-m-d') ?>" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="w-full sm:col-span-2 lg:w-auto">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Entitas Cabang</label>
                <select id="filter-branch" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Cabang (Konsolidasi)</option>
                    <?php foreach ($activeBranches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full sm:col-span-2 lg:w-auto flex items-center space-x-1.5 pt-1">
                <button onclick="loadJournals()" class="flex-1 lg:flex-none px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs justify-center flex items-center">
                    Terapkan Filter
                </button>
                <button onclick="resetJournalFilter()" title="Reset Filter" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <div class="text-xs text-slate-500">
            <span id="journal-count-badge" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-white/[0.06] font-bold text-slate-700 dark:text-slate-300 font-mono">0 Transaksi</span>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                        <th class="py-3.5 px-4 w-36">Tanggal & Bukti</th>
                        <th class="py-3.5 px-4">Deskripsi Transaksi & Pos Akun</th>
                        <th class="py-3.5 px-4 w-32">Klasifikasi</th>
                        <th class="py-3.5 px-4 text-right w-36">Debit (Rp)</th>
                        <th class="py-3.5 px-4 text-right w-36">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody id="journal-table-body" class="divide-y divide-slate-100">
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">Memuat jurnal umum...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Input / Edit Jurnal Umum -->
<div id="manual-journal-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#11182c] rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col justify-between border border-slate-200/80 dark:border-white/10">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10">
            <div>
                <h4 id="man-modal-title" class="font-extrabold text-sm text-slate-900 dark:text-white">Entri Jurnal Umum Manual</h4>
                <p id="man-modal-subtitle" class="text-[11px] text-slate-400">Pencatatan transaksi manual sesuai kaidah debit & kredit seimbang</p>
            </div>
            <button onclick="closeManualJournalModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold">&times;</button>
        </div>

        <input type="hidden" id="man-journal-id" value="">

        <div class="space-y-3.5 flex-1 overflow-y-auto pr-1">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Transaksi</label>
                    <input type="date" id="man-date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Beban Cabang</label>
                    <select id="man-branch" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keterangan Transaksi</label>
                <input type="text" id="man-desc" placeholder="Contoh: Pembayaran Biaya Listrik & Operasional Kantor" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <!-- Dynamic Lines -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Baris Akun (Debit & Kredit)</span>
                    <button type="button" onclick="addJournalRow()" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center space-x-1">
                        <span>+ Tambah Baris</span>
                    </button>
                </div>

                <div id="journal-rows-container" class="space-y-2"></div>

                <!-- Balance Check Display (KomakFX Live Balance Meter) -->
                <div id="journal-balance-meter-container" class="mt-4"></div>
                <div class="hidden">
                    <span id="calc-debit"></span>
                    <span id="calc-credit"></span>
                    <span id="balance-badge"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2 pt-3 border-t border-slate-100 dark:border-white/10">
            <button onclick="submitManualJournal()" id="btn-save-journal" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                Simpan Jurnal Umum
            </button>
            <button onclick="closeManualJournalModal()" class="py-3 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                Batal
            </button>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Jurnal -->
<div id="delete-journal-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#11182c] rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200/80 dark:border-white/10">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">Hapus Jurnal Umum?</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Jurnal beserta seluruh rincian debit dan kreditnya akan dihapus permanen dan saldo buku besar otomatis menyesuaikan.
                </p>
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-white/10 text-xs space-y-1">
            <div class="flex justify-between gap-2">
                <span class="text-slate-400">No. Bukti</span>
                <span id="del-jrn-no" class="font-mono font-bold text-indigo-600 dark:text-indigo-400">-</span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-slate-400">Deskripsi</span>
                <span id="del-jrn-desc" class="font-semibold text-slate-800 dark:text-slate-200 text-right truncate">-</span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-slate-400">Total Nominal</span>
                <span id="del-jrn-total" class="font-mono font-bold text-slate-900 dark:text-white">Rp 0</span>
            </div>
        </div>

        <input type="hidden" id="del-jrn-id" value="">

        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="closeDeleteJournalModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors">
                Batal
            </button>
            <button type="button" onclick="executeDeleteJournal()" id="btn-confirm-delete-journal" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-colors">
                Ya, Hapus Jurnal
            </button>
        </div>
    </div>
</div>

<script>
    let rawAccounts = [];
    let loadedJournals = [];

    document.addEventListener('DOMContentLoaded', async () => {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        const startDateParam = urlParams.get('start_date');
        const endDateParam = urlParams.get('end_date');
        const branchParam = urlParams.get('branch_id');

        if (startDateParam) document.getElementById('filter-start-date').value = startDateParam;
        if (endDateParam) document.getElementById('filter-end-date').value = endDateParam;
        if (branchParam) document.getElementById('filter-branch').value = branchParam;
        if (searchParam) document.getElementById('filter-search').value = searchParam;

        await loadAccounts();
        await loadJournals();
    });

    function resetJournalFilter() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-start-date').value = '<?= date('Y-m-01') ?>';
        document.getElementById('filter-end-date').value = '<?= date('Y-m-d') ?>';
        document.getElementById('filter-branch').value = '';
        if (window.history.pushState) {
            window.history.pushState({}, '', window.location.pathname);
        }
        loadJournals();
    }

    async function loadAccounts() {
        try {
            const res = await fetch('../api/accounts.php');
            const result = await res.json();
            if (result.success) {
                rawAccounts = result.data;
            }
        } catch (e) {}
    }

    async function loadJournals() {
        const start = document.getElementById('filter-start-date').value;
        const end = document.getElementById('filter-end-date').value;
        const branch = document.getElementById('filter-branch').value;
        const search = (document.getElementById('filter-search')?.value || '').trim();

        try {
            const url = `../api/journals.php?action=list&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}&branch_id=${encodeURIComponent(branch)}&search=${encodeURIComponent(search)}`;
            const res = await fetch(url);
            const result = await res.json();
            if (!result.success) return;

            loadedJournals = result.data || [];

            const tbody = document.getElementById('journal-table-body');
            tbody.innerHTML = '';

            const badgeCount = document.getElementById('journal-count-badge');
            if (badgeCount) badgeCount.innerText = loadedJournals.length + ' Transaksi';

            if (loadedJournals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-slate-400">Tidak ada jurnal pada periode atau kata kunci pencarian ini.</td></tr>';
                return;
            }

            loadedJournals.forEach(j => {
                const trHeader = document.createElement('tr');
                const isMatchedSearch = search && (
                    (j.transaction_no && j.transaction_no.toLowerCase().includes(search.toLowerCase())) ||
                    (j.description && j.description.toLowerCase().includes(search.toLowerCase()))
                );
                trHeader.className = isMatchedSearch 
                    ? 'bg-amber-50/90 font-semibold ring-2 ring-amber-400/80 ring-inset transition-colors' 
                    : 'bg-slate-50/80 font-semibold';
                
                let refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Manual</span>';
                if (j.ref_type === 'pos') {
                    refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Kasir POS</span>';
                } else if (j.ref_type === 'invoice_scan') {
                    refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">Scan Faktur AI</span>';
                } else if (j.ref_type === 'adjustment') {
                    refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Penyesuaian (AJE)</span>';
                } else if (j.ref_type === 'closing') {
                    refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Tutup Buku (Closing)</span>';
                } else if (j.ref_type === 'reversal') {
                    refBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Koreksi / Reversal</span>';
                }

                if (isMatchedSearch) {
                    refBadge += ' <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300">Hasil Pencarian</span>';
                }

                let actionBtns = '';
                if (j.ref_type !== 'closing') {
                    actionBtns = `
                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                            <button type="button" onclick="openEditJournalModal(${j.id})" title="Edit Jurnal" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold border border-indigo-200/80 transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="openDeleteJournalModal(${j.id})" title="Hapus Jurnal" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[10px] font-bold border border-rose-200/80 transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Jurnal</span>
                            </button>
                        </div>
                    `;
                }

                trHeader.innerHTML = `
                    <td class="py-3 px-4 text-slate-600 font-mono">
                        <span class="block text-slate-700 font-bold">${j.date}</span>
                        <span class="text-indigo-600 font-semibold text-[11px]">${j.transaction_no}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-900 font-bold" colspan="2">
                        <div class="flex items-center justify-between gap-2">
                            <span>${j.description}</span>
                            ${actionBtns}
                        </div>
                        <div class="mt-1 flex items-center space-x-2 font-normal text-slate-400 text-[11px]">
                            <span class="text-slate-600 font-medium">${j.branch_name || 'Kantor Pusat'}</span>
                            <span>&bull;</span>
                            <span>${refBadge}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-right text-slate-700 font-mono font-bold">Rp ${Number(j.total_debit).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right text-slate-700 font-mono font-bold">Rp ${Number(j.total_credit).toLocaleString('id-ID')}</td>
                `;
                tbody.appendChild(trHeader);

                // Baris detail debit & kredit
                j.items.forEach(it => {
                    const trItem = document.createElement('tr');
                    trItem.className = 'hover:bg-slate-50/70 transition-colors';
                    const isCredit = Number(it.credit) > 0;
                    trItem.innerHTML = `
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4 ${isCredit ? 'pl-9 text-slate-600' : 'text-slate-800 font-semibold'}">
                            ${isCredit ? '<span class="text-slate-400 mr-1.5 font-mono">↳</span>' : ''}
                            <a href="buku-besar.php?account_id=${it.account_id}" class="hover:underline font-mono text-indigo-600 mr-1.5" title="Lihat di Buku Besar">[${it.account_code}]</a>
                            <a href="buku-besar.php?account_id=${it.account_id}" class="hover:underline hover:text-indigo-600" title="Lihat di Buku Besar">${it.account_name}</a>
                            ${it.memo ? `<span class="text-slate-400 text-[10px] block italic">${it.memo}</span>` : ''}
                        </td>
                        <td class="py-2 px-4 text-slate-400 text-[11px]">${it.category}</td>
                        <td class="py-2 px-4 text-right font-mono ${Number(it.debit) > 0 ? 'font-bold text-slate-900' : 'text-slate-300'}">
                            ${Number(it.debit) > 0 ? 'Rp ' + Number(it.debit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td class="py-2 px-4 text-right font-mono ${Number(it.credit) > 0 ? 'font-bold text-slate-900' : 'text-slate-300'}">
                            ${Number(it.credit) > 0 ? 'Rp ' + Number(it.credit).toLocaleString('id-ID') : '-'}
                        </td>
                    `;
                    tbody.appendChild(trItem);
                });
            });
        } catch (e) {
            console.error('Error load journals:', e);
        }
    }

    let journalMeter = null;

    function initJournalBalanceMeter() {
        const root = document.getElementById('journal-balance-meter-container');
        if (!root) return;
        if (!journalMeter && window.KomakFX && typeof window.KomakFX.balanceMeter === 'function') {
            root.innerHTML = '';
            journalMeter = window.KomakFX.balanceMeter(
                root,
                () => {
                    let d = 0;
                    document.querySelectorAll('.row-debit').forEach(inp => d += Number(inp.value) || 0);
                    return d;
                },
                () => {
                    let c = 0;
                    document.querySelectorAll('.row-credit').forEach(inp => c += Number(inp.value) || 0);
                    return c;
                },
                document.getElementById('btn-save-journal')
            );
        }
        if (journalMeter) {
            journalMeter.update();
        }
    }

    function openManualJournalModal() {
        document.getElementById('man-journal-id').value = '';
        document.getElementById('man-modal-title').innerText = 'Entri Jurnal Umum Manual';
        document.getElementById('man-modal-subtitle').innerText = 'Pencatatan transaksi manual sesuai kaidah debit & kredit seimbang';
        document.getElementById('btn-save-journal').innerText = 'Simpan Jurnal Umum';
        document.getElementById('man-date').value = '<?= date('Y-m-d') ?>';
        document.getElementById('man-desc').value = '';
        const branchSel = document.getElementById('man-branch');
        if (branchSel && branchSel.options.length > 0) branchSel.selectedIndex = 0;

        document.getElementById('manual-journal-modal').classList.remove('hidden');
        const container = document.getElementById('journal-rows-container');
        container.innerHTML = '';
        addJournalRow();
        addJournalRow();
        initJournalBalanceMeter();
        calculateJournalBalance();
    }

    function openEditJournalModal(journalId) {
        const j = loadedJournals.find(item => Number(item.id) === Number(journalId));
        if (!j) {
            showToast('Data jurnal tidak ditemukan.', 'error');
            return;
        }

        document.getElementById('man-journal-id').value = j.id;
        document.getElementById('man-modal-title').innerText = `Edit Jurnal Umum (${j.transaction_no})`;
        document.getElementById('man-modal-subtitle').innerText = 'Perbarui tanggal, keterangan, atau baris akun debit & kredit (harus seimbang)';
        document.getElementById('btn-save-journal').innerText = 'Simpan Perubahan Jurnal';
        document.getElementById('man-date').value = j.date || '<?= date('Y-m-d') ?>';
        document.getElementById('man-branch').value = j.branch_id || 1;
        document.getElementById('man-desc').value = j.description || '';

        document.getElementById('manual-journal-modal').classList.remove('hidden');
        const container = document.getElementById('journal-rows-container');
        container.innerHTML = '';

        if (j.items && j.items.length > 0) {
            j.items.forEach(it => addJournalRow(it));
        } else {
            addJournalRow();
            addJournalRow();
        }

        initJournalBalanceMeter();
        calculateJournalBalance();
    }

    function closeManualJournalModal() {
        document.getElementById('manual-journal-modal').classList.add('hidden');
    }

    function addJournalRow(prefill = null) {
        const container = document.getElementById('journal-rows-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-center journal-entry-row';
        const selectedAccId = prefill ? Number(prefill.account_id) : null;
        const debitVal = prefill && Number(prefill.debit) > 0 ? Number(prefill.debit) : '';
        const creditVal = prefill && Number(prefill.credit) > 0 ? Number(prefill.credit) : '';
        const memoVal = prefill && prefill.memo ? prefill.memo : '';

        div.dataset.memo = memoVal;
        div.innerHTML = `
            <div class="col-span-5">
                <select class="w-full p-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 row-acc-id focus:ring-2 focus:ring-indigo-500">
                    ${rawAccounts.map(a => `<option value="${a.id}" ${selectedAccId === Number(a.id) ? 'selected' : ''}>[${a.code}] ${a.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" step="any" min="0" value="${debitVal}" oninput="calculateJournalBalance()" placeholder="Debit (Rp)" class="w-full p-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 row-debit font-mono focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="col-span-3">
                <input type="number" step="any" min="0" value="${creditVal}" oninput="calculateJournalBalance()" placeholder="Kredit (Rp)" class="w-full p-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 row-credit font-mono focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.journal-entry-row').remove(); calculateJournalBalance();" class="text-slate-400 hover:text-rose-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        container.appendChild(div);
    }

    function calculateJournalBalance() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.row-debit').forEach(inp => totalDebit += Number(inp.value) || 0);
        document.querySelectorAll('.row-credit').forEach(inp => totalCredit += Number(inp.value) || 0);

        const dEl = document.getElementById('calc-debit');
        const cEl = document.getElementById('calc-credit');
        const badge = document.getElementById('balance-badge');
        const btn = document.getElementById('btn-save-journal');

        if (dEl) dEl.innerText = 'Rp ' + totalDebit.toLocaleString('id-ID');
        if (cEl) cEl.innerText = 'Rp ' + totalCredit.toLocaleString('id-ID');

        if (journalMeter) {
            const isBalanced = journalMeter.update();
            if (btn) btn.disabled = !isBalanced;
        } else {
            if (totalDebit > 0 && Math.abs(totalDebit - totalCredit) < 0.01) {
                if (badge) {
                    badge.innerText = '✓ SEIMBANG (BALANCE)';
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
                }
                if (btn) btn.disabled = false;
            } else {
                const diff = Math.abs(totalDebit - totalCredit);
                if (badge) {
                    badge.innerText = `✕ SELISIH Rp ${diff.toLocaleString('id-ID')}`;
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700';
                }
                if (btn) btn.disabled = true;
            }
        }
    }

    async function submitManualJournal() {
        if (journalMeter && !journalMeter.guard()) {
            return;
        }

        const desc = (document.getElementById('man-desc').value || '').trim();
        if (!desc) {
            showToast('Keterangan transaksi wajib diisi!', 'error');
            document.getElementById('man-desc').focus();
            return;
        }

        const rows = document.querySelectorAll('.journal-entry-row');
        const items = [];

        rows.forEach(r => {
            const accId = r.querySelector('.row-acc-id').value;
            const debit = Number(r.querySelector('.row-debit').value) || 0;
            const credit = Number(r.querySelector('.row-credit').value) || 0;
            const memo = r.dataset.memo || '';
            if (accId && (debit > 0 || credit > 0)) {
                items.push({ account_id: accId, debit: debit, credit: credit, memo: memo });
            }
        });

        if (items.length < 2) {
            showToast('Jurnal minimal memiliki 2 baris akun (Debit & Kredit)!', 'error');
            return;
        }

        const editId = document.getElementById('man-journal-id').value;
        const isEdit = editId !== '';
        const payload = {
            id: isEdit ? Number(editId) : undefined,
            date: document.getElementById('man-date').value,
            branch_id: document.getElementById('man-branch').value,
            description: desc,
            ref_type: 'manual',
            items: items
        };

        const btn = document.getElementById('btn-save-journal');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = isEdit ? 'Menyimpan Perubahan...' : 'Menyimpan Jurnal...';

        try {
            const endpoint = isEdit ? '../api/journals.php?action=update' : '../api/journals.php?action=create';
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeManualJournalModal();
                loadJournals();
            } else {
                showToast(result.message || 'Gagal menyimpan jurnal.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan koneksi!', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    }

    function openDeleteJournalModal(journalId) {
        const j = loadedJournals.find(item => Number(item.id) === Number(journalId));
        if (!j) return;

        document.getElementById('del-jrn-id').value = j.id;
        document.getElementById('del-jrn-no').innerText = j.transaction_no || '-';
        document.getElementById('del-jrn-desc').innerText = j.description || '-';
        document.getElementById('del-jrn-total').innerText = 'Rp ' + Number(j.total_debit || 0).toLocaleString('id-ID');
        document.getElementById('delete-journal-modal').classList.remove('hidden');
    }

    function closeDeleteJournalModal() {
        document.getElementById('delete-journal-modal').classList.add('hidden');
    }

    async function executeDeleteJournal() {
        const journalId = document.getElementById('del-jrn-id').value;
        if (!journalId) return;

        const btn = document.getElementById('btn-confirm-delete-journal');
        btn.disabled = true;
        btn.innerText = 'Menghapus...';

        try {
            const res = await fetch('../api/journals.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ journal_id: Number(journalId) })
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message || 'Jurnal berhasil dihapus.', 'success');
                closeDeleteJournalModal();
                loadJournals();
            } else {
                showToast(result.message || 'Gagal menghapus jurnal.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan koneksi saat menghapus jurnal.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Ya, Hapus Jurnal';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
