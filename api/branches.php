<?php
/**
 * Branch Management API (CRUD Cabang, Akun Kasir & Penugasan Kasir)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $showAll = isset($_GET['all']) && $_GET['all'] == '1';
        $sql = "SELECT b.*, 
                (SELECT COUNT(*) FROM users WHERE branch_id = b.id AND role = 'kasir' AND is_active = 1) as cashier_count,
                (SELECT COALESCE(SUM(total_amount), 0) FROM pos_transactions WHERE branch_id = b.id AND date(date) = date('now')) as today_sales,
                (SELECT GROUP_CONCAT(name, ', ') FROM users WHERE branch_id = b.id AND role = 'kasir' AND is_active = 1) as cashier_names
                FROM branches b ";
        if (!$showAll) {
            $sql .= "WHERE b.is_active = 1 ";
        }
        $sql .= "ORDER BY b.id ASC";

        $branches = $db->query($sql)->fetchAll();
        jsonResponse(['success' => true, 'data' => $branches]);
        break;

    case 'create':
        requireAuth(['akuntan']);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Pengaturan kasir otomatis
        $createCashier = !empty($_POST['create_cashier']);
        $cashierName = trim($_POST['cashier_name'] ?? '');
        $cashierUsername = trim($_POST['cashier_username'] ?? '');
        $cashierPassword = trim($_POST['cashier_password'] ?? '');

        if (empty($code) || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Kode dan Nama Cabang wajib diisi.'], 400);
        }

        // Cek duplikasi kode cabang
        $chkCode = $db->prepare("SELECT id, name FROM branches WHERE UPPER(code) = ?");
        $chkCode->execute([$code]);
        if ($existing = $chkCode->fetch()) {
            jsonResponse([
                'success' => false, 
                'message' => "Kode cabang '{$code}' sudah digunakan oleh {$existing['name']}. Silakan gunakan kode lain (misal: CBG-03, CBG-04)."
            ], 400);
        }

        try {
            $db->beginTransaction();

            // 1. Simpan Cabang Baru
            $stmt = $db->prepare("INSERT INTO branches (code, name, address, phone, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$code, $name, $address, $phone]);
            $branchId = (int)$db->lastInsertId();

            // 2. Buatkan Akun Kas Kasir di Bagan Akun (COA) dengan kode unik yang aman
            // Menggunakan prefiks 1101- agar kompatibel dengan query kas & bank
            $baseAccCode = '1101-' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
            $accountCode = $baseAccCode;
            $chkAcc = $db->prepare("SELECT id FROM accounts WHERE code = ?");
            $chkAcc->execute([$accountCode]);
            $seq = 1;
            while ($chkAcc->fetch()) {
                $accountCode = $baseAccCode . '-' . $seq;
                $chkAcc->execute([$accountCode]);
                $seq++;
            }

            $accountName = 'Kas Kasir ' . $name;
            $stmtAcc = $db->prepare("INSERT INTO accounts (code, name, category, normal_balance, is_active) VALUES (?, ?, 'Aset', 'debit', 1)");
            $stmtAcc->execute([$accountCode, $accountName]);
            $accountId = (int)$db->lastInsertId();

            // 3. Inisialisasi Stok Bahan & Produk untuk Cabang Baru di branch_stocks
            $products = $db->query("SELECT id FROM products")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($products)) {
                $stmtBS = $db->prepare("INSERT OR IGNORE INTO branch_stocks (branch_id, product_id, stock) VALUES (?, ?, 0)");
                foreach ($products as $pId) {
                    $stmtBS->execute([$branchId, (int)$pId]);
                }
            }

            // 4. Buatkan Akun Kasir (Opsional atau Otomatis)
            $createdCashier = null;
            if ($createCashier || !empty($cashierUsername) || !empty($cashierName)) {
                $cleanCode = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $code));
                $cName = !empty($cashierName) ? $cashierName : ('Kasir ' . $name);
                $cUser = !empty($cashierUsername) ? strtolower($cashierUsername) : ('kasir_' . $cleanCode);
                $cPass = !empty($cashierPassword) ? $cashierPassword : 'kasir123';

                // Pastikan username unik
                $chkU = $db->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
                $chkU->execute([$cUser]);
                if ($chkU->fetch()) {
                    $cUser .= '_' . $branchId;
                }

                $hashPass = password_hash($cPass, PASSWORD_DEFAULT);
                $stmtUser = $db->prepare("INSERT INTO users (username, password, name, role, branch_id, is_active) VALUES (?, ?, ?, 'kasir', ?, 1)");
                $stmtUser->execute([$cUser, $hashPass, $cName, $branchId]);
                $createdCashier = [
                    'id' => (int)$db->lastInsertId(),
                    'name' => $cName,
                    'username' => $cUser,
                    'password' => $cPass
                ];
            }

            $db->commit();

            auditLog('CREATE', 'branches', "Menambahkan cabang baru: $name ($code)", [
                'branch_id' => $branchId,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'account_code' => $accountCode,
                'cashier' => $createdCashier ? $createdCashier['username'] : null
            ]);

            jsonResponse([
                'success' => true,
                'message' => "Cabang '$name' ($code) berhasil didaftarkan.",
                'branch_id' => $branchId,
                'account' => [
                    'id' => $accountId,
                    'code' => $accountCode,
                    'name' => $accountName
                ],
                'cashier' => $createdCashier
            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal menambah cabang: ' . $e->getMessage()], 500);
        }
        break;

    case 'update':
        requireAuth(['akuntan']);
        $branchId = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if ($branchId <= 0 || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'ID dan Nama Cabang wajib diisi.'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE branches SET name = ?, address = ?, phone = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $isActive, $branchId]);

            // Update nama akun kas kasir terkait jika ada
            $accCodePrefix = '1101-' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
            $db->prepare("UPDATE accounts SET name = ? WHERE code LIKE ?")
               ->execute(['Kas Kasir ' . $name, $accCodePrefix . '%']);

            auditLog('UPDATE', 'branches', "Memperbarui cabang ID $branchId: $name", [
                'branch_id' => $branchId,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'is_active' => $isActive
            ]);

            jsonResponse(['success' => true, 'message' => 'Data cabang berhasil diperbarui.']);
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui cabang: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete':
        requireAuth(['akuntan']);
        $branchId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $force = !empty($_POST['force']) || !empty($_GET['force']);

        if ($branchId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID Cabang tidak valid.'], 400);
        }

        // Cabang utama (Pusat) tidak boleh dihapus
        if ($branchId === 1) {
            jsonResponse(['success' => false, 'message' => 'Kantor Pusat & Gudang adalah cabang utama dan tidak dapat dihapus.'], 400);
        }

        $branch = $db->query("SELECT * FROM branches WHERE id = $branchId")->fetch();
        if (!$branch) {
            jsonResponse(['success' => false, 'message' => 'Cabang tidak ditemukan atau sudah dihapus.'], 404);
        }

        // Cek data terkait
        $trxCount = (int)$db->query("SELECT COUNT(*) FROM pos_transactions WHERE branch_id = $branchId")->fetchColumn();
        $journalCount = (int)$db->query("SELECT COUNT(*) FROM journal_entries WHERE branch_id = $branchId")->fetchColumn();
        $cashierCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE branch_id = $branchId")->fetchColumn();

        // Jika memiliki transaksi dan belum dikonfirmasi force delete
        if (($trxCount > 0 || $journalCount > 0) && !$force) {
            jsonResponse([
                'success' => false,
                'has_data' => true,
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'branch_code' => $branch['code'],
                'trx_count' => $trxCount,
                'journal_count' => $journalCount,
                'cashier_count' => $cashierCount,
                'message' => "Cabang '{$branch['name']}' memiliki $trxCount transaksi penjualan & $journalCount jurnal tercatat."
            ]);
        }

        try {
            $db->beginTransaction();

            if ($force && ($trxCount > 0 || $journalCount > 0)) {
                // 1. Hapus pos_items dari transaksi cabang ini
                $db->prepare("DELETE FROM pos_items WHERE pos_transaction_id IN (SELECT id FROM pos_transactions WHERE branch_id = ?)")->execute([$branchId]);

                // 2. Hapus pos_transactions
                $db->prepare("DELETE FROM pos_transactions WHERE branch_id = ?")->execute([$branchId]);

                // 3. Hapus journal_items dari jurnal cabang ini
                $db->prepare("DELETE FROM journal_items WHERE journal_entry_id IN (SELECT id FROM journal_entries WHERE branch_id = ?)")->execute([$branchId]);

                // 4. Hapus journal_entries cabang ini
                $db->prepare("DELETE FROM journal_entries WHERE branch_id = ?")->execute([$branchId]);

                // 5. Hapus stock_transfers terkait
                $db->prepare("DELETE FROM stock_transfer_items WHERE transfer_id IN (SELECT id FROM stock_transfers WHERE from_branch_id = ? OR to_branch_id = ?)")->execute([$branchId, $branchId]);
                $db->prepare("DELETE FROM stock_transfers WHERE from_branch_id = ? OR to_branch_id = ?")->execute([$branchId, $branchId]);

                // 6. Hapus stock_adjustments terkait
                $db->prepare("DELETE FROM stock_adjustments WHERE branch_id = ?")->execute([$branchId]);
            }

            // 7. Hapus relasi stok di branch_stocks
            $db->prepare("DELETE FROM branch_stocks WHERE branch_id = ?")->execute([$branchId]);

            // 8. Hapus kasir khusus cabang ini atau lepaskan penugasan
            if ($force) {
                $db->prepare("DELETE FROM users WHERE branch_id = ? AND role = 'kasir'")->execute([$branchId]);
                $db->prepare("UPDATE users SET branch_id = NULL WHERE branch_id = ?")->execute([$branchId]);
            } else {
                $db->prepare("UPDATE users SET branch_id = NULL WHERE branch_id = ?")->execute([$branchId]);
            }

            // 9. Hapus akun kas kasir di accounts jika ada
            $accCodePrefix = '1101-' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
            $accList = $db->query("SELECT id FROM accounts WHERE code LIKE '$accCodePrefix%' OR (category = 'Aset' AND name LIKE '%Kas Kasir " . addslashes($branch['name']) . "%')")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($accList as $accId) {
                $db->prepare("DELETE FROM journal_items WHERE account_id = ?")->execute([$accId]);
                $db->prepare("DELETE FROM accounts WHERE id = ?")->execute([$accId]);
            }

            // Cabang legacy 2 & 3 jika force delete
            if ($force) {
                if ($branchId === 2) {
                    $acc2 = $db->query("SELECT id FROM accounts WHERE code = '1101' LIMIT 1")->fetchColumn();
                    if ($acc2) {
                        $db->prepare("DELETE FROM journal_items WHERE account_id = ?")->execute([$acc2]);
                        $db->prepare("DELETE FROM accounts WHERE id = ?")->execute([$acc2]);
                    }
                } elseif ($branchId === 3) {
                    $acc3 = $db->query("SELECT id FROM accounts WHERE code = '1102' LIMIT 1")->fetchColumn();
                    if ($acc3) {
                        $db->prepare("DELETE FROM journal_items WHERE account_id = ?")->execute([$acc3]);
                        $db->prepare("DELETE FROM accounts WHERE id = ?")->execute([$acc3]);
                    }
                }
            }

            // 10. Hapus cabang dari branches
            $db->prepare("DELETE FROM branches WHERE id = ?")->execute([$branchId]);

            $db->commit();

            auditLog('DELETE', 'branches', "Menghapus cabang: {$branch['name']} ({$branch['code']})", [
                'branch_id' => $branchId,
                'force' => $force,
                'deleted_trx' => $trxCount
            ]);

            jsonResponse(['success' => true, 'message' => "Cabang '{$branch['name']}' berhasil dihapus secara permanen."]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus cabang: ' . $e->getMessage()], 500);
        }
        break;

    case 'deactivate':
        requireAuth(['akuntan']);
        $branchId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($branchId <= 1) {
            jsonResponse(['success' => false, 'message' => 'Cabang ini tidak dapat dinonaktifkan.'], 400);
        }

        try {
            $db->prepare("UPDATE branches SET is_active = 0 WHERE id = ?")->execute([$branchId]);
            auditLog('DEACTIVATE', 'branches', "Menonaktifkan cabang ID $branchId");
            jsonResponse(['success' => true, 'message' => 'Cabang berhasil dinonaktifkan dari operasional.']);
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menonaktifkan cabang: ' . $e->getMessage()], 500);
        }
        break;

    case 'activate':
        requireAuth(['akuntan']);
        $branchId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($branchId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID Cabang tidak valid.'], 400);
        }

        try {
            $db->prepare("UPDATE branches SET is_active = 1 WHERE id = ?")->execute([$branchId]);
            auditLog('ACTIVATE', 'branches', "Mengaktifkan kembali cabang ID $branchId");
            jsonResponse(['success' => true, 'message' => 'Cabang berhasil diaktifkan kembali.']);
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal mengaktifkan cabang: ' . $e->getMessage()], 500);
        }
        break;

    case 'cashiers':
        $branchId = (int)($_GET['branch_id'] ?? 0);
        if ($branchId > 0) {
            $stmt = $db->prepare("SELECT u.id, u.username, u.name, u.role, u.is_active, u.created_at, u.branch_id, b.name as branch_name, b.code as branch_code
                                  FROM users u
                                  LEFT JOIN branches b ON u.branch_id = b.id
                                  WHERE u.branch_id = ? 
                                  ORDER BY u.role DESC, u.name ASC");
            $stmt->execute([$branchId]);
        } else {
            $stmt = $db->query("SELECT u.id, u.username, u.name, u.role, u.is_active, u.created_at, u.branch_id, b.name as branch_name, b.code as branch_code
                                FROM users u
                                LEFT JOIN branches b ON u.branch_id = b.id
                                ORDER BY u.branch_id ASC, u.role DESC, u.name ASC");
        }
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'data' => $users]);
        break;

    case 'add_cashier':
    case 'add_user':
        requireAuth(['akuntan']);
        $branchId = (int)($_POST['branch_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'kasir');
        if (!in_array($role, ['kasir', 'akuntan', 'superadmin'])) {
            $role = 'kasir';
        }

        if (empty($name) || empty($username) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Nama, username, dan password wajib diisi.'], 400);
        }

        // Tentukan cabang default jika belum ditentukan
        if ($branchId <= 0) {
            $branchId = ($role === 'kasir') ? 2 : 1;
        }

        // Cek duplikasi username
        $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => "Username '$username' sudah terdaftar. Silakan gunakan username lain."], 400);
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, name, role, branch_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$username, $hash, $name, $role, $branchId]);
            $newId = (int)$db->lastInsertId();

            auditLog('CREATE', 'users', "Menambahkan pengguna baru: $name ($username, Peran: $role) pada cabang ID $branchId", [
                'user_id' => $newId,
                'username' => $username,
                'name' => $name,
                'role' => $role,
                'branch_id' => $branchId
            ]);

            jsonResponse([
                'success' => true, 
                'message' => "Akses pengguna '$name' ($username) dengan peran " . strtoupper($role) . " berhasil ditambahkan.", 
                'user_id' => $newId
            ]);
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menambah akses pengguna: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete_user':
        requireAuth(['akuntan']);
        $userId = (int)($_POST['user_id'] ?? 0);
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        if ($userId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID Pengguna tidak valid.'], 400);
        }
        if ($userId === $currentUserId) {
            jsonResponse(['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.'], 400);
        }

        $stmt = $db->prepare("SELECT id, username, name, role, branch_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$targetUser) {
            jsonResponse(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        try {
            // Cek apakah user memiliki riwayat transaksi penjualan
            $salesCount = (int)$db->query("SELECT COUNT(*) FROM sales WHERE cashier_id = $userId")->fetchColumn();
            if ($salesCount > 0) {
                // Nonaktifkan saja jika memiliki transaksi penjualan agar integritas histori terjaga
                $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$userId]);
                auditLog('DEACTIVATE', 'users', "Menonaktifkan akses pengguna {$targetUser['username']} (memiliki $salesCount transaksi)", ['user_id' => $userId]);
                jsonResponse(['success' => true, 'message' => "Pengguna '{$targetUser['name']}' memiliki $salesCount riwayat transaksi penjualan. Akses login telah DINONAKTIFKAN."]);
            } else {
                $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
                auditLog('DELETE', 'users', "Menghapus akun pengguna {$targetUser['username']}", ['user_id' => $userId]);
                jsonResponse(['success' => true, 'message' => "Akun pengguna '{$targetUser['name']}' ($targetUser[username]) berhasil dihapus permanen."]);
            }
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
