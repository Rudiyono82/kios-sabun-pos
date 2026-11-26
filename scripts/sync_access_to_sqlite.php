<?php

$jsonPath = __DIR__ . '/../writable/access_sync_latest.json';
if (!file_exists($jsonPath)) {
    die("File JSON tidak ditemukan: $jsonPath\n");
}

$raw = file_get_contents($jsonPath);
$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
$raw = trim($raw);
$data = json_decode($raw, true);
if (!$data) {
    // Try utf8_encode if needed
    $data = json_decode(utf8_encode($raw), true);
}
if (!$data) {
    die("Gagal decode JSON: " . json_last_error_msg() . "\n");
}

$targetDbs = [
    'd:/dev/ksr/APLIKASI POS/pos-ci-ci4/writable/database.db',
    'C:/xampp/htdocs/kios-sabun-parfum-pos/writable/database.db'
];

foreach ($targetDbs as $dbPath) {
    if (!file_exists($dbPath)) {
        echo "Database $dbPath tidak ditemukan, lewati.\n";
        continue;
    }

    echo "\n=======================================================\n";
    echo "MEMPROSES DATABASE SQLITE: $dbPath\n";
    echo "=======================================================\n";

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. SINKRONISASI BARANG
    $existingBarang = [];
    $stmt = $pdo->query("SELECT id_barang, nama_barang, stok, harga_beli FROM barang");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingBarang[$r['id_barang']] = $r;
    }

    $insertBarang = $pdo->prepare("INSERT INTO barang (
        id_barang, barcode, nama_barang, satuan, harga_beli, harga_jual,
        stok, kode_supplier_1, status, created_at, updated_at
    ) VALUES (
        :id_barang, :barcode, :nama_barang, :satuan, :harga_beli, :harga_jual,
        :stok, :kode_supplier, :status, :created_at, :updated_at
    )");

    $updateStokBeli = $pdo->prepare("UPDATE barang SET 
        stok = :stok, 
        harga_beli = CASE WHEN :harga_beli > 0 THEN :harga_beli ELSE harga_beli END,
        updated_at = :updated_at 
        WHERE id_barang = :id_barang");

    $stokCabangStmt = $pdo->prepare("INSERT INTO stok_cabang (id_barang, id_cabang, stok, min_stok)
        VALUES (:id_barang, 1, :stok, 5)
        ON CONFLICT(id_barang, id_cabang) DO UPDATE SET stok = :stok");

    $newBarangCount = 0;
    $updatedBarangCount = 0;
    $pdo->beginTransaction();

    foreach ($data['barang'] as $b) {
        $idB = trim($b['id_barang']);
        if (empty($idB)) continue;

        if (!isset($existingBarang[$idB])) {
            $insertBarang->execute([
                ':id_barang'     => $idB,
                ':barcode'       => $b['barcode'] ?: '',
                ':nama_barang'   => $b['nama_barang'] ?: $idB,
                ':satuan'        => $b['satuan'] ?: 'PCS',
                ':harga_beli'    => (float)($b['harga_beli'] ?: 0),
                ':harga_jual'    => (float)($b['harga_jual'] ?: 0),
                ':stok'          => (float)($b['stok'] ?: 0),
                ':kode_supplier' => $b['kode_supplier'] ?: '',
                ':status'        => (!empty($b['is_active']) && (int)$b['is_active'] != 0 ? 'Aktif' : 'Tidak Aktif'),
                ':created_at'    => date('Y-m-d H:i:s'),
                ':updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $newBarangCount++;
            $existingBarang[$idB] = [
                'id_barang'   => $idB,
                'nama_barang' => $b['nama_barang'],
                'stok'        => $b['stok'],
                'harga_beli'  => $b['harga_beli']
            ];
        } else {
            // Update stok jika di Access stok berbeda (misal akibat pembelian baru)
            $oldStok = (float)$existingBarang[$idB]['stok'];
            $newStok = (float)$b['stok'];
            $newHrgBeli = (float)$b['harga_beli'];
            if ($oldStok != $newStok || ($newHrgBeli > 0 && (float)$existingBarang[$idB]['harga_beli'] == 0)) {
                $updateStokBeli->execute([
                    ':stok'       => $newStok,
                    ':harga_beli' => $newHrgBeli,
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':id_barang'  => $idB
                ]);
                $updatedBarangCount++;
            }
        }

        // Pastikan stok cabang selalu sinkron
        $stokCabangStmt->execute([
            ':id_barang' => $idB,
            ':stok'      => (float)($b['stok'] ?: 0)
        ]);
    }
    $pdo->commit();
    echo "Barang: $newBarangCount produk baru ditambahkan, $updatedBarangCount produk di-update stok/harganya.\n";

    // 2. SINKRONISASI PEMBELIAN & PEMBELIAN DETAIL
    $existingPembelian = [];
    $stmtB = $pdo->query("SELECT no_beli FROM pembelian");
    while ($r = $stmtB->fetch(PDO::FETCH_ASSOC)) {
        $existingPembelian[$r['no_beli']] = true;
    }

    $insertPembelian = $pdo->prepare("INSERT INTO pembelian (
        no_beli, tanggal, kode_supplier, nama_supplier, kode_user,
        total_biaya, status_bayar, catatan, created_at, id_cabang
    ) VALUES (
        :no_beli, :tanggal, :kode_supplier, :nama_supplier, :kode_user,
        :total_biaya, :status_bayar, :catatan, :created_at, 1
    )");

    $insertPembelianDtl = $pdo->prepare("INSERT INTO pembelian_detail (
        no_beli, id_barang, nama_barang, harga_beli, qty, subtotal
    ) VALUES (
        :no_beli, :id_barang, :nama_barang, :harga_beli, :qty, :subtotal
    )");

    $itemsByBeli = [];
    foreach ($data['pembelian_detail'] as $pd) {
        $noB = trim($pd['no_beli']);
        if (!isset($itemsByBeli[$noB])) {
            $itemsByBeli[$noB] = [];
        }
        $itemsByBeli[$noB][] = $pd;
    }

    $newBeliCount = 0;
    $newBeliDtlCount = 0;
    $pdo->beginTransaction();

    foreach ($data['pembelian_header'] as $ph) {
        $noB = trim($ph['no_beli']);
        if (isset($existingPembelian[$noB])) {
            continue; // Sudah ada di database
        }

        $insertPembelian->execute([
            ':no_beli'       => $noB,
            ':tanggal'       => $ph['tanggal'],
            ':kode_supplier' => $ph['kode_supplier'],
            ':nama_supplier' => $ph['nama_supplier'],
            ':kode_user'     => $ph['kode_user'],
            ':total_biaya'   => (float)$ph['total_biaya'],
            ':status_bayar'  => 'Lunas (Kas Toko)',
            ':catatan'       => $ph['keterangan'],
            ':created_at'    => $ph['tanggal'],
        ]);
        $existingPembelian[$noB] = true;
        $newBeliCount++;

        // Masukkan rincian items
        if (isset($itemsByBeli[$noB])) {
            foreach ($itemsByBeli[$noB] as $it) {
                $idB = trim($it['id_barang']);
                $namaB = $existingBarang[$idB]['nama_barang'] ?? ('Barang ' . $idB);

                $insertPembelianDtl->execute([
                    ':no_beli'     => $noB,
                    ':id_barang'   => $idB,
                    ':nama_barang' => $namaB,
                    ':harga_beli'  => (float)$it['harga_beli'],
                    ':qty'         => (float)$it['qty'],
                    ':subtotal'    => (float)$it['subtotal']
                ]);
                $newBeliDtlCount++;
            }
        }
    }
    $pdo->commit();
    echo "Pembelian: $newBeliCount faktur pembelian baru berhasil dimasukkan ($newBeliDtlCount rincian produk).\n";
}

echo "\n=======================================================\n";
echo "SINKRONISASI ACCESS KE SQLITE SELESAI!\n";
echo "=======================================================\n";
