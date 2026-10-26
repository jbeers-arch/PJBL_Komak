<?php
$pageTitle = 'Bagan Akun (Chart of Accounts)';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Bagan Akun (Chart of Accounts)</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Struktur klasifikasi akun keuangan standar untuk penjurnalan dan pelaporan akuntansi.</p>
        </div>
        <button onclick="openAddAccountModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Akun Baru
        </button>
    </div>

    <!-- Quick Search & Overview Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" id="coa-search-input" oninput="filterCOA()" placeholder="Cari kode akun atau nama akun..."
                   class="w-full pl-10 pr-8 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
            <button type="button" onclick="document.getElementById('coa-search-input').value=''; filterCOA();" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-300 hover:text-slate-500">
                &times;
            </button>
        </div>

        <div class="flex items-center space-x-2 text-xs text-slate-500">
            <span class="font-medium">Total Akun Terdaftar:</span>
            <span id="coa-total-count" class="px-2.5 py-0.5 rounded-full bg-slate-100 font-bold text-slate-800 font-mono">0 akun</span>
        </div>
    </div>

    <!-- Account Categories Accordion / Sections -->
    <div id="coa-container" class="space-y-4">
        <p class="text-xs text-slate-400 text-center py-12">Memuat bagan akun...</p>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div id="add-account-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Tambah Akun Baru</h4>
                <p class="text-[11px] text-slate-400">Daftarkan akun baru ke Bagan Akun (COA)</p>
            </div>
            <button onclick="closeAddAccountModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="add-account-form" onsubmit="submitAddAccount(event)" class="space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Akun</label>
                <select id="acc-category" name="category" onchange="autoSuggestNormalBalance(this.value, 'acc-normal-balance')" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500" required>
                    <option value="Aset">1 - Aset (Harta / Kas / Piutang / Persediaan)</option>
                    <option value="Liabilitas">2 - Liabilitas (Kewajiban / Utang)</option>
                    <option value="Ekuitas">3 - Ekuitas (Modal Pemilik / Laba Ditahan)</option>
                    <option value="Pendapatan">4 - Pendapatan (Penjualan / Pendapatan Lain)</option>
                    <option value="Beban_Pokok">5 - Beban Pokok Penjualan (HPP)</option>
                    <option value="Beban_Operasional">6 - Beban Operasional (Gaji / Sewa / Listrik)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Akun</label>
                <input type="text" id="acc-code" name="code" placeholder="Contoh: 6108" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Akun</label>
                <input type="text" id="acc-name" name="name" placeholder="Contoh: Beban Pemeliharaan Peralatan" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Saldo Normal</label>
                <select id="acc-normal-balance" name="normal_balance" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 bg-white font-bold focus:ring-2 focus:ring-indigo-500" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                    Simpan Akun
                </button>
                <button type="button" onclick="closeAddAccountModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit / Penyesuaian Akun COA -->
<div id="edit-account-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Sesuaikan / Edit Akun COA</h4>
                <p class="text-[11px] text-slate-400">Ubah kode atau nama akun</p>
            </div>
            <button onclick="closeEditAccountModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="edit-account-form" onsubmit="submitEditAccount(event)" class="space-y-3.5">
            <input type="hidden" id="edit-acc-id" name="id">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Akun</label>
                <select id="edit-acc-category" name="category" onchange="autoSuggestNormalBalance(this.value, 'edit-acc-normal-balance')" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500" required>
                    <option value="Aset">1 - Aset (Harta)</option>
                    <option value="Liabilitas">2 - Liabilitas (Kewajiban/Utang)</option>
                    <option value="Ekuitas">3 - Ekuitas (Modal)</option>
                    <option value="Pendapatan">4 - Pendapatan</option>
                    <option value="Beban_Pokok">5 - Beban Pokok Penjualan (HPP)</option>
                    <option value="Beban_Operasional">6 - Beban Operasional</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Akun</label>
                <input type="text" id="edit-acc-code" name="code" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Akun</label>
                <input type="text" id="edit-acc-name" name="name" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Saldo Normal</label>
                <select id="edit-acc-normal-balance" name="normal_balance" class="w-full p-2.5 text-xs rounded-2xl border border-slate-300 bg-white font-bold focus:ring-2 focus:ring-indigo-500" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeEditAccountModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let rawCOAData = null;

    document.addEventListener('DOMContentLoaded', loadCOA);

    async function loadCOA() {
        try {
            const res = await fetch('../api/accounts.php');
            const result = await res.json();
            if (!result.success) return;

            rawCOAData = result.grouped;
            renderCOATables(rawCOAData);
        } catch (e) {
            console.error('Error load COA:', e);
        }
    }

    function renderCOATables(groupedData) {
        const container = document.getElementById('coa-container');
        container.innerHTML = '';

        const categoryMeta = {
            'Aset': { label: '1. ASET & AKTIVA', color: 'indigo', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
            'Liabilitas': { label: '2. LIABILITAS & UTANG', color: 'amber', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            'Ekuitas': { label: '3. EKUITAS & MODAL', color: 'purple', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
            'Pendapatan': { label: '4. PENDAPATAN', color: 'emerald', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
            'Beban_Pokok': { label: '5. BEBAN POKOK PENJUALAN (HPP)', color: 'rose', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
            'Beban_Operasional': { label: '6. BEBAN OPERASIONAL & UMUM', color: 'orange', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' }
        };

        let totalAccountsCount = 0;

        for (const [cat, items] of Object.entries(groupedData)) {
            if (!items || items.length === 0) continue;
            totalAccountsCount += items.length;

            const meta = categoryMeta[cat] || { label: cat, color: 'slate', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' };

            const card = document.createElement('div');
            card.className = 'bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs space-y-3';
            card.innerHTML = `
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="w-7 h-7 rounded-xl bg-${meta.color}-50 text-${meta.color}-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${meta.icon}"/></svg>
                        </span>
                        <h3 class="text-xs font-extrabold text-slate-900 tracking-wider uppercase">${meta.label}</h3>
                    </div>
                    <span class="text-[11px] text-slate-500 font-bold bg-slate-100 px-2.5 py-0.5 rounded-full font-mono">${items.length} akun</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold tracking-wider">
                                <th class="pb-2.5 px-3 w-32">Kode Akun</th>
                                <th class="pb-2.5 px-3">Nama Akun</th>
                                <th class="pb-2.5 px-3 text-center w-32">Saldo Normal</th>
                                <th class="pb-2.5 px-3 text-right w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${items.map(acc => `
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">${acc.code}</td>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800">${acc.name}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${acc.normal_balance === 'debit' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-indigo-50 text-indigo-700 border border-indigo-200/80'}">
                                            ${acc.normal_balance.toUpperCase()}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right space-x-1.5">
                                        <button onclick="editAccount(${acc.id})" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-bold text-[11px] transition-colors">
                                            Edit
                                        </button>
                                        <button onclick="deleteAccount(${acc.id}, '${acc.name}')" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-bold text-[11px] transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            container.appendChild(card);
        }

        document.getElementById('coa-total-count').innerText = totalAccountsCount + ' akun';
    }

    function filterCOA() {
        if (!rawCOAData) return;
        const q = document.getElementById('coa-search-input').value.toLowerCase();
        
        const filteredGrouped = {};
        for (const [cat, items] of Object.entries(rawCOAData)) {
            filteredGrouped[cat] = items.filter(a => 
                a.code.toLowerCase().includes(q) || 
                a.name.toLowerCase().includes(q)
            );
        }
        renderCOATables(filteredGrouped);
    }

    function autoSuggestNormalBalance(category, elementId) {
        const balSelect = document.getElementById(elementId);
        if (category === 'Aset' || category === 'Beban_Pokok' || category === 'Beban_Operasional') {
            balSelect.value = 'debit';
        } else {
            balSelect.value = 'credit';
        }
    }

    function openAddAccountModal() {
        document.getElementById('add-account-modal').classList.remove('hidden');
    }

    function closeAddAccountModal() {
        document.getElementById('add-account-modal').classList.add('hidden');
    }

    async function editAccount(id) {
        try {
            const res = await fetch(`../api/accounts.php?action=get&id=${id}`);
            const result = await res.json();
            if (result.success && result.data) {
                const a = result.data;
                document.getElementById('edit-acc-id').value = a.id;
                document.getElementById('edit-acc-code').value = a.code;
                document.getElementById('edit-acc-name').value = a.name;
                document.getElementById('edit-acc-category').value = a.category;
                document.getElementById('edit-acc-normal-balance').value = a.normal_balance;
                document.getElementById('edit-account-modal').classList.remove('hidden');
            }
        } catch (e) {
            showToast('Gagal memuat data akun.', 'error');
        }
    }

    function closeEditAccountModal() {
        document.getElementById('edit-account-modal').classList.add('hidden');
    }

    async function submitAddAccount(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('add-account-form'));
        try {
            const res = await fetch('../api/accounts.php?action=create', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeAddAccountModal();
                loadCOA();
                document.getElementById('add-account-form').reset();
            } else {
                showToast(result.message || 'Gagal menyimpan akun.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat menyimpan akun.', 'error');
        }
    }

    async function submitEditAccount(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('edit-account-form'));
        try {
            const res = await fetch('../api/accounts.php?action=update', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeEditAccountModal();
                loadCOA();
            } else {
                showToast(result.message || 'Gagal memperbarui akun.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat memperbarui akun.', 'error');
        }
    }

    async function deleteAccount(id, name) {
        const confirmed = await showConfirmDialog({
            title: 'Hapus / Nonaktifkan Akun?',
            message: `Apakah Anda yakin ingin menghapus atau menonaktifkan akun "<strong>${name}</strong>"? Akun yang memiliki mutasi jurnal akan dinonaktifkan demi integritas buku besar.`,
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            type: 'warning'
        });
        if (!confirmed) return;

        try {
            const formData = new FormData();
            formData.append('id', id);

            const res = await fetch('../api/accounts.php?action=delete', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                loadCOA();
            } else {
                showToast(result.message || 'Gagal menghapus akun.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan saat menghapus akun.', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
