<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-cash-coin text-success me-2"></i>Laporan Arus Kas &amp; Valuasi Kekayaan Toko
        </h4>
        <p class="text-muted small mb-0">
            Monitoring mutasi uang masuk (Cash In), uang keluar (Cash Out), saldo kas berjalan, dan total kekayaan aset toko per periode bulanan / rentang tanggal.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php
            $queryFilter = http_build_query([
                'mode_periode' => $mode_periode,
                'bulan'        => $bulan,
                'tgl_mulai'    => $tgl_mulai,
                'tgl_selesai'  => $tgl_selesai,
                'id_cabang'    => $filter_cabang ?? '',
                'jenis'        => $jenis,
                'q'            => $q
            ]);
        ?>
        <a href="<?= base_url('laporan/arus-kas/cetak?' . $queryFilter) ?>" target="_blank" class="btn btn-danger rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </a>
        <a href="<?= base_url('laporan/arus-kas/excel?' . $queryFilter) ?>" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- FORM FILTER PERIODE                            -->
<!-- ============================================== -->
<div class="card-modern p-4 mb-4 border-top border-4 border-success">
    <form action="<?= base_url('laporan/arus-kas') ?>" method="GET" id="form-filter-aruskas">
        <div class="row g-3 align-items-end">
            
            <!-- Pilihan Mode Periode -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-funnel-fill text-success me-1"></i>Mode Periode:
                </label>
                <select name="mode_periode" id="mode_periode" class="form-select select2 fw-semibold">
                    <option value="bulan" <?= $mode_periode === 'bulan' ? 'selected' : '' ?>>Pilihan Bulanan (1 Bulan Penuh)</option>
                    <option value="rentang" <?= $mode_periode === 'rentang' ? 'selected' : '' ?>>Custom Rentang Tanggal</option>
                </select>
            </div>

            <!-- Field Pilih Bulan (Muncul jika mode = bulan) -->
            <div class="col-12 col-md-3" id="wrapper-bulan" style="<?= $mode_periode === 'rentang' ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-dark mb-1">Pilih Bulan &amp; Tahun:</label>
                <input type="month" name="bulan" id="input_bulan" class="form-control fw-semibold" value="<?= esc($bulan) ?>">
            </div>

            <!-- Field Rentang Tanggal (Muncul jika mode = rentang) -->
            <div class="col-6 col-md-3" id="wrapper-tgl-mulai" style="<?= $mode_periode !== 'rentang' ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-dark mb-1">Tanggal Mulai:</label>
                <input type="date" name="tgl_mulai" id="input_tgl_mulai" class="form-control" value="<?= esc($tgl_mulai) ?>">
            </div>

            <div class="col-6 col-md-3" id="wrapper-tgl-selesai" style="<?= $mode_periode !== 'rentang' ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-dark mb-1">Tanggal Selesai:</label>
                <input type="date" name="tgl_selesai" id="input_tgl_selesai" class="form-control" value="<?= esc($tgl_selesai) ?>">
            </div>

            <!-- Filter Cabang -->
            <?php if (!empty($is_admin)): ?>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-shop me-1 text-primary"></i>Cabang Operasional:
                </label>
                <select name="id_cabang" class="form-select select2 fw-semibold">
                    <option value="semua">-- Semua Cabang --</option>
                    <?php foreach ($cabang_list as $cb): ?>
                        <option value="<?= $cb['id'] ?>" <?= ($filter_cabang == $cb['id']) ? 'selected' : '' ?>>
                            <?= esc($cb['nama_cabang']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-shop me-1 text-success"></i>Cabang Tugas:
                </label>
                <div class="badge bg-light text-dark border p-2 w-100 text-truncate text-start d-flex align-items-center gap-1" style="height: 38px;">
                    <i class="bi bi-lock-fill text-muted"></i>
                    <span class="text-truncate fw-semibold"><?= esc(session()->get('nama_cabang') ?? 'KSP Pusat') ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filter Kategori Arus Kas -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Jenis Mutasi Kas:</label>
                <select name="jenis" class="form-select select2">
                    <option value="semua">-- Semua Mutasi --</option>
                    <option value="Pemasukan" <?= $jenis === 'Pemasukan' ? 'selected' : '' ?>>Pemasukan Saja (Cash In)</option>
                    <option value="Pengeluaran" <?= $jenis === 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran Saja (Cash Out)</option>
                    <option value="Kas Awal" <?= $jenis === 'Kas Awal' ? 'selected' : '' ?>>Kas Awal / Modal Awal</option>
                </select>
            </div>

            <!-- Tombol Terapkan & Reset -->
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                    <i class="bi bi-filter me-1"></i> Tampilkan
                </button>
                <a href="<?= base_url('laporan/arus-kas') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

        </div>

        <!-- Tombol Pintas Preset Periode -->
        <div class="d-flex flex-wrap align-items-center gap-1 mt-3 pt-3 border-top">
            <span class="small text-muted me-2 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pintas Cepat:</span>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-quick-month" data-month="<?= date('Y-m') ?>">Bulan Ini (<?= date('M Y') ?>)</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-quick-month" data-month="<?= date('Y-m', strtotime('-1 month')) ?>">Bulan Lalu</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-quick-month" data-month="<?= date('Y-m', strtotime('-2 month')) ?>"><?= date('M Y', strtotime('-2 month')) ?></button>
        </div>
    </form>
</div>

<!-- ============================================== -->
<!-- ============================================== -->
<!-- HERO CARD: VALUASI TOTAL KEKAYAAN TOKO (1 SCREEN)-->
<!-- ============================================== -->
<div class="card border-0 rounded-4 p-4 mb-4 text-white shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%) !important; color: #ffffff !important;">
    <!-- Bagian Atas: Grand Total Valuasi Kekayaan Toko -->
    <div class="row align-items-center g-3 mb-3 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.15) !important;">
        <div class="col-12 col-lg-8">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase shadow-sm">
                    <i class="bi bi-shield-check me-1"></i> Posisi Kekayaan Toko (Periode: <?= esc($periode_label) ?>)
                </span>
                <span class="badge bg-light text-dark px-2 py-1 rounded-pill fw-semibold">
                    <i class="bi bi-shop me-1"></i> <?= esc($profil['nama_toko'] ?? 'Kios Sabun & Parfum') ?>
                </span>
            </div>
            <h5 class="text-white-50 mb-1" style="color: rgba(255, 255, 255, 0.8) !important; font-size: 0.95rem;">
                TOTAL ESTIMASI KEKAYAAN BERSIH TOKO (NET ASSET VALUATION):
            </h5>
            <h1 class="display-4 fw-bolder text-white mb-2" style="letter-spacing: -1.5px; color: #ffffff !important; line-height: 1.1;">
                Rp <?= number_format($total_kekayaan_toko, 0, ',', '.') ?>
            </h1>
            <p class="small mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">
                Kombinasi utuh 3 aset toko: <strong>Uang Kas Tunai</strong> di laci/toko + <strong>Uang di Rekening Bank/QRIS</strong> + <strong>Nilai Modal Persediaan Seluruh Stok Barang</strong>.
            </p>
        </div>

        <div class="col-12 col-lg-4 text-lg-end">
            <!-- Komposisi Persentase Aset -->
            <?php 
                $persenKas = ($total_kekayaan_toko > 0) ? round(($saldo_kas_tunai / $total_kekayaan_toko) * 100, 1) : 0;
                $persenBank = ($total_kekayaan_toko > 0) ? round(($saldo_kas_bank / $total_kekayaan_toko) * 100, 1) : 0;
                $persenStok = ($total_kekayaan_toko > 0) ? round(($nilai_aset_hpp / $total_kekayaan_toko) * 100, 1) : 0;
            ?>
            <div class="p-3 rounded-3 text-start" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold" style="color: rgba(255, 255, 255, 0.85);">Komposisi Aset Toko:</span>
                    <span class="badge bg-info text-dark fw-bold">100% Total</span>
                </div>
                <div class="progress mb-2" style="height: 10px; background-color: rgba(255, 255, 255, 0.2);">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $persenKas ?>%" title="Kas Tunai: <?= $persenKas ?>%"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $persenBank ?>%" title="Rekening Bank: <?= $persenBank ?>%"></div>
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $persenStok ?>%" title="Stok Barang: <?= $persenStok ?>%"></div>
                </div>
                <div class="d-flex justify-content-between text-white-50" style="font-size: 0.75rem;">
                    <span><i class="bi bi-circle-fill text-success me-1"></i>Tunai: <?= $persenKas ?>%</span>
                    <span><i class="bi bi-circle-fill text-warning me-1"></i>Bank: <?= $persenBank ?>%</span>
                    <span><i class="bi bi-circle-fill text-info me-1"></i>Stok: <?= $persenStok ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Tengah: 3 PILAR UTAMA KEKAYAAN TOKO DALAM 1 SCREEN -->
    <div class="row g-3">
        <!-- Pilar 1: Uang Cash / Kas Tunai -->
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 rounded-3 p-3 shadow-sm" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(52, 211, 153, 0.4) !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge px-2 py-1 rounded text-white fw-bold mb-1" style="background-color: #059669;">
                            <i class="bi bi-cash-stack me-1"></i> PILAR 1: KAS FISIK
                        </span>
                        <div class="text-white-50 small" style="color: rgba(255, 255, 255, 0.8) !important;">Uang Cash / Tunai di Toko</div>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(16, 185, 129, 0.25);">
                        <i class="bi bi-cash-coin fs-3 text-success" style="color: #6ee7b7 !important;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-white mb-1" style="color: #ffffff !important; letter-spacing: -0.5px;">
                    Rp <?= number_format($saldo_kas_tunai, 0, ',', '.') ?>
                </h3>
                <div class="small" style="color: rgba(255, 255, 255, 0.75) !important; font-size: 0.8rem;">
                    <i class="bi bi-check-circle text-success me-1"></i> Ada di laci kasir &amp; brankas toko (likuid riil setelah operasional tunai).
                </div>
            </div>
        </div>

        <!-- Pilar 2: Uang di Rekening Bank & QRIS -->
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 rounded-3 p-3 shadow-sm" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(251, 191, 36, 0.4) !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge px-2 py-1 rounded text-dark fw-bold mb-1" style="background-color: #f59e0b;">
                            <i class="bi bi-bank me-1"></i> PILAR 2: BANK &amp; QRIS
                        </span>
                        <div class="text-white-50 small" style="color: rgba(255, 255, 255, 0.8) !important;">Uang di Rekening Bank</div>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(245, 158, 11, 0.25);">
                        <i class="bi bi-credit-card-2-front fs-3 text-warning" style="color: #fde047 !important;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-white mb-1" style="color: #ffffff !important; letter-spacing: -0.5px;">
                    Rp <?= number_format($saldo_kas_bank, 0, ',', '.') ?>
                </h3>
                <div class="small" style="color: rgba(255, 255, 255, 0.75) !important; font-size: 0.8rem;">
                    <i class="bi bi-check-circle text-warning me-1"></i> Saldo dari Transfer Bank, QRIS, &amp; Debit yang tersimpan di rekening toko.
                </div>
            </div>
        </div>

        <!-- Pilar 3: Nilai Modal Persediaan Stok Barang (Stok x HPP) -->
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 rounded-3 p-3 shadow-sm" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(56, 189, 248, 0.4) !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge px-2 py-1 rounded text-dark fw-bold mb-1" style="background-color: #38bdf8;">
                            <i class="bi bi-boxes me-1"></i> PILAR 3: ASET STOK BARANG
                        </span>
                        <div class="text-white-50 small" style="color: rgba(255, 255, 255, 0.8) !important;">Diconvert dari Harga Modal (HPP)</div>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(14, 165, 233, 0.25);">
                        <i class="bi bi-box-seam fs-3 text-info" style="color: #7dd3fc !important;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-white mb-1" style="color: #ffffff !important; letter-spacing: -0.5px;">
                    Rp <?= number_format($nilai_aset_hpp, 0, ',', '.') ?>
                </h3>
                <div class="small" style="color: rgba(255, 255, 255, 0.75) !important; font-size: 0.8rem;">
                    <i class="bi bi-check-circle text-info me-1"></i> Rumus: <strong>Stok Fisik &times; Harga Beli (HPP)</strong> &bull; <?= number_format($total_sku, 0, ',', '.') ?> SKU (<?= number_format($total_unit, 0, ',', '.') ?> Unit)
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Bawah: Insight Nilai Jual & Potensi Profit Margin Barang Dagang -->
    <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-color: rgba(255, 255, 255, 0.15) !important;">
        <div class="d-flex flex-wrap align-items-center gap-3 text-white small" style="font-size: 0.85rem;">
            <div>
                <span class="text-white-50"><i class="bi bi-tag-fill text-warning me-1"></i>Potensi Nilai Jual Seluruh Stok:</span>
                <strong class="text-white ms-1">Rp <?= number_format($nilai_aset_jual, 0, ',', '.') ?></strong>
            </div>
            <div>
                <span class="text-white-50"><i class="bi bi-graph-up-arrow text-success me-1"></i>Potensi Keuntungan Modal Stok:</span>
                <strong class="text-success ms-1" style="color: #86efac !important;">
                    +Rp <?= number_format($potensi_keuntungan_stok, 0, ',', '.') ?> (+<?= $persen_margin_stok ?>%)
                </strong>
            </div>
        </div>
        <div class="small text-white-50" style="font-size: 0.75rem;">
            <i class="bi bi-info-circle me-1"></i> Seluruh angka dihitung otomatis dan sinkron secara real-time dari data penjualan, kas, dan stok toko.
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- METRICS ARUS KAS (CASH FLOW BREAKDOWN)         -->
<!-- ============================================== -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-secondary">
            <div class="text-muted small fw-semibold text-uppercase">Saldo Kas Awal Periode</div>
            <div class="h4 fw-bold text-dark mb-0 mt-1">
                Rp <?= number_format($saldo_awal, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">Modal Kas Sebelum Periode Ini</div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="text-muted small fw-semibold text-uppercase">Total Kas Masuk (Cash In)</div>
            <div class="h4 fw-bold text-success mb-0 mt-1">
                Rp <?= number_format($total_pemasukan, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                POS: <strong>Rp <?= number_format($total_penjualan_pos, 0, ',', '.') ?></strong> (Tunai: Rp <?= number_format($periode_penjualan_cash, 0, ',', '.') ?> &bull; Bank: Rp <?= number_format($periode_penjualan_bank, 0, ',', '.') ?>)
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-danger">
            <div class="text-muted small fw-semibold text-uppercase">Total Kas Keluar (Cash Out)</div>
            <div class="h4 fw-bold text-danger mb-0 mt-1">
                Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                Restock: <strong>Rp <?= number_format($total_belanja_restock, 0, ',', '.') ?></strong> &bull; Ops: <strong>Rp <?= number_format($total_biaya_operasional, 0, ',', '.') ?></strong>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 <?= $net_cash_flow >= 0 ? 'border-primary' : 'border-danger' ?>">
            <div class="text-muted small fw-semibold text-uppercase">Arus Kas Bersih (Net Flow)</div>
            <div class="h4 fw-bold <?= $net_cash_flow >= 0 ? 'text-primary' : 'text-danger' ?> mb-0 mt-1">
                <?= $net_cash_flow >= 0 ? '+' : '' ?>Rp <?= number_format($net_cash_flow, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                <?= $net_cash_flow >= 0 ? '<span class="text-success fw-bold"><i class="bi bi-arrow-up-circle"></i> Kas Bertambah</span>' : '<span class="text-danger fw-bold"><i class="bi bi-arrow-down-circle"></i> Kas Berkurang</span>' ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- TABEL MUTASI ARUS KAS LENGKAP                  -->
<!-- ============================================== -->
<div class="card-modern p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-journal-text me-1 text-primary"></i> Buku Kas Mutasi Arus Kas (Running Balance Ledger)
            </h6>
            <small class="text-muted">Riwayat pemasukan, pengeluaran, dan saldo kas berjalan per transaksi</small>
        </div>
        <span class="badge bg-light text-dark border">
            Total Mutasi: <?= count($transaksi_list) ?> Baris
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 45px;" class="text-center">No.</th>
                    <th style="width: 140px;">Waktu Mutasi</th>
                    <th style="width: 110px;" class="text-center">Kategori</th>
                    <th>Uraian / Keterangan Mutasi</th>
                    <th style="width: 100px;">Petugas</th>
                    <th class="text-end" style="width: 130px;">Kas Masuk (+)</th>
                    <th class="text-end" style="width: 130px;">Kas Keluar (-)</th>
                    <th class="text-end" style="width: 140px;">Saldo Kas</th>
                </tr>
            </thead>
            <tbody>
                <!-- Baris Saldo Awal -->
                <tr class="table-light">
                    <td class="text-center text-muted fw-bold">&bull;</td>
                    <td class="small fw-semibold text-muted">-</td>
                    <td class="text-center">
                        <span class="badge bg-secondary text-white">Saldo Awal</span>
                    </td>
                    <td class="fw-semibold text-dark">
                        <em>Saldo Kas Awal Sebelum Periode <?= esc($periode_label) ?></em>
                    </td>
                    <td class="text-muted small">-</td>
                    <td class="text-end text-muted">-</td>
                    <td class="text-end text-muted">-</td>
                    <td class="text-end fw-bold text-dark">
                        Rp <?= number_format($saldo_awal, 0, ',', '.') ?>
                    </td>
                </tr>

                <?php $no = 1; foreach ($transaksi_list as $t): ?>
                    <tr>
                        <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                        <td class="small">
                            <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($t['tanggal'])) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($t['tanggal'])) ?> WIB</div>
                        </td>
                        <td class="text-center">
                            <?php if ($t['jenis_keuangan'] === 'Pemasukan'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">Pemasukan</span>
                            <?php elseif ($t['jenis_keuangan'] === 'Pengeluaran'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">Pengeluaran</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">Kas Awal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fw-bold text-dark"><?= esc($t['nama_keuangan']) ?></span>
                            <?php if (!empty($t['keterangan'])): ?>
                                <div class="text-muted small" style="font-size: 0.78rem;"><?= esc($t['keterangan']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-secondary fw-medium">
                            <?= esc($t['kode_user'] ?: '-') ?>
                        </td>
                        <td class="text-end fw-bold text-success">
                            <?= $t['kas_masuk'] > 0 ? '+ Rp ' . number_format($t['kas_masuk'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end fw-bold text-danger">
                            <?= $t['kas_keluar'] > 0 ? '- Rp ' . number_format($t['kas_keluar'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end fw-bold text-primary">
                            Rp <?= number_format($t['saldo_berjalan'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL MUTASI PERIODE INI:</td>
                    <td class="text-end text-success">+ Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
                    <td class="text-end text-danger">- Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
                    <td class="text-end text-primary h6 mb-0">Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeSelect = document.getElementById('mode_periode');
    const wrapperBulan = document.getElementById('wrapper-bulan');
    const wrapperTglMulai = document.getElementById('wrapper-tgl-mulai');
    const wrapperTglSelesai = document.getElementById('wrapper-tgl-selesai');
    const inputBulan = document.getElementById('input_bulan');

    modeSelect.addEventListener('change', function() {
        if (this.value === 'bulan') {
            wrapperBulan.style.display = '';
            wrapperTglMulai.style.display = 'none';
            wrapperTglSelesai.style.display = 'none';
        } else {
            wrapperBulan.style.display = 'none';
            wrapperTglMulai.style.display = '';
            wrapperTglSelesai.style.display = '';
        }
    });

    const quickMonthBtns = document.querySelectorAll('.btn-quick-month');
    quickMonthBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modeSelect.value = 'bulan';
            inputBulan.value = this.getAttribute('data-month');
            document.getElementById('form-filter-aruskas').submit();
        });
    });
});
</script>

<?= $this->endSection() ?>

