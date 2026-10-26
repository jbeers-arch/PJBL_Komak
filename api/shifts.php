<?php
/**
 * Cashier Shifts & Cash Drawer Management API
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'current';
$branchId = (int)($_SESSION['user']['branch_id'] ?? 1);
$cashierId = (int)$_SESSION['user']['id'];

switch ($action) {
    case 'current':
        // Cek shift yang sedang aktif untuk kasir & cabang ini
        $stmt = $db->prepare("SELECT s.*, u.name as cashier_name, b.name as branch_name 
                              FROM cashier_shifts s
                              JOIN users u ON s.cashier_id = u.id
                              JOIN branches b ON s.branch_id = b.id
                              WHERE s.branch_id = ? AND s.cashier_id = ? AND s.status = 'open' 
                              ORDER BY s.id DESC LIMIT 1");
        $stmt->execute([$branchId, $cashierId]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonResponse([
            'success' => true,
            'has_active_shift' => (bool)$shift,
            'shift' => $shift
        ]);
        break;

    case 'open':
        // Cek apakah masih ada shift yang belum ditutup
        $stmtCheck = $db->prepare("SELECT id FROM cashier_shifts WHERE branch_id = ? AND cashier_id = ? AND status = 'open' LIMIT 1");
        $stmtCheck->execute([$branchId, $cashierId]);
        if ($stmtCheck->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Anda masih memiliki shift aktif yang belum ditutup.'], 400);
        }

        $initialCash = (float)($_POST['initial_cash'] ?? 0);
        $notes = trim($_POST['notes'] ?? 'Shift kasir reguler');
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $stmt = $db->prepare("INSERT INTO cashier_shifts 
            (branch_id, cashier_id, shift_date, start_time, initial_cash, expected_cash, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, 'open', ?)");
        $stmt->execute([$branchId, $cashierId, $today, $now, $initialCash, $initialCash, $notes]);
        $shiftId = $db->lastInsertId();

        auditLog('OPEN_SHIFT', 'shifts', "Membuka shift kasir dengan modal awal Rp " . number_format($initialCash, 0, ',', '.'), [
            'shift_id' => $shiftId,
            'branch_id' => $branchId,
            'initial_cash' => $initialCash
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($initialCash, 0, ',', '.'),
            'shift_id' => $shiftId
        ]);
        break;

    case 'close':
        $shiftId = (int)($_POST['shift_id'] ?? 0);
        $actualCash = (float)($_POST['actual_cash'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        $stmt = $db->prepare("SELECT * FROM cashier_shifts WHERE id = ? AND status = 'open'");
        $stmt->execute([$shiftId]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shift) {
            jsonResponse(['success' => false, 'message' => 'Shift tidak ditemukan atau sudah ditutup sebelumnya.'], 404);
        }

        $expectedCash = (float)$shift['expected_cash'];
        $cashDiff = $actualCash - $expectedCash;
        $now = date('Y-m-d H:i:s');

        $fullNotes = trim(($shift['notes'] ? $shift['notes'] . " | " : "") . $notes);

        $stmtUpd = $db->prepare("UPDATE cashier_shifts SET 
            end_time = ?,
            actual_cash = ?,
            cash_difference = ?,
            status = 'closed',
            notes = ?
            WHERE id = ?");
        $stmtUpd->execute([$now, $actualCash, $cashDiff, $fullNotes, $shiftId]);

        auditLog('CLOSE_SHIFT', 'shifts', "Menutup shift kasir #$shiftId (Fisik: Rp " . number_format($actualCash, 0, ',', '.') . ", Selisih: Rp " . number_format($cashDiff, 0, ',', '.') . ")", [
            'shift_id' => $shiftId,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'cash_difference' => $cashDiff
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Shift kasir berhasil ditutup.',
            'summary' => [
                'initial_cash' => (float)$shift['initial_cash'],
                'total_cash_sales' => (float)$shift['total_cash_sales'],
                'total_non_cash_sales' => (float)$shift['total_non_cash_sales'],
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_difference' => $cashDiff
            ]
        ]);
        break;

    case 'history':
        $filterBranch = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
        $sql = "SELECT s.*, u.name as cashier_name, b.name as branch_name 
                FROM cashier_shifts s
                JOIN users u ON s.cashier_id = u.id
                JOIN branches b ON s.branch_id = b.id
                WHERE 1=1";
        $params = [];
        if ($filterBranch) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $filterBranch;
        }
        $sql .= " ORDER BY s.id DESC LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse(['success' => true, 'data' => $history]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
