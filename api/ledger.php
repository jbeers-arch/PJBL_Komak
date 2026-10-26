<?php
/**
 * General Ledger API (Buku Besar)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();

$accountId = (int)($_GET['account_id'] ?? 0);
$branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

if (!$accountId) {
    // Ambil akun pertama jika belum dipilih
    $firstAcc = $db->query("SELECT id FROM accounts WHERE is_active = 1 ORDER BY code ASC LIMIT 1")->fetch();
    $accountId = $firstAcc ? (int)$firstAcc['id'] : 1;
}

// 1. Ambil Info Akun
$stmtAcc = $db->prepare("SELECT * FROM accounts WHERE id = ?");
$stmtAcc->execute([$accountId]);
$account = $stmtAcc->fetch();

if (!$account) {
    jsonResponse(['success' => false, 'message' => 'Akun tidak ditemukan.'], 404);
}

// 2. Hitung Saldo Awal sebelum $startDate
$sqlInitial = "SELECT COALESCE(SUM(ji.debit), 0) as total_debit, COALESCE(SUM(ji.credit), 0) as total_credit
               FROM journal_items ji
               JOIN journal_entries j ON ji.journal_entry_id = j.id
               WHERE ji.account_id = ? AND j.date < ?";
$paramsInitial = [$accountId, $startDate];

if ($branchId) {
    $sqlInitial .= " AND j.branch_id = ?";
    $paramsInitial[] = $branchId;
}

$stmtInit = $db->prepare($sqlInitial);
$stmtInit->execute($paramsInitial);
$initTotals = $stmtInit->fetch();

$initialBalance = 0;
if ($account['normal_balance'] === 'debit') {
    $initialBalance = (float)$initTotals['total_debit'] - (float)$initTotals['total_credit'];
} else {
    $initialBalance = (float)$initTotals['total_credit'] - (float)$initTotals['total_debit'];
}

// 3. Ambil Mutasi Transaksi pada periode berjalan
$sqlMutasi = "SELECT ji.id, ji.debit, ji.credit, ji.memo,
                     j.date, j.transaction_no, j.description, j.ref_type,
                     b.name as branch_name
              FROM journal_items ji
              JOIN journal_entries j ON ji.journal_entry_id = j.id
              LEFT JOIN branches b ON j.branch_id = b.id
              WHERE ji.account_id = ? AND j.date BETWEEN ? AND ?";
$paramsMutasi = [$accountId, $startDate, $endDate];

if ($branchId) {
    $sqlMutasi .= " AND j.branch_id = ?";
    $paramsMutasi[] = $branchId;
}

$sqlMutasi .= " ORDER BY j.date ASC, j.id ASC, ji.id ASC";
$stmtMutasi = $db->prepare($sqlMutasi);
$stmtMutasi->execute($paramsMutasi);
$mutations = $stmtMutasi->fetchAll();

// 4. Hitung Saldo Berjalan (Running Balance)
$currentBalance = $initialBalance;
$totalDebitPeriod = 0;
$totalCreditPeriod = 0;

foreach ($mutations as &$m) {
    $d = (float)$m['debit'];
    $c = (float)$m['credit'];
    $totalDebitPeriod += $d;
    $totalCreditPeriod += $c;

    if ($account['normal_balance'] === 'debit') {
        $currentBalance += ($d - $c);
    } else {
        $currentBalance += ($c - $d);
    }
    $m['running_balance'] = $currentBalance;
}

// 5. Ambil daftar seluruh akun yang memiliki mutasi pada periode ini
$sqlActive = "SELECT a.id, a.code, a.name, a.category, COUNT(ji.id) as mutation_count
              FROM accounts a
              JOIN journal_items ji ON a.id = ji.account_id
              JOIN journal_entries j ON ji.journal_entry_id = j.id
              WHERE j.date BETWEEN ? AND ? " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
              GROUP BY a.id
              ORDER BY mutation_count DESC, a.code ASC";
$stmtActive = $db->prepare($sqlActive);
$stmtActive->execute([$startDate, $endDate]);
$activeAccounts = $stmtActive->fetchAll();

jsonResponse([
    'success' => true,
    'account' => $account,
    'period' => ['start_date' => $startDate, 'end_date' => $endDate],
    'initial_balance' => $initialBalance,
    'mutations' => $mutations,
    'total_debit' => $totalDebitPeriod,
    'total_credit' => $totalCreditPeriod,
    'ending_balance' => $currentBalance,
    'active_accounts' => $activeAccounts
]);
