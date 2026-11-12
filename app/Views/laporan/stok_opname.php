<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<style>
/* Styling khusus Print & Kartu Laporan Aset STO */
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 11pt !important;
    }
    #sidebar, .navbar, .btn, .no-print, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate {
        display: none !important;
    }
    #content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .card-modern {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        background: transparent !important;
    }
    .print-header {
        display: block !important;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 12px;
    }
    .table thead th {
        background: #f1f5f9 !important;
        color: #000000 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .table td {
        border: 1px solid #cbd5e1 !important;
    }
    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background: transparent !important;
    }
}

.print-header {
    display: none;
}

.asset-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-radius: 14px;
    color: #ffffff;
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
}

.asset-hero-card::after {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(13, 110, 253, 0.25) 0%, rgba(13, 110, 253, 0) 70%);
    border-radius: 50%;
}

.stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
</style>

<div class="card-modern p-4 mb-4">
    <!-- KOP CETAK RESMI (Hanya Muncul saat Window.Print) -->
    <div class="print-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-uppercase"><?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?></h3>
                <div class="small text-muted"><?= esc($profil['alamat'] ?? 'Metland Cileungsi, Bogor') ?></div>
                <div class="small text-muted">Telp / WA: <?= esc($profil['telepon'] ?? '-') ?></div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0 text-dark">LAPORAN VALUASI ASET &amp; AUDIT STO</h5>
                <span class="badge bg-dark text-white font-monospace"><?= esc($periode_label) ?></span>
                <div class="small text-muted mt-1">Dicetak: <?= date('d/m/Y H:i') ?> | Oleh: <?= esc(session()->get('nama_user') ?? 'Administrator') ?></div>
            </div>
        </div>
    </div>

    <!-- FILTER & TOOLBAR (No Print) -->
    <div class="no-print mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 pb-3 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white p-2 rounded-3">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Laporan Valuasi Aset Toko &amp; Audit Stok Opname</h5>
                        <small class="text-muted">Kontrol nilai modal persediaan per bulan untuk menjaga perputaran kas (Cash Flow) &amp; cegah kerugian susut/kehilangan</small>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-dark fw-semibold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Dokumen A4
                </button>
                <a href="<?= base_url('stok-opname/tambah') ?>" class="btn btn-primary fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Input Sesi STO Baru
                </a>
            </div>
        </div>

        <!-- FORM FILTER PERIODE -->
        <form action="<?= base_url('laporan/stok-opname') ?>" method="GET" class="row g-2 align-items-end mt-2 bg-light p-3 rounded-3 border">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Mode Periode</label>
                <select name="tipe_periode" id="tipePeriodeSelect" class="form-select form-select-sm" onchange="toggleFilterMode(this.value)">
                    <option value="bulan" <?= $tipe_periode === 'bulan' ? 'selected' : '' ?>>Bulanan (Month)</option>
                    <option value="rentang" <?= $tipe_periode === 'rentang' ? 'selected' : '' ?>>Rentang Tanggal Kustom</option>
                </select>
            </div>

            <div class="col-md-3" id="wrapperBulan" style="<?= $tipe_periode === 'rentang' ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Bulan</label>
                <input type="month" name="bulan" class="form-control form-control-sm" value="<?= esc($bulan) ?>">
            </div>

            <div class="col-md-4" id="wrapperRentang" style="<?= $tipe_periode !== 'rentang' ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-muted mb-1">Dari s/d Sampai Tanggal</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="tgl_awal" class="form-control" value="<?= esc($tgl_awal) ?>">
                    <span class="input-group-text">s/d</span>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= esc($tgl_akhir) ?>">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Filter Selisih</label>
                <select name="filter_selisih" class="form-select form-select-sm">
                    <option value="semua" <?= $filter_selisih === 'semua' ? 'selected' : '' ?>>Semua Hitungan</option>
                    <option value="selisih" <?= $filter_selisih === 'selisih' ? 'selected' : '' ?>>Hanya Ada Selisih (!=0)</option>
                    <option value="minus" <?= $filter_selisih === 'minus' ? 'selected' : '' ?>>Hanya Defisit / Kurang (-)</option>
                    <option value="plus" <?= $filter_selisih === 'plus' ? 'selected' : '' ?>>Hanya Surplus / Lebih (+)</option>
                    <option value="cocok" <?= $filter_selisih === 'cocok' ? 'selected' : '' ?>>Hanya Cocok (Akurat)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
                <a href="<?= base_url('laporan/stok-opname') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- HERO VALUASI ASET INVENTARIS TOKO -->
    <div class="asset-hero-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <span class="badge bg-primary bg-opacity-75 px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-safe2 me-1"></i> Ringkasan Total Aset Toko Saat Ini
                </span>
                <h2 class="fw-bold mb-1 text-white">
                    Rp <?= number_format($aset['total_hpp'], 0, ',', '.') ?>
                </h2>
                <p class="text-light text-opacity-75 mb-0 fs-6">
                    Total Nilai Modal Persediaan (HPP) yang tertanam di seluruh rak toko &amp; gudang. Terbagi dalam <strong><?= number_format($aset['total_sku'], 0, ',', '.') ?> SKU Barang</strong> (Total <strong><?= number_format($aset['total_unit'], 0, ',', '.') ?> Unit</strong>).
                </p>
            </div>
            <div class="col-lg-5">
                <div class="row g-2 text-dark">
                    <div class="col-6">
                        <div class="bg-white bg-opacity-95 p-3 rounded-3 shadow-sm border h-100">
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.72rem;">Potensi Nilai Jual</small>
                            <h5 class="fw-bold text-success mb-1">Rp <?= number_format($aset['total_jual'], 0, ',', '.') ?></h5>
                            <small class="text-muted" style="font-size: 0.75rem;">Estimasi total omzet jika seluruh stok laku</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white bg-opacity-95 p-3 rounded-3 shadow-sm border h-100">
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.72rem;">Potensi Margin Profit</small>
                            <h5 class="fw-bold text-primary mb-1">Rp <?= number_format($aset['potensi_margin'], 0, ',', '.') ?></h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;">
                                <?= number_format($aset['persen_margin'], 1, ',', '.') ?>% Margin
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI CARDS AUDIT STO PERIODE -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Sesi STO -->
        <div class="col-md-3">
            <div class="stat-box h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Sesi STO Terlaksana</small>
                    <span class="badge bg-light text-dark border"><i class="bi bi-calendar-check"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= $audit['total_sesi'] ?> <span class="fs-6 fw-normal text-muted">Sesi</span></h3>
                <small class="text-muted"><?= number_format($audit['total_dihitung'], 0, ',', '.') ?> total item fisik diperiksa</small>
            </div>
        </div>

        <!-- 2. Akurasi Hitungan Fisik vs Komputer -->
        <div class="col-md-3">
            <div class="stat-box h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Tingkat Akurasi Stok</small>
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2-circle"></i></span>
                </div>
                <h3 class="fw-bold <?= $audit['akurasi'] >= 95 ? 'text-success' : ($audit['akurasi'] >= 85 ? 'text-warning' : 'text-danger') ?> mb-0">
                    <?= number_format($audit['akurasi'], 1, ',', '.') ?>%
                </h3>
                <small class="text-muted"><?= $audit['count_cocok'] ?> item 100% cocok tanpa selisih</small>
            </div>
        </div>

        <!-- 3. Total Kebocoran Kas / Kerugian Stok Hilang (Defisit) -->
        <div class="col-md-3">
            <div class="stat-box h-100" style="border-left: 4px solid #ef4444;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-danger fw-bold text-uppercase" style="font-size: 0.72rem;">Kerugian Susut / Hilang</small>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-arrow-down-circle"></i> Defisit</span>
                </div>
                <h3 class="fw-bold text-danger mb-0">
                    Rp <?= number_format($audit['rugi_shrinkage_hpp'], 0, ',', '.') ?>
                </h3>
                <small class="text-danger"><?= number_format($audit['unit_minus'], 0, ',', '.') ?> unit berkurang (<?= $audit['count_minus'] ?> item)</small>
            </div>
        </div>

        <!-- 4. Surplus Fisik Tak Tercatat -->
        <div class="col-md-3">
            <div class="stat-box h-100" style="border-left: 4px solid #10b981;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-success fw-bold text-uppercase" style="font-size: 0.72rem;">Temuan Surplus Stok</small>
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-arrow-up-circle"></i> Lebih</span>
                </div>
                <h3 class="fw-bold text-success mb-0">
                    Rp <?= number_format($audit['surplus_hpp'], 0, ',', '.') ?>
                </h3>
                <small class="text-success"><?= number_format($audit['unit_plus'], 0, ',', '.') ?> unit bertambah (<?= $audit['count_plus'] ?> item)</small>
            </div>
        </div>
    </div>

    <!-- KOMPOSISI ASET PER KATEGORI PRODUK -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Komposisi Alokasi Modal &amp; Aset per Kategori Barang
            </h6>
            <small class="text-muted">Untuk menjaga perputaran modal agar tidak menumpuk di satu jenis barang</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kategori Produk</th>
                        <th class="text-center" style="width: 120px;">Jumlah SKU</th>
                        <th class="text-center" style="width: 140px;">Total Fisik</th>
                        <th class="text-end" style="width: 180px;">Valuasi Modal (HPP)</th>
                        <th class="text-end" style="width: 180px;">Potensi Omzet</th>
                        <th style="width: 200px;">Porsi dari Total Aset Toko</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategori_aset)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data barang tercatat.</td></tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        $totSkuKat = 0;
                        $totUnitKat = 0;
                        $totHppKat = 0;
                        $totJualKat = 0;
                        foreach ($kategori_aset as $kat): 
                            $totSkuKat += (int)$kat['total_sku'];
                            $totUnitKat += (float)$kat['total_unit'];
                            $totHppKat += (float)$kat['total_hpp'];
                            $totJualKat += (float)$kat['total_jual'];
                            $pctAset = $aset['total_hpp'] > 0 ? ($kat['total_hpp'] / $aset['total_hpp']) * 100 : 0;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong class="text-dark"><?= esc($kat['nama_kategori']) ?></strong>
                            </td>
                            <td class="text-center"><?= number_format($kat['total_sku'], 0, ',', '.') ?> SKU</td>
                            <td class="text-center fw-semibold"><?= number_format($kat['total_unit'], 0, ',', '.') ?> Unit</td>
                            <td class="text-end fw-bold text-dark">Rp <?= number_format($kat['total_hpp'], 0, ',', '.') ?></td>
                            <td class="text-end text-success fw-semibold">Rp <?= number_format($kat['total_jual'], 0, ',', '.') ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $pctAset) ?>%"></div>
                                    </div>
                                    <span class="small fw-bold text-muted" style="min-width: 45px;"><?= number_format($pctAset, 1, ',', '.') ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-uppercase text-dark">Total Seluruh Kategori</td>
                        <td class="text-center"><?= number_format($totSkuKat, 0, ',', '.') ?> SKU</td>
                        <td class="text-center"><?= number_format($totUnitKat, 0, ',', '.') ?> Unit</td>
                        <td class="text-end text-dark">Rp <?= number_format($totHppKat, 0, ',', '.') ?></td>
                        <td class="text-end text-success">Rp <?= number_format($totJualKat, 0, ',', '.') ?></td>
                        <td class="text-muted">100.0% Aset Terdata</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- DAFTAR SESI STO PADA PERIODE INI -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history text-secondary me-2"></i>Daftar Sesi Stok Opname Periode <?= esc($periode_label) ?>
            </h6>
            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                <?= count($sesi_sto) ?> Sesi Ditemukan
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 140px;">No. STO</th>
                        <th style="width: 150px;">Tanggal &amp; Waktu</th>
                        <th>Petugas Audit</th>
                        <th class="text-center">Item Dihitung</th>
                        <th class="text-center">Surplus (+)</th>
                        <th class="text-center">Defisit (-)</th>
                        <th class="text-end">Dampak Kas (Rp)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-print" style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sesi_sto)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada sesi stok opname pada periode ini. Lakukan audit fisik melalui menu Stok Opname.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sesi_sto as $s): ?>
                        <tr>
                            <td>
                                <strong class="font-monospace text-primary"><?= esc($s['no_sto']) ?></strong>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($s['tanggal'])) ?></td>
                            <td>
                                <i class="bi bi-person me-1 text-muted"></i><?= esc($s['nama_petugas']) ?>
                            </td>
                            <td class="text-center fw-semibold"><?= $s['total_item_dihitung'] ?> Item</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    +<?= (float)$s['total_selisih_plus'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    -<?= (float)$s['total_selisih_minus'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold <?= (float)$s['total_nilai_selisih'] < 0 ? 'text-danger' : ((float)$s['total_nilai_selisih'] > 0 ? 'text-success' : 'text-muted') ?>">
                                <?= (float)$s['total_nilai_selisih'] < 0 ? '-' : ((float)$s['total_nilai_selisih'] > 0 ? '+' : '') ?>Rp <?= number_format(abs((float)$s['total_nilai_selisih']), 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= esc($s['status']) ?></span>
                            </td>
                            <td class="text-center no-print">
                                <a href="<?= base_url('stok-opname/detail/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Lihat Detail Sesi">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RINCIAN AUDIT SELISIH STOK BARANG (SHRINKAGE BREAKDOWN) -->
    <div class="mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-list-check text-warning me-2"></i>Rincian Hitungan Fisik &amp; Audit Selisih Barang
                </h6>
                <small class="text-muted">Menampilkan <?= count($detail_items) ?> item barang berdasarkan filter yang dipilih</small>
            </div>
            <div class="no-print d-flex gap-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-flex align-items-center gap-1">
                    <i class="bi bi-exclamation-triangle-fill"></i> Defisit: <?= $audit['count_minus'] ?> Barang
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center gap-1">
                    <i class="bi bi-plus-circle-fill"></i> Surplus: <?= $audit['count_plus'] ?> Barang
                </span>
                <span class="badge bg-light text-dark border d-flex align-items-center gap-1">
                    <i class="bi bi-check-circle-fill text-success"></i> Cocok: <?= $audit['count_cocok'] ?> Barang
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableAuditItems" class="table table-hover align-middle mb-0" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 130px;">No. STO</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th>Barang &amp; Kategori</th>
                        <th class="text-center" style="width: 80px;">Satuan</th>
                        <th class="text-end" style="width: 100px;">Stok Komp</th>
                        <th class="text-end" style="width: 100px;">Stok Fisik</th>
                        <th class="text-center" style="width: 90px;">Selisih</th>
                        <th class="text-end" style="width: 120px;">HPP (Modal)</th>
                        <th class="text-end" style="width: 130px;">Dampak Kas</th>
                        <th>Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detail_items)): ?>
                        <?php 
                        $no = 1;
                        foreach ($detail_items as $it): 
                            $selisih = (float)$it['selisih'];
                            $nilaiSelisih = (float)$it['nilai_selisih'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <a href="<?= base_url('stok-opname/detail/' . $it['sto_id']) ?>" class="font-monospace fw-bold text-decoration-none">
                                    <?= esc($it['no_sto']) ?>
                                </a>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($it['tgl_sto'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($it['nama_barang']) ?></div>
                                <div class="small text-muted">
                                    <span class="badge bg-light text-dark border"><?= esc($it['nama_kategori']) ?></span>
                                    <span class="font-monospace ms-1"><?= esc($it['id_barang']) ?></span>
                                </div>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?= esc($it['satuan'] ?? 'pcs') ?></span></td>
                            <td class="text-end text-muted fw-semibold"><?= (float)$it['stok_sistem'] ?></td>
                            <td class="text-end fw-bold text-dark"><?= (float)$it['stok_fisik'] ?></td>
                            <td class="text-center">
                                <?php if ($selisih < 0): ?>
                                    <span class="badge bg-danger text-white fw-bold">
                                        <i class="bi bi-dash"></i><?= abs($selisih) ?>
                                    </span>
                                <?php elseif ($selisih > 0): ?>
                                    <span class="badge bg-success text-white fw-bold">
                                        <i class="bi bi-plus"></i><?= $selisih ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">0 Cocok</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted">Rp <?= number_format((float)$it['harga_beli'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold <?= $selisih < 0 ? 'text-danger' : ($selisih > 0 ? 'text-success' : 'text-muted') ?>">
                                <?= $selisih < 0 ? '-' : ($selisih > 0 ? '+' : '') ?>Rp <?= number_format(abs($nilaiSelisih), 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted">
                                <?= esc($it['catatan'] ?: '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TANDA TANGAN RESMI PADA CETAK DOKUMEN -->
    <div class="print-header mt-5 pt-4">
        <div class="row text-center">
            <div class="col-4">
                <p class="mb-5 small text-muted">Petugas Stok Opname,</p>
                <p class="fw-bold mb-0 text-decoration-underline"><?= esc(session()->get('nama_user') ?? 'Petugas Audit') ?></p>
                <small class="text-muted">Staff / Auditor</small>
            </div>
            <div class="col-4">
                <p class="mb-5 small text-muted">Diperiksa Oleh,</p>
                <p class="fw-bold mb-0 text-decoration-underline">_______________________</p>
                <small class="text-muted">Kepala Toko / Supervisor</small>
            </div>
            <div class="col-4">
                <p class="mb-5 small text-muted">Mengetahui &amp; Menyetujui,</p>
                <p class="fw-bold mb-0 text-decoration-underline">_______________________</p>
                <small class="text-muted">Owner / Pemilik Toko</small>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFilterMode(val) {
    var wrapperBulan = document.getElementById('wrapperBulan');
    var wrapperRentang = document.getElementById('wrapperRentang');
    if (val === 'rentang') {
        wrapperBulan.style.display = 'none';
        wrapperRentang.style.display = 'block';
    } else {
        wrapperBulan.style.display = 'block';
        wrapperRentang.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        if (!$.fn.DataTable.isDataTable('#tableAuditItems')) {
            $('#tableAuditItems').DataTable({
                language: {
                    search: "Cari Item:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Data tidak ditemukan",
                    emptyTable: "Tidak ada data rincian selisih barang pada periode ini",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                pageLength: 25,
                ordering: true,
                order: [[7, 'asc']] // Urutkan default selisih minus terlebih dahulu
            });
        }
    }
});
</script>

<?= $this->endSection() ?>

