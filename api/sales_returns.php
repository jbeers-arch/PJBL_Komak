<?php
/**
 * Sales Returns API (Retur Penjualan & Pembalik Akuntansi)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
        $sql = "SELECT sr.*, b.name as branch_name, u.name as cashier_name, j.transaction_no as journal_no
                FROM sales_returns sr
                LEFT JOIN branches b ON sr.branch_id = b.id
                LEFT JOIN users u ON sr.cashier_id = u.id
                LEFT JOIN journal_entries j ON sr.journal_entry_id = j.id
                WHERE 1=1";
        $params = [];
        if ($branchId) {
            $sql .= " AND sr.branch_id = ?";
            $params[] = $branchId;
        }
        $sql .= " ORDER BY sr.id DESC LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtItems = $db->prepare("SELECT * FROM sales_return_items WHERE sales_return_id = ?");
        foreach ($returns as &$ret) {
            $stmtItems->execute([$ret['id']]);
            $ret['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        jsonResponse(['success' => true, 'data' => $returns]);
        break;

    case 'search_transaction':
        $trxNo = trim($_GET['transaction_no'] ?? '');
        if (empty($trxNo)) {
            jsonResponse(['success' => false, 'message' => 'Nomor transaksi tidak boleh kosong.'], 400);
        }

        $stmt = $db->prepare("SELECT p.*, b.name as branch_name, u.name as cashier_name 
                              FROM pos_transactions p
                              LEFT JOIN branches b ON p.branch_id = b.id
                              LEFT JOIN users u ON p.cashier_id = u.id
                              WHERE p.transaction_no = ?");
        $stmt->execute([$trxNo]);
        $trx = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trx) {
            jsonResponse(['success' => false, 'message' => "Transaksi kasir '$trxNo' tidak ditemukan."], 404);
        }

        $stmtItems = $db->prepare("SELECT pi.*, p.code as product_code 
                                   FROM pos_items pi
                                   LEFT JOIN products p ON pi.product_id = p.id
                                   WHERE pi.pos_transaction_id = ?");
        $stmtItems->execute([$trx['id']]);
        $trx['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse(['success' => true, 'data' => $trx]);
        break;

    case 'create':
        $trxNo = trim($_POST['transaction_no'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Barang cacat / Pembatalan');
        $itemsJson = $_POST['items'] ?? '[]';
        $returnItems = json_decode($itemsJson, true);

        if (empty($trxNo) || empty($returnItems)) {
            jsonResponse(['success' => false, 'message' => 'Nomor transaksi dan minimal 1 item retur wajib diisi.'], 400);
        }

        $stmtTrx = $db->prepare("SELECT * FROM pos_transactions WHERE transaction_no = ?");
        $stmtTrx->execute([$trxNo]);
        $origTrx = $stmtTrx->fetch(PDO::FETCH_ASSOC);

        if (!$origTrx) {
            jsonResponse(['success' => false, 'message' => 'Transaksi asli tidak ditemukan.'], 404);
        }

        $branchId = (int)$origTrx['branch_id'];
        $cashierId = (int)$_SESSION['user']['id'];
        $today = date('Y-m-d H:i:s');

        // Cek lock periode
        $dateOnly = date('Y-m-d');
        $stmtLock = $db->prepare("SELECT period_name FROM period_locks WHERE ? <= period_end AND status = 'locked' LIMIT 1");
        $stmtLock->execute([$dateOnly]);
        if ($stmtLock->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Periode hari ini terkunci. Tidak dapat memproses retur.'], 403);
        }

        // Ambil akun penting
        $accReturn = $db->query("SELECT id FROM accounts WHERE code = '4103' LIMIT 1")->fetchColumn() 
                     ?: $db->query("SELECT id FROM accounts WHERE code = '4101' LIMIT 1")->fetchColumn();
        $accInventory = $db->query("SELECT id FROM accounts WHERE code = '1105' LIMIT 1")->fetchColumn() ?: 5;
        $accHPP = $db->query("SELECT id FROM accounts WHERE code = '5101' LIMIT 1")->fetchColumn() ?: 15;

        // Tentukan akun kas cabang
        $accCash = null;
        if ($branchId == 1) {
            $accCash = $db->query("SELECT id FROM accounts WHERE code = '1103' LIMIT 1")->fetchColumn();
        } else {
            $stmtCode = $db->prepare("SELECT id FROM accounts WHERE code = ? OR code = ? LIMIT 1");
            $stmtCode->execute(['1101-' . str_pad($branchId, 2, '0', STR_PAD_LEFT), '1101-' . $branchId]);
            $accCash = $stmtCode->fetchColumn();
            if (!$accCash) {
                $accCode = ($branchId == 2) ? '1101' : (($branchId == 3) ? '1102' : '1101');
                $accCash = $db->query("SELECT id FROM accounts WHERE code = '$accCode' LIMIT 1")->fetchColumn();
            }
        }
        $accCash = $accCash ?: 1;

        try {
            $db->beginTransaction();

            $totalRefund = 0;
            $totalCostRefund = 0;
            $validItems = [];

            foreach ($returnItems as $it) {
                $pId = (int)$it['product_id'];
                $qty = (int)$it['qty'];
                $price = (float)$it['price'];
                $costPrice = (float)($it['cost_price'] ?? 0);
                $pName = trim($it['name'] ?? 'Item Retur');

                if ($qty <= 0) continue;

                $subtotal = $price * $qty;
                $costSubtotal = $costPrice * $qty;

                $totalRefund += $subtotal;
                $totalCostRefund += $costSubtotal;

                $validItems[] = [
                    'product_id' => $pId,
                    'product_name' => $pName,
                    'price' => $price,
                    'cost_price' => $costPrice,
                    'qty' => $qty,
                    'subtotal' => $subtotal
                ];
            }

            if (empty($validItems) || $totalRefund <= 0) {
                throw new Exception('Kuantitas item retur tidak valid.');
            }

            // 1. Buat Header Jurnal Pembalik Retur
            $retNo = 'RET-' . date('Ymd') . '-' . rand(1000, 9999);
            $jrnNo = 'JRN-' . $retNo;
            $jrnDesc = "Retur Penjualan atas [$trxNo]: $reason";

            $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, ref_id, created_by)
                                     VALUES (?, ?, date(?), ?, 'manual', ?, ?)");
            $stmtJrn->execute([$branchId, $jrnNo, $today, $jrnDesc, $retNo, $cashierId]);
            $journalId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
            
            // Jurnal 1: Pembalik Penerimaan Kas
            // (D) Retur & Pengurangan Penjualan (4103)
            $stmtItem->execute([$journalId, $accReturn, $totalRefund, 0, 'Retur Penjualan ' . $trxNo]);
            // (K) Kas Kasir Cabang
            $stmtItem->execute([$journalId, $accCash, 0, $totalRefund, 'Pengembalian Dana Kas ' . $trxNo]);

            // Jurnal 2: Pembalik HPP & Restok Persediaan
            if ($totalCostRefund > 0) {
                // (D) Persediaan Barang Dagang (1105)
                $stmtItem->execute([$journalId, $accInventory, $totalCostRefund, 0, 'Restok Barang Retur ' . $trxNo]);
                // (K) Beban Pokok Penjualan (5101)
                $stmtItem->execute([$journalId, $accHPP, 0, $totalCostRefund, 'Pembalik HPP Retur ' . $trxNo]);
            }

            // 2. Simpan Header Retur
            $stmtRet = $db->prepare("INSERT INTO sales_returns 
                (return_no, pos_transaction_id, branch_id, cashier_id, date, total_refund, total_cost_refund, payment_method, reason, journal_entry_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtRet->execute([$retNo, $origTrx['id'], $branchId, $cashierId, $today, $totalRefund, $totalCostRefund, $origTrx['payment_method'], $reason, $journalId]);
            $retId = $db->lastInsertId();

            // 3. Simpan Detail Item Retur & Kembalikan Stok Fisik (Global & Cabang)
            $stmtRetItem = $db->prepare("INSERT INTO sales_return_items (sales_return_id, product_id, product_name, price, cost_price, qty, subtotal)
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtRestock = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmtRestockBranch = $db->prepare("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES (?, ?, ?)
                                               ON CONFLICT(branch_id, product_id) DO UPDATE SET stock = stock + excluded.stock");

            foreach ($validItems as $vi) {
                $stmtRetItem->execute([$retId, $vi['product_id'], $vi['product_name'], $vi['price'], $vi['cost_price'], $vi['qty'], $vi['subtotal']]);
                if ($vi['product_id'] > 0) {
                    $stmtRestock->execute([$vi['qty'], $vi['product_id']]);
                    $stmtRestockBranch->execute([$branchId, $vi['product_id'], $vi['qty']]);
                }
            }

            $db->commit();

            auditLog('CREATE', 'sales_returns', "Memproses retur penjualan ($retNo) untuk transaksi $trxNo sebesar Rp " . number_format($totalRefund, 0, ',', '.'), [
                'return_no' => $retNo,
                'transaction_no' => $trxNo,
                'total_refund' => $totalRefund,
                'journal_no' => $jrnNo,
                'items_count' => count($validItems)
            ]);

            jsonResponse([
                'success' => true,
                'message' => "Retur penjualan ($retNo) berhasil diproses. Jurnal pembalik ($jrnNo) telah diposting dan stok telah dikembalikan.",
                'return_no' => $retNo,
                'total_refund' => $totalRefund
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal memproses retur: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
