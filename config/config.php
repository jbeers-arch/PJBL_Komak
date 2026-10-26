<?php
/**
 * Konfigurasi Utama Sistem Informasi Akuntansi & POS Multi-Cabang
 * Proyek: PJBL_Komak
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'PJBL Komak - SIA & POS Laundry Multi-Cabang');
define('APP_VERSION', '1.0.0');

// Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseDir = rtrim(explode('/views', explode('/api', $scriptDir)[0])[0], '/');
define('BASE_URL', $protocol . $host . $baseDir);

// Path definitions
define('ROOT_PATH', dirname(__DIR__));
define('DB_PATH', ROOT_PATH . '/database/pjbl_komak.sqlite');

// Gemini API Key (Bisa diisi via setting UI atau default)
define('DEFAULT_GEMINI_KEY', '');

// Helper JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Authentication guard helper
function requireAuth($allowedRoles = []) {
    $isApi = (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) ||
             (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
             (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if (!isset($_SESSION['user'])) {
        if ($isApi) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized. Silakan login terlebih dahulu.'], 401);
        } else {
            header('Location: ' . BASE_URL . '/views/login.php');
            exit;
        }
    }

    $userRole = $_SESSION['user']['role'] ?? '';
    // Superadmin memiliki hak akses ke seluruh fitur tanpa pengecualian
    if ($userRole === 'superadmin') {
        return;
    }

    if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
        if ($isApi) {
            jsonResponse(['success' => false, 'message' => 'Akses ditolak untuk peran Anda.'], 403);
        } else {
            header('Location: ' . BASE_URL . '/views/unauthorized.php');
            exit;
        }
    }
}

// Audit Log Helper
function auditLog($action, $module, $description, $details = null, $userId = null, $userName = null, $role = null) {
    try {
        if (!class_exists('Database')) {
            require_once __DIR__ . '/database.php';
        }
        $db = Database::getConnection();

        if ($userId === null && isset($_SESSION['user']['id'])) {
            $userId = (int)$_SESSION['user']['id'];
        } elseif ($userId !== null && !is_scalar($userId)) {
            $userId = is_numeric($userId) ? (int)$userId : null;
        }
        if ($userName === null) {
            $userName = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Tamu/Publik');
        } elseif (!is_scalar($userName)) {
            $userName = is_array($userName) ? ($userName['name'] ?? json_encode($userName)) : (string)$userName;
        }
        if ($role === null) {
            $role = $_SESSION['user']['role'] ?? 'guest';
        } elseif (!is_scalar($role)) {
            $role = is_array($role) ? ($role['role'] ?? json_encode($role)) : (string)$role;
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $detailsJson = null;
        if ($details !== null) {
            $detailsJson = is_string($details) ? $details : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, user_name, role, action, module, description, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))");
        $stmt->execute([
            $userId,
            $userName,
            $role,
            strtoupper(trim($action)),
            trim($module),
            trim($description),
            $detailsJson,
            $ip,
            $userAgent
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
        return false;
    }
}
