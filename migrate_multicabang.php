<?php
$dbPath = __DIR__ . '/writable/database.db';
$db = new SQLite3($dbPath);
$db->busyTimeout(5000);

echo "Starting Multi-Cabang & Audit Trail Migration...\n";

// 1. Tabel Cabang
$db->exec("
CREATE TABLE IF NOT EXISTS cabang (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode_cabang VARCHAR(20) NOT NULL UNIQUE,
    nama_cabang VARCHAR(100) NOT NULL,
    alamat TEXT,
    kota VARCHAR(100),
    telepon VARCHAR(50),
    footer_struk TEXT,
    is_pusat INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// Seed Cabang 1 & 2 jika belum ada
$countCabang = $db->querySingle("SELECT COUNT(*) FROM cabang");
if ($countCabang == 0) {
    $db->exec("
    INSERT INTO cabang (id, kode_cabang, nama_cabang, alamat, kota, telepon, footer_struk, is_pusat, status)
    VALUES 
    (1, 'CBG-01', 'KSP Cileungsi (Pusat)', 'Jl. Metland Cileungsi, Blok BD.04, No.17', 'Cileungsi - Bogor', '0857-1115-9296', 'Terima Kasih Atas Kunjungan Anda di Cabang Pusat Cileungsi!', 1, 'Aktif'),
    (2, 'CBG-02', 'KSP Cibubur (Cabang 2)', 'Jl. Alternatif Cibubur No. 88', 'Cibubur - Jakarta Timur', '0812-8707-1352', 'Terima Kasih Atas Kunjungan Anda di Outlet Cabang Cibubur!', 0, 'Aktif');
    ");
    echo "Inserted initial branches: Cabang 1 & Cabang 2.\n";
}

// 2. Tabel Stok Cabang
$db->exec("
CREATE TABLE IF NOT EXISTS stok_cabang (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_barang VARCHAR(50) NOT NULL,
    id_cabang INTEGER NOT NULL,
    stok REAL DEFAULT 0,
    min_stok REAL DEFAULT 5,
    lokasi_rak VARCHAR(50) DEFAULT 'Rak Utama',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_barang, id_cabang)
);
");

// Ambil semua barang ke memory
$barangList = [];
$bRes = $db->query("SELECT id_barang, stok, min_stok FROM barang");
while ($row = $bRes->fetchArray(SQLITE3_ASSOC)) {
    $barangList[] = $row;
}
unset($bRes);

// Transaksi batch insert untuk stok cabang
$db->exec("BEGIN TRANSACTION;");
foreach ($barangList as $b) {
    $idB = SQLite3::escapeString($b['id_barang']);
    $stok1 = (float)$b['stok'];
    $minStok = (float)($b['min_stok'] ?: 5);

    // Cabang 1 (Pusat)
    $db->exec("INSERT OR IGNORE INTO stok_cabang (id_barang, id_cabang, stok, min_stok) VALUES ('$idB', 1, $stok1, $minStok)");
    
    // Cabang 2 (Contoh stok pembagian awal: 30% dari pusat)
    $stok2 = max(5, round($stok1 * 0.3));
    $db->exec("INSERT OR IGNORE INTO stok_cabang (id_barang, id_cabang, stok, min_stok) VALUES ('$idB', 2, $stok2, $minStok)");
}
$db->exec("COMMIT;");
echo "Migrated stock to stok_cabang table for " . count($barangList) . " products.\n";

// 3. Tabel Transfer Stok Antar Cabang
$db->exec("
CREATE TABLE IF NOT EXISTS transfer_stok (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    no_transfer VARCHAR(50) NOT NULL UNIQUE,
    tanggal DATETIME NOT NULL,
    id_cabang_asal INTEGER NOT NULL,
    id_cabang_tujuan INTEGER NOT NULL,
    total_item INTEGER DEFAULT 0,
    total_qty REAL DEFAULT 0,
    status VARCHAR(30) DEFAULT 'Dikirim',
    catatan TEXT,
    kode_user_pengirim VARCHAR(50),
    nama_user_pengirim VARCHAR(100),
    kode_user_penerima VARCHAR(50),
    nama_user_penerima VARCHAR(100),
    tgl_terima DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transfer_stok_detail (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transfer_id INTEGER NOT NULL,
    id_barang VARCHAR(50) NOT NULL,
    nama_barang VARCHAR(150),
    satuan VARCHAR(50),
    qty REAL NOT NULL,
    qty_terima REAL DEFAULT 0,
    catatan TEXT,
    FOREIGN KEY (transfer_id) REFERENCES transfer_stok(id) ON DELETE CASCADE
);
");
echo "Created transfer_stok and transfer_stok_detail tables.\n";

// 4. Tabel Audit Trail
$db->exec("
CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cabang INTEGER DEFAULT NULL,
    nama_cabang VARCHAR(100) DEFAULT NULL,
    user_id INTEGER DEFAULT NULL,
    kode_user VARCHAR(50) DEFAULT NULL,
    nama_user VARCHAR(100) DEFAULT NULL,
    role VARCHAR(50) DEFAULT NULL,
    aksi VARCHAR(50) NOT NULL,
    modul VARCHAR(50) NOT NULL,
    deskripsi TEXT NOT NULL,
    data_lama TEXT DEFAULT NULL,
    data_baru TEXT DEFAULT NULL,
    ip_address VARCHAR(50) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");
echo "Created audit_logs table for comprehensive audit trail.\n";

// 5. Tambahkan kolom pendukung ke tabel-tabel utama jika belum ada
function addColumnIfNotExists($db, $table, $column, $definition) {
    $res = $db->query("PRAGMA table_info($table)");
    $exists = false;
    while ($col = $res->fetchArray(SQLITE3_ASSOC)) {
        if ($col['name'] === $column) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $db->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        echo "Added column $column to $table.\n";
    }
}

addColumnIfNotExists($db, 'users', 'id_cabang', 'INTEGER DEFAULT 1');
addColumnIfNotExists($db, 'users', 'can_switch_cabang', 'INTEGER DEFAULT 1');
addColumnIfNotExists($db, 'penjualan', 'id_cabang', 'INTEGER DEFAULT 1');
addColumnIfNotExists($db, 'pembelian', 'id_cabang', 'INTEGER DEFAULT 1');
addColumnIfNotExists($db, 'data_keuangan', 'id_cabang', 'INTEGER DEFAULT 1');

// Update data eksisting agar terkait ke Cabang 1 (Pusat)
$db->exec("UPDATE users SET id_cabang = 1 WHERE id_cabang IS NULL OR id_cabang = 0");
$db->exec("UPDATE penjualan SET id_cabang = 1 WHERE id_cabang IS NULL OR id_cabang = 0");
$db->exec("UPDATE pembelian SET id_cabang = 1 WHERE id_cabang IS NULL OR id_cabang = 0");
$db->exec("UPDATE data_keuangan SET id_cabang = 1 WHERE id_cabang IS NULL OR id_cabang = 0");

echo "Multi-Cabang & Audit Trail database setup completed successfully!\n";
