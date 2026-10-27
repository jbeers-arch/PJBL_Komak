<?php
$pageTitle = 'Jurnal Penyesuaian (AJE)';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Jurnal Penyesuaian (Adjusting Entries)</h2>
            </div>
        </div>
        <button onclick="openAdjustmentModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Jurnal Penyesuaian
        </button>
    </div>

    <!-- Quick Info Cards: Preset Standar Penyesuaian -->
    <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Pola Standar Cepat (1-Klik Isi Form)</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div onclick="quickCreateAdjustment('depreciation')" class="bg-white p-4 rounded-3xl border border-slate-200/80 hover:border-amber-400 hover:shadow-md cursor-pointer transition-all duration-200 group">
                <div class="flex items-center space-x-2 text-amber-600 mb-1.5">
                    <span class="w-7 h-7 rounded-xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-900">Penyusutan Peralatan</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-snug">
                    <span class="text-indigo-600 font-semibold">(D) 6106 Beban Penyusutan</span><br>
                    <span class="text-slate-500">(K) 1202 Akum. Penyusutan</span>
                </p>
            </div>

            <div onclick="quickCreateAdjustment('supplies')" class="bg-white p-4 rounded-3xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md cursor-pointer transition-all duration-200 group">
                <div class="flex items-center space-x-2 text-blue-600 mb-1.5">
                    <span class="w-7 h-7 rounded-xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-900">Pemakaian Perlengkapan</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-snug">
                    <span class="text-indigo-600 font-semibold">(D) 6104 Beban Perlengkapan</span><br>
                    <span class="text-slate-500">(K) 1106 Perlengkapan Toko</span>
                </p>
            </div>

            <div onclick="quickCreateAdjustment('accrual')" class="bg-white p-4 rounded-3xl border border-slate-200/80 hover:border-purple-400 hover:shadow-md cursor-pointer transition-all duration-200 group">
                <div class="flex items-center space-x-2 text-purple-600 mb-1.5">
                    <span class="w-7 h-7 rounded-xl bg-purple-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-900">Beban Akrual / Utang</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-snug">
                    <span class="text-indigo-600 font-semibold">(D) 6102 Beban Listrik/Gaji</span><br>
                    <span class="text-slate-500">(K) 2104 Utang Beban/Akrual</span>
                </p>
            </div>

            <div onclick="quickCreateAdjustment('inventory')" class="bg-white p-4 rounded-3xl border border-slate-200/80 hover:border-rose-400 hover:shadow-md cursor-pointer transition-all duration-200 group">
                <div class="flex items-center space-x-2 text-rose-600 mb-1.5">
                    <span class="w-7 h-7 rounded-xl bg-rose-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-900">Selisih Stok Persediaan</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-snug">
                    <span class="text-indigo-600 font-semibold">(D) 6107 Beban Selisih Stok</span><br>
                    <span class="text-slate-500">(K) 1105 Persediaan Barang</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap items-end gap-3 w-full md:w-auto">
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" id="filter-start-date" value="<?= date('Y-m-01') ?>" class="w-full p-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" id="filter-end-date" value="<?= date('Y-m-d') ?>" class="w-full p-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Entitas / Cabang</label>
                <select id="filter-branch" class="w-full p-2 text-xs rounded-xl border border-slate-200 bg-white font-medium focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Cabang (Konsolidasi)</option>
                    <?php foreach ($activeBranches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <button onclick="loadAdjustments()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs">
                    Terapkan Filter
                </button>
            </div>
        </div>

        <div class="text-xs text-slate-500 pt-1 md:pt-0">
            <span id="aje-count-badge" class="inline-block px-3 py-1 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200/80 font-mono">0 AJE Terdaftar</span>
        </div>
    </div>

    <!-- Table of Adjusting Journal Entries -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                        <th class="py-3.5 px-4 w-36">Tanggal & No. AJE</th>
                        <th class="py-3.5 px-4">Keterangan & Pos Penyesuaian</th>
                        <th class="py-3.5 px-4 w-32">Klasifikasi</th>
                        <th class="py-3.5 px-4 text-right w-36">Debit (Rp)</th>
                        <th class="py-3.5 px-4 text-right w-36">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody id="adjustment-table-body" class="divide-y divide-slate-100">
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">Memuat jurnal penyesuaian...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Jurnal Penyesuaian -->
<div id="adjustment-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Entri Jurnal Penyesuaian (AJE)</h4>
                <p class="text-[11px] text-slate-400">Penyesuaian akun nominal dan riil agar akuntansi akurat pada akhir periode</p>
            </div>
            <button onclick="closeAdjustmentModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <div class="space-y-3.5 flex-1 overflow-y-auto pr-1">
            <!-- Preset Template Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gunakan Pola Cepat</label>
                <select id="aje-template-select" onchange="applyAjeTemplate(this.value)" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50 font-semibold text-slate-800 focus:ring-2 focus:ring-amber-500">
                    <option value="custom">-- Penyesuaian Bebas / Manual --</option>
                    <option value="depreciation">Penyusutan Peralatan Toko & Kantor (6106 / 1202)</option>
                    <option value="supplies">Pemakaian Perlengkapan Habis Pakai (6104 / 1106)</option>
                    <option value="accrual">Beban Akrual / Utang Beban Belum Terbayar (6102 / 2104)</option>
                    <option value="inventory">Selisih Fisik / Kerusakan Persediaan (6107 / 1105)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Penyesuaian</label>
                    <input type="date" id="aje-date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cabang Entitas</label>
                    <select id="aje-branch" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-amber-500">
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Memo Penyesuaian</label>
                <input type="text" id="aje-desc" placeholder="Contoh: Penyesuaian penyusutan peralatan bulan ini" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500" required>
            </div>

            <!-- Baris Akun Debit & Kredit -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800">Pos Akun Penyesuaian</span>
                    <button type="button" onclick="addAjeRow()" class="text-xs font-bold text-amber-600 hover:text-amber-800 flex items-center space-x-1">
                        <span>+ Tambah Baris</span>
                    </button>
                </div>

                <div id="aje-rows-container" class="space-y-2"></div>

                <!-- Balance Check Display (KomakFX Live Balance Meter) -->
                <div id="aje-balance-meter-container" class="mt-3.5"></div>
                <div class="hidden">
                    <span id="calc-aje-debit"></span>
                    <span id="calc-aje-credit"></span>
                    <span id="aje-balance-badge"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
            <button onclick="submitAdjustmentJournal()" id="btn-save-aje" class="flex-1 py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-xs">
                Simpan & Posting Jurnal Penyesuaian
            </button>
            <button onclick="closeAdjustmentModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
    let allAccounts = [];

    document.addEventListener('DOMContentLoaded', async () => {
        await loadAccounts();
        await loadAdjustments();
    });

    async function loadAccounts() {
        try {
            const res = await fetch('../api/accounts.php');
            const result = await res.json();
            if (result.success) {
                allAccounts = result.data;
            }
        } catch (e) {
            console.error('Error load accounts:', e);
        }
    }

    async function loadAdjustments() {
        const start = document.getElementById('filter-start-date').value;
        const end = document.getElementById('filter-end-date').value;
        const branch = document.getElementById('filter-branch').value;

        try {
            const res = await fetch(`../api/journals.php?action=list&ref_type=adjustment&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            const tbody = document.getElementById('adjustment-table-body');
            tbody.innerHTML = '';

            const badgeCount = document.getElementById('aje-count-badge');
            if (badgeCount) badgeCount.innerText = result.data.length + ' AJE Terdaftar';

            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-slate-400">Belum ada jurnal penyesuaian (AJE) pada periode ini.</td></tr>';
                return;
            }

            result.data.forEach(j => {
                const trHeader = document.createElement('tr');
                trHeader.className = 'bg-amber-50/50 font-semibold';
                trHeader.innerHTML = `
                    <td class="py-3 px-4 text-slate-600 font-mono">
                        <span class="block text-slate-700 font-bold">${j.date}</span>
                        <span class="text-amber-700 font-semibold text-[11px]">${j.transaction_no}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-900 font-bold" colspan="2">
                        ${j.description}
                        <div class="mt-1 flex items-center space-x-2 font-normal text-slate-400 text-[11px]">
                            <span class="text-slate-600 font-medium">${j.branch_name || 'Kantor Pusat'}</span>
                            <span>&bull;</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 font-bold">Penyesuaian (AJE)</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-right text-slate-700 font-mono font-bold">Rp ${Number(j.total_debit).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right text-slate-700 font-mono font-bold">Rp ${Number(j.total_credit).toLocaleString('id-ID')}</td>
                `;
                tbody.appendChild(trHeader);

                // Detail lines
                j.items.forEach(it => {
                    const trItem = document.createElement('tr');
                    trItem.className = 'hover:bg-slate-50/70 transition-colors';
                    const isCredit = Number(it.credit) > 0;
                    trItem.innerHTML = `
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4 ${isCredit ? 'pl-9 text-slate-600' : 'text-slate-800 font-semibold'}">
                            ${isCredit ? '<span class="text-slate-400 mr-1.5 font-mono">↳</span>' : ''}
                            <span class="font-mono text-amber-700 mr-1.5">[${it.account_code}]</span>
                            ${it.account_name}
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
            console.error('Error load adjustments:', e);
        }
    }

    let ajeMeter = null;

    function initAjeBalanceMeter() {
        const root = document.getElementById('aje-balance-meter-container');
        if (!root) return;
        if (!ajeMeter && window.KomakFX && typeof window.KomakFX.balanceMeter === 'function') {
            root.innerHTML = '';
            ajeMeter = window.KomakFX.balanceMeter(
                root,
                () => {
                    let d = 0;
                    document.querySelectorAll('.aje-row-debit').forEach(inp => d += Number(inp.value) || 0);
                    return d;
                },
                () => {
                    let c = 0;
                    document.querySelectorAll('.aje-row-credit').forEach(inp => c += Number(inp.value) || 0);
                    return c;
                },
                document.getElementById('btn-save-aje')
            );
        }
        if (ajeMeter) {
            ajeMeter.update();
        }
    }

    function openAdjustmentModal() {
        document.getElementById('adjustment-modal').classList.remove('hidden');
        document.getElementById('aje-template-select').value = 'custom';
        document.getElementById('aje-desc').value = '';
        const container = document.getElementById('aje-rows-container');
        container.innerHTML = '';
        addAjeRow();
        addAjeRow();
        initAjeBalanceMeter();
        calculateAjeBalance();
    }

    function closeAdjustmentModal() {
        document.getElementById('adjustment-modal').classList.add('hidden');
    }

    function addAjeRow(accId = null, debit = '', credit = '', memo = '') {
        const container = document.getElementById('aje-rows-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-center aje-entry-row';
        div.innerHTML = `
            <div class="col-span-5">
                <select class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white aje-row-acc focus:ring-2 focus:ring-amber-500">
                    ${allAccounts.map(a => `<option value="${a.id}" ${accId && a.id == accId ? 'selected' : ''}>[${a.code}] ${a.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" oninput="calculateAjeBalance()" value="${debit}" placeholder="Debit (Rp)" class="w-full p-2 text-xs rounded-xl border border-slate-300 aje-row-debit font-mono focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="col-span-3">
                <input type="number" oninput="calculateAjeBalance()" value="${credit}" placeholder="Kredit (Rp)" class="w-full p-2 text-xs rounded-xl border border-slate-300 aje-row-credit font-mono focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.aje-entry-row').remove(); calculateAjeBalance();" class="text-slate-400 hover:text-rose-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        container.appendChild(div);
    }

    function calculateAjeBalance() {
        const debits = document.querySelectorAll('.aje-row-debit');
        const credits = document.querySelectorAll('.aje-row-credit');

        let totalD = 0;
        let totalC = 0;

        debits.forEach(d => totalD += Number(d.value) || 0);
        credits.forEach(c => totalC += Number(c.value) || 0);

        const dEl = document.getElementById('calc-aje-debit');
        const cEl = document.getElementById('calc-aje-credit');
        const badge = document.getElementById('aje-balance-badge');
        const btnSave = document.getElementById('btn-save-aje');

        if (dEl) dEl.innerText = 'Rp ' + Number(totalD).toLocaleString('id-ID');
        if (cEl) cEl.innerText = 'Rp ' + Number(totalC).toLocaleString('id-ID');

        if (ajeMeter) {
            const isBalanced = ajeMeter.update();
            if (btnSave) btnSave.disabled = !isBalanced;
        } else {
            if (totalD > 0 && totalD === totalC) {
                if (badge) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold';
                    badge.innerText = 'BALANCE ✓';
                }
                if (btnSave) btnSave.disabled = false;
            } else {
                if (badge) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-bold';
                    badge.innerText = totalD !== totalC ? `SELISIH Rp ${Number(Math.abs(totalD - totalC)).toLocaleString('id-ID')}` : 'BELUM DIISI';
                }
                if (btnSave) btnSave.disabled = true;
            }
        }
    }

    function findAccByCode(code) {
        return allAccounts.find(a => a.code === code);
    }

    function applyAjeTemplate(type) {
        const container = document.getElementById('aje-rows-container');
        container.innerHTML = '';

        if (type === 'depreciation') {
            document.getElementById('aje-desc').value = 'Penyesuaian Beban Penyusutan Peralatan Toko & Kasir';
            const accDeb = findAccByCode('6106'); // Beban Penyusutan
            const accCred = findAccByCode('1202'); // Akumulasi Penyusutan
            addAjeRow(accDeb?.id, '', '', 'Beban Penyusutan Peralatan');
            addAjeRow(accCred?.id, '', '', 'Akumulasi Penyusutan');
        } else if (type === 'supplies') {
            document.getElementById('aje-desc').value = 'Penyesuaian Pemakaian Perlengkapan Toko / Kantor';
            const accDeb = findAccByCode('6104'); // Beban Perlengkapan
            const accCred = findAccByCode('1106'); // Perlengkapan Toko
            addAjeRow(accDeb?.id, '', '', 'Beban Perlengkapan Operasional');
            addAjeRow(accCred?.id, '', '', 'Pengurangan Perlengkapan');
        } else if (type === 'accrual') {
            document.getElementById('aje-desc').value = 'Penyesuaian Beban Akrual / Utang Operasional yang Masih Harus Dibayar';
            const accDeb = findAccByCode('6102'); // Beban Listrik, Air & Internet
            const accCred = findAccByCode('2104'); // Utang Beban
            addAjeRow(accDeb?.id, '', '', 'Beban Akrual Bulan Ini');
            addAjeRow(accCred?.id, '', '', 'Utang Beban Terutang');
        } else if (type === 'inventory') {
            document.getElementById('aje-desc').value = 'Penyesuaian Selisih / Kerusakan Persediaan Barang Dagang';
            const accDeb = findAccByCode('6107'); // Beban Penyesuaian Persediaan
            const accCred = findAccByCode('1105'); // Persediaan Barang
            addAjeRow(accDeb?.id, '', '', 'Beban Selisih Stok');
            addAjeRow(accCred?.id, '', '', 'Koreksi Nilai Persediaan');
        } else {
            addAjeRow();
            addAjeRow();
        }
        calculateAjeBalance();
    }

    function quickCreateAdjustment(type) {
        openAdjustmentModal();
        document.getElementById('aje-template-select').value = type;
        applyAjeTemplate(type);
    }

    async function submitAdjustmentJournal() {
        if (ajeMeter && !ajeMeter.guard()) {
            return;
        }

        const date = document.getElementById('aje-date').value;
        const branchId = document.getElementById('aje-branch').value;
        const desc = document.getElementById('aje-desc').value;

        if (!desc.trim()) {
            showToast('Deskripsi penyesuaian wajib diisi!', 'warning');
            return;
        }

        const rows = document.querySelectorAll('.aje-entry-row');
        const items = [];

        rows.forEach(r => {
            const accId = r.querySelector('.aje-row-acc').value;
            const debit = Number(r.querySelector('.aje-row-debit').value) || 0;
            const credit = Number(r.querySelector('.aje-row-credit').value) || 0;
            if (debit > 0 || credit > 0) {
                items.push({ account_id: accId, debit, credit, memo: desc });
            }
        });

        if (items.length < 2) {
            showToast('Minimal harus ada 2 baris akun terisi!', 'warning');
            return;
        }

        const btn = document.getElementById('btn-save-aje');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const formData = new FormData();
        formData.append('date', date);
        formData.append('branch_id', branchId);
        formData.append('description', desc);
        formData.append('ref_type', 'adjustment'); // ref_type = adjustment
        formData.append('items', JSON.stringify(items));

        try {
            const res = await fetch('../api/journals.php?action=create', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast('Jurnal Penyesuaian (AJE) berhasil diposting!', 'success');
                closeAdjustmentModal();
                await loadAdjustments();
            } else {
                showToast(result.message || 'Gagal menyimpan penyesuaian!', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan sistem!', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan & Posting Jurnal Penyesuaian';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
