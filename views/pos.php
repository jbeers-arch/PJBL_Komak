<?php
$pageTitle = 'Kasir POS (Offline-First)';
require_once __DIR__ . '/../includes/header.php';
$userBranchId = $_SESSION['user']['branch_id'] ?? 2; // Default Cabang 1 jika belum ditentukan
?>

<style>
/* =========================================================
 * POS INTERACTIVE ANIMATIONS (Re-engineered with Spring Physics)
 * ========================================================= */
:root {
  --spring: cubic-bezier(.34, 1.56, .64, 1);
  --ease-out: cubic-bezier(.2, .8, .2, 1);
}

/* 1. Card Pop Effect on Click */
.pos-card-pop {
  animation: posCardPop 0.35s var(--spring);
}
@keyframes posCardPop {
  0% { transform: scale(1); }
  35% { transform: scale(0.92) translateY(-2px); }
  70% { transform: scale(1.04) translateY(-3px); }
  100% { transform: scale(1) translateY(0); }
}

/* 2. Flying Badge along Curved Parabola Arc */
.pos-fly-badge {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 9999;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 16px;
  background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
  color: #ffffff;
  font-size: 18px;
  box-shadow: 0 12px 28px -6px rgba(5, 150, 105, 0.45), 0 4px 10px rgba(0, 0, 0, 0.1);
  will-change: transform, opacity;
}

/* 3. Cart Badge Bump / Pulse */
.pos-badge-bump {
  animation: posBadgeBump 0.45s var(--spring);
}
@keyframes posBadgeBump {
  0% { transform: scale(1); }
  40% { transform: scale(1.45); }
  100% { transform: scale(1); }
}

/* 4. Cart Line Item Transitions */
.pos-line-enter {
  animation: posLineEnter 0.4s var(--spring) forwards;
}
@keyframes posLineEnter {
  from {
    opacity: 0;
    transform: translateX(35px) scale(0.92);
  }
  to {
    opacity: 1;
    transform: none;
  }
}

.pos-line-flash {
  animation: posLineFlash 0.6s ease-out;
}
@keyframes posLineFlash {
  0% {
    background-color: rgba(16, 185, 129, 0.22);
    border-color: rgba(16, 185, 129, 0.6);
  }
  100% {
    background-color: rgba(248, 250, 252, 0.9);
    border-color: #f1f5f9;
  }
}

.pos-line-leave {
  animation: posLineLeave 0.3s cubic-bezier(.4, 0, 1, 1) forwards;
  pointer-events: none;
  overflow: hidden;
}
@keyframes posLineLeave {
  0% {
    opacity: 1;
    transform: translateX(0);
    max-height: 80px;
    margin-bottom: 8px;
  }
  100% {
    opacity: 0;
    transform: translateX(50px);
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    margin-bottom: 0;
    border-width: 0;
  }
}

/* 5. Drag & Drop Hover on Cart Panel */
#cart-container-panel.pos-drop-active {
  outline: 2.5px dashed #059669 !important;
  outline-offset: -2px;
  transform: scale(1.012);
  box-shadow: 0 20px 35px -10px rgba(5, 150, 105, 0.25) !important;
  transition: all 0.25s var(--spring);
}

/* 6. Thermal Receipt Paper with Serrated Edge & Animated Printing */
.pos-receipt-paper {
  position: relative;
  overflow: hidden;
  animation: posReceiptPrint 1.1s steps(22) both;
  -webkit-mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50%/14px 100%;
  mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50%/14px 100%;
}
@keyframes posReceiptPrint {
  from { clip-path: inset(0 0 100% 0); }
  to { clip-path: inset(0); }
}

/* 7. Success Checkmark Drawing Animation */
.pos-receipt-chk {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #ecfdf5;
  border: 2px solid #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
  animation: posBadgeBump 0.5s 0.3s var(--spring) both;
}
.pos-receipt-chk svg {
  width: 24px;
  height: 24px;
}
.pos-receipt-chk path {
  fill: none;
  stroke: #059669;
  stroke-width: 3;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-dasharray: 24;
  stroke-dashoffset: 24;
  animation: posCheckDraw 0.45s 0.6s forwards;
}
@keyframes posCheckDraw {
  to { stroke-dashoffset: 0; }
}

/* Touch scroll smooth & no-scrollbar helper */
.touch-scroll {
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

@media (prefers-reduced-motion: reduce) {
  .pos-card-pop, .pos-badge-bump, .pos-line-enter, .pos-line-flash, .pos-line-leave, .pos-receipt-paper, .pos-receipt-chk {
    animation: none !important;
    transition: none !important;
  }
}
</style>

<div class="space-y-4">
    <!-- Offline-First Banner & Notification -->
    <div id="pending-sync-banner" class="hidden p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 shadow-xs flex items-center justify-between">
        <div class="flex items-center space-x-2.5 text-amber-900 text-xs font-semibold">
            <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </span>
            <span>Terdapat <strong id="pending-sync-count" class="font-black text-amber-900">0</strong> transaksi tersimpan di memori offline browser. Sinkronisasi otomatis berjalan saat terhubung.</span>
        </div>
        <button onclick="window.posEngine?.syncPendingTransactions()" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-all">
            Sinkronkan Sekarang
        </button>
    </div>

    <!-- Cashier Shift & Return Bar -->
    <div class="bg-white p-3.5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-3">
            <div id="shift-status-indicator" class="flex items-center space-x-2">
                <span id="shift-dot" class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="font-bold text-slate-800" id="shift-status-text">Memeriksa status shift kasir...</span>
            </div>
            <span id="shift-cash-float" class="px-2.5 py-0.5 rounded-full bg-slate-100 font-mono font-bold text-slate-700 hidden">Modal Kas: Rp 0</span>
        </div>
        <div class="flex items-center space-x-2">
            <button id="btn-open-shift" onclick="openStartShiftModal()" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-xs flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buka Shift Kasir</span>
            </button>
            <button id="btn-close-shift" onclick="openEndShiftModal()" class="hidden px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition-all shadow-xs flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Tutup Shift & Hitung Kas</span>
            </button>
            <button onclick="openLaundryOrdersModal()" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-xs flex items-center space-x-1.5">
                <i class="fa-solid fa-shirt text-xs"></i>
                <span>Status & Antrian Cucian</span>
            </button>
            <button onclick="openSalesReturnModal()" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                <span>Retur Transaksi</span>
            </button>
        </div>
    </div>

    <!-- POS Layout: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        <!-- Kolom Kiri: Katalog Produk (7 Kolom) -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Search, Kategori, & Penyesuaian Barang Kasir -->
            <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex flex-col sm:flex-row gap-2.5 items-center justify-between">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="search-product" oninput="filterProducts()" placeholder="Cari nama barang atau barcode..."
                               class="w-full pl-9 pr-8 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <button type="button" onclick="document.getElementById('search-product').value=''; filterProducts();" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-300 hover:text-slate-500">
                            &times;
                        </button>
                    </div>

                    <!-- Tombol Cek Stok & Penyesuaian Barang Kasir -->
                    <div class="flex items-center space-x-2 w-full sm:w-auto">
                        <button onclick="openStockOverviewModal()" class="flex-1 sm:flex-none px-3 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center space-x-1.5 transition-all">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Cek Stok</span>
                        </button>
                        <button onclick="openCashierStockModal()" class="flex-1 sm:flex-none px-3.5 py-2 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex items-center justify-center space-x-1.5 transition-all shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <span>Penyesuaian Stok</span>
                        </button>
                    </div>
                </div>

                <!-- Kategori Filter Pills -->
                <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar touch-scroll pb-1 pt-1 border-t border-slate-100" id="category-filter-container">
                    <button onclick="setCategory('all')" class="category-btn active shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-xs transition-all">Semua</button>
                    <button onclick="setCategory('Laundry Kiloan')" class="category-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100/80 text-slate-600 hover:bg-slate-200 transition-all">Kiloan</button>
                    <button onclick="setCategory('Laundry Satuan')" class="category-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100/80 text-slate-600 hover:bg-slate-200 transition-all">Satuan</button>
                    <button onclick="setCategory('Dry Cleaning')" class="category-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100/80 text-slate-600 hover:bg-slate-200 transition-all">Dry Clean</button>
                    <button onclick="setCategory('Bahan & Perlengkapan')" class="category-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100/80 text-slate-600 hover:bg-slate-200 transition-all">Bahan/Retail</button>
                </div>
            </div>

            <!-- Grid Produk -->
            <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <p class="text-xs text-slate-400 col-span-3 text-center py-12">Memuat katalog produk...</p>
            </div>
        </div>

        <!-- Kolom Kanan: Keranjang Kasir (5 Kolom) -->
        <div class="lg:col-span-5">
            <div id="cart-container-panel" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs flex flex-col transition-all duration-200 lg:sticky lg:top-20 lg:max-h-[calc(100vh-5.5rem)] lg:overflow-y-auto no-scrollbar">
                <!-- Header Keranjang -->
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Nota Cucian Kasir</h3>
                            <span id="cart-badge-count" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">0</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Outlet: <?= htmlspecialchars($currentUser['branch_name'] ?? 'Cabang') ?></p>
                    </div>
                    <button onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition-colors">
                        Kosongkan
                    </button>
                </div>

                <!-- Input Data Pelanggan & Rak Cucian -->
                <div class="p-3 bg-slate-50/70 border-b border-slate-100 space-y-2 shrink-0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="text" id="customer-name" placeholder="Nama Pelanggan (Ibu Rina)"
                                   class="w-full pl-7 pr-2.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-semibold bg-white text-slate-800">
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-brands fa-whatsapp text-xs"></i>
                            </span>
                            <input type="text" id="customer-phone" placeholder="No. WA (08123xxx)"
                                   class="w-full pl-7 pr-2.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-medium bg-white text-slate-800">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <input type="text" id="customer-rack" placeholder="Rak / Loker (Rak A-02)"
                                   class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-medium bg-white text-slate-800">
                        </div>
                        <div>
                            <input type="date" id="customer-estimate" title="Estimasi Siap Ambil"
                                   class="w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-medium bg-white text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- List Item Keranjang -->
                <div id="cart-items" class="overflow-y-auto p-3.5 space-y-2 min-h-[160px] max-h-[300px] lg:max-h-[340px] touch-scroll">
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <span class="font-semibold text-slate-600 block">Keranjang Belum Terisi</span>
                        <span class="text-[11px] text-slate-400">Klik item di katalog untuk menambahkan pesanan</span>
                    </div>
                </div>

                <!-- Ringkasan & Form Pembayaran -->
                <div class="p-3.5 bg-slate-50/80 border-t border-slate-200/80 space-y-2.5 rounded-b-3xl shrink-0">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="font-medium">Total Kuantitas:</span>
                        <span id="cart-total-qty" class="font-bold text-slate-800">0 item</span>
                    </div>
                    <!-- Subtotal, Diskon, Pajak PB1 -->
                    <div class="space-y-1.5 pt-1 border-t border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal Produk:</span>
                            <span id="cart-subtotal" class="font-bold text-slate-800 font-mono">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600">Diskon Promosi (Rp):</span>
                            <input type="number" id="cart-discount" oninput="calculateGrandTotal()" placeholder="0" min="0"
                                   class="w-28 p-1 text-right text-xs rounded-lg border border-slate-300 font-mono font-bold text-slate-900 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center cursor-pointer text-slate-600 select-none">
                                <input type="checkbox" id="cart-tax-toggle" onchange="calculateGrandTotal()" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-1.5 font-medium">Pajak Restoran (PB1 10%)</span>
                            </label>
                            <span id="cart-tax-amount" class="font-bold text-slate-700 font-mono text-xs">Rp 0</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 pb-2 border-t border-b border-slate-200">
                        <span class="text-sm font-extrabold text-slate-800">Total Tagihan</span>
                        <span id="cart-grand-total" class="text-xl font-black text-emerald-700 font-mono tracking-tight">Rp 0</span>
                    </div>

                    <!-- Metode Bayar & Uang Tunai -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Metode Bayar</label>
                            <select id="payment-method" class="w-full p-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold text-slate-700">
                                <option value="Tunai">Tunai (Cash)</option>
                                <option value="QRIS">QRIS Dinamis</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Uang Diterima (Rp)</label>
                            <input type="number" id="cash-received" oninput="calculateChange()" placeholder="0"
                                   class="w-full p-2 text-xs rounded-xl border border-slate-300 font-mono font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Quick Cash Preset Buttons -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5">
                        <button type="button" onclick="setQuickCash('pas')" class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-[10px] font-bold shrink-0 transition-colors">Uang Pas</button>
                        <button type="button" onclick="setQuickCash(10000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[10px] font-bold shrink-0 transition-colors">10k</button>
                        <button type="button" onclick="setQuickCash(20000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[10px] font-bold shrink-0 transition-colors">20k</button>
                        <button type="button" onclick="setQuickCash(50000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[10px] font-bold shrink-0 transition-colors">50k</button>
                        <button type="button" onclick="setQuickCash(100000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[10px] font-bold shrink-0 transition-colors">100k</button>
                    </div>

                    <!-- Kembalian Display -->
                    <div class="flex items-center justify-between text-xs px-1">
                        <span class="text-slate-500 font-medium">Status Kembalian:</span>
                        <span id="cash-change" class="font-black text-slate-900 font-mono text-sm">Rp 0</span>
                    </div>

                    <!-- Indikator Kepatuhan Akuntansi SAK EMKM -->
                    <div class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200/60 flex items-center justify-between text-[10px] text-emerald-800 font-semibold">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Pencatatan Persediaan: Perpetual Terpadu</span>
                        </div>
                        <span class="font-mono text-[9px] bg-emerald-100/80 px-1.5 py-0.5 rounded text-emerald-700 font-bold">SAK EMKM</span>
                    </div>

                    <!-- Tombol Bayar -->
                    <button onclick="processCheckout()" id="btn-checkout"
                            class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Selesaikan Transaksi & Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Struk Thermal Transaksi -->
<div id="receipt-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div id="receipt-print-area" class="font-mono text-xs text-slate-800 p-4 border border-dashed border-slate-300 rounded-2xl bg-white pos-receipt-paper">
            <div id="receipt-chk-badge" class="pos-receipt-chk">
                <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </div>
            <div class="text-center pb-3 border-b border-dashed border-slate-300">
                <h4 class="font-extrabold text-base tracking-tight text-slate-900">PJBL KOMAK LAUNDRY</h4>
                <p class="text-[10px] text-slate-500 font-sans"><?= htmlspecialchars($currentUser['branch_name'] ?? 'Cabang') ?> &bull; Jasa Cuci Kiloan &amp; Satuan</p>
                <div class="mt-2 text-[10px] text-slate-600 space-y-0.5">
                    <p id="rcp-trx-no">TRX-000</p>
                    <p id="rcp-date">2026-09-25 12:00</p>
                    <p id="rcp-customer" class="font-bold text-slate-800 font-sans text-[11px]">Pelanggan: Umum</p>
                    <p id="rcp-phone" class="text-slate-600 font-sans hidden">WA: -</p>
                    <div id="rcp-rack-box" class="pt-1 flex items-center justify-center space-x-1.5 hidden">
                        <span id="rcp-rack" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Rak: -</span>
                        <span id="rcp-estimate" class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">Siap: -</span>
                    </div>
                </div>
            </div>

            <div id="rcp-items" class="py-3 border-b border-dashed border-slate-300 space-y-1.5 text-xs"></div>

            <div class="pt-3 space-y-1 text-xs">
                <div id="rcp-subtotal-row" class="flex justify-between text-slate-600 hidden">
                    <span>Subtotal:</span>
                    <span id="rcp-subtotal">Rp 0</span>
                </div>
                <div id="rcp-discount-row" class="flex justify-between text-rose-600 hidden">
                    <span>Diskon / Promo:</span>
                    <span id="rcp-discount">- Rp 0</span>
                </div>
                <div id="rcp-tax-row" class="flex justify-between text-slate-600 hidden">
                    <span>Pajak Layanan (10%):</span>
                    <span id="rcp-tax">Rp 0</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900 border-t border-dashed border-slate-200 pt-1">
                    <span>Total Tagihan:</span>
                    <span id="rcp-total">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Bayar (<span id="rcp-method">Tunai</span>):</span>
                    <span id="rcp-cash">Rp 0</span>
                </div>
                <div class="flex justify-between font-bold text-emerald-700">
                    <span>Kembalian:</span>
                    <span id="rcp-change">Rp 0</span>
                </div>
            </div>

            <div class="text-center pt-3 border-t border-dashed border-slate-300 text-[10px] text-slate-400">
                <p>Simpan nota ini sebagai tanda terima pengambilan cucian.</p>
                <p class="mt-0.5">Pakaian bersih, wangi &amp; rapi adalah prioritas kami.</p>
                <p id="rcp-sync-status" class="font-bold text-emerald-600 mt-1">STATUS: TERSINKRON KE SIA PUSAT</p>
                <p class="text-[9px] text-slate-400 font-sans mt-0.5">&bull; Pencatatan Persediaan: Perpetual SAK EMKM &bull;</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="flex-1 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </button>
            <a id="btn-wa-receipt" href="#" target="_blank" class="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center space-x-1.5">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Kirim WA</span>
            </a>
            <button onclick="closeReceiptModal()" class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Status & Antrian Cucian (Pipeline Produksi ala Simpelin) -->
<div id="laundry-orders-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-5xl w-full p-6 shadow-2xl space-y-4 max-h-[92vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-shirt"></i>
                    </span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 tracking-tight">Monitoring Antrian &amp; Status Cucian</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Kelola tahapan pengerjaan cucian pelanggan, lokasi rak/loker, dan kirim notifikasi WhatsApp</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="loadLaundryOrders()" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors" title="Segarkan Data">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button onclick="closeLaundryOrdersModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
        </div>

        <!-- Filter Status Pipeline -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs" id="laundry-status-filters">
            <button onclick="filterLaundryOrders('')" class="laundry-filter-btn active px-3.5 py-1.5 rounded-xl font-bold bg-slate-900 text-white shadow-xs transition-all">Semua Antrian</button>
            <button onclick="filterLaundryOrders('antrian')" class="laundry-filter-btn px-3.5 py-1.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all">Antrian Masuk</button>
            <button onclick="filterLaundryOrders('sedang_dicuci')" class="laundry-filter-btn px-3.5 py-1.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all">Sedang Dicuci</button>
            <button onclick="filterLaundryOrders('sedang_disetrika')" class="laundry-filter-btn px-3.5 py-1.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all">Sedang Disetrika</button>
            <button onclick="filterLaundryOrders('siap_diambil')" class="laundry-filter-btn px-3.5 py-1.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all">Siap Diambil</button>
            <button onclick="filterLaundryOrders('selesai')" class="laundry-filter-btn px-3.5 py-1.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all">Selesai / Diambil</button>
        </div>

        <!-- Table Antrian Cucian -->
        <div class="flex-1 overflow-y-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold sticky top-0 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3">No. Nota &amp; Tgl</th>
                        <th class="py-3 px-3">Pelanggan &amp; WhatsApp</th>
                        <th class="py-3 px-3">Rincian Cucian</th>
                        <th class="py-3 px-3">Lokasi Rak</th>
                        <th class="py-3 px-3">Tahapan Pengerjaan</th>
                        <th class="py-3 px-3 text-center">Aksi / Notif WA</th>
                    </tr>
                </thead>
                <tbody id="laundry-orders-tbody" class="divide-y divide-slate-100">
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">Memuat data antrian cucian...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <span class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Perubahan status akan otomatis tersimpan ke server dan tercatat pada audit log.</span>
            </span>
            <button onclick="closeLaundryOrdersModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Penyesuaian Stok Kasir -->
<div id="cashier-stock-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Penyesuaian Stok Kasir</h4>
                <p class="text-[11px] text-slate-400">Cabang: <?= htmlspecialchars($currentUser['branch_name'] ?? 'Cabang') ?></p>
            </div>
            <button onclick="closeCashierStockModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="cashier-stock-form" onsubmit="submitCashierStockAdjustment(event)" class="space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk</label>
                <select id="c-adj-product" onchange="onCashierProductSelect(this.value)" required class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-amber-500 font-medium">
                    <option value="">-- Pilih Barang --</option>
                </select>
            </div>

            <!-- Kartu Info Stok Terkini -->
            <div id="c-adj-stock-info" class="hidden p-3 rounded-2xl bg-amber-50/70 border border-amber-200 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-500 block text-[11px]">Sisa Stok di Sistem:</span>
                    <span id="c-adj-current-stock" class="font-extrabold text-amber-900 text-sm">0</span>
                    <span id="c-adj-unit" class="text-amber-800 text-[11px]">pcs</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block text-[11px]">Harga Pokok (HPP):</span>
                    <span id="c-adj-cost" class="font-bold text-slate-700">Rp 0</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Penyesuaian</label>
                <select id="c-adj-type" onchange="onCashierAdjTypeChange(this.value)" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white">
                    <option value="rusak">Barang Rusak / Pecah / Basi di Kasir (-)</option>
                    <option value="masuk">Penerimaan Kiriman Barang Baru (+)</option>
                    <option value="keluar">Pengeluaran Toko / Retur (-)</option>
                    <option value="opname">Hitung Ulang Fisik Kasir (Set Stok Baru)</option>
                </select>
            </div>

            <div id="c-adj-qty-container">
                <label id="c-adj-qty-label" class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit yang Disesuaikan</label>
                <input type="number" id="c-adj-qty" min="1" placeholder="Contoh: 2" required class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-bold focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Catatan Kasir</label>
                <input type="text" id="c-adj-reason" placeholder="Contoh: 1 botol pecah saat penataan rak" required class="w-full p-2.5 text-xs rounded-xl border border-slate-300">
            </div>

            <div class="pt-2 flex items-center space-x-2">
                <button type="submit" id="btn-save-cashier-adj" class="flex-1 py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-xs">
                    Simpan Penyesuaian
                </button>
                <button type="button" onclick="closeCashierStockModal()" class="py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Informasi Semua Stok Cabang -->
<div id="cashier-stock-overview-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Ketersediaan Stok Cabang</h4>
                <p class="text-[11px] text-slate-400">Pantau ketersediaan barang di outlet <?= htmlspecialchars($currentUser['branch_name'] ?? 'Cabang') ?></p>
            </div>
            <button onclick="closeStockOverviewModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <div class="relative">
            <input type="text" id="stock-modal-search" oninput="filterStockModalTable()" placeholder="Cari nama atau barcode barang..."
                   class="w-full pl-9 pr-4 py-2 rounded-2xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-400 focus:outline-none">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex-1 overflow-y-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold sticky top-0">
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Nama Produk</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3 text-right">Harga Jual</th>
                        <th class="py-2.5 px-3 text-center">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody id="stock-overview-tbody" class="divide-y divide-slate-100">
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">Memuat stok...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pt-2 text-right">
            <button onclick="closeStockOverviewModal()" class="px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Buka Shift Kasir -->
<div id="open-shift-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Buka Shift Kasir Baru</h4>
                <p class="text-[11px] text-slate-500">Mulai sesi kasir & catat uang kembalian awal</p>
            </div>
            <button onclick="closeStartShiftModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>
        <form id="start-shift-form" onsubmit="submitStartShift(event)" class="space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kas Awal di Laci (Modal Kembalian)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                    <input type="number" id="shift-starting-cash" value="100000" min="0" required
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Uang fisik yang ada di laci kasir saat toko dibuka.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Shift (Opsional)</label>
                <textarea id="shift-start-notes" rows="2" placeholder="Contoh: Shift Pagi, pecahan lengkap"
                          class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div class="pt-2 flex items-center space-x-2">
                <button type="button" onclick="closeStartShiftModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-submit-start-shift" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                    Mulai Shift
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tutup Shift & Rekonsiliasi Kas -->
<div id="close-shift-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Tutup Shift & Rekonsiliasi Kas</h4>
                <p class="text-[11px] text-slate-500">Hitung fisik kas drawer dan periksa selisih</p>
            </div>
            <button onclick="closeEndShiftModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Modal Kas Awal:</span>
                <span id="cs-starting-cash" class="font-mono font-bold text-slate-900">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Total Penjualan Tunai:</span>
                <span id="cs-cash-sales" class="font-mono font-bold text-emerald-700">+ Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-500 text-[11px]">
                <span>Penjualan Non-Tunai (QRIS/Bank):</span>
                <span id="cs-non-cash-sales" class="font-mono">Rp 0</span>
            </div>
            <div class="flex justify-between font-extrabold text-slate-900 border-t border-slate-200 pt-2 text-sm">
                <span>Kas Diharapkan di Laci:</span>
                <span id="cs-expected-cash" class="font-mono text-emerald-800">Rp 0</span>
            </div>
        </div>

        <form id="end-shift-form" onsubmit="submitEndShift(event)" class="space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Uang Kas Fisik Aktual di Laci (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                    <input type="number" id="shift-actual-cash" oninput="calculateShiftVariance()" min="0" required placeholder="0"
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-black text-slate-900 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Kartu Selisih Kas -->
            <div id="cs-variance-card" class="p-3 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-semibold">Status Selisih (Varian):</span>
                <span id="cs-variance-amount" class="font-black font-mono px-2.5 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-800">Rp 0 (Pas)</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Penutupan Shift</label>
                <textarea id="shift-end-notes" rows="2" placeholder="Alasan bila ada selisih kas..."
                          class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="pt-2 flex items-center space-x-2">
                <button type="button" onclick="closeEndShiftModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-submit-end-shift" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs">
                    Tutup Shift Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Retur Penjualan -->
<div id="sales-return-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900">Retur Penjualan & Pembalik Otomatis</h4>
                <p class="text-[11px] text-slate-500">Batalkan/retur item barang, kembalikan stok, dan jurnal pembalik</p>
            </div>
            <button onclick="closeSalesReturnModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <!-- Pencarian No Transaksi -->
        <div class="space-y-2 shrink-0">
            <label class="block text-xs font-bold text-slate-700">Nomor Transaksi POS</label>
            <div class="flex items-center space-x-2">
                <input type="text" id="ret-search-trx" placeholder="Contoh: TRX-CBG01-202610..."
                       class="flex-1 p-2.5 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:ring-2 focus:ring-slate-400 focus:outline-none">
                <button type="button" onclick="searchReturnTransaction()" id="btn-search-ret-trx"
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                    Cari Transaksi
                </button>
            </div>
        </div>

        <!-- Detail Transaksi Ditemukan -->
        <div id="ret-details-container" class="hidden space-y-3 overflow-y-auto flex-1 pr-1">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs grid grid-cols-2 gap-2">
                <div>
                    <span class="text-slate-500 block text-[10px]">Waktu Transaksi:</span>
                    <span id="ret-info-date" class="font-bold text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px]">Metode Pembayaran Asli:</span>
                    <span id="ret-info-payment" class="font-bold text-slate-800">-</span>
                </div>
            </div>

            <!-- Tabel Item Yang Dapat Diretur -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tentukan Kuantitas Yang Diretur:</label>
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                                <th class="p-2.5">Produk</th>
                                <th class="p-2.5 text-right">Harga</th>
                                <th class="p-2.5 text-center">Qty Beli</th>
                                <th class="p-2.5 text-center w-24">Qty Retur</th>
                                <th class="p-2.5 text-right">Total Retur</th>
                            </tr>
                        </thead>
                        <tbody id="ret-items-tbody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50/80 border border-rose-200 text-xs">
                <span class="font-bold text-rose-900">Total Pengembalian Dana:</span>
                <span id="ret-total-amount" class="font-black text-rose-700 font-mono text-sm">Rp 0</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Retur (Wajib)</label>
                <input type="text" id="ret-reason" placeholder="Contoh: Kemasan cacat / basi / salah transaksi" required
                       class="w-full p-2.5 text-xs rounded-xl border border-slate-300 font-medium text-slate-800 focus:ring-2 focus:ring-rose-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pengembalian Dana (Refund)</label>
                <select id="ret-refund-method" class="w-full p-2.5 text-xs rounded-xl border border-slate-300 bg-white font-medium text-slate-800">
                    <option value="Tunai">Kas Tunai (Ambil dari Laci Kasir)</option>
                    <option value="Transfer Bank">Transfer Bank / Kas Utama</option>
                </select>
            </div>
        </div>

        <div id="ret-actions" class="hidden pt-2 flex items-center space-x-2 shrink-0">
            <button type="button" onclick="closeSalesReturnModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Batal
            </button>
            <button type="button" onclick="submitSalesReturn()" id="btn-submit-sales-return"
                    class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs">
                Proses Retur & Reversal
            </button>
        </div>
    </div>
</div>

<script>
    let allProducts = [];
    let cart = [];
    let currentCategory = 'all';
    const branchId = <?= (int)$userBranchId ?>;
    const branchCode = '<?= htmlspecialchars($currentUser['branch_code'] ?? 'CBG01') ?>';
    const cashierId = <?= (int)$currentUser['id'] ?>;

    document.addEventListener('DOMContentLoaded', async () => {
        // Inisialisasi estimasi selesai default (+2 hari)
        const estInput = document.getElementById('customer-estimate');
        if (estInput && !estInput.value) {
            const d = new Date();
            d.setDate(d.getDate() + 2);
            estInput.value = d.toISOString().split('T')[0];
        }
        initPosDragAndDrop();
        await initPOS();
    });

    // =========================================================================
    // REVERSE-ENGINEERED POS ANIMATION ENGINE (Physics, Flight Arc, Ticker, DND)
    // =========================================================================
    function bumpCartBadge() {
        const badge = document.getElementById('cart-badge-count');
        if (!badge) return;
        badge.classList.remove('pos-badge-bump');
        void badge.offsetWidth; // Force CSS reflow
        badge.classList.add('pos-badge-bump');
    }

    function rollCurrency(el, to) {
        if (!el) return;
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) {
            el.innerText = 'Rp ' + Number(to).toLocaleString('id-ID');
            return;
        }

        const currentVal = parseInt(el.dataset.val ?? (el.innerText.replace(/[^\d]/g, '') || 0), 10);
        const from = isNaN(currentVal) ? 0 : currentVal;
        const target = Number(to);
        if (from === target) {
            el.innerText = 'Rp ' + target.toLocaleString('id-ID');
            el.dataset.val = target;
            return;
        }

        const start = performance.now();
        const duration = 320;
        el.dataset.val = target;

        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            // Cubic ease-out: 1 - (1 - progress)^3
            const ease = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(from + (target - from) * ease);
            el.innerText = 'Rp ' + value.toLocaleString('id-ID');
            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.innerText = 'Rp ' + target.toLocaleString('id-ID');
            }
        }
        requestAnimationFrame(tick);
    }

    function launchFlyToCart(sourceEl, product) {
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const targetEl = document.getElementById('cart-badge-count') || document.getElementById('cart-container-panel');
        if (prefersReduced || !targetEl || !sourceEl) {
            bumpCartBadge();
            return;
        }

        const s = sourceEl.getBoundingClientRect();
        const t = targetEl.getBoundingClientRect();

        const badge = document.createElement('div');
        badge.className = 'pos-fly-badge';
        badge.textContent = product.name ? product.name.slice(0, 16) : '+1 Layanan';
        document.body.appendChild(badge);

        const sx = s.left + s.width / 2;
        const sy = s.top + s.height / 2;
        const ex = t.left + t.width / 2;
        const ey = t.top + t.height / 2;

        const my = Math.min(sy, ey) - 75; // Apex arc parabola
        const mx = (sx + ex) / 2;

        const anim = badge.animate([
            { transform: `translate(${sx}px, ${sy}px) scale(1) rotate(0deg)`, opacity: 1 },
            { transform: `translate(${mx}px, ${my}px) scale(1.15) rotate(180deg)`, opacity: 0.95, offset: 0.5 },
            { transform: `translate(${ex}px, ${ey}px) scale(0.25) rotate(360deg)`, opacity: 0.2 }
        ], {
            duration: 520,
            easing: 'cubic-bezier(.22, .61, .36, 1)',
            fill: 'forwards'
        });

        anim.onfinish = () => {
            badge.remove();
            bumpCartBadge();
        };
    }

    function initPosDragAndDrop() {
        const cartPanel = document.getElementById('cart-container-panel');
        if (!cartPanel) return;

        cartPanel.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            cartPanel.classList.add('pos-drop-active');
        });

        cartPanel.addEventListener('dragleave', (e) => {
            if (!cartPanel.contains(e.relatedTarget)) {
                cartPanel.classList.remove('pos-drop-active');
            }
        });

        cartPanel.addEventListener('drop', (e) => {
            e.preventDefault();
            cartPanel.classList.remove('pos-drop-active');
            const raw = e.dataTransfer.getData('application/json');
            if (!raw) return;
            try {
                const prod = JSON.parse(raw);
                if (prod && prod.id) {
                    const fullProd = allProducts.find(p => p.id === prod.id) || prod;
                    if (Number(fullProd.stock) <= 0) {
                        showToast('Kapasitas/stok layanan ' + fullProd.name + ' habis!', 'error');
                        return;
                    }
                    bumpCartBadge();
                    addToCart(fullProd);
                }
            } catch (err) {}
        });
    }

    async function initPOS() {
        try {
            // Ambil dari server jika online, simpan ke IndexedDB lokal
            if (navigator.onLine) {
                const res = await fetch('../api/products.php');
                const result = await res.json();
                if (result.success && result.data) {
                    allProducts = result.data;
                    await window.posEngine?.cacheProducts(allProducts);
                }
            }
        } catch (e) {
            console.log('Menggunakan cache IndexedDB lokal...');
        }

        // Jika offline atau fetch gagal, ambil dari IndexedDB lokal
        if (!allProducts || allProducts.length === 0) {
            allProducts = await window.posEngine?.getLocalProducts() || [];
        }

        renderProducts(allProducts);
        window.posEngine?.refreshPendingCounter();
        await checkActiveShift();
    }

    function renderProducts(products) {
        const grid = document.getElementById('product-grid');
        grid.innerHTML = '';

        const filtered = products.filter(p => {
            const matchCat = (currentCategory === 'all' || p.category === currentCategory);
            return matchCat;
        });

        if (filtered.length === 0) {
            grid.innerHTML = '<p class="col-span-3 text-center text-xs text-slate-400 py-12">Tidak ada produk ditemukan.</p>';
            return;
        }

        filtered.forEach(p => {
            const card = document.createElement('div');
            const isOutOfStock = Number(p.stock) <= 0;
            const isLowStock = Number(p.stock) > 0 && Number(p.stock) <= 10;
            
            let stockBadge = '';
            if (isOutOfStock) {
                stockBadge = '<span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-full">Habis (0)</span>';
            } else if (isLowStock) {
                stockBadge = `<span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-full">Sisa: ${p.stock}</span>`;
            } else {
                stockBadge = `<span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full">Stok: ${p.stock}</span>`;
            }

            card.className = `bg-white p-4 rounded-3xl border ${isOutOfStock ? 'border-rose-200/80 opacity-60' : 'border-slate-200/80 hover:border-emerald-500 hover:shadow-md hover:-translate-y-0.5'} shadow-xs cursor-pointer transition-all duration-200 flex flex-col justify-between group`;
            
            // Drag and drop capability
            card.draggable = !isOutOfStock;
            card.ondragstart = (e) => {
                e.dataTransfer.setData('application/json', JSON.stringify({ id: p.id, name: p.name, price: p.price, stock: p.stock }));
                e.dataTransfer.effectAllowed = 'copy';
            };

            card.onclick = () => {
                if (isOutOfStock) {
                    showToast('Stok produk ' + p.name + ' habis! Silakan lakukan penyesuaian stok jika barang tersedia.', 'error');
                    return;
                }
                // Elastic card pop animation
                card.classList.remove('pos-card-pop');
                void card.offsetWidth;
                card.classList.add('pos-card-pop');

                // Parabolic curved flight to cart
                launchFlyToCart(card, p);
                addToCart(p);
            };

            card.innerHTML = `
                <div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">${p.category || 'Umum'}</span>
                        ${stockBadge}
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 mt-2.5 line-clamp-2 group-hover:text-emerald-700 transition-colors leading-snug">${p.name}</h4>
                    <span class="text-[10px] text-slate-400 font-mono">${p.code || ''}</span>
                </div>
                <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs sm:text-sm font-black text-emerald-700 font-mono">Rp ${Number(p.price).toLocaleString('id-ID')}</span>
                    <span class="text-[10px] text-slate-400 font-medium">${p.unit || 'pcs'}</span>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function setCategory(cat) {
        currentCategory = cat;
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-600', 'text-white', 'shadow-xs');
            btn.classList.add('bg-slate-100/80', 'text-slate-600');
        });
        event.target.classList.remove('bg-slate-100/80', 'text-slate-600');
        event.target.classList.add('bg-emerald-600', 'text-white', 'shadow-xs');
        filterProducts();
    }

    function filterProducts() {
        const q = document.getElementById('search-product').value.toLowerCase();
        const filtered = allProducts.filter(p => {
            const matchCat = (currentCategory === 'all' || p.category === currentCategory);
            const matchText = p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q);
            return matchCat && matchText;
        });
        renderProducts(filtered);
    }

    function addToCart(product) {
        if (Number(product.stock) <= 0) {
            showToast('Kapasitas/stok layanan ' + product.name + ' habis!', 'error');
            return;
        }

        const existing = cart.find(item => item.product_id === product.id);
        if (existing) {
            const step = existing.unit === 'kg' ? 1 : 1;
            existing.qty = Math.round((existing.qty + step) * 100) / 100;
            existing.subtotal = Math.round(existing.qty * existing.price);
            existing._isUpdated = true;
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                category: product.category || 'Umum',
                price: Number(product.price),
                cost_price: Number(product.cost_price || 0),
                unit: product.unit || 'pcs',
                qty: 1,
                subtotal: Number(product.price),
                _isNew: true
            });
        }
        renderCart();
    }

    function updateCartQty(productId, delta) {
        const item = cart.find(i => i.product_id === productId);
        if (!item) return;

        const step = item.unit === 'kg' ? (delta > 0 ? 0.5 : -0.5) : delta;
        item.qty = Math.round((item.qty + step) * 100) / 100;
        if (item.qty <= 0) {
            removeFromCart(productId);
            return;
        } else {
            item.subtotal = Math.round(item.qty * item.price);
            item._isUpdated = true;
        }
        renderCart();
    }

    function setCartItemQty(productId, val) {
        const item = cart.find(i => i.product_id === productId);
        if (!item) return;
        const num = parseFloat(val);
        if (isNaN(num) || num <= 0) {
            removeFromCart(productId);
            return;
        } else {
            item.qty = Math.round(num * 100) / 100;
            item.subtotal = Math.round(item.qty * item.price);
            item._isUpdated = true;
        }
        renderCart();
    }

    function removeFromCart(productId) {
        const row = document.querySelector(`[data-cart-item-id="${productId}"]`);
        if (row) {
            row.classList.add('pos-line-leave');
            setTimeout(() => {
                cart = cart.filter(i => i.product_id !== productId);
                renderCart();
            }, 230);
        } else {
            cart = cart.filter(i => i.product_id !== productId);
            renderCart();
        }
    }

    function clearCart() {
        if (cart.length === 0) return;
        const rows = document.querySelectorAll('#cart-items > div');
        if (rows.length > 0) {
            rows.forEach(r => r.classList.add('pos-line-leave'));
            setTimeout(() => {
                cart = [];
                renderCart();
            }, 230);
        } else {
            cart = [];
            renderCart();
        }
    }

    function getCartTotals() {
        const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
        const discountInput = Number(document.getElementById('cart-discount')?.value || 0);
        const discount = Math.min(subtotal, Math.max(0, discountInput));
        const taxable = Math.max(0, subtotal - discount);
        const isTaxChecked = document.getElementById('cart-tax-toggle')?.checked || false;
        const tax = isTaxChecked ? Math.round(taxable * 0.10) : 0;
        const grandTotal = taxable + tax;
        return { subtotal, discount, tax, grandTotal, isTaxChecked };
    }

    function calculateGrandTotal() {
        const totals = getCartTotals();
        const subtotalEl = document.getElementById('cart-subtotal');
        if (subtotalEl) rollCurrency(subtotalEl, totals.subtotal);

        const taxEl = document.getElementById('cart-tax-amount');
        if (taxEl) rollCurrency(taxEl, totals.tax);

        const grandTotalEl = document.getElementById('cart-grand-total');
        if (grandTotalEl) rollCurrency(grandTotalEl, totals.grandTotal);

        calculateChange();
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        const badgeCount = document.getElementById('cart-badge-count');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-slate-400 text-xs">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="font-semibold text-slate-600 block">Nota Cucian Belum Terisi</span>
                    <span class="text-[11px] text-slate-400">Pilih layanan laundry pada katalog di sebelah kiri</span>
                </div>
            `;
            document.getElementById('cart-total-qty').innerText = '0 item';
            const subtotalEl = document.getElementById('cart-subtotal');
            if (subtotalEl) rollCurrency(subtotalEl, 0);
            const discEl = document.getElementById('cart-discount');
            if (discEl) discEl.value = '';
            const taxEl = document.getElementById('cart-tax-amount');
            if (taxEl) rollCurrency(taxEl, 0);
            const grandTotalEl = document.getElementById('cart-grand-total');
            if (grandTotalEl) rollCurrency(grandTotalEl, 0);
            document.getElementById('cash-received').value = '';
            const changeEl = document.getElementById('cash-change');
            if (changeEl) {
                rollCurrency(changeEl, 0);
                changeEl.className = 'font-black text-slate-900 font-mono text-sm';
            }
            if (badgeCount) badgeCount.innerText = '0';
            return;
        }

        container.innerHTML = '';
        let totalItemsCount = 0;

        cart.forEach(item => {
            totalItemsCount += (item.unit === 'kg' ? 1 : item.qty);

            const div = document.createElement('div');
            let animClass = '';
            if (item._isNew) {
                animClass = 'pos-line-enter';
                delete item._isNew;
            } else if (item._isUpdated) {
                animClass = 'pos-line-flash';
                delete item._isUpdated;
            }

            div.dataset.cartItemId = item.product_id;
            div.className = `flex items-center justify-between p-3 rounded-2xl bg-slate-50/90 border border-slate-100 text-xs hover:bg-slate-100/60 transition-colors ${animClass}`;
            div.innerHTML = `
                <div class="flex-1 pr-2">
                    <span class="font-bold text-slate-900 block leading-tight">${item.name}</span>
                    <span class="text-[11px] text-slate-500 font-mono">@ Rp ${Number(item.price).toLocaleString('id-ID')} / ${item.unit || 'pcs'}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <div class="flex items-center border border-slate-200 rounded-xl bg-white shadow-2xs overflow-hidden">
                        <button onclick="updateCartQty(${item.product_id}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-xs">-</button>
                        <input type="number" step="${item.unit === 'kg' ? '0.1' : '1'}" min="0.1" value="${item.qty}" onchange="setCartItemQty(${item.product_id}, this.value)"
                               class="w-12 text-center text-xs font-bold border-0 p-0 focus:ring-0 text-slate-800 font-mono bg-transparent" title="Ketik berat/jumlah">
                        <button onclick="updateCartQty(${item.product_id}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-xs">+</button>
                    </div>
                    <span class="font-bold text-slate-900 font-mono w-18 text-right">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</span>
                    <button onclick="removeFromCart(${item.product_id})" class="text-slate-300 hover:text-rose-600 p-1 rounded-lg transition-colors" title="Hapus">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            `;
            container.appendChild(div);
        });

        if (badgeCount) badgeCount.innerText = cart.length;
        document.getElementById('cart-total-qty').innerText = cart.length + ' jenis';
        calculateGrandTotal();

        const totals = getCartTotals();
        const cashInput = document.getElementById('cash-received');
        if (!cashInput.value || Number(cashInput.value) < totals.grandTotal) {
            cashInput.value = totals.grandTotal;
        }
        calculateChange();
    }

    function setQuickCash(amount) {
        const totals = getCartTotals();
        const cashInput = document.getElementById('cash-received');
        if (amount === 'pas') {
            cashInput.value = totals.grandTotal;
        } else {
            cashInput.value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        const totals = getCartTotals();
        let cashReceived = Number(document.getElementById('cash-received').value) || 0;
        let change = cashReceived - totals.grandTotal;
        const changeEl = document.getElementById('cash-change');
        if (!changeEl) return;

        if (change >= 0) {
            rollCurrency(changeEl, change);
            changeEl.className = 'font-black text-emerald-600 font-mono text-sm';
        } else {
            changeEl.innerText = 'Kurang Rp ' + Number(Math.abs(change)).toLocaleString('id-ID');
            changeEl.className = 'font-black text-rose-600 font-mono text-sm';
        }
    }

    async function processCheckout() {
        if (cart.length === 0) {
            showToast('Keranjang masih kosong!', 'error');
            return;
        }

        const totals = getCartTotals();
        const grandTotal = totals.grandTotal;
        const totalCost = cart.reduce((sum, item) => sum + (item.cost_price * item.qty), 0);
        const cashReceived = Number(document.getElementById('cash-received').value) || 0;

        if (cashReceived < grandTotal) {
            showToast('Uang yang diterima kurang dari total belanja!', 'error');
            return;
        }

        const changeAmount = cashReceived - grandTotal;
        const paymentMethod = document.getElementById('payment-method').value;

        // Nomor transaksi unik berbasis cabang & timestamp
        const timestamp = new Date().toISOString().replace(/[-:T.Z]/g, '').slice(0, 14);
        const randomSuffix = Math.floor(100 + Math.random() * 900);
        const transactionNo = `TRX-${branchCode}-${timestamp}-${randomSuffix}`;
        const trxDate = new Date().toISOString().slice(0, 19).replace('T', ' ');

        const customerName = document.getElementById('customer-name')?.value.trim() || 'Pelanggan Umum';
        const customerPhone = document.getElementById('customer-phone')?.value.trim() || '';
        const rackNo = document.getElementById('customer-rack')?.value.trim() || '';
        const estimatedCompletion = document.getElementById('customer-estimate')?.value || '';

        const trxPayload = {
            transaction_no: transactionNo,
            branch_id: branchId,
            cashier_id: cashierId,
            customer_name: customerName,
            customer_phone: customerPhone,
            rack_no: rackNo,
            laundry_status: 'sedang_dicuci',
            estimated_completion: estimatedCompletion,
            date: trxDate,
            subtotal: totals.subtotal,
            discount_amount: totals.discount,
            tax_amount: totals.tax,
            total_amount: grandTotal,
            total_cost: totalCost,
            payment_method: paymentMethod,
            cash_received: cashReceived,
            change_amount: changeAmount,
            items: cart
        };

        const isOnline = window.posEngine?.isOnline();
        let wasSyncedDirectly = false;

        // Simpan selalu ke IndexedDB lokal terlebih dahulu (Offline-First principle)
        await window.posEngine?.saveTransactionLocally(trxPayload);

        // Jika online, kirim langsung ke server
        if (isOnline) {
            try {
                const res = await fetch('../api/pos_sync.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ transactions: [trxPayload] })
                });
                const result = await res.json();
                if (result.success) {
                    await window.posEngine?.markAsSynced([transactionNo]);
                    wasSyncedDirectly = true;
                    showToast('Transaksi Berhasil & Otomatis Terjurnal di SIA Pusat!', 'success');
                    // Refresh active shift totals if shift is active
                    await checkActiveShift();
                } else {
                    console.warn('POS Sync server message:', result.message, result.errors);
                    if (result.errors && result.errors.length > 0) {
                        showToast('Gagal sinkron pusat: ' + result.errors[0], 'warning');
                    }
                }
            } catch (err) {
                console.warn('Gagal posting langsung, transaksi tetap tersimpan lokal:', err);
            }
        }

        if (!wasSyncedDirectly) {
            showToast('Transaksi Sukses disimpan di Mode Offline! Akan disinkron otomatis saat online.', 'info');
        }

        // Rayakan transaksi sukses dengan animasi cuci maskot Komak-Bot!
        if (typeof window.triggerKomakWash === 'function') {
            window.triggerKomakWash();
        }

        // Buka Struk
        showReceiptModal(trxPayload, wasSyncedDirectly);

        // Update local stock in memory & re-render
        cart.forEach(c => {
            const p = allProducts.find(item => item.id === c.product_id);
            if (p) p.stock = Math.max(0, Number(p.stock) - c.qty);
        });

        // Reset Cart & Input Pelanggan
        cart = [];
        if (document.getElementById('customer-name')) document.getElementById('customer-name').value = '';
        if (document.getElementById('customer-phone')) document.getElementById('customer-phone').value = '';
        if (document.getElementById('customer-rack')) document.getElementById('customer-rack').value = '';
        if (document.getElementById('customer-estimate')) {
            const d = new Date();
            d.setDate(d.getDate() + 2);
            document.getElementById('customer-estimate').value = d.toISOString().split('T')[0];
        }
        renderCart();
        renderProducts(allProducts);
    }

    function showReceiptModal(trx, isSynced) {
        const paper = document.getElementById('receipt-print-area');
        if (paper) {
            paper.classList.remove('pos-receipt-paper');
            void paper.offsetWidth; // Force CSS reflow
            paper.classList.add('pos-receipt-paper');
        }
        const chkBadge = document.getElementById('receipt-chk-badge');
        if (chkBadge) {
            chkBadge.classList.remove('pos-receipt-chk');
            void chkBadge.offsetWidth; // Force CSS reflow
            chkBadge.classList.add('pos-receipt-chk');
        }

        document.getElementById('rcp-trx-no').innerText = trx.transaction_no;
        document.getElementById('rcp-date').innerText = trx.date;
        document.getElementById('rcp-total').innerText = 'Rp ' + Number(trx.total_amount).toLocaleString('id-ID');
        document.getElementById('rcp-method').innerText = trx.payment_method;
        document.getElementById('rcp-cash').innerText = 'Rp ' + Number(trx.cash_received).toLocaleString('id-ID');
        document.getElementById('rcp-change').innerText = 'Rp ' + Number(trx.change_amount).toLocaleString('id-ID');

        // Subtotal, Discount & Tax breakdown on receipt
        const subtotalRow = document.getElementById('rcp-subtotal-row');
        const discountRow = document.getElementById('rcp-discount-row');
        const taxRow = document.getElementById('rcp-tax-row');

        if (trx.discount_amount && Number(trx.discount_amount) > 0) {
            if (subtotalRow) {
                subtotalRow.classList.remove('hidden');
                document.getElementById('rcp-subtotal').innerText = 'Rp ' + Number(trx.subtotal || (trx.total_amount + trx.discount_amount - (trx.tax_amount || 0))).toLocaleString('id-ID');
            }
            if (discountRow) {
                discountRow.classList.remove('hidden');
                document.getElementById('rcp-discount').innerText = '- Rp ' + Number(trx.discount_amount).toLocaleString('id-ID');
            }
        } else {
            if (subtotalRow) subtotalRow.classList.add('hidden');
            if (discountRow) discountRow.classList.add('hidden');
        }

        if (trx.tax_amount && Number(trx.tax_amount) > 0) {
            if (taxRow) {
                taxRow.classList.remove('hidden');
                document.getElementById('rcp-tax').innerText = 'Rp ' + Number(trx.tax_amount).toLocaleString('id-ID');
            }
        } else {
            if (taxRow) taxRow.classList.add('hidden');
        }

        const custName = trx.customer_name || 'Umum';
        const custPhone = trx.customer_phone || '';
        const rackNo = trx.rack_no || '';
        const estimateDate = trx.estimated_completion || '';

        const custEl = document.getElementById('rcp-customer');
        if (custEl) custEl.innerText = 'Pelanggan: ' + custName;

        const phoneEl = document.getElementById('rcp-phone');
        if (phoneEl) {
            if (custPhone) {
                phoneEl.innerText = 'WhatsApp: ' + custPhone;
                phoneEl.classList.remove('hidden');
            } else {
                phoneEl.classList.add('hidden');
            }
        }

        const rackBox = document.getElementById('rcp-rack-box');
        if (rackBox) {
            if (rackNo || estimateDate) {
                rackBox.classList.remove('hidden');
                document.getElementById('rcp-rack').innerText = rackNo ? 'Rak: ' + rackNo : 'Rak: -';
                document.getElementById('rcp-estimate').innerText = estimateDate ? 'Siap: ' + estimateDate : 'Siap: 2 Hari';
            } else {
                rackBox.classList.add('hidden');
            }
        }

        // Generate WhatsApp notification text
        let waText = `*PJBL KOMAK LAUNDRY*\n`;
        waText += `Halo Kak ${custName}, terima kasih telah mencuci di PJBL Komak Laundry!\n\n`;
        waText += `*Rincian Nota Cucian:*\n`;
        waText += `No. Nota: ${trx.transaction_no}\n`;
        waText += `Tanggal: ${trx.date}\n`;
        if (rackNo) waText += `Lokasi Rak: ${rackNo}\n`;
        if (estimateDate) waText += `Estimasi Siap Ambil: ${estimateDate}\n`;
        waText += `\n*Daftar Layanan:*\n`;
        trx.items.forEach(it => {
            const u = it.unit ? ` ${it.unit}` : '';
            waText += `- ${it.name} (${it.qty}${u}): Rp ${Number(it.subtotal).toLocaleString('id-ID')}\n`;
        });
        waText += `\n*Total Tagihan: Rp ${Number(trx.total_amount).toLocaleString('id-ID')}*\n`;
        waText += `Metode Pembayaran: ${trx.payment_method}\n`;
        waText += `Status Bayar: ${Number(trx.cash_received) >= Number(trx.total_amount) ? 'LUNAS' : 'Belum Lunas'}\n\n`;
        waText += `Kami akan menginfokan kembali saat cucian selesai. Harap tunjukkan pesan ini saat pengambilan. Terima kasih! 🙏`;

        let cleanedPhone = custPhone.replace(/[^0-9]/g, '');
        if (cleanedPhone.startsWith('0')) {
            cleanedPhone = '62' + cleanedPhone.slice(1);
        }

        const btnWa = document.getElementById('btn-wa-receipt');
        if (btnWa) {
            btnWa.href = `https://api.whatsapp.com/send?phone=${cleanedPhone}&text=${encodeURIComponent(waText)}`;
        }

        const itemsContainer = document.getElementById('rcp-items');
        itemsContainer.innerHTML = '';
        trx.items.forEach(it => {
            const row = document.createElement('div');
            row.className = 'flex justify-between';
            const unitLabel = it.unit ? ` ${it.unit}` : '';
            row.innerHTML = `
                <span>${it.name} (${it.qty}${unitLabel})</span>
                <span>Rp ${Number(it.subtotal).toLocaleString('id-ID')}</span>
            `;
            itemsContainer.appendChild(row);
        });

        const statusEl = document.getElementById('rcp-sync-status');
        if (isSynced) {
            statusEl.className = 'font-bold text-emerald-600 mt-1';
            statusEl.innerText = 'STATUS: TERSINKRON KE SIA PUSAT (JURNAL AKTIF)';
        } else {
            statusEl.className = 'font-bold text-amber-600 mt-1';
            statusEl.innerText = 'STATUS: TERSIMPAN LOKAL (PENDING SYNC)';
        }

        document.getElementById('receipt-modal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
    }

    // --- Cashier Stock Adjustment Handlers ---
    function openCashierStockModal() {
        const select = document.getElementById('c-adj-product');
        select.innerHTML = '<option value="">-- Pilih Barang --</option>' + 
            allProducts.map(p => `<option value="${p.id}">[${p.code}] ${p.name} - Stok: ${p.stock} ${p.unit || 'pcs'}</option>`).join('');
        
        document.getElementById('c-adj-stock-info').classList.add('hidden');
        document.getElementById('c-adj-type').value = 'rusak';
        onCashierAdjTypeChange('rusak');
        document.getElementById('c-adj-qty').value = '';
        document.getElementById('c-adj-reason').value = '';
        document.getElementById('cashier-stock-modal').classList.remove('hidden');
    }

    function closeCashierStockModal() {
        document.getElementById('cashier-stock-modal').classList.add('hidden');
    }

    function onCashierProductSelect(id) {
        const info = document.getElementById('c-adj-stock-info');
        if (!id) {
            info.classList.add('hidden');
            return;
        }
        const prod = allProducts.find(p => p.id == id);
        if (prod) {
            document.getElementById('c-adj-current-stock').innerText = prod.stock;
            document.getElementById('c-adj-unit').innerText = prod.unit || 'pcs';
            document.getElementById('c-adj-cost').innerText = 'Rp ' + Number(prod.cost_price || 0).toLocaleString('id-ID');
            info.classList.remove('hidden');
        }
    }

    function onCashierAdjTypeChange(type) {
        const label = document.getElementById('c-adj-qty-label');
        const input = document.getElementById('c-adj-qty');
        if (type === 'opname') {
            label.innerText = 'Stok Fisik Baru (Hasil Hitung Fisik Kasir)';
            input.placeholder = 'Contoh: 15';
            input.min = '0';
        } else if (type === 'masuk') {
            label.innerText = 'Jumlah Barang Masuk / Kiriman (+)';
            input.placeholder = 'Contoh: 10';
            input.min = '1';
        } else {
            label.innerText = 'Jumlah Barang Rusak / Berkurang (-)';
            input.placeholder = 'Contoh: 1';
            input.min = '1';
        }
    }

    async function submitCashierStockAdjustment(e) {
        e.preventDefault();
        const productId = document.getElementById('c-adj-product').value;
        const type = document.getElementById('c-adj-type').value;
        const qtyVal = document.getElementById('c-adj-qty').value;
        const reason = document.getElementById('c-adj-reason').value;

        if (!productId) {
            showToast('Pilih barang terlebih dahulu!', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('branch_id', branchId);
        formData.append('type', type);
        formData.append('reason', reason);
        formData.append('auto_journal', '1'); // Otomatis catat jurnal di SIA pusat jika ada dampak biaya

        if (type === 'opname') {
            formData.append('new_stock', qtyVal);
        } else {
            formData.append('qty', qtyVal);
        }

        const btn = document.getElementById('btn-save-cashier-adj');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        try {
            const res = await fetch('../api/products.php?action=adjust_stock', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message || 'Penyesuaian stok berhasil disimpan!', 'success');
                closeCashierStockModal();
                await initPOS(); // reload products & stock
            } else {
                showToast(result.message || 'Gagal menyesuaikan stok!', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan koneksi!', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan Penyesuaian';
        }
    }

    // --- Cashier Branch Stock Overview Handlers ---
    function openStockOverviewModal() {
        renderStockOverviewTable(allProducts);
        document.getElementById('stock-modal-search').value = '';
        document.getElementById('cashier-stock-overview-modal').classList.remove('hidden');
    }

    function closeStockOverviewModal() {
        document.getElementById('cashier-stock-overview-modal').classList.add('hidden');
    }

    function renderStockOverviewTable(products) {
        const tbody = document.getElementById('stock-overview-tbody');
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400">Tidak ada produk.</td></tr>';
            return;
        }

        tbody.innerHTML = products.map(p => {
            const isOutOfStock = Number(p.stock) <= 0;
            const isLowStock = Number(p.stock) > 0 && Number(p.stock) <= 10;
            let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            if (isOutOfStock) badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
            else if (isLowStock) badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';

            return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-2.5 px-3 font-mono text-slate-500">${p.code}</td>
                    <td class="py-2.5 px-3 font-bold text-slate-900">${p.name}</td>
                    <td class="py-2.5 px-3 text-slate-500"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold">${p.category || 'Umum'}</span></td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">Rp ${Number(p.price).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border ${badgeClass}">
                            ${p.stock} ${p.unit || 'pcs'}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterStockModalTable() {
        const q = document.getElementById('stock-modal-search').value.toLowerCase();
        const filtered = allProducts.filter(p => 
            p.name.toLowerCase().includes(q) || 
            p.code.toLowerCase().includes(q) || 
            (p.category && p.category.toLowerCase().includes(q))
        );
        renderStockOverviewTable(filtered);
    }

    // ==========================================
    // CASHIER SHIFT MANAGEMENT
    // ==========================================
    let activeShift = null;

    async function checkActiveShift() {
        try {
            const res = await fetch(`../api/shifts.php?action=active_shift&branch_id=${branchId}`);
            const result = await res.json();
            if (result.success && result.has_active_shift) {
                activeShift = result.shift;
                const dot = document.getElementById('shift-dot');
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse';
                const statusText = document.getElementById('shift-status-text');
                if (statusText) statusText.innerText = `Shift Aktif: ${activeShift.cashier_name} (Mulai: ${activeShift.start_time.slice(11, 16)})`;
                const floatBadge = document.getElementById('shift-cash-float');
                if (floatBadge) {
                    floatBadge.innerText = `Modal Kas: Rp ${Number(activeShift.starting_cash).toLocaleString('id-ID')}`;
                    floatBadge.classList.remove('hidden');
                }
                const btnOpen = document.getElementById('btn-open-shift');
                if (btnOpen) btnOpen.classList.add('hidden');
                const btnClose = document.getElementById('btn-close-shift');
                if (btnClose) btnClose.classList.remove('hidden');
            } else {
                activeShift = null;
                const dot = document.getElementById('shift-dot');
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-slate-400';
                const statusText = document.getElementById('shift-status-text');
                if (statusText) statusText.innerText = 'Shift Belum Dibuka';
                const floatBadge = document.getElementById('shift-cash-float');
                if (floatBadge) floatBadge.classList.add('hidden');
                const btnOpen = document.getElementById('btn-open-shift');
                if (btnOpen) btnOpen.classList.remove('hidden');
                const btnClose = document.getElementById('btn-close-shift');
                if (btnClose) btnClose.classList.add('hidden');
            }
        } catch (err) {
            console.warn('Gagal memuat status shift:', err);
        }
    }

    function openStartShiftModal() {
        document.getElementById('open-shift-modal').classList.remove('hidden');
    }

    function closeStartShiftModal() {
        document.getElementById('open-shift-modal').classList.add('hidden');
    }

    async function submitStartShift(e) {
        e.preventDefault();
        const startCash = document.getElementById('shift-starting-cash').value;
        const notes = document.getElementById('shift-start-notes').value;
        const btn = document.getElementById('btn-submit-start-shift');
        btn.disabled = true;
        btn.innerText = 'Membuka...';

        try {
            const fd = new FormData();
            fd.append('action', 'start_shift');
            fd.append('branch_id', branchId);
            fd.append('starting_cash', startCash);
            fd.append('notes', notes);

            const res = await fetch('../api/shifts.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeStartShiftModal();
                await checkActiveShift();
            } else {
                showToast(result.message || 'Gagal membuka shift', 'error');
            }
        } catch (err) {
            showToast('Kesalahan koneksi saat membuka shift', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Mulai Shift';
        }
    }

    async function openEndShiftModal() {
        if (!activeShift) {
            showToast('Tidak ada shift aktif yang perlu ditutup.', 'info');
            return;
        }
        try {
            const res = await fetch(`../api/shifts.php?action=active_shift&branch_id=${branchId}`);
            const result = await res.json();
            if (result.success && result.shift) {
                activeShift = result.shift;
            }
        } catch (e) {}

        const startCash = Number(activeShift.starting_cash || 0);
        const cashSales = Number(activeShift.total_cash_sales || 0);
        const nonCashSales = Number(activeShift.total_non_cash_sales || 0);
        const expectedCash = Number(activeShift.expected_cash || (startCash + cashSales));

        document.getElementById('cs-starting-cash').innerText = 'Rp ' + startCash.toLocaleString('id-ID');
        document.getElementById('cs-cash-sales').innerText = '+ Rp ' + cashSales.toLocaleString('id-ID');
        document.getElementById('cs-non-cash-sales').innerText = 'Rp ' + nonCashSales.toLocaleString('id-ID');
        document.getElementById('cs-expected-cash').innerText = 'Rp ' + expectedCash.toLocaleString('id-ID');
        document.getElementById('shift-actual-cash').value = expectedCash;
        calculateShiftVariance();

        document.getElementById('close-shift-modal').classList.remove('hidden');
    }

    function closeEndShiftModal() {
        document.getElementById('close-shift-modal').classList.add('hidden');
    }

    function calculateShiftVariance() {
        if (!activeShift) return;
        const expectedCash = Number(activeShift.expected_cash || (Number(activeShift.starting_cash || 0) + Number(activeShift.total_cash_sales || 0)));
        const actualCash = Number(document.getElementById('shift-actual-cash').value) || 0;
        const diff = actualCash - expectedCash;
        const badge = document.getElementById('cs-variance-amount');

        if (diff === 0) {
            badge.innerText = 'Rp 0 (Sesuai / Pas)';
            badge.className = 'font-black font-mono px-2.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300';
        } else if (diff > 0) {
            badge.innerText = `+ Rp ${diff.toLocaleString('id-ID')} (Lebih)`;
            badge.className = 'font-black font-mono px-2.5 py-0.5 rounded-lg bg-blue-100 text-blue-800 border border-blue-300';
        } else {
            badge.innerText = `- Rp ${Math.abs(diff).toLocaleString('id-ID')} (Kurang)`;
            badge.className = 'font-black font-mono px-2.5 py-0.5 rounded-lg bg-rose-100 text-rose-800 border border-rose-300';
        }
    }

    async function submitEndShift(e) {
        e.preventDefault();
        if (!activeShift) return;
        const actualCash = document.getElementById('shift-actual-cash').value;
        const notes = document.getElementById('shift-end-notes').value;
        const btn = document.getElementById('btn-submit-end-shift');
        btn.disabled = true;
        btn.innerText = 'Menutup Shift...';

        try {
            const fd = new FormData();
            fd.append('action', 'close_shift');
            fd.append('shift_id', activeShift.id);
            fd.append('actual_cash', actualCash);
            fd.append('notes', notes);

            const res = await fetch('../api/shifts.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                closeEndShiftModal();
                await checkActiveShift();
            } else {
                showToast(result.message || 'Gagal menutup shift', 'error');
            }
        } catch (err) {
            showToast('Kesalahan koneksi saat menutup shift', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Tutup Shift Sekarang';
        }
    }

    // ==========================================
    // SALES RETURN MANAGEMENT
    // ==========================================
    let foundReturnTrx = null;

    function openSalesReturnModal() {
        document.getElementById('ret-search-trx').value = '';
        document.getElementById('ret-details-container').classList.add('hidden');
        document.getElementById('ret-actions').classList.add('hidden');
        document.getElementById('sales-return-modal').classList.remove('hidden');
    }

    function closeSalesReturnModal() {
        document.getElementById('sales-return-modal').classList.add('hidden');
    }

    async function searchReturnTransaction() {
        const trxNo = document.getElementById('ret-search-trx').value.trim();
        if (!trxNo) {
            showToast('Masukkan nomor transaksi terlebih dahulu!', 'warning');
            return;
        }

        const btn = document.getElementById('btn-search-ret-trx');
        btn.disabled = true;
        btn.innerText = 'Mencari...';

        try {
            const res = await fetch(`../api/sales_returns.php?action=get_transaction&transaction_no=${encodeURIComponent(trxNo)}`);
            const result = await res.json();
            if (result.success && result.transaction) {
                foundReturnTrx = result.transaction;
                document.getElementById('ret-info-date').innerText = foundReturnTrx.date;
                document.getElementById('ret-info-payment').innerText = `${foundReturnTrx.payment_method} (Total: Rp ${Number(foundReturnTrx.total_amount).toLocaleString('id-ID')})`;

                const tbody = document.getElementById('ret-items-tbody');
                tbody.innerHTML = (foundReturnTrx.items || []).map(it => `
                    <tr class="hover:bg-slate-50 transition-colors" data-product-id="${it.product_id}" data-price="${it.price}" data-cost="${it.cost_price || 0}">
                        <td class="p-2.5 font-bold text-slate-800">${it.product_name}</td>
                        <td class="p-2.5 text-right font-mono text-slate-600">Rp ${Number(it.price).toLocaleString('id-ID')}</td>
                        <td class="p-2.5 text-center font-bold text-slate-700">${it.qty}</td>
                        <td class="p-2.5 text-center">
                            <input type="number" min="0" max="${it.qty}" value="0" oninput="calculateReturnTotal()"
                                   class="ret-item-qty w-16 p-1 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-1 focus:ring-rose-500">
                        </td>
                        <td class="p-2.5 text-right font-mono font-bold text-slate-900 ret-item-subtotal">Rp 0</td>
                    </tr>
                `).join('');

                calculateReturnTotal();
                document.getElementById('ret-details-container').classList.remove('hidden');
                document.getElementById('ret-actions').classList.remove('hidden');
            } else {
                showToast(result.message || 'Transaksi tidak ditemukan!', 'error');
            }
        } catch (err) {
            showToast('Kesalahan saat mencari transaksi!', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Cari Transaksi';
        }
    }

    function calculateReturnTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#ret-items-tbody tr');
        rows.forEach(r => {
            const price = Number(r.getAttribute('data-price')) || 0;
            const input = r.querySelector('.ret-item-qty');
            const qty = Math.max(0, Number(input?.value) || 0);
            const subtotal = qty * price;
            total += subtotal;
            const subtotalEl = r.querySelector('.ret-item-subtotal');
            if (subtotalEl) subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        });

        document.getElementById('ret-total-amount').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    async function submitSalesReturn() {
        if (!foundReturnTrx) return;
        const reason = document.getElementById('ret-reason').value.trim();
        if (!reason) {
            showToast('Alasan retur wajib diisi!', 'warning');
            return;
        }

        const itemsToReturn = [];
        const rows = document.querySelectorAll('#ret-items-tbody tr');
        rows.forEach(r => {
            const productId = Number(r.getAttribute('data-product-id'));
            const price = Number(r.getAttribute('data-price')) || 0;
            const costPrice = Number(r.getAttribute('data-cost')) || 0;
            const qty = Number(r.querySelector('.ret-item-qty')?.value) || 0;
            if (qty > 0) {
                itemsToReturn.push({
                    product_id: productId,
                    qty: qty,
                    price: price,
                    cost_price: costPrice,
                    subtotal: qty * price
                });
            }
        });

        if (itemsToReturn.length === 0) {
            showToast('Pilih minimal 1 barang dengan kuantitas > 0 untuk diretur!', 'warning');
            return;
        }

        const refundMethod = document.getElementById('ret-refund-method').value;
        const btn = document.getElementById('btn-submit-sales-return');
        btn.disabled = true;
        btn.innerText = 'Memproses Retur...';

        try {
            const res = await fetch('../api/sales_returns.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create_return',
                    transaction_no: foundReturnTrx.transaction_no,
                    branch_id: branchId,
                    reason: reason,
                    refund_method: refundMethod,
                    items: itemsToReturn
                })
            });

            const result = await res.json();
            if (result.success) {
                showToast(`Retur Berhasil (${result.return_no}). Jurnal Pembalik: ${result.reversal_journal_no}`, 'success');
                closeSalesReturnModal();
                await initPOS(); // reload products to reflect restored stock
            } else {
                showToast(result.message || 'Gagal memproses retur!', 'error');
            }
        } catch (err) {
            showToast('Kesalahan saat memproses retur!', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Proses Retur & Reversal';
        }
    }

    // ==========================================
    // LAUNDRY ORDERS & PIPELINE MANAGEMENT (SIMPELIN STYLE)
    // ==========================================
    let currentLaundryStatusFilter = '';
    let currentLaundryOrders = [];

    function openLaundryOrdersModal() {
        document.getElementById('laundry-orders-modal').classList.remove('hidden');
        loadLaundryOrders(currentLaundryStatusFilter);
    }

    function closeLaundryOrdersModal() {
        document.getElementById('laundry-orders-modal').classList.add('hidden');
    }

    function filterLaundryOrders(status) {
        currentLaundryStatusFilter = status;
        const filterBtns = document.querySelectorAll('#laundry-status-filters .laundry-filter-btn');
        filterBtns.forEach(btn => {
            btn.classList.remove('bg-slate-900', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');
        });
        if (window.event && window.event.target) {
            window.event.target.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');
            window.event.target.classList.add('bg-slate-900', 'text-white');
        }
        loadLaundryOrders(status);
    }

    function escapeHtmlLaundry(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadLaundryOrders(statusFilter = '') {
        const tbody = document.getElementById('laundry-orders-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400 font-medium">Memuat data antrian cucian...</td></tr>';

        try {
            const url = `../api/pos_sync.php?action=list_orders&branch_id=${branchId}&status=${encodeURIComponent(statusFilter)}`;
            const res = await fetch(url);
            const result = await res.json();

            if (result.success && Array.isArray(result.data)) {
                currentLaundryOrders = result.data;
                renderLaundryOrdersTable(currentLaundryOrders);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-rose-500 font-medium">Gagal memuat antrian: ' + (result.message || 'Format tidak valid') + '</td></tr>';
            }
        } catch (err) {
            console.error('Error fetching laundry orders:', err);
            tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-rose-500 font-medium">Gagal menghubungi server untuk memuat antrian cucian.</td></tr>';
        }
    }

    function renderLaundryOrdersTable(orders) {
        const tbody = document.getElementById('laundry-orders-tbody');
        if (!tbody) return;

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center text-slate-400 font-medium">Tidak ada pesanan cucian pada tahapan ini.</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(ord => {
            const itemsHtml = (ord.items || []).map(it => 
                `<div>• ${escapeHtmlLaundry(it.product_name || it.name || 'Layanan')} (${it.qty} ${it.unit || 'pcs'})</div>`
            ).join('');

            const statusColors = {
                'antrian': 'border-amber-300 text-amber-800 bg-amber-50',
                'sedang_dicuci': 'border-blue-300 text-blue-800 bg-blue-50',
                'sedang_disetrika': 'border-purple-300 text-purple-800 bg-purple-50',
                'siap_diambil': 'border-emerald-300 text-emerald-800 bg-emerald-50',
                'selesai': 'border-slate-300 text-slate-700 bg-slate-50'
            };
            const currentStatusColor = statusColors[ord.laundry_status] || 'border-slate-300 text-slate-700 bg-white';

            const phoneDisplay = ord.customer_phone ? 
                `<span class="flex items-center space-x-1"><i class="fa-brands fa-whatsapp text-emerald-600"></i><span>${escapeHtmlLaundry(ord.customer_phone)}</span></span>` : 
                `<span class="text-slate-400 italic">Tanpa WA</span>`;

            const waBtn = ord.customer_phone ? `
                <button type="button" onclick="triggerWhatsAppPickup(${ord.id})"
                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center space-x-1.5 mx-auto">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>WA Siap</span>
                </button>
            ` : `<span class="text-slate-400 text-[11px] italic">Tanpa WA</span>`;

            return `
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3 px-3 align-top">
                        <div class="font-mono font-bold text-slate-900">${escapeHtmlLaundry(ord.transaction_no)}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">${ord.date || '-'}</div>
                        <div class="text-[10px] text-slate-500 font-semibold mt-0.5">${escapeHtmlLaundry(ord.branch_name || branchCode)}</div>
                    </td>
                    <td class="py-3 px-3 align-top">
                        <div class="font-bold text-slate-900">${escapeHtmlLaundry(ord.customer_name || 'Pelanggan Umum')}</div>
                        <div class="text-[11px] font-mono text-slate-600 mt-0.5">${phoneDisplay}</div>
                    </td>
                    <td class="py-3 px-3 align-top">
                        <div class="text-[11px] text-slate-700 max-w-xs space-y-0.5 leading-tight">
                            ${itemsHtml || '<span class="text-slate-400 italic">Tidak ada item</span>'}
                        </div>
                        <div class="text-[11px] font-extrabold text-emerald-700 font-mono mt-1">
                            Total: Rp ${Number(ord.total_amount).toLocaleString('id-ID')}
                        </div>
                        ${ord.estimated_completion ? `<div class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md inline-block mt-1 font-semibold">Estimasi: ${escapeHtmlLaundry(ord.estimated_completion)}</div>` : ''}
                    </td>
                    <td class="py-3 px-3 align-top">
                        <div class="flex items-center space-x-1">
                            <input type="text" id="rack-input-${ord.id}" value="${escapeHtmlLaundry(ord.rack_no || '')}" placeholder="A-01"
                                   class="w-20 px-2 py-1.5 text-xs font-mono font-bold rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <button type="button" onclick="saveOrderRack(${ord.id})" title="Simpan Rak" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </div>
                    </td>
                    <td class="py-3 px-3 align-top">
                        <select id="status-select-${ord.id}" onchange="changeOrderStatus(${ord.id}, this.value)"
                                class="text-xs font-bold rounded-xl border px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 transition-colors ${currentStatusColor}">
                            <option value="antrian" ${ord.laundry_status === 'antrian' ? 'selected' : ''}>⏳ Antrian Masuk</option>
                            <option value="sedang_dicuci" ${ord.laundry_status === 'sedang_dicuci' ? 'selected' : ''}>🧼 Sedang Dicuci</option>
                            <option value="sedang_disetrika" ${ord.laundry_status === 'sedang_disetrika' ? 'selected' : ''}>💨 Sedang Disetrika</option>
                            <option value="siap_diambil" ${ord.laundry_status === 'siap_diambil' ? 'selected' : ''}>✨ Siap Diambil</option>
                            <option value="selesai" ${ord.laundry_status === 'selesai' ? 'selected' : ''}>✅ Selesai / Diambil</option>
                        </select>
                    </td>
                    <td class="py-3 px-3 align-top text-center">
                        ${waBtn}
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function changeOrderStatus(orderId, newStatus) {
        const rackInput = document.getElementById('rack-input-' + orderId);
        const rackNo = rackInput ? rackInput.value.trim() : '';

        try {
            const fd = new FormData();
            fd.append('action', 'update_status');
            fd.append('id', orderId);
            fd.append('laundry_status', newStatus);
            fd.append('rack_no', rackNo);

            const res = await fetch('../api/pos_sync.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                const ord = currentLaundryOrders.find(o => o.id == orderId);
                if (ord) {
                    ord.laundry_status = newStatus;
                    ord.rack_no = rackNo;
                }
                const selectEl = document.getElementById('status-select-' + orderId);
                if (selectEl) {
                    const statusColors = {
                        'antrian': 'border-amber-300 text-amber-800 bg-amber-50',
                        'sedang_dicuci': 'border-blue-300 text-blue-800 bg-blue-50',
                        'sedang_disetrika': 'border-purple-300 text-purple-800 bg-purple-50',
                        'siap_diambil': 'border-emerald-300 text-emerald-800 bg-emerald-50',
                        'selesai': 'border-slate-300 text-slate-700 bg-slate-50'
                    };
                    selectEl.className = `text-xs font-bold rounded-xl border px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 transition-colors ${statusColors[newStatus] || 'border-slate-300 bg-white'}`;
                }
            } else {
                showToast(result.message || 'Gagal memperbarui status!', 'error');
            }
        } catch (err) {
            showToast('Kesalahan saat memperbarui status pengerjaan cucian', 'error');
        }
    }

    async function saveOrderRack(orderId) {
        const rackInput = document.getElementById('rack-input-' + orderId);
        const rackNo = rackInput ? rackInput.value.trim() : '';
        const selectEl = document.getElementById('status-select-' + orderId);
        const currentStatus = selectEl ? selectEl.value : 'antrian';

        try {
            const fd = new FormData();
            fd.append('action', 'update_status');
            fd.append('id', orderId);
            fd.append('laundry_status', currentStatus);
            fd.append('rack_no', rackNo);

            const res = await fetch('../api/pos_sync.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showToast('Nomor rak berhasil disimpan: ' + (rackNo || '-'), 'success');
                const ord = currentLaundryOrders.find(o => o.id == orderId);
                if (ord) ord.rack_no = rackNo;
            } else {
                showToast(result.message || 'Gagal menyimpan nomor rak!', 'error');
            }
        } catch (err) {
            showToast('Kesalahan koneksi saat menyimpan nomor rak', 'error');
        }
    }

    function triggerWhatsAppPickup(orderId) {
        const ord = currentLaundryOrders.find(o => o.id == orderId);
        if (!ord || !ord.customer_phone) {
            showToast('Nomor WhatsApp pelanggan tidak ditemukan!', 'warning');
            return;
        }

        const rackInput = document.getElementById('rack-input-' + orderId);
        const rackNo = rackInput ? rackInput.value.trim() : (ord.rack_no || '-');
        const custName = ord.customer_name || 'Pelanggan';
        const trxNo = ord.transaction_no;
        const totalAmount = ord.total_amount;

        let cleanedPhone = ord.customer_phone.replace(/[^0-9]/g, '');
        if (cleanedPhone.startsWith('0')) {
            cleanedPhone = '62' + cleanedPhone.slice(1);
        }

        let msg = `*PJBL KOMAK LAUNDRY - CUCIAN SIAP DIAMBIL* 🧺✨\n`;
        msg += `Halo Kak ${custName},\n`;
        msg += `Kabar gembira! Cucian Anda dengan rincian berikut:\n\n`;
        msg += `• *No. Nota:* ${trxNo}\n`;
        msg += `• *Lokasi Rak/Loker:* ${rackNo || 'Kasir/Penyimpanan'}\n`;
        msg += `• *Total Tagihan:* Rp ${Number(totalAmount).toLocaleString('id-ID')}\n\n`;
        msg += `Sudah *SELESAI* diproses, wangi, bersih, dan siap diambil di outlet PJBL Komak Laundry.\n`;
        msg += `Harap tunjukkan pesan/nota ini kepada kasir saat pengambilan ya Kak. Terima kasih! 🙏`;

        const waUrl = `https://api.whatsapp.com/send?phone=${cleanedPhone}&text=${encodeURIComponent(msg)}`;
        window.open(waUrl, '_blank');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
