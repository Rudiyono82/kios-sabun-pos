<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_Arus_Kas_<?= str_replace([' ', '/', '-'], '_', $periode_label) ?></title>
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
            <td colspan="7" class="header-title"><?= strtoupper(esc($nama_toko)) ?></td>
        </tr>
        <tr>
            <td colspan="7" class="header-sub"><?= esc($profil['alamat'] ?? '') ?> &bull; Telp: <?= esc($profil['telepon'] ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="7" class="header-title" style="font-size: 12pt; padding-top: 10px;">
                LAPORAN ARUS KAS (CASH FLOW) &amp; VALUASI KEKAYAAN TOKO
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-size: 9pt; color: #64748b;">
                Periode: <?= esc($periode_label) ?> | Tanggal Export: <?= date('d/m/Y H:i') ?>
            </td>
        </tr>
        <tr><td colspan="7"></td></tr>
        
        <!-- NERACA VALUASI ASET -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="2">POSISI KEKAYAAN TOKO:</td>
            <td colspan="2">Kas Akhir: Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></td>
            <td colspan="2">Aset Barang (HPP): Rp <?= number_format($nilai_aset_hpp, 0, ',', '.') ?></td>
            <td style="color: #047857;">TOTAL ASET: Rp <?= number_format($total_kekayaan_toko, 0, ',', '.') ?></td>
        </tr>
        <tr><td colspan="7"></td></tr>
    </table>

    <table class="table-data" border="1">
        <thead>
            <tr style="background-color: #0f172a; color: #ffffff;">
                <th style="background-color: #0f172a; color: #ffffff;">No.</th>
                <th style="background-color: #0f172a; color: #ffffff;">Waktu Mutasi</th>
                <th style="background-color: #0f172a; color: #ffffff;">Jenis</th>
                <th style="background-color: #0f172a; color: #ffffff;">Uraian / Keterangan Mutasi</th>
                <th style="background-color: #0f172a; color: #ffffff;">Kas Masuk (+)</th>
                <th style="background-color: #0f172a; color: #ffffff;">Kas Keluar (-)</th>
                <th style="background-color: #0f172a; color: #ffffff;">Saldo Kas</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f8fafc; font-style: italic;">
                <td align="center">&bull;</td>
                <td align="center">-</td>
                <td align="center">Saldo Awal</td>
                <td>Saldo Kas Sebelum Periode <?= esc($periode_label) ?></td>
                <td align="right">-</td>
                <td align="right">-</td>
                <td class="num" align="right"><?= (float)$saldo_awal ?></td>
            </tr>

            <?php $no = 1; foreach ($transaksi_list as $t): ?>
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><?= esc($t['tanggal']) ?></td>
                    <td align="center"><?= esc($t['jenis_keuangan']) ?></td>
                    <td>
                        <?= esc($t['nama_keuangan']) ?>
                        <?php if (!empty($t['keterangan'])): ?> (<?= esc($t['keterangan']) ?>)<?php endif; ?>
                    </td>
                    <td class="num" align="right"><?= (float)$t['kas_masuk'] ?></td>
                    <td class="num" align="right"><?= (float)$t['kas_keluar'] ?></td>
                    <td class="num" align="right"><?= (float)$t['saldo_berjalan'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" align="right">TOTAL MUTASI PERIODE INI:</td>
                <td class="num" align="right"><?= (float)$total_pemasukan ?></td>
                <td class="num" align="right"><?= (float)$total_pengeluaran ?></td>
                <td class="num" align="right"><?= (float)$saldo_akhir_kas ?></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
