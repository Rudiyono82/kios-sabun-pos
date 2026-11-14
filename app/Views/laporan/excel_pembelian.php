<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_Pembelian_<?= $tgl_mulai ?>_<?= $tgl_selesai ?></title>
    <style>
        .str { mso-number-format: '\@'; }
        .num { mso-number-format: '#,##0'; }
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; }
        .header-sub { font-size: 10pt; text-align: center; color: #555; }
        .table-data { border-collapse: collapse; width: 100%; }
        .table-data th { background-color: #0f172a; color: #ffffff; font-weight: bold; border: 1px solid #334155; text-align: center; padding: 6px; }
        .table-data td { border: 1px solid #cbd5e1; padding: 5px; vertical-align: middle; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? 7 : 7 ?>" class="header-title">
                <?= strtoupper(esc($nama_toko)) ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? 7 : 7 ?>" class="header-sub">
                <?= esc($profil['alamat'] ?? '') ?> &bull; Telp: <?= esc($profil['telepon'] ?? '-') ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? 7 : 7 ?>" class="header-title" style="font-size: 12pt; padding-top: 10px;">
                <?= $tipe === 'barang' ? 'LAPORAN REKAP PEMBELIAN PER ITEM PRODUK KULAKAN' : 'LAPORAN PEMBELIAN & RESTOCK SUPPLIER' ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? 7 : 7 ?>" style="text-align: center; font-size: 9pt; color: #64748b;">
                Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?> | Supplier: <?= esc($kode_supplier) ?> | Status: <?= esc($status_bayar) ?> | Export: <?= date('d/m/Y H:i') ?>
            </td>
        </tr>
        <tr><td colspan="<?= $tipe === 'barang' ? 7 : 7 ?>"></td></tr>
    </table>

    <?php if ($tipe === 'barang'): ?>
        <table class="table-data" border="1">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="background-color: #0f172a; color: #ffffff;">No.</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Kode Barang</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Nama Produk</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Kategori</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Satuan</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Total Qty Masuk</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Total Biaya Pembelian</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $no = 1;
                    $grandQty = 0;
                    $grandBiaya = 0;
                    foreach ($list_barang as $b): 
                        $grandQty += (float)$b['total_qty'];
                        $grandBiaya += (float)$b['total_biaya'];
                ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($b['id_barang']) ?></td>
                        <td style="font-weight: bold;"><?= esc($b['nama_barang']) ?></td>
                        <td align="center"><?= esc($b['nama_kategori'] ?? '-') ?></td>
                        <td align="center"><?= esc($b['satuan'] ?? '-') ?></td>
                        <td class="num" align="center"><?= (float)$b['total_qty'] ?></td>
                        <td class="num" align="right"><?= (float)$b['total_biaya'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" align="right">TOTAL KESELURUHAN:</td>
                    <td class="num" align="center"><?= $grandQty ?></td>
                    <td class="num" align="right"><?= $grandBiaya ?></td>
                </tr>
            </tfoot>
        </table>

    <?php else: ?>
        <table class="table-data" border="1">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="background-color: #0f172a; color: #ffffff;">No.</th>
                    <th style="background-color: #0f172a; color: #ffffff;">No. Beli / Nota</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Tanggal</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Nama Supplier</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Petugas</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Status Bayar</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $no = 1;
                    $grandBiaya = 0;
                    foreach ($list as $b): 
                        $grandBiaya += (float)$b['total_biaya'];
                ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($b['no_beli']) ?></td>
                        <td align="center"><?= esc($b['tanggal']) ?></td>
                        <td><?= esc($b['nama_supplier']) ?></td>
                        <td align="center"><?= esc($b['kode_user'] ?: 'Admin') ?></td>
                        <td align="center"><?= esc($b['status_bayar'] ?? 'Lunas') ?></td>
                        <td class="num" align="right"><?= (float)$b['total_biaya'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="6" align="right">TOTAL (<?= count($list) ?> Nota):</td>
                    <td class="num" align="right"><?= $grandBiaya ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

</body>
</html>

