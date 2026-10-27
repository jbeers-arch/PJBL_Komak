<?php
/**
 * General Journal API (Jurnal Umum - Double Entry)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $refType = isset($_GET['ref_type']) && $_GET['ref_type'] !== '' ? trim($_GET['ref_type']) : null;
        $sql = "SELECT j.*, b.name as branch_name, u.name as creator_name
                FROM journal_entries j
                LEFT JOIN branches b ON j.branch_id = b.id
                LEFT JOIN users u ON j.created_by = u.id
                WHERE j.date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];

        if ($branchId) {
            $sql .= " AND j.branch_id = ?";
            $params[] = $branchId;
        }

        if ($refType) {
            $sql .= " AND j.ref_type = ?";
            $params[] = $refType;
        }

        $search = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;
        if ($search) {
            $sql .= " AND (j.transaction_no LIKE ? OR j.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY j.date DESC, j.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        // Ambil item debit kredit untuk setiap entri jurnal
        $stmtItems = $db->prepare("SELECT ji.*, a.code as account_code, a.name as account_name, a.category 
                                   FROM journal_items ji
                                   JOIN accounts a ON ji.account_id = a.id
                                   WHERE ji.journal_entry_id = ?
                                   ORDER BY ji.debit DESC, ji.id ASC");

        foreach ($entries as &$entry) {
            $stmtItems->execute([$entry['id']]);
            $entry['items'] = $stmtItems->fetchAll();

            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($entry['items'] as $item) {
                $totalDebit += (float)$item['debit'];
                $totalCredit += (float)$item['credit'];
            }
            $entry['total_debit'] = $totalDebit;
            $entry['total_credit'] = $totalCredit;
            $entry['is_balanced'] = abs($totalDebit - $totalCredit) < 0.01;
        }

        jsonResponse(['success' => true, 'data' => $entries]);
        break;

    case 'create':
        $branchId = (int)($_POST['branch_id'] ?? 1);
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $description = trim($_POST['description'] ?? '');
        $items = json_decode($_POST['items'] ?? '[]', true);

        if (empty($description) || empty($items) || count($items) < 2) {
            jsonResponse(['success' => false, 'message' => 'Deskripsi dan minimal 2 baris akun (Debit & Kredit) wajib diisi.'], 400);
        }

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($items as $item) {
            $totalDebit += (float)($item['debit'] ?? 0);
            $totalCredit += (float)($item['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) >= 0.01 || $totalDebit <= 0) {
            jsonResponse(['success' => false, 'message' => 'Total Debit (' . number_format($totalDebit) . ') dan Kredit (' . number_format($totalCredit) . ') tidak seimbang (Balance Error).'], 400);
        }

        // Cek penguncian periode (Period Lock)
        $stmtLockCheck = $db->prepare("SELECT period_name, period_end FROM period_locks WHERE ? <= period_end AND status = 'locked' ORDER BY period_end DESC LIMIT 1");
        $stmtLockCheck->execute([$date]);
        $lockHit = $stmtLockCheck->fetch();
        if ($lockHit) {
            jsonResponse([
                'success' => false,
                'message' => "Tanggal jurnal ($date) berada dalam periode yang telah DITUTUP BUKU & DIKUNCI ('{$lockHit['period_name']}'). Pembukuan ke periode terkunci dilarang demi kepatuhan audit."
            ], 403);
        }

        try {
            $db->beginTransaction();

            $refType = trim($_POST['ref_type'] ?? 'manual');
            if (!in_array($refType, ['manual', 'adjustment', 'pos', 'invoice_scan'])) {
                $refType = 'manual';
            }
            $prefix = ($refType === 'adjustment') ? 'JRN-AJE-' : 'JRN-MAN-';
            $trxNo = $prefix . date('Ymd') . '-' . rand(1000, 9999);
            $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, created_by) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
            $stmtJrn->execute([$branchId, $trxNo, $date, $description, $refType, $_SESSION['user']['id']]);
            $journalId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $accId = (int)$item['account_id'];
                $debit = (float)($item['debit'] ?? 0);
                $credit = (float)($item['credit'] ?? 0);
                $memo = trim($item['memo'] ?? '');
                $stmtItem->execute([$journalId, $accId, $debit, $credit, $memo]);
            }

            $db->commit();

            auditLog('CREATE', ($refType === 'adjustment' ? 'Jurnal Penyesuaian' : 'Jurnal Umum'), "Posting jurnal baru #$trxNo: $description (Total: Rp " . number_format($totalDebit, 0, ',', '.') . ")", [
                'transaction_no' => $trxNo,
                'date' => $date,
                'ref_type' => $refType,
                'total' => $totalDebit,
                'items_count' => count($items)
            ]);

            jsonResponse(['success' => true, 'message' => 'Jurnal umum berhasil disimpan.', 'transaction_no' => $trxNo]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan jurnal: ' . $e->getMessage()], 500);
        }
        break;

    case 'reverse':
        // Jurnal Pembalik / Audit-compliant Reversal Entry
        $journalId = (int)($_POST['journal_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Koreksi Transaksi');

        if ($journalId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID Jurnal tidak valid.'], 400);
        }

        $stmtOrig = $db->prepare("SELECT * FROM journal_entries WHERE id = ?");
        $stmtOrig->execute([$journalId]);
        $orig = $stmtOrig->fetch();
        if (!$orig) {
            jsonResponse(['success' => false, 'message' => 'Entri jurnal asli tidak ditemukan.'], 404);
        }

        if ($orig['ref_type'] === 'reversal') {
            jsonResponse(['success' => false, 'message' => 'Jurnal pembalik tidak dapat dibalik kembali.'], 400);
        }

        // Cek apakah sudah pernah dibalik
        $stmtRevCheck = $db->prepare("SELECT id, transaction_no FROM journal_entries WHERE ref_type = 'reversal' AND ref_id = ?");
        $stmtRevCheck->execute([$journalId]);
        $existingRev = $stmtRevCheck->fetch();
        if ($existingRev) {
            jsonResponse(['success' => false, 'message' => "Jurnal ini sudah pernah dibatalkan melalui Jurnal Pembalik {$existingRev['transaction_no']}."], 400);
        }

        // Cek periode lock untuk tanggal hari ini
        $today = date('Y-m-d');
        $stmtLockCheck = $db->prepare("SELECT period_name, period_end FROM period_locks WHERE ? <= period_end AND status = 'locked' ORDER BY period_end DESC LIMIT 1");
        $stmtLockCheck->execute([$today]);
        if ($stmtLockCheck->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Periode hari ini terkunci. Tidak dapat membuat jurnal pembalik.'], 403);
        }

        // Ambil item jurnal asli
        $stmtItems = $db->prepare("SELECT * FROM journal_items WHERE journal_entry_id = ?");
        $stmtItems->execute([$journalId]);
        $items = $stmtItems->fetchAll();

        if (empty($items)) {
            jsonResponse(['success' => false, 'message' => 'Item jurnal tidak ditemukan.'], 400);
        }

        try {
            $db->beginTransaction();

            $trxNo = 'JRN-REV-' . date('Ymd') . '-' . rand(1000, 9999);
            $desc = "Jurnal Pembalik atas [{$orig['transaction_no']}]: " . $reason;
            
            $stmtNew = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, ref_id, created_by)
                                     VALUES (?, ?, ?, ?, 'reversal', ?, ?)");
            $stmtNew->execute([$orig['branch_id'], $trxNo, $today, $desc, $journalId, $_SESSION['user']['id']]);
            $newJournalId = $db->lastInsertId();

            // Tukar Debit dan Kredit
            $stmtInsertItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $it) {
                $stmtInsertItem->execute([
                    $newJournalId,
                    $it['account_id'],
                    $it['credit'], // Debit diisi saldo kredit asli
                    $it['debit'],  // Kredit diisi saldo debit asli
                    "Pembalik: " . ($it['memo'] ?: $orig['transaction_no'])
                ]);
            }

            $db->commit();

            auditLog('REVERSE', 'Jurnal Umum', "Membuat jurnal pembalik #$trxNo untuk membatalkan entri {$orig['transaction_no']} (Alasan: $reason)", [
                'reversal_no' => $trxNo,
                'original_no' => $orig['transaction_no'],
                'reason' => $reason
            ]);

            jsonResponse([
                'success' => true,
                'message' => "Jurnal pembalik ($trxNo) berhasil dibuat untuk membatalkan entri {$orig['transaction_no']}.",
                'transaction_no' => $trxNo
            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal membuat jurnal pembalik: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
