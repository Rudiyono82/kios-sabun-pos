<?php

/**
 * Script Migrasi & Sinkronisasi Otomatis dari SQLite (writable/database.db) ke MySQL di Railway.
 * Berjalan otomatis saat container web-pos booting.
 */

$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$port = (int)(getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306));
$user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$pass = getenv('DB_PASSWORD') ?: (getenv('MYSQLPASSWORD') ?: '');
$dbName = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'railway');

echo "[MIGRATION] Checking MySQL target: {$host}:{$port}, Database: {$dbName}, User: {$user}...\n";

// 1. Hubungkan ke MySQL dengan retry loop
$pdo = null;
$maxRetries = 15;
for ($i = 1; $i <= $maxRetries; $i++) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 10
        ]);
        echo "[MIGRATION] Connected to MySQL successfully on attempt #{$i}!\n";
        break;
    } catch (Exception $e) {
        echo "[MIGRATION] Attempt #{$i} failed to connect to MySQL ({$e->getMessage()}). Retrying in 3 seconds...\n";
        sleep(3);
    }
}

if (!$pdo) {
    echo "[MIGRATION WARNING] Could not connect to MySQL after {$maxRetries} attempts. Skipping migration.\n";
    exit(0);
}

// 2. Buka SQLite database lokal
$sqlitePath = __DIR__ . '/../writable/database.db';
if (!file_exists($sqlitePath)) {
    echo "[MIGRATION WARNING] SQLite database not found at {$sqlitePath}. Nothing to migrate.\n";
    exit(0);
}

$sqlite = new SQLite3($sqlitePath);
echo "[MIGRATION] Opened SQLite database at {$sqlitePath}.\n";

// Helper DDL converter
function sqliteToMysqlDdl(string $sqliteSql): string
{
    $ddl = preg_replace('/^CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sqliteSql);
    $ddl = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]+\)\s*REFERENCES\s+[^,\)]+\([^)]+\)(\s+ON\s+(DELETE|UPDATE)\s+[A-Z]+)*/i', '', $ddl);
    $ddl = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'BIGINT AUTO_INCREMENT PRIMARY KEY', $ddl);
    $ddl = preg_replace('/VARCHAR(?!\s*\()/i', 'VARCHAR(255)', $ddl);
    $ddl = preg_replace('/DECIMAL(?!\s*\()/i', 'DECIMAL(15,2)', $ddl);
    $ddl = preg_replace('/\bREAL\b/i', 'DOUBLE', $ddl);
    $ddl = str_ireplace('AUTOINCREMENT', 'AUTO_INCREMENT', $ddl);
    $ddl = rtrim($ddl, "; \t\n\r");
    return $ddl . " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
}

function insertBatchIgnore(PDO $pdo, string $tableName, array $rows): void
{
    if (empty($rows)) return;

    $cols = array_keys($rows[0]);
    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
    $colList = implode(', ', $escapedCols);

    $valuesClauses = [];
    $params = [];

    foreach ($rows as $row) {
        $placeholders = [];
        foreach ($cols as $col) {
            $placeholders[] = '?';
            $params[] = $row[$col];
        }
        $valuesClauses[] = '(' . implode(', ', $placeholders) . ')';
    }

    $sql = "INSERT IGNORE INTO `{$tableName}` ({$colList}) VALUES " . implode(', ', $valuesClauses);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function upsertBarangBatch(PDO $pdo, array $rows): void
{
    if (empty($rows)) return;

    $cols = array_keys($rows[0]);
    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
    $colList = implode(', ', $escapedCols);

    $valuesClauses = [];
    $params = [];

    foreach ($rows as $row) {
        $placeholders = [];
        foreach ($cols as $col) {
            $placeholders[] = '?';
            $params[] = $row[$col];
        }
        $valuesClauses[] = '(' . implode(', ', $placeholders) . ')';
    }

    $sql = "INSERT INTO `barang` ({$colList}) VALUES " . implode(', ', $valuesClauses) . "
        ON DUPLICATE KEY UPDATE
            `stok` = VALUES(`stok`),
            `harga_beli` = VALUES(`harga_beli`),
            `harga_jual` = VALUES(`harga_jual`),
            `nama_barang` = VALUES(`nama_barang`),
            `satuan` = VALUES(`satuan`),
            `status` = VALUES(`status`),
            `qty_paket_a` = VALUES(`qty_paket_a`),
            `hrg_paket_a` = VALUES(`hrg_paket_a`),
            `qty_paket_b` = VALUES(`qty_paket_b`),
            `hrg_paket_b` = VALUES(`hrg_paket_b`),
            `qty_paket_c` = VALUES(`qty_paket_c`),
            `hrg_paket_c` = VALUES(`hrg_paket_c`),
            `updated_at` = VALUES(`updated_at`)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function upsertStokCabangBatch(PDO $pdo, array $rows): void
{
    if (empty($rows)) return;

    $cols = array_keys($rows[0]);
    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
    $colList = implode(', ', $escapedCols);

    $valuesClauses = [];
    $params = [];

    foreach ($rows as $row) {
        $placeholders = [];
        foreach ($cols as $col) {
            $placeholders[] = '?';
            $params[] = $row[$col];
        }
        $valuesClauses[] = '(' . implode(', ', $placeholders) . ')';
    }

    $sql = "INSERT INTO `stok_cabang` ({$colList}) VALUES " . implode(', ', $valuesClauses) . "
        ON DUPLICATE KEY UPDATE `stok` = VALUES(`stok`)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

// 3. Proses Impor & Sinkronisasi Data Per-Tabel
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

    $tablesRes = $sqlite->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'test'");
    
    $tableList = [];
    while ($r = $tablesRes->fetchArray(SQLITE3_ASSOC)) {
        $tableList[] = $r;
    }

    echo "[MIGRATION] Found " . count($tableList) . " tables in SQLite to verify/sync.\n";

    foreach ($tableList as $tInfo) {
        $tableName = $tInfo['name'];
        $sqliteSql = $tInfo['sql'];

        if (empty($sqliteSql)) {
            continue;
        }

        try {
            // 3a. Buat tabel jika belum ada di MySQL
            $mysqlDdl = sqliteToMysqlDdl($sqliteSql);
            try {
                $pdo->exec($mysqlDdl);
            } catch (Exception $ddlErr) {
                // Table might already exist
            }

            // 3b. Cek jumlah baris di MySQL vs SQLite
            $mysqlRows = 0;
            try {
                $mysqlRows = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}`")->fetchColumn();
            } catch (Exception $e) {
                $mysqlRows = 0;
            }

            $sqliteRows = (int)$sqlite->querySingle("SELECT COUNT(*) FROM `{$tableName}`");

            // KASUS 1: Tabel barang -> Selalu sync stok & harga terbaru (UPSERT)
            if ($tableName === 'barang') {
                echo "[MIGRATION] Syncing master `barang` (stok & harga) to MySQL...\n";
                $dataRes = $sqlite->query("SELECT * FROM `barang`");
                $batch = [];
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    $batch[] = $row;
                    if (count($batch) >= 250) {
                        upsertBarangBatch($pdo, $batch);
                        $batch = [];
                    }
                }
                if (count($batch) > 0) {
                    upsertBarangBatch($pdo, $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `barang` synced successfully ($sqliteRows products).\n";
                continue;
            }

            // KASUS 2: Tabel stok_cabang -> Selalu sync stok cabang
            if ($tableName === 'stok_cabang') {
                echo "[MIGRATION] Syncing `stok_cabang` to MySQL...\n";
                $dataRes = $sqlite->query("SELECT * FROM `stok_cabang`");
                $batch = [];
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    $batch[] = $row;
                    if (count($batch) >= 250) {
                        upsertStokCabangBatch($pdo, $batch);
                        $batch = [];
                    }
                }
                if (count($batch) > 0) {
                    upsertStokCabangBatch($pdo, $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `stok_cabang` synced successfully.\n";
                continue;
            }

            // KASUS 3: Tabel Penjualan (Master Transaksi) -> Insert transaksi baru
            if ($tableName === 'penjualan') {
                echo "[MIGRATION] Checking `penjualan` transactions ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                $existingFaktur = [];
                $stF = $pdo->query("SELECT no_faktur FROM `penjualan`");
                while ($f = $stF->fetchColumn()) {
                    $existingFaktur[$f] = true;
                }

                $dataRes = $sqlite->query("SELECT * FROM `penjualan`");
                $batch = [];
                $inserted = 0;
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    if (!isset($existingFaktur[$row['no_faktur']])) {
                        unset($row['id']);
                        $batch[] = $row;
                        $inserted++;
                        if (count($batch) >= 250) {
                            insertBatchIgnore($pdo, 'penjualan', $batch);
                            $batch = [];
                        }
                    }
                }
                if (count($batch) > 0) {
                    insertBatchIgnore($pdo, 'penjualan', $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `penjualan` synced: {$inserted} new transactions inserted into MySQL.\n";
                continue;
            }

            // KASUS 4: Tabel Penjualan Detail -> Insert detail transaksi baru
            if ($tableName === 'penjualan_detail') {
                echo "[MIGRATION] Checking `penjualan_detail` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                $existingFaktur = [];
                $stF = $pdo->query("SELECT DISTINCT no_faktur FROM `penjualan_detail`");
                while ($f = $stF->fetchColumn()) {
                    $existingFaktur[$f] = true;
                }

                $dataRes = $sqlite->query("SELECT * FROM `penjualan_detail`");
                $batch = [];
                $inserted = 0;
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    if (!isset($existingFaktur[$row['no_faktur']])) {
                        unset($row['id']);
                        $batch[] = $row;
                        $inserted++;
                        if (count($batch) >= 250) {
                            insertBatchIgnore($pdo, 'penjualan_detail', $batch);
                            $batch = [];
                        }
                    }
                }
                if (count($batch) > 0) {
                    insertBatchIgnore($pdo, 'penjualan_detail', $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `penjualan_detail` synced: {$inserted} product rows inserted into MySQL.\n";
                continue;
            }

            // KASUS 5: Tabel Data Keuangan -> Insert mutasi kas baru & Sinkronisasi Kas Awal Resmi
            if ($tableName === 'data_keuangan') {
                echo "[MIGRATION] Checking & syncing `data_keuangan` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                $existingKeys = [];
                $stK = $pdo->query("SELECT tanggal, jenis_keuangan, nama_keuangan, total FROM `data_keuangan`");
                while ($rk = $stK->fetch()) {
                    $kKey = $rk['tanggal'] . '|' . $rk['jenis_keuangan'] . '|' . $rk['nama_keuangan'] . '|' . (float)$rk['total'];
                    $existingKeys[$kKey] = true;
                }

                $dataRes = $sqlite->query("SELECT * FROM `data_keuangan`");
                $batch = [];
                $inserted = 0;
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    $kKey = $row['tanggal'] . '|' . $row['jenis_keuangan'] . '|' . $row['nama_keuangan'] . '|' . (float)$row['total'];
                    if (!isset($existingKeys[$kKey])) {
                        unset($row['id']);
                        $batch[] = $row;
                        $inserted++;
                        if (count($batch) >= 250) {
                            insertBatchIgnore($pdo, 'data_keuangan', $batch);
                            $batch = [];
                        }
                    }
                }
                if (count($batch) > 0) {
                    insertBatchIgnore($pdo, 'data_keuangan', $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `data_keuangan` synced: {$inserted} new cash records inserted into MySQL.\n";

                // SINKRONISASI KAS AWAL RESMI DARI ACCESS/SQLITE UNTUK HARI INI
                $kasAwalSqliteRow = $sqlite->querySingle("SELECT tanggal, total, kode_user FROM data_keuangan WHERE DATE(tanggal) = '2026-11-23' AND (jenis_keuangan = 'Kas Awal' OR nama_keuangan LIKE '%Kas Awal%') AND total > 0 ORDER BY id DESC LIMIT 1", true);

                if (!empty($kasAwalSqliteRow) && (float)$kasAwalSqliteRow['total'] > 0) {
                    $nominalResmi = (float)$kasAwalSqliteRow['total'];
                    $tglResmi = $kasAwalSqliteRow['tanggal'] ?: '2026-11-23 06:53:09';
                    $userResmi = $kasAwalSqliteRow['kode_user'] ?: 'SYSTEM';
                    $todayStr = '2026-11-23';

                    echo "[MIGRATION] Ensuring Kas Awal for {$todayStr} in MySQL is exactly Rp " . number_format($nominalResmi, 0, ',', '.') . "...\n";

                    $stCheck = $pdo->prepare("SELECT id, total, nama_keuangan FROM data_keuangan WHERE DATE(tanggal) = ? AND (jenis_keuangan = 'Kas Awal' OR nama_keuangan LIKE '%Kas Awal%') ORDER BY id ASC");
                    $stCheck->execute([$todayStr]);
                    $kasAwalMysqlList = $stCheck->fetchAll();

                    if (!empty($kasAwalMysqlList)) {
                        $firstId = $kasAwalMysqlList[0]['id'];
                        $stUp = $pdo->prepare("UPDATE data_keuangan SET total = ?, jenis_keuangan = 'Kas Awal', nama_keuangan = 'Uang Kas Awal', kode_user = ? WHERE id = ?");
                        $stUp->execute([$nominalResmi, $userResmi, $firstId]);
                        echo "[MIGRATION] Updated Kas Awal ID #{$firstId} to Rp " . number_format($nominalResmi, 0, ',', '.') . ".\n";

                        // Hapus baris kas awal duplikat (misal input coba-coba 1.000)
                        if (count($kasAwalMysqlList) > 1) {
                            for ($idx = 1; $idx < count($kasAwalMysqlList); $idx++) {
                                $dupId = $kasAwalMysqlList[$idx]['id'];
                                $pdo->exec("DELETE FROM data_keuangan WHERE id = {$dupId}");
                                echo "[MIGRATION] Cleaned up duplicate Kas Awal ID #{$dupId}.\n";
                            }
                        }
                    } else {
                        $stIns = $pdo->prepare("INSERT INTO data_keuangan (tanggal, jenis_keuangan, nama_keuangan, total, keterangan, kode_user, is_locked, id_cabang, tipe_pengeluaran) VALUES (?, 'Kas Awal', 'Uang Kas Awal', ?, 'Modal Kas Awal Toko (Sync Access)', ?, 0, 1, 'toko')");
                        $stIns->execute([$tglResmi, $nominalResmi, $userResmi]);
                        echo "[MIGRATION] Inserted official Kas Awal Rp " . number_format($nominalResmi, 0, ',', '.') . " into MySQL.\n";
                    }
                }
                continue;
            }

            // KASUS 6: Tabel Pembelian -> Insert pembelian baru
            if ($tableName === 'pembelian') {
                echo "[MIGRATION] Checking `pembelian` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                $existingBeli = [];
                $stB = $pdo->query("SELECT no_beli FROM `pembelian`");
                while ($b = $stB->fetchColumn()) {
                    $existingBeli[$b] = true;
                }

                $dataRes = $sqlite->query("SELECT * FROM `pembelian`");
                $batch = [];
                $inserted = 0;
                $pdo->beginTransaction();
                while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                    if (!isset($existingBeli[$row['no_beli']])) {
                        unset($row['id']);
                        $batch[] = $row;
                        $inserted++;
                        if (count($batch) >= 250) {
                            insertBatchIgnore($pdo, 'pembelian', $batch);
                            $batch = [];
                        }
                    }
                }
                if (count($batch) > 0) {
                    insertBatchIgnore($pdo, 'pembelian', $batch);
                }
                $pdo->commit();
                echo "[MIGRATION] `pembelian` synced: {$inserted} new purchases inserted into MySQL.\n";

                // Sync status_bayar dari SQLite ke MySQL untuk nota pembelian terbaru
                try {
                    $updStmt = $pdo->prepare("UPDATE `pembelian` SET `status_bayar` = :status WHERE `no_beli` = :no_beli");
                    $recentBeli = $sqlite->query("SELECT no_beli, status_bayar FROM `pembelian` ORDER BY id DESC LIMIT 50");
                    while ($rb = $recentBeli->fetchArray(SQLITE3_ASSOC)) {
                        $updStmt->execute([':status' => $rb['status_bayar'], ':no_beli' => $rb['no_beli']]);
                    }
                    $pdo->exec("DELETE FROM `data_keuangan` WHERE `nama_keuangan` IN (SELECT CONCAT('Belanja Restock ', no_beli) FROM `pembelian` WHERE `status_bayar` IN ('Lunas (Transfer)', 'Lunas (Tidak Potong Kas)', 'Hutang / Tempo'))");
                    echo "[MIGRATION] Synchronized status_bayar for recent `pembelian` in MySQL.\n";
                } catch (Exception $eBeli) {
                    // Silently ignore if table format differs
                }
                continue;
            }

            // KASUS 7: Tabel Pembelian Detail -> Insert rincian pembelian baru
            if ($tableName === 'pembelian_detail') {
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `pembelian_detail` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                    $existingBeli = [];
                    $stB = $pdo->query("SELECT DISTINCT no_beli FROM `pembelian_detail`");
                    while ($b = $stB->fetchColumn()) {
                        $existingBeli[$b] = true;
                    }

                    $dataRes = $sqlite->query("SELECT * FROM `pembelian_detail`");
                    $batch = [];
                    $inserted = 0;
                    $pdo->beginTransaction();
                    while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                        if (!isset($existingBeli[$row['no_beli']])) {
                            unset($row['id']);
                            $batch[] = $row;
                            $inserted++;
                            if (count($batch) >= 250) {
                                insertBatchIgnore($pdo, 'pembelian_detail', $batch);
                                $batch = [];
                            }
                        }
                    }
                    if (count($batch) > 0) {
                        insertBatchIgnore($pdo, 'pembelian_detail', $batch);
                    }
                    $pdo->commit();
                    echo "[MIGRATION] `pembelian_detail` synced: {$inserted} purchase item rows inserted into MySQL.\n";
                } else {
                    echo "[MIGRATION] `pembelian_detail` is up to date ($mysqlRows rows). Skipped.\n";
                }
                continue;
            }

            // KASUS 8: Tabel Barang Pending Stok -> Sync pending stok baru
            if ($tableName === 'barang_pending_stok') {
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `barang_pending_stok` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
                    $existingIds = [];
                    $stP = $pdo->query("SELECT id FROM `barang_pending_stok`");
                    while ($pid = $stP->fetchColumn()) {
                        $existingIds[$pid] = true;
                    }

                    $dataRes = $sqlite->query("SELECT * FROM `barang_pending_stok`");
                    $batch = [];
                    $inserted = 0;
                    $pdo->beginTransaction();
                    while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                        if (!isset($existingIds[$row['id']])) {
                            $batch[] = $row;
                            $inserted++;
                            if (count($batch) >= 250) {
                                insertBatchIgnore($pdo, 'barang_pending_stok', $batch);
                                $batch = [];
                            }
                        }
                    }
                    if (count($batch) > 0) {
                        insertBatchIgnore($pdo, 'barang_pending_stok', $batch);
                    }
                    $pdo->commit();
                    echo "[MIGRATION] `barang_pending_stok` synced: {$inserted} pending rows inserted into MySQL.\n";
                } else {
                    echo "[MIGRATION] `barang_pending_stok` is up to date ($mysqlRows rows). Skipped.\n";
                }
                continue;
            }

            // KASUS STANDAR: Tabel lainnya (users, member, supplier, retur, audit_logs, dsb)
            if ($mysqlRows > 0) {
                echo "[MIGRATION] Table `{$tableName}` already populated ({$mysqlRows} rows in MySQL). Skipped.\n";
                continue;
            }

            if ($sqliteRows === 0) {
                echo "[MIGRATION] Table `{$tableName}` is empty in SQLite. Ready.\n";
                continue;
            }

            echo "[MIGRATION] Copying {$sqliteRows} rows for `{$tableName}` into MySQL...\n";
            $dataRes = $sqlite->query("SELECT * FROM `{$tableName}`");
            $batch = [];
            $totalInserted = 0;
            $pdo->beginTransaction();

            while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
                $batch[] = $row;
                if (count($batch) >= 250) {
                    insertBatchIgnore($pdo, $tableName, $batch);
                    $totalInserted += count($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                insertBatchIgnore($pdo, $tableName, $batch);
                $totalInserted += count($batch);
                $batch = [];
            }

            $pdo->commit();
            echo "[MIGRATION] Completed `{$tableName}`: {$totalInserted} rows inserted.\n";

        } catch (Exception $tableErr) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "[MIGRATION ERROR on table `{$tableName}`]: " . $tableErr->getMessage() . "\n";
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // Koreksi jam transaksi POS yang sempat tercatat dalam waktu UTC (sebelum jam 15:00 UTC hari ini)
    try {
        $pdo->exec("UPDATE `penjualan` SET tanggal = DATE_ADD(tanggal, INTERVAL 7 HOUR), created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE no_faktur LIKE 'J261121%' AND HOUR(tanggal) <= 15;");
        $pdo->exec("UPDATE `data_keuangan` SET tanggal = DATE_ADD(tanggal, INTERVAL 7 HOUR) WHERE nama_keuangan LIKE '%J261121%' AND HOUR(tanggal) <= 15;");
        $pdo->exec("UPDATE `kartu_stok` SET tanggal = DATE_ADD(tanggal, INTERVAL 7 HOUR) WHERE no_referensi LIKE 'J261121%' AND HOUR(tanggal) <= 15;");
        echo "[MIGRATION] Corrected UTC timestamps to WIB for today's transactions.\n";
    } catch (Exception $e) {
        // ignore
    }

    // Hapus seluruh faktur duplikat hari ini (akibat double submit Enter) dan kembalikan stoknya
    try {
        $duplicates = ['J2611210002', 'J2611210004', 'J2611210006', 'J2611210008'];
        foreach ($duplicates as $dupFaktur) {
            $dup = $pdo->query("SELECT * FROM `penjualan` WHERE no_faktur = '{$dupFaktur}'")->fetch();
            if ($dup) {
                $dtlStmt = $pdo->query("SELECT id_barang, qty FROM `penjualan_detail` WHERE no_faktur = '{$dupFaktur}'");
                while ($dt = $dtlStmt->fetch()) {
                    $cabangId = (int)($dup['id_cabang'] ?? 1);
                    $qty = (float)$dt['qty'];
                    $idB = $dt['id_barang'];
                    $pdo->exec("UPDATE `stok_cabang` SET stok = stok + {$qty} WHERE id_barang = '{$idB}' AND id_cabang = {$cabangId}");
                    $pdo->exec("UPDATE `barang` SET stok = (SELECT COALESCE(SUM(stok), 0) FROM `stok_cabang` WHERE id_barang = '{$idB}') WHERE id_barang = '{$idB}'");
                }
                $pdo->exec("DELETE FROM `penjualan_detail` WHERE no_faktur = '{$dupFaktur}'");
                $pdo->exec("DELETE FROM `data_keuangan` WHERE nama_keuangan LIKE '%{$dupFaktur}%'");
                $pdo->exec("DELETE FROM `kartu_stok` WHERE no_referensi = '{$dupFaktur}'");
                $pdo->exec("DELETE FROM `penjualan` WHERE no_faktur = '{$dupFaktur}'");
                echo "[MIGRATION] Removed duplicate invoice {$dupFaktur} and restored stock.\n";
            }
        }
    } catch (Exception $e) {
        // ignore
    }

    // Hapus seluruh faktur duplikat hari ini (akibat double submit Enter) dan kembalikan stoknya
    try {
        $duplicates = ['J2611210002', 'J2611210004', 'J2611210006', 'J2611210008'];
        foreach ($duplicates as $dupFaktur) {
            $dup = $pdo->query("SELECT * FROM `penjualan` WHERE no_faktur = '{$dupFaktur}'")->fetch();
            if ($dup) {
                $dtlStmt = $pdo->query("SELECT id_barang, qty FROM `penjualan_detail` WHERE no_faktur = '{$dupFaktur}'");
                while ($dt = $dtlStmt->fetch()) {
                    $cabangId = (int)($dup['id_cabang'] ?? 1);
                    $qty = (float)$dt['qty'];
                    $idB = $dt['id_barang'];
                    $pdo->exec("UPDATE `stok_cabang` SET stok = stok + {$qty} WHERE id_barang = '{$idB}' AND id_cabang = {$cabangId}");
                    $pdo->exec("UPDATE `barang` SET stok = (SELECT COALESCE(SUM(stok), 0) FROM `stok_cabang` WHERE id_barang = '{$idB}') WHERE id_barang = '{$idB}'");
                }
                $pdo->exec("DELETE FROM `penjualan_detail` WHERE no_faktur = '{$dupFaktur}'");
                $pdo->exec("DELETE FROM `data_keuangan` WHERE nama_keuangan LIKE '%{$dupFaktur}%'");
                $pdo->exec("DELETE FROM `kartu_stok` WHERE no_referensi = '{$dupFaktur}'");
                $pdo->exec("DELETE FROM `penjualan` WHERE no_faktur = '{$dupFaktur}'");
                echo "[MIGRATION] Removed duplicate invoice {$dupFaktur} and restored stock.\n";
            }
        }
    } catch (Exception $e) {
        // ignore
    }

    // Hapus seluruh catatan rekapan sintetik Total Profit dan Total Omzet dari data_keuangan MySQL
    try {
        $pdo->exec("DELETE FROM `data_keuangan` WHERE `nama_keuangan` LIKE '%Total Profit%' OR `nama_keuangan` LIKE '%Total Omzet%'");
        echo "[MIGRATION] Cleaned synthetic Total Profit and Total Omzet rows from data_keuangan in MySQL.\n";
    } catch (Exception $e) {
        // ignore
    }

    // =========================================================================
    // SINKRONISASI PEMULIHAN KHUSUS TRANSAKSI HARI INI (SAFETY NET J00140708, dsb)
    // =========================================================================
    try {
        $todayTxs = $sqlite->query("SELECT * FROM `penjualan` WHERE DATE(tanggal) = '2026-11-23'");
        while ($tx = $todayTxs->fetchArray(SQLITE3_ASSOC)) {
            $faktur = $tx['no_faktur'];
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `penjualan` WHERE `no_faktur` = ?");
            $chk->execute([$faktur]);
            if ((int)$chk->fetchColumn() === 0) {
                echo "[MIGRATION RECOVERY] Force inserting missing transaction {$faktur} into MySQL...\n";
                unset($tx['id']);
                $cols = array_keys($tx);
                $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                $colList = implode(', ', $escapedCols);
                $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                $insP = $pdo->prepare("INSERT INTO `penjualan` ({$colList}) VALUES ({$placeholders})");
                $insP->execute(array_values($tx));

                // Also ensure its details
                $dtls = $sqlite->query("SELECT * FROM `penjualan_detail` WHERE `no_faktur` = '{$faktur}'");
                while ($dRow = $dtls->fetchArray(SQLITE3_ASSOC)) {
                    unset($dRow['id']);
                    $dCols = array_keys($dRow);
                    $dEscaped = array_map(fn($c) => "`{$c}`", $dCols);
                    $dColList = implode(', ', $dEscaped);
                    $dPlaceholders = implode(', ', array_fill(0, count($dCols), '?'));
                    $insD = $pdo->prepare("INSERT INTO `penjualan_detail` ({$dColList}) VALUES ({$dPlaceholders})");
                    $insD->execute(array_values($dRow));
                }
                echo "[MIGRATION RECOVERY] Successfully recovered {$faktur} and its detail rows.\n";
            }
        }
    } catch (Exception $eRec) {
        echo "[MIGRATION RECOVERY NOTICE] " . $eRec->getMessage() . "\n";
    }

    // =========================================================================
    // REKONSILIASI STOK HASIL RESTOCK / PEMBELIAN WEB POS
    // =========================================================================
    try {
        echo "[MIGRATION] Reconciling stock from Web POS Purchases...\n";
        // 1. Ambil seluruh pembelian yang diinput melalui Web POS (format no_beli B%)
        $webPurchases = $pdo->query("
            SELECT pd.id_barang, SUM(pd.qty) as total_qty, COALESCE(p.id_cabang, 1) as id_cabang
            FROM `pembelian_detail` pd
            JOIN `pembelian` p ON p.no_beli = pd.no_beli
            WHERE p.no_beli LIKE 'B%'
            GROUP BY pd.id_barang, COALESCE(p.id_cabang, 1)
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($webPurchases as $wp) {
            $idB = $wp['id_barang'];
            $qty = (float)$wp['total_qty'];
            $idC = (int)$wp['id_cabang'];

            // Cek apakah stok_cabang sudah mencakup qty restock ini
            $chkSc = $pdo->prepare("SELECT id, stok FROM `stok_cabang` WHERE id_barang = ? AND id_cabang = ?");
            $chkSc->execute([$idB, $idC]);
            $scRow = $chkSc->fetch(PDO::FETCH_ASSOC);

            if ($scRow) {
                // Pastikan stok cabang bertambah sesuai qty pembelian
                $pdo->exec("UPDATE `stok_cabang` SET stok = stok + {$qty}, updated_at = NOW() WHERE id = {$scRow['id']}");
            } else {
                $pdo->exec("INSERT INTO `stok_cabang` (id_barang, id_cabang, stok, min_stok, lokasi_rak, updated_at) VALUES ('{$idB}', {$idC}, {$qty}, 5, 'Rak Utama', NOW())");
            }

            // Selaraskan master barang
            $pdo->exec("UPDATE `barang` SET stok = (SELECT COALESCE(SUM(stok), 0) FROM `stok_cabang` WHERE id_barang = '{$idB}') WHERE id_barang = '{$idB}'");
            echo "[MIGRATION] Restored Web POS Restock for {$idB} (+{$qty} pcs).\n";
        }

        // 2. Harmonisasi menyeluruh: Jika stok_cabang = 0 tapi barang.stok > 0 (karena pembelian terdahulu)
        $pdo->exec("UPDATE `stok_cabang` sc JOIN `barang` b ON b.id_barang = sc.id_barang SET sc.stok = b.stok WHERE sc.stok = 0 AND b.stok > 0");
        $pdo->exec("UPDATE `barang` b JOIN (SELECT id_barang, SUM(stok) as tot_stok FROM `stok_cabang` GROUP BY id_barang) sc ON sc.id_barang = b.id_barang SET b.stok = sc.tot_stok WHERE sc.tot_stok > b.stok");
        echo "[MIGRATION] Stock synchronization between `barang` and `stok_cabang` completed.\n";

    } catch (Exception $e) {
        echo "[MIGRATION NOTICE] Purchase stock reconciliation warning: " . $e->getMessage() . "\n";
    }

    echo "\n=======================================================\n";
    echo "[MIGRATION SUCCESS] Seluruh tabel & data SQLite berhasil disinkronkan ke MySQL!\n";
    echo "=======================================================\n";

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($pdo) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
    echo "[MIGRATION FATAL ERROR] " . $e->getMessage() . "\n";
}
