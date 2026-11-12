<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_Penjualan_<?= $tgl_mulai ?>_<?= $tgl_selesai ?></title>
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
            <td colspan="<?= $tipe === 'barang' ? ($is_admin ? 8 : 7) : ($is_admin ? 9 : 8) ?>" class="header-title">
                <?= strtoupper(esc($nama_toko)) ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? ($is_admin ? 8 : 7) : ($is_admin ? 9 : 8) ?>" class="header-sub">
                <?= esc($profil['alamat'] ?? '') ?> &bull; Telp: <?= esc($profil['telepon'] ?? '-') ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? ($is_admin ? 8 : 7) : ($is_admin ? 9 : 8) ?>" class="header-title" style="font-size: 12pt; padding-top: 10px;">
                <?= $tipe === 'barang' ? 'LAPORAN REKAP PENJUALAN PER PRODUK' : 'LAPORAN PENJUALAN KASIR (PER FAKTUR)' ?>
            </td>
        </tr>
        <tr>
            <td colspan="<?= $tipe === 'barang' ? ($is_admin ? 8 : 7) : ($is_admin ? 9 : 8) ?>" style="text-align: center; font-size: 9pt; color: #64748b;">
                Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?> | Metode: <?= esc($metode_bayar) ?> | Kasir: <?= esc($kasir) ?> | Export: <?= date('d/m/Y H:i') ?>
            </td>
        </tr>
        <tr><td colspan="<?= $tipe === 'barang' ? ($is_admin ? 8 : 7) : ($is_admin ? 9 : 8) ?>"></td></tr>
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
                    <th style="background-color: #0f172a; color: #ffffff;">Total Terjual (Qty)</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Total Omzet</th>
                    <?php if ($is_admin): ?>
                        <th style="background-color: #0f172a; color: #ffffff;">Total Profit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $no = 1;
                    $grandQty = 0;
                    $grandOmzet = 0;
                    $grandProfit = 0;
                    foreach ($list_barang as $b): 
                        $grandQty += (float)$b['total_qty'];
                        $grandOmzet += (float)$b['total_omzet'];
                        $grandProfit += (float)$b['total_profit'];
                ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($b['id_barang']) ?></td>
                        <td style="font-weight: bold;"><?= esc($b['nama_barang']) ?></td>
                        <td align="center"><?= esc($b['nama_kategori'] ?? '-') ?></td>
                        <td align="center"><?= esc($b['satuan'] ?? '-') ?></td>
                        <td class="num" align="center"><?= (float)$b['total_qty'] ?></td>
                        <td class="num" align="right"><?= (float)$b['total_omzet'] ?></td>
                        <?php if ($is_admin): ?>
                            <td class="num" align="right"><?= (float)$b['total_profit'] ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" align="right">TOTAL KESELURUHAN:</td>
                    <td class="num" align="center"><?= $grandQty ?></td>
                    <td class="num" align="right"><?= $grandOmzet ?></td>
                    <?php if ($is_admin): ?>
                        <td class="num" align="right"><?= $grandProfit ?></td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>

    <?php else: ?>
        <table class="table-data" border="1">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="background-color: #0f172a; color: #ffffff;">No.</th>
                    <th style="background-color: #0f172a; color: #ffffff;">No. Faktur</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Tanggal</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Kasir</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Pelanggan</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Total Qty</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Grand Total</th>
                    <th style="background-color: #0f172a; color: #ffffff;">Metode Bayar</th>
                    <?php if ($is_admin): ?>
                        <th style="background-color: #0f172a; color: #ffffff;">Profit</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $no = 1;
                    $grandQty = 0;
                    $grandTotal = 0;
                    $grandProfit = 0;
                    foreach ($list as $p): 
                        $grandQty += (float)$p['total_qty'];
                        $grandTotal += (float)$p['grand_total'];
                        $grandProfit += (float)($p['total_profit'] ?? 0);
                ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td class="str" align="center"><?= esc($p['no_faktur']) ?></td>
                        <td align="center"><?= esc($p['tanggal']) ?></td>
                        <td><?= esc($p['nama_kasir'] ?: $p['kode_user']) ?></td>
                        <td align="center"><?= esc($p['kode_member'] ?: 'Umum') ?></td>
                        <td class="num" align="center"><?= (float)$p['total_qty'] ?></td>
                        <td class="num" align="right"><?= (float)$p['grand_total'] ?></td>
                        <td align="center"><?= esc($p['metode_bayar']) ?></td>
                        <?php if ($is_admin): ?>
                            <td class="num" align="right"><?= (float)($p['total_profit'] ?? 0) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" align="right">TOTAL (<?= count($list) ?> Transaksi):</td>
                    <td class="num" align="center"><?= $grandQty ?></td>
                    <td class="num" align="right"><?= $grandTotal ?></td>
                    <td></td>
                    <?php if ($is_admin): ?>
                        <td class="num" align="right"><?= $grandProfit ?></td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

</body>
</html>
