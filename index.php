<?php
/**
 * Root Router / Redirector
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Trigger database initialization on first visit
Database::getConnection();

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'kasir') {
        header('Location: ' . BASE_URL . '/views/pos.php');
    } else {
        header('Location: ' . BASE_URL . '/views/dashboard.php');
    }
    exit;
} else {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}
