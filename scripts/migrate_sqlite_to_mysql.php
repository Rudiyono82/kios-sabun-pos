<?php

/**
 * Script Migrasi Otomatis dari SQLite (writable/database.db) ke MySQL di Railway.
 * Berjalan otomatis saat container web-pos booting.
 */

$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$port = (int)(getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306));
$user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$pass = getenv('DB_PASSWORD') ?: (getenv('MYSQLPASSWORD') ?: '');
$dbName = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'railway');

echo "[MIGRATION] Checking MySQL target: {$host}:{$port}, Database: {$dbName}, User: {$user}...\n";

// 1. Hubungkan ke MySQL dengan retry loop (menunggu jika MySQL sedang proses start)
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

// 2. Cek apakah MySQL sudah memiliki data
try {
    $checkStmt = $pdo->query("SHOW TABLES LIKE 'barang'");
    if ($checkStmt && $checkStmt->rowCount() > 0) {
        $count = $pdo->query("SELECT COUNT(*) FROM `barang`")->fetchColumn();
        if ((int)$count > 0) {
            echo "[MIGRATION] MySQL already populated with {$count} products in 'barang'. Migration already completed. Skipping.\n";
            exit(0);
        }
    }
} catch (Exception $e) {
    // Tabel belum ada, lanjut ke proses pembuatan tabel dan impor data
}

// 3. Buka SQLite database lokal
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
    $ddl = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'BIGINT AUTO_INCREMENT PRIMARY KEY', $sqliteSql);
    $ddl = preg_replace('/VARCHAR(?!\s*\()/i', 'VARCHAR(255)', $ddl);
    $ddl = preg_replace('/DECIMAL(?!\s*\()/i', 'DECIMAL(15,2)', $ddl);
    $ddl = preg_replace('/\bREAL\b/i', 'DOUBLE', $ddl);
    $ddl = str_ireplace('AUTOINCREMENT', 'AUTO_INCREMENT', $ddl);
    $ddl = rtrim($ddl, "; \t\n\r");
    return $ddl . " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
}

// 4. Proses Impor Schema & Data
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

    $tablesRes = $sqlite->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'test'");
    
    $tableList = [];
    while ($r = $tablesRes->fetchArray(SQLITE3_ASSOC)) {
        $tableList[] = $r;
    }

    echo "[MIGRATION] Found " . count($tableList) . " tables in SQLite to migrate.\n";

    foreach ($tableList as $tInfo) {
        $tableName = $tInfo['name'];
        $sqliteSql = $tInfo['sql'];

        if (empty($sqliteSql)) {
            continue;
        }

        echo "[MIGRATION] Creating table `{$tableName}` in MySQL...\n";
        $mysqlDdl = sqliteToMysqlDdl($sqliteSql);
        
        try {
            $pdo->exec($mysqlDdl);
        } catch (Exception $ddlErr) {
            echo "[MIGRATION WARN] Table creation warning for `{$tableName}`: " . $ddlErr->getMessage() . "\n";
        }

        // Ambil data dari SQLite
        $rowCount = (int)$sqlite->querySingle("SELECT COUNT(*) FROM `{$tableName}`");
        if ($rowCount === 0) {
            echo "[MIGRATION] Table `{$tableName}` is empty. Skipped data copying.\n";
            continue;
        }

        echo "[MIGRATION] Copying {$rowCount} rows for `{$tableName}`...\n";
        
        $dataRes = $sqlite->query("SELECT * FROM `{$tableName}`");
        
        $batch = [];
        $batchSize = 250;
        $totalInserted = 0;

        $pdo->beginTransaction();

        while ($row = $dataRes->fetchArray(SQLITE3_ASSOC)) {
            $batch[] = $row;
            if (count($batch) >= $batchSize) {
                insertBatch($pdo, $tableName, $batch);
                $totalInserted += count($batch);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            insertBatch($pdo, $tableName, $batch);
            $totalInserted += count($batch);
            $batch = [];
        }

        $pdo->commit();
        echo "[MIGRATION] Completed `{$tableName}`: {$totalInserted} rows inserted.\n";
    }

    // Buat marker log migrasi
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `_sqlite_migration_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migrated_at` DATETIME,
            `note` VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $pdo->exec("INSERT INTO `_sqlite_migration_log` (migrated_at, note) VALUES (NOW(), 'Migrasi awal SQLite ke MySQL berhasil.');");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n=======================================================\n";
    echo "[MIGRATION SUCCESS] Seluruh data SQLite berhasil dipindahkan ke MySQL!\n";
    echo "=======================================================\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "[MIGRATION ERROR] " . $e->getMessage() . "\n";
}

function insertBatch(PDO $pdo, string $tableName, array $rows): void
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

    $sql = "INSERT INTO `{$tableName}` ({$colList}) VALUES " . implode(', ', $valuesClauses);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}
