<?php

/**
 * Script Penggabungan Data Bersih dari MS Access (access_update_dump.json)
 * ke Database SQLite Lokal dan Sinkronisasi ke MySQL.
 */

$jsonPath = __DIR__ . '/../writable/access_update_dump.json';
if (!file_exists($jsonPath)) {
    die("File JSON tidak ditemukan di $jsonPath\n");
}

echo "Membaca file data JSON...\n";
$raw = file_get_contents($jsonPath);
$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
$data = json_decode($raw, true);
if (!$data) {
    die("Gagal decode JSON: " . json_last_error_msg() . "\n");
}

$targetDatabases = [
    'D:/dev/ksr/APLIKASI POS/pos-ci-ci4/writable/database.db',
    'C:/xampp/htdocs/kios-sabun-parfum-pos/writable/database.db',
];

foreach ($targetDatabases as $sqlitePath) {
    if (!file_exists($sqlitePath)) {
        echo "Lewati: $sqlitePath (tidak ditemukan)\n";
        continue;
    }

    echo "\n=======================================================\n";
    echo "MEMPROSES DATABASE: $sqlitePath\n";
    echo "=======================================================\n";

    $db = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1. UPDATE & INSERT MASTER BARANG
    echo "--- 1. Sinkronisasi Master Barang ---\n";
    $existingBarang = [];
    $stmtB = $db->query("SELECT id_barang FROM barang");
    while ($r = $stmtB->fetch()) {
        $existingBarang[$r['id_barang']] = true;
    }

    $updateStmt = $db->prepare("UPDATE barang SET
        barcode = :barcode,
        nama_barang = :nama,
        satuan = :satuan,
        harga_beli = :hrg_beli,
        harga_jual = :hrg_jual,
        stok = :stok,
        kode_supplier_1 = CASE WHEN :kode_supp != '' THEN :kode_supp ELSE kode_supplier_1 END,
        qty_paket_a = :qa,
        hrg_paket_a = :ha,
        qty_paket_b = :qb,
        hrg_paket_b = :hb,
        qty_paket_c = :qc,
        hrg_paket_c = :hc,
        status = :status,
        updated_at = :updated_at
        WHERE id_barang = :id_barang");

    $insertStmt = $db->prepare("INSERT INTO barang (
        id_barang, barcode, nama_barang, satuan, harga_beli, harga_jual,
        stok, qty_paket_a, hrg_paket_a, qty_paket_b, hrg_paket_b, qty_paket_c, hrg_paket_c,
        status, created_at, updated_at
    ) VALUES (
        :id_barang, :barcode, :nama, :satuan, :hrg_beli, :hrg_jual,
        :stok, :qa, :ha, :qb, :hb, :qc, :hc,
        :status, :created_at, :updated_at
    )");

    $stokCabangStmt = $db->prepare("INSERT INTO stok_cabang (id_barang, id_cabang, stok, min_stok)
        VALUES (:id_barang, 1, :stok, 5)
        ON CONFLICT(id_barang, id_cabang) DO UPDATE SET stok = :stok");

    $updatedBarang = 0;
    $newBarang = 0;
    $db->beginTransaction();

    foreach ($data['barang'] as $b) {
        $idBarang = trim($b['ID_BARANG']);
        if (empty($idBarang)) continue;

        $params = [
            ':id_barang' => $idBarang,
            ':barcode'   => $b['KODE_BARCODE'] ?: '',
            ':nama'      => $b['NAMA'] ?: $idBarang,
            ':satuan'    => $b['SATUAN'] ?: 'PCS',
            ':hrg_beli'  => (float)($b['HRG_BELI'] ?: 0),
            ':hrg_jual'  => (float)($b['HRG_JUAL'] ?: 0),
            ':stok'      => (float)($b['STOK'] ?: 0),
            ':kode_supp' => $b['KODE_SUPPLIER'] ?: '',
            ':qa'        => (float)($b['QTY_PAKET_A'] ?: 0),
            ':ha'        => (float)($b['HRG_PAKET_A'] ?: 0),
            ':qb'        => (float)($b['QTY_PAKET_B'] ?: 0),
            ':hb'        => (float)($b['HRG_PAKET_B'] ?: 0),
            ':qc'        => (float)($b['QTY_PAKET_C'] ?: 0),
            ':hc'        => (float)($b['HRG_PAKET_C'] ?: 0),
            ':status'    => ($b['ISACTIVE'] == 1 ? 'Aktif' : 'Tidak Aktif'),
            ':updated_at'=> date('Y-m-d H:i:s'),
        ];

        if (isset($existingBarang[$idBarang])) {
            $updateStmt->execute($params);
            $updatedBarang++;
        } else {
            $params[':created_at'] = date('Y-m-d H:i:s');
            $insertStmt->execute($params);
            $newBarang++;
            $existingBarang[$idBarang] = true;
        }

        // Sinkronisasi stok cabang 1
        $stokCabangStmt->execute([
            ':id_barang' => $idBarang,
            ':stok'      => (float)($b['STOK'] ?: 0)
        ]);
    }
    $db->commit();
    echo "Master Barang: $updatedBarang di-update stok & harganya, $newBarang produk baru ditambahkan.\n";

    // Peta Nama Barang untuk Rincian Transaksi
    $barangNameMap = [];
    $stmtBN = $db->query("SELECT id_barang, nama_barang, harga_beli, harga_jual FROM barang");
    while ($r = $stmtBN->fetch()) {
        $barangNameMap[$r['id_barang']] = $r;
    }

    // 2. PENJUALAN BARU
    echo "--- 2. Sinkronisasi Transaksi Penjualan Baru ---\n";
    $existingFaktur = [];
    $stmtF = $db->query("SELECT no_faktur FROM penjualan");
    while ($r = $stmtF->fetch()) {
        $existingFaktur[$r['no_faktur']] = true;
    }

    // Hitung item & qty per no_faktur dari data['penjualan']
    $itemsByFaktur = [];
    foreach ($data['penjualan'] as $p) {
        $faktur = trim($p['ID_JUAL']);
        if (!isset($itemsByFaktur[$faktur])) {
            $itemsByFaktur[$faktur] = [
                'items' => [],
                'total_qty' => 0,
                'total_profit' => 0
            ];
        }
        $qty = (float)($p['JUMLAH'] ?: 0);
        $hrgJual = (float)($p['HARGA_JUAL'] ?: 0);
        $hrgBeli = (float)($p['HRG_BELI'] ?: 0);
        $profit = ($hrgJual - $hrgBeli) * $qty;

        $itemsByFaktur[$faktur]['items'][] = $p;
        $itemsByFaktur[$faktur]['total_qty'] += $qty;
        $itemsByFaktur[$faktur]['total_profit'] += $profit;
    }

    $insertJualStmt = $db->prepare("INSERT INTO penjualan (
        no_faktur, tanggal, kode_user, nama_kasir, kode_member,
        total_item, total_qty, subtotal, diskon, potongan_tukar_jerigen,
        grand_total, bayar, kembalian, metode_bayar, no_kartu,
        total_profit, catatan, created_at, kode_voucher, potongan_voucher, id_cabang
    ) VALUES (
        :no_faktur, :tanggal, :kode_user, :nama_kasir, :kode_member,
        :total_item, :total_qty, :subtotal, 0, 0,
        :grand_total, :bayar, :kembalian, :metode_bayar, :no_kartu,
        :total_profit, :catatan, :created_at, '', 0, 1
    )");

    $insertDetailStmt = $db->prepare("INSERT INTO penjualan_detail (
        no_faktur, id_barang, nama_barang, harga_beli, harga_jual,
        qty, subtotal, profit
    ) VALUES (
        :no_faktur, :id_barang, :nama_barang, :harga_beli, :harga_jual,
        :qty, :subtotal, :profit
    )");

    $newFakturCount = 0;
    $newDetailCount = 0;
    $db->beginTransaction();

    foreach ($data['masterjual'] as $mj) {
        $faktur = trim($mj['ID_JUAL']);
        if (empty($faktur) || isset($existingFaktur[$faktur])) {
            continue; // Sudah ada, lewati
        }

        $fInfo = $itemsByFaktur[$faktur] ?? ['items' => [], 'total_qty' => 0, 'total_profit' => 0];
        $totalItem = count($fInfo['items']);
        $totalQty = $fInfo['total_qty'];
        $totalProfit = $fInfo['total_profit'];

        $grandTotal = (float)($mj['TOT_BIAYA'] ?: 0);
        $bayar = (float)($mj['TOT_BAYAR'] ?: 0);
        $sisa = (float)($mj['TOT_SISA'] ?: 0);

        $metode = 'Cash';
        $ket = trim((string)($mj['KETERANGAN'] ?? ''));
        if (stripos($ket, 'QRIS') !== false) {
            $metode = 'QRIS';
        } elseif (stripos($ket, 'TRANSFER') !== false || stripos($ket, 'BCA') !== false || stripos($ket, 'MANDIRI') !== false) {
            $metode = 'Transfer';
        } elseif ((int)($mj['CARA_BAYAR'] ?? 1) == 2) {
            $metode = 'Debit';
        }

        $insertJualStmt->execute([
            ':no_faktur'    => $faktur,
            ':tanggal'      => $mj['TGL_JUAL'],
            ':kode_user'    => $mj['KODE_USER'] ?: 'KASIR',
            ':nama_kasir'   => $mj['KODE_USER'] ?: 'KASIR',
            ':kode_member'  => $mj['KODE_PELANGGAN'] ?: null,
            ':total_item'   => $totalItem,
            ':total_qty'    => $totalQty,
            ':subtotal'     => $grandTotal,
            ':grand_total'  => $grandTotal,
            ':bayar'        => $bayar,
            ':kembalian'    => $sisa,
            ':metode_bayar' => $metode,
            ':no_kartu'     => $mj['NOKARTU_DEBIT'] ?: '',
            ':total_profit' => $totalProfit,
            ':catatan'      => $ket,
            ':created_at'   => $mj['TGL_JUAL'],
        ]);
        $newFakturCount++;
        $existingFaktur[$faktur] = true;

        // Masukkan rincian item
        foreach ($fInfo['items'] as $it) {
            $idB = trim($it['ID_BARANG']);
            $bRef = $barangNameMap[$idB] ?? null;
            $namaB = $bRef ? $bRef['nama_barang'] : ('Barang ' . $idB);

            $q = (float)($it['JUMLAH'] ?: 0);
            $hj = (float)($it['HARGA_JUAL'] ?: 0);
            $hb = (float)($it['HRG_BELI'] ?: 0);
            $sub = $q * $hj;
            $prf = ($hj - $hb) * $q;

            $insertDetailStmt->execute([
                ':no_faktur'   => $faktur,
                ':id_barang'   => $idB,
                ':nama_barang' => $namaB,
                ':harga_beli'  => $hb,
                ':harga_jual'  => $hj,
                ':qty'         => $q,
                ':subtotal'    => $sub,
                ':profit'      => $prf
            ]);
            $newDetailCount++;
        }
    }
    $db->commit();
    echo "Penjualan: $newFakturCount transaksi baru berhasil dimasukkan ($newDetailCount rincian produk).\n";

    // 3. DATA KEUANGAN BARU
    echo "--- 3. Sinkronisasi Data Keuangan & Kas Baru ---\n";
    $existingKeuangan = [];
    $stmtK = $db->query("SELECT tanggal, jenis_keuangan, nama_keuangan, total FROM data_keuangan");
    while ($r = $stmtK->fetch()) {
        $kKey = $r['tanggal'] . '|' . $r['jenis_keuangan'] . '|' . $r['nama_keuangan'] . '|' . (float)$r['total'];
        $existingKeuangan[$kKey] = true;
    }

    $insertKeuanganStmt = $db->prepare("INSERT INTO data_keuangan (
        tanggal, jenis_keuangan, nama_keuangan, total, keterangan,
        kode_user, is_locked, id_cabang, tipe_pengeluaran
    ) VALUES (
        :tanggal, :jenis_keuangan, :nama_keuangan, :total, :keterangan,
        :kode_user, :is_locked, 1, :tipe_pengeluaran
    )");

    $newKeuanganCount = 0;
    $db->beginTransaction();

    foreach ($data['data_keuangan'] as $dk) {
        $tgl = $dk['Tanggal'];
        $jenis = $dk['JenisKeuangan'] ?: 'Pengeluaran';
        $nama = $dk['NamaKeuangan'] ?: 'Pengeluaran Toko';
        $tot = (float)($dk['Total'] ?: 0);

        $kKey = $tgl . '|' . $jenis . '|' . $nama . '|' . $tot;
        if (isset($existingKeuangan[$kKey])) {
            continue; // Sudah ada
        }

        $insertKeuanganStmt->execute([
            ':tanggal'          => $tgl,
            ':jenis_keuangan'   => $jenis,
            ':nama_keuangan'    => $nama,
            ':total'            => $tot,
            ':keterangan'       => $dk['KodeKeuangan'] ?: '',
            ':kode_user'        => 'SYSTEM',
            ':is_locked'        => ($dk['IsSettle'] ? 1 : 0),
            ':tipe_pengeluaran' => 'toko'
        ]);
        $existingKeuangan[$kKey] = true;
        $newKeuanganCount++;
    }
    $db->commit();
    echo "Data Keuangan: $newKeuanganCount catatan kas/keuangan baru berhasil dimasukkan.\n";

    // 4. PEMBELIAN & PEMBELIAN DETAIL BARU
    echo "--- 4. Sinkronisasi Pembelian Baru ---\n";
    $existingBeli = [];
    $stmtBL = $db->query("SELECT no_beli FROM pembelian");
    while ($r = $stmtBL->fetch()) {
        $existingBeli[$r['no_beli']] = true;
    }

    $itemsByBeli = [];
    foreach ($data['pembelian'] as $pb) {
        $noB = trim($pb['ID_BELI']);
        if (!isset($itemsByBeli[$noB])) {
            $itemsByBeli[$noB] = [];
        }
        $itemsByBeli[$noB][] = $pb;
    }

    $insertBeliStmt = $db->prepare("INSERT INTO pembelian (
        no_beli, tanggal, kode_supplier, nama_supplier, kode_user,
        total_biaya, status_bayar, catatan, created_at, id_cabang
    ) VALUES (
        :no_beli, :tanggal, :kode_supplier, :nama_supplier, :kode_user,
        :total_biaya, :status_bayar, :catatan, :created_at, 1
    )");

    $insertBeliDtlStmt = $db->prepare("INSERT INTO pembelian_detail (
        no_beli, id_barang, nama_barang, harga_beli, qty, subtotal
    ) VALUES (
        :no_beli, :id_barang, :nama_barang, :harga_beli, :qty, :subtotal
    )");

    $newBeliCount = 0;
    $newBeliDtlCount = 0;
    $db->beginTransaction();

    foreach ($data['masterbeli'] as $mb) {
        $noB = trim($mb['ID_BELI']);
        if (empty($noB) || isset($existingBeli[$noB])) {
            continue;
        }

        $totBiaya = (float)($mb['TOT_BIAYA'] ?: 0);
        $insertBeliStmt->execute([
            ':no_beli'       => $noB,
            ':tanggal'       => $mb['TGL_BELI'],
            ':kode_supplier' => $mb['KODE_SUPPLIER'] ?: '',
            ':nama_supplier' => $mb['KODE_SUPPLIER'] ?: 'SUPPLIER',
            ':kode_user'     => $mb['KODE_USER'] ?: 'ADMIN',
            ':total_biaya'   => $totBiaya,
            ':status_bayar'  => 'Lunas',
            ':catatan'       => $mb['KETERANGAN'] ?: 'Pembelian dari Access',
            ':created_at'    => $mb['TGL_BELI']
        ]);
        $existingBeli[$noB] = true;
        $newBeliCount++;

        $dtlList = $itemsByBeli[$noB] ?? [];
        foreach ($dtlList as $d) {
            $idB = trim($d['ID_BARANG']);
            $bRef = $barangNameMap[$idB] ?? null;
            $namaB = $bRef ? $bRef['nama_barang'] : ('Barang ' . $idB);

            $q = (float)($d['JUMLAH'] ?: 0);
            $hb = (float)(isset($d['HRG_BELI']) ? $d['HRG_BELI'] : (isset($d['HARGA_BELI']) ? $d['HARGA_BELI'] : 0));
            $sub = $q * $hb;

            $insertBeliDtlStmt->execute([
                ':no_beli'     => $noB,
                ':id_barang'   => $idB,
                ':nama_barang' => $namaB,
                ':harga_beli'  => $hb,
                ':qty'         => $q,
                ':subtotal'    => $sub
            ]);
            $newBeliDtlCount++;
        }
    }
    $db->commit();
    echo "Pembelian: $newBeliCount pembelian baru ($newBeliDtlCount detail item).\n";

    // 5. BARANG PENDING STOK
    if (!empty($data['barang_pending_stok'])) {
        echo "--- 5. Sinkronisasi Barang Pending Stok ---\n";
        $existingPending = [];
        $stmtP = $db->query("SELECT id FROM barang_pending_stok");
        while ($r = $stmtP->fetch()) {
            $existingPending[$r['id']] = true;
        }

        $insertPendingStmt = $db->prepare("INSERT INTO barang_pending_stok (
            id, ref_id, tgl_input, id_barang, nama_barang, stok_pending,
            harga_beli_lama, harga_beli, harga_jual_lama, harga_jual,
            qty_paket_a, harga_paket_a, qty_paket_b, harga_paket_b, qty_paket_c, harga_paket_c,
            status_persetujuan, status_eksekusi, user_input, created_at, updated_at
        ) VALUES (
            :id, :ref_id, :tgl_input, :id_barang, :nama_barang, :stok_pending,
            :harga_beli_lama, :harga_beli, :harga_jual_lama, :harga_jual,
            :qty_paket_a, :harga_paket_a, :qty_paket_b, :harga_paket_b, :qty_paket_c, :harga_paket_c,
            :status_persetujuan, :status_eksekusi, :user_input, :created_at, :updated_at
        )");

        $newPendingCount = 0;
        $db->beginTransaction();
        foreach ($data['barang_pending_stok'] as $ps) {
            $id = (int)$ps['ID'];
            if (isset($existingPending[$id])) continue;

            $idB = trim($ps['ID_BARANG']);
            $bRef = $barangNameMap[$idB] ?? null;
            $namaB = $bRef ? $bRef['nama_barang'] : ('Barang ' . $idB);

            $insertPendingStmt->execute([
                ':id'                 => $id,
                ':ref_id'             => $ps['REF_ID'] ?: '',
                ':tgl_input'          => $ps['TGL_INPUT'],
                ':id_barang'          => $idB,
                ':nama_barang'        => $namaB,
                ':stok_pending'       => (float)($ps['STOK_PENDING'] ?: 0),
                ':harga_beli_lama'    => (float)($bRef['harga_beli'] ?? 0),
                ':harga_beli'         => (float)($ps['HRG_BELI'] ?: 0),
                ':harga_jual_lama'    => (float)($bRef['harga_jual'] ?? 0),
                ':harga_jual'         => (float)($ps['HRG_JUAL'] ?: 0),
                ':qty_paket_a'        => (int)($ps['QTY_PAKET_A'] ?: 0),
                ':harga_paket_a'      => (float)($ps['HRG_PAKET_A'] ?: 0),
                ':qty_paket_b'        => (int)($ps['QTY_PAKET_B'] ?: 0),
                ':harga_paket_b'      => (float)($ps['HRG_PAKET_B'] ?: 0),
                ':qty_paket_c'        => (int)($ps['QTY_PAKET_C'] ?: 0),
                ':harga_paket_c'      => (float)($ps['HRG_PAKET_C'] ?: 0),
                ':status_persetujuan' => ($ps['STATUS'] == 1 ? 'Disetujui' : 'Belum Setuju'),
                ':status_eksekusi'    => ($ps['STATUS'] == 1 ? 1 : 0),
                ':user_input'         => 'KASIR',
                ':created_at'         => $ps['TGL_INPUT'],
                ':updated_at'         => date('Y-m-d H:i:s'),
            ]);
            $existingPending[$id] = true;
            $newPendingCount++;
        }
        $db->commit();
        echo "Barang Pending Stok: $newPendingCount baris baru ditambahkan.\n";
    }

    // Hitung total akhir
    $tBarang = $db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
    $tJual = $db->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
    $tJualDtl = $db->query("SELECT COUNT(*) FROM penjualan_detail")->fetchColumn();
    $tKeu = $db->query("SELECT COUNT(*) FROM data_keuangan")->fetchColumn();
    $tBeli = $db->query("SELECT COUNT(*) FROM pembelian")->fetchColumn();
    $tPending = $db->query("SELECT COUNT(*) FROM barang_pending_stok")->fetchColumn();

    echo "\nRingkasan Total di Database $sqlitePath:\n";
    echo "  - Total Barang       : $tBarang produk\n";
    echo "  - Total Penjualan    : $tJual transaksi\n";
    echo "  - Total Item Terjual : $tJualDtl baris\n";
    echo "  - Total Keuangan/Kas : $tKeu baris\n";
    echo "  - Total Pembelian    : $tBeli transaksi\n";
    echo "  - Total Pending Stok : $tPending baris\n";
}

echo "\n=======================================================\n";
echo "Selesai memproses seluruh database lokal!\n";
echo "=======================================================\n";
