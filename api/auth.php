<?php
/**
 * Authentication API (Login, Logout, Check Session)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Brute-force rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $lockKey = 'login_attempts_' . md5($ip . '_' . $username);
        $attempts = $_SESSION[$lockKey] ?? ['count' => 0, 'last_attempt' => time()];

        // Reset counter if more than 15 minutes passed
        if (time() - $attempts['last_attempt'] > 900) {
            $attempts = ['count' => 0, 'last_attempt' => time()];
        }

        if ($attempts['count'] >= 5) {
            $waitTime = 900 - (time() - $attempts['last_attempt']);
            $waitMinutes = ceil($waitTime / 60);
            jsonResponse([
                'success' => false,
                'message' => "Terlalu banyak percobaan login gagal. Akun dibekukan sementara demi keamanan. Silakan coba kembali dalam $waitMinutes menit."
            ], 429);
        }

        if (empty($username) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Username dan password wajib diisi.'], 400);
        }

        $stmt = $db->prepare("SELECT u.*, b.name as branch_name, b.code as branch_code 
                              FROM users u 
                              LEFT JOIN branches b ON u.branch_id = b.id 
                              WHERE LOWER(u.username) = LOWER(?) AND u.is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts['count']++;
            $attempts['last_attempt'] = time();
            $_SESSION[$lockKey] = $attempts;
            $remaining = max(0, 5 - $attempts['count']);
            $extraMsg = ($remaining > 0) ? " Sisa percobaan: $remaining kali." : " Akun dibekukan sementara selama 15 menit.";
            auditLog('LOGIN_FAILED', 'Autentikasi', "Percobaan login gagal untuk username '$username'", [
                'ip' => $ip,
                'remaining_attempts' => $remaining
            ], null, $username, 'guest');
            jsonResponse(['success' => false, 'message' => 'Username atau password salah.' . $extraMsg], 401);
        }

        // Cek apakah ada pemilihan cabang operasional spesifik
        $reqBranchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
        $finalBranchId = $user['branch_id'] ? (int)$user['branch_id'] : null;
        $finalBranchName = $user['branch_name'] ?? 'Pusat';
        $finalBranchCode = $user['branch_code'] ?? 'CBG-PST';

        // Superadmin & Akuntan dapat memilih cabang operasional yang dituju saat login
        if ($reqBranchId && in_array($user['role'], ['superadmin', 'akuntan'])) {
            $bStmt = $db->prepare("SELECT id, name, code FROM branches WHERE id = ? AND is_active = 1");
            $bStmt->execute([$reqBranchId]);
            $bData = $bStmt->fetch();
            if ($bData) {
                $finalBranchId = (int)$bData['id'];
                $finalBranchName = $bData['name'];
                $finalBranchCode = $bData['code'];
            }
        }

        // Login berhasil: reset counter
        unset($_SESSION[$lockKey]);

        // Simpan sesi
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
            'branch_id' => $finalBranchId,
            'branch_name' => $finalBranchName,
            'branch_code' => $finalBranchCode
        ];

        // Catat Audit Log Login
        auditLog('LOGIN', 'Autentikasi', "Pengguna {$user['name']} ({$user['username']}) berhasil masuk ke {$finalBranchName}", [
            'role' => $user['role'],
            'branch_id' => $finalBranchId,
            'branch' => $finalBranchName
        ], (int)$user['id'], $user['name'], $user['role']);

        // Tentukan redirect berdasarkan peran
        $redirectUrl = ($user['role'] === 'kasir') ? BASE_URL . '/views/pos.php' : BASE_URL . '/views/dashboard.php';

        jsonResponse([
            'success' => true,
            'message' => 'Login berhasil. Selamat datang, ' . $user['name'] . ' (' . $finalBranchName . ')',
            'user' => $_SESSION['user'],
            'redirect' => $redirectUrl
        ]);
        break;

    case 'switch_branch':
        requireAuth(['akuntan']);
        $newBranchId = (int)($_POST['branch_id'] ?? 0);
        $bStmt = $db->prepare("SELECT id, name, code FROM branches WHERE id = ? AND is_active = 1");
        $bStmt->execute([$newBranchId]);
        $br = $bStmt->fetch();
        if (!$br) {
            jsonResponse(['success' => false, 'message' => 'Cabang tidak ditemukan atau tidak aktif.'], 404);
        }
        $_SESSION['user']['branch_id'] = (int)$br['id'];
        $_SESSION['user']['branch_name'] = $br['name'];
        $_SESSION['user']['branch_code'] = $br['code'];

        auditLog('SWITCH_BRANCH', 'Autentikasi', "Pengguna {$_SESSION['user']['name']} beralih ke {$br['name']}", [
            'branch_id' => $br['id'],
            'branch_name' => $br['name']
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Berhasil beralih ke ' . $br['name'],
            'user' => $_SESSION['user']
        ]);
        break;

    case 'branches':
        $branches = $db->query("SELECT id, code, name, address, phone FROM branches WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'branches' => $branches]);
        break;

    case 'logout':
        $uName = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'User');
        $uId = $_SESSION['user']['id'] ?? null;
        $uRole = $_SESSION['user']['role'] ?? null;
        auditLog('LOGOUT', 'Autentikasi', "Pengguna {$uName} keluar dari sistem", null, $uId, $uName, $uRole);
        unset($_SESSION['user']);
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Berhasil logout.', 'redirect' => BASE_URL . '/views/login.php']);
        break;

    case 'session':
        if (isset($_SESSION['user'])) {
            jsonResponse(['success' => true, 'authenticated' => true, 'user' => $_SESSION['user']]);
        } else {
            jsonResponse(['success' => true, 'authenticated' => false, 'user' => null]);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
