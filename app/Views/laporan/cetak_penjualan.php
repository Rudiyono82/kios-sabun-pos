<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - <?= esc($profil['nama_toko'] ?? 'Kios Sabun & Parfum') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            font-size: 10pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }

        .paper-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto 40px auto;
            padding: 18mm 15mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            box-sizing: border-box;
        }

        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .table-print th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            text-align: center;
        }

        .table-print td {
            border: 1px solid #e2e8f0;
            padding: 5px 7px;
            vertical-align: middle;
        }

        .table-print tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .table-print tfoot td {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            border-top: 2px solid #0f172a;
        }

        .header-kop {
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .floating-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            display: flex;
            gap: 10px;
            align-items: center;
        }

        @media print {
            body {
                background: #ffffff;
                margin: 0;
                padding: 0;
            }
            .paper-sheet {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
                min-height: auto;
            }
            .floating-bar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 12mm 10mm;
            }
            tr {
                page-break-inside: avoid;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>

    <!-- Floating action bar -->
    <div class="floating-bar no-print">
        <button onclick="window.print()" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
        <?php
            $queryFilter = http_build_query([
                'tgl_mulai'    => $tgl_mulai,
                'tgl_selesai'  => $tgl_selesai,
                'metode_bayar' => $metode_bayar,
                'kasir'        => $kasir,
                'tipe'         => $tipe
            ]);
        ?>
        <a href="<?= base_url('laporan/penjualan/excel?' . $queryFilter) ?>" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
        </a>
        <a href="<?= base_url('laporan/penjualan?' . $queryFilter) ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="paper-sheet">
        <!-- HEADER KOP TOKO -->
        <div class="header-kop d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">
                    <?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?>
                </h3>
                <div class="text-secondary small mt-1">
                    <?= esc($profil['alamat'] ?? 'Jl. Raya Kios Sabun & Parfum') ?>
                    <?php if (!empty($profil['telepon'])): ?>
                        &bull; Telp/WA: <strong><?= esc($profil['telepon']) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark text-white px-2 py-1">LAPORAN PENJUALAN</span>
                <div class="text-muted small mt-1" style="font-size: 8pt;">
                    Tgl Cetak: <strong><?= date('d/m/Y H:i') ?></strong>
                </div>
            </div>
        </div>

        <!-- JUDUL & PARAMETER -->
        <div class="text-center my-3">
            <h5 class="fw-bold text-uppercase mb-1">
                <?= $tipe === 'barang' ? 'LAPORAN REKAP PENJUALAN PER PRODUK' : 'LAPORAN FAKTUR PENJUALAN KASIR' ?>
            </h5>
            <div class="text-dark fw-semibold small">
                Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?>
            </div>
            <div class="d-flex justify-content-center flex-wrap gap-2 mt-1 text-muted small" style="font-size: 8pt;">
                <span>Metode: <strong><?= esc($metode_bayar === 'semua' ? 'Semua Metode' : $metode_bayar) ?></strong></span>
                <span>&bull;</span>
                <span>Kasir: <strong><?= esc($kasir === 'semua' ? 'Semua Kasir' : $kasir) ?></strong></span>
            </div>
        </div>

        <?php if ($tipe === 'barang'): ?>
            <!-- TABEL REKAP PRODUK -->
            <table class="table-print">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 15%;">Kode Barang</th>
                        <th style="width: 35%; text-align: left;">Nama Produk</th>
                        <th style="width: 13%;">Kategori</th>
                        <th style="width: 7%;">Satuan</th>
                        <th style="width: 8%; text-align: center;">Qty</th>
                        <th style="width: 17%; text-align: right;">Total Omzet</th>
                        <?php if ($is_admin): ?>
                            <th style="width: 15%; text-align: right;">Profit</th>
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
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td class="text-center code-font text-primary"><?= esc($b['id_barang']) ?></td>
                            <td class="fw-bold text-dark"><?= esc($b['nama_barang']) ?></td>
                            <td class="text-center small text-secondary"><?= esc($b['nama_kategori'] ?? '-') ?></td>
                            <td class="text-center small"><?= esc($b['satuan'] ?? '-') ?></td>
                            <td class="text-center fw-bold"><?= number_format((float)$b['total_qty'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold">Rp <?= number_format((float)$b['total_omzet'], 0, ',', '.') ?></td>
                            <?php if ($is_admin): ?>
                                <td class="text-end fw-semibold text-success">Rp <?= number_format((float)$b['total_profit'], 0, ',', '.') ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL KESELURUHAN:</td>
                        <td class="text-center fw-bold"><?= number_format($grandQty, 0, ',', '.') ?></td>
                        <td class="text-end fw-bold text-primary">Rp <?= number_format($grandOmzet, 0, ',', '.') ?></td>
                        <?php if ($is_admin): ?>
                            <td class="text-end fw-bold text-success">Rp <?= number_format($grandProfit, 0, ',', '.') ?></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>

        <?php else: ?>
            <!-- TABEL REKAP TRANSAKSI / FAKTUR -->
            <table class="table-print">
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 14%;">No. Faktur</th>
                        <th style="width: 16%;">Tanggal & Waktu</th>
                        <th style="width: 13%;">Kasir</th>
                        <th style="width: 13%;">Pelanggan</th>
                        <th style="width: 6%; text-align: center;">Qty</th>
                        <th style="width: 18%; text-align: right;">Grand Total</th>
                        <th style="width: 10%; text-align: center;">Metode</th>
                        <?php if ($is_admin): ?>
                            <th style="width: 16%; text-align: right;">Profit</th>
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
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td class="text-center code-font fw-bold text-primary"><?= esc($p['no_faktur']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
                            <td class="small"><?= esc($p['nama_kasir'] ?: $p['kode_user']) ?></td>
                            <td class="small"><?= esc($p['kode_member'] ?: 'Umum') ?></td>
                            <td class="text-center"><?= number_format((float)$p['total_qty'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold text-dark">Rp <?= number_format((float)$p['grand_total'], 0, ',', '.') ?></td>
                            <td class="text-center small"><?= esc($p['metode_bayar']) ?></td>
                            <?php if ($is_admin): ?>
                                <td class="text-end fw-semibold text-success small">Rp <?= number_format((float)$p['total_profit'], 0, ',', '.') ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL (<?= count($list) ?> Transaksi):</td>
                        <td class="text-center fw-bold"><?= number_format($grandQty, 0, ',', '.') ?></td>
                        <td class="text-end fw-bold text-primary">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                        <td></td>
                        <?php if ($is_admin): ?>
                            <td class="text-end fw-bold text-success">Rp <?= number_format($grandProfit, 0, ',', '.') ?></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

        <!-- TANDA TANGAN & CATATAN -->
        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-end text-muted small" style="font-size: 8pt;">
            <div>
                <em>* Laporan resmi dihasilkan oleh Sistem POS <?= esc($profil['nama_toko'] ?? 'Kios Sabun & Parfum') ?></em>
            </div>
            <div class="text-center" style="min-width: 150px;">
                <div>Mengetahui,</div>
                <div style="height: 45px;"></div>
                <div class="fw-bold text-dark border-top pt-1">( Pengelola Toko )</div>
            </div>
        </div>

    </div>

</body>
</html>
