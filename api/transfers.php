<?php
/**
 * Stock Transfers & Multi-Warehouse API (Transfer Stok Antar Cabang)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $sql = "SELECT st.*, fb.name as from_branch_name, tb.name as to_branch_name, u.name as creator_name,
                       (SELECT COUNT(*) FROM stock_transfer_items WHERE transfer_id = st.id) as item_count,
                       (SELECT COALESCE(SUM(qty), 0) FROM stock_transfer_items WHERE transfer_id = st.id) as total_qty
                FROM stock_transfers st
                JOIN branches fb ON st.from_branch_id = fb.id
                JOIN branches tb ON st.to_branch_id = tb.id
                LEFT JOIN users u ON st.created_by = u.id
                ORDER BY st.id DESC";
        $transfers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $stmtItems = $db->prepare("SELECT sti.*, p.code as product_code, p.name as product_name, p.unit 
                                   FROM stock_transfer_items sti 
                                   JOIN products p ON sti.product_id = p.id 
                                   WHERE sti.transfer_id = ?");
        foreach ($transfers as &$t) {
            $stmtItems->execute([$t['id']]);
            $t['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        jsonResponse(['success' => true, 'data' => $transfers]);
        break;

    case 'branch_stocks':
        $branchId = (int)($_GET['branch_id'] ?? $_SESSION['user']['branch_id'] ?? 1);
        $category = $_GET['category'] ?? null;
        
        $sql = "SELECT p.id as product_id, p.code, p.name, p.category, p.price, p.cost_price, p.unit,
                       COALESCE(bs.stock, 0) as branch_stock,
                       p.stock as global_stock
                FROM products p
                LEFT JOIN branch_stocks bs ON p.id = bs.product_id AND bs.branch_id = ?
                WHERE 1=1";
        $params = [$branchId];

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY p.name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse(['success' => true, 'branch_id' => $branchId, 'data' => $stocks]);
        break;

    case 'consumables_summary':
        // Ringkasan stok seluruh keperluan cuci & bahan antar cabang
        $branches = $db->query("SELECT id, code, name FROM branches WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $products = $db->query("SELECT id, code, name, category, unit, cost_price, price FROM products WHERE category = 'Bahan & Perlengkapan' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $summary = [];
        $stmtBStock = $db->prepare("SELECT COALESCE(stock, 0) as stock FROM branch_stocks WHERE branch_id = ? AND product_id = ?");

        foreach ($products as $p) {
            $row = [
                'product_id' => (int)$p['id'],
                'code' => $p['code'],
                'name' => $p['name'],
                'unit' => $p['unit'],
                'cost_price' => (float)$p['cost_price'],
                'branch_stocks' => [],
                'total_stock' => 0
            ];

            foreach ($branches as $b) {
                $stmtBStock->execute([$b['id'], $p['id']]);
                $stk = (int)$stmtBStock->fetchColumn();
                $row['branch_stocks'][$b['id']] = [
                    'branch_id' => $b['id'],
                    'branch_name' => $b['name'],
                    'branch_code' => $b['code'],
                    'stock' => $stk
                ];
                $row['total_stock'] += $stk;
            }
            $summary[] = $row;
        }

        jsonResponse([
            'success' => true,
            'branches' => $branches,
            'summary' => $summary
        ]);
        break;

    case 'create':
        requireAuth(['akuntan']);
        $fromBranchId = (int)($_POST['from_branch_id'] ?? 0);
        $toBranchId = (int)($_POST['to_branch_id'] ?? 0);
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $notes = trim($_POST['notes'] ?? 'Transfer stok gudang');
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);

        if (!$fromBranchId || !$toBranchId || $fromBranchId === $toBranchId) {
            jsonResponse(['success' => false, 'message' => 'Cabang asal dan cabang tujuan harus berbeda dan valid.'], 400);
        }

        if (empty($items)) {
            jsonResponse(['success' => false, 'message' => 'Minimal 1 produk harus dipilih untuk transfer stok.'], 400);
        }

        try {
            $db->beginTransaction();

            // Cek ketersediaan stok di cabang asal
            $stmtCheck = $db->prepare("SELECT stock FROM branch_stocks WHERE branch_id = ? AND product_id = ?");
            foreach ($items as $it) {
                $pId = (int)$it['product_id'];
                $qty = (int)$it['qty'];
                if ($qty <= 0) continue;

                $stmtCheck->execute([$fromBranchId, $pId]);
                $currStock = (int)$stmtCheck->fetchColumn();

                if ($currStock < $qty) {
                    $pName = $db->query("SELECT name FROM products WHERE id = $pId")->fetchColumn();
                    throw new Exception("Stok untuk '$pName' di cabang asal tidak mencukupi (Tersedia: $currStock, Diminta: $qty).");
                }
            }

            $transferNo = 'TRF-' . date('Ymd') . '-' . rand(1000, 9999);
            $stmtTrf = $db->prepare("INSERT INTO stock_transfers 
                (transfer_no, from_branch_id, to_branch_id, date, status, notes, created_by)
                VALUES (?, ?, ?, ?, 'completed', ?, ?)");
            $stmtTrf->execute([$transferNo, $fromBranchId, $toBranchId, $date, $notes, $_SESSION['user']['id']]);
            $transferId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO stock_transfer_items (transfer_id, product_id, qty, cost_price) VALUES (?, ?, ?, ?)");
            $stmtDeduct = $db->prepare("UPDATE branch_stocks SET stock = MAX(0, stock - ?) WHERE branch_id = ? AND product_id = ?");
            $stmtAdd = $db->prepare("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES (?, ?, ?)
                                     ON CONFLICT(branch_id, product_id) DO UPDATE SET stock = stock + excluded.stock");

            $stmtAdj = $db->prepare("INSERT INTO stock_adjustments 
                (branch_id, product_id, type, qty_change, previous_stock, new_stock, reason, adjusted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($items as $it) {
                $pId = (int)$it['product_id'];
                $qty = (int)$it['qty'];
                $costPrice = (float)($it['cost_price'] ?? 0);
                if ($qty <= 0) continue;

                $stmtItem->execute([$transferId, $pId, $qty, $costPrice]);

                // 1. Kurangi dari cabang asal
                $stmtCheck->execute([$fromBranchId, $pId]);
                $prevFrom = (int)$stmtCheck->fetchColumn();
                $stmtDeduct->execute([$qty, $fromBranchId, $pId]);
                $stmtAdj->execute([$fromBranchId, $pId, 'keluar', $qty, $prevFrom, $prevFrom - $qty, "Transfer Keluar ($transferNo)", $_SESSION['user']['id']]);

                // 2. Tambah ke cabang tujuan
                $stmtCheck->execute([$toBranchId, $pId]);
                $prevTo = (int)$stmtCheck->fetchColumn();
                $stmtAdd->execute([$toBranchId, $pId, $qty]);
                $stmtAdj->execute([$toBranchId, $pId, 'masuk', $qty, $prevTo, $prevTo + $qty, "Transfer Masuk ($transferNo)", $_SESSION['user']['id']]);
            }

            $db->commit();

            auditLog('CREATE', 'transfers', "Transfer stok $transferNo dari cabang #$fromBranchId ke cabang #$toBranchId", [
                'transfer_no' => $transferNo,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'items_count' => count($items)
            ]);

            jsonResponse([
                'success' => true,
                'message' => "Transfer stok ($transferNo) berhasil diselesaikan.",
                'transfer_no' => $transferNo
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal memproses transfer stok: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
