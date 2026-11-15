<?php
// Template Export Excel Rekap Owner
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Owner <?= $namaBulan[$bulan] ?> <?= $tahun ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000000; padding: 5px 8px; font-family: Arial, sans-serif; font-size: 11px; }
        th { background-color: #d9e1f2; font-weight: bold; text-align: center; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2>REKAPITULASI OMZET, PROFIT &amp; PENGELUARAN HARIAN (KHUSUS OWNER)</h2>
    <p>Periode: <strong><?= $namaBulan[$bulan] ?> <?= $tahun ?></strong> | Cabang: <strong><?= $filterCabang === 'semua' ? 'Semua Cabang' : 'Cabang ' . $filterCabang ?></strong></p>

    <h3>1. REKAP PROFIT &amp; PENGELUARAN (FORMAT BUKU MARET)</h3>
    <table>
        <thead>
            <tr>
                <th>Tgl</th>
                <th>Hari</th>
                <th>Profit Awal</th>
                <th>Profit Baru</th>
                <th>Total Profit</th>
                <th>Pengeluaran Toko (Detail)</th>
                <th>Total Toko</th>
                <th>Pengeluaran Pribadi (Detail)</th>
                <th>Total Pribadi</th>
                <th>Profit Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap['rows'] as $r): ?>
                <tr>
                    <td class="text-center"><?= $r['tgl_num'] ?></td>
                    <td class="text-center"><?= $r['hari'] ?></td>
                    <td class="text-end"><?= $r['has_data'] ? $r['profit_awal'] : '-' ?></td>
                    <td class="text-end fw-bold"><?= $r['profit_baru'] > 0 ? $r['profit_baru'] : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? $r['total_profit'] : '-' ?></td>
                    <td>
                        <?php 
                            if (!empty($r['pengeluaran_toko_items'])) {
                                $arr = [];
                                foreach ($r['pengeluaran_toko_items'] as $it) {
                                    $arr[] = $it['nama'] . ' (' . number_format($it['total']) . ')';
                                }
                                echo implode('; ', $arr);
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td class="text-end"><?= $r['pengeluaran_toko'] > 0 ? $r['pengeluaran_toko'] : '-' ?></td>
                    <td>
                        <?php 
                            if (!empty($r['pengeluaran_pribadi_items'])) {
                                $arr = [];
                                foreach ($r['pengeluaran_pribadi_items'] as $it) {
                                    $arr[] = $it['nama'] . ' (' . number_format($it['total']) . ')';
                                }
                                echo implode('; ', $arr);
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td class="text-end"><?= $r['pengeluaran_pribadi'] > 0 ? $r['pengeluaran_pribadi'] : '-' ?></td>
                    <td class="text-end fw-bold"><?= $r['has_data'] ? $r['profit_akhir'] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL REKAPITULASI</td>
                <td class="text-end"><?= $rekap['summary']['grand_profit_baru'] ?></td>
                <td class="text-end">-</td>
                <td class="text-center">Total Beban Toko:</td>
                <td class="text-end"><?= $rekap['summary']['grand_pengeluaran_toko'] ?></td>
                <td class="text-center">Total Prive:</td>
                <td class="text-end"><?= $rekap['summary']['grand_pengeluaran_pribadi'] ?></td>
                <td class="text-end"><?= $rekap['summary']['grand_profit_akhir'] ?></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <h3>2. REKAP OMZET, METODE BAYAR &amp; KULAKAN (FORMAT BUKU FEBRUARI)</h3>
    <table>
        <thead>
            <tr>
                <th>TGL</th>
                <th>Hari</th>
                <th>OMZET</th>
                <th>QRIS</th>
                <th>TF Bank</th>
                <th>Cash</th>
                <th>M. Awal</th>
                <th>Saldo Laci</th>
                <th>Modal (HPP)</th>
                <th>Total Modal</th>
                <th>Supplier (Detail)</th>
                <th>Total Beli</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap['rows'] as $r): ?>
                <tr>
                    <td class="text-center"><?= $r['tgl_num'] ?></td>
                    <td class="text-center"><?= $r['hari'] ?></td>
                    <td class="text-end fw-bold"><?= $r['omzet'] > 0 ? $r['omzet'] : '-' ?></td>
                    <td class="text-end"><?= $r['qris'] > 0 ? $r['qris'] : '-' ?></td>
                    <td class="text-end"><?= $r['transfer'] > 0 ? $r['transfer'] : '-' ?></td>
                    <td class="text-end"><?= $r['cash'] > 0 ? $r['cash'] : '-' ?></td>
                    <td class="text-end"><?= $r['kas_awal'] > 0 ? $r['kas_awal'] : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? $r['saldo_laci'] : '-' ?></td>
                    <td class="text-end"><?= $r['hpp_modal'] > 0 ? $r['hpp_modal'] : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? $r['total_modal_berjalan'] : '-' ?></td>
                    <td>
                        <?php 
                            if (!empty($r['pembelian_items'])) {
                                $arr = [];
                                foreach ($r['pembelian_items'] as $pb) {
                                    $arr[] = $pb['supplier'] . ' (' . number_format($pb['total']) . ')';
                                }
                                echo implode('; ', $arr);
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td class="text-end"><?= $r['total_pembelian'] > 0 ? $r['total_pembelian'] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAL REKAPITULASI</td>
                <td class="text-end"><?= $rekap['summary']['grand_omzet'] ?></td>
                <td class="text-end"><?= $rekap['summary']['grand_qris'] ?></td>
                <td class="text-end"><?= $rekap['summary']['grand_tf'] ?></td>
                <td class="text-end"><?= $rekap['summary']['grand_cash'] ?></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end"><?= $rekap['summary']['grand_hpp'] ?></td>
                <td class="text-end">-</td>
                <td class="text-center">Total Kulakan:</td>
                <td class="text-end"><?= $rekap['summary']['grand_pembelian'] ?></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <h3>3. VALUASI ASET INVENTARIS TOKO GLOBAL</h3>
    <table>
        <thead>
            <tr>
                <th>Cabang Toko</th>
                <th>Alamat</th>
                <th>Total Item</th>
                <th>Total Qty Stok</th>
                <th>Nilai Modal (HPP)</th>
                <th>Nilai Jual Retail</th>
                <th>Potensi Profit</th>
                <th>Total Valuasi Cabang</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aset['per_cabang'] as $cb): ?>
                <tr>
                    <td class="fw-bold"><?= esc($cb['nama_cabang']) ?></td>
                    <td><?= esc($cb['alamat']) ?></td>
                    <td class="text-center"><?= $cb['total_item'] ?></td>
                    <td class="text-center"><?= $cb['total_qty'] ?></td>
                    <td class="text-end"><?= $cb['nilai_modal'] ?></td>
                    <td class="text-end"><?= $cb['nilai_jual'] ?></td>
                    <td class="text-end"><?= $cb['potensi_profit'] ?></td>
                    <td class="text-end fw-bold"><?= $cb['nilai_modal'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAL KONSOLIDASI SELURUH CABANG</td>
                <td class="text-center"><?= $aset['global_item'] ?></td>
                <td class="text-center"><?= $aset['global_qty'] ?></td>
                <td class="text-end"><?= $aset['global_nilai_modal'] ?></td>
                <td class="text-end"><?= $aset['global_nilai_jual'] ?></td>
                <td class="text-end"><?= $aset['global_potensi_profit'] ?></td>
                <td class="text-end"><?= $aset['global_nilai_modal'] ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

