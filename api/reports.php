<?php
/**
 * Financial Reports API (Laba Rugi, Neraca, Arus Kas, Perubahan Ekuitas, Dashboard Summary & Charts)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$type = $_GET['type'] ?? 'dashboard';
$branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

switch ($type) {
    case 'dashboard':
        // 1. Total Penjualan Hari Ini
        $sqlTodaySales = "SELECT COALESCE(SUM(total_amount), 0) as total FROM pos_transactions WHERE date(date) = date('now')";
        if ($branchId) $sqlTodaySales .= " AND branch_id = $branchId";
        $todaySales = (float)$db->query($sqlTodaySales)->fetch()['total'];

        // 2. Total Penjualan Bulan Ini
        $sqlMonthSales = "SELECT COALESCE(SUM(total_amount), 0) as total FROM pos_transactions WHERE strftime('%Y-%m', date) = strftime('%Y-%m', 'now')";
        if ($branchId) $sqlMonthSales .= " AND branch_id = $branchId";
        $monthSales = (float)$db->query($sqlMonthSales)->fetch()['total'];

        // 3. Total Kas & Bank
        $cashAccounts = $db->query("SELECT a.id, a.code, a.name, 
                                    COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance
                                    FROM accounts a
                                    LEFT JOIN journal_items ji ON a.id = ji.account_id
                                    WHERE a.category = 'Aset' AND (a.code LIKE '1101%' OR a.code LIKE '1102%' OR a.code LIKE '1103%')
                                    GROUP BY a.id")->fetchAll();
        $totalCash = 0;
        foreach ($cashAccounts as $ca) {
            $totalCash += (float)$ca['balance'];
        }

        // 4. Performa Penjualan Tiap Cabang
        $branchPerformances = $db->query("SELECT b.id, b.code, b.name,
                                          COALESCE(SUM(p.total_amount), 0) as total_sales,
                                          COUNT(p.id) as trx_count
                                          FROM branches b
                                          LEFT JOIN pos_transactions p ON b.id = p.branch_id AND strftime('%Y-%m', p.date) = strftime('%Y-%m', 'now')
                                          WHERE b.is_active = 1
                                          GROUP BY b.id")->fetchAll();

        // 5. Faktur Masuk Menunggu Approval
        $pendingScans = (int)$db->query("SELECT COUNT(*) as cnt FROM invoice_scans WHERE status = 'draft'")->fetch()['cnt'];

        // 6. Data Chart Tren 7 Hari Terakhir
        $chartDates = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStr = date('Y-m-d', strtotime("-$i days"));
            $chartDates[] = date('d M', strtotime($dayStr));
            $sqlDay = "SELECT COALESCE(SUM(total_amount), 0) as total FROM pos_transactions WHERE date(date) = '$dayStr'";
            if ($branchId) $sqlDay .= " AND branch_id = $branchId";
            $chartSales[] = (float)$db->query($sqlDay)->fetchColumn();
        }

        // 7. Data Chart Distribusi Cabang
        $pieLabels = [];
        $pieValues = [];
        foreach ($branchPerformances as $bp) {
            $pieLabels[] = $bp['name'];
            $pieValues[] = (float)$bp['total_sales'];
        }

        jsonResponse([
            'success' => true,
            'data' => [
                'today_sales' => $todaySales,
                'month_sales' => $monthSales,
                'total_cash' => $totalCash,
                'cash_accounts' => $cashAccounts,
                'branch_performances' => $branchPerformances,
                'pending_scans' => $pendingScans,
                'chart_trend' => [
                    'labels' => $chartDates,
                    'sales' => $chartSales
                ],
                'chart_branches' => [
                    'labels' => $pieLabels,
                    'values' => $pieValues
                ]
            ]
        ]);
        break;

    case 'income_statement':
        // Laporan Laba Rugi
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $sql = "SELECT a.id, a.code, a.name, a.category, a.normal_balance,
                       COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit ELSE 0 END), 0) as total_debit,
                       COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.credit ELSE 0 END), 0) as total_credit
                FROM accounts a
                LEFT JOIN journal_items ji ON a.id = ji.account_id
                LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date BETWEEN ? AND ? AND (j.ref_type IS NULL OR j.ref_type != 'closing') " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                WHERE a.category IN ('Pendapatan', 'Beban_Pokok', 'Beban_Operasional')
                GROUP BY a.id
                ORDER BY a.code ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll();

        $revenues = [];
        $totalRevenue = 0;

        $cogs = [];
        $totalCogs = 0;

        $expenses = [];
        $totalExpenses = 0;

        foreach ($rows as $r) {
            $cat = $r['category'];
            $debit = (float)$r['total_debit'];
            $credit = (float)$r['total_credit'];

            if ($cat === 'Pendapatan') {
                $amount = $credit - $debit;
                $revenues[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalRevenue += $amount;
            } elseif ($cat === 'Beban_Pokok') {
                $amount = $debit - $credit;
                $cogs[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalCogs += $amount;
            } elseif ($cat === 'Beban_Operasional') {
                $amount = $debit - $credit;
                $expenses[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalExpenses += $amount;
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalExpenses;

        jsonResponse([
            'success' => true,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit
        ]);
        break;

    case 'balance_sheet':
        // Laporan Neraca (Posisi Keuangan)
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');

        $sql = "SELECT a.id, a.code, a.name, a.category, a.normal_balance,
                       COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit ELSE 0 END), 0) as total_debit,
                       COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.credit ELSE 0 END), 0) as total_credit
                FROM accounts a
                LEFT JOIN journal_items ji ON a.id = ji.account_id
                LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= ? " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                GROUP BY a.id
                ORDER BY a.code ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$asOfDate]);
        $rows = $stmt->fetchAll();

        $assets = [];
        $totalAssets = 0;

        $liabilities = [];
        $totalLiabilities = 0;

        $equities = [];
        $totalEquities = 0;

        $currentEarnings = 0;

        foreach ($rows as $r) {
            $cat = $r['category'];
            $debit = (float)$r['total_debit'];
            $credit = (float)$r['total_credit'];

            if ($cat === 'Aset') {
                // Untuk akun kontra aset seperti Akumulasi Penyusutan (normal_balance = credit)
                if ($r['normal_balance'] === 'credit') {
                    $amount = -($credit - $debit);
                } else {
                    $amount = $debit - $credit;
                }
                $assets[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalAssets += $amount;
            } elseif ($cat === 'Liabilitas') {
                $amount = $credit - $debit;
                $liabilities[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalLiabilities += $amount;
            } elseif ($cat === 'Ekuitas') {
                // Untuk akun kontra ekuitas seperti Prive Pemilik (normal_balance = debit)
                if ($r['normal_balance'] === 'debit') {
                    $amount = -($debit - $credit);
                } else {
                    $amount = $credit - $debit;
                }
                $equities[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amount];
                $totalEquities += $amount;
            } elseif ($cat === 'Pendapatan') {
                $currentEarnings += ($credit - $debit);
            } elseif ($cat === 'Beban_Pokok' || $cat === 'Beban_Operasional') {
                $currentEarnings -= ($debit - $credit);
            }
        }

        // Tambahkan Laba Berjalan ke Ekuitas
        $equities[] = [
            'code' => '3301',
            'name' => 'Laba Periode Berjalan',
            'amount' => $currentEarnings
        ];
        $totalEquities += $currentEarnings;

        $totalPassiva = $totalLiabilities + $totalEquities;
        $isBalanced = abs($totalAssets - $totalPassiva) < 0.01;

        jsonResponse([
            'success' => true,
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equities' => $equities,
            'total_equities' => $totalEquities,
            'total_passiva' => $totalPassiva,
            'is_balanced' => $isBalanced,
            'diff' => $totalAssets - $totalPassiva
        ]);
        break;

    case 'cash_flow':
        // Laporan Arus Kas (Cash Flow Statement - Standar SAK EMKM Direct Method)
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Ambil seluruh ID akun Kas & Bank
        $cashAccounts = $db->query("SELECT id, code, name FROM accounts WHERE category = 'Aset' AND (code LIKE '1101%' OR code LIKE '1102%' OR code LIKE '1103%')")->fetchAll();
        $cashAccIds = array_column($cashAccounts, 'id');
        if (empty($cashAccIds)) {
            $cashAccIds = [1, 2, 3];
        }
        $cashPlaceholders = implode(',', array_fill(0, count($cashAccIds), '?'));

        // 1. Saldo Kas Awal sebelum $startDate
        $sqlInitCash = "SELECT COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) as balance 
                        FROM journal_items ji 
                        JOIN journal_entries j ON ji.journal_entry_id = j.id
                        WHERE j.date < ? AND ji.account_id IN ($cashPlaceholders)";
        $paramsInit = array_merge([$startDate], $cashAccIds);
        if ($branchId) {
            $sqlInitCash .= " AND j.branch_id = $branchId";
        }
        $stmtInit = $db->prepare($sqlInitCash);
        $stmtInit->execute($paramsInit);
        $beginningCash = (float)$stmtInit->fetchColumn();

        // 2. Ambil seluruh transaksi jurnal periode berjalan yang menyentuh kas
        $sqlTrx = "SELECT j.id as journal_id, j.date, j.transaction_no, j.description, j.ref_type,
                          ji.account_id as cash_acc_id, ji.debit as cash_debit, ji.credit as cash_credit
                   FROM journal_items ji
                   JOIN journal_entries j ON ji.journal_entry_id = j.id
                   WHERE j.date BETWEEN ? AND ? AND ji.account_id IN ($cashPlaceholders)";
        if ($branchId) {
            $sqlTrx .= " AND j.branch_id = $branchId";
        }
        $sqlTrx .= " ORDER BY j.date ASC, j.id ASC";

        $stmtTrx = $db->prepare($sqlTrx);
        $stmtTrx->execute(array_merge([$startDate, $endDate], $cashAccIds));
        $cashMovements = $stmtTrx->fetchAll();

        // Kategori Arus Kas Standar SAK EMKM (Metode Langsung)
        $operatingInflows = [
            'sales' => ['name' => 'Penerimaan Kas dari Pelanggan (Penjualan Kasir POS & Jasa)', 'amount' => 0],
            'other_revenue' => ['name' => 'Penerimaan Kas Operasional Lain-lain', 'amount' => 0]
        ];

        $operatingOutflows = [
            'suppliers' => ['name' => 'Pembayaran Kas kepada Pemasok & Persediaan Bahan Cuci', 'amount' => 0],
            'salaries' => ['name' => 'Pembayaran Beban Gaji Karyawan & Kasir', 'amount' => 0],
            'utilities' => ['name' => 'Pembayaran Beban Utilitas (Listrik, Air & Gas Boiler)', 'amount' => 0],
            'rent' => ['name' => 'Pembayaran Beban Sewa Tempat Usaha', 'amount' => 0],
            'operations' => ['name' => 'Pembayaran Beban Operasional & Pemeliharaan Lainnya', 'amount' => 0]
        ];

        $investingInflows = [
            'asset_sales' => ['name' => 'Penerimaan dari Pelepasan / Penjualan Aset Tetap', 'amount' => 0]
        ];

        $investingOutflows = [
            'equipment' => ['name' => 'Perolehan Peralatan Toko & Mesin Kasir / Dryer', 'amount' => 0]
        ];

        $financingInflows = [
            'capital' => ['name' => 'Penerimaan Setoran Modal Pemilik', 'amount' => 0],
            'loans' => ['name' => 'Penerimaan Pinjaman Bank / Kewajiban Jangka Panjang', 'amount' => 0]
        ];

        $financingOutflows = [
            'drawings' => ['name' => 'Pengambilan Prive Pemilik', 'amount' => 0],
            'loan_repayments' => ['name' => 'Pembayaran Pokok Pinjaman Bank', 'amount' => 0]
        ];

        $stmtCounter = $db->prepare("
            SELECT ji.account_id, ji.debit, ji.credit, a.code, a.name, a.category 
            FROM journal_items ji
            JOIN accounts a ON ji.account_id = a.id
            WHERE ji.journal_entry_id = ? AND ji.id != ?
        ");

        foreach ($cashMovements as $cm) {
            $jId = $cm['journal_id'];
            $isDebit = $cm['cash_debit'] > 0;
            $cashAmount = $isDebit ? (float)$cm['cash_debit'] : (float)$cm['cash_credit'];

            // Ambil akun lawan dari transaksi jurnal
            $stmtCounter->execute([$jId, $cm['cash_acc_id']]);
            $counters = $stmtCounter->fetchAll();

            if ($isDebit) {
                // UANG MASUK (KAS BERTAMBAH)
                $allocated = false;
                foreach ($counters as $c) {
                    $code = $c['code'];
                    $cat = $c['category'];
                    if ($cat === 'Pendapatan' || $code === '1104' || $cm['ref_type'] === 'pos') {
                        $operatingInflows['sales']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($code === '3101' || $code === '3103') {
                        $financingInflows['capital']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif (str_starts_with($code, '22')) {
                        $financingInflows['loans']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif (str_starts_with($code, '12')) {
                        $investingInflows['asset_sales']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    }
                }
                if (!$allocated) {
                    $operatingInflows['other_revenue']['amount'] += $cashAmount;
                }
            } else {
                // UANG KELUAR (KAS BERKURANG)
                $allocated = false;
                foreach ($counters as $c) {
                    $code = $c['code'];
                    $cat = $c['category'];
                    if (str_starts_with($code, '12')) {
                        $investingOutflows['equipment']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($code === '3102') {
                        $financingOutflows['drawings']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif (str_starts_with($code, '22')) {
                        $financingOutflows['loan_repayments']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($code === '6101') {
                        $operatingOutflows['salaries']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($code === '6102') {
                        $operatingOutflows['utilities']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($code === '6103') {
                        $operatingOutflows['rent']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    } elseif ($cat === 'Beban_Pokok' || $code === '2101' || $code === '1105' || $cm['ref_type'] === 'invoice_scan') {
                        $operatingOutflows['suppliers']['amount'] += $cashAmount;
                        $allocated = true;
                        break;
                    }
                }
                if (!$allocated) {
                    $operatingOutflows['operations']['amount'] += $cashAmount;
                }
            }
        }

        $totOpIn = array_sum(array_column($operatingInflows, 'amount'));
        $totOpOut = array_sum(array_column($operatingOutflows, 'amount'));
        $netOperating = $totOpIn - $totOpOut;

        $totInvIn = array_sum(array_column($investingInflows, 'amount'));
        $totInvOut = array_sum(array_column($investingOutflows, 'amount'));
        $netInvesting = $totInvIn - $totInvOut;

        $totFinIn = array_sum(array_column($financingInflows, 'amount'));
        $totFinOut = array_sum(array_column($financingOutflows, 'amount'));
        $netFinancing = $totFinIn - $totFinOut;

        $netChange = $netOperating + $netInvesting + $netFinancing;
        $endingCash = $beginningCash + $netChange;

        // Hitung Saldo Kas di Neraca pada akhir periode untuk garansi rekonsiliasi
        $sqlNeraca = "SELECT COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) FROM journal_items ji
                      JOIN journal_entries j ON ji.journal_entry_id = j.id
                      WHERE j.date <= ? AND ji.account_id IN ($cashPlaceholders)";
        if ($branchId) {
            $sqlNeraca .= " AND j.branch_id = $branchId";
        }
        $stmtNeraca = $db->prepare($sqlNeraca);
        $stmtNeraca->execute(array_merge([$endDate], $cashAccIds));
        $balanceSheetCash = (float)$stmtNeraca->fetchColumn();

        $isReconciled = abs($endingCash - $balanceSheetCash) < 0.01;

        jsonResponse([
            'success' => true,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'operating' => [
                'inflows' => $operatingInflows,
                'outflows' => $operatingOutflows,
                'total_inflows' => $totOpIn,
                'total_outflows' => $totOpOut,
                'cash_from_sales' => $operatingInflows['sales']['amount'],
                'cash_from_other' => $operatingInflows['other_revenue']['amount'],
                'cash_for_suppliers' => $operatingOutflows['suppliers']['amount'],
                'cash_for_salaries' => $operatingOutflows['salaries']['amount'],
                'cash_for_utilities' => $operatingOutflows['utilities']['amount'],
                'cash_for_rent' => $operatingOutflows['rent']['amount'],
                'cash_for_operations' => $operatingOutflows['operations']['amount'],
                'net_operating' => $netOperating
            ],
            'investing' => [
                'inflows' => $investingInflows,
                'outflows' => $investingOutflows,
                'total_inflows' => $totInvIn,
                'total_outflows' => $totInvOut,
                'cash_from_assets' => $investingInflows['asset_sales']['amount'],
                'cash_for_equipment' => $investingOutflows['equipment']['amount'],
                'net_investing' => $netInvesting
            ],
            'financing' => [
                'inflows' => $financingInflows,
                'outflows' => $financingOutflows,
                'total_inflows' => $totFinIn,
                'total_outflows' => $totFinOut,
                'cash_from_capital' => $financingInflows['capital']['amount'],
                'cash_from_loans' => $financingInflows['loans']['amount'],
                'cash_for_drawings' => $financingOutflows['drawings']['amount'],
                'cash_for_loans' => $financingOutflows['loan_repayments']['amount'],
                'net_financing' => $netFinancing
            ],
            'net_change_in_cash' => $netChange,
            'beginning_cash' => $beginningCash,
            'ending_cash' => $endingCash,
            'balance_sheet_cash' => $balanceSheetCash,
            'is_reconciled' => $isReconciled,
            'diff' => $endingCash - $balanceSheetCash
        ]);
        break;

    case 'equity_changes':
        // Laporan Perubahan Ekuitas
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Modal Awal sebelum startDate
        $sqlInitEq = "SELECT COALESCE(SUM(ji.credit) - SUM(ji.debit), 0) as balance 
                      FROM journal_items ji 
                      JOIN journal_entries j ON ji.journal_entry_id = j.id
                      WHERE j.date < ? AND ji.account_id IN (SELECT id FROM accounts WHERE category = 'Ekuitas' AND normal_balance = 'credit') AND (j.ref_type IS NULL OR j.ref_type != 'closing')";
        $stmtInitEq = $db->prepare($sqlInitEq);
        $stmtInitEq->execute([$startDate]);
        $beginningCapital = (float)$stmtInitEq->fetchColumn();

        // Tambahan Modal selama periode
        $sqlAddCap = "SELECT COALESCE(SUM(ji.credit) - SUM(ji.debit), 0) as balance 
                      FROM journal_items ji 
                      JOIN journal_entries j ON ji.journal_entry_id = j.id
                      WHERE j.date BETWEEN ? AND ? AND ji.account_id IN (SELECT id FROM accounts WHERE code = '3101') AND (j.ref_type IS NULL OR j.ref_type != 'closing')";
        $stmtAddCap = $db->prepare($sqlAddCap);
        $stmtAddCap->execute([$startDate, $endDate]);
        $additionalCapital = max(0, (float)$stmtAddCap->fetchColumn());

        // Laba Bersih Periode ini (Pendapatan - HPP - Beban) - kecualikan jurnal penutup
        $sqlNetProfit = "SELECT 
            COALESCE(SUM(CASE WHEN a.category = 'Pendapatan' THEN ji.credit - ji.debit ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN a.category IN ('Beban_Pokok', 'Beban_Operasional') THEN ji.debit - ji.credit ELSE 0 END), 0) as net_profit
            FROM journal_items ji
            JOIN journal_entries j ON ji.journal_entry_id = j.id
            JOIN accounts a ON ji.account_id = a.id
            WHERE j.date BETWEEN ? AND ? AND (j.ref_type IS NULL OR j.ref_type != 'closing') " . ($branchId ? "AND j.branch_id = $branchId" : "");
        $stmtNP = $db->prepare($sqlNetProfit);
        $stmtNP->execute([$startDate, $endDate]);
        $netIncome = (float)$stmtNP->fetchColumn();

        // Prive / Pengambilan Pribadi
        $sqlPrive = "SELECT COALESCE(SUM(ji.debit) - SUM(ji.credit), 0) 
                     FROM journal_items ji 
                     JOIN journal_entries j ON ji.journal_entry_id = j.id
                     WHERE j.date BETWEEN ? AND ? AND ji.account_id IN (SELECT id FROM accounts WHERE code = '3102') AND (j.ref_type IS NULL OR j.ref_type != 'closing')";
        $stmtPrive = $db->prepare($sqlPrive);
        $stmtPrive->execute([$startDate, $endDate]);
        $drawings = (float)$stmtPrive->fetchColumn();

        $endingCapital = $beginningCapital + $additionalCapital + $netIncome - $drawings;

        jsonResponse([
            'success' => true,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'beginning_capital' => $beginningCapital,
            'additional_capital' => $additionalCapital,
            'net_income' => $netIncome,
            'drawings' => $drawings,
            'ending_capital' => $endingCapital
        ]);
        break;

    case 'post_closing_trial_balance':
        // Neraca Saldo Setelah Penutupan (Post-Closing Trial Balance)
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');

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

    case 'financial_ratios':
        // Analisis Rasio Keuangan
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
        $startYear = date('Y-01-01', strtotime($asOfDate));

        // 1. Ambil data Neraca
        $sqlBS = "SELECT a.category, a.code, a.normal_balance,
                         COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit ELSE 0 END), 0) as total_debit,
                         COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.credit ELSE 0 END), 0) as total_credit
                  FROM accounts a
                  LEFT JOIN journal_items ji ON a.id = ji.account_id
                  LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= ? " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                  GROUP BY a.id";
        $stmtBS = $db->prepare($sqlBS);
        $stmtBS->execute([$asOfDate]);
        $rowsBS = $stmtBS->fetchAll();

        $cash = 0;
        $receivables = 0;
        $inventory = 0;
        $supplies = 0;
        $currentAssets = 0;
        $fixedAssets = 0;
        $totalAssets = 0;

        $currentLiabilities = 0;
        $totalLiabilities = 0;

        $totalEquity = 0;
        $currentEarnings = 0;

        foreach ($rowsBS as $r) {
            $cat = $r['category'];
            $code = $r['code'];
            $debit = (float)$r['total_debit'];
            $credit = (float)$r['total_credit'];
            $bal = ($r['normal_balance'] === 'debit') ? ($debit - $credit) : ($credit - $debit);

            if ($cat === 'Aset') {
                if ($r['normal_balance'] === 'credit') {
                    $bal = -($credit - $debit); // Akumulasi
                }
                $totalAssets += $bal;
                if (str_starts_with($code, '11')) {
                    $currentAssets += $bal;
                    if (str_starts_with($code, '1101') || str_starts_with($code, '1102') || str_starts_with($code, '1103')) $cash += $bal;
                    elseif (str_starts_with($code, '1104')) $receivables += $bal;
                    elseif (str_starts_with($code, '1105')) $inventory += $bal;
                    elseif (str_starts_with($code, '1106')) $supplies += $bal;
                } else {
                    $fixedAssets += $bal;
                }
            } elseif ($cat === 'Liabilitas') {
                $totalLiabilities += $bal;
                $currentLiabilities += $bal;
            } elseif ($cat === 'Ekuitas') {
                $totalEquity += $bal;
            } elseif ($cat === 'Pendapatan') {
                $currentEarnings += ($credit - $debit);
            } elseif ($cat === 'Beban_Pokok' || $cat === 'Beban_Operasional') {
                $currentEarnings -= ($debit - $credit);
            }
        }
        $totalEquity += $currentEarnings;

        // 2. Ambil data Laba Rugi Year-To-Date
        $sqlPL = "SELECT a.category,
                         COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit ELSE 0 END), 0) as total_debit,
                         COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.credit ELSE 0 END), 0) as total_credit
                  FROM accounts a
                  LEFT JOIN journal_items ji ON a.id = ji.account_id
                  LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date BETWEEN ? AND ? AND (j.ref_type IS NULL OR j.ref_type != 'closing') " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                  WHERE a.category IN ('Pendapatan', 'Beban_Pokok', 'Beban_Operasional')
                  GROUP BY a.id";
        $stmtPL = $db->prepare($sqlPL);
        $stmtPL->execute([$startYear, $asOfDate]);
        $rowsPL = $stmtPL->fetchAll();

        $revenue = 0;
        $cogs = 0;
        $expenses = 0;
        foreach ($rowsPL as $r) {
            $cat = $r['category'];
            $d = (float)$r['total_debit'];
            $c = (float)$r['total_credit'];
            if ($cat === 'Pendapatan') $revenue += ($c - $d);
            elseif ($cat === 'Beban_Pokok') $cogs += ($d - $c);
            elseif ($cat === 'Beban_Operasional') $expenses += ($d - $c);
        }
        $netProfit = $revenue - $cogs - $expenses;

        // Hitung Rasio
        $currentRatio = $currentLiabilities > 0 ? ($currentAssets / $currentLiabilities) : 0;
        $quickRatio = $currentLiabilities > 0 ? (($cash + $receivables) / $currentLiabilities) : 0;
        $cashRatio = $currentLiabilities > 0 ? ($cash / $currentLiabilities) : 0;
        $npm = $revenue > 0 ? (($netProfit / $revenue) * 100) : 0;
        $roa = $totalAssets > 0 ? (($netProfit / $totalAssets) * 100) : 0;
        $roe = $totalEquity > 0 ? (($netProfit / $totalEquity) * 100) : 0;
        $der = $totalEquity > 0 ? (($totalLiabilities / $totalEquity) * 100) : 0;

        jsonResponse([
            'success' => true,
            'as_of_date' => $asOfDate,
            'metrics' => [
                'cash' => $cash,
                'receivables' => $receivables,
                'inventory' => $inventory,
                'current_assets' => $currentAssets,
                'fixed_assets' => $fixedAssets,
                'total_assets' => $totalAssets,
                'current_liabilities' => $currentLiabilities,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'revenue_ytd' => $revenue,
                'net_profit_ytd' => $netProfit
            ],
            'ratios' => [
                'current_ratio' => round($currentRatio, 2),
                'quick_ratio' => round($quickRatio, 2),
                'cash_ratio' => round($cashRatio, 2),
                'net_profit_margin' => round($npm, 2),
                'return_on_assets' => round($roa, 2),
                'return_on_equity' => round($roe, 2),
                'debt_to_equity' => round($der, 2)
            ]
        ]);
        break;

    case 'trial_balance':
        // Neraca Saldo (Trial Balance) Sebelum Penutupan
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $sql = "SELECT a.id, a.code, a.name, a.category, a.normal_balance,
                       COALESCE(SUM(CASE WHEN j.date < ? THEN (ji.debit - ji.credit) ELSE 0 END), 0) as init_net,
                       COALESCE(SUM(CASE WHEN j.date BETWEEN ? AND ? AND (j.ref_type IS NULL OR j.ref_type != 'closing') THEN ji.debit ELSE 0 END), 0) as mut_debit,
                       COALESCE(SUM(CASE WHEN j.date BETWEEN ? AND ? AND (j.ref_type IS NULL OR j.ref_type != 'closing') THEN ji.credit ELSE 0 END), 0) as mut_credit
                FROM accounts a
                LEFT JOIN journal_items ji ON a.id = ji.account_id
                LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                WHERE a.is_active = 1
                GROUP BY a.id
                ORDER BY a.code ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $startDate, $endDate, $startDate, $endDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $accounts = [];
        $sumEndingDebit = 0;
        $sumEndingCredit = 0;

        foreach ($rows as $r) {
            $nb = $r['normal_balance'];
            $initNet = (float)$r['init_net'];
            $mutDebit = (float)$r['mut_debit'];
            $mutCredit = (float)$r['mut_credit'];

            $endingNet = $initNet + ($mutDebit - $mutCredit);

            $endingDebit = 0;
            $endingCredit = 0;

            if ($nb === 'debit') {
                if ($endingNet >= 0) {
                    $endingDebit = $endingNet;
                } else {
                    $endingCredit = abs($endingNet);
                }
            } else {
                if ($endingNet <= 0) {
                    $endingCredit = abs($endingNet);
                } else {
                    $endingDebit = $endingNet;
                }
            }

            if ($initNet != 0 || $mutDebit != 0 || $mutCredit != 0 || $endingDebit != 0 || $endingCredit != 0) {
                $accounts[] = [
                    'id' => (int)$r['id'],
                    'code' => $r['code'],
                    'name' => $r['name'],
                    'category' => $r['category'],
                    'normal_balance' => $nb,
                    'initial_net' => $initNet,
                    'mut_debit' => $mutDebit,
                    'mut_credit' => $mutCredit,
                    'ending_debit' => $endingDebit,
                    'ending_credit' => $endingCredit
                ];

                $sumEndingDebit += $endingDebit;
                $sumEndingCredit += $endingCredit;
            }
        }

        jsonResponse([
            'success' => true,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'branch_id' => $branchId,
            'accounts' => $accounts,
            'total_debit' => $sumEndingDebit,
            'total_credit' => $sumEndingCredit,
            'difference' => round(abs($sumEndingDebit - $sumEndingCredit), 2),
            'is_balanced' => round($sumEndingDebit, 2) === round($sumEndingCredit, 2)
        ]);
        break;

    case 'calk':
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
        
        // 1. Kas & Bank Breakdown
        $cashDetails = $db->query("SELECT a.code, a.name, COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit - ji.credit ELSE 0 END), 0) as balance 
                                   FROM accounts a 
                                   LEFT JOIN journal_items ji ON a.id = ji.account_id 
                                   LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= '$asOfDate' " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                                   WHERE a.category = 'Aset' AND (a.code LIKE '1101%' OR a.code LIKE '1102%' OR a.code LIKE '1103%')
                                   GROUP BY a.id ORDER BY a.code ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Persediaan Bahan Cuci
        $invDetails = $db->query("SELECT a.code, a.name, COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit - ji.credit ELSE 0 END), 0) as balance 
                                  FROM accounts a 
                                  LEFT JOIN journal_items ji ON a.id = ji.account_id 
                                  LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= '$asOfDate' " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                                  WHERE a.category = 'Aset' AND (a.code LIKE '1105%' OR a.code LIKE '1106%')
                                  GROUP BY a.id ORDER BY a.code ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Aset Tetap
        $fixedDetails = $db->query("SELECT a.code, a.name, COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.debit - ji.credit ELSE 0 END), 0) as balance 
                                    FROM accounts a 
                                    LEFT JOIN journal_items ji ON a.id = ji.account_id 
                                    LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= '$asOfDate' " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                                    WHERE a.category = 'Aset' AND a.code LIKE '120%'
                                    GROUP BY a.id ORDER BY a.code ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Liabilitas & Ekuitas
        $liabDetails = $db->query("SELECT a.code, a.name, COALESCE(SUM(CASE WHEN j.id IS NOT NULL THEN ji.credit - ji.debit ELSE 0 END), 0) as balance 
                                   FROM accounts a 
                                   LEFT JOIN journal_items ji ON a.id = ji.account_id 
                                   LEFT JOIN journal_entries j ON ji.journal_entry_id = j.id AND j.date <= '$asOfDate' " . ($branchId ? "AND j.branch_id = $branchId" : "") . "
                                   WHERE a.category IN ('Liabilitas', 'Ekuitas')
                                   GROUP BY a.id ORDER BY a.code ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 5. Total Omset & Transaksi
        $salesStat = $db->query("SELECT COUNT(*) as total_trx, COALESCE(SUM(total_amount), 0) as total_omzet FROM pos_transactions WHERE date(date) <= '$asOfDate'")->fetch(PDO::FETCH_ASSOC);

        jsonResponse([
            'success' => true,
            'as_of_date' => $asOfDate,
            'entity' => [
                'name' => 'PJBL Komak Laundry & Dry Cleaning',
                'standard' => 'SAK-EMKM (Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah)',
                'business_type' => 'Jasa Binatu / Laundry Kiloan, Satuan, dan Dry Cleaning Multi-Cabang',
                'branches_count' => (int)$db->query("SELECT COUNT(*) FROM branches WHERE is_active = 1")->fetchColumn(),
                'address' => 'Kantor Pusat: Jl. Pemuda No. 10, Surabaya'
            ],
            'notes' => [
                'cash_and_banks' => $cashDetails,
                'inventories' => $invDetails,
                'fixed_assets' => $fixedDetails,
                'liabilities_and_equity' => $liabDetails,
                'statistics' => $salesStat
            ]
        ]);
        break;

    case 'branch_comparison':
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $branches = $db->query("SELECT id, code, name FROM branches WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $comparisons = [];

        foreach ($branches as $b) {
            $bId = $b['id'];

            // Revenue
            $revSql = "SELECT COALESCE(SUM(ji.credit - ji.debit), 0) as amount 
                       FROM journal_items ji 
                       JOIN journal_entries j ON ji.journal_entry_id = j.id 
                       JOIN accounts a ON ji.account_id = a.id 
                       WHERE a.category = 'Pendapatan' AND j.date BETWEEN ? AND ? AND j.branch_id = ?";
            $stmt = $db->prepare($revSql);
            $stmt->execute([$startDate, $endDate, $bId]);
            $rev = (float)$stmt->fetchColumn();

            // COGS
            $cogsSql = "SELECT COALESCE(SUM(ji.debit - ji.credit), 0) as amount 
                        FROM journal_items ji 
                        JOIN journal_entries j ON ji.journal_entry_id = j.id 
                        JOIN accounts a ON ji.account_id = a.id 
                        WHERE a.category = 'Beban_Pokok' AND j.date BETWEEN ? AND ? AND j.branch_id = ?";
            $stmt = $db->prepare($cogsSql);
            $stmt->execute([$startDate, $endDate, $bId]);
            $cogs = (float)$stmt->fetchColumn();

            // Expenses
            $expSql = "SELECT COALESCE(SUM(ji.debit - ji.credit), 0) as amount 
                       FROM journal_items ji 
                       JOIN journal_entries j ON ji.journal_entry_id = j.id 
                       JOIN accounts a ON ji.account_id = a.id 
                       WHERE a.category = 'Beban_Operasional' AND j.date BETWEEN ? AND ? AND j.branch_id = ?";
            $stmt = $db->prepare($expSql);
            $stmt->execute([$startDate, $endDate, $bId]);
            $exp = (float)$stmt->fetchColumn();

            $gross = $rev - $cogs;
            $net = $gross - $exp;

            $comparisons[] = [
                'branch_id' => $bId,
                'branch_code' => $b['code'],
                'branch_name' => $b['name'],
                'revenue' => $rev,
                'cogs' => $cogs,
                'gross_profit' => $gross,
                'expenses' => $exp,
                'net_profit' => $net
            ];
        }

        jsonResponse([
            'success' => true,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'comparisons' => $comparisons
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Tipe laporan tidak dikenal.'], 400);
}
