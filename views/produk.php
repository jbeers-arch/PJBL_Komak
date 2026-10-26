<?php
$pageTitle = 'Katalog Layanan & Bahan Laundry';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Katalog Layanan &amp; Bahan Laundry</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola paket layanan laundry kiloan, satuan, dry cleaning, dan bahan pendukung operasional.</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <button onclick="openTransferModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transfer Antar Cabang
            </button>
            <button onclick="openAddProductModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Layanan / Item
            </button>
            <button onclick="openAdjustStockModal()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Penyesuaian Stok &amp; Kuota
            </button>
        </div>
    </div>

    <!-- Segmented Tab Selector -->
    <div class="bg-slate-200/60 p-1.5 rounded-2xl flex items-center space-x-1 max-w-xl">
        <button onclick="switchTab('catalog')" id="tab-btn-catalog" class="flex-1 py-2 px-3.5 rounded-xl font-bold text-xs bg-white text-indigo-700 shadow-xs transition-all text-center">
            Katalog Produk & Stok
        </button>
        <button onclick="switchTab('history')" id="tab-btn-history" class="flex-1 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 transition-all text-center">
            Riwayat Penyesuaian
        </button>
        <button onclick="switchTab('transfers')" id="tab-btn-transfers" class="flex-1 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 transition-all text-center">
            Mutasi & Transfer Cabang
        </button>
    </div>

    <!-- Tab 1: Katalog Produk -->
    <div id="tab-content-catalog" class="space-y-4">
        <!-- Quick Search Bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="prod-search-input" oninput="filterProductTable()" placeholder="Cari nama barang atau kode produk..."
                       class="w-full pl-10 pr-8 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                <button type="button" onclick="document.getElementById('prod-search-input').value=''; filterProductTable();" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-300 hover:text-slate-500">
                    &times;
                </button>
            </div>

            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <span class="font-medium">Total Produk:</span>
                <span id="product-count-badge" class="px-2.5 py-0.5 rounded-full bg-slate-100 font-bold text-slate-800 font-mono">0 produk</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                            <th class="py-3 px-4 w-32">Kode</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4 w-28">Kategori</th>
                            <th class="py-3 px-4 text-right w-32">Harga Jual</th>
                            <th class="py-3 px-4 text-right w-32">HPP (Modal)</th>
                            <th class="py-3 px-4 text-center w-36">Stok Tersedia</th>
                            <th class="py-3 px-4 text-right w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body" class="divide-y divide-slate-100">
                        <tr><td colspan="7" class="py-12 text-center text-slate-400">Memuat katalog produk...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Riwayat Penyesuaian Stok -->
    <div id="tab-content-history" class="hidden space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Log Riwayat Penyesuaian & Opname Stok</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Catatan audit perubahan kuantitas barang dagang</p>
                </div>
                <span class="text-xs text-slate-500 font-medium">50 catatan terakhir</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold tracking-wider">
                            <th class="pb-3 px-3">Waktu</th>
                            <th class="pb-3 px-3">Produk</th>
                            <th class="pb-3 px-3">Tipe</th>
                            <th class="pb-3 px-3 text-center">Stok Awal</th>
                            <th class="pb-3 px-3 text-center">Perubahan</th>
                            <th class="pb-3 px-3 text-center">Stok Akhir</th>
                            <th class="pb-3 px-3">Alasan / Catatan</th>
                            <th class="pb-3 px-3">Oleh</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="divide-y divide-slate-100">
                        <tr><td colspan="8" class="py-12 text-center text-slate-400">Memuat riwayat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Mutasi & Transfer Antar Cabang -->
    <div id="tab-content-transfers" class="hidden space-y-4">
        <!-- Multi-Branch Stocks Overview Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Ketersediaan Stok per Cabang / Gudang</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau alokasi kuantitas produk di setiap outlet</p>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-semibold text-slate-600">Pilih Cabang:</label>
                    <select id="branch-stock-selector" onchange="loadBranchSpecificStocks(this.value)" class="p-2 text-xs rounded-xl border border-slate-200 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                            <th class="py-2.5 px-3">Kode</th>
                            <th class="py-2.5 px-3">Nama Produk</th>
                            <th class="py-2.5 px-3 text-center">Stok di Cabang Ini</th>
                            <th class="py-2.5 px-3 text-center">Total Global (Pusat + Cabang)</th>
                            <th class="py-2.5 px-3 text-right">Status Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody id="branch-stocks-table-body" class="divide-y divide-slate-100 font-mono">
                        <tr><td colspan="5" class="py-8 text-center text-slate-400 font-sans">Memuat data stok cabang...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Log Transfer Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Riwayat Dokumen Transfer Antar Cabang</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Surat jalan & pencatatan perpindahan stok internal</p>
                </div>
                <button onclick="loadTransfers()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    Segarkan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                            <th class="py-2.5 px-3">No. Transfer</th>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Cabang Asal</th>
                            <th class="py-2.5 px-3">Cabang Tujuan</th>
                            <th class="py-2.5 px-3 text-center">Total Item</th>
                            <th class="py-2.5 px-3">Catatan / Keterangan</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="transfers-table-body" class="divide-y divide-slate-100">
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Memuat riwayat transfer...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk Baru -->
<div id="add-product-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Tambah Layanan / Item Baru</h4>
                <p class="text-[11px] text-slate-400">Daftarkan paket laundry atau bahan untuk kasir cabang</p>
            </div>
            <button onclick="closeAddProductModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="add-product-form" onsubmit="submitAddProduct(event)" class="space-y-3.5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Layanan</label>
                    <input type="text" name="code" placeholder="Contoh: LND-017" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                    <select name="category" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                        <option value="Laundry Kiloan">Laundry Kiloan</option>
                        <option value="Laundry Satuan">Laundry Satuan</option>
                        <option value="Dry Cleaning">Dry Cleaning</option>
                        <option value="Bahan & Perlengkapan">Bahan & Perlengkapan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Layanan / Item</label>
                <input type="text" name="name" placeholder="Contoh: Cuci Karpet Masjid / Bedcover King" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Layanan (Rp)</label>
                    <input type="number" name="price" placeholder="20000" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold font-mono focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">HPP / Biaya Bahan (Rp)</label>
                    <input type="number" name="cost_price" placeholder="5000" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Stok / Kapasitas Awal</label>
                    <input type="number" name="stock" value="999" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                    <select name="unit" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-indigo-500">
                        <option value="kg">kg (Kilogram)</option>
                        <option value="pcs">pcs (Potong / Satuan)</option>
                        <option value="pasang">pasang (Sepatu / Sandal)</option>
                        <option value="meter">meter (Karpet / Gordyn)</option>
                        <option value="botol">botol (Parfum / Bahan)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors">
                    Simpan Layanan
                </button>
                <button type="button" onclick="closeAddProductModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Penyesuaian Stok (Stock Opname) -->
<div id="adjust-stock-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Penyesuaian Stok Inventory</h4>
                <p class="text-[11px] text-slate-400">Sinkronkan stok fisik dengan pencatatan akuntansi</p>
            </div>
            <button onclick="closeAdjustStockModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="adjust-stock-form" onsubmit="submitAdjustStock(event)" class="space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk</label>
                <select id="adj-product-id" name="product_id" onchange="onSelectProductToAdjust()" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-amber-500" required></select>
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Stok Sistem Saat Ini:</span>
                <span id="current-system-stock" class="font-extrabold text-indigo-700 text-sm font-mono">0 pcs</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Penyesuaian</label>
                <select id="adj-type" name="type" onchange="toggleAdjustInputMode()" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium">
                    <option value="opname">Stock Opname (Isi Jumlah Stok Fisik Hasil Hitung)</option>
                    <option value="masuk">Barang Masuk / Tambah Stok (+ Qty)</option>
                    <option value="keluar">Barang Keluar / Terpakai (- Qty)</option>
                    <option value="rusak">Barang Rusak / Kadaluarsa (- Qty)</option>
                </select>
            </div>

            <div id="div-opname-qty">
                <label class="block text-xs font-bold text-slate-700 mb-1">Stok Fisik Sebenarnya</label>
                <input type="number" id="adj-new-stock" name="new_stock" placeholder="0" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold font-mono focus:ring-2 focus:ring-amber-500">
            </div>

            <div id="div-delta-qty" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Perubahan Qty</label>
                <input type="number" id="adj-qty" name="qty" placeholder="Contoh: 10" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold font-mono focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penyesuaian</label>
                <input type="text" name="reason" placeholder="Contoh: Selisih hitung opname akhir bulan / Barang basah" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500" required>
            </div>

            <div class="p-3 bg-amber-50/80 rounded-2xl border border-amber-200">
                <label class="flex items-center space-x-2 text-xs text-amber-900 font-bold cursor-pointer">
                    <input type="checkbox" name="auto_journal" value="1" checked class="rounded text-amber-600">
                    <span>Otomatis buatkan Jurnal Penyesuaian Selisih Persediaan</span>
                </label>
                <p class="text-[10px] text-amber-700 mt-1 pl-5">Jika dicentang, selisih nilai barang akan otomatis masuk ke akun <strong>Beban Selisih Stok (6107)</strong> dan <strong>Persediaan (1105)</strong>.</p>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition-colors">
                    Simpan Penyesuaian Stok
                </button>
                <button type="button" onclick="closeAdjustStockModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transfer Stok Antar Cabang -->
<div id="transfer-stock-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Transfer Stok Antar Cabang</h4>
                <p class="text-[11px] text-slate-400">Kirim mutasi stok barang antar gudang / outlet cabang</p>
            </div>
            <button onclick="closeTransferModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="transfer-stock-form" onsubmit="submitStockTransfer(event)" class="space-y-3.5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cabang Asal (Pengirim)</label>
                    <select id="trf-from-branch" name="from_branch_id" onchange="onFromBranchChange()" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cabang Tujuan (Penerima)</label>
                    <select id="trf-to-branch" name="to_branch_id" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                        <?php foreach ($activeBranches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengiriman</label>
                <input type="date" id="trf-date" name="date" value="<?= date('Y-m-d') ?>" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium focus:ring-2 focus:ring-emerald-500" required>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Pilih Barang & Kuantitas</label>
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-8">
                        <select id="trf-product-id" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-medium">
                            <option value="">-- Pilih Produk --</option>
                        </select>
                    </div>
                    <div class="col-span-4">
                        <input type="number" id="trf-qty" min="1" placeholder="Qty" class="w-full p-2 text-xs rounded-xl border border-slate-300 font-bold font-mono">
                    </div>
                </div>
                <button type="button" onclick="addTransferItemRow()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    + Tambahkan ke Daftar Transfer
                </button>
            </div>

            <!-- Daftar Item yang akan ditransfer -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden max-h-40 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 font-bold">
                            <th class="p-2">Barang</th>
                            <th class="p-2 text-center w-20">Qty</th>
                            <th class="p-2 text-center w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="trf-items-tbody" class="divide-y divide-slate-100">
                        <tr><td colspan="3" class="p-4 text-center text-slate-400">Belum ada barang dipilih.</td></tr>
                    </tbody>
                </table>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pengiriman / Mutasi</label>
                <textarea id="trf-notes" rows="2" placeholder="Contoh: Pengisian stok reguler akhir pekan" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                <button type="submit" id="btn-submit-transfer" class="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors">
                    Kirim & Perbarui Stok Cabang
                </button>
                <button type="button" onclick="closeTransferModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let productsData = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadProducts();
    });

    function switchTab(tab) {
        const btnCat = document.getElementById('tab-btn-catalog');
        const btnHis = document.getElementById('tab-btn-history');
        const btnTrf = document.getElementById('tab-btn-transfers');
        const conCat = document.getElementById('tab-content-catalog');
        const conHis = document.getElementById('tab-content-history');
        const conTrf = document.getElementById('tab-content-transfers');

        const activeClass = 'flex-1 py-2 px-3.5 rounded-xl font-bold text-xs bg-white text-indigo-700 shadow-xs transition-all text-center';
        const inactiveClass = 'flex-1 py-2 px-3.5 rounded-xl font-semibold text-xs text-slate-600 hover:text-slate-900 transition-all text-center';

        [btnCat, btnHis, btnTrf].forEach(b => { if (b) b.className = inactiveClass; });
        [conCat, conHis, conTrf].forEach(c => { if (c) c.classList.add('hidden'); });

        if (tab === 'catalog') {
            btnCat.className = activeClass;
            conCat.classList.remove('hidden');
            loadProducts();
        } else if (tab === 'history') {
            btnHis.className = activeClass;
            conHis.classList.remove('hidden');
            loadStockHistory();
        } else if (tab === 'transfers') {
            btnTrf.className = activeClass;
            conTrf.classList.remove('hidden');
            loadBranchSpecificStocks(document.getElementById('branch-stock-selector')?.value || 2);
            loadTransfers();
        }
    }

    async function loadProducts() {
        try {
            const res = await fetch('../api/products.php?action=list');
            const result = await res.json();
            if (!result.success) return;

            productsData = result.data;
            renderProductRows(productsData);
        } catch (e) {
            console.error('Error load products:', e);
        }
    }

    function renderProductRows(products) {
        document.getElementById('product-count-badge').innerText = `${products.length} produk`;

        const tbody = document.getElementById('product-table-body');
        tbody.innerHTML = '';

        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-slate-400">Tidak ada produk ditemukan.</td></tr>';
            return;
        }

        products.forEach(p => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';
            const stock = Number(p.stock);
            const stockBadge = stock <= 10 
                ? `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">${stock} ${p.unit} (Menipis)</span>`
                : `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">${stock} ${p.unit}</span>`;

            tr.innerHTML = `
                <td class="py-3 px-4 font-mono font-bold text-indigo-600">${p.code}</td>
                <td class="py-3 px-4 font-bold text-slate-900">${p.name}</td>
                <td class="py-3 px-4 text-slate-500"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold">${p.category || 'Umum'}</span></td>
                <td class="py-3 px-4 text-right font-black text-slate-900 font-mono">Rp ${Number(p.price).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-right text-slate-500 font-mono">Rp ${Number(p.cost_price || 0).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-center">${stockBadge}</td>
                <td class="py-3 px-4 text-right">
                    <button onclick="quickAdjustProduct(${p.id})" class="px-3 py-1 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] transition-colors border border-amber-200/60">
                        Sesuaikan Stok
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterProductTable() {
        const q = document.getElementById('prod-search-input').value.toLowerCase();
        const filtered = productsData.filter(p => 
            p.name.toLowerCase().includes(q) || 
            p.code.toLowerCase().includes(q) || 
            (p.category && p.category.toLowerCase().includes(q))
        );
        renderProductRows(filtered);
    }

    async function loadStockHistory() {
        try {
            const res = await fetch('../api/products.php?action=stock_history');
            const result = await res.json();
            if (!result.success) return;

            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML = '';

            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="py-12 text-center text-slate-400">Belum ada riwayat penyesuaian stok.</td></tr>';
                return;
            }

            result.data.forEach(h => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 transition-colors';

                let typeColor = 'bg-slate-100 text-slate-700';
                if (h.type === 'masuk') typeColor = 'bg-emerald-50 text-emerald-700 border border-emerald-200/80';
                if (h.type === 'rusak' || h.type === 'keluar') typeColor = 'bg-rose-50 text-rose-700 border border-rose-200/80';
                if (h.type === 'opname') typeColor = 'bg-indigo-50 text-indigo-700 border border-indigo-200/80';

                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">${h.created_at}</td>
                    <td class="py-2.5 px-3 font-bold text-slate-800">${h.product_name} <span class="font-normal text-slate-400 font-mono text-[10px]">(${h.product_code})</span></td>
                    <td class="py-2.5 px-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${typeColor}">${h.type}</span>
                    </td>
                    <td class="py-2.5 px-3 text-center text-slate-500 font-mono">${h.previous_stock}</td>
                    <td class="py-2.5 px-3 text-center font-bold font-mono ${h.qty_change > 0 ? 'text-emerald-600' : (h.qty_change < 0 ? 'text-rose-600' : 'text-slate-600')}">
                        ${h.qty_change > 0 ? '+' + h.qty_change : h.qty_change}
                    </td>
                    <td class="py-2.5 px-3 text-center font-bold text-slate-900 font-mono">${h.new_stock} ${h.unit}</td>
                    <td class="py-2.5 px-3 text-slate-600">${h.reason || '-'}</td>
                    <td class="py-2.5 px-3 text-slate-400 text-[11px]">${h.user_name || 'Admin'}</td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {}
    }

    function openAddProductModal() {
        document.getElementById('add-product-modal').classList.remove('hidden');
    }

    function closeAddProductModal() {
        document.getElementById('add-product-modal').classList.add('hidden');
    }

    async function submitAddProduct(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('add-product-form'));
        try {
            const res = await fetch('../api/products.php?action=create', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeAddProductModal();
                loadProducts();
                document.getElementById('add-product-form').reset();
            } else {
                showToast(result.message || 'Gagal menambah produk.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan server saat menambah produk.', 'error');
        }
    }

    function openAdjustStockModal() {
        populateProductDropdown();
        document.getElementById('adjust-stock-modal').classList.remove('hidden');
    }

    function closeAdjustStockModal() {
        document.getElementById('adjust-stock-modal').classList.add('hidden');
    }

    function populateProductDropdown(selectedId = null) {
        const select = document.getElementById('adj-product-id');
        select.innerHTML = '';
        productsData.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.innerText = `[${p.code}] ${p.name} (Stok: ${p.stock} ${p.unit})`;
            if (selectedId && p.id == selectedId) opt.selected = true;
            select.appendChild(opt);
        });
        onSelectProductToAdjust();
    }

    function onSelectProductToAdjust() {
        const id = document.getElementById('adj-product-id').value;
        const prod = productsData.find(p => p.id == id);
        if (prod) {
            document.getElementById('current-system-stock').innerText = `${prod.stock} ${prod.unit}`;
            document.getElementById('adj-new-stock').value = prod.stock;
        }
    }

    function quickAdjustProduct(id) {
        openAdjustStockModal();
        populateProductDropdown(id);
    }

    function toggleAdjustInputMode() {
        const type = document.getElementById('adj-type').value;
        const divOpname = document.getElementById('div-opname-qty');
        const divDelta = document.getElementById('div-delta-qty');

        if (type === 'opname') {
            divOpname.classList.remove('hidden');
            divDelta.classList.add('hidden');
        } else {
            divOpname.classList.add('hidden');
            divDelta.classList.remove('hidden');
        }
    }

    async function submitAdjustStock(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('adjust-stock-form'));
        try {
            const res = await fetch('../api/products.php?action=adjust_stock', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeAdjustStockModal();
                loadProducts();
            } else {
                showToast(result.message || 'Gagal menyimpan penyesuaian stok.', 'error');
            }
        } catch (e) {
            showToast('Kesalahan server saat menyesuaikan stok.', 'error');
        }
    }

    // ==========================================
    // MULTI-BRANCH STOCK & TRANSFERS
    // ==========================================
    let transferItemsList = [];

    async function loadBranchSpecificStocks(branchId) {
        const tbody = document.getElementById('branch-stocks-table-body');
        try {
            const res = await fetch(`../api/transfers.php?action=branch_stocks&branch_id=${branchId}`);
            const result = await res.json();
            if (!result.success || !result.data) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-slate-400">Data tidak ditemukan.</td></tr>';
                return;
            }

            tbody.innerHTML = result.data.map(p => {
                const bStock = Number(p.branch_stock);
                let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                let statusText = 'Tersedia';
                if (bStock <= 0) {
                    badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                    statusText = 'Stok Kosong';
                } else if (bStock <= 10) {
                    badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    statusText = 'Stok Kritis';
                }

                return `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-500">${p.code}</td>
                        <td class="py-2.5 px-3 font-sans font-bold text-slate-800">${p.name}</td>
                        <td class="py-2.5 px-3 text-center font-bold font-mono text-indigo-700">${bStock} ${p.unit || 'pcs'}</td>
                        <td class="py-2.5 px-3 text-center font-mono text-slate-600">${p.global_stock} ${p.unit || 'pcs'}</td>
                        <td class="py-2.5 px-3 text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeClass}">
                                ${statusText}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-rose-500">Gagal memuat stok cabang.</td></tr>';
        }
    }

    async function loadTransfers() {
        const tbody = document.getElementById('transfers-table-body');
        try {
            const res = await fetch('../api/transfers.php?action=list');
            const result = await res.json();
            if (!result.success || !result.data || result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat transfer stok.</td></tr>';
                return;
            }

            tbody.innerHTML = result.data.map(t => {
                const itemSummary = (t.items || []).map(it => `${it.product_name} (${it.qty} ${it.unit || 'pcs'})`).join(', ');
                return `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-700">${t.transfer_no}</td>
                        <td class="py-2.5 px-3 text-slate-600">${t.date}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-800">${t.from_branch_name}</td>
                        <td class="py-2.5 px-3 font-bold text-indigo-700">${t.to_branch_name}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-900">${t.total_qty || 0}</td>
                        <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-xs truncate" title="${itemSummary}">
                            <span class="block font-medium text-slate-700">${t.notes || '-'}</span>
                            <span class="text-[10px] text-slate-400">${itemSummary}</span>
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Selesai
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-rose-500">Gagal memuat riwayat transfer.</td></tr>';
        }
    }

    function openTransferModal() {
        transferItemsList = [];
        renderTransferItemsTable();
        const select = document.getElementById('trf-product-id');
        select.innerHTML = '<option value="">-- Pilih Produk --</option>' + 
            productsData.map(p => `<option value="${p.id}">[${p.code}] ${p.name}</option>`).join('');
        document.getElementById('trf-qty').value = '';
        document.getElementById('transfer-stock-modal').classList.remove('hidden');
    }

    function closeTransferModal() {
        document.getElementById('transfer-stock-modal').classList.add('hidden');
    }

    function onFromBranchChange() {
        const from = document.getElementById('trf-from-branch').value;
        const to = document.getElementById('trf-to-branch');
        if (from === to.value) {
            to.value = (from === '2') ? '3' : '2';
        }
    }

    function addTransferItemRow() {
        const prodSelect = document.getElementById('trf-product-id');
        const qtyInput = document.getElementById('trf-qty');
        const pId = Number(prodSelect.value);
        const qty = Number(qtyInput.value);

        if (!pId || qty <= 0) {
            showToast('Pilih barang dan isi kuantitas > 0!', 'warning');
            return;
        }

        const prod = productsData.find(p => p.id === pId);
        if (!prod) return;

        const existing = transferItemsList.find(it => it.product_id === pId);
        if (existing) {
            existing.qty += qty;
        } else {
            transferItemsList.push({
                product_id: pId,
                name: prod.name,
                qty: qty
            });
        }

        qtyInput.value = '';
        renderTransferItemsTable();
    }

    function removeTransferItemRow(index) {
        transferItemsList.splice(index, 1);
        renderTransferItemsTable();
    }

    function renderTransferItemsTable() {
        const tbody = document.getElementById('trf-items-tbody');
        if (transferItemsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-slate-400">Belum ada barang dipilih.</td></tr>';
            return;
        }

        tbody.innerHTML = transferItemsList.map((it, idx) => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-2 font-bold text-slate-800">${it.name}</td>
                <td class="p-2 text-center font-mono font-bold text-indigo-700">${it.qty}</td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeTransferItemRow(${idx})" class="text-rose-500 hover:text-rose-700 font-bold text-xs">&times;</button>
                </td>
            </tr>
        `).join('');
    }

    async function submitStockTransfer(e) {
        e.preventDefault();
        const fromBranch = document.getElementById('trf-from-branch').value;
        const toBranch = document.getElementById('trf-to-branch').value;
        const date = document.getElementById('trf-date').value;
        const notes = document.getElementById('trf-notes').value;

        if (fromBranch === toBranch) {
            showToast('Cabang asal dan tujuan tidak boleh sama!', 'warning');
            return;
        }

        if (transferItemsList.length === 0) {
            showToast('Daftar item transfer masih kosong!', 'warning');
            return;
        }

        const btn = document.getElementById('btn-submit-transfer');
        btn.disabled = true;
        btn.innerText = 'Mengirim...';

        try {
            const fd = new FormData();
            fd.append('action', 'create');
            fd.append('from_branch_id', fromBranch);
            fd.append('to_branch_id', toBranch);
            fd.append('date', date);
            fd.append('notes', notes);
            fd.append('items', JSON.stringify(transferItemsList));

            const res = await fetch('../api/transfers.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showToast(result.message || 'Transfer stok berhasil!', 'success');
                closeTransferModal();
                loadBranchSpecificStocks(document.getElementById('branch-stock-selector')?.value || 2);
                loadTransfers();
            } else {
                showToast(result.message || 'Gagal mengirim transfer stok!', 'error');
            }
        } catch (err) {
            showToast('Kesalahan server saat memproses transfer.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Kirim & Perbarui Stok Cabang';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
