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
            font-size: 11pt;
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
            padding: 20mm 15mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            box-sizing: border-box;
        }

        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .table-print th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            text-align: center;
        }

        .table-print td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .table-print tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .header-kop {
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .badge-mode {
            font-size: 8pt;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Floating action toolbar for screen view */
        .floating-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            padding: 10px 16px;
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
            tfoot {
                display: table-footer-group;
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
                'kategori_id' => $kategori_id,
                'satuan'      => $satuan,
                'status'      => $status,
                'mode'        => $mode,
                'q'           => $q
            ]);
        ?>
        <a href="<?= base_url('laporan/barang/excel?' . $queryFilter) ?>" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
        </a>
        <a href="<?= base_url('laporan/barang?' . $queryFilter) ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
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
                <span class="badge badge-mode <?= $mode === 'eksternal' ? 'bg-success text-white' : 'bg-dark text-white' ?>">
                    <?= $mode === 'eksternal' ? 'KATALOG / DAFTAR HARGA' : 'LAPORAN INTERNAL' ?>
                </span>
                <div class="text-muted small mt-1" style="font-size: 8pt;">
                    Tanggal Cetak: <strong><?= date('d/m/Y H:i') ?></strong>
                </div>
            </div>
        </div>

        <!-- JUDUL & PARAMETER FILTER -->
        <div class="text-center my-3">
            <h5 class="fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">
                <?= $mode === 'eksternal' ? 'DAFTAR HARGA &amp; KATALOG PRODUK' : 'LAPORAN MASTER DATA BARANG (LENGKAP)' ?>
            </h5>
            <div class="text-muted small">
                Diurutkan Abjad Nama Barang (A - Z) &bull; Total: <strong><?= number_format(count($barang_list), 0, ',', '.') ?> Produk</strong>
            </div>
            
            <!-- Metadata Filter -->
            <div class="d-flex justify-content-center flex-wrap gap-3 mt-2 text-secondary" style="font-size: 8.5pt;">
                <span>Kategori: <strong><?= esc($kategori_nama) ?></strong></span>
                <span>&bull;</span>
                <span>Satuan: <strong><?= esc($satuan === 'semua' || empty($satuan) ? 'Semua Satuan' : $satuan) ?></strong></span>
                <span>&bull;</span>
                <span>Status: <strong><?= esc($status === 'semua' || empty($status) ? 'Semua' : $status) ?></strong></span>
                <?php if (!empty($q)): ?>
                    <span>&bull;</span>
                    <span>Kata Kunci: "<strong><?= esc($q) ?></strong>"</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL DATA SESUAI MODE                         -->
        <!-- ============================================== -->
        <?php if (empty($barang_list)): ?>
            <div class="alert alert-light text-center border p-4 my-4">
                <p class="text-muted mb-0">Tidak ada data produk yang cocok dengan kriteria filter yang dipilih.</p>
            </div>
        <?php else: ?>

            <?php if ($mode === 'eksternal'): ?>
                <!-- ============================================== -->
                <!-- MODE EKSTERNAL (ORANG LUAR / RESELLER / KONSUMEN) -->
                <!-- Menampilkan: No, Kode, Nama, Kategori, Satuan, -->
                <!--              Harga Jual, Harga Grosir           -->
                <!-- *Modal Beli & Stok disembunyikan demi rahasia!  -->
                <!-- ============================================== -->
                <table class="table-print">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 15%;">Kode Barang</th>
                            <th style="width: 38%; text-align: left;">Nama Produk</th>
                            <th style="width: 14%;">Kategori</th>
                            <th style="width: 8%;">Satuan</th>
                            <th style="width: 10%; text-align: right;">Harga Jual</th>
                            <th style="width: 10%; text-align: right;">Harga Grosir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($barang_list as $b): ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                                <td class="text-center code-font fw-semibold text-primary"><?= esc($b['id_barang']) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= esc($b['nama_barang']) ?></div>
                                    <?php if (!empty($b['barcode']) && $b['barcode'] !== $b['id_barang']): ?>
                                        <small class="text-muted code-font" style="font-size: 7.5pt;">Barcode: <?= esc($b['barcode']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-secondary small"><?= esc($b['nama_kategori'] ?? 'Umum') ?></td>
                                <td class="text-center fw-medium"><?= esc($b['satuan'] ?? 'Pcs') ?></td>
                                <td class="text-end fw-bold text-dark">
                                    Rp <?= number_format((float)($b['harga_jual'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    Rp <?= number_format((float)($b['harga_grosir'] ?? 0), 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- ============================================== -->
                <!-- MODE INTERNAL (MANAJEMEN / ADMIN LENGKAP)      -->
                <!-- ============================================== -->
                <table class="table-print" style="font-size: 8.5pt;">
                    <thead>
                        <tr>
                            <th style="width: 3%;">No</th>
                            <th style="width: 11%;">Kode / Barcode</th>
                            <th style="width: 25%; text-align: left;">Nama Produk</th>
                            <th style="width: 10%;">Kategori</th>
                            <th style="width: 6%;">Satuan</th>
                            <th style="width: 9%; text-align: right;">Modal (HPP)</th>
                            <th style="width: 9%; text-align: right;">Harga Jual</th>
                            <th style="width: 9%; text-align: right;">Grosir Std</th>
                            <th style="width: 10%;">Paket Bertingkat</th>
                            <th style="width: 8%; text-align: right;">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($barang_list as $b): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no++ ?></td>
                                <td class="text-center code-font">
                                    <strong class="text-primary"><?= esc($b['id_barang']) ?></strong>
                                    <?php if (!empty($b['barcode'])): ?>
                                        <div class="text-muted" style="font-size: 7pt;"><?= esc($b['barcode']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= esc($b['nama_barang']) ?></span>
                                    <?php if ($b['status'] !== 'Aktif'): ?>
                                        <span class="badge bg-danger text-white ms-1" style="font-size: 6.5pt;">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center small"><?= esc($b['nama_kategori'] ?? '-') ?></td>
                                <td class="text-center"><?= esc($b['satuan'] ?? '-') ?></td>
                                <td class="text-end text-muted">
                                    Rp <?= number_format((float)($b['harga_beli'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    Rp <?= number_format((float)($b['harga_jual'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp <?= number_format((float)($b['harga_grosir'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td style="font-size: 7.5pt; line-height: 1.2;">
                                    <?php if (!empty($b['min_qty_a']) && $b['min_qty_a'] > 0): ?>
                                        <div><strong>A (≥<?= $b['min_qty_a'] ?>):</strong> <?= number_format((float)$b['harga_paket_a'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($b['min_qty_b']) && $b['min_qty_b'] > 0): ?>
                                        <div><strong>B (≥<?= $b['min_qty_b'] ?>):</strong> <?= number_format((float)$b['harga_paket_b'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($b['min_qty_c']) && $b['min_qty_c'] > 0): ?>
                                        <div><strong>C (≥<?= $b['min_qty_c'] ?>):</strong> <?= number_format((float)$b['harga_paket_c'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if (empty($b['min_qty_a']) && empty($b['min_qty_b']) && empty($b['min_qty_c'])): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold <?= ($b['stok'] ?? 0) <= ($b['stok_minimal'] ?? 0) ? 'text-danger' : 'text-primary' ?>">
                                    <?= number_format((float)($b['stok'] ?? 0), 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php endif; ?>

        <!-- FOOTER CATATAN & TANDA TANGAN -->
        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-end text-muted small" style="font-size: 8pt;">
            <div>
                <em>* Harga dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.</em><br>
                <em>* Dicetak otomatis melalui Sistem POS <?= esc($profil['nama_toko'] ?? 'Kios Sabun & Parfum') ?></em>
            </div>
            <div class="text-center" style="min-width: 150px;">
                <div>Mengetahui,</div>
                <div style="height: 50px;"></div>
                <div class="fw-bold text-dark border-top pt-1">( Pengelola Toko )</div>
            </div>
        </div>

    </div>

</body>
</html>

