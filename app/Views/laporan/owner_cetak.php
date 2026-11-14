<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - <?= $namaBulan[$bulan] ?> <?= $tahun ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 15px;
        }
        .header-box {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-sub {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .table-print th, .table-print td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .table-print th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .badge-confidential {
            background: #ef4444;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print d-flex justify-content-between align-items-center mb-3 p-2 bg-light border rounded">
        <div>
            <span class="fw-bold">Preview Cetak Laporan Rekap Harian Owner</span>
            <span class="text-muted ms-2">(Disarankan orientasi: Landscape / Cetak Memanjang)</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
                🖨️ Cetak Dokumen
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
                Tutup
            </button>
        </div>
    </div>

    <!-- Header Dokumen Cetak -->
    <div class="header-box d-flex justify-content-between align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge-confidential">RAHASIA / KHUSUS OWNER</span>
                <span style="font-weight: 600; color: #0284c7;"><?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?></span>
            </div>
            <h1 class="header-title">REKAPITULASI OMZET, PROFIT &amp; PENGELUARAN HARIAN</h1>
            <p class="header-sub">
                Periode: <strong><?= $namaBulan[$bulan] ?> <?= $tahun ?></strong> | 
                Cabang: <strong><?= $filterCabang === 'semua' ? 'Konsolidasi Seluruh Cabang' : 'Cabang ID #' . $filterCabang ?></strong> | 
                Dicetak oleh: Admin Owner pada <?= date('d/m/Y H:i') ?>
            </p>
        </div>
        <div class="text-end">
            <div style="font-size: 13px; font-weight: 800; color: #0f172a;">KIOS SABUN POS</div>
            <small class="text-muted"><?= esc($profil['alamat'] ?? '') ?></small>
        </div>
    </div>

    <!-- 1. TABEL BUKU REKAP PROFIT & PENGELUARAN (FORMAT MARET) -->
    <h2 style="font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #0f172a;">
        1. REKAPITULASI PROFIT &amp; PENGELUARAN (FORMAT BUKU PROFIT)
    </h2>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 30px;">Tgl</th>
                <th style="width: 55px;">Hari</th>
                <th style="width: 80px;" class="text-end">Profit Awal</th>
                <th style="width: 85px;" class="text-end">Profit Baru</th>
                <th style="width: 85px;" class="text-end">Total Profit</th>
                <th class="text-start">Pengeluaran Toko (Operasional)</th>
                <th style="width: 80px;" class="text-end">Total Toko</th>
                <th class="text-start">Pengeluaran Pribadi (Prive)</th>
                <th style="width: 80px;" class="text-end">Total Pribadi</th>
                <th style="width: 90px;" class="text-end">Profit Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap['rows'] as $r): ?>
                <tr>
                    <td class="text-center font-monospace fw-bold"><?= $r['tgl_num'] ?></td>
                    <td class="text-center"><?= $r['hari'] ?></td>
                    <td class="text-end"><?= $r['has_data'] ? number_format($r['profit_awal'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end fw-bold"><?= $r['profit_baru'] > 0 ? number_format($r['profit_baru'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? number_format($r['total_profit'], 0, ',', '.') : '-' ?></td>
                    <td class="text-start">
                        <?php if (!empty($r['pengeluaran_toko_items'])): ?>
                            <?php foreach ($r['pengeluaran_toko_items'] as $it): ?>
                                <span><?= esc($it['nama']) ?> (<?= number_format($it['total']) ?>); </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold text-danger"><?= $r['pengeluaran_toko'] > 0 ? number_format($r['pengeluaran_toko'], 0, ',', '.') : '-' ?></td>
                    <td class="text-start">
                        <?php if (!empty($r['pengeluaran_pribadi_items'])): ?>
                            <?php foreach ($r['pengeluaran_pribadi_items'] as $it): ?>
                                <span><?= esc($it['nama']) ?> (<?= number_format($it['total']) ?>); </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold text-danger"><?= $r['pengeluaran_pribadi'] > 0 ? number_format($r['pengeluaran_pribadi'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end fw-bold"><?= $r['has_data'] ? number_format($r['profit_akhir'], 0, ',', '.') : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background-color: #f8fafc; font-weight: 800;">
            <tr>
                <td colspan="3" class="text-center">TOTAL PERIODE INI</td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_profit_baru'], 0, ',', '.') ?></td>
                <td class="text-end">-</td>
                <td class="text-center">Total Operasional Toko:</td>
                <td class="text-end text-danger">Rp <?= number_format($rekap['summary']['grand_pengeluaran_toko'], 0, ',', '.') ?></td>
                <td class="text-center">Total Prive Pribadi:</td>
                <td class="text-end text-danger">Rp <?= number_format($rekap['summary']['grand_pengeluaran_pribadi'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_profit_akhir'], 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. TABEL BUKU REKAP OMZET, METODE BAYAR & KULAKAN (FORMAT FEBRUARI) -->
    <h2 style="font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #0f172a; page-break-before: auto;">
        2. REKAPITULASI OMZET, KAS &amp; BELANJA KULAKAN SUPPLIER (FORMAT BUKU OMZET)
    </h2>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 30px;">Tgl</th>
                <th style="width: 55px;">Hari</th>
                <th style="width: 90px;" class="text-end">OMZET</th>
                <th style="width: 80px;" class="text-end">QRIS</th>
                <th style="width: 80px;" class="text-end">TF Bank</th>
                <th style="width: 80px;" class="text-end">Cash</th>
                <th style="width: 80px;" class="text-end">M. Awal</th>
                <th style="width: 80px;" class="text-end">Saldo Laci</th>
                <th style="width: 85px;" class="text-end">Modal (HPP)</th>
                <th style="width: 95px;" class="text-end">Total Modal</th>
                <th class="text-start">Belanja Kulakan Supplier</th>
                <th style="width: 85px;" class="text-end">Total Beli</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap['rows'] as $r): ?>
                <tr>
                    <td class="text-center font-monospace fw-bold"><?= $r['tgl_num'] ?></td>
                    <td class="text-center"><?= $r['hari'] ?></td>
                    <td class="text-end fw-bold"><?= $r['omzet'] > 0 ? number_format($r['omzet'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['qris'] > 0 ? number_format($r['qris'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['transfer'] > 0 ? number_format($r['transfer'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['cash'] > 0 ? number_format($r['cash'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['kas_awal'] > 0 ? number_format($r['kas_awal'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? number_format($r['saldo_laci'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['hpp_modal'] > 0 ? number_format($r['hpp_modal'], 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= $r['has_data'] ? number_format($r['total_modal_berjalan'], 0, ',', '.') : '-' ?></td>
                    <td class="text-start">
                        <?php if (!empty($r['pembelian_items'])): ?>
                            <?php foreach ($r['pembelian_items'] as $pb): ?>
                                <span><?= esc($pb['supplier']) ?> (<?= number_format($pb['total']) ?>); </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold text-danger"><?= $r['total_pembelian'] > 0 ? number_format($r['total_pembelian'], 0, ',', '.') : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background-color: #f8fafc; font-weight: 800;">
            <tr>
                <td colspan="2" class="text-center">TOTAL REKAPITULASI</td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_omzet'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_qris'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_tf'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_cash'], 0, ',', '.') ?></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end">Rp <?= number_format($rekap['summary']['grand_hpp'], 0, ',', '.') ?></td>
                <td class="text-end">-</td>
                <td class="text-center">Total Kulakan:</td>
                <td class="text-end text-danger">Rp <?= number_format($rekap['summary']['grand_pembelian'], 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- 3. RINGKASAN VALUASI ASET STOK GLOBAL -->
    <h2 style="font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #0f172a;">
        3. VALUASI ASET INVENTARIS TOKO GLOBAL (PER CABANG)
    </h2>
    <table class="table-print">
        <thead>
            <tr>
                <th>Cabang Toko</th>
                <th>Alamat</th>
                <th class="text-center">Total Item</th>
                <th class="text-center">Total Unit Stok</th>
                <th class="text-end">Nilai Modal (HPP)</th>
                <th class="text-end">Nilai Jual Retail</th>
                <th class="text-end">Potensi Keuntungan</th>
                <th class="text-end">Total Valuasi Cabang</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aset['per_cabang'] as $cb): ?>
                <tr>
                    <td class="fw-bold"><?= esc($cb['nama_cabang']) ?></td>
                    <td><?= esc($cb['alamat']) ?></td>
                    <td class="text-center"><?= number_format($cb['total_item']) ?></td>
                    <td class="text-center"><?= number_format($cb['total_qty']) ?></td>
                    <td class="text-end">Rp <?= number_format($cb['nilai_modal'], 0, ',', '.') ?></td>
                    <td class="text-end">Rp <?= number_format($cb['nilai_jual'], 0, ',', '.') ?></td>
                    <td class="text-end">Rp <?= number_format($cb['potensi_profit'], 0, ',', '.') ?></td>
                    <td class="text-end fw-bold">Rp <?= number_format($cb['nilai_modal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background-color: #f8fafc; font-weight: 800;">
            <tr>
                <td colspan="2" class="text-center">KONSOLIDASI SELURUH CABANG</td>
                <td class="text-center"><?= number_format($aset['global_item']) ?></td>
                <td class="text-center"><?= number_format($aset['global_qty']) ?></td>
                <td class="text-end">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($aset['global_nilai_jual'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($aset['global_potensi_profit'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top" style="font-size: 10px; color: #64748b;">
        <div>Kios Sabun &amp; Parfum POS System • Dokumen Rahasia Pemilik Toko</div>
        <div>Halaman 1 dari 1</div>
    </div>
</body>
</html>
