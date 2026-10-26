<?php
$pageTitle = 'Manajemen Cabang & Kasir';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Cabang &amp; Kasir</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola outlet cabang operasional, penugasan staf kasir, dan pantau penjualan harian.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="openAddAccessModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Tambah Akses Pengguna
            </button>
            <button onclick="openAddBranchModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Cabang Baru
            </button>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div class="flex items-center space-x-1.5 border-b border-slate-200/60 pb-3 overflow-x-auto no-scrollbar touch-scroll">
        <button onclick="filterBranchView('all')" id="tab-filter-all" class="shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-xs">
            Semua Cabang (<span id="count-all">0</span>)
        </button>
        <button onclick="filterBranchView('active')" id="tab-filter-active" class="shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all">
            Aktif (<span id="count-active">0</span>)
        </button>
        <button onclick="filterBranchView('inactive')" id="tab-filter-inactive" class="shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all">
            Nonaktif (<span id="count-inactive">0</span>)
        </button>
    </div>

    <!-- Branches Cards Grid -->
    <div id="branch-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <p class="text-xs text-slate-400 col-span-full text-center py-12">Memuat data cabang...</p>
    </div>
</div>

<!-- Modal Tambah Cabang -->
<div id="add-branch-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-base text-slate-900">Tambah Cabang Baru</h4>
                <p class="text-xs text-slate-400">Daftarkan outlet baru dan siapkan kas kasir otomatis</p>
            </div>
            <button onclick="closeAddBranchModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form id="add-branch-form" onsubmit="submitAddBranch(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Cabang <span class="text-rose-500">*</span></label>
                    <input type="text" id="add-branch-code" name="code" placeholder="Contoh: CBG-03" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-500 uppercase" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No Telepon Outlet</label>
                    <input type="text" name="phone" placeholder="031-8280000" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cabang / Outlet <span class="text-rose-500">*</span></label>
                <input type="text" id="add-branch-name" name="name" oninput="syncCashierDefaults()" placeholder="Contoh: Cabang 3 - Surabaya Selatan" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Outlet</label>
                <input type="text" name="address" placeholder="Jl. Ahmad Yani No. 50, Surabaya" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Opsi Buat Akun Kasir Langsung -->
            <div class="p-3.5 bg-indigo-50/60 rounded-2xl border border-indigo-100 space-y-3">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" id="create_cashier_toggle" name="create_cashier" value="1" checked onchange="toggleCashierFields()" class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                    <span class="text-xs font-bold text-indigo-950">Buatkan Akun Kasir untuk Cabang Ini</span>
                </label>

                <div id="cashier-fields" class="space-y-2.5 pt-1">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Nama Kasir</label>
                        <input type="text" id="cashier_name" name="cashier_name" placeholder="Nama Kasir (contoh: Siti Aminah)" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Username Kasir</label>
                            <input type="text" id="cashier_username" name="cashier_username" placeholder="kasir_cbg3" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Password Awal</label>
                            <input type="text" id="cashier_password" name="cashier_password" value="kasir123" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-blue-50/80 rounded-2xl border border-blue-200/80 text-[11px] text-blue-800 flex items-start space-x-2">
                <span class="text-blue-600 font-bold shrink-0 mt-0.5">💡</span>
                <span>Sistem otomatis membuatkan akun <strong>Kas Kasir</strong> di COA dan menyiapkan catatan stok awal untuk seluruh bahan &amp; produk laundry.</span>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" id="btn-submit-branch" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                    Simpan Cabang Baru
                </button>
                <button type="button" onclick="closeAddBranchModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Cabang -->
<div id="edit-branch-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Perbarui Data Cabang</h4>
                <p class="text-[11px] text-slate-400" id="edit-modal-code-badge">Kode: -</p>
            </div>
            <button onclick="closeEditBranchModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form id="edit-branch-form" onsubmit="submitEditBranch(event)" class="space-y-3.5">
            <input type="hidden" id="edit-branch-id" name="id">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Cabang / Outlet</label>
                <input type="text" id="edit-branch-name" name="name" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Outlet</label>
                <input type="text" id="edit-branch-address" name="address" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No Telepon</label>
                <input type="text" id="edit-branch-phone" name="phone" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Operasional</label>
                <select id="edit-branch-status" name="is_active" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="1">Aktif Beroperasi</option>
                    <option value="0">Nonaktif / Tutup Sementara</option>
                </select>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeEditBranchModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus / Nonaktifkan Cabang -->
<div id="delete-branch-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-base text-slate-900">Konfirmasi Hapus Cabang</h4>
                <p class="text-xs text-slate-400" id="delete-modal-subtitle">Tentukan tindakan penghapusan outlet</p>
            </div>
            <button onclick="closeDeleteBranchModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <input type="hidden" id="delete-target-id">

        <!-- Info Box Ringkasan Cabang & Data Terkait -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Nama Cabang:</span>
                <span class="font-bold text-slate-900" id="delete-modal-branch-name">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Kode Outlet:</span>
                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md" id="delete-modal-branch-code">-</span>
            </div>
            <div id="delete-modal-stats" class="pt-2 border-t border-slate-200/60 space-y-1.5">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Transaksi Penjualan Kasir:</span>
                    <span class="font-bold font-mono text-slate-900" id="delete-modal-trx-count">0 transaksi</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Jurnal Pembukuan:</span>
                    <span class="font-bold font-mono text-slate-900" id="delete-modal-journal-count">0 jurnal</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Staf Kasir Terdaftar:</span>
                    <span class="font-bold text-slate-900" id="delete-modal-cashier-count">0 kasir</span>
                </div>
            </div>
        </div>

        <div id="delete-modal-warning" class="p-3 bg-amber-50 rounded-2xl border border-amber-200/80 text-[11px] text-amber-900 space-y-1">
            <p class="font-bold flex items-center space-x-1">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Pilihan Tindakan:</span>
            </p>
            <p class="text-amber-800 leading-relaxed" id="delete-modal-desc">
                Pilih <strong>Hapus Permanen</strong> untuk menghapus cabang beserta seluruh data transaksi kasir dan jurnal terkait. Atau pilih <strong>Nonaktifkan Saja</strong> untuk menutup operasional tanpa menghapus riwayat.
            </p>
        </div>

        <div class="space-y-2 pt-2">
            <!-- Tombol Hapus Permanen -->
            <button type="button" id="btn-force-delete" onclick="confirmExecuteDelete(true)" class="w-full py-2.5 px-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center justify-center space-x-1.5 shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Hapus Permanen Cabang</span>
            </button>

            <!-- Tombol Nonaktifkan Saja -->
            <button type="button" id="btn-deactivate-branch" onclick="executeDeactivateBranch()" class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center space-x-1.5 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Nonaktifkan Saja (Arsipkan)</span>
            </button>

            <!-- Batal -->
            <button type="button" onclick="closeDeleteBranchModal()" class="w-full py-2 text-center text-xs font-semibold text-slate-400 hover:text-slate-600">
                Batal
            </button>
        </div>
    </div>
</div>

<!-- Modal Kelola Akses & Kasir -->
<div id="cashier-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 my-8 max-h-[90vh] flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900" id="cashier-modal-title">Kelola Akses Pengguna &amp; Kasir</h4>
                <p class="text-[11px] text-slate-400">Daftar akun login dan penugasan hak akses cabang</p>
            </div>
            <button onclick="closeCashierModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
        </div>

        <div class="space-y-4 overflow-y-auto pr-1">
            <!-- Filter Cabang -->
            <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-2xl border border-slate-200/70 text-xs">
                <span class="text-slate-500 font-medium">Lingkup Cabang:</span>
                <select id="modal-branch-filter" onchange="onModalBranchFilterChange(this.value)" class="p-1.5 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-800">
                    <option value="0">Semua Cabang (Seluruh Jaringan)</option>
                    <?php foreach ($activeBranches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Daftar Pengguna Saat Ini -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Akses Terdaftar</h5>
                    <span id="modal-users-count" class="text-[10px] font-mono text-slate-400 font-bold">0 akun</span>
                </div>
                <div id="cashier-list-container" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <p class="text-xs text-slate-400">Memuat pengguna...</p>
                </div>
            </div>

            <!-- Form Tambah Akses Baru -->
            <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 space-y-3">
                <h5 class="text-xs font-bold text-indigo-950 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Tambah Akses / Pengguna Baru</span>
                </h5>
                <form id="add-cashier-form" onsubmit="submitAddUserAccess(event)" class="space-y-2.5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama Lengkap Pengguna <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="form-user-name" placeholder="Contoh: Siti Rahmawati / Budi Santoso" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Username Login <span class="text-rose-500">*</span></label>
                            <input type="text" name="username" id="form-user-username" placeholder="misal: kasir_rahma" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Password Login <span class="text-rose-500">*</span></label>
                            <input type="text" name="password" id="form-user-password" placeholder="Password" value="kasir123" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Peran / Hak Akses <span class="text-rose-500">*</span></label>
                            <select name="role" id="form-user-role" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500" required>
                                <option value="kasir">Kasir (POS &amp; Operasional Toko)</option>
                                <option value="akuntan">Akuntan (Jurnal &amp; Laporan SAK-EMKM)</option>
                                <option value="superadmin">Superadmin (Kendali Penuh)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Penugasan Cabang <span class="text-rose-500">*</span></label>
                            <select id="form-user-branch" name="branch_id" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500" required>
                                <?php foreach ($activeBranches as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors mt-1">
                        + Simpan Hak Akses Baru
                    </button>
                </form>
            </div>
        </div>

        <div class="flex justify-end pt-3 border-t border-slate-100 shrink-0">
            <button type="button" onclick="closeCashierModal()" class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>
</div>

<script>
    let branchDataCache = [];
    let currentFilter = 'all';

    document.addEventListener('DOMContentLoaded', loadBranches);

    async function loadBranches() {
        try {
            const res = await fetch('../api/branches.php?action=list&all=1');
            const result = await res.json();
            if (!result.success) return;

            branchDataCache = result.data;
            updateFilterCounts(result.data);
            renderFilteredBranches();
            suggestNextBranchCode(result.data);
        } catch (e) {
            console.error('Error load branches:', e);
        }
    }

    function updateFilterCounts(branches) {
        const total = branches.length;
        const active = branches.filter(b => Number(b.is_active) === 1).length;
        const inactive = branches.filter(b => Number(b.is_active) === 0).length;

        document.getElementById('count-all').innerText = total;
        document.getElementById('count-active').innerText = active;
        document.getElementById('count-inactive').innerText = inactive;
    }

    function filterBranchView(filter) {
        currentFilter = filter;
        
        ['all', 'active', 'inactive'].forEach(f => {
            const btn = document.getElementById('tab-filter-' + f);
            if (f === filter) {
                btn.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-xs';
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all';
            }
        });

        renderFilteredBranches();
    }

    function renderFilteredBranches() {
        let filtered = branchDataCache;
        if (currentFilter === 'active') {
            filtered = branchDataCache.filter(b => Number(b.is_active) === 1);
        } else if (currentFilter === 'inactive') {
            filtered = branchDataCache.filter(b => Number(b.is_active) === 0);
        }
        renderBranchCards(filtered);
    }

    function renderBranchCards(branches) {
        const grid = document.getElementById('branch-cards-grid');
        grid.innerHTML = '';

        if (!branches || branches.length === 0) {
            grid.innerHTML = '<p class="text-xs text-slate-400 col-span-full text-center py-12">Tidak ada cabang pada filter ini.</p>';
            return;
        }

        branches.forEach(b => {
            const isPusat = (Number(b.id) === 1);
            const isActive = (Number(b.is_active) === 1);

            const card = document.createElement('div');
            card.className = `bg-white rounded-3xl border ${isActive ? 'border-slate-200/80' : 'border-slate-200 bg-slate-50/50'} p-5 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group`;
            
            card.innerHTML = `
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${isPusat ? 'from-amber-500 to-indigo-600' : (isActive ? 'from-indigo-500 to-blue-500' : 'from-slate-400 to-slate-500')}"></div>
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-mono text-xs font-bold ${isPusat ? 'text-amber-800 bg-amber-50 border border-amber-200/80' : 'text-indigo-700 bg-indigo-50 border border-indigo-200/60'} px-2.5 py-0.5 rounded-lg">${escapeHtml(b.code)}</span>
                            ${isPusat ? '<span class="text-[10px] font-bold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-md">Pusat</span>' : ''}
                        </div>
                        <span class="inline-flex items-center text-[10px] font-bold ${isActive ? 'text-emerald-700 bg-emerald-50 border border-emerald-200/80' : 'text-slate-600 bg-slate-100 border border-slate-200'} px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full ${isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'} mr-1.5"></span>
                            ${isActive ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 mt-3 group-hover:text-indigo-600 transition-colors">${escapeHtml(b.name)}</h3>
                    
                    <div class="mt-2.5 space-y-1.5 text-xs text-slate-500">
                        <p class="flex items-start space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="line-clamp-2">${escapeHtml(b.address || 'Alamat belum diatur')}</span>
                        </p>
                        <p class="flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>${escapeHtml(b.phone || '-')}</span>
                        </p>
                    </div>

                    <!-- Staf Kasir Info -->
                    <div class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 font-medium">Staf Kasir Bertugas:</span>
                        <span class="font-bold text-indigo-700">${b.cashier_count > 0 ? (b.cashier_count + ' Kasir') : '<span class="text-slate-400">Belum ada</span>'}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider">Penjualan Hari Ini</span>
                            <span class="font-black text-slate-900 font-mono text-sm">Rp ${Number(b.today_sales || 0).toLocaleString('id-ID')}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-1.5 pt-1">
                        <button onclick="openCashierModal(${b.id}, '${escapeHtml(b.name)}', '${escapeHtml(b.code)}')" class="flex-1 inline-flex items-center justify-center py-2 px-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Kelola Akses (${b.cashier_count})
                        </button>
                        <button onclick="openEditBranchModal(${b.id})" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Edit Cabang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        ${!isPusat ? `
                        <button onclick="openDeleteBranchModal(${b.id})" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors" title="Hapus / Nonaktifkan Cabang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>` : ''}
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function suggestNextBranchCode(branches) {
        if (!branches) return;
        let maxNum = 2;
        branches.forEach(b => {
            const match = b.code.match(/CBG-(\d+)/i);
            if (match) {
                const num = parseInt(match[1]);
                if (num > maxNum) maxNum = num;
            }
        });
        const nextCode = 'CBG-' + String(maxNum + 1).padStart(2, '0');
        const codeInput = document.getElementById('add-branch-code');
        if (codeInput && !codeInput.value) {
            codeInput.value = nextCode;
        }
    }

    function syncCashierDefaults() {
        const nameVal = document.getElementById('add-branch-name').value.trim();
        const codeVal = document.getElementById('add-branch-code').value.trim();
        const cashierNameInput = document.getElementById('cashier_name');
        const cashierUserInput = document.getElementById('cashier_username');

        if (nameVal && (!cashierNameInput.value || cashierNameInput.dataset.auto === '1')) {
            cashierNameInput.value = 'Kasir ' + nameVal;
            cashierNameInput.dataset.auto = '1';
        }
        if (codeVal && (!cashierUserInput.value || cashierUserInput.dataset.auto === '1')) {
            const clean = codeVal.toLowerCase().replace(/[^a-z0-9]/g, '');
            cashierUserInput.value = 'kasir_' + clean;
            cashierUserInput.dataset.auto = '1';
        }
    }

    function toggleCashierFields() {
        const chk = document.getElementById('create_cashier_toggle');
        const fields = document.getElementById('cashier-fields');
        if (chk.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    function openAddBranchModal() {
        document.getElementById('add-branch-modal').classList.remove('hidden');
        suggestNextBranchCode(branchDataCache);
        syncCashierDefaults();
    }

    function closeAddBranchModal() {
        document.getElementById('add-branch-modal').classList.add('hidden');
    }

    async function submitAddBranch(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-branch');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';

        const formData = new FormData(document.getElementById('add-branch-form'));
        try {
            const res = await fetch('../api/branches.php?action=create', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                let msg = result.message;
                if (result.cashier) {
                    msg += ` Akun kasir siap: username '${result.cashier.username}', password '${result.cashier.password}'.`;
                }
                showToast(msg, 'success');
                closeAddBranchModal();
                document.getElementById('add-branch-form').reset();
                loadBranches();
            } else {
                showToast(result.message || 'Gagal menambah cabang.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat menambah cabang.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Cabang Baru';
        }
    }

    function openEditBranchModal(id) {
        const b = branchDataCache.find(x => Number(x.id) === Number(id));
        if (!b) return;

        document.getElementById('edit-branch-id').value = b.id;
        document.getElementById('edit-modal-code-badge').innerText = `Kode: ${b.code}`;
        document.getElementById('edit-branch-name').value = b.name;
        document.getElementById('edit-branch-address').value = b.address || '';
        document.getElementById('edit-branch-phone').value = b.phone || '';
        document.getElementById('edit-branch-status').value = b.is_active;

        document.getElementById('edit-branch-modal').classList.remove('hidden');
    }

    function closeEditBranchModal() {
        document.getElementById('edit-branch-modal').classList.add('hidden');
    }

    async function submitEditBranch(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('edit-branch-form'));
        try {
            const res = await fetch('../api/branches.php?action=update', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeEditBranchModal();
                loadBranches();
            } else {
                showToast(result.message || 'Gagal memperbarui data.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat memperbarui cabang.', 'error');
        }
    }

    // Modal Konfirmasi Hapus / Nonaktifkan Cabang
    async function openDeleteBranchModal(id) {
        const b = branchDataCache.find(x => Number(x.id) === Number(id));
        if (!b) return;

        document.getElementById('delete-target-id').value = b.id;
        document.getElementById('delete-modal-branch-name').innerText = b.name;
        document.getElementById('delete-modal-branch-code').innerText = b.code;
        document.getElementById('delete-modal-cashier-count').innerText = `${b.cashier_count} kasir`;

        // Ambil info data terkait dari backend
        try {
            const checkRes = await fetch(`../api/branches.php?action=delete&id=${b.id}`);
            const checkData = await checkRes.json();
            
            const trxCnt = checkData.trx_count !== undefined ? checkData.trx_count : 0;
            const jrnCnt = checkData.journal_count !== undefined ? checkData.journal_count : 0;

            document.getElementById('delete-modal-trx-count').innerText = `${trxCnt} transaksi`;
            document.getElementById('delete-modal-journal-count').innerText = `${jrnCnt} jurnal`;

            const warningBox = document.getElementById('delete-modal-warning');
            const desc = document.getElementById('delete-modal-desc');

            if (trxCnt === 0 && jrnCnt === 0) {
                desc.innerHTML = 'Cabang ini belum memiliki riwayat transaksi atau jurnal. Anda dapat menghapusnya secara aman dan permanen dari basis data.';
                warningBox.className = 'p-3 bg-blue-50 rounded-2xl border border-blue-200 text-[11px] text-blue-800 space-y-1';
            } else {
                desc.innerHTML = `Cabang ini memiliki <strong>${trxCnt} transaksi kasir</strong> dan <strong>${jrnCnt} jurnal pembukuan</strong>. Pilih <strong>Hapus Permanen</strong> untuk menghapus cabang beserta seluruh data ujinya, atau <strong>Nonaktifkan Saja</strong> untuk menutup outlet tanpa menghapus histori.`;
                warningBox.className = 'p-3 bg-amber-50 rounded-2xl border border-amber-200/80 text-[11px] text-amber-900 space-y-1';
            }
        } catch (e) {
            document.getElementById('delete-modal-trx-count').innerText = '0 transaksi';
            document.getElementById('delete-modal-journal-count').innerText = '0 jurnal';
        }

        document.getElementById('delete-branch-modal').classList.remove('hidden');
    }

    function closeDeleteBranchModal() {
        document.getElementById('delete-branch-modal').classList.add('hidden');
    }

    async function confirmExecuteDelete(force = true) {
        const id = document.getElementById('delete-target-id').value;
        const b = branchDataCache.find(x => Number(x.id) === Number(id));
        const bName = b ? b.name : 'Cabang';

        const confirmed = await showConfirmDialog({
            title: 'Hapus Permanen Cabang?',
            message: `Konfirmasi akhir: Apakah Anda yakin ingin MENGHAPUS PERMANEN cabang "${bName}"? Tindakan ini akan menghapus seluruh data transaksi kasir dan jurnal terkait secara tuntas dan tidak dapat dibatalkan.`,
            confirmText: 'Ya, Hapus Permanen',
            cancelText: 'Batal',
            type: 'danger'
        });
        if (!confirmed) return;

        const btn = document.getElementById('btn-force-delete');
        btn.disabled = true;
        btn.innerHTML = 'Menghapus...';

        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('force', '1');

            const res = await fetch('../api/branches.php?action=delete', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeDeleteBranchModal();
                loadBranches();
            } else {
                showToast(result.message || 'Gagal menghapus cabang.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan server saat menghapus cabang.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Hapus Permanen Cabang</span>
            `;
        }
    }

    async function executeDeactivateBranch() {
        const id = document.getElementById('delete-target-id').value;
        try {
            const formData = new FormData();
            formData.append('id', id);

            const res = await fetch('../api/branches.php?action=deactivate', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeDeleteBranchModal();
                loadBranches();
            } else {
                showToast(result.message || 'Gagal menonaktifkan cabang.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan server saat menonaktifkan cabang.', 'error');
        }
    }

    // Modal Kelola Akses Pengguna & Kasir
    function openAddAccessModal() {
        document.getElementById('cashier-modal-title').innerText = 'Kelola Akses Pengguna & Kasir';
        document.getElementById('modal-branch-filter').value = '0';
        document.getElementById('form-user-branch').value = '1';
        document.getElementById('form-user-role').value = 'kasir';
        document.getElementById('cashier-modal').classList.remove('hidden');
        loadCashiersList(0);
    }

    async function openCashierModal(branchId, branchName, branchCode) {
        document.getElementById('cashier-modal-title').innerText = `Akses Cabang: ${branchName} (${branchCode})`;
        document.getElementById('modal-branch-filter').value = branchId;
        document.getElementById('form-user-branch').value = branchId;
        document.getElementById('form-user-role').value = 'kasir';
        document.getElementById('cashier-modal').classList.remove('hidden');
        loadCashiersList(branchId);
    }

    function onModalBranchFilterChange(branchId) {
        if (Number(branchId) > 0) {
            document.getElementById('form-user-branch').value = branchId;
        }
        loadCashiersList(branchId);
    }

    function closeCashierModal() {
        document.getElementById('cashier-modal').classList.add('hidden');
    }

    async function loadCashiersList(branchId = 0) {
        const container = document.getElementById('cashier-list-container');
        container.innerHTML = '<p class="text-xs text-slate-400 py-3 text-center">Memuat daftar pengguna...</p>';

        try {
            const url = (branchId && Number(branchId) > 0) 
                ? `../api/branches.php?action=cashiers&branch_id=${branchId}`
                : `../api/branches.php?action=cashiers`;

            const res = await fetch(url);
            const result = await res.json();
            const countBadge = document.getElementById('modal-users-count');
            if (countBadge) countBadge.innerText = `${(result.data || []).length} akun`;

            if (!result.success || !result.data || result.data.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-400 py-3 text-center">Belum ada akun pengguna yang terdaftar pada lingkup ini.</p>';
                return;
            }

            container.innerHTML = '';
            result.data.forEach(u => {
                const roleUpper = (u.role || 'kasir').toUpperCase();
                let roleBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                if (u.role === 'akuntan') roleBadgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                if (u.role === 'superadmin') roleBadgeClass = 'bg-purple-50 text-purple-700 border-purple-200';

                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:border-indigo-300 transition-colors text-xs';
                item.innerHTML = `
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold shrink-0 text-xs uppercase">
                            ${escapeHtml((u.name || 'User').substring(0, 2))}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 truncate">${escapeHtml(u.name)}</div>
                            <div class="flex items-center space-x-1.5 text-[11px] text-slate-400 font-mono mt-0.5">
                                <span>@${escapeHtml(u.username)}</span>
                                <span>&bull;</span>
                                <span class="text-slate-600 truncate">${escapeHtml(u.branch_name || 'Kantor Pusat')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md border ${roleBadgeClass}">
                            ${roleUpper}
                        </span>
                        <span class="text-[10px] font-bold ${u.is_active == 1 ? 'text-emerald-700 bg-emerald-50' : 'text-slate-500 bg-slate-100'} px-2 py-0.5 rounded-md">
                            ${u.is_active == 1 ? 'Aktif' : 'Nonaktif'}
                        </span>
                        <button onclick="deleteUserAccess(${u.id}, '${escapeHtml(u.name)}')" class="p-1 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Cabut Akses Pengguna">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                `;
                container.appendChild(item);
            });
        } catch (e) {
            container.innerHTML = '<p class="text-xs text-rose-500 py-3 text-center">Gagal memuat staf pengguna.</p>';
        }
    }

    async function submitAddUserAccess(e) {
        e.preventDefault();
        const form = document.getElementById('add-cashier-form');
        const formData = new FormData(form);
        const filterBranch = document.getElementById('modal-branch-filter').value;

        try {
            const res = await fetch('../api/branches.php?action=add_user', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                document.getElementById('form-user-name').value = '';
                document.getElementById('form-user-username').value = '';
                document.getElementById('form-user-password').value = 'kasir123';
                loadCashiersList(filterBranch);
                loadBranches();
            } else {
                showToast(result.message || 'Gagal menambahkan akses.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat menambahkan akses pengguna.', 'error');
        }
    }

    const submitAddCashier = submitAddUserAccess;

    async function deleteUserAccess(userId, userName) {
        if (!confirm(`Cabut / Hapus hak akses untuk pengguna '${userName}'?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('user_id', userId);

        try {
            const res = await fetch('../api/branches.php?action=delete_user', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                const filterBranch = document.getElementById('modal-branch-filter').value;
                loadCashiersList(filterBranch);
                loadBranches();
            } else {
                showToast(result.message || 'Gagal menghapus pengguna.', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat menghapus akses pengguna.', 'error');
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
