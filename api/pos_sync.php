<?php
/**
 * POS Sync API - Menerima transaksi kasir offline & auto-generate Jurnal Akuntansi
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? 'sync';

// Action: List Laundry Orders & Tracking Pipeline
if ($action === 'list_orders') {
    $branchId = (int)($_GET['branch_id'] ?? 0);
    $statusFilter = trim($_GET['status'] ?? '');
    $sql = "SELECT p.*, b.name as branch_name, u.name as cashier_name 
            FROM pos_transactions p 
            LEFT JOIN branches b ON p.branch_id = b.id 
            LEFT JOIN users u ON p.cashier_id = u.id";
    
    $where = [];
    $params = [];
    
    if (($_SESSION['user']['role'] ?? '') === 'kasir') {
        $where[] = "p.branch_id = ?";
        $params[] = (int)($_SESSION['user']['branch_id'] ?? 1);
    } elseif ($branchId > 0) {
        $where[] = "p.branch_id = ?";
        $params[] = $branchId;
    }
    
    if (!empty($statusFilter)) {
        $where[] = "p.laundry_status = ?";
        $params[] = $statusFilter;
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY p.id DESC LIMIT 80";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    $stmtItems = $db->prepare("SELECT * FROM pos_items WHERE pos_transaction_id = ?");
    foreach ($orders as &$ord) {
        $stmtItems->execute([$ord['id']]);
        $ord['items'] = $stmtItems->fetchAll();
    }
    jsonResponse(['success' => true, 'data' => $orders]);
}

// Action: Update Laundry Status & Rack Number
if ($action === 'update_status') {
    $trxId = (int)($_POST['id'] ?? 0);
    $newStatus = trim($_POST['laundry_status'] ?? '');
    $rackNo = trim($_POST['rack_no'] ?? '');
    
    $allowedStatuses = ['antrian', 'sedang_dicuci', 'sedang_disetrika', 'siap_diambil', 'selesai'];
    if (!$trxId || !in_array($newStatus, $allowedStatuses)) {
        jsonResponse(['success' => false, 'message' => 'Data status tidak valid.'], 400);
    }
    
    $stmtUpd = $db->prepare("UPDATE pos_transactions SET laundry_status = ?, rack_no = CASE WHEN ? != '' THEN ? ELSE rack_no END WHERE id = ?");
    $stmtUpd->execute([$newStatus, $rackNo, $rackNo, $trxId]);
    
    auditLog('UPDATE', 'POS Laundry', "Memperbarui status pesanan laundry ID $trxId menjadi '$newStatus'" . ($rackNo ? " (Rak: $rackNo)" : ""), ['id' => $trxId, 'status' => $newStatus, 'rack_no' => $rackNo]);
    
    jsonResponse(['success' => true, 'message' => 'Status pengerjaan cucian berhasil diperbarui.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Hanya metode POST yang diizinkan.'], 405);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

$transactions = $data['transactions'] ?? [];
if (empty($transactions) && isset($data['transaction_no'])) {
    // Single transaction payload
    $transactions = [$data];
}

if (empty($transactions)) {
    jsonResponse(['success' => false, 'message' => 'Tidak ada data transaksi yang dikirim.'], 400);
}

$syncedIds = [];
$errors = [];

// Helper untuk menemukan akun kas kasir yang cocok berdasarkan cabang
function getCashierAccount(PDO $db, $branchId) {
    if ($branchId == 1) {
        // Kantor Pusat
        $accPusat = $db->query("SELECT id FROM accounts WHERE code = '1103' LIMIT 1")->fetch();
        if ($accPusat) return (int)$accPusat['id'];
    }

    // 1. Cek kode spesifik cabang yang baru (1101-XX)
    $stmtCode = $db->prepare("SELECT id FROM accounts WHERE code = ? OR code = ? LIMIT 1");
    $stmtCode->execute(['1101-' . str_pad($branchId, 2, '0', STR_PAD_LEFT), '1101-' . $branchId]);
    $acc = $stmtCode->fetch();
    if ($acc) return (int)$acc['id'];

    // 2. Akun legacy Cabang 1 (1101) & Cabang 2 (1102)
    if ($branchId == 2) {
        $acc = $db->query("SELECT id FROM accounts WHERE code = '1101' LIMIT 1")->fetch();
        if ($acc) return (int)$acc['id'];
    } elseif ($branchId == 3) {
        $acc = $db->query("SELECT id FROM accounts WHERE code = '1102' LIMIT 1")->fetch();
        if ($acc) return (int)$acc['id'];
    }

    // 3. Cari berdasarkan nama cabang
    $b = $db->query("SELECT name FROM branches WHERE id = " . (int)$branchId)->fetch();
    if ($b) {
        $stmtName = $db->prepare("SELECT id FROM accounts WHERE name LIKE ? LIMIT 1");
        $stmtName->execute(['%Kas Kasir%' . $b['name'] . '%']);
        $acc = $stmtName->fetch();
        if ($acc) return (int)$acc['id'];
    }

    // Fallback ke akun kas pertama
    $accDefault = $db->query("SELECT id FROM accounts WHERE code LIKE '1101%' OR code LIKE '1102%' ORDER BY code ASC LIMIT 1")->fetch();
    return $accDefault ? (int)$accDefault['id'] : 1;
}

// Ambil ID akun penting untuk Penjualan & Persediaan Perpetual
$accSalesLaundry = $db->query("SELECT id FROM accounts WHERE code = '4101' LIMIT 1")->fetch()['id'] ?? 13; // Pendapatan Jasa Laundry
$accSalesRetail  = $db->query("SELECT id FROM accounts WHERE code = '4102' LIMIT 1")->fetch()['id'] ?? 14; // Pendapatan Penjualan Retail & Lain-lain
$accDiscount     = $db->query("SELECT id FROM accounts WHERE code = '4104' LIMIT 1")->fetch()['id'] ?? null; // Diskon Penjualan
$accTaxLiability = $db->query("SELECT id FROM accounts WHERE code = '2102' LIMIT 1")->fetch()['id'] ?? null; // Utang Pajak
$accHPP          = $db->query("SELECT id FROM accounts WHERE code = '5101' LIMIT 1")->fetch()['id'] ?? 15; // Beban Pokok Pendapatan (HPP)
$accInventory    = $db->query("SELECT id FROM accounts WHERE code = '1105' LIMIT 1")->fetch()['id'] ?? 5;  // Persediaan Bahan Cuci & Perlengkapan

foreach ($transactions as $trx) {
    $trxNo = $trx['transaction_no'] ?? null;
    $branchId = (int)($trx['branch_id'] ?? $_SESSION['user']['branch_id'] ?? 1);
    $cashierId = (int)($trx['cashier_id'] ?? $_SESSION['user']['id']);
    $trxDate = $trx['date'] ?? date('Y-m-d H:i:s');
    $totalAmount = (float)($trx['total_amount'] ?? 0);
    $totalCost = (float)($trx['total_cost'] ?? 0);
    $discountAmount = (float)($trx['discount_amount'] ?? 0);
    $taxAmount = (float)($trx['tax_amount'] ?? 0);
    $paymentMethod = $trx['payment_method'] ?? 'Tunai';
    $cashReceived = (float)($trx['cash_received'] ?? $totalAmount);
    $changeAmount = (float)($trx['change_amount'] ?? 0);
    $items = $trx['items'] ?? [];

    if (!$trxNo || $totalAmount <= 0) {
        $errors[] = "Transaksi $trxNo tidak valid (data kosong atau nominal <= 0).";
        continue;
    }

    try {
        $db->beginTransaction();

        // 1. Cek Idempotency: Apakah transaksi dengan nomor ini sudah tersimpan?
        $stmtCheck = $db->prepare("SELECT id, status FROM pos_transactions WHERE transaction_no = ?");
        $stmtCheck->execute([$trxNo]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            // Sudah tersimpan, anggap sudah sinkron
            $syncedIds[] = $trxNo;
            $db->commit();
            continue;
        }

        // 2. SISTEM PERSEDIAAN PERPETUAL SAK EMKM: Verifikasi Biaya Pokok Langsung dari Database Master Produk
        $stmtProdLookup = $db->prepare("SELECT id, name, category, price, cost_price, unit FROM products WHERE id = ?");
        $processedItems = [];
        $laundryGross = 0.0;
        $retailGross = 0.0;
        $totalCalculatedHPP = 0.0;

        foreach ($items as $item) {
            $pId = (int)($item['product_id'] ?? 0);
            $pQty = max(0.01, (float)($item['qty'] ?? 1));
            $pPrice = (float)($item['price'] ?? 0);
            $pCost = (float)($item['cost_price'] ?? 0);
            $pCategory = trim($item['category'] ?? '');
            $pName = trim($item['name'] ?? 'Item Transaksi');

            if ($pId > 0) {
                $stmtProdLookup->execute([$pId]);
                $prodRow = $stmtProdLookup->fetch();
                if ($prodRow) {
                    $pName = $prodRow['name'];
                    $pCategory = $prodRow['category'];
                    if ($pPrice <= 0) $pPrice = (float)$prodRow['price'];
                    // Gunakan cost_price riil dari database master untuk memastikan akurasi HPP
                    if ((float)$prodRow['cost_price'] > 0) {
                        $pCost = (float)$prodRow['cost_price'];
                    }
                }
            }

            $itemSubtotal = round($pPrice * $pQty, 2);
            $itemHPP = round($pCost * $pQty, 2);

            // Klasifikasi Pendapatan: Penjualan Retail Bahan (4102) vs Jasa Laundry (4101)
            if (strcasecmp($pCategory, 'Bahan & Perlengkapan') === 0) {
                $retailGross += $itemSubtotal;
            } else {
                $laundryGross += $itemSubtotal;
            }
            $totalCalculatedHPP += $itemHPP;

            $processedItems[] = [
                'product_id' => $pId,
                'name' => $pName,
                'category' => $pCategory,
                'price' => $pPrice,
                'cost_price' => $pCost,
                'qty' => $pQty,
                'subtotal' => $itemSubtotal,
                'hpp' => $itemHPP
            ];
        }

        // Jika rincian item tidak memiliki HPP tapi total_cost dikirim dari payload, gunakan nilai cadangan
        if ($totalCalculatedHPP <= 0 && $totalCost > 0) {
            $totalCalculatedHPP = $totalCost;
        }
        $totalCost = $totalCalculatedHPP;

        // Alokasi Pendapatan Kotor (Net of Discount & Tax Allocation)
        $netGrossSales = $totalAmount + $discountAmount - $taxAmount;
        $sumGross = $laundryGross + $retailGross;

        if ($sumGross > 0) {
            $ratio = $netGrossSales / $sumGross;
            $revenueLaundry = round($laundryGross * $ratio, 2);
            $revenueRetail = round($netGrossSales - $revenueLaundry, 2);
        } else {
            $revenueLaundry = $netGrossSales;
            $revenueRetail = 0.0;
        }

        // 3. OTOMATISASI JURNAL AKUNTANSI (Double-Entry Bookkeeping)
        $accCashier = getCashierAccount($db, $branchId);

        // Buat Header Jurnal
        $customerName = trim($trx['customer_name'] ?? '');
        $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, ref_id, created_by) 
                                 VALUES (?, ?, date(?), ?, 'pos', ?, ?)");
        $jrnDesc = "Penjualan Kasir POS ($paymentMethod) - " . $trxNo . ($customerName ? " [Pelanggan: $customerName]" : "") . " [Sistem Perpetual]";
        $stmtJrn->execute([$branchId, 'JRN-' . $trxNo, $trxDate, $jrnDesc, $trxNo, $cashierId]);
        $journalEntryId = $db->lastInsertId();

        $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
        
        // Jurnal 1: Pengakuan Penerimaan Kas & Diskon
        // (D) Kas Kasir Cabang (sebesar kas yang diterima secara bersih)
        $stmtItem->execute([$journalEntryId, $accCashier, $totalAmount, 0, 'Penerimaan Penjualan ' . $trxNo . ($customerName ? " ($customerName)" : "")]);

        // (D) Diskon Penjualan (jika ada potongan harga)
        if ($discountAmount > 0 && $accDiscount) {
            $stmtItem->execute([$journalEntryId, $accDiscount, $discountAmount, 0, 'Diskon Penjualan ' . $trxNo]);
        }

        // (K) Utang PPN/Pajak (jika memungut pajak transaksi)
        if ($taxAmount > 0 && $accTaxLiability) {
            $stmtItem->execute([$journalEntryId, $accTaxLiability, 0, $taxAmount, 'PPN Penjualan ' . $trxNo]);
        }

        // (K) Pendapatan Jasa Laundry (Akun 4101)
        if ($revenueLaundry > 0) {
            $stmtItem->execute([$journalEntryId, $accSalesLaundry, 0, $revenueLaundry, 'Pendapatan Jasa Laundry ' . $trxNo]);
        }

        // (K) Pendapatan Penjualan Retail & Bahan (Akun 4102)
        if ($revenueRetail > 0) {
            $stmtItem->execute([$journalEntryId, $accSalesRetail, 0, $revenueRetail, 'Penjualan Retail Perlengkapan ' . $trxNo]);
        }

        // Jurnal 2: SISTEM PERSEDIAAN PERPETUAL SAK EMKM (Beban Pokok Pendapatan & Pengurangan Persediaan Bahan)
        if ($totalCost > 0) {
            // (D) Beban Pokok Pendapatan / HPP (Akun 5101)
            $stmtItem->execute([$journalEntryId, $accHPP, $totalCost, 0, 'HPP Penjualan (Perpetual SAK EMKM) ' . $trxNo]);
            // (K) Persediaan Bahan Cuci & Perlengkapan (Akun 1105)
            $stmtItem->execute([$journalEntryId, $accInventory, 0, $totalCost, 'Pengurangan Persediaan Bahan/Barang ' . $trxNo]);
        }

        // 4. Simpan Header Transaksi POS
        $customerPhone = trim($trx['customer_phone'] ?? '');
        $rackNo = trim($trx['rack_no'] ?? '');
        $laundryStatus = trim($trx['laundry_status'] ?? 'sedang_dicuci');
        $estimatedCompletion = !empty($trx['estimated_completion']) ? $trx['estimated_completion'] : null;

        $stmtTrx = $db->prepare("INSERT INTO pos_transactions (transaction_no, branch_id, cashier_id, date, total_amount, total_cost, payment_method, cash_received, change_amount, discount_amount, tax_amount, customer_name, customer_phone, rack_no, laundry_status, estimated_completion, status, journal_entry_id) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'synced', ?)");
        $stmtTrx->execute([$trxNo, $branchId, $cashierId, $trxDate, $totalAmount, $totalCost, $paymentMethod, $cashReceived, $changeAmount, $discountAmount, $taxAmount, $customerName, $customerPhone, $rackNo, $laundryStatus, $estimatedCompletion, $journalEntryId]);
        $posTrxId = $db->lastInsertId();

        // 5. Simpan Detail Item POS & Kurangi Stok (Global & Multi-Gudang Cabang)
        $stmtPosItem = $db->prepare("INSERT INTO pos_items (pos_transaction_id, product_id, product_name, price, cost_price, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtStock = $db->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?");
        $stmtBranchStock = $db->prepare("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES (?, ?, 0) 
                                         ON CONFLICT(branch_id, product_id) DO UPDATE SET stock = MAX(0, stock - ?)");

        foreach ($processedItems as $pItem) {
            $stmtPosItem->execute([
                $posTrxId, 
                $pItem['product_id'], 
                $pItem['name'], 
                $pItem['price'], 
                $pItem['cost_price'], 
                $pItem['qty'], 
                $pItem['subtotal']
            ]);
            if ($pItem['product_id'] > 0) {
                // Potong stok global
                $stmtStock->execute([$pItem['qty'], $pItem['product_id']]);
                // Potong stok cabang spesifik
                $stmtBranchStock->execute([$branchId, $pItem['product_id'], $pItem['qty']]);
            }
        }

        // 6. Update Akumulasi Kas pada Shift Kasir yang Sedang Aktif
        $stmtShift = $db->prepare("SELECT id FROM cashier_shifts WHERE branch_id = ? AND cashier_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
        $stmtShift->execute([$branchId, $cashierId]);
        $activeShift = $stmtShift->fetch();
        if ($activeShift) {
            if (strcasecmp($paymentMethod, 'Tunai') === 0) {
                $db->prepare("UPDATE cashier_shifts SET total_cash_sales = total_cash_sales + ?, expected_cash = initial_cash + total_cash_sales + ? WHERE id = ?")
                   ->execute([$totalAmount, $totalAmount, $activeShift['id']]);
            } else {
                $db->prepare("UPDATE cashier_shifts SET total_non_cash_sales = total_non_cash_sales + ? WHERE id = ?")
                   ->execute([$totalAmount, $activeShift['id']]);
            }
        }

        $db->commit();
        $syncedIds[] = $trxNo;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $errors[] = "Gagal memproses transaksi $trxNo: " . $e->getMessage();
    }
}

if (!empty($syncedIds)) {
    auditLog('POS_SYNC', 'pos', "Sinkronisasi " . count($syncedIds) . " transaksi kasir POS", [
        'synced_count' => count($syncedIds),
        'transaction_nos' => $syncedIds
    ]);
}

jsonResponse([
    'success' => count($syncedIds) > 0,
    'message' => count($syncedIds) . ' transaksi berhasil disinkronisasi & otomatis dijurnal.',
    'synced_transaction_nos' => $syncedIds,
    'errors' => $errors
]);
