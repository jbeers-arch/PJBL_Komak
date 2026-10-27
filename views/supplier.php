<?php
$pageTitle = 'Manajemen Supplier & Pemasok';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Manajemen Supplier &amp; Pemasok</h2>
            </div>
        </div>
        <button onclick="openAddSupplierModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Supplier Baru
        </button>
    </div>

    <!-- Quick Search & Stats Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" id="supplier-search-input" oninput="filterSupplierTable()" placeholder="Cari nama supplier, kontak, atau kota..."
                   class="w-full pl-10 pr-8 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
            <button type="button" onclick="document.getElementById('supplier-search-input').value=''; filterSupplierTable();" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-300 hover:text-slate-500">
                &times;
            </button>
        </div>

        <div class="flex items-center space-x-2 text-xs text-slate-500">
            <span class="font-medium">Total Rekanan Aktif:</span>
            <span id="supplier-count-badge" class="px-2.5 py-0.5 rounded-full bg-slate-100 font-bold text-slate-800 font-mono">0 supplier</span>
        </div>
    </div>

    <!-- Tabel Supplier -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                        <th class="py-3 px-4 w-28">Kode</th>
                        <th class="py-3 px-4">Nama Supplier</th>
                        <th class="py-3 px-4">Kontak PIC</th>
                        <th class="py-3 px-4">Telepon / WhatsApp</th>
                        <th class="py-3 px-4">Alamat</th>
                        <th class="py-3 px-4">Rekening Bank</th>
                        <th class="py-3 px-4 text-center w-28">Termin Bayar</th>
                        <th class="py-3 px-4 text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody id="supplier-table-body" class="divide-y divide-slate-100">
                    <tr><td colspan="8" class="py-12 text-center text-slate-400">Memuat daftar supplier...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Supplier -->
<div id="supplier-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 id="supplier-modal-title" class="font-extrabold text-sm text-slate-900">Tambah Supplier Baru</h4>
                <p class="text-[11px] text-slate-400">Informasi profil supplier untuk pembelian persediaan</p>
            </div>
            <button onclick="closeSupplierModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="supplier-form" onsubmit="submitSupplierForm(event)" class="space-y-3.5">
            <input type="hidden" id="sup-id" name="id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / Supplier</label>
                <input type="text" id="sup-name" name="name" placeholder="Contoh: PT Sumber Pangan Berkah" required
                       class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC (Kontak)</label>
                    <input type="text" id="sup-contact" name="contact_person" placeholder="Contoh: Budi Santoso"
                           class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WA</label>
                    <input type="text" id="sup-phone" name="phone" placeholder="Contoh: 08123456789"
                           class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor / Gudang Supplier</label>
                <textarea id="sup-address" name="address" rows="2" placeholder="Contoh: Jl. Industri No. 45, Rungkut, Surabaya"
                          class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank</label>
                    <input type="text" id="sup-bank-name" name="bank_name" placeholder="Contoh: BCA / Mandiri"
                           class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening</label>
                    <input type="text" id="sup-bank-account" name="bank_account" placeholder="Contoh: 8290123456"
                           class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono font-medium focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Termin Pembayaran (TOP)</label>
                <select id="sup-payment-terms" name="payment_terms" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="COD">COD (Cash on Delivery / Bayar Tunai Saat Kirim)</option>
                    <option value="Net 7">Net 7 Hari</option>
                    <option value="Net 14">Net 14 Hari</option>
                    <option value="Net 30">Net 30 Hari</option>
                </select>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" id="btn-submit-supplier" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors">
                    Simpan Data Supplier
                </button>
                <button type="button" onclick="closeSupplierModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let suppliersData = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadSuppliers();
    });

    async function loadSuppliers() {
        const tbody = document.getElementById('supplier-table-body');
        try {
            const res = await fetch('../api/suppliers.php?action=list');
            const result = await res.json();
            if (!result.success || !result.data) {
                tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-400">Tidak ada supplier aktif.</td></tr>';
                return;
            }

            suppliersData = result.data;
            renderSupplierRows(suppliersData);
            document.getElementById('supplier-count-badge').innerText = `${suppliersData.length} supplier`;
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-rose-500">Gagal memuat data supplier.</td></tr>';
        }
    }

    function renderSupplierRows(data) {
        const tbody = document.getElementById('supplier-table-body');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-400">Tidak ada supplier yang cocok.</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(s => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-3 px-4 font-mono font-bold text-slate-500">${s.code || '-'}</td>
                <td class="py-3 px-4 font-bold text-slate-900">${s.name}</td>
                <td class="py-3 px-4 text-slate-600">${s.contact_person || '-'}</td>
                <td class="py-3 px-4 font-mono text-slate-600">${s.phone || '-'}</td>
                <td class="py-3 px-4 text-slate-500 text-[11px] max-w-xs truncate" title="${s.address || ''}">${s.address || '-'}</td>
                <td class="py-3 px-4 font-mono text-slate-600">${s.bank_name ? s.bank_name + ' - ' + s.bank_account : '-'}</td>
                <td class="py-3 px-4 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        ${s.payment_terms || 'COD'}
                    </span>
                </td>
                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                    <button onclick="editSupplier(${s.id})" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold transition-colors">
                        Edit
                    </button>
                    <button onclick="deleteSupplier(${s.id}, '${s.name.replace(/'/g, "\\'")}')" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold transition-colors">
                        Hapus
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function filterSupplierTable() {
        const q = document.getElementById('supplier-search-input').value.toLowerCase();
        const filtered = suppliersData.filter(s => 
            s.name.toLowerCase().includes(q) ||
            (s.code && s.code.toLowerCase().includes(q)) ||
            (s.contact_person && s.contact_person.toLowerCase().includes(q)) ||
            (s.address && s.address.toLowerCase().includes(q))
        );
        renderSupplierRows(filtered);
    }

    function openAddSupplierModal() {
        document.getElementById('supplier-form').reset();
        document.getElementById('sup-id').value = '';
        document.getElementById('supplier-modal-title').innerText = 'Tambah Supplier Baru';
        document.getElementById('btn-submit-supplier').innerText = 'Simpan Data Supplier';
        document.getElementById('supplier-modal').classList.remove('hidden');
    }

    function editSupplier(id) {
        const s = suppliersData.find(item => item.id == id);
        if (!s) return;

        document.getElementById('sup-id').value = s.id;
        document.getElementById('sup-name').value = s.name;
        document.getElementById('sup-contact').value = s.contact_person || '';
        document.getElementById('sup-phone').value = s.phone || '';
        document.getElementById('sup-address').value = s.address || '';
        document.getElementById('sup-bank-name').value = s.bank_name || '';
        document.getElementById('sup-bank-account').value = s.bank_account || '';
        document.getElementById('sup-payment-terms').value = s.payment_terms || 'COD';

        document.getElementById('supplier-modal-title').innerText = 'Edit Profil Supplier';
        document.getElementById('btn-submit-supplier').innerText = 'Perbarui Data Supplier';
        document.getElementById('supplier-modal').classList.remove('hidden');
    }

    function closeSupplierModal() {
        document.getElementById('supplier-modal').classList.add('hidden');
    }

    async function submitSupplierForm(e) {
        e.preventDefault();
        const id = document.getElementById('sup-id').value;
        const action = id ? 'update' : 'create';
        const formData = new FormData(document.getElementById('supplier-form'));
        formData.append('action', action);

        const btn = document.getElementById('btn-submit-supplier');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        try {
            const res = await fetch('../api/suppliers.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeSupplierModal();
                loadSuppliers();
            } else {
                showToast(result.message || 'Gagal menyimpan supplier', 'error');
            }
        } catch (err) {
            showToast('Kesalahan koneksi ke server', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = id ? 'Perbarui Data Supplier' : 'Simpan Data Supplier';
        }
    }

    async function deleteSupplier(id, name) {
        const confirmed = await showConfirmDialog({
            title: 'Nonaktifkan Supplier?',
            message: `Apakah Anda yakin ingin menonaktifkan supplier "<strong>${name}</strong>"?`,
            confirmText: 'Ya, Nonaktifkan',
            cancelText: 'Batal',
            type: 'warning'
        });
        if (!confirmed) return;

        try {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);

            const res = await fetch('../api/suppliers.php', {
                method: 'POST',
                body: fd
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                loadSuppliers();
            } else {
                showToast(result.message || 'Gagal menonaktifkan supplier', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat menghapus', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
