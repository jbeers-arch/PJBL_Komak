<?php
/**
 * Chart of Accounts (COA) API
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $stmt = $db->query("SELECT * FROM accounts WHERE is_active = 1 ORDER BY code ASC");
        $accounts = $stmt->fetchAll();

        // Mengelompokkan berdasarkan kategori
        $grouped = [
            'Aset' => [],
            'Liabilitas' => [],
            'Ekuitas' => [],
            'Pendapatan' => [],
            'Beban_Pokok' => [],
            'Beban_Operasional' => []
        ];

        foreach ($accounts as $acc) {
            if (isset($grouped[$acc['category']])) {
                $grouped[$acc['category']][] = $acc;
            }
        }

        jsonResponse([
            'success' => true,
            'data' => $accounts,
            'grouped' => $grouped
        ]);
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
        $stmt->execute([$id]);
        $acc = $stmt->fetch();
        if ($acc) {
            jsonResponse(['success' => true, 'data' => $acc]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Akun tidak ditemukan.'], 404);
        }
        break;

    case 'create':
        requireAuth(['akuntan']);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $normalBalance = trim($_POST['normal_balance'] ?? 'debit');

        if (empty($code) || empty($name) || empty($category)) {
            jsonResponse(['success' => false, 'message' => 'Kode, Nama Akun, dan Kategori wajib diisi.'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO accounts (code, name, category, normal_balance) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $name, $category, $normalBalance]);
            $newAccId = $db->lastInsertId();

            auditLog('CREATE', 'Bagan Akun', "Menambahkan akun baru '$code - $name' ($category, Saldo Normal: $normalBalance)", [
                'id' => $newAccId,
                'code' => $code,
                'name' => $name,
                'category' => $category,
                'normal_balance' => $normalBalance
            ]);

            jsonResponse(['success' => true, 'message' => 'Akun berhasil ditambahkan.']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Kode akun mungkin sudah digunakan: ' . $e->getMessage()], 400);
        }
        break;

    case 'update':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $normalBalance = trim($_POST['normal_balance'] ?? 'debit');

        if (!$id || empty($code) || empty($name) || empty($category)) {
            jsonResponse(['success' => false, 'message' => 'Data akun tidak lengkap.'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE accounts SET code = ?, name = ?, category = ?, normal_balance = ? WHERE id = ?");
            $stmt->execute([$code, $name, $category, $normalBalance, $id]);

            auditLog('UPDATE', 'Bagan Akun', "Memperbarui akun '$code - $name' ($category, Saldo Normal: $normalBalance)", [
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
                'normal_balance' => $normalBalance
            ]);

            jsonResponse(['success' => true, 'message' => 'Akun berhasil diperbarui.']);
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui akun: ' . $e->getMessage()], 400);
        }
        break;

    case 'delete':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        // Ambil info akun
        $accInfo = $db->query("SELECT code, name FROM accounts WHERE id = " . (int)$id)->fetch(PDO::FETCH_ASSOC);
        $accLabel = $accInfo ? "{$accInfo['code']} - {$accInfo['name']}" : "ID $id";

        // Cek apakah akun sudah pernah dipakai di jurnal
        $stmtUsed = $db->prepare("SELECT COUNT(*) FROM journal_items WHERE account_id = ?");
        $stmtUsed->execute([$id]);
        $usedCount = (int)$stmtUsed->fetchColumn();

        if ($usedCount > 0) {
            // Nonaktifkan saja agar integritas jurnal tidak rusak
            $stmtDeactivate = $db->prepare("UPDATE accounts SET is_active = 0 WHERE id = ?");
            $stmtDeactivate->execute([$id]);

            auditLog('DEACTIVATE', 'Bagan Akun', "Menonaktifkan / mengarsipkan akun '$accLabel' ($usedCount riwayat transaksi)", [
                'id' => $id,
                'account' => $accLabel,
                'used_in_transactions' => $usedCount
            ]);

            jsonResponse(['success' => true, 'message' => 'Akun telah memiliki riwayat transaksi, sehingga statusnya dinonaktifkan (diarsipkan).']);
        } else {
            $stmtDel = $db->prepare("DELETE FROM accounts WHERE id = ?");
            $stmtDel->execute([$id]);

            auditLog('DELETE', 'Bagan Akun', "Menghapus akun '$accLabel'", ['id' => $id, 'account' => $accLabel]);

            jsonResponse(['success' => true, 'message' => 'Akun berhasil dihapus.']);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
