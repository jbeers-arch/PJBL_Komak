<?php
/**
 * Smart Invoice Scanner & Auto-Journaling API
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'quota':
        // Cek kuota pemindaian harian
        $todayScans = (int)$db->query("SELECT COUNT(*) FROM invoice_scans WHERE date(created_at) = date('now')")->fetchColumn();
        $maxQuota = 1500;
        $remaining = max(0, $maxQuota - $todayScans);
        jsonResponse([
            'success' => true,
            'today_used' => $todayScans,
            'max_daily' => $maxQuota,
            'remaining' => $remaining
        ]);
        break;

    case 'list':
        $stmt = $db->query("SELECT s.*, 
                            d.name as debit_account_name, d.code as debit_account_code,
                            c.name as credit_account_name, c.code as credit_account_code,
                            j.transaction_no as journal_no
                            FROM invoice_scans s
                            LEFT JOIN accounts d ON s.debit_account_id = d.id
                            LEFT JOIN accounts c ON s.credit_account_id = c.id
                            LEFT JOIN journal_entries j ON s.journal_entry_id = j.id
                            ORDER BY s.id DESC");
        $scans = $stmt->fetchAll();
        foreach ($scans as &$sc) {
            $sc['image_url'] = BASE_URL . '/uploads/invoices/' . $sc['image_path'];
            $sc['is_pdf'] = (strtolower(pathinfo($sc['image_path'] ?? '', PATHINFO_EXTENSION)) === 'pdf');
        }
        jsonResponse(['success' => true, 'data' => $scans]);
        break;

    case 'get':
        $scanId = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT s.*, 
                              d.name as debit_account_name, d.code as debit_account_code,
                              c.name as credit_account_name, c.code as credit_account_code,
                              j.transaction_no as journal_no
                              FROM invoice_scans s
                              LEFT JOIN accounts d ON s.debit_account_id = d.id
                              LEFT JOIN accounts c ON s.credit_account_id = c.id
                              LEFT JOIN journal_entries j ON s.journal_entry_id = j.id
                              WHERE s.id = ?");
        $stmt->execute([$scanId]);
        $item = $stmt->fetch();
        if ($item) {
            $item['image_url'] = BASE_URL . '/uploads/invoices/' . $item['image_path'];
            $item['is_pdf'] = (strtolower(pathinfo($item['image_path'] ?? '', PATHINFO_EXTENSION)) === 'pdf');
            jsonResponse(['success' => true, 'data' => $item]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Faktur tidak ditemukan.'], 404);
        }
        break;

    case 'upload_scan':
        if (!isset($_FILES['invoice_file']) || $_FILES['invoice_file']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(['success' => false, 'message' => 'File faktur (foto atau dokumen PDF) tidak ditemukan atau gagal diunggah.'], 400);
        }

        $fileExt = strtolower(pathinfo($_FILES['invoice_file']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($fileExt, $allowedExts)) {
            jsonResponse(['success' => false, 'message' => 'Format file tidak didukung. Harap unggah foto (JPG, PNG, WEBP) atau dokumen PDF.'], 400);
        }

        // Validasi MIME Type Asli File (Mencegah file berbahaya)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['invoice_file']['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/webp', 'application/pdf', 'application/x-pdf'];
        if (!in_array($mimeType, $allowedMimes)) {
            jsonResponse(['success' => false, 'message' => 'Tipe konten file tidak valid atau berpotensi membahayakan server.'], 400);
        }

        $fileSize = $_FILES['invoice_file']['size'];
        if ($fileSize > 25 * 1024 * 1024) {
            jsonResponse(['success' => false, 'message' => 'Ukuran file melebihi batas maksimum 25 MB.'], 400);
        }

        $uploadDir = ROOT_PATH . '/uploads/invoices/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['invoice_file']['tmp_name'];
        $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['invoice_file']['name']);
        $fileName = time() . '_' . $cleanName;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($fileTmp, $targetPath)) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan file ke server.'], 500);
        }

        $webImagePath = BASE_URL . '/uploads/invoices/' . $fileName;
        $isPdf = ($fileExt === 'pdf');

        // Ambil Key dari database settings
        $stmtKey = $db->prepare("SELECT value FROM settings WHERE key = 'gemini_api_key' LIMIT 1");
        $stmtKey->execute();
        $apiKeyRow = $stmtKey->fetch();
        $apiKey = !empty($apiKeyRow['value']) ? trim($apiKeyRow['value']) : '';

        $clientOcrText = trim($_POST['client_ocr_text'] ?? '');

        $parsedData = null;
        $aiSource = '';

        // 1. Ekstraksi cerdas via Backend AI Engine
        if (!empty($apiKey)) {
            $parsedData = callSmartVisionAPI($targetPath, $apiKey);
            if ($parsedData) {
                $aiSource = 'Sistem Pemindai Cerdas (Smart AI OCR)';
            }
        }

        // 2. Fallback OCR lokal jika diperlukan
        if (!$parsedData && !empty($clientOcrText)) {
            $parsedData = parseTextToInvoiceData($clientOcrText);
            $aiSource = 'Mesin OCR Standar';
        }

        // 3. Fallback form input
        if (!$parsedData) {
            $parsedData = [
                'vendor_name' => 'Faktur Pembelian',
                'invoice_no' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
                'invoice_date' => date('Y-m-d'),
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'items' => [],
                'expense_type' => 'operasional',
                'suggested_debit_account_code' => '6104',
                'suggested_debit_account_name' => 'Beban Operasional & Perlengkapan',
                'suggested_credit_account_code' => '1103',
                'suggested_credit_account_name' => 'Kas Utama / Bank BCA',
                'summary' => 'Faktur / Nota Pengeluaran'
            ];
            $aiSource = 'Pemeriksaan Manual';
        }

        // Tentukan akun debit & kredit
        $debitAccCode = $parsedData['suggested_debit_account_code'] ?? '6104';
        $creditAccCode = $parsedData['suggested_credit_account_code'] ?? '1103';

        $stmtD = $db->prepare("SELECT id FROM accounts WHERE code = ? LIMIT 1");
        $stmtD->execute([$debitAccCode]);
        $rowD = $stmtD->fetch();
        $debitId = $rowD ? (int)$rowD['id'] : 19; // Default 6104 Beban Operasional & Perlengkapan (Laba Rugi)

        $stmtC = $db->prepare("SELECT id FROM accounts WHERE code = ? LIMIT 1");
        $stmtC->execute([$creditAccCode]);
        $rowC = $stmtC->fetch();
        $creditId = $rowC ? (int)$rowC['id'] : 3; // Default Kas Utama

        // Simpan ke invoice_scans sebagai DRAFT
        $stmtScan = $db->prepare("INSERT INTO invoice_scans 
            (branch_id, vendor_name, invoice_no, invoice_date, total_amount, tax_amount, items_json, debit_account_id, credit_account_id, image_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')");
        
        $branchId = (int)($_POST['branch_id'] ?? 1);
        $stmtScan->execute([
            $branchId,
            $parsedData['vendor_name'],
            $parsedData['invoice_no'],
            $parsedData['invoice_date'],
            (float)$parsedData['total_amount'],
            (float)($parsedData['tax_amount'] ?? 0),
            json_encode($parsedData['items'] ?? []),
            $debitId,
            $creditId,
            $fileName
        ]);

        $scanId = $db->lastInsertId();

        jsonResponse([
            'success' => true,
            'message' => 'Faktur berhasil dipindai oleh ' . $aiSource,
            'scan_id' => $scanId,
            'data' => array_merge($parsedData, [
                'id' => $scanId,
                'debit_account_id' => $debitId,
                'credit_account_id' => $creditId,
                'image_url' => $webImagePath,
                'is_pdf' => $isPdf,
                'file_name' => $fileName,
                'ai_source' => $aiSource
            ])
        ]);
        break;

    case 'approve':
        $scanId = (int)($_POST['scan_id'] ?? 0);
        $debitAccountId = (int)($_POST['debit_account_id'] ?? 0);
        $creditAccountId = (int)($_POST['credit_account_id'] ?? 0);
        $totalAmount = (float)($_POST['total_amount'] ?? 0);
        $taxAmount = (float)($_POST['tax_amount'] ?? 0);
        $vendorName = trim($_POST['vendor_name'] ?? 'Vendor');
        $invoiceNo = trim($_POST['invoice_no'] ?? '-');
        $invoiceDate = trim($_POST['invoice_date'] ?? date('Y-m-d'));
        $postingDate = trim($_POST['posting_date'] ?? date('Y-m-d'));
        if (empty($postingDate)) {
            $postingDate = date('Y-m-d');
        }
        $description = trim($_POST['description'] ?? '');

        if (!$scanId || $totalAmount <= 0 || !$debitAccountId || !$creditAccountId) {
            jsonResponse(['success' => false, 'message' => 'Data verifikasi jurnal tidak lengkap atau total bayar Rp 0.'], 400);
        }

        $stmt = $db->prepare("SELECT * FROM invoice_scans WHERE id = ?");
        $stmt->execute([$scanId]);
        $scan = $stmt->fetch();

        if (!$scan) {
            jsonResponse(['success' => false, 'message' => 'Faktur tidak ditemukan.'], 404);
        }

        if ($scan['status'] === 'approved') {
            jsonResponse(['success' => false, 'message' => 'Faktur ini sudah pernah disetujui sebelumnya.'], 400);
        }

        // Cek penguncian periode (Period Lock) terhadap tanggal pembukuan (posting date)
        $stmtLockCheck = $db->prepare("SELECT period_name, period_end FROM period_locks WHERE ? <= period_end AND status = 'locked' ORDER BY period_end DESC LIMIT 1");
        $stmtLockCheck->execute([$postingDate]);
        $lockHit = $stmtLockCheck->fetch();
        if ($lockHit) {
            jsonResponse([
                'success' => false,
                'message' => "Tanggal pembukuan ($postingDate) berada dalam periode yang telah DITUTUP BUKU & DIKUNCI ('{$lockHit['period_name']}'). Pembukuan ke periode terkunci dilarang demi kepatuhan audit."
            ], 403);
        }

        try {
            $db->beginTransaction();

            $trxNo = 'JRN-SCN-' . str_pad($scanId, 5, '0', STR_PAD_LEFT);
            $desc = !empty($description) ? $description : ("Pembayaran Faktur $vendorName ($invoiceNo)");
            if (!empty($invoiceDate) && $invoiceDate !== $postingDate && strpos($desc, 'Tgl Nota:') === false) {
                $desc .= " (Tgl Nota: $invoiceDate)";
            }

            // 1. Buat Header Jurnal Umum (Gunakan Tanggal Pembukuan Periode Berjalan)
            $stmtJrn = $db->prepare("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, ref_id, created_by) 
                                     VALUES (?, ?, ?, ?, 'invoice_scan', ?, ?)");
            $stmtJrn->execute([$scan['branch_id'] ?? 1, $trxNo, $postingDate, $desc, (string)$scanId, $_SESSION['user']['id']]);
            $journalId = $db->lastInsertId();

            // 2. Buat Detail Jurnal (Debit & Kredit)
            $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
            
            // Subtotal ke Akun Beban/Persediaan (Debit)
            $netExpense = $totalAmount - $taxAmount;
            $stmtItem->execute([$journalId, $debitAccountId, $netExpense, 0, $desc]);

            // Jika ada PPN Pajak, masukkan ke Akun PPN Masukan (1107 Aset Lancar / Pajak Dibayar di Muka)
            if ($taxAmount > 0) {
                $accPPNMasukan = $db->query("SELECT id FROM accounts WHERE code = '1107' LIMIT 1")->fetchColumn() ?: 9;
                $stmtItem->execute([$journalId, $accPPNMasukan, $taxAmount, 0, 'PPN Masukan ' . $invoiceNo]);
            }

            // Total Bayar ke Akun Kas / Utang (Kredit)
            $stmtItem->execute([$journalId, $creditAccountId, 0, $totalAmount, 'Pelunasan Faktur ' . $vendorName]);

            // 3. Auto-Restock: Tambah Stok Fisik Produk ke Gudang Cabang jika diaktifkan
            $autoRestock = !empty($_POST['auto_restock']);
            $restockItems = json_decode($_POST['restock_items'] ?? '[]', true);
            $restockedCount = 0;
            if ($autoRestock && !empty($restockItems)) {
                $targetBranchId = (int)($scan['branch_id'] ?? 1);
                $stmtStockPrd = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmtStockBr = $db->prepare("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES (?, ?, ?)
                                             ON CONFLICT(branch_id, product_id) DO UPDATE SET stock = stock + excluded.stock");
                $stmtAdj = $db->prepare("INSERT INTO stock_adjustments (branch_id, product_id, type, qty_change, previous_stock, new_stock, reason, adjusted_by)
                                         VALUES (?, ?, 'masuk', ?, 0, ?, ?, ?)");
                foreach ($restockItems as $ri) {
                    $pId = (int)($ri['product_id'] ?? 0);
                    $rQty = (int)($ri['qty'] ?? 0);
                    if ($pId > 0 && $rQty > 0) {
                        $stmtStockPrd->execute([$rQty, $pId]);
                        $stmtStockBr->execute([$targetBranchId, $pId, $rQty]);
                        $stmtAdj->execute([$targetBranchId, $pId, $rQty, $rQty, "Restok Faktur $invoiceNo ($vendorName)", $_SESSION['user']['id']]);
                        $restockedCount += $rQty;
                    }
                }
            }

            // 4. Update status scan invoice & supplier
            $supplierId = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;
            $stmtUpd = $db->prepare("UPDATE invoice_scans SET 
                                     status = 'approved', 
                                     journal_entry_id = ?, 
                                     vendor_name = ?,
                                     invoice_no = ?,
                                     invoice_date = ?,
                                     debit_account_id = ?, 
                                     credit_account_id = ?, 
                                     total_amount = ?,
                                     tax_amount = ?,
                                     supplier_id = ?
                                     WHERE id = ?");
            $stmtUpd->execute([$journalId, $vendorName, $invoiceNo, $invoiceDate, $debitAccountId, $creditAccountId, $totalAmount, $taxAmount, $supplierId, $scanId]);

            $db->commit();

            auditLog('APPROVE', 'Auto-Jurnal AI', "Otorisasi & posting faktur #$invoiceNo dari $vendorName ke Jurnal Umum ($trxNo) senilai Rp " . number_format($totalAmount, 0, ',', '.'), [
                'scan_id' => $scanId,
                'journal_no' => $trxNo,
                'posting_date' => $postingDate,
                'total_amount' => $totalAmount
            ]);

            $msgRestock = ($restockedCount > 0) ? " dan $restockedCount unit produk otomatis direstok ke gudang cabang." : ".";
            jsonResponse([
                'success' => true,
                'message' => "Faktur berhasil disetujui sebesar Rp " . number_format($totalAmount, 0, ',', '.') . " dan otomatis diposting ke Jurnal Umum ($trxNo)" . $msgRestock,
                'journal_no' => $trxNo
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal posting jurnal: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete':
        $scanId = (int)($_POST['scan_id'] ?? $_GET['scan_id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM invoice_scans WHERE id = ?");
        $stmt->execute([$scanId]);
        $scan = $stmt->fetch();

        if (!$scan) {
            jsonResponse(['success' => false, 'message' => 'Faktur tidak ditemukan.'], 404);
        }

        try {
            $db->beginTransaction();

            // Jika sudah dijurnal, hapus juga jurnalnya
            if ($scan['journal_entry_id']) {
                $stmtJrn = $db->prepare("DELETE FROM journal_entries WHERE id = ?");
                $stmtJrn->execute([$scan['journal_entry_id']]);
            }

            // Hapus file gambar fisik jika ada
            if (!empty($scan['image_path'])) {
                $filePath = ROOT_PATH . '/uploads/invoices/' . $scan['image_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Hapus record scan
            $stmtDel = $db->prepare("DELETE FROM invoice_scans WHERE id = ?");
            $stmtDel->execute([$scanId]);

            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Faktur dan catatan terkait berhasil dihapus dari sistem.']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            jsonResponse(['success' => false, 'message' => 'Gagal menghapus faktur: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}

// Function to call AI Vision API (Formal internal service)
function callSmartVisionAPI($filePath, $apiKey) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
        $mimeType = 'application/pdf';
    } else {
        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
    }
    $imageData = base64_encode(file_get_contents($filePath));

    $prompt = <<<TXT
Kamu adalah sistem pemindai dokumen akuntansi dan faktur cerdas. Analisis gambar faktur, kuitansi, struk pembelian, nota pengeluaran, atau dokumen PDF faktur/invoice ini secara teliti dan akurat.
BACA NOMINAL ANGKA DENGAN SANGAT TELITI (misalnya jika tertulis 30.000 atau 30rb maka total adalah 30000, 1.250.000 menjadi 1250000).
Jika dokumen berupa file PDF (e-faktur, tagihan vendor, invoice resmi B2B), periksa seluruh dokumen: nama vendor/perusahaan, nomor invoice/nota, tanggal transaksi, rincian barang/jasa, subtotal, potongan harga (diskon), PPN/pajak, dan grand total.

Kembalikan HANYA format JSON valid tanpa format markdown atau kutip backticks:
{
  "vendor_name": "Nama Toko / Penjual / Vendor",
  "invoice_no": "Nomor Nota / Faktur / Invoice",
  "invoice_date": "YYYY-MM-DD",
  "subtotal": 0,
  "tax_amount": 0,
  "total_amount": 0,
  "items": [
     {"name": "Nama Item", "qty": 1, "price": 0, "subtotal": 0}
  ],
  "expense_type": "operasional",
  "suggested_debit_account_code": "6104",
  "suggested_debit_account_name": "Beban Operasional & Perlengkapan",
  "suggested_credit_account_code": "1103",
  "suggested_credit_account_name": "Kas Utama / Bank BCA",
  "summary": "Ringkasan pengeluaran"
}

Panduan Akun Debit SAK-EMKM (Bisnis Jasa Laundry):
- 5101 jika pembelian detergen, sabun, softener, pewangi, pemutih, pelicin setrika, chemical laundry, plastik pembungkus, gantungan hanger (Beban Pokok Layanan Laundry - Masuk Laporan Laba Rugi HPP)
- 6104 jika ATK, kertas, nota bon, lakban, perlengkapan toko/kantor umum, konsumsi, bensin (Beban Operasional & Perlengkapan - Masuk Laporan Laba Rugi)
- 6102 jika tagihan listrik PLN, air PDAM, isi ulang gas boiler/LPG, internet, pulsa (Beban Operasional - Masuk Laporan Laba Rugi)
- 6103 jika sewa tempat / ruko / kios laundry (Beban Operasional - Masuk Laporan Laba Rugi)
- 6105 jika pemeliharaan, servis mesin cuci, perbaikan dryer/boiler, instalasi pipa (Beban Operasional - Masuk Laporan Laba Rugi)
- 1105 jika pencatatan pembelian stok bahan baku yang disimpan sebagai persediaan gudang (Aset Lancar - Masuk Neraca)
- 1106 jika perlengkapan yang dicatat sebagai aset perlengkapan fisik (Aset Lancar - Masuk Neraca)
- 1201 jika pembelian aset mesin baru, dryer industri, boiler besar, komputer kasir (Aset Tetap - Masuk Neraca)
TXT;

    $models = ['gemini-2.5-flash', 'gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-3.5-flash', 'gemini-3.8-flash', 'gemini-flash-latest'];
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $imageData
                        ]
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "response_mime_type" => "application/json"
        ]
    ];

    foreach ($models as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $modelName . ":generateContent?key=" . $apiKey;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $data = json_decode($content, true);
            if ($data && !empty($data['total_amount'])) {
                return $data;
            }
        }
    }
    return null;
}

// Function to parse raw text extracted from image
function parseTextToInvoiceData($text) {
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    $firstLine = reset($lines) ?: 'Toko / Vendor';

    $detectedTotal = 0;
    if (preg_match('/(\d+)\s*(rb|k|ribu)/i', $text, $matchK)) {
        $detectedTotal = (float)$matchK[1] * 1000;
    }

    if ($detectedTotal <= 0) {
        preg_match_all('/(?:Rp\.?|Total|Jumlah)?\s*([0-9]{1,3}(?:\.[0-9]{3})*(?:,[0-9]{2})?|[0-9]{4,})/i', $text, $matches);
        if (!empty($matches[1])) {
            $numbers = [];
            foreach ($matches[1] as $m) {
                $cleaned = str_replace('.', '', explode(',', $m)[0]);
                $val = (float)$cleaned;
                if ($val >= 1000) {
                    $numbers[] = $val;
                }
            }
            if (!empty($numbers)) {
                $detectedTotal = max($numbers);
            }
        }
    }

    $detectedDate = date('Y-m-d');
    if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $dateMatch)) {
        $detectedDate = sprintf('%04d-%02d-%02d', $dateMatch[3], $dateMatch[2], $dateMatch[1]);
    }

    return [
        'vendor_name' => substr($firstLine, 0, 50),
        'invoice_no' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
        'invoice_date' => $detectedDate,
        'subtotal' => $detectedTotal,
        'tax_amount' => 0,
        'total_amount' => $detectedTotal,
        'items' => [
            ['name' => 'Item Faktur', 'qty' => 1, 'price' => $detectedTotal, 'subtotal' => $detectedTotal]
        ],
        'expense_type' => 'operasional',
        'suggested_debit_account_code' => '6104',
        'suggested_debit_account_name' => 'Beban Operasional & Perlengkapan',
        'suggested_credit_account_code' => '1103',
        'suggested_credit_account_name' => 'Kas Utama / Bank BCA',
        'summary' => 'Pembayaran ' . substr($firstLine, 0, 40)
    ];
}
