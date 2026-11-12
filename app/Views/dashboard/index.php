<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Baris Metrik Operasional Hari Ini -->
<div class="row g-3 mb-4">
    <!-- Omzet Hari Ini -->
    <div class="<?= session()->get('role') === 'admin' ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-sm-6 col-xl-4' ?>">
        <div class="card-modern metric-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Omzet Hari Ini</span>
                    <h3 class="fw-bold mt-2 mb-1 text-primary">Rp <?= number_format($summary['total_omzet'] ?? 0, 0, ',', '.') ?></h3>
                    <span class="badge badge-subtle-primary"><i class="bi bi-cart-check"></i> <?= $summary['total_transaksi'] ?? 0 ?> Transaksi</span>
                </div>
                <div class="metric-icon badge-subtle-primary">
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
                    <span class="badge badge-subtle-warning"><i class="bi bi-safe"></i> Kas Awal: Rp <?= number_format($kas['kas_awal'] ?? 0, 0, ',', '.') ?></span>
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
    <div class="col-12 <?= $isAdmin ? 'col-lg-8' : 'col-lg-12' ?>">
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

    <?php if ($isAdmin): ?>
    <!-- Alert Stok Kritis / Menipis (Khusus Admin / Owner Toko) -->
    <div class="col-12 col-lg-4">
        <div class="card-modern p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-danger"><i class="bi bi-exclamation-octagon-fill"></i> Perlu Restock Segera</h5>
                    <small class="text-muted">Barang di bawah batas minimum stok</small>
                </div>
                <span class="badge bg-danger text-white rounded-pill"><?= count($stokMenipis) ?> Item</span>
            </div>

            <?php if (empty($stokMenipis)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    <p class="mt-2 mb-0">Semua stok barang dalam kondisi aman!</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush overflow-auto" style="max-height: 300px;">
                    <?php foreach ($stokMenipis as $item): ?>
                        <div class="list-group-item px-0 py-2 border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 180px;"><?= esc($item['nama_barang']) ?></div>
                                <small class="text-muted"><?= esc($item['id_barang']) ?> &bull; Min: <?= $item['min_stok'] ?> <?= esc($item['satuan']) ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-subtle text-danger fw-bold fs-6"><?= $item['stok'] ?></span>
                                <div><a href="<?= base_url('pembelian') ?>" class="text-primary small text-decoration-none">Order <i class="bi bi-arrow-right"></i></a></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
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
</script>
<?= $this->endSection() ?>

