<?php
/**
 * Database Connection & Auto-Migration
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $isNewDb = !file_exists(DB_PATH);

            self::$instance = new PDO('sqlite:' . DB_PATH);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA busy_timeout = 5000;');
            try {
                self::$instance->exec('PRAGMA journal_mode = WAL;');
            } catch (Exception $e) {}

            if ($isNewDb) {
                self::initSchema(self::$instance);
                self::seedInitialData(self::$instance);
            } else {
                self::ensureMigrations(self::$instance);
            }
        }
        return self::$instance;
    }

    private static function ensureMigrations(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                user_name TEXT NULL,
                role TEXT NULL,
                action TEXT NOT NULL,
                module TEXT NOT NULL,
                description TEXT NOT NULL,
                details TEXT NULL,
                ip_address TEXT NULL,
                user_agent TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at);
            CREATE INDEX IF NOT EXISTS idx_audit_logs_module ON audit_logs(module);
            CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);

            CREATE TABLE IF NOT EXISTS stock_adjustments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                branch_id INTEGER,
                product_id INTEGER NOT NULL,
                type TEXT CHECK(type IN ('masuk', 'keluar', 'opname', 'rusak')) NOT NULL,
                qty_change INTEGER NOT NULL,
                previous_stock INTEGER NOT NULL,
                new_stock INTEGER NOT NULL,
                cost_impact REAL DEFAULT 0,
                reason TEXT,
                adjusted_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id),
                FOREIGN KEY (branch_id) REFERENCES branches(id),
                FOREIGN KEY (adjusted_by) REFERENCES users(id)
            );

            CREATE TABLE IF NOT EXISTS period_locks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                period_name TEXT NOT NULL,
                period_start DATE NOT NULL,
                period_end DATE NOT NULL,
                closed_at DATETIME NOT NULL,
                closed_by INTEGER,
                total_revenue REAL DEFAULT 0,
                total_expense REAL DEFAULT 0,
                net_profit_loss REAL DEFAULT 0,
                prive_amount REAL DEFAULT 0,
                closing_journal_id INTEGER,
                status TEXT DEFAULT 'locked',
                notes TEXT,
                FOREIGN KEY (closed_by) REFERENCES users(id),
                FOREIGN KEY (closing_journal_id) REFERENCES journal_entries(id)
            );
        ");

        // Migrasi otomatis ke Layanan Jasa Laundry
        try {
            $checkLnd = $db->query("SELECT COUNT(*) FROM products WHERE code LIKE 'LND%'")->fetchColumn();
            if ($checkLnd == 0) {
                // 1. Update Accounts COA untuk Jasa Laundry
                $db->exec("UPDATE accounts SET name = 'Persediaan Bahan Cuci & Perlengkapan' WHERE code = '1105'");
                $db->exec("UPDATE accounts SET name = 'Mesin Cuci, Dryer & Peralatan Laundry' WHERE code = '1201'");
                $db->exec("UPDATE accounts SET name = 'Pendapatan Jasa Laundry' WHERE code = '4101'");
                $db->exec("UPDATE accounts SET name = 'Beban Pokok Layanan Laundry (Bahan & Chemical)' WHERE code = '5101'");
                $db->exec("UPDATE accounts SET name = 'Beban Listrik, Air Bersih & Gas Boiler' WHERE code = '6102'");

                // 2. Daftar Layanan & Produk Laundry
                $laundryServices = [
                    [1, 'LND-001', 'Cuci Kering Setrika (Reguler 2 Hari)', 'Laundry Kiloan', 8000, 2500, 999, 'kg'],
                    [2, 'LND-002', 'Cuci Kering Setrika (Express 1 Hari)', 'Laundry Kiloan', 12000, 3500, 999, 'kg'],
                    [3, 'LND-003', 'Cuci Kering Setrika (Kilat 3-4 Jam)', 'Laundry Kiloan', 18000, 5000, 999, 'kg'],
                    [4, 'LND-004', 'Cuci Kering Lipat (Non-Setrika)', 'Laundry Kiloan', 6000, 1800, 999, 'kg'],
                    [5, 'LND-005', 'Setrika Uap Saja (Steam Press)', 'Laundry Kiloan', 5000, 1200, 999, 'kg'],
                    [6, 'LND-006', 'Bedcover Jumbo / King Size', 'Laundry Satuan', 35000, 9000, 100, 'pcs'],
                    [7, 'LND-007', 'Bedcover Single', 'Laundry Satuan', 25000, 7000, 100, 'pcs'],
                    [8, 'LND-008', 'Selimut Tebal / Quilt', 'Laundry Satuan', 20000, 5500, 100, 'pcs'],
                    [9, 'LND-009', 'Cuci Sepatu Sneaker / Canvas', 'Laundry Satuan', 30000, 8000, 150, 'pasang'],
                    [10, 'LND-010', 'Cuci Helm Full Face / Half Face', 'Laundry Satuan', 25000, 7000, 100, 'pcs'],
                    [11, 'LND-011', 'Cuci Karpet Tebal', 'Laundry Satuan', 15000, 4000, 200, 'meter'],
                    [12, 'LND-012', 'Jas Formal / Blazer', 'Dry Cleaning', 35000, 10000, 80, 'pcs'],
                    [13, 'LND-013', 'Gaun / Kebaya Pesta', 'Dry Cleaning', 45000, 12000, 60, 'pcs'],
                    [14, 'LND-014', 'Gordyn / Tirai Tebal', 'Dry Cleaning', 15000, 4500, 150, 'meter'],
                    [15, 'LND-015', 'Parfum Laundry Premium 250ml', 'Bahan & Perlengkapan', 25000, 14000, 50, 'botol'],
                    [16, 'LND-016', 'Deterjen Cair Ramah Lingkungan 1L', 'Bahan & Perlengkapan', 28000, 16000, 50, 'botol']
                ];

                foreach ($laundryServices as $svc) {
                    [$id, $code, $name, $cat, $price, $cost, $stock, $unit] = $svc;
                    $exists = $db->query("SELECT id FROM products WHERE id = $id")->fetchColumn();
                    if ($exists) {
                        $stmt = $db->prepare("UPDATE products SET code = ?, name = ?, category = ?, price = ?, cost_price = ?, stock = ?, unit = ? WHERE id = ?");
                        $stmt->execute([$code, $name, $cat, $price, $cost, $stock, $unit, $id]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO products (id, code, name, category, price, cost_price, stock, unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$id, $code, $name, $cat, $price, $cost, $stock, $unit]);
                    }
                    foreach ([1, 2, 3] as $bId) {
                        $bStock = ($unit === 'kg') ? 999 : max(10, floor($stock / 2));
                        $db->exec("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES ($bId, $id, $bStock) ON CONFLICT(branch_id, product_id) DO UPDATE SET stock = $bStock");
                    }
                }

                // 3. Update Supplier ke Vendor Laundry
                $laundrySuppliers = [
                    [1, 'SPL-001', 'CV Berkah Chemical Laundry', 'Hendra Wijaya', '081234567890', 'Kawasan Industri Rungkut, Surabaya', 'BCA', '1420987654', 'Net 14'],
                    [2, 'SPL-002', 'PT Surya Jaya Mandiri Plastik & Hanger', 'Budi Santoso', '081398765432', 'Jl. Kalibutuh No. 25, Surabaya', 'Mandiri', '1410012345678', 'Net 30'],
                    [3, 'SPL-003', 'Toko Mitra Sparepart Mesin & Boiler', 'Ibu Ratna', '082155667788', 'Jl. Pasar Besar No. 12, Surabaya', 'BCA', '2160998877', 'COD']
                ];

                foreach ($laundrySuppliers as $sup) {
                    [$sId, $sCode, $sName, $sContact, $sPhone, $sAddr, $sBank, $sAcc, $sTerms] = $sup;
                    $ex = $db->query("SELECT id FROM suppliers WHERE id = $sId")->fetchColumn();
                    if ($ex) {
                        $stmt = $db->prepare("UPDATE suppliers SET code = ?, name = ?, contact_person = ?, phone = ?, address = ?, bank_name = ?, bank_account = ?, payment_terms = ? WHERE id = ?");
                        $stmt->execute([$sCode, $sName, $sContact, $sPhone, $sAddr, $sBank, $sAcc, $sTerms, $sId]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO suppliers (id, code, name, contact_person, phone, address, bank_name, bank_account, payment_terms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$sId, $sCode, $sName, $sContact, $sPhone, $sAddr, $sBank, $sAcc, $sTerms]);
                    }
                }
            }
        } catch (Exception $e) {}

        // Migrasi kolom pelacakan laundry pada pos_transactions
        try {
            $cols = $db->query("PRAGMA table_info(pos_transactions)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('customer_name', $cols)) {
                $db->exec("ALTER TABLE pos_transactions ADD COLUMN customer_name TEXT DEFAULT ''");
            }
            if (!in_array('customer_phone', $cols)) {
                $db->exec("ALTER TABLE pos_transactions ADD COLUMN customer_phone TEXT DEFAULT ''");
            }
            if (!in_array('rack_no', $cols)) {
                $db->exec("ALTER TABLE pos_transactions ADD COLUMN rack_no TEXT DEFAULT ''");
            }
            if (!in_array('laundry_status', $cols)) {
                $db->exec("ALTER TABLE pos_transactions ADD COLUMN laundry_status TEXT DEFAULT 'sedang_diproses'");
            }
            if (!in_array('estimated_completion', $cols)) {
                $db->exec("ALTER TABLE pos_transactions ADD COLUMN estimated_completion DATETIME NULL");
            }
        } catch (Exception $e) {}

        // Migrasi Standar SAK EMKM: Reklasifikasi Akun 4102 untuk Penjualan Retail & Bahan
        try {
            $db->exec("UPDATE accounts SET name = 'Pendapatan Penjualan Retail & Lain-lain' WHERE code = '4102' AND name != 'Pendapatan Penjualan Retail & Lain-lain'");
        } catch (Exception $e) {}
    }

    private static function initSchema(PDO $db): void {
        $schema = <<<SQL
        -- Tabel Cabang / Outlet
        CREATE TABLE IF NOT EXISTS branches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            address TEXT,
            phone TEXT,
            is_active INTEGER DEFAULT 1
        );

        -- Tabel Pengguna (Akuntan & Kasir)
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT NOT NULL,
            role TEXT CHECK(role IN ('akuntan', 'kasir', 'superadmin')) NOT NULL,
            branch_id INTEGER,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
        );

        -- Tabel Bagan Akun (Chart of Accounts - COA)
        CREATE TABLE IF NOT EXISTS accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            category TEXT CHECK(category IN ('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban_Pokok', 'Beban_Operasional')) NOT NULL,
            normal_balance TEXT CHECK(normal_balance IN ('debit', 'credit')) NOT NULL,
            is_active INTEGER DEFAULT 1
        );

        -- Tabel Master Produk (Untuk Kasir)
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            category TEXT,
            price REAL NOT NULL,
            cost_price REAL DEFAULT 0,
            stock INTEGER DEFAULT 100,
            unit TEXT DEFAULT 'pcs'
        );

        -- Tabel Header Jurnal Umum (Double Entry)
        CREATE TABLE IF NOT EXISTS journal_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            branch_id INTEGER,
            transaction_no TEXT UNIQUE NOT NULL,
            date DATE NOT NULL,
            description TEXT NOT NULL,
            ref_type TEXT CHECK(ref_type IN ('pos', 'invoice_scan', 'manual')) DEFAULT 'manual',
            ref_id TEXT,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        );

        -- Tabel Detail Jurnal (Item Debit / Kredit)
        CREATE TABLE IF NOT EXISTS journal_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            journal_entry_id INTEGER NOT NULL,
            account_id INTEGER NOT NULL,
            debit REAL DEFAULT 0,
            credit REAL DEFAULT 0,
            memo TEXT,
            FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
            FOREIGN KEY (account_id) REFERENCES accounts(id)
        );

        -- Tabel Transaksi Kasir POS (Pusat & Sinkronisasi)
        CREATE TABLE IF NOT EXISTS pos_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transaction_no TEXT UNIQUE NOT NULL,
            branch_id INTEGER NOT NULL,
            cashier_id INTEGER NOT NULL,
            date DATETIME NOT NULL,
            total_amount REAL NOT NULL,
            total_cost REAL DEFAULT 0,
            payment_method TEXT DEFAULT 'Tunai',
            cash_received REAL DEFAULT 0,
            change_amount REAL DEFAULT 0,
            customer_name TEXT DEFAULT '',
            customer_phone TEXT DEFAULT '',
            rack_no TEXT DEFAULT '',
            laundry_status TEXT DEFAULT 'sedang_diproses',
            estimated_completion DATETIME NULL,
            status TEXT CHECK(status IN ('synced', 'draft')) DEFAULT 'synced',
            journal_entry_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (cashier_id) REFERENCES users(id),
            FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE SET NULL
        );

        -- Tabel Detail Item Kasir
        CREATE TABLE IF NOT EXISTS pos_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pos_transaction_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            price REAL NOT NULL,
            cost_price REAL DEFAULT 0,
            qty INTEGER NOT NULL,
            subtotal REAL NOT NULL,
            FOREIGN KEY (pos_transaction_id) REFERENCES pos_transactions(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        -- Tabel Scan Faktur (AI OCR)
        CREATE TABLE IF NOT EXISTS invoice_scans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            branch_id INTEGER,
            vendor_name TEXT NOT NULL,
            invoice_no TEXT,
            invoice_date DATE,
            total_amount REAL NOT NULL,
            tax_amount REAL DEFAULT 0,
            items_json TEXT,
            debit_account_id INTEGER,
            credit_account_id INTEGER,
            image_path TEXT,
            status TEXT CHECK(status IN ('draft', 'approved', 'rejected')) DEFAULT 'draft',
            journal_entry_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (debit_account_id) REFERENCES accounts(id),
            FOREIGN KEY (credit_account_id) REFERENCES accounts(id),
            FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE SET NULL
        );

        -- Tabel Pengaturan Sistem
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        );

        -- Tabel Stok per Cabang (Multi-Gudang)
        CREATE TABLE IF NOT EXISTS branch_stocks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            branch_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            stock INTEGER DEFAULT 0,
            min_stock INTEGER DEFAULT 5,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(branch_id, product_id),
            FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        -- Tabel Mutasi / Transfer Stok Antar Cabang
        CREATE TABLE IF NOT EXISTS stock_transfers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transfer_no TEXT UNIQUE NOT NULL,
            from_branch_id INTEGER NOT NULL,
            to_branch_id INTEGER NOT NULL,
            date DATE NOT NULL,
            status TEXT CHECK(status IN ('pending', 'completed', 'cancelled')) DEFAULT 'completed',
            notes TEXT,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (from_branch_id) REFERENCES branches(id),
            FOREIGN KEY (to_branch_id) REFERENCES branches(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS stock_transfer_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transfer_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            qty INTEGER NOT NULL,
            cost_price REAL DEFAULT 0,
            FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        -- Tabel Shift Kasir & Rekonsiliasi Kas (Cash Drawer)
        CREATE TABLE IF NOT EXISTS cashier_shifts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            branch_id INTEGER NOT NULL,
            cashier_id INTEGER NOT NULL,
            shift_date DATE NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME,
            initial_cash REAL DEFAULT 0,
            total_cash_sales REAL DEFAULT 0,
            total_non_cash_sales REAL DEFAULT 0,
            expected_cash REAL DEFAULT 0,
            actual_cash REAL DEFAULT 0,
            cash_difference REAL DEFAULT 0,
            status TEXT CHECK(status IN ('open', 'closed')) DEFAULT 'open',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (cashier_id) REFERENCES users(id)
        );

        -- Tabel Retur Penjualan (Sales Returns)
        CREATE TABLE IF NOT EXISTS sales_returns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            return_no TEXT UNIQUE NOT NULL,
            pos_transaction_id INTEGER NOT NULL,
            branch_id INTEGER NOT NULL,
            cashier_id INTEGER NOT NULL,
            date DATETIME NOT NULL,
            total_refund REAL NOT NULL,
            total_cost_refund REAL DEFAULT 0,
            payment_method TEXT DEFAULT 'Tunai',
            reason TEXT NOT NULL,
            journal_entry_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (pos_transaction_id) REFERENCES pos_transactions(id),
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (cashier_id) REFERENCES users(id),
            FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id)
        );

        CREATE TABLE IF NOT EXISTS sales_return_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sales_return_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            price REAL NOT NULL,
            cost_price REAL DEFAULT 0,
            qty INTEGER NOT NULL,
            subtotal REAL NOT NULL,
            FOREIGN KEY (sales_return_id) REFERENCES sales_returns(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        -- Tabel Master Supplier / Vendor
        CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            contact_person TEXT,
            phone TEXT,
            address TEXT,
            bank_name TEXT,
            bank_account TEXT,
            payment_terms TEXT DEFAULT 'COD',
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabel Jejak Audit & Log Aktivitas Sistem
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            user_name TEXT,
            role TEXT,
            action TEXT NOT NULL,
            module TEXT NOT NULL,
            description TEXT NOT NULL,
            details TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );
        CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at DESC);
        CREATE INDEX IF NOT EXISTS idx_audit_logs_module ON audit_logs(module);
        CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);

        -- Tabel Riwayat Penyesuaian Stok (Opname & Stock Adjustment)
        CREATE TABLE IF NOT EXISTS stock_adjustments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            branch_id INTEGER,
            product_id INTEGER NOT NULL,
            type TEXT CHECK(type IN ('masuk', 'keluar', 'opname', 'rusak')) NOT NULL,
            qty_change INTEGER NOT NULL,
            previous_stock INTEGER NOT NULL,
            new_stock INTEGER NOT NULL,
            cost_impact REAL DEFAULT 0,
            reason TEXT,
            adjusted_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (branch_id) REFERENCES branches(id),
            FOREIGN KEY (adjusted_by) REFERENCES users(id)
        );

        -- Tabel Penguncian Periode Akuntansi (Tutup Buku)
        CREATE TABLE IF NOT EXISTS period_locks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            period_name TEXT NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            closed_at DATETIME NOT NULL,
            closed_by INTEGER,
            total_revenue REAL DEFAULT 0,
            total_expense REAL DEFAULT 0,
            net_profit_loss REAL DEFAULT 0,
            prive_amount REAL DEFAULT 0,
            closing_journal_id INTEGER,
            status TEXT DEFAULT 'locked',
            notes TEXT,
            FOREIGN KEY (closed_by) REFERENCES users(id),
            FOREIGN KEY (closing_journal_id) REFERENCES journal_entries(id)
        );
SQL;
        $db->exec($schema);

        // Ensure optional columns and tables exist if migrating existing DB
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                user_name TEXT,
                role TEXT,
                action TEXT NOT NULL,
                module TEXT NOT NULL,
                description TEXT NOT NULL,
                details TEXT,
                ip_address TEXT,
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_audit_logs_module ON audit_logs(module);
            CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);");

            $cols = $db->query("PRAGMA table_info(pos_transactions)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('discount_amount', $cols)) $db->exec("ALTER TABLE pos_transactions ADD COLUMN discount_amount REAL DEFAULT 0");
            if (!in_array('tax_amount', $cols)) $db->exec("ALTER TABLE pos_transactions ADD COLUMN tax_amount REAL DEFAULT 0");
            $invCols = $db->query("PRAGMA table_info(invoice_scans)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('supplier_id', $invCols)) $db->exec("ALTER TABLE invoice_scans ADD COLUMN supplier_id INTEGER");
        } catch (Exception $e) {}
    }

    private static function seedInitialData(PDO $db): void {
        // 1. Seed Cabang
        $db->exec("INSERT INTO branches (code, name, address, phone) VALUES 
            ('CBG-PST', 'Kantor Pusat & Gudang', 'Jl. Pemuda No. 10, Surabaya', '031-5550101'),
            ('CBG-01', 'Cabang 1 - Surabaya Barat', 'Jl. HR Muhammad No. 45, Surabaya', '031-7340202'),
            ('CBG-02', 'Cabang 2 - Surabaya Timur', 'Jl. MERR Rungkut No. 88, Surabaya', '031-8720303')
        ");

        // 2. Seed Pengguna (Password: akuntan123, kasir123)
        $passAkuntan = password_hash('akuntan123', PASSWORD_DEFAULT);
        $passKasir = password_hash('kasir123', PASSWORD_DEFAULT);

        $stmtUser = $db->prepare("INSERT INTO users (username, password, name, role, branch_id) VALUES (?, ?, ?, ?, ?)");
        $stmtUser->execute(['akuntan', $passAkuntan, 'Budi Santoso, S.Ak', 'akuntan', 1]);
        $stmtUser->execute(['kasir1', $passKasir, 'Siti Rahma (Kasir 1)', 'kasir', 2]);
        $stmtUser->execute(['kasir2', $passKasir, 'Dimas Anggara (Kasir 2)', 'kasir', 3]);

        // 3. Seed Bagan Akun (COA Standar Indonesia)
        $accounts = [
            // Aset
            ['1101', 'Kas Kasir Cabang 1', 'Aset', 'debit'],
            ['1102', 'Kas Kasir Cabang 2', 'Aset', 'debit'],
            ['1103', 'Kas Utama / Bank BCA', 'Aset', 'debit'],
            ['1104', 'Piutang Usaha', 'Aset', 'debit'],
            ['1105', 'Persediaan Bahan Cuci & Perlengkapan', 'Aset', 'debit'],
            ['1106', 'Perlengkapan Operasional & Kantor', 'Aset', 'debit'],
            ['1107', 'PPN Masukan (Pajak Dibayar di Muka)', 'Aset', 'debit'],
            ['1201', 'Mesin Cuci, Dryer & Peralatan Laundry', 'Aset', 'debit'],
            ['1202', 'Akumulasi Penyusutan Peralatan', 'Aset', 'credit'],
            
            // Liabilitas
            ['2101', 'Utang Usaha / Supplier', 'Liabilitas', 'credit'],
            ['2102', 'Utang PPN / Pajak', 'Liabilitas', 'credit'],
            ['2103', 'Utang Gaji Karyawan', 'Liabilitas', 'credit'],
            ['2104', 'Utang Beban / Beban Akrual', 'Liabilitas', 'credit'],

            // Ekuitas
            ['3101', 'Modal Pemilik', 'Ekuitas', 'credit'],
            ['3102', 'Prive Pemilik (Pengambilan Pribadi)', 'Ekuitas', 'debit'],
            ['3201', 'Laba Ditahan', 'Ekuitas', 'credit'],
            ['3301', 'Ikhtisar Laba Rugi', 'Ekuitas', 'credit'],

            // Pendapatan
            ['4101', 'Pendapatan Jasa Laundry', 'Pendapatan', 'credit'],
            ['4102', 'Pendapatan Penjualan Retail & Lain-lain', 'Pendapatan', 'credit'],
            ['4103', 'Retur & Kompensasi Jasa', 'Pendapatan', 'debit'],
            ['4104', 'Diskon & Promosi Laundry', 'Pendapatan', 'debit'],

            // Beban Pokok
            ['5101', 'Beban Pokok Layanan Laundry (Bahan & Chemical)', 'Beban_Pokok', 'debit'],

            // Beban Operasional
            ['6101', 'Beban Gaji Karyawan & Operator', 'Beban_Operasional', 'debit'],
            ['6102', 'Beban Listrik, Air Bersih & Gas Boiler', 'Beban_Operasional', 'debit'],
            ['6103', 'Beban Sewa Outlet / Cabang', 'Beban_Operasional', 'debit'],
            ['6104', 'Beban Operasional & Perlengkapan', 'Beban_Operasional', 'debit'],
            ['6105', 'Beban Pemeliharaan & Servis Mesin', 'Beban_Operasional', 'debit'],
            ['6106', 'Beban Penyusutan Peralatan Laundry', 'Beban_Operasional', 'debit'],
            ['6107', 'Beban Penyesuaian / Selisih Persediaan', 'Beban_Operasional', 'debit']
        ];

        $stmtAcc = $db->prepare("INSERT INTO accounts (code, name, category, normal_balance) VALUES (?, ?, ?, ?)");
        foreach ($accounts as $acc) {
            $stmtAcc->execute($acc);
        }

        // 4. Seed Layanan & Produk Laundry
        $products = [
            ['LND-001', 'Cuci Kering Setrika (Reguler 2 Hari)', 'Laundry Kiloan', 8000, 2500, 999, 'kg'],
            ['LND-002', 'Cuci Kering Setrika (Express 1 Hari)', 'Laundry Kiloan', 12000, 3500, 999, 'kg'],
            ['LND-003', 'Cuci Kering Setrika (Kilat 3-4 Jam)', 'Laundry Kiloan', 18000, 5000, 999, 'kg'],
            ['LND-004', 'Cuci Kering Lipat (Non-Setrika)', 'Laundry Kiloan', 6000, 1800, 999, 'kg'],
            ['LND-005', 'Setrika Uap Saja (Steam Press)', 'Laundry Kiloan', 5000, 1200, 999, 'kg'],
            ['LND-006', 'Bedcover Jumbo / King Size', 'Laundry Satuan', 35000, 9000, 100, 'pcs'],
            ['LND-007', 'Bedcover Single', 'Laundry Satuan', 25000, 7000, 100, 'pcs'],
            ['LND-008', 'Selimut Tebal / Quilt', 'Laundry Satuan', 20000, 5500, 100, 'pcs'],
            ['LND-009', 'Cuci Sepatu Sneaker / Canvas', 'Laundry Satuan', 30000, 8000, 150, 'pasang'],
            ['LND-010', 'Cuci Helm Full Face / Half Face', 'Laundry Satuan', 25000, 7000, 100, 'pcs'],
            ['LND-011', 'Cuci Karpet Tebal', 'Laundry Satuan', 15000, 4000, 200, 'meter'],
            ['LND-012', 'Jas Formal / Blazer', 'Dry Cleaning', 35000, 10000, 80, 'pcs'],
            ['LND-013', 'Gaun / Kebaya Pesta', 'Dry Cleaning', 45000, 12000, 60, 'pcs'],
            ['LND-014', 'Gordyn / Tirai Tebal', 'Dry Cleaning', 15000, 4500, 150, 'meter'],
            ['LND-015', 'Parfum Laundry Premium 250ml', 'Bahan & Perlengkapan', 25000, 14000, 50, 'botol'],
            ['LND-016', 'Deterjen Cair Ramah Lingkungan 1L', 'Bahan & Perlengkapan', 28000, 16000, 50, 'botol']
        ];

        $stmtPrd = $db->prepare("INSERT INTO products (code, name, category, price, cost_price, stock, unit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $prd) {
            $stmtPrd->execute($prd);
            $pId = $db->lastInsertId();
            // Seed stok cabang awal
            $bStock = ($prd[6] === 'kg') ? 999 : max(10, floor($prd[5]/2));
            $db->exec("INSERT INTO branch_stocks (branch_id, product_id, stock) VALUES (1, $pId, {$prd[5]}), (2, $pId, $bStock), (3, $pId, $bStock)");
        }

        // 5. Seed Modal Awal ke Jurnal Umum (Contoh Saldo Awal)
        $accBankId = $db->query("SELECT id FROM accounts WHERE code = '1103' LIMIT 1")->fetchColumn() ?: 3;
        $accModalId = $db->query("SELECT id FROM accounts WHERE code = '3101' LIMIT 1")->fetchColumn() ?: 11;

        $db->exec("INSERT INTO journal_entries (branch_id, transaction_no, date, description, ref_type, created_by) 
            VALUES (1, 'JRN-INIT-001', date('now', '-7 days'), 'Setoran Modal Awal Pemilik', 'manual', 1)");
        $initJrnId = $db->lastInsertId();
        
        // Debit Kas Bank 50jt, Kredit Modal Pemilik 50jt
        $stmtItem = $db->prepare("INSERT INTO journal_items (journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?)");
        $stmtItem->execute([$initJrnId, $accBankId, 50000000, 0, 'Setoran Kas Bank']);
        $stmtItem->execute([$initJrnId, $accModalId, 0, 50000000, 'Modal Pemilik']);

        // 6. Seed Supplier Awal
        $db->exec("INSERT INTO suppliers (code, name, contact_person, phone, address, bank_name, bank_account, payment_terms) VALUES 
            ('SPL-001', 'CV Berkah Chemical Laundry', 'Hendra Wijaya', '081234567890', 'Kawasan Industri Rungkut, Surabaya', 'BCA', '1420987654', 'Net 14'),
            ('SPL-002', 'PT Surya Jaya Mandiri Plastik & Hanger', 'Budi Santoso', '081398765432', 'Jl. Kalibutuh No. 25, Surabaya', 'Mandiri', '1410012345678', 'Net 30'),
            ('SPL-003', 'Toko Mitra Sparepart Mesin & Boiler', 'Ibu Ratna', '082155667788', 'Jl. Pasar Besar No. 12, Surabaya', 'BCA', '2160998877', 'COD')
        ");

        // Seed Default Gemini API Key setting
        $db->exec("INSERT INTO settings (key, value) VALUES ('gemini_api_key', '')");
    }
}
