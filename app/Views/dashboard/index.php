<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Banner Status Cabang Aktif -->
<div class="card-modern p-3 mb-4 bg-white border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
            <i class="bi bi-shop-window fs-4"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark"><?= esc(session()->get('nama_cabang') ?? 'KSP Metland Cileungsi (Pusat)') ?></h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size: 0.7rem;">
                    <?= esc(session()->get('kode_cabang') ?? 'CBG-01') ?>
                </span>
            </div>
            <small class="text-muted">
                <?= esc(session()->get('role') === 'admin' ? 'Administrator Jaringan Toko' : 'Kasir Bertugas: ' . (session()->get('nama_user') ?? 'Petugas')) ?> &bull; Data transaksi, stok barang cabang, dan buku kas aktif terisolasi.
            </small>
        </div>
    </div>
    <?php if (session()->get('role') === 'admin' || session()->get('can_switch_cabang') == 1): ?>
        <a href="<?= base_url('cabang') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold text-nowrap">
            <i class="bi bi-arrow-repeat me-1"></i> Beralih Cabang Toko
        </a>
    <?php endif; ?>
</div>

<!-- Baris Metrik Operasional Hari Ini -->
<div class="row g-3 mb-4">
    <!-- Omzet Hari Ini -->
    <div class="<?= session()->get('role') === 'admin' ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-sm-6 col-xl-4' ?>">
        <div class="card-modern metric-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Omzet Hari Ini</span>
                    <h3 class="fw-bold mt-2 mb-1 text-primary">Rp <?= number_format($summary['total_omzet'] ?? 0, 0, ',', '.') ?></h3>
                    <div class="d-flex flex-wrap gap-1 align-items-center mb-2">
                        <span class="badge badge-subtle-primary"><i class="bi bi-cart-check"></i> <?= $summary['total_transaksi'] ?? 0 ?> Transaksi</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center pt-2 border-top">
                        <span class="badge bg-light text-dark border fw-normal" title="Pembayaran Tunai (Cash)"><i class="bi bi-cash text-success"></i> Cash: <strong>Rp <?= number_format($summary['omzet_cash'] ?? 0, 0, ',', '.') ?></strong></span>
                        <span class="badge bg-light text-dark border fw-normal" title="Pembayaran QRIS"><i class="bi bi-qr-code text-primary"></i> QRIS: <strong>Rp <?= number_format($summary['omzet_qris'] ?? 0, 0, ',', '.') ?></strong></span>
                        <span class="badge bg-light text-dark border fw-normal" title="Pembayaran Transfer Bank"><i class="bi bi-arrow-left-right text-info"></i> TF: <strong>Rp <?= number_format($summary['omzet_transfer'] ?? 0, 0, ',', '.') ?></strong></span>
                    </div>
                </div>
                <div class="metric-icon badge-subtle-primary align-self-start mt-1">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Estimasi Profit Bersih (HANYA MUNCUL JIKA ROLE ADMIN) -->
    <?php if (session()->get('role') === 'admin'): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern metric-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Profit Hari Ini</span>
                    <h3 class="fw-bold mt-2 mb-1 text-success">Rp <?= number_format($summary['total_profit'] ?? 0, 0, ',', '.') ?></h3>
                    <span class="badge badge-subtle-success"><i class="bi bi-graph-up-arrow"></i> Khusus Admin</span>
                </div>
                <div class="metric-icon badge-subtle-success">
                    <i class="bi bi-piggy-bank-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Posisi Kas Tunai di Laci -->
    <div class="<?= session()->get('role') === 'admin' ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-sm-6 col-xl-4' ?>">
        <div class="card-modern metric-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Saldo Kas di Toko</span>
                    <h3 class="fw-bold mt-2 mb-1 text-dark">Rp <?= number_format($kas['saldo_akhir'] ?? 0, 0, ',', '.') ?></h3>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        <span class="badge badge-subtle-warning"><i class="bi bi-safe"></i> Kas Awal: Rp <?= number_format($kas['kas_awal'] ?? 0, 0, ',', '.') ?></span>
                        <?php if (!empty($kas['penjualan_tunai'])): ?>
                            <span class="badge badge-subtle-success"><i class="bi bi-cash"></i> Cash: Rp <?= number_format($kas['penjualan_tunai'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="metric-icon badge-subtle-warning">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Volume Refill / Tukar Jerigen 5L -->
    <div class="<?= session()->get('role') === 'admin' ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-sm-6 col-xl-4' ?>">
        <div class="card-modern metric-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Tukar Jerigen 5L</span>
                    <h3 class="fw-bold mt-2 mb-1 text-info">Rp <?= number_format($summary['total_tukar_jerigen'] ?? 0, 0, ',', '.') ?></h3>
                    <span class="badge badge-subtle-primary"><i class="bi bi-recycle"></i> <?= number_format(($summary['total_tukar_jerigen'] ?? 0) / 1000, 0) ?> Jerigen ditukar</span>
                </div>
                <div class="metric-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris Grafik & Alert Stok Menipis -->
<?php $isAdmin = (session()->get('role') === 'admin'); ?>
<div class="row g-4 mb-4">
    <!-- Grafik Penjualan & Profit 7 Hari Terakhir -->
    <div class="col-12 col-lg-8">
        <div class="card-modern p-4 h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <?php if ($isAdmin): ?>
                        <h5 class="fw-bold mb-1">Tren Penjualan &amp; Margin Profit (<span id="grafik-periode-label">7 Hari</span>)</h5>
                        <small class="text-muted" id="grafik-subtitle">Pantau pertumbuhan omzet dan laba kotor toko</small>
                    <?php else: ?>
                        <h5 class="fw-bold mb-1">Tren Penjualan (<span id="grafik-periode-label">7 Hari</span>)</h5>
                        <small class="text-muted" id="grafik-subtitle">Pantau pertumbuhan omzet harian toko</small>
                    <?php endif; ?>
                </div>

                <!-- Filter Periode Tombol Pills: 7 Hari, 1 Minggu, 1 Bulan, 1 Tahun -->
                <div class="btn-group btn-group-sm bg-light p-1 rounded-pill border shadow-sm" role="group" id="filter-grafik-group">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold btn-filter-grafik active" data-periode="7_hari" data-label="7 Hari">7 Hari</button>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-secondary btn-filter-grafik" data-periode="1_minggu" data-label="1 Minggu">1 Minggu</button>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-secondary btn-filter-grafik" data-periode="1_bulan" data-label="1 Bulan">1 Bulan</button>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-secondary btn-filter-grafik" data-periode="1_tahun" data-label="1 Tahun">1 Tahun</button>
                </div>
            </div>
            <div style="height: 320px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Alert Stok Kritis / Menipis (Card Ringkasan Cepat) -->
    <div class="col-12 col-lg-4">
        <div class="card-modern p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-danger"><i class="bi bi-exclamation-octagon-fill"></i> Perlu Restock</h5>
                        <small class="text-muted">Barang &le; batas minimum stok</small>
                    </div>
                    <span class="badge bg-danger text-white rounded-pill px-3 py-1 fs-6"><?= count($stokMenipis) ?> Item</span>
                </div>

                <?php if (empty($stokMenipis)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        <p class="mt-2 mb-0">Semua stok barang dalam kondisi aman!</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush overflow-auto" style="max-height: 250px;">
                        <?php foreach (array_slice($stokMenipis, 0, 8) as $item): ?>
                            <div class="list-group-item px-0 py-2 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-dark small text-truncate" style="max-width: 175px;"><?= esc($item['nama_barang']) ?></div>
                                    <small class="text-muted"><?= esc($item['id_barang']) ?> &bull; Min: <?= $item['min_stok'] ?> <?= esc($item['satuan']) ?></small>
                                </div>
                                <div class="text-end">
                                    <?php if ((float)$item['stok'] <= 0): ?>
                                        <span class="badge bg-danger fw-bold fs-6">0</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark fw-bold fs-6"><?= $item['stok'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-3 border-top mt-2">
                <a href="#section-tabel-stok-menipis" class="btn btn-outline-danger btn-sm w-100 fw-bold">
                    <i class="bi bi-table me-1"></i> Lihat Tabel Lengkap Stok Menipis (<?= count($stokMenipis) ?>)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TABEL DATA BARANG AKAN & SUDAH HABIS (PERLU RESTOCK SEGERA)               -->
<!-- ========================================================================= -->
<div class="row g-4 mb-4" id="section-tabel-stok-menipis">
    <div class="col-12">
        <div class="card-modern p-4 border shadow-sm rounded-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger p-2 rounded-3 text-white shadow-xs">
                            <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Data Barang Akan &amp; Sudah Habis (Perlu Restock)</h5>
                            <small class="text-muted">
                                Monitoring barang dengan stok saat ini &le; batas minimum pada cabang 
                                <strong class="text-primary font-monospace"><?= esc(session()->get('nama_cabang') ?? 'KSP Metland Cileungsi (Pusat)') ?></strong>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Kontrol Filter & Pencarian Cepat -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group" id="filter-stok-kritis-pills">
                        <button type="button" class="btn btn-outline-secondary active btn-filter-stok" data-type="all">
                            Semua <span class="badge bg-secondary ms-1"><?= count($stokMenipis) ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-filter-stok" data-type="habis">
                            <i class="bi bi-x-circle-fill me-1"></i> Stok 0 (Kosong)
                            <span class="badge bg-danger ms-1"><?= count(array_filter($stokMenipis, fn($i) => (float)$i['stok'] <= 0)) ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-warning text-dark btn-filter-stok" data-type="menipis">
                            <i class="bi bi-hourglass-bottom me-1"></i> Menipis (&le; Min)
                            <span class="badge bg-warning text-dark ms-1"><?= count(array_filter($stokMenipis, fn($i) => (float)$i['stok'] > 0 && (float)$i['stok'] <= (float)$i['min_stok'])) ?></span>
                        </button>
                    </div>

                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-light text-muted border"><i class="bi bi-search"></i></span>
                        <input type="text" id="input-cari-stok-kritis" class="form-control" placeholder="Cari barang / kode..." autocomplete="off">
                    </div>

                    <?php if (session()->get('role') === 'admin'): ?>
                        <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-sm btn-primary fw-bold text-nowrap shadow-xs">
                            <i class="bi bi-cart-plus me-1"></i> Buat Restock
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="table-stok-menipis" style="font-size: 0.85rem;">
                    <thead class="table-light sticky-top small text-uppercase fw-bold" style="z-index: 2;">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th style="width: 55px;" class="text-center">Foto</th>
                            <th style="width: 110px;">ID Barang</th>
                            <th>Nama Barang</th>
                            <th style="width: 130px;">Kategori</th>
                            <th style="width: 85px;">Satuan</th>
                            <th class="text-center" style="width: 100px;">Stok Saat Ini</th>
                            <th class="text-center" style="width: 90px;">Min. Stok</th>
                            <th style="width: 160px;">Supplier Utama</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <?php if (session()->get('role') === 'admin'): ?>
                                <th class="text-center" style="width: 90px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tbody-stok-menipis">
                        <?php if (empty($stokMenipis)): ?>
                            <tr>
                                <td colspan="<?= session()->get('role') === 'admin' ? '11' : '10' ?>" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                                    <strong>Semua Stok Barang Aman!</strong>
                                    <div class="small">Tidak ada produk yang habis atau di bawah batas minimum stok saat ini.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1;
                            foreach ($stokMenipis as $item): 
                                $stok = (float)($item['stok'] ?? 0);
                                $minStok = (float)($item['min_stok'] ?? 5);
                                $isHabis = ($stok <= 0);
                                $rowType = $isHabis ? 'habis' : 'menipis';
                                $imgSrc = !empty($item['gambar']) ? base_url('uploads/barang/' . $item['gambar']) : null;
                            ?>
                                <tr class="row-stok-kritis" data-type="<?= $rowType ?>" data-search="<?= strtolower(esc($item['id_barang'] . ' ' . $item['nama_barang'] . ' ' . ($item['nama_kategori'] ?? '') . ' ' . ($item['nama_supplier'] ?? ''))) ?>">
                                    <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                                    <td class="text-center">
                                        <?php if ($imgSrc): ?>
                                            <img src="<?= $imgSrc ?>" alt="<?= esc($item['nama_barang']) ?>" class="rounded border shadow-xs" style="width: 32px; height: 32px; object-fit: cover; cursor: zoom-in;" onclick="window.open('<?= $imgSrc ?>', '_blank')" title="Klik perbesar foto" onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted mx-auto" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-monospace fw-bold text-dark"><?= esc($item['id_barang']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 260px;" title="<?= esc($item['nama_barang']) ?>">
                                            <?= esc($item['nama_barang']) ?>
                                        </div>
                                        <?php if (!empty($item['barcode'])): ?>
                                            <small class="text-muted font-monospace"><i class="bi bi-upc-scan"></i> <?= esc($item['barcode']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= esc($item['nama_kategori'] ?? 'Umum') ?></span></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($item['satuan'] ?? 'PCS') ?></span></td>
                                    <td class="text-center">
                                        <?php if ($isHabis): ?>
                                            <span class="badge bg-danger fs-6 px-2 py-1 shadow-xs">0</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark fs-6 px-2 py-1 shadow-xs"><?= $stok ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center font-monospace text-muted fw-semibold"><?= $minStok ?></td>
                                    <td>
                                        <div class="small text-truncate" style="max-width: 150px;" title="<?= esc($item['nama_supplier'] ?? '-') ?>">
                                            <i class="bi bi-truck text-muted me-1"></i><?= esc($item['nama_supplier'] ?? 'Supplier Umum') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isHabis): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle small fw-bold">
                                                <i class="bi bi-x-circle me-1"></i> Habis
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small fw-bold">
                                                <i class="bi bi-exclamation-triangle me-1"></i> Menipis
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <td class="text-center">
                                            <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-xs btn-outline-primary py-1 px-2 rounded" title="Buat Pesanan Restock">
                                                <i class="bi bi-cart-plus me-1"></i> Restock
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small">
                <div>
                    Menampilkan <span id="count-visible-kritis" class="fw-bold text-dark"><?= count($stokMenipis) ?></span> dari total <span class="fw-bold text-dark"><?= count($stokMenipis) ?></span> item kritis/menipis.
                </div>
                <div class="d-flex gap-3">
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-danger p-1 rounded-circle"> </span> Stok Habis (0)</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-warning p-1 rounded-circle"> </span> Stok Menipis (&le; Batas Min)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris Tabel Operasional: Top Produk Terlaris & Top Member -->
<div class="row g-4">
    <!-- Top 5 Produk Terlaris -->
    <div class="col-12 col-md-6">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Produk Terlaris</h5>
                <small class="text-muted">Berdasarkan volume penjualan</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Nama Barang</th>
                            <th class="text-center">Qty Terjual</th>
                            <th class="text-end">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProduk)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada transaksi hari ini</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProduk as $tp): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?= esc($tp['nama_barang']) ?></div>
                                        <small class="text-muted"><?= esc($tp['id_barang']) ?></small>
                                    </td>
                                    <td class="text-center fw-bold text-primary"><?= (int) $tp['total_qty'] ?></td>
                                    <td class="text-end fw-semibold text-dark">Rp <?= number_format($tp['total_nilai'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Member / Pelanggan Terloyal -->
    <div class="col-12 col-md-6">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Member / Pelanggan Terbaik</h5>
                <a href="<?= base_url('member') ?>" class="small text-primary text-decoration-none">Lihat Semua <i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Nama Member</th>
                            <th class="text-center">Poin</th>
                            <th class="text-end">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topMember)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data member</td></tr>
                        <?php else: ?>
                            <?php foreach ($topMember as $tm): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?= esc($tm['nama']) ?></div>
                                        <small class="text-muted"><i class="bi bi-telephone"></i> <?= esc($tm['telepon'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-gem"></i> <?= (int) $tm['point'] ?> Pts</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">Rp <?= number_format($tm['total_belanja'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const initialLabels = <?= json_encode(array_column($grafik, 'label')) ?>;
    const initialOmzet = <?= json_encode(array_column($grafik, 'omzet')) ?>;
    const initialProfit = <?= json_encode(array_column($grafik, 'profit')) ?>;
    const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;

    const ctx = document.getElementById('salesChart').getContext('2d');
    const datasets = [
        {
            label: 'Omzet Penjualan (Rp)',
            data: initialOmzet,
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
        }
    ];

    if (isAdmin) {
        datasets.push({
            label: 'Margin Profit (Rp)',
            data: initialProfit,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
        });
    }

    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initialLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.y || 0;
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return 'Rp ' + (val / 1000) + 'k';
                        }
                    }
                }
            }
        }
    });

    // Handle Perubahan Filter Periode: 7 Hari, 1 Minggu, 1 Bulan, 1 Tahun
    document.querySelectorAll('.btn-filter-grafik').forEach(btn => {
        btn.addEventListener('click', function() {
            const periode = this.getAttribute('data-periode');
            const labelText = this.getAttribute('data-label');

            // Update status visual tombol aktif
            document.querySelectorAll('.btn-filter-grafik').forEach(b => {
                b.className = 'btn btn-sm btn-light rounded-pill px-3 fw-semibold text-secondary btn-filter-grafik';
            });
            this.className = 'btn btn-sm btn-primary rounded-pill px-3 fw-semibold btn-filter-grafik active';

            // Update teks label periode pada judul
            const labelSpan = document.getElementById('grafik-periode-label');
            if (labelSpan) {
                labelSpan.innerText = labelText;
            }

            // Animasi transparan saat memuat data baru
            const chartCanvas = document.getElementById('salesChart');
            chartCanvas.style.opacity = '0.4';

            fetch('<?= base_url('dashboard/grafik') ?>?periode=' + periode)
                .then(res => res.json())
                .then(data => {
                    chartCanvas.style.opacity = '1';
                    if (data && data.status === 'success') {
                        salesChart.data.labels = data.labels;
                        salesChart.data.datasets[0].data = data.omzet;
                        if (isAdmin && salesChart.data.datasets[1]) {
                            salesChart.data.datasets[1].data = data.profit;
                        }
                        salesChart.update();
                    }
                })
                .catch(err => {
                    chartCanvas.style.opacity = '1';
                    console.error('Gagal memuat data grafik periode:', err);
                });
        });
    });

    // =========================================================================
    // Filter & Pencarian Cepat Tabel Stok Menipis & Habis
    // =========================================================================
    let activeStokType = 'all';

    $('.btn-filter-stok').on('click', function() {
        $('.btn-filter-stok').removeClass('active');
        $(this).addClass('active');
        activeStokType = $(this).data('type');
        filterTableStokKritis();
    });

    $('#input-cari-stok-kritis').on('keyup input', function() {
        filterTableStokKritis();
    });

    function filterTableStokKritis() {
        const keyword = ($('#input-cari-stok-kritis').val() || '').toLowerCase().trim();
        let visibleCount = 0;

        $('.row-stok-kritis').each(function() {
            const rowType = $(this).data('type');
            const rowSearch = $(this).data('search') || '';

            const matchType = (activeStokType === 'all' || rowType === activeStokType);
            const matchSearch = (!keyword || rowSearch.indexOf(keyword) !== -1);

            if (matchType && matchSearch) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        $('#count-visible-kritis').text(visibleCount);
    }
</script>
<?= $this->endSection() ?>

