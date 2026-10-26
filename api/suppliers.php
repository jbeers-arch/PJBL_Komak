<?php
/**
 * Suppliers & Vendor Master API
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $suppliers = $db->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'data' => $suppliers]);
        break;

    case 'create':
        requireAuth(['akuntan']);
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $bankAccount = trim($_POST['bank_account'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? 'COD');

        if (empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Nama supplier wajib diisi.'], 400);
        }

        $code = 'SPL-' . str_pad($db->query("SELECT COUNT(*) + 1 FROM suppliers")->fetchColumn(), 3, '0', STR_PAD_LEFT);

        $stmt = $db->prepare("INSERT INTO suppliers (code, name, contact_person, phone, address, bank_name, bank_account, payment_terms) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $contact, $phone, $address, $bankName, $bankAccount, $paymentTerms]);
        $newId = $db->lastInsertId();

        auditLog('CREATE', 'suppliers', "Menambahkan supplier baru: $name ($code)", [
            'supplier_id' => $newId,
            'code' => $code,
            'name' => $name,
            'contact_person' => $contact,
            'phone' => $phone
        ]);

        jsonResponse(['success' => true, 'message' => "Supplier '$name' berhasil ditambahkan.", 'id' => $newId]);
        break;

    case 'update':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $bankAccount = trim($_POST['bank_account'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? 'COD');

        if (!$id || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Data supplier tidak lengkap.'], 400);
        }

        $stmt = $db->prepare("UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, address = ?, bank_name = ?, bank_account = ?, payment_terms = ? WHERE id = ?");
        $stmt->execute([$name, $contact, $phone, $address, $bankName, $bankAccount, $paymentTerms, $id]);

        auditLog('UPDATE', 'suppliers', "Memperbarui data supplier: $name (ID #$id)", [
            'supplier_id' => $id,
            'name' => $name,
            'contact_person' => $contact,
            'phone' => $phone,
            'payment_terms' => $paymentTerms
        ]);

        jsonResponse(['success' => true, 'message' => 'Data supplier berhasil diperbarui.']);
        break;

    case 'delete':
        requireAuth(['akuntan']);
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $s = $db->query("SELECT name, code FROM suppliers WHERE id = " . (int)$id)->fetch(PDO::FETCH_ASSOC);
        $sName = $s['name'] ?? "ID #$id";

        $stmt = $db->prepare("UPDATE suppliers SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        auditLog('DELETE', 'suppliers', "Menonaktifkan supplier: $sName", ['supplier_id' => $id]);

        jsonResponse(['success' => true, 'message' => 'Supplier dinonaktifkan.']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
