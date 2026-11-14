<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    /* Styling Khusus Dashboard & Laporan Owner */
    .owner-card-kpi {
        border-radius: 14px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        background: #ffffff;
    }
    .owner-card-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.1);
    }
    .owner-tab-nav .nav-link {
        font-weight: 700;
        font-size: 0.92rem;
        padding: 12px 20px;
        color: #64748b;
        border-radius: 10px 10px 0 0;
        border: none;
        border-bottom: 3px solid transparent;
        background: transparent;
    }
    .owner-tab-nav .nav-link:hover {
        color: #0284c7;
        background: rgba(2, 132, 199, 0.04);
    }
    .owner-tab-nav .nav-link.active {
        color: #0284c7;
        border-bottom: 3px solid #0284c7;
        background: #ffffff;
    }
    .badge-prive {
        background: #fdf2f8;
        color: #be185d;
        border: 1px solid #fbcfe8;
        font-weight: 600;
    }
    .badge-toko {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
    }
    .table-ledger th {
        font-size: 0.76rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 8px !important;
        text-align: center;
        vertical-align: middle;
    }
    .table-ledger td {
        font-size: 0.83rem !important;
        padding: 8px 10px !important;
        vertical-align: middle;
    }
    .ledger-empty-row {
        opacity: 0.45;
        background-color: #fafafa !important;
    }
    .item-tag {
        display: inline-block;
        padding: 2px 7px;
        margin: 2px;
        background: #f1f5f9;
        border-radius: 6px;
        font-size: 0.75rem;
        color: #334155;
    }
</style>

<div class="container-fluid px-3 py-3">
    <!-- Header Halaman Owner -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark px-2.5 py-1.5 fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> KHUSUS OWNER &amp; DIREKSI
                </span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fw-semibold" style="font-size: 0.72rem;">
                    Buku Rekap Harian &amp; Aset Global
                </span>
            </div>
            <h3 class="fw-bolder text-slate-800 mb-0">Rekapitulasi Omzet, Profit &amp; Aset Toko</h3>
            <p class="text-muted small mb-0">Format pembukuan harian persis buku catatan owner fisik: Rekap Omzet, Gross Profit, Operasional Toko, Prive Pribadi &amp; Valuasi Aset.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Tombol Catat Pengeluaran Cepat -->
            <button type="button" class="btn btn-primary fw-bold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPengeluaranCepat">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Catat Pengeluaran (Toko/Pribadi)</span>
            </button>

            <!-- Tombol Cetak -->
            <a href="<?= base_url('laporan/owner/cetak?bulan=' . $bulan . '&tahun=' . $tahun . '&id_cabang=' . $filterCabang . '&tab=' . $tabActive) ?>" target="_blank" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-1.5 bg-white">
                <i class="bi bi-printer"></i>
                <span>Cetak Laporan</span>
            </a>

            <!-- Tombol Export Excel -->
            <a href="<?= base_url('laporan/owner/excel?bulan=' . $bulan . '&tahun=' . $tahun . '&id_cabang=' . $filterCabang . '&tab=' . $tabActive) ?>" class="btn btn-success fw-semibold d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Session -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= session()->getFlashdata('success') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= session()->getFlashdata('error') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Bar Filter Periode & Cabang -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0 !important;">
        <div class="card-body py-3 px-3">
            <form action="<?= base_url('laporan/owner') ?>" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="tab" id="formTabInput" value="<?= esc($tabActive) ?>">
                
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-xs fw-bold text-uppercase text-secondary mb-1">
                        <i class="bi bi-calendar-month text-primary me-1"></i> Pilih Bulan
                    </label>
                    <select name="bulan" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                        <?php foreach ($namaBulan as $mKey => $mName): ?>
                            <option value="<?= $mKey ?>" <?= $bulan == $mKey ? 'selected' : '' ?>><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label text-xs fw-bold text-uppercase text-secondary mb-1">
                        <i class="bi bi-calendar4 text-primary me-1"></i> Pilih Tahun
                    </label>
                    <select name="tahun" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4 col-sm-8">
                    <label class="form-label text-xs fw-bold text-uppercase text-secondary mb-1">
                        <i class="bi bi-shop text-primary me-1"></i> Filter Cabang Toko
                    </label>
                    <select name="id_cabang" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                        <option value="semua" <?= $filterCabang === 'semua' ? 'selected' : '' ?>>🌐 Konsolidasi Semua Cabang (Global)</option>
                        <?php foreach ($cabangList as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filterCabang == $c['id'] ? 'selected' : '' ?>><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Tampilkan
                    </button>
                    <a href="<?= base_url('laporan/owner') ?>" class="btn btn-outline-secondary btn-sm" title="Reset Periode">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Omzet Penjualan -->
        <div class="col-xl-3 col-md-6">
            <div class="owner-card-kpi p-3 h-100" style="border-top: 4px solid #0284c7;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-xs fw-bold text-uppercase text-muted">Total Omzet Penjualan</span>
                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-graph-up"></i> Sales</span>
                </div>
                <h4 class="fw-bolder text-slate-800 mb-1">Rp <?= number_format($rekap['summary']['grand_omzet'], 0, ',', '.') ?></h4>
                <div class="text-xs text-muted d-flex gap-2 flex-wrap">
                    <span>Cash: <strong>Rp <?= number_format($rekap['summary']['grand_cash'], 0, ',', '.') ?></strong></span>
                    <span>•</span>
                    <span>QRIS: <strong>Rp <?= number_format($rekap['summary']['grand_qris'], 0, ',', '.') ?></strong></span>
                    <span>•</span>
                    <span>TF: <strong>Rp <?= number_format($rekap['summary']['grand_tf'], 0, ',', '.') ?></strong></span>
                </div>
            </div>
        </div>

        <!-- 2. Gross Profit Toko (Profit Baru) -->
        <div class="col-xl-3 col-md-6">
            <div class="owner-card-kpi p-3 h-100" style="border-top: 4px solid #10b981;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-xs fw-bold text-uppercase text-muted">Gross Profit Penjualan</span>
                    <span class="badge bg-success-subtle text-success"><i class="bi bi-piggy-bank-fill"></i> Laba Kotor</span>
                </div>
                <h4 class="fw-bolder text-emerald-600 mb-1">Rp <?= number_format($rekap['summary']['grand_profit_baru'], 0, ',', '.') ?></h4>
                <div class="text-xs text-muted">
                    HPP Modal Terjual: <strong>Rp <?= number_format($rekap['summary']['grand_hpp'], 0, ',', '.') ?></strong>
                    <?php if ($rekap['summary']['grand_omzet'] > 0): ?>
                        <span class="badge bg-success-subtle text-success ms-1">Margin <?= number_format(($rekap['summary']['grand_profit_baru'] / $rekap['summary']['grand_omzet']) * 100, 1) ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 3. Pengeluaran Toko vs Prive -->
        <div class="col-xl-3 col-md-6">
            <div class="owner-card-kpi p-3 h-100" style="border-top: 4px solid #f59e0b;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-xs fw-bold text-uppercase text-muted">Pengeluaran &amp; Prive</span>
                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-receipt"></i> Biaya</span>
                </div>
                <h4 class="fw-bolder text-slate-800 mb-1">Rp <?= number_format($rekap['summary']['grand_pengeluaran_toko'] + $rekap['summary']['grand_pengeluaran_pribadi'], 0, ',', '.') ?></h4>
                <div class="text-xs d-flex gap-2 flex-wrap">
                    <span class="badge-toko px-1.5 py-0.5 rounded">Toko: Rp <?= number_format($rekap['summary']['grand_pengeluaran_toko'], 0, ',', '.') ?></span>
                    <span class="badge-prive px-1.5 py-0.5 rounded">Pribadi: Rp <?= number_format($rekap['summary']['grand_pengeluaran_pribadi'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- 4. Valuasi Total Aset Toko Global -->
        <div class="col-xl-3 col-md-6">
            <div class="owner-card-kpi p-3 h-100" style="border-top: 4px solid #6366f1;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-xs fw-bold text-uppercase text-muted">Valuasi Modal Stok Global</span>
                    <span class="badge bg-indigo-subtle text-primary"><i class="bi bi-gem"></i> Asset</span>
                </div>
                <h4 class="fw-bolder text-indigo-600 mb-1">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></h4>
                <div class="text-xs text-muted">
                    Potensi Omzet Retail: <strong>Rp <?= number_format($aset['global_nilai_jual'], 0, ',', '.') ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white p-0 border-bottom">
            <ul class="nav nav-tabs owner-tab-nav px-3 pt-2" id="ownerTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $tabActive === 'profit' ? 'active' : '' ?>" id="profit-tab" data-bs-toggle="tab" data-bs-target="#tab-profit" type="button" role="tab" onclick="switchTab('profit')">
                        <i class="bi bi-journal-bookmark-fill me-1.5 text-warning"></i>
                        1. Rekap Profit &amp; Pengeluaran (Format Buku Maret)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $tabActive === 'omzet' ? 'active' : '' ?>" id="omzet-tab" data-bs-toggle="tab" data-bs-target="#tab-omzet" type="button" role="tab" onclick="switchTab('omzet')">
                        <i class="bi bi-cash-stack me-1.5 text-primary"></i>
                        2. Rekap Omzet &amp; Kas / Modal (Format Buku Februari)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $tabActive === 'aset' ? 'active' : '' ?>" id="aset-tab" data-bs-toggle="tab" data-bs-target="#tab-aset" type="button" role="tab" onclick="switchTab('aset')">
                        <i class="bi bi-buildings-fill me-1.5 text-info"></i>
                        3. Valuasi &amp; Aset Toko Global (Multi-Cabang)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="ownerTabContent">
                
                <!-- ======================================================== -->
                <!-- TAB 1: REKAP PROFIT & PENGELUARAN (BUKU MARET)           -->
                <!-- ======================================================== -->
                <div class="tab-pane fade <?= $tabActive === 'profit' ? 'show active' : '' ?>" id="tab-profit" role="tabpanel">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="fw-bold text-slate-800"><i class="bi bi-book me-1 text-primary"></i> Buku Rekapitulasi Profit Harian: Periode <?= $namaBulan[$bulan] ?> <?= $tahun ?></span>
                            <span class="text-muted text-xs ms-2">(Profit Akhir hari ini digulung otomatis menjadi Profit Awal hari berikutnya)</span>
                        </div>
                        <div class="text-xs">
                            <span class="badge bg-success-subtle text-success me-2">Profit Toko Riil: Rp <?= number_format($rekap['summary']['grand_profit_bersih_toko'], 0, ',', '.') ?></span>
                            <span class="badge bg-info-subtle text-info">Profit Akhir Setelah Prive: Rp <?= number_format($rekap['summary']['grand_profit_akhir'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-ledger mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">Tgl</th>
                                    <th style="width: 80px;">Hari</th>
                                    <th style="width: 120px;" class="text-end">Profit Awal</th>
                                    <th style="width: 120px;" class="text-end bg-success-subtle text-success">Profit Baru</th>
                                    <th style="width: 125px;" class="text-end">Total Profit</th>
                                    <th style="min-width: 220px;" class="text-start">Pengeluaran TOKO (Operasional)</th>
                                    <th style="width: 110px;" class="text-end">Total Toko</th>
                                    <th style="min-width: 220px;" class="text-start">Pengeluaran PRIBADI (Prive)</th>
                                    <th style="width: 110px;" class="text-end">Total Pribadi</th>
                                    <th style="width: 130px;" class="text-end bg-primary-subtle text-primary">Profit Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rekap['rows'] as $r): ?>
                                    <tr class="<?= !$r['has_data'] ? 'ledger-empty-row' : '' ?>">
                                        <td class="text-center fw-bold"><?= $r['tgl_num'] ?></td>
                                        <td class="text-center <?= in_array($r['hari'], ['Minggu', 'Sabtu']) ? 'text-danger fw-bold' : '' ?>"><?= $r['hari'] ?></td>
                                        
                                        <!-- Profit Awal -->
                                        <td class="text-end text-muted"><?= $r['has_data'] ? number_format($r['profit_awal'], 0, ',', '.') : '-' ?></td>
                                        
                                        <!-- Profit Baru -->
                                        <td class="text-end fw-bold <?= $r['profit_baru'] > 0 ? 'text-success' : 'text-muted' ?>">
                                            <?= $r['profit_baru'] > 0 ? number_format($r['profit_baru'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Total Profit (Awal + Baru) -->
                                        <td class="text-end fw-semibold"><?= $r['has_data'] ? number_format($r['total_profit'], 0, ',', '.') : '-' ?></td>
                                        
                                        <!-- Pengeluaran Toko Detail -->
                                        <td class="text-start">
                                            <?php if (!empty($r['pengeluaran_toko_items'])): ?>
                                                <?php foreach ($r['pengeluaran_toko_items'] as $it): ?>
                                                    <span class="item-tag">
                                                        <?= esc($it['nama']) ?> <strong class="text-danger">(-<?= number_format($it['total'] / 1000, 0) ?>k)</strong>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Total Toko -->
                                        <td class="text-end <?= $r['pengeluaran_toko'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            <?= $r['pengeluaran_toko'] > 0 ? number_format($r['pengeluaran_toko'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Pengeluaran Pribadi Detail -->
                                        <td class="text-start">
                                            <?php if (!empty($r['pengeluaran_pribadi_items'])): ?>
                                                <?php foreach ($r['pengeluaran_pribadi_items'] as $it): ?>
                                                    <span class="item-tag" style="background: #fdf2f8; border: 1px solid #fbcfe8;">
                                                        <?= esc($it['nama']) ?> <strong class="text-danger">(-<?= number_format($it['total'] / 1000, 0) ?>k)</strong>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Total Pribadi -->
                                        <td class="text-end <?= $r['pengeluaran_pribadi'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            <?= $r['pengeluaran_pribadi'] > 0 ? number_format($r['pengeluaran_pribadi'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Profit Akhir -->
                                        <td class="text-end fw-bolder text-primary">
                                            <?= $r['has_data'] ? number_format($r['profit_akhir'], 0, ',', '.') : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr style="border-top: 2px solid #0284c7;">
                                    <td colspan="3" class="text-center text-uppercase">TOTAL REKAPITULASI (JUMLAH)</td>
                                    <td class="text-end text-success fs-6">Rp <?= number_format($rekap['summary']['grand_profit_baru'], 0, ',', '.') ?></td>
                                    <td class="text-end">-</td>
                                    <td class="text-center">Total Beban Toko:</td>
                                    <td class="text-end text-danger fs-6">Rp <?= number_format($rekap['summary']['grand_pengeluaran_toko'], 0, ',', '.') ?></td>
                                    <td class="text-center">Total Prive Pribadi:</td>
                                    <td class="text-end text-danger fs-6">Rp <?= number_format($rekap['summary']['grand_pengeluaran_pribadi'], 0, ',', '.') ?></td>
                                    <td class="text-end text-primary fs-6">Rp <?= number_format($rekap['summary']['grand_profit_akhir'], 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 2: REKAP OMZET, KAS & MODAL (BUKU FEBRUARI)          -->
                <!-- ======================================================== -->
                <div class="tab-pane fade <?= $tabActive === 'omzet' ? 'show active' : '' ?>" id="tab-omzet" role="tabpanel">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="fw-bold text-slate-800"><i class="bi bi-book me-1 text-primary"></i> Buku Rekapitulasi Omzet &amp; Belanja Supplier: Periode <?= $namaBulan[$bulan] ?> <?= $tahun ?></span>
                            <span class="text-muted text-xs ms-2">(Rincian Metode Pembayaran, Modal HPP Terjual &amp; Belanja Kulakan Supplier)</span>
                        </div>
                        <div class="text-xs">
                            <span class="badge bg-primary-subtle text-primary me-2">Total Omzet: Rp <?= number_format($rekap['summary']['grand_omzet'], 0, ',', '.') ?></span>
                            <span class="badge bg-warning-subtle text-warning">Total Restock Supplier: Rp <?= number_format($rekap['summary']['grand_pembelian'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-ledger mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">Tgl</th>
                                    <th style="width: 75px;">Hari</th>
                                    <th style="width: 120px;" class="text-end bg-primary-subtle text-primary">OMZET</th>
                                    <th style="width: 100px;" class="text-end">QR (QRIS)</th>
                                    <th style="width: 95px;" class="text-end">TF (Bank)</th>
                                    <th style="width: 100px;" class="text-end">Cash (Tunai)</th>
                                    <th style="width: 105px;" class="text-end">M. AWAL</th>
                                    <th style="width: 105px;" class="text-end">SALDO LACI</th>
                                    <th style="width: 110px;" class="text-end">MODAL (HPP)</th>
                                    <th style="width: 120px;" class="text-end">TOTAL MODAL</th>
                                    <th style="min-width: 230px;" class="text-start">PENGELUARAN RESTOCK (Supplier)</th>
                                    <th style="width: 110px;" class="text-end">TOTAL BELI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rekap['rows'] as $r): ?>
                                    <tr class="<?= !$r['has_data'] ? 'ledger-empty-row' : '' ?>">
                                        <td class="text-center fw-bold"><?= $r['tgl_num'] ?></td>
                                        <td class="text-center <?= in_array($r['hari'], ['Minggu', 'Sabtu']) ? 'text-danger fw-bold' : '' ?>"><?= $r['hari'] ?></td>
                                        
                                        <!-- Omzet -->
                                        <td class="text-end fw-bold text-primary">
                                            <?= $r['omzet'] > 0 ? number_format($r['omzet'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- QRIS -->
                                        <td class="text-end text-muted">
                                            <?= $r['qris'] > 0 ? number_format($r['qris'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Transfer -->
                                        <td class="text-end text-muted">
                                            <?= $r['transfer'] > 0 ? number_format($r['transfer'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Cash -->
                                        <td class="text-end fw-semibold text-slate-800">
                                            <?= $r['cash'] > 0 ? number_format($r['cash'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- M. Awal -->
                                        <td class="text-end text-muted">
                                            <?= $r['kas_awal'] > 0 ? number_format($r['kas_awal'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Saldo Laci -->
                                        <td class="text-end fw-semibold">
                                            <?= $r['has_data'] ? number_format($r['saldo_laci'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Modal (HPP) -->
                                        <td class="text-end text-secondary">
                                            <?= $r['hpp_modal'] > 0 ? number_format($r['hpp_modal'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Total Modal Berjalan -->
                                        <td class="text-end text-muted">
                                            <?= $r['has_data'] ? number_format($r['total_modal_berjalan'], 0, ',', '.') : '-' ?>
                                        </td>
                                        
                                        <!-- Supplier Items -->
                                        <td class="text-start">
                                            <?php if (!empty($r['pembelian_items'])): ?>
                                                <?php foreach ($r['pembelian_items'] as $pb): ?>
                                                    <span class="item-tag" style="background: #eef2ff; border: 1px solid #c7d2fe;">
                                                        <i class="bi bi-truck me-1"></i><?= esc($pb['supplier']) ?> <strong class="text-primary">(Rp <?= number_format($pb['total'], 0, ',', '.') ?>)</strong>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Total Pembelian -->
                                        <td class="text-end fw-bold text-danger">
                                            <?= $r['total_pembelian'] > 0 ? number_format($r['total_pembelian'], 0, ',', '.') : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr style="border-top: 2px solid #0284c7;">
                                    <td colspan="2" class="text-center text-uppercase">JUMLAH REKAP</td>
                                    <td class="text-end text-primary fs-6">Rp <?= number_format($rekap['summary']['grand_omzet'], 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($rekap['summary']['grand_qris'], 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($rekap['summary']['grand_tf'], 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($rekap['summary']['grand_cash'], 0, ',', '.') ?></td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">Rp <?= number_format($rekap['summary']['grand_hpp'], 0, ',', '.') ?></td>
                                    <td class="text-end">-</td>
                                    <td class="text-center">Total Kulakan Supplier:</td>
                                    <td class="text-end text-danger fs-6">Rp <?= number_format($rekap['summary']['grand_pembelian'], 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 3: VALUASI & ASET TOKO GLOBAL (MULTI-CABANG)         -->
                <!-- ======================================================== -->
                <div class="tab-pane fade <?= $tabActive === 'aset' ? 'show active' : '' ?>" id="tab-aset" role="tabpanel">
                    <div class="p-4">
                        <!-- Ringkasan Global Aset -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="p-3 bg-primary-subtle border border-primary-subtle rounded-3 text-center">
                                    <div class="text-xs fw-bold text-uppercase text-primary mb-1">Total Nilai Modal Stok (HPP)</div>
                                    <h4 class="fw-bolder text-primary mb-0">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></h4>
                                    <small class="text-muted">Aset Inventaris Riil Toko</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-info-subtle border border-info-subtle rounded-3 text-center">
                                    <div class="text-xs fw-bold text-uppercase text-info mb-1">Total Nilai Jual Retail</div>
                                    <h4 class="fw-bolder text-info mb-0">Rp <?= number_format($aset['global_nilai_jual'], 0, ',', '.') ?></h4>
                                    <small class="text-muted">Potensi Omzet Keseluruhan</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-success-subtle border border-success-subtle rounded-3 text-center">
                                    <div class="text-xs fw-bold text-uppercase text-success mb-1">Potensi Keuntungan (Margin)</div>
                                    <h4 class="fw-bolder text-success mb-0">Rp <?= number_format($aset['global_potensi_profit'], 0, ',', '.') ?></h4>
                                    <small class="text-muted">Jika seluruh stok terjual</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-secondary-subtle border border-secondary-subtle rounded-3 text-center">
                                    <div class="text-xs fw-bold text-uppercase text-secondary mb-1">Total Unit &amp; Item Fisik</div>
                                    <h4 class="fw-bolder text-slate-800 mb-0"><?= number_format($aset['global_qty'], 0, ',', '.') ?> Unit</h4>
                                    <small class="text-muted">Dari <?= number_format($aset['global_item'], 0, ',', '.') ?> Item Produk Aktif</small>
                                </div>
                            </div>
                        </div>

                        <!-- 1. Valuasi per Cabang Toko -->
                        <div class="card border mb-4">
                            <div class="card-header bg-slate-900 text-white py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                <span class="fw-bold fs-6"><i class="bi bi-shop me-2 text-warning"></i> 1. Valuasi Aset Inventaris Stok per Cabang</span>
                                <span class="badge bg-warning text-dark fw-bold">Semua Cabang Aktif</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Cabang Toko</th>
                                            <th>Alamat / Lokasi</th>
                                            <th class="text-center">Total Item</th>
                                            <th class="text-center">Total Qty Stok</th>
                                            <th class="text-end">Nilai Modal (HPP)</th>
                                            <th class="text-end">Nilai Jual Retail</th>
                                            <th class="text-end text-success">Potensi Profit</th>
                                            <th class="text-end bg-primary-subtle text-primary">Total Aset Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aset['per_cabang'] as $cb): ?>
                                            <tr>
                                                <td class="fw-bold text-slate-800">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                    <?= esc($cb['nama_cabang']) ?>
                                                </td>
                                                <td class="text-muted text-xs"><?= esc($cb['alamat'] ?: 'Belum diisi') ?></td>
                                                <td class="text-center"><?= number_format($cb['total_item'], 0, ',', '.') ?></td>
                                                <td class="text-center fw-semibold"><?= number_format($cb['total_qty'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-semibold">Rp <?= number_format($cb['nilai_modal'], 0, ',', '.') ?></td>
                                                <td class="text-end">Rp <?= number_format($cb['nilai_jual'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-bold text-success">Rp <?= number_format($cb['potensi_profit'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-bolder text-primary">Rp <?= number_format($cb['nilai_modal'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-center text-uppercase">TOTAL KONSOLIDASI SELURUH CABANG</td>
                                            <td class="text-center"><?= number_format($aset['global_item'], 0, ',', '.') ?></td>
                                            <td class="text-center"><?= number_format($aset['global_qty'], 0, ',', '.') ?></td>
                                            <td class="text-end text-primary fs-6">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></td>
                                            <td class="text-end fs-6">Rp <?= number_format($aset['global_nilai_jual'], 0, ',', '.') ?></td>
                                            <td class="text-end text-success fs-6">Rp <?= number_format($aset['global_potensi_profit'], 0, ',', '.') ?></td>
                                            <td class="text-end text-primary fs-6">Rp <?= number_format($aset['global_nilai_modal'], 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- 2. Breakdown Valuasi per Kategori Produk -->
                        <div class="card border">
                            <div class="card-header bg-slate-900 text-white py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                <span class="fw-bold fs-6"><i class="bi bi-tags-fill me-2 text-info"></i> 2. Komposisi Valuasi Modal Aset per Kategori Produk</span>
                                <span class="badge bg-info-subtle text-info fw-bold">Urut Aset Terbesar</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Kategori Produk</th>
                                            <th class="text-center">Jumlah Item</th>
                                            <th class="text-center">Total Unit Stok</th>
                                            <th class="text-end">Total Nilai Modal (HPP)</th>
                                            <th class="text-end">Total Nilai Jual Retail</th>
                                            <th class="text-end text-success">Potensi Profit</th>
                                            <th class="text-center">Porsi Aset (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aset['kategori'] as $kat): ?>
                                            <?php 
                                                $pct = $aset['global_nilai_modal'] > 0 ? ($kat['total_modal'] / $aset['global_nilai_modal']) * 100 : 0; 
                                                $marginKat = $kat['total_jual'] - $kat['total_modal'];
                                            ?>
                                            <tr>
                                                <td class="fw-bold text-slate-800">
                                                    <i class="bi bi-folder2-open text-primary me-1"></i>
                                                    <?= esc($kat['kategori']) ?>
                                                </td>
                                                <td class="text-center"><?= number_format($kat['total_item'], 0, ',', '.') ?></td>
                                                <td class="text-center"><?= number_format($kat['total_qty'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-semibold">Rp <?= number_format($kat['total_modal'], 0, ',', '.') ?></td>
                                                <td class="text-end">Rp <?= number_format($kat['total_jual'], 0, ',', '.') ?></td>
                                                <td class="text-end fw-semibold text-success">Rp <?= number_format($marginKat, 0, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary-subtle text-secondary fw-bold"><?= number_format($pct, 1) ?>%</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: CATAT PENGELUARAN CEPAT (TOKO VS PRIBADI)         -->
<!-- ======================================================== -->
<div class="modal fade" id="modalPengeluaranCepat" tabindex="-1" aria-labelledby="modalPengeluaranCepatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0284c7 100%); border-bottom: 2px solid #38bdf8;">
                <h5 class="modal-title fw-bold" id="modalPengeluaranCepatLabel">
                    <i class="bi bi-pencil-square me-2"></i>Catat Pengeluaran Cepat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= base_url('laporan/owner/simpan-pengeluaran') ?>" method="POST" id="formPengeluaranOwner">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <!-- 1. Pilihan Tipe Pengeluaran (Toko vs Pribadi) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700">Jenis / Tipe Pengeluaran <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="tipe_pengeluaran" id="tipe_toko" value="toko" checked autocomplete="off" onchange="toggleTipeHint()">
                                <label class="btn btn-outline-success w-100 py-2.5 text-start fw-bold d-flex align-items-center gap-2" for="tipe_toko">
                                    <i class="bi bi-shop fs-5"></i>
                                    <div>
                                        <div class="text-sm">Beban Toko</div>
                                        <small class="text-xs fw-normal opacity-75">Operasional Toko</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="tipe_pengeluaran" id="tipe_pribadi" value="pribadi" autocomplete="off" onchange="toggleTipeHint()">
                                <label class="btn btn-outline-danger w-100 py-2.5 text-start fw-bold d-flex align-items-center gap-2" for="tipe_pribadi">
                                    <i class="bi bi-person-fill fs-5"></i>
                                    <div>
                                        <div class="text-sm">Pribadi / Prive</div>
                                        <small class="text-xs fw-normal opacity-75">Keperluan Rumah/Owner</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="tipeHint" class="form-text text-xs mt-1.5 text-success">
                            <i class="bi bi-info-circle me-1"></i> Contoh Beban Toko: Lakban, kresek, bensin, token PLN, makan karyawan, galon, dll.
                        </div>
                    </div>

                    <!-- 2. Tanggal Transaksi -->
                    <div class="mb-3">
                        <label for="inputTanggal" class="form-label fw-bold text-slate-700">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="inputTanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- 3. Cabang -->
                    <div class="mb-3">
                        <label for="inputCabang" class="form-label fw-bold text-slate-700">Cabang Toko</label>
                        <select name="id_cabang" id="inputCabang" class="form-select">
                            <?php foreach ($cabangList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['nama_cabang']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 4. Keperluan / Nama Pengeluaran -->
                    <div class="mb-3">
                        <label for="inputNama" class="form-label fw-bold text-slate-700">Keperluan / Keterangan Biaya <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputNama" name="nama_keuangan" placeholder="Contoh: Lakban 2 roll / Makan Karyawan / SPP Anak" required autocomplete="off" list="listKeperluanSering">
                        <datalist id="listKeperluanSering">
                            <option value="Beli Lakban & Kresek">
                            <option value="Makan Karyawan">
                            <option value="Air Galon Toko">
                            <option value="Bensin Motor Operasional">
                            <option value="Token Listrik PLN">
                            <option value="Beli Sabun Cuci & Sapu">
                            <option value="Belanjaan Ibu">
                            <option value="Bayar Paket Ibu / Keluarga">
                            <option value="SPP Anak">
                            <option value="Berobat / Vitamin">
                            <option value="Uang Dapur">
                        </datalist>
                    </div>

                    <!-- 5. Nominal (Rp) -->
                    <div class="mb-3">
                        <label for="inputNominal" class="form-label fw-bold text-slate-700">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">Rp</span>
                            <input type="text" class="form-control form-control-lg fw-bold text-end" id="inputNominal" name="total" placeholder="0" required onkeyup="formatRupiahInput(this)">
                        </div>
                    </div>

                    <!-- 6. Catatan Tambahan (Opsional) -->
                    <div class="mb-2">
                        <label for="inputKet" class="form-label fw-semibold text-muted text-xs">Catatan Tambahan (Opsional)</label>
                        <input type="text" class="form-control form-control-sm" id="inputKet" name="keterangan" placeholder="Contoh: Titip ke kasir / Nota terlampir">
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabKey) {
        document.getElementById('formTabInput').value = tabKey;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', currentUrl);
    }

    function toggleTipeHint() {
        const isPribadi = document.getElementById('tipe_pribadi').checked;
        const hintEl = document.getElementById('tipeHint');
        if (isPribadi) {
            hintEl.className = 'form-text text-xs mt-1.5 text-danger';
            hintEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> Contoh Pribadi/Prive: Belanja dapur, paket ibu/anak, SPP sekolah, berobat dokter, baju, dll.';
        } else {
            hintEl.className = 'form-text text-xs mt-1.5 text-success';
            hintEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> Contoh Beban Toko: Lakban, kresek, bensin, token PLN, makan karyawan, galon, dll.';
        }
    }

    function formatRupiahInput(input) {
        let val = input.value.replace(/[^,\d]/g, '').toString();
        let split = val.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = rupiah;
    }
</script>

<?= $this->endSection() ?>
