<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clipboard2-check text-success me-2"></i>Stok Opname (STO) Fisik Toko</h4>
        <p class="text-muted small mb-0">Audit penghitungan fisik stok sabun &amp; parfum di rak toko secara berkala menggunakan metode Blind Count untuk mencegah kecurangan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('laporan/stok-opname') ?>" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-box-seam-fill me-1"></i> Laporan Aset &amp; STO
        </a>
        <a href="<?= base_url('stok-opname/tambah') ?>" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
            <i class="bi bi-plus-circle-fill me-1"></i> Mulai Sesi STO Baru (Blind Count)
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Metrik Riwayat Sesi STO -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Total Sesi Audit STO</span>
            <h3 class="fw-bold mt-1 mb-0 text-success"><?= count($sto_list) ?> Sesi</h3>
            <small class="text-muted">Riwayat audit fisik rak toko</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Sesi Aktif &amp; Selesai</span>
            <h3 class="fw-bold mt-1 mb-0 text-primary"><?= count(array_filter($sto_list, fn($s) => $s['status'] === 'Selesai')) ?> Sesi</h3>
            <small class="text-muted">Stok sistem tersinkronkan</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-danger">
            <span class="text-muted small text-uppercase fw-semibold">Sesi Dibatalkan (Revert)</span>
            <h3 class="fw-bold mt-1 mb-0 text-danger"><?= count(array_filter($sto_list, fn($s) => $s['status'] === 'Dibatalkan')) ?> Sesi</h3>
            <small class="text-muted">Stok telah dikembalikan ke asal</small>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Sesi STO -->
<div class="card-modern p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Daftar Berkas Stok Opname</h6>
        
        <!-- Filter Periode Bulan -->
        <div class="btn-group btn-group-sm bg-light p-1 rounded-pill border" role="group">
            <a href="<?= base_url('stok-opname?bulan=semua') ?>" class="btn btn-sm <?= ($filter_bulan ?? 'semua') === 'semua' ? 'btn-primary' : 'btn-light' ?> rounded-pill px-3 fw-semibold">Semua</a>
            <a href="<?= base_url('stok-opname?bulan=bulan_ini') ?>" class="btn btn-sm <?= ($filter_bulan ?? '') === 'bulan_ini' ? 'btn-primary' : 'btn-light' ?> rounded-pill px-3 fw-semibold">Bulan Ini</a>
            <a href="<?= base_url('stok-opname?bulan=1_bulan_lalu') ?>" class="btn btn-sm <?= ($filter_bulan ?? '') === '1_bulan_lalu' ? 'btn-primary' : 'btn-light' ?> rounded-pill px-3 fw-semibold">Bulan Lalu</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-sto" style="width: 100%;">
            <thead class="table-dark small text-uppercase">
                <tr>
                    <th style="width: 130px;">No STO</th>
                    <th style="width: 150px;">Waktu Audit</th>
                    <th>Petugas Audit</th>
                    <th class="text-center" style="width: 110px;">Item Dihitung</th>
                    <th class="text-center" style="width: 110px;">Selisih (+)</th>
                    <th class="text-center" style="width: 110px;">Selisih (-)</th>
                    <th class="text-end" style="width: 160px;">Total Nilai Selisih</th>
                    <th class="text-center" style="width: 130px;">Status</th>
                    <th class="text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sto_list)): ?>
                    <?php foreach ($sto_list as $s): ?>
                        <tr class="<?= $s['status'] === 'Dibatalkan' ? 'opacity-75 text-muted' : '' ?>">
                            <td>
                                <strong class="font-monospace text-primary"><?= esc($s['no_sto']) ?></strong>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($s['tanggal'])) ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= esc($s['nama_petugas']) ?></strong>
                                <small class="text-muted font-monospace"><?= esc($s['kode_user'] ?? 'KASIR') ?></small>
                            </td>
                            <td class="text-center fw-bold"><?= $s['total_item_dihitung'] ?> Item</td>
                            <td class="text-center fw-bold text-success">+<?= number_format($s['total_selisih_plus'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold text-danger">-<?= number_format($s['total_selisih_minus'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold font-monospace <?= (float)$s['total_nilai_selisih'] < 0 ? 'text-danger' : 'text-success' ?>">
                                Rp <?= number_format($s['total_nilai_selisih'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['status'] === 'Dibatalkan'): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-x-circle me-1"></i> Dibatalkan
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-3 py-1">
                                        <i class="bi bi-check2-all me-1"></i> <?= esc($s['status']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('stok-opname/detail/' . $s['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#table-sto').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                search: "_INPUT_",
                searchPlaceholder: "Cari nomor STO / auditor..."
            },
            pageLength: 15,
            order: [[1, 'desc']]
        });
    }
});
</script>
<?= $this->endSection() ?>
