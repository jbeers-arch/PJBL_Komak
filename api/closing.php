<?php
/**
 * Closing Entries & Accounting Period Management API (Tutup Buku & Neraca Saldo Penutupan)
 * Standar SAK-EMKM
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

switch ($action) {
    case 'status':
        // Cek riwayat penguncian periode (Tutup Buku)
        $locks = $db->query("SELECT p.*, u.name as closed_by_name, j.transaction_no as closing_journal_no
                             FROM period_locks p
                             LEFT JOIN users u ON p.closed_by = u.id
                             LEFT JOIN journal_entries j ON p.closing_journal_id = j.id
                             ORDER BY p.period_end DESC")->fetchAll(PDO::FETCH_ASSOC);

        $latestLock = !empty($locks) ? $locks[0] : null;

        // Tanggal mulai yang disarankan untuk periode baru berikutnya
        $suggestedStart = date('Y-01-01');
        if ($latestLock) {
            $suggestedStart = date('Y-m-d', strtotime($latestLock['period_end'] . ' +1 day'));
        }
        $suggestedEnd = date('Y-m-d');

        // Ringkasan transaksi aktif yang belum ditutup
        $sqlUnclosed = "SELECT 
            COUNT(DISTINCT j.id) as unclosed_journals,
            COALESCE(SUM(CASE WHEN a.category = 'Pendapatan' THEN ji.credit - ji.debit ELSE 0 END), 0) as current_revenue,
            COALESCE(SUM(CASE WHEN a.category IN ('Beban_Pokok', 'Beban_Operasional') THEN ji.debit - ji.credit ELSE 0 END), 0) as current_expense
            FROM journal_entries j
            JOIN journal_items ji ON j.id = ji.journal_entry_id
            JOIN accounts a ON ji.account_id = a.id
            WHERE j.ref_type != 'closing' " . ($latestLock ? "AND j.date > '{$latestLock['period_end']}'" : "");
        
        $unclosedStats = $db->query($sqlUnclosed)->fetch(PDO::FETCH_ASSOC);

        jsonResponse([
            'success' => true,
            'data' => [
                'latest_lock' => $latestLock,
                'suggested_start' => $suggestedStart,
                'suggested_end' => $suggestedEnd,
                'unclosed_journals' => (int)($unclosedStats['unclosed_journals'] ?? 0),
                'current_revenue' => (float)($unclosedStats['current_revenue'] ?? 0),
                'current_expense' => (float)($unclosedStats['current_expense'] ?? 0),
                'current_profit' => (float)($unclosedStats['current_revenue'] ?? 0) - (float)($unclosedStats['current_expense'] ?? 0),
                'history' => $locks
            ]
        ]);
        break;

    case 'preview':
        // Menghitung draf Jurnal Penutup untuk periode tertentu
        $periodStart = $_GET['period_start'] ?? date('Y-01-01');
        $periodEnd = $_GET['period_end'] ?? date('Y-m-d');
        $targetEquityCode = $_GET['target_equity'] ?? '3201'; // Default: 3201 Laba Ditahan, atau 3101 Modal

        // Pastikan target equity valid
        $stmtTarget = $db->prepare("SELECT id, code, name FROM accounts WHERE code = ? LIMIT 1");
        $stmtTarget->execute([$targetEquityCode]);
        $targetEquity = $stmtTarget->fetch(PDO::FETCH_ASSOC);
        if (!$targetEquity) {
            $stmtTarget->execute(['3201']);
            $targetEquity = $stmtTarget->fetch(PDO::FETCH_ASSOC);
        }

        // Ambil akun Ikhtisar Laba Rugi (3301)
        $stmtSummary = $db->prepare("SELECT id, code, name FROM accounts WHERE code = '3301' LIMIT 1");
        $stmtSummary->execute();
        $summaryAcc = $stmtSummary->fetch(PDO::FETCH_ASSOC);

        // Ambil akun Modal Pemilik (3101)
        $stmtModal = $db->prepare("SELECT id, code, name FROM accounts WHERE code = '3101' LIMIT 1");
        $stmtModal->execute();
        $modalAcc = $stmtModal->fetch(PDO::FETCH_ASSOC);

        // 1. Ambil saldo akun Pendapatan (Kredit - Debit)
        $sqlRevenues = "SELECT a.id, a.code, a.name, 
                        COALESCE(SUM(ji.credit) - SUM(ji.debit), 0) as balance
                        FROM accounts a
                        JOIN journal_items ji ON a.id = ji.account_id
                        JOIN journal_entries j ON ji.journal_entry_id = j.id
                        WHERE a.category = 'Pendapatan' AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                        GROUP BY a.id
                        HAVING balance != 0";
        $stmtRev = $db->prepare($sqlRevenues);
        $stmtRev->execute([$periodStart, $periodEnd]);
        $revenueAccounts = $stmtRev->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ambil saldo akun Beban (Debit - Kredit)
        $sqlExpenses = "SELECT a.id, a.code, a.name, a.category,
                        COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance
                        FROM accounts a
                        JOIN journal_items ji ON a.id = ji.account_id
                        JOIN journal_entries j ON ji.journal_entry_id = j.id
                        WHERE a.category IN ('Beban_Pokok', 'Beban_Operasional') AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                        GROUP BY a.id
                        HAVING balance != 0";
        $stmtExp = $db->prepare($sqlExpenses);
        $stmtExp->execute([$periodStart, $periodEnd]);
        $expenseAccounts = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ambil saldo Prive Pemilik (Debit - Kredit)
        $sqlPrive = "SELECT a.id, a.code, a.name,
                     COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance
                     FROM accounts a
                     JOIN journal_items ji ON a.id = ji.account_id
                     JOIN journal_entries j ON ji.journal_entry_id = j.id
                     WHERE a.code = '3102' AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                     GROUP BY a.id
                     HAVING balance != 0";
        $stmtPrive = $db->prepare($sqlPrive);
        $stmtPrive->execute([$periodStart, $periodEnd]);
        $priveAccount = $stmtPrive->fetch(PDO::FETCH_ASSOC);
        $priveAmount = $priveAccount ? (float)$priveAccount['balance'] : 0;

        // Susun Jurnal Penutup (Closing Entries)
        $closingItems = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        // Tahap A: Menutup Akun Pendapatan ke Ikhtisar Laba Rugi
        // Pendapatan ditutup di DEBIT, Ikhtisar Laba Rugi di KREDIT
        foreach ($revenueAccounts as $rev) {
            $amount = (float)$rev['balance'];
            if ($amount > 0) {
                $closingItems[] = [
                    'step' => 'A. Penutupan Pendapatan',
                    'account_id' => $rev['id'],
                    'code' => $rev['code'],
                    'name' => $rev['name'],
                    'debit' => $amount,
                    'credit' => 0,
                    'memo' => 'Menutup akun pendapatan ke Ikhtisar Laba Rugi'
                ];
                $totalRevenue += $amount;
            }
        }
        if ($totalRevenue > 0) {
            $closingItems[] = [
                'step' => 'A. Penutupan Pendapatan',
                'account_id' => $summaryAcc['id'],
                'code' => $summaryAcc['code'],
                'name' => $summaryAcc['name'],
                'debit' => 0,
                'credit' => $totalRevenue,
                'memo' => 'Kredit Ikhtisar Laba Rugi dari pendapatan'
            ];
        }

        // Tahap B: Menutup Akun Beban ke Ikhtisar Laba Rugi
        // Ikhtisar Laba Rugi di DEBIT, Akun Beban di KREDIT
        foreach ($expenseAccounts as $exp) {
            $amount = (float)$exp['balance'];
            if ($amount > 0) {
                $totalExpense += $amount;
            }
        }
        if ($totalExpense > 0) {
            $closingItems[] = [
                'step' => 'B. Penutupan Beban',
                'account_id' => $summaryAcc['id'],
                'code' => $summaryAcc['code'],
                'name' => $summaryAcc['name'],
                'debit' => $totalExpense,
                'credit' => 0,
                'memo' => 'Debit Ikhtisar Laba Rugi dari total beban'
            ];
            foreach ($expenseAccounts as $exp) {
                $amount = (float)$exp['balance'];
                if ($amount > 0) {
                    $closingItems[] = [
                        'step' => 'B. Penutupan Beban',
                        'account_id' => $exp['id'],
                        'code' => $exp['code'],
                        'name' => $exp['name'],
                        'debit' => 0,
                        'credit' => $amount,
                        'memo' => 'Menutup akun beban ke Ikhtisar Laba Rugi'
                    ];
                }
            }
        }

        // Tahap C: Menutup Saldo Ikhtisar Laba Rugi ke Ekuitas (Laba Ditahan / Modal)
        $netProfit = $totalRevenue - $totalExpense;
        if ($netProfit > 0) {
            // Laba Bersih: Debit Ikhtisar Laba Rugi, Kredit Laba Ditahan/Modal
            $closingItems[] = [
                'step' => 'C. Penutupan Laba Bersih',
                'account_id' => $summaryAcc['id'],
                'code' => $summaryAcc['code'],
                'name' => $summaryAcc['name'],
                'debit' => $netProfit,
                'credit' => 0,
                'memo' => 'Menutup saldo laba bersih dari Ikhtisar Laba Rugi'
            ];
            $closingItems[] = [
                'step' => 'C. Penutupan Laba Bersih',
                'account_id' => $targetEquity['id'],
                'code' => $targetEquity['code'],
                'name' => $targetEquity['name'],
                'debit' => 0,
                'credit' => $netProfit,
                'memo' => 'Pemindahan laba bersih periode ke ekuitas'
            ];
        } elseif ($netProfit < 0) {
            // Rugi Bersih: Debit Laba Ditahan/Modal, Kredit Ikhtisar Laba Rugi
            $lossAmount = abs($netProfit);
            $closingItems[] = [
                'step' => 'C. Penutupan Rugi Bersih',
                'account_id' => $targetEquity['id'],
                'code' => $targetEquity['code'],
                'name' => $targetEquity['name'],
                'debit' => $lossAmount,
                'credit' => 0,
                'memo' => 'Pembebanan rugi bersih periode ke ekuitas'
            ];
            $closingItems[] = [
                'step' => 'C. Penutupan Rugi Bersih',
                'account_id' => $summaryAcc['id'],
                'code' => $summaryAcc['code'],
                'name' => $summaryAcc['name'],
                'debit' => 0,
                'credit' => $lossAmount,
                'memo' => 'Menutup saldo rugi dari Ikhtisar Laba Rugi'
            ];
        }

        // Tahap D: Menutup Prive Pemilik ke Modal Pemilik
        if ($priveAmount > 0 && $priveAccount && $modalAcc) {
            $closingItems[] = [
                'step' => 'D. Penutupan Prive',
                'account_id' => $modalAcc['id'],
                'code' => $modalAcc['code'],
                'name' => $modalAcc['name'],
                'debit' => $priveAmount,
                'credit' => 0,
                'memo' => 'Pengurangan modal pemilik atas prive'
            ];
            $closingItems[] = [
                'step' => 'D. Penutupan Prive',
                'account_id' => $priveAccount['id'],
                'code' => $priveAccount['code'],
                'name' => $priveAccount['name'],
                'debit' => 0,
                'credit' => $priveAmount,
                'memo' => 'Menutup akun prive pemilik ke modal'
            ];
        }

        $sumDebit = array_sum(array_column($closingItems, 'debit'));
        $sumCredit = array_sum(array_column($closingItems, 'credit'));

        jsonResponse([
            'success' => true,
            'period' => ['start' => $periodStart, 'end' => $periodEnd],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_profit_loss' => $netProfit,
                'prive_amount' => $priveAmount,
                'target_equity' => $targetEquity
            ],
            'closing_entries' => $closingItems,
            'total_debit' => $sumDebit,
            'total_credit' => $sumCredit,
            'is_balanced' => abs($sumDebit - $sumCredit) < 0.01,
            'can_close' => count($closingItems) > 0
        ]);
        break;

    case 'execute':
        // Eksekusi Tutup Buku: Simpan Jurnal Penutup & Kunci Periode
        $periodName = trim($_POST['period_name'] ?? '');
        $periodStart = trim($_POST['period_start'] ?? '');
        $periodEnd = trim($_POST['period_end'] ?? '');
        $targetEquityCode = trim($_POST['target_equity'] ?? '3201');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($periodName) || empty($periodStart) || empty($periodEnd)) {
            jsonResponse(['success' => false, 'message' => 'Nama periode, tanggal mulai, dan tanggal selesai wajib diisi.'], 400);
        }

        // Cek apakah tanggal periode sudah pernah dikunci
        $stmtCheck = $db->prepare("SELECT id, period_name FROM period_locks WHERE period_end >= ? AND status = 'locked' LIMIT 1");
        $stmtCheck->execute([$periodEnd]);
        $existingLock = $stmtCheck->fetch();
        if ($existingLock) {
            jsonResponse([
                'success' => false, 
                'message' => "Periode per tanggal $periodEnd sudah pernah ditutup dan dikunci dalam '{$existingLock['period_name']}'."
            ], 400);
        }

        // Jalankan preview secara internal untuk mendapatkan item jurnal penutup
        $_GET['period_start'] = $periodStart;
        $_GET['period_end'] = $periodEnd;
        $_GET['target_equity'] = $targetEquityCode;

        // Ambil data jurnal penutup
        $stmtSummary = $db->prepare("SELECT id, code, name FROM accounts WHERE code = '3301' LIMIT 1");
        $stmtSummary->execute();
        $summaryAcc = $stmtSummary->fetch(PDO::FETCH_ASSOC);

        $stmtTarget = $db->prepare("SELECT id, code, name FROM accounts WHERE code = ? LIMIT 1");
        $stmtTarget->execute([$targetEquityCode]);
        $targetEquity = $stmtTarget->fetch(PDO::FETCH_ASSOC);
        if (!$targetEquity) {
            $stmtTarget->execute(['3201']);
            $targetEquity = $stmtTarget->fetch(PDO::FETCH_ASSOC);
        }

        $stmtModal = $db->prepare("SELECT id, code, name FROM accounts WHERE code = '3101' LIMIT 1");
        $stmtModal->execute();
        $modalAcc = $stmtModal->fetch(PDO::FETCH_ASSOC);

        // Revenue
        $stmtRev = $db->prepare("SELECT a.id, a.code, a.name, COALESCE(SUM(ji.credit) - SUM(ji.debit), 0) as balance
                                 FROM accounts a
                                 JOIN journal_items ji ON a.id = ji.account_id
                                 JOIN journal_entries j ON ji.journal_entry_id = j.id
                                 WHERE a.category = 'Pendapatan' AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                                 GROUP BY a.id HAVING balance != 0");
        $stmtRev->execute([$periodStart, $periodEnd]);
        $revs = $stmtRev->fetchAll(PDO::FETCH_ASSOC);

        // Expense
        $stmtExp = $db->prepare("SELECT a.id, a.code, a.name, COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance
                                 FROM accounts a
                                 JOIN journal_items ji ON a.id = ji.account_id
                                 JOIN journal_entries j ON ji.journal_entry_id = j.id
                                 WHERE a.category IN ('Beban_Pokok', 'Beban_Operasional') AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                                 GROUP BY a.id HAVING balance != 0");
        $stmtExp->execute([$periodStart, $periodEnd]);
        $exps = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

        // Prive
        $stmtPrive = $db->prepare("SELECT a.id, a.code, a.name, COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance
                                   FROM accounts a
                                   JOIN journal_items ji ON a.id = ji.account_id
                                   JOIN journal_entries j ON ji.journal_entry_id = j.id
                                   WHERE a.code = '3102' AND j.date BETWEEN ? AND ? AND j.ref_type != 'closing'
                                   GROUP BY a.id HAVING balance != 0");
        $stmtPrive->execute([$periodStart, $periodEnd]);
        $priveRow = $stmtPrive->fetch(PDO::FETCH_ASSOC);
        $priveAmount = $priveRow ? (float)$priveRow['balance'] : 0;

        $itemsToInsert = [];
        $totalRev = 0;
        $totalExp = 0;

        // A. Revenues
        foreach ($revs as $r) {
            $amt = (float)$r['balance'];
            if ($amt > 0) {
                $itemsToInsert[] = ['account_id' => $r['id'], 'debit' => $amt, 'credit' => 0, 'memo' => 'Tutup Pendapatan: ' . $r['name']];
                $totalRev += $amt;
            }
        }
        if ($totalRev > 0) {
            $itemsToInsert[] = ['account_id' => $summaryAcc['id'], 'debit' => 0, 'credit' => $totalRev, 'memo' => 'Kredit Ikhtisar Laba Rugi dari pendapatan'];
        }

        // B. Expenses
        foreach ($exps as $e) {
            $amt = (float)$e['balance'];
            if ($amt > 0) {
                $totalExp += $amt;
            }
        }
        if ($totalExp > 0) {
            $itemsToInsert[] = ['account_id' => $summaryAcc['id'], 'debit' => $totalExp, 'credit' => 0, 'memo' => 'Debit Ikhtisar Laba Rugi dari beban'];
            foreach ($exps as $e) {
                $amt = (float)$e['balance'];
                if ($amt > 0) {
                    $itemsToInsert[] = ['account_id' => $e['id'], 'debit' => 0, 'credit' => $amt, 'memo' => 'Tutup Beban: ' . $e['name']];
                }
            }
        }

        // C. Net Profit
        $netProfit = $totalRev - $totalExp;
        if ($netProfit > 0) {
            $itemsToInsert[] = ['account_id' => $summaryAcc['id'], 'debit' => $netProfit, 'credit' => 0, 'memo' => 'Tutup saldo laba bersih ke ' . $targetEquity['name']];
            $itemsToInsert[] = ['account_id' => $targetEquity['id'], 'debit' => 0, 'credit' => $netProfit, 'memo' => 'Penambahan ekuitas dari laba bersih periode'];
        } elseif ($netProfit < 0) {
            $loss = abs($netProfit);
            $itemsToInsert[] = ['account_id' => $targetEquity['id'], 'debit' => $loss, 'credit' => 0, 'memo' => 'Pengurangan ekuitas atas rugi bersih periode'];
            $itemsToInsert[] = ['account_id' => $summaryAcc['id'], 'debit' => 0, 'credit' => $loss, 'memo' => 'Tutup saldo rugi bersih ke ' . $targetEquity['name']];
        }

        // D. Prive
        if ($priveAmount > 0 && $priveRow && $modalAcc) {
            $itemsToInsert[] = ['account_id' => $modalAcc['id'], 'debit' => $priveAmount, 'credit' => 0, 'memo' => 'Tutup Prive Pemilik ke Modal'];
            $itemsToInsert[] = ['account_id' => $priveRow['id'], 'debit' => 0, 'credit' => $priveAmount, 'memo' => 'Pengurangan akun prive pemilik'];
        }

        if (empty($itemsToInsert)) {
            jsonResponse(['success' => false, 'message' => 'Tidak ada akun pendapatan atau beban yang perlu ditutup untuk periode ini.'], 400);
        }

        try {
            $db->beginTransaction();

            $trxNo = 'JRN-CLS-' . date('Ymd', strtotime($periodEnd)) . '-' . rand(1000, 9999);
            $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, created_by)
                                     VALUES (1, ?, ?, ?, 'closing', ?)");
            $stmtJrn->execute([
                $trxNo, 
                $periodEnd, 
                "Jurnal Penutup (Closing Entries) - $periodName", 
                $_SESSION['user']['id']
            ]);
            $journalId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
            foreach ($itemsToInsert as $it) {
                $stmtItem->execute([$journalId, $it['account_id'], $it['debit'], $it['credit'], $it['memo']]);
            }

            // Simpan record penguncian periode
            $stmtLock = $db->prepare("INSERT INTO period_locks 
                (period_name, period_start, period_end, closed_at, closed_by, total_revenue, total_expense, net_profit_loss, prive_amount, closing_journal_id, status, notes)
                VALUES (?, ?, ?, datetime('now'), ?, ?, ?, ?, ?, ?, 'locked', ?)");
            $stmtLock->execute([
                $periodName,
                $periodStart,
                $periodEnd,
                $_SESSION['user']['id'],
                $totalRev,
                $totalExp,
                $netProfit,
                $priveAmount,
                $journalId,
                $notes
            ]);
            $lockId = $db->lastInsertId();

            $db->commit();

            auditLog('CLOSING_PERIOD', 'Tutup Buku', "Eksekusi Tutup Buku '$periodName' ($periodStart s/d $periodEnd) - Jurnal Penutup #$trxNo, Laba Bersih: Rp " . number_format($netProfit, 0, ',', '.'), [
                'lock_id' => $lockId,
                'journal_id' => $journalId,
                'transaction_no' => $trxNo,
                'net_profit' => $netProfit,
                'total_revenue' => $totalRev,
                'total_expense' => $totalExp
            ]);

            jsonResponse([
                'success' => true,
                'message' => "Proses Tutup Buku '$periodName' berhasil diselesaikan. Periode telah dikunci dan Jurnal Penutup ($trxNo) berhasil diposting.",
                'data' => [
                    'lock_id' => $lockId,
                    'journal_id' => $journalId,
                    'transaction_no' => $trxNo,
                    'net_profit_loss' => $netProfit
                ]
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal melakukan tutup buku: ' . $e->getMessage()], 500);
        }
        break;

    case 'post_closing_trial_balance':
        // Neraca Saldo Setelah Penutupan (Post-Closing Trial Balance)
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
        $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

        // Ambil semua akun riil (Aset, Liabilitas, Ekuitas) dan akun nominal (untuk membuktikan saldo nominal = 0)
        $sql = "SELECT a.id, a.code, a.name, a.category, a.normal_balance,
                       COALESCE(SUM(ji.debit), 0) as total_debit,
                       COALESCE(SUM(ji.credit), 0) as total_credit
                FROM accounts a
                LEFT JOIN journal_items ji ON a.id = ji.account_id
                LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= ? " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                GROUP BY a.id
                ORDER BY a.code ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$asOfDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $realAccounts = [];
        $nominalAccounts = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($rows as $r) {
            $cat = $r['category'];
            $debitRaw = (float)$r['total_debit'];
            $creditRaw = (float)$r['total_credit'];

            if (in_array($cat, ['Aset', 'Liabilitas', 'Ekuitas'])) {
                // Hitung saldo debit / kredit berdasarkan saldo normal
                $debitVal = 0;
                $creditVal = 0;

                if ($r['normal_balance'] === 'debit') {
                    $net = $debitRaw - $creditRaw;
                    if ($net >= 0) {
                        $debitVal = $net;
                    } else {
                        $creditVal = abs($net);
                    }
                } else {
                    $net = $creditRaw - $debitRaw;
                    if ($net >= 0) {
                        $creditVal = $net;
                    } else {
                        $debitVal = abs($net);
                    }
                }

                // Jangan tampilkan jika saldo 0 (misalnya prive yang sudah ditutup)
                if ($debitVal > 0 || $creditVal > 0) {
                    $realAccounts[] = [
                        'code' => $r['code'],
                        'name' => $r['name'],
                        'category' => $cat,
                        'debit' => $debitVal,
                        'credit' => $creditVal
                    ];
                    $totalDebit += $debitVal;
                    $totalCredit += $creditVal;
                }
            } else {
                // Akun nominal (Pendapatan & Beban)
                $net = ($cat === 'Pendapatan') ? ($creditRaw - $debitRaw) : ($debitRaw - $creditRaw);
                $nominalAccounts[] = [
                    'code' => $r['code'],
                    'name' => $r['name'],
                    'category' => $cat,
                    'remaining_balance' => $net,
                    'is_zero' => abs($net) < 0.01
                ];
            }
        }

        $allNominalZero = true;
        foreach ($nominalAccounts as $na) {
            if (!$na['is_zero']) {
                $allNominalZero = false;
                break;
            }
        }

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        jsonResponse([
            'success' => true,
            'as_of_date' => $asOfDate,
            'real_accounts' => $realAccounts,
            'nominal_accounts_proof' => $nominalAccounts,
            'all_nominal_zero' => $allNominalZero,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => $isBalanced,
            'diff' => $totalDebit - $totalCredit
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
