<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $mode === 'eksternal' ? 'Katalog_Harga_Barang' : 'Laporan_Master_Barang' ?></title>
    <style>
        .str { mso-number-format: '\@'; }
        .num { mso-number-format: '#,##0'; }
        .num-cur { mso-number-format: '"Rp "#,##0'; }
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; }
        .header-sub { font-size: 10pt; text-align: center; color: #555; }
        .table-data { border-collapse: collapse; width: 100%; }
        .table-data th { background-color: #0f172a; color: #ffffff; font-weight: bold; border: 1px solid #334155; text-align: center; padding: 6px; }
        .table-data td { border: 1px solid #cbd5e1; padding: 5px; vertical-align: middle; }
    </style>
</head>
<body>

    <!-- KOP TOKO -->
    <table>
        <tr>
            <td colspan="<?= $mode === 'eksternal' ? '7' : '12' ?>" class="header-title">
                <?= strtoupper(esc($nama_toko)) ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $mode === 'eksternal' ? '7' : '12' ?>" class="header-sub">
                <?= esc($profil['alamat'] ?? 'Kios Sabun & Parfum') ?> &bull; Telp: <?= esc($profil['telepon'] ?? '-') ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $mode === 'eksternal' ? '7' : '12' ?>" class="header-title" style="font-size: 12pt; padding-top: 10px;">
                <?= $mode === 'eksternal' ? 'DAFTAR HARGA &amp; KATALOG PRODUK (PRICELIST)' : 'LAPORAN MASTER DATA BARANG (LENGKAP)' ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $mode === 'eksternal' ? '7' : '12' ?>" style="text-align: center; font-size: 9pt; color: #64748b;">
                Filter: Kategori: <?= esc($kategori_nama) ?> | Satuan: <?= esc($satuan === 'semua' || empty($satuan) ? 'Semua' : $satuan) ?> | Status: <?= esc($status === 'semua' || empty($status) ? 'Semua' : $status) ?> | Diurutkan Abjad (A - Z) | Tgl Export: <?= date('d/m/Y H:i') ?>
            </td>
        </tr>
        <tr><td colspan="<?= $mode === 'eksternal' ? '7' : '12' ?>"></td></tr>
    </table>

    <!-- TABEL UTAMA -->
    <?php if ($mode === 'eksternal'): ?>
        <!-- ============================================== -->
        <!-- MODE EKSTERNAL (ORANG LUAR / RESELLER / PUBLIK) -->
        <!-- Hanya: No, Kode, Nama, Kategori, Satuan,       -->
        <!--        Harga Jual, Harga Grosir                -->
        <!-- ============================================== -->
        <table class="table-data" border="1">
            <thead>
                <tr style="background-color: #1e293b; color: #ffffff;">
                    <th style="width: 40px; background-color: #0f172a; color: #ffffff;">No.</th>
                    <th style="width: 130px; background-color: #0f172a; color: #ffffff;">Kode Barang</th>
                    <th style="width: 280px; background-color: #0f172a; color: #ffffff;">Nama Produk</th>
                    <th style="width: 140px; background-color: #0f172a; color: #ffffff;">Kategori</th>
                    <th style="width: 80px; background-color: #0f172a; color: #ffffff;">Satuan</th>
                    <th style="width: 130px; background-color: #0f172a; color: #ffffff;">Harga Jual</th>
                    <th style="width: 130px; background-color: #0f172a; color: #ffffff;">Harga Jual Grosir</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($barang_list as $b): ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($b['id_barang']) ?></td>
                        <td style="font-weight: bold;"><?= esc($b['nama_barang']) ?></td>
                        <td align="center"><?= esc($b['nama_kategori'] ?? 'Umum') ?></td>
                        <td align="center"><?= esc($b['satuan'] ?? 'Pcs') ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_jual'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_grosir'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- ============================================== -->
        <!-- MODE INTERNAL TOKO (MANAJEMEN LENGKAP)         -->
        <!-- ============================================== -->
        <table class="table-data" border="1">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="background-color: #0f172a; color: #ffffff;">No.</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Kode Barang</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Barcode</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Nama Produk</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Kategori</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Satuan</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Beli (HPP)</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Jual</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Grosir</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Min Qty A</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Paket A</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Min Qty B</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Paket B</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Min Qty C</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Harga Paket C</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Stok</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($barang_list as $b): ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($b['id_barang']) ?></td>
                        <td class="str" align="center"><?= esc($b['barcode']) ?></td>
                        <td style="font-weight: bold;"><?= esc($b['nama_barang']) ?></td>
                        <td align="center"><?= esc($b['nama_kategori'] ?? '-') ?></td>
                        <td align="center"><?= esc($b['satuan'] ?? '-') ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_beli'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_jual'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_grosir'] ?? 0) ?></td>
                        <td class="num" align="center"><?= (int)($b['min_qty_a'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_paket_a'] ?? 0) ?></td>
                        <td class="num" align="center"><?= (int)($b['min_qty_b'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_paket_b'] ?? 0) ?></td>
                        <td class="num" align="center"><?= (int)($b['min_qty_c'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['harga_paket_c'] ?? 0) ?></td>
                        <td class="num" align="right"><?= (float)($b['stok'] ?? 0) ?></td>
                        <td align="center"><?= esc($b['status'] ?? 'Aktif') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
