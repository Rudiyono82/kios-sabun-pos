<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan <?= esc($transfer['no_transfer']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            color: #555;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures td {
            text-align: center;
            width: 33.33%;
            vertical-align: top;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 160px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Cetak Dokumen</button>
    </div>

    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <div class="title">KIOS SABUN &amp; PARFUM</div>
                    <div class="subtitle">Sistem Distribusi &amp; Mutasi Antar Cabang</div>
                </td>
                <td class="text-end">
                    <div style="font-size: 16px; font-weight: bold;">SURAT JALAN / TRANSFER STOK</div>
                    <div style="font-size: 13px; font-family: monospace; font-weight: bold;"><?= esc($transfer['no_transfer']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Pengirim (Cabang Asal):</strong><br>
                <strong><?= esc($transfer['nama_cabang_asal']) ?></strong> (<?= esc($transfer['kode_cabang_asal']) ?>)<br>
                <?= esc($transfer['alamat_asal'] ?? '-') ?><br>
                Telp: <?= esc($transfer['telp_asal'] ?? '-') ?><br>
                Petugas: <?= esc($transfer['nama_user_pengirim']) ?>
            </td>
            <td width="50%">
                <strong>Penerima (Cabang Tujuan):</strong><br>
                <strong><?= esc($transfer['nama_cabang_tujuan']) ?></strong> (<?= esc($transfer['kode_cabang_tujuan']) ?>)<br>
                <?= esc($transfer['alamat_tujuan'] ?? '-') ?><br>
                Telp: <?= esc($transfer['telp_tujuan'] ?? '-') ?><br>
                Tanggal Kirim: <?= date('d/m/Y H:i', strtotime($transfer['tanggal'])) ?> WIB
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Nama Barang</th>
                <th width="100">Qty Kirim</th>
                <th width="80">Satuan</th>
                <th>Keterangan / Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($details as $d): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= esc($d['nama_barang']) ?></strong></td>
                    <td class="text-center"><strong><?= (float)$d['qty'] ?></strong></td>
                    <td class="text-center"><?= esc($d['satuan'] ?? 'Pcs') ?></td>
                    <td><?= esc($d['catatan'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Total Kuantitas:</th>
                <th class="text-center"><?= (float)$transfer['total_qty'] ?></th>
                <th class="text-center"><?= number_format($transfer['total_item']) ?> Item</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($transfer['catatan'])): ?>
        <div style="border: 1px dashed #666; padding: 6px 10px; margin-bottom: 20px; font-size: 11px;">
            <strong>Catatan Pengiriman:</strong> <?= esc($transfer['catatan']) ?>
        </div>
    <?php endif; ?>

    <table class="signatures">
        <tr>
            <td>
                Pengirim / Gudang Asal
                <div class="signature-line"></div>
                <div>( <?= esc($transfer['nama_user_pengirim'] ?? 'Staf Asal') ?> )</div>
            </td>
            <td>
                Sopir / Kurir Ekspedisi
                <div class="signature-line"></div>
                <div>( .................................... )</div>
            </td>
            <td>
                Penerima / Cabang Tujuan
                <div class="signature-line"></div>
                <div>( <?= esc($transfer['nama_user_penerima'] ?? 'Staf Penerima') ?> )</div>
            </td>
        </tr>
    </table>
</body>
</html>

