<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-arrow-left-right text-primary me-2"></i>Mutasi & Transfer Stok Antar Cabang
            </h4>
            <p class="text-muted small mb-0">Kelola perpindahan dan distribusi stok barang antar gudang atau cabang toko secara terkontrol.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('transfer-stok/tambah') ?>" class="btn btn-primary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-send-plus-fill"></i>
                <span>Kirim Transfer Stok Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <?php
            $totalTransfer = count($transfers);
            $totalDikirim = 0;
            $totalDiterima = 0;
            foreach ($transfers as $t) {
                if ($t['status'] === 'Dikirim') $totalDikirim++;
                if ($t['status'] === 'Diterima') $totalDiterima++;
            }
        ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-3">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Mutasi Tercatat</div>
                    <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalTransfer) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-3">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Dalam Pengiriman (In Transit)</div>
                    <h4 class="fw-bold mb-0 text-warning"><?= number_format($totalDikirim) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-3">
                    <i class="bi bi-check-all"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Selesai Diterima</div>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($totalDiterima) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Data List Table -->
    <div class="card-modern">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-transfer">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>No. Transfer</th>
                            <th>Tanggal</th>
                            <th>Rute Pengiriman</th>
                            <th class="text-center">Total Item / Qty</th>
                            <th class="text-center">Status</th>
                            <th>Petugas (Kirim / Terima)</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transfers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada transaksi mutasi atau transfer stok antar cabang yang tercatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($transfers as $t): ?>
                                <tr>
                                    <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-primary"><?= esc($t['no_transfer']) ?></div>
                                        <?php if (!empty($t['catatan'])): ?>
                                            <small class="text-muted text-truncate d-block" style="max-width: 180px;" title="<?= esc($t['catatan']) ?>">
                                                <i class="bi bi-chat-left-text me-1"></i><?= esc($t['catatan']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($t['tanggal'])) ?> WIB</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= esc($t['nama_cabang_asal'] ?? 'Asal') ?>
                                            </span>
                                            <i class="bi bi-arrow-right text-primary"></i>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="bi bi-geo-alt-fill text-success me-1"></i><?= esc($t['nama_cabang_tujuan'] ?? 'Tujuan') ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold"><?= number_format($t['total_item']) ?> <small class="text-muted">Item</small></div>
                                        <span class="badge bg-secondary-subtle text-secondary"><?= (float)$t['total_qty'] ?> Pcs</span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['status'] === 'Diterima'): ?>
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                                <i class="bi bi-check-circle-fill me-1"></i> Diterima
                                            </span>
                                        <?php elseif ($t['status'] === 'Dikirim'): ?>
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-semibold">
                                                <i class="bi bi-truck me-1"></i> Dikirim
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                                                <?= esc($t['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><i class="bi bi-person-up text-primary me-1"></i><?= esc($t['nama_user_pengirim'] ?? '-') ?></div>
                                        <?php if ($t['status'] === 'Diterima'): ?>
                                            <small class="text-success d-block">
                                                <i class="bi bi-person-check text-success me-1"></i><?= esc($t['nama_user_penerima'] ?? '-') ?>
                                                (<?= date('d/m H:i', strtotime($t['tgl_terima'])) ?>)
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted fst-italic">Menunggu konfirmasi</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('transfer-stok/detail/' . $t['id']) ?>" class="btn btn-outline-info" title="Lihat Detail Barang">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= base_url('transfer-stok/cetak/' . $t['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Cetak Surat Jalan">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php if ($t['status'] === 'Dikirim' && ($isAdmin || $currentCabangId == (int)$t['id_cabang_tujuan'])): ?>
                                                <a href="<?= base_url('transfer-stok/terima/' . $t['id']) ?>" class="btn btn-success" title="Konfirmasi Terima Barang" onclick="return confirm('Konfirmasi bahwa barang transfer ini telah tiba dan dicek di cabang Anda?')">
                                                    <i class="bi bi-box-seam-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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
    $(document).ready(function() {
        if ($('#table-transfer tbody tr').length > 1 || !$('#table-transfer tbody tr td').hasClass('text-muted')) {
            $('#table-transfer').DataTable({
                language: {
                    search: "Cari Mutasi:",
                    lengthMenu: "Tampil _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                },
                order: [[0, 'asc']]
            });
        }
    });
</script>
<?= $this->endSection() ?>

