<?php
/**
 * Settings API (Gemini API Key, dsb)
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth(['akuntan']);

$db = Database::getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {
    case 'get':
        $stmt = $db->query("SELECT * FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        // Mask key for safety
        if (!empty($settings['gemini_api_key'])) {
            $key = $settings['gemini_api_key'];
            $settings['gemini_api_key_masked'] = substr($key, 0, 6) . '...' . substr($key, -4);
        } else {
            $settings['gemini_api_key_masked'] = 'Belum diisi (Menggunakan Smart Simulator)';
        }
        jsonResponse(['success' => true, 'data' => $settings]);
        break;

    case 'save':
        $geminiKey = trim($_POST['gemini_api_key'] ?? '');
        $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES ('gemini_api_key', ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value");
        $stmt->execute([$geminiKey]);

        auditLog('UPDATE', 'settings', "Memperbarui konfigurasi Gemini API Key", [
            'key' => 'gemini_api_key',
            'has_value' => !empty($geminiKey)
        ]);

        jsonResponse(['success' => true, 'message' => 'Pengaturan Gemini API Key berhasil disimpan.']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}
