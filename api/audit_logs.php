<?php
/**
 * Audit Logs API
 * Proyek: PJBL_Komak
 * Menyediakan riwayat jejak audit (siapa yang login & perubahan data apa yang dilakukan)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(200, max(10, (int)($_GET['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;

        $module = trim($_GET['module'] ?? '');
        $actionType = trim($_GET['action_type'] ?? '');
        $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
        $search = trim($_GET['search'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($module)) {
            $where[] = "module = ?";
            $params[] = $module;
        }

        if (!empty($actionType)) {
            $where[] = "action = ?";
            $params[] = $actionType;
        }

        if ($userId !== null) {
            $where[] = "user_id = ?";
            $params[] = $userId;
        }

        if (!empty($startDate)) {
            $where[] = "date(created_at) >= ?";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "date(created_at) <= ?";
            $params[] = $endDate;
        }

        if (!empty($search)) {
            $where[] = "(description LIKE ? OR user_name LIKE ? OR action LIKE ? OR module LIKE ? OR ip_address LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(" AND ", $where);

        // Count total
        $countStmt = $db->prepare("SELECT COUNT(*) FROM audit_logs WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch logs
        $stmt = $db->prepare("SELECT * FROM audit_logs WHERE $whereClause ORDER BY id DESC LIMIT ? OFFSET ?");
        $execParams = array_merge($params, [$limit, $offset]);
        $stmt->execute($execParams);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parse details JSON
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $decoded = json_decode($log['details'], true);
                $log['details_parsed'] = $decoded !== null ? $decoded : $log['details'];
            } else {
                $log['details_parsed'] = null;
            }
        }

        jsonResponse([
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        break;

    case 'stats':
        $today = date('Y-m-d');

        // Total log
        $totalLogs = (int)$db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

        // Login hari ini
        $loginsToday = (int)$db->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'LOGIN' AND date(created_at) = '$today'")->fetchColumn();

        // Login gagal hari ini
        $failedLoginsToday = (int)$db->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'LOGIN_FAILED' AND date(created_at) = '$today'")->fetchColumn();

        // Mutasi data hari ini
        $mutationsToday = (int)$db->query("SELECT COUNT(*) FROM audit_logs WHERE action NOT IN ('LOGIN', 'LOGOUT', 'LOGIN_FAILED') AND date(created_at) = '$today'")->fetchColumn();

        // Modul unik untuk filter dropdown
        $modules = $db->query("SELECT DISTINCT module FROM audit_logs WHERE module IS NOT NULL AND module != '' ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Aksi unik untuk filter dropdown
        $actions = $db->query("SELECT DISTINCT action FROM audit_logs WHERE action IS NOT NULL AND action != '' ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Daftar pengguna yang pernah tercatat di audit log
        $users = $db->query("SELECT DISTINCT user_id, user_name, role FROM audit_logs WHERE user_name IS NOT NULL ORDER BY user_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse([
            'success' => true,
            'stats' => [
                'total_logs' => $totalLogs,
                'logins_today' => $loginsToday,
                'failed_logins_today' => $failedLoginsToday,
                'mutations_today' => $mutationsToday
            ],
            'modules' => $modules,
            'actions' => $actions,
            'users' => $users
        ]);
        break;

    case 'export':
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');
        $module = trim($_GET['module'] ?? '');
        $actionType = trim($_GET['action_type'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($module)) {
            $where[] = "module = ?";
            $params[] = $module;
        }
        if (!empty($actionType)) {
            $where[] = "action = ?";
            $params[] = $actionType;
        }
        if (!empty($startDate)) {
            $where[] = "date(created_at) >= ?";
            $params[] = $startDate;
        }
        if (!empty($endDate)) {
            $where[] = "date(created_at) <= ?";
            $params[] = $endDate;
        }

        $whereClause = implode(" AND ", $where);
        $stmt = $db->prepare("SELECT id, created_at, user_name, role, module, action, description, ip_address, user_agent, details 
                              FROM audit_logs 
                              WHERE $whereClause 
                              ORDER BY id DESC LIMIT 5000");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_log_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        // Add BOM for UTF-8 Excel support
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Waktu (WIB)', 'Pengguna', 'Peran', 'Modul', 'Aksi', 'Deskripsi', 'IP Address', 'User Agent', 'Rincian Perubahan']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['created_at'],
                $r['user_name'] ?? 'Sistem/Anonim',
                $r['role'] ?? '-',
                $r['module'] ?? '-',
                $r['action'],
                $r['description'],
                $r['ip_address'] ?? '-',
                $r['user_agent'] ?? '-',
                $r['details'] ?? ''
            ]);
        }
        fclose($out);
        exit;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
