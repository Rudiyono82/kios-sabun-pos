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
                'mode_periode' => $mode_periode,
                'bulan'        => $bulan,
                'tgl_mulai'    => $tgl_mulai,
                'tgl_selesai'  => $tgl_selesai,
                'jenis'        => $jenis
            ]);
        ?>
        <a href="<?= base_url('laporan/arus-kas/excel?' . $queryFilter) ?>" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
        </a>
        <a href="<?= base_url('laporan/arus-kas?' . $queryFilter) ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
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
                <span class="badge bg-success text-white px-2 py-1">LAPORAN ARUS KAS &amp; ASET</span>
                <div class="text-muted small mt-1" style="font-size: 8pt;">
                    Tgl Cetak: <strong><?= date('d/m/Y H:i') ?></strong>
                </div>
            </div>
        </div>

        <!-- JUDUL -->
        <div class="text-center my-3">
            <h5 class="fw-bold text-uppercase mb-1">
                LAPORAN ARUS KAS (CASH FLOW) &amp; VALUASI KEKAYAAN TOKO
            </h5>
            <div class="text-dark fw-semibold small">
                Periode: <strong><?= esc($periode_label) ?></strong>
            </div>
        </div>

        <!-- RINGKASAN POSISI KEKAYAAN TOKO -->
        <div class="border rounded p-3 mb-4 bg-light">
            <div class="fw-bold text-dark text-uppercase small mb-2 border-bottom pb-1">
                <i class="bi bi-shield-check text-success me-1"></i> Ringkasan Neraca Kekayaan Toko (Net Asset Valuation)
            </div>
            <div class="row g-2 small">
                <div class="col-4">
                    <span class="text-muted">1. Saldo Kas Akhir Toko:</span>
                    <div class="h6 fw-bold text-primary mb-0">Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></div>
                </div>
                <div class="col-4">
                    <span class="text-muted">2. Nilai Persediaan Barang (HPP):</span>
                    <div class="h6 fw-bold text-info mb-0">Rp <?= number_format($nilai_aset_hpp, 0, ',', '.') ?></div>
                </div>
                <div class="col-4 border-start ps-3">
                    <span class="text-muted">TOTAL VALUASI ASET TOKO:</span>
                    <div class="h5 fw-bold text-success mb-0">Rp <?= number_format($total_kekayaan_toko, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- TABEL MUTASI ARUS KAS -->
        <table class="table-print">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 17%;">Waktu</th>
                    <th style="width: 14%;">Jenis</th>
                    <th style="width: 28%; text-align: left;">Uraian / Keterangan Mutasi</th>
                    <th style="width: 12%; text-align: right;">Kas Masuk (+)</th>
                    <th style="width: 12%; text-align: right;">Kas Keluar (-)</th>
                    <th style="width: 12%; text-align: right;">Saldo Kas</th>
                </tr>
            </thead>
            <tbody>
                <!-- Saldo Awal -->
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td class="text-center text-muted">&bull;</td>
                    <td class="text-center">-</td>
                    <td class="text-center"><span class="badge bg-secondary text-white" style="font-size: 7.5pt;">Saldo Awal</span></td>
                    <td><em>Saldo Kas Sebelum Periode <?= esc($periode_label) ?></em></td>
                    <td class="text-end text-muted">-</td>
                    <td class="text-end text-muted">-</td>
                    <td class="text-end fw-bold text-dark">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></td>
                </tr>

                <?php $no = 1; foreach ($transaksi_list as $t): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td class="small text-center"><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                        <td class="text-center small">
                            <?= esc($t['jenis_keuangan']) ?>
                        </td>
                        <td>
                            <strong><?= esc($t['nama_keuangan']) ?></strong>
                            <?php if (!empty($t['keterangan'])): ?>
                                <div class="text-muted" style="font-size: 7.5pt;"><?= esc($t['keterangan']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold text-success">
                            <?= $t['kas_masuk'] > 0 ? '+ ' . number_format($t['kas_masuk'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end fw-semibold text-danger">
                            <?= $t['kas_keluar'] > 0 ? '- ' . number_format($t['kas_keluar'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end fw-bold text-primary">
                            Rp <?= number_format($t['saldo_berjalan'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL MUTASI PERIODE INI:</td>
                    <td class="text-end fw-bold text-success">+ Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
                    <td class="text-end fw-bold text-danger">- Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
                    <td class="text-end fw-bold text-primary">Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- TANDA TANGAN -->
        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-end text-muted small" style="font-size: 8pt;">
            <div>
                <em>* Arus Kas Bersih (Net Cash Flow) Periode ini: <strong><?= $net_cash_flow >= 0 ? '+' : '' ?>Rp <?= number_format($net_cash_flow, 0, ',', '.') ?></strong></em><br>
                <em>* Dicetak otomatis melalui Sistem POS <?= esc($profil['nama_toko'] ?? 'Kios Sabun & Parfum') ?></em>
            </div>
            <div class="text-center" style="min-width: 150px;">
                <div>Mengetahui,</div>
                <div style="height: 45px;"></div>
                <div class="fw-bold text-dark border-top pt-1">( Owner / Pengelola Toko )</div>
            </div>
        </div>

    </div>

</body>
</html>

