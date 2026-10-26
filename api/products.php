<?php
/**
 * Product Master & Inventory Stock Adjustment API
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $stmt = $db->query("SELECT * FROM products ORDER BY name ASC");
        $products = $stmt->fetchAll();
        jsonResponse(['success' => true, 'data' => $products]);
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if ($prod) {
            jsonResponse(['success' => true, 'data' => $prod]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }
        break;

    case 'create':
        requireAuth(['akuntan']);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Umum');
        $price = (float)($_POST['price'] ?? 0);
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'pcs');

        if (empty($code) || empty($name) || $price <= 0) {
            jsonResponse(['success' => false, 'message' => 'Kode, nama, dan harga jual produk harus valid.'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO products (code, name, category, price, cost_price, stock, unit) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $category, $price, $costPrice, $stock, $unit]);
            $newId = $db->lastInsertId();

            // Catat saldo awal di history stock
            if ($stock > 0) {
                $stmtAdj = $db->prepare("INSERT INTO stock_adjustments (branch_id, product_id, type, qty_change, previous_stock, new_stock, reason, adjusted_by) 
                                         VALUES (1, ?, 'masuk', ?, 0, ?, 'Stok Awal Produk Baru', ?)");
                $stmtAdj->execute([$newId, $stock, $stock, $_SESSION['user']['id']]);
            }

            auditLog('CREATE', 'Produk', "Menambahkan item baru '$name' (Kode: $code, Harga: Rp " . number_format($price, 0, ',', '.') . ")", [
                'id' => $newId,
                'code' => $code,
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'cost_price' => $costPrice,
                'stock' => $stock
            ]);

            jsonResponse(['success' => true, 'message' => 'Item jual berhasil ditambahkan ke katalog.']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menambah produk: ' . $e->getMessage()], 400);
        }
        break;

    case 'update':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Umum');
        $price = (float)($_POST['price'] ?? 0);
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'pcs');

        if (!$id || empty($code) || empty($name) || $price <= 0) {
            jsonResponse(['success' => false, 'message' => 'Data produk tidak lengkap atau tidak valid.'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE products SET code = ?, name = ?, category = ?, price = ?, cost_price = ?, unit = ? WHERE id = ?");
            $stmt->execute([$code, $name, $category, $price, $costPrice, $unit, $id]);

            auditLog('UPDATE', 'Produk', "Memperbarui data produk '$name' (Kode: $code, Harga: Rp " . number_format($price, 0, ',', '.') . ")", [
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'price' => $price,
                'cost_price' => $costPrice
            ]);

            jsonResponse(['success' => true, 'message' => 'Data produk berhasil diperbarui.']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui produk: ' . $e->getMessage()], 400);
        }
        break;

    case 'delete':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        // Ambil nama sebelum delete
        $delName = $db->query("SELECT name FROM products WHERE id = " . (int)$id)->fetchColumn() ?: "ID $id";

        // Cek apakah produk sudah ada di pos_items
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM pos_items WHERE product_id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            jsonResponse(['success' => false, 'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi kasir.'], 400);
        }

        $stmtDel = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmtDel->execute([$id]);

        auditLog('DELETE', 'Produk', "Menghapus item produk '$delName' (ID: $id)", ['id' => $id, 'name' => $delName]);

        jsonResponse(['success' => true, 'message' => 'Produk berhasil dihapus.']);
        break;

    case 'adjust_stock':
        // Dapat dilakukan oleh Kasir (di cabangnya) maupun Akuntan (Pusat)
        $productId = (int)($_POST['product_id'] ?? 0);
        $branchId = (int)($_POST['branch_id'] ?? $_SESSION['user']['branch_id'] ?? 1);
        $type = trim($_POST['type'] ?? 'opname'); // masuk, keluar, rusak, opname
        $reason = trim($_POST['reason'] ?? 'Penyesuaian stok berkala');
        $autoJournal = !empty($_POST['auto_journal']) ? 1 : 0;

        $stmtProd = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmtProd->execute([$productId]);
        $prod = $stmtProd->fetch();

        if (!$prod) {
            jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $prevStock = (int)$prod['stock'];
        $newStock = $prevStock;
        $qtyChange = 0;

        if ($type === 'opname') {
            $newStock = (int)($_POST['new_stock'] ?? $prevStock);
            $qtyChange = $newStock - $prevStock;
        } elseif ($type === 'masuk') {
            $qty = abs((int)($_POST['qty'] ?? 0));
            $qtyChange = $qty;
            $newStock = $prevStock + $qty;
        } elseif ($type === 'keluar' || $type === 'rusak') {
            $qty = abs((int)($_POST['qty'] ?? 0));
            $qtyChange = -$qty;
            $newStock = max(0, $prevStock - $qty);
        }

        if ($qtyChange === 0 && $type !== 'opname') {
            jsonResponse(['success' => false, 'message' => 'Jumlah penyesuaian stok harus lebih dari 0.'], 400);
        }

        try {
            $db->beginTransaction();

            // 1. Update stok di tabel products
            $stmtUpd = $db->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmtUpd->execute([$newStock, $productId]);

            // 2. Catat riwayat penyesuaian stok
            $costImpact = abs($qtyChange) * (float)$prod['cost_price'];
            $stmtAdj = $db->prepare("INSERT INTO stock_adjustments 
                (branch_id, product_id, type, qty_change, previous_stock, new_stock, cost_impact, reason, adjusted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtAdj->execute([$branchId, $productId, $type, $qtyChange, $prevStock, $newStock, $costImpact, $reason, $_SESSION['user']['id']]);

            // 3. Jika diminta auto_journal dan ada dampak finansial (HPP)
            if ($autoJournal && $costImpact > 0) {
                $trxNo = 'JRN-AJE-STK-' . date('Ymd') . '-' . rand(100, 999);
                $desc = "Penyesuaian Stok $type (" . $prod['name'] . ") - " . $reason;
                $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, created_by) 
                                         VALUES (?, ?, date('now'), ?, 'adjustment', ?)");
                $stmtJrn->execute([$branchId, $trxNo, $desc, $_SESSION['user']['id']]);
                $jrnId = $db->lastInsertId();

                $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");

                if ($qtyChange < 0) {
                    // Selisih kurang / rusak: (D) Beban Selisih Stok (6107), (K) Persediaan (1105)
                    $accLoss = $db->query("SELECT id FROM accounts WHERE code = '6107' LIMIT 1")->fetchColumn() ?: 20;
                    $accInv = $db->query("SELECT id FROM accounts WHERE code = '1105' LIMIT 1")->fetchColumn() ?: 5;
                    $stmtItem->execute([$jrnId, $accLoss, $costImpact, 0, $desc]);
                    $stmtItem->execute([$jrnId, $accInv, 0, $costImpact, 'Pengurangan Persediaan']);
                } else {
                    // Selisih lebih: (D) Persediaan (1105), (K) Pendapatan Lain-lain (4102)
                    $accInv = $db->query("SELECT id FROM accounts WHERE code = '1105' LIMIT 1")->fetchColumn() ?: 5;
                    $accGain = $db->query("SELECT id FROM accounts WHERE code = '4102' LIMIT 1")->fetchColumn() ?: 14;
                    $stmtItem->execute([$jrnId, $accInv, $costImpact, 0, 'Penambahan Persediaan']);
                    $stmtItem->execute([$jrnId, $accGain, 0, $costImpact, $desc]);
                }
            }

            $db->commit();

            auditLog('STOCK_ADJUST', 'Inventaris', "Penyesuaian stok $type '{$prod['name']}' ($qtyChange unit, stok akhir: $newStock)", [
                'product_id' => $productId,
                'product_name' => $prod['name'],
                'type' => $type,
                'qty_change' => $qtyChange,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'reason' => $reason
            ]);
            jsonResponse([
                'success' => true,
                'message' => 'Penyesuaian stok berhasil disimpan. Stok saat ini: ' . $newStock . ' ' . $prod['unit'],
                'new_stock' => $newStock
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal menyesuaikan stok: ' . $e->getMessage()], 500);
        }
        break;

    case 'stock_history':
        $productId = isset($_GET['product_id']) && $_GET['product_id'] !== '' ? (int)$_GET['product_id'] : null;
        $sql = "SELECT a.*, p.name as product_name, p.code as product_code, p.unit, b.name as branch_name, u.name as user_name
                FROM stock_adjustments a
                JOIN products p ON a.product_id = p.id
                LEFT JOIN branches b ON a.branch_id = b.id
                LEFT JOIN users u ON a.adjusted_by = u.id";
        if ($productId) {
            $sql .= " WHERE a.product_id = $productId";
        }
        $sql .= " ORDER BY a.id DESC LIMIT 50";
        $history = $db->query($sql)->fetchAll();
        jsonResponse(['success' => true, 'data' => $history]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
