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
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `penjualan` transactions ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
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
                } else {
                    echo "[MIGRATION] `penjualan` is up to date ($mysqlRows transactions). Skipped.\n";
                }
                continue;
            }

            // KASUS 4: Tabel Penjualan Detail -> Insert detail transaksi baru
            if ($tableName === 'penjualan_detail') {
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `penjualan_detail` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
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
                } else {
                    echo "[MIGRATION] `penjualan_detail` is up to date ($mysqlRows rows). Skipped.\n";
                }
                continue;
            }

            // KASUS 5: Tabel Data Keuangan -> Insert mutasi kas baru
            if ($tableName === 'data_keuangan') {
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `data_keuangan` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
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
                } else {
                    echo "[MIGRATION] `data_keuangan` is up to date ($mysqlRows rows). Skipped.\n";
                }
                continue;
            }

            // KASUS 6: Tabel Pembelian -> Insert pembelian baru
            if ($tableName === 'pembelian') {
                if ($sqliteRows > $mysqlRows) {
                    echo "[MIGRATION] Syncing new `pembelian` ($sqliteRows in SQLite vs $mysqlRows in MySQL)...\n";
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
                } else {
                    echo "[MIGRATION] `pembelian` is up to date ($mysqlRows rows). Skipped.\n";
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
