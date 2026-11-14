<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <!-- Header Page with Live Pulse Badge -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-broadcast text-danger me-2"></i>Pantau Kinerja Kasir Real-Time
                </h4>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                    <span class="spinner-grow spinner-grow-sm me-1 text-danger" style="width: 8px; height: 8px;" role="status"></span> LIVE MONITOR
                </span>
            </div>
            <p class="text-muted small mb-0">Pantau aktivitas, status kerja, omset penjualan hari ini, dan saldo fisik kas di laci kasir setiap cabang secara langsung.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted d-none d-sm-inline" id="last-refresh-time">Diperbarui: <?= date('H:i:s') ?> WIB</small>
            <button type="button" class="btn btn-outline-primary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh Data</span>
            </button>
        </div>
    </div>

    <!-- Top Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-3">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Kasir Aktif Melayani</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h4 class="fw-bold mb-0 text-success"><?= $totalKasirAktif ?></h4>
                        <small class="text-muted">dari <?= $totalKasir ?> staf</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Transaksi Hari Ini</div>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format($totalTransaksiHariIni) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Omset Kasir Hari Ini</div>
                    <h4 class="fw-bold mb-0 text-dark">Rp <?= number_format($totalOmsetHariIni, 0, ',', '.') ?></h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-3">
                    <i class="bi bi-safe-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Estimasi Kas di Laci (Semua)</div>
                    <h4 class="fw-bold mb-0 text-warning">Rp <?= number_format($totalKasLaciSemua, 0, ',', '.') ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Cashier Cards -->
    <div class="row g-4">
        <?php foreach ($kasirPerformance as $kp): ?>
            <?php 
                $u = $kp['user'];
                $isAktif = ($kp['status'] === 'aktif');
                $isStandby = ($kp['status'] === 'standby');
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card-modern h-100 border <?= $isAktif ? 'border-success shadow' : ($isStandby ? 'border-warning' : '') ?>">
                    <!-- Card Header -->
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center <?= $isAktif ? 'bg-success bg-opacity-10' : ($isStandby ? 'bg-warning bg-opacity-10' : 'bg-light') ?>">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr($u['nama_user'], 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= esc($u['nama_user']) ?></h6>
                                <span class="badge bg-light text-muted border px-2 py-0" style="font-size: 0.68rem;">
                                    <?= esc($u['kode_user']) ?> &bull; <?= strtoupper($u['role']) ?>
                                </span>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="bi bi-shop-window text-primary me-1"></i><?= esc($u['nama_cabang'] ?? 'Pusat') ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3 p-md-4">
                        <!-- Live Status Badge -->
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Status Kerja:</span>
                            <?php if ($isAktif): ?>
                                <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-semibold d-flex align-items-center gap-1 shadow-sm">
                                    <span class="spinner-grow spinner-grow-sm text-white" style="width: 7px; height: 7px;"></span>
                                    <span><?= esc($kp['status_label']) ?></span>
                                </span>
                            <?php elseif ($isStandby): ?>
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold d-flex align-items-center gap-1">
                                    <i class="bi bi-pause-circle-fill"></i>
                                    <span><?= esc($kp['status_label']) ?></span>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-moon-stars me-1"></i><?= esc($kp['status_label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Cashier Financial Box -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">MODAL KAS AWAL</small>
                                    <div class="fw-bold text-dark font-monospace small">Rp <?= number_format($kp['kas_awal'], 0, ',', '.') ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">OMSET HARI INI</small>
                                    <div class="fw-bold text-primary font-monospace small">
                                        Rp <?= number_format($kp['total_omset'], 0, ',', '.') ?>
                                        <small class="text-muted fw-normal">(<?= $kp['total_trx'] ?> trx)</small>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-dark fw-bold">Uang Fisik di Laci Kasir:</small>
                                        <span class="badge bg-success-subtle text-success fs-6 fw-bold font-monospace px-2 py-1">
                                            Rp <?= number_format($kp['kas_di_laci'], 0, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Last Activity Log -->
                        <div class="border-top pt-2">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.72rem;">AKTIVITAS TERAKHIR:</small>
                            <?php if ($kp['last_activity']): ?>
                                <div class="small text-dark text-truncate" title="<?= esc($kp['last_activity']['deskripsi']) ?>">
                                    <i class="bi bi-arrow-return-right text-primary me-1"></i><?= esc($kp['last_activity']['deskripsi']) ?>
                                </div>
                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                    <?= date('H:i:s', strtotime($kp['last_activity']['created_at'])) ?> WIB &bull; <?= esc($kp['last_activity']['modul']) ?>
                                </small>
                            <?php else: ?>
                                <small class="text-muted fst-italic">Belum ada aktivitas tercatat hari ini</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-white border-top p-2 text-center">
                        <a href="<?= base_url('audit-trail?keyword=' . urlencode($u['kode_user'])) ?>" class="small text-decoration-none fw-semibold text-primary">
                            <i class="bi bi-shield-check me-1"></i>Lihat Jejak Audit Kasir Ini &rarr;
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-refresh monitoring page every 30 seconds
    setInterval(function() {
        console.log('Auto-refreshing cashier live monitor...');
        window.location.reload();
    }, 30000);
</script>
<?= $this->endSection() ?>

