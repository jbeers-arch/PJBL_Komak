# PJBL Komak - Sistem Informasi Akuntansi (SIA) & Kasir POS Multi-Cabang

Sistem Informasi Akuntansi (SIA) berbasis web modern yang terintegrasi dengan **Kasir Multi-Cabang (Offline-First)** dan **Otomatisasi Penjurnalan Berbasis AI (Gemini Vision OCR)**.

---

## 🌟 Fitur Utama

### 1. Sistem Informasi Akuntansi (SIA) Terpadu
* **Bagan Akun (Chart of Accounts - COA):** Pengelompokan akun standar Indonesia (1-Aset, 2-Liabilitas, 3-Ekuitas, 4-Pendapatan, 5-HPP, 6-Beban Operasional).
* **Jurnal Umum (General Journal):** Sistem pencatatan berpasangan (*Double-Entry Bookkeeping*) dengan validasi otomatis agar Debit dan Kredit selalu seimbang.
* **Buku Besar (General Ledger):** Perhitungan saldo awal, mutasi kronologis, dan saldo berjalan (*running balance*) otomatis.
* **Laporan Keuangan Real-time:**
  * **Laporan Laba Rugi (Income Statement):** Pendapatan Usaha - HPP = Laba Kotor - Beban Operasional = Laba Bersih.
  * **Laporan Neraca (Balance Sheet):** Aset = Liabilitas + Ekuitas (termasuk Laba Periode Berjalan) dengan indikator keseimbangan (*balance indicator*).
  * Filter laporan konsolidasian (semua cabang) atau per cabang spesifik.

### 2. Kasir POS Multi-Cabang (Offline-First)
* **Tetap Bisa Jualan Tanpa Internet:** Kasir toko tetap bisa bertransaksi meskipun kabel LAN dicabut atau WiFi terputus. Data transaksi tersimpan aman di database lokal browser kasir (**IndexedDB**).
* **Simulasi Offline Switch:** Dilengkapi tombol toggle *"Simulasi Offline"* untuk mendemokan kemampuan offline kasir secara langsung.
* **Auto-Sync ke Pusat:** Begitu internet kembali online, antrean transaksi offline otomatis ter-upload ke server pusat di latar belakang (*background worker*).
* **Auto-Journaling Kasir:** Setiap transaksi kasir otomatis membuat jurnal akuntansi di server pusat:
  * *(D) Kas Kasir Cabang X*
  * *(K) Pendapatan Penjualan Kasir*
  * *(D) Beban Pokok Penjualan (HPP)*
  * *(K) Persediaan Barang Dagang*
* **Cetak Struk Thermal:** Cetak struk belanja 80mm langsung dari browser.

### 3. AI Scan Faktur & Auto-Journaling (Gemini Vision OCR)
* **Ekstraksi Dokumen Otomatis:** Cukup upload foto/scan faktur, nota pembelian bahan baku, struk bensin, atau tagihan listrik.
* **Analisis AI:** Menggunakan **Google Gemini Vision API** untuk mengekstrak Nama Vendor, Nomor Faktur, Tanggal, Subtotal, Pajak/PPN, dan Total Bayar.
* **Rekomendasi Jurnal Otomatis:** AI merekomendasikan akun debit (misal: *Beban Perlengkapan*, *Persediaan*, atau *Listrik*) dan akun kredit (*Kas Utama* atau *Utang Usaha*).
* **1-Click Posting:** Akuntan cukup memeriksa (*review*) dan klik satu tombol untuk mempostingnya langsung ke Jurnal Umum.
* **Smart Simulator:** Tetap bisa mendemokan scanning faktur meskipun belum memasukkan Gemini API Key.

---

## 👥 Akun Login Demo (Tersedia Tombol 1-Klik di Halaman Login)

| Peran | Username | Password | Hak Akses & Tugas |
| :--- | :--- | :--- | :--- |
| **Akuntan / Owner (Pusat)** | `akuntan` | `akuntan123` | Akses penuh: Dashboard Pusat, Monitoring Semua Cabang, COA, Jurnal Umum, Buku Besar, Laporan Keuangan, Scan Faktur AI, Manajemen Cabang. |
| **Kasir Cabang 1** | `kasir1` | `kasir123` | Akses Kasir POS Cabang 1 (Surabaya Barat), transaksi offline & online, riwayat kasir lokal. |
| **Kasir Cabang 2** | `kasir2` | `kasir123` | Akses Kasir POS Cabang 2 (Surabaya Timur), transaksi offline & online, riwayat kasir lokal. |

---

## 🚀 Cara Menjalankan Proyek

### Opsi A: Lewat XAMPP (Rekomendasi)
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik **Start** pada modul **Apache**.
3. Buka browser dan kunjungi:
   ```
   http://localhost/PJBL_Komak
   ```
4. Database SQLite akan **otomatis dibuat dan diisi data awal** (*auto-migrate & seed*) pada kunjungan pertama!

### Opsi B: Lewat Terminal Built-in PHP
Jika tidak membuka Apache XAMPP, buka terminal PowerShell dan jalankan:
```powershell
cd C:\xamppv2\htdocs\PJBL_Komak
php -S localhost:8080
```
Lalu buka browser di: `http://localhost:8080`.

---

## 📁 Struktur Direktori Proyek

```
C:\xamppv2\htdocs\PJBL_Komak\
├── api\
│   ├── auth.php              # Login, logout, session check
│   ├── branches.php          # Manajemen cabang & omset
│   ├── accounts.php          # Bagan Akun (COA)
│   ├── products.php          # Master data produk kasir
│   ├── pos_sync.php          # Sync transaksi kasir offline & auto-jurnal
│   ├── scan_invoice.php      # AI Gemini Vision scan faktur & auto-draft
│   ├── journals.php          # Jurnal umum (double entry) & validasi balance
│   ├── ledger.php            # Buku besar & saldo berjalan
│   ├── reports.php           # Laba Rugi & Neraca
│   └── settings.php          # Pengaturan Gemini API Key
├── assets\
│   └── js\
│       └── pos-offline.js    # Engine Offline-First IndexedDB kasir
├── config\
│   ├── config.php            # Konfigurasi aplikasi & base URL
│   └── database.php          # Koneksi SQLite & auto-migrasi skema/seeder
├── database\
│   └── pjbl_komak.sqlite     # Database lokal (terbuat otomatis)
├── includes\
│   ├── header.php            # Header, status badge koneksi & role
│   ├── sidebar.php           # Sidebar navigasi dinamis berbasis role
│   └── footer.php            # Toast notifications & scripts
├── uploads\
│   └── invoices\             # Foto faktur yang diupload
├── views\
│   ├── login.php             # Login dengan tombol 1-klik akun demo
│   ├── dashboard.php         # Dashboard monitoring multi-cabang (Akuntan)
│   ├── pos.php               # Kasir POS Offline-First (Kasir & Akuntan)
│   ├── scan-faktur.php       # AI Scan Faktur & Auto-Jurnal (Akuntan)
│   ├── coa.php               # Bagan Akun / COA (Akuntan)
│   ├── jurnal.php            # Jurnal Umum (Akuntan)
│   ├── buku-besar.php        # Buku Besar (Akuntan)
│   ├── laporan.php           # Laporan Laba Rugi & Neraca (Akuntan)
│   └── cabang.php            # Manajemen Cabang & Kasir (Akuntan)
├── index.php                 # Root router pengarah halaman
└── README.md                 # Dokumentasi proyek
```

---

## 🤝 Panduan Kolaborasi Kelompok (Git & CI/CD)

Repositori ini telah terintegrasi dengan **GitHub Actions CI/CD** untuk deployment otomatis ke hosting InfinityFree:

* **URL Hosting Produksi**: [http://pjbl-komak.xo.je/](http://pjbl-komak.xo.je/)
* **Repositori GitHub**: [https://github.com/jbeers-arch/PJBL_Komak](https://github.com/jbeers-arch/PJBL_Komak)

### Alur Kerja Kolaborasi Kelompok:
1. **Undang Anggota Kelompok**:
   Owner repositori (`jbeers-arch`) dapat mengundang anggota kelompok melalui menu:
   `Settings` > `Collaborators` > `Add people` di GitHub.

2. **Clone Repositori ke Laptop Masing-Masing**:
   Buka terminal di folder `C:\xampp\htdocs\` (atau direktori web server masing-masing):
   ```bash
   git clone https://github.com/jbeers-arch/PJBL_Komak.git
   ```

3. **Membuat Branch Fitur Sebelum Mengerjakan Tugas**:
   Agar pekerjaan antar anggota tidak saling bentrok, biasakan membuat branch baru:
   ```bash
   git checkout -b fitur-nama-anggota
   ```

4. **Kirim Perubahan (Push & Pull Request)**:
   ```bash
   git add .
   git commit -m "feat: deskripsi tugas yang dikerjakan"
   git push origin fitur-nama-anggota
   ```
   Buka GitHub dan buat **Pull Request (PR)** ke branch `main`.

5. **Deployment Otomatis ke InfinityFree**:
   Setiap kali ada commit yang digabungkan (*merged*) ke branch `main`, GitHub Actions akan **secara otomatis mengunggah perubahan ke hosting InfinityFree** tanpa perlu upload manual melalui FileZilla.

