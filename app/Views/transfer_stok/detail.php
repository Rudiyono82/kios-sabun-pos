<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-box-seam text-primary me-2"></i>Detail Dokumen Transfer: <?= esc($transfer['no_transfer']) ?>
            </h4>
            <p class="text-muted small mb-0">Informasi lengkap pergerakan stok barang antar cabang dan histori penerimaan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('transfer-stok') ?>" class="btn btn-outline-secondary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="<?= base_url('transfer-stok/cetak/' . $transfer['id']) ?>" target="_blank" class="btn btn-outline-primary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i>
                <span>Cetak Surat Jalan</span>
            </a>
            <?php 
                $session = session();
                $currentCabangId = (int)($session->get('id_cabang') ?? 1);
                $isAdmin = ($session->get('role') === 'admin');
                if ($transfer['status'] === 'Dikirim' && ($isAdmin || $currentCabangId === (int)$transfer['id_cabang_tujuan'])): 
            ?>
                <a href="<?= base_url('transfer-stok/terima/' . $transfer['id']) ?>" class="btn btn-success px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="return confirm('Konfirmasi bahwa barang transfer ini telah tiba dan dicek di cabang Anda?')">
                    <i class="bi bi-check2-circle fs-5"></i>
                    <span>Konfirmasi Penerimaan Barang</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Informasi Status & Rute -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card-modern p-3 h-100">
                <small class="text-muted fw-bold d-block mb-1">STATUS TRANSFER</small>
                <?php if ($transfer['status'] === 'Diterima'): ?>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Diterima Selesai
                    </span>
                    <small class="text-muted d-block mt-2">Diterima: <?= date('d M Y, H:i', strtotime($transfer['tgl_terima'])) ?> WIB</small>
                <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold fs-6">
                        <i class="bi bi-truck me-1"></i> Dalam Pengiriman
                    </span>
                    <small class="text-muted d-block mt-2">Dikirim: <?= date('d M Y, H:i', strtotime($transfer['tanggal'])) ?> WIB</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card-modern p-3 h-100">
                <small class="text-muted fw-bold d-block mb-1">CABANG ASAL (PENGIRIM)</small>
                <div class="fw-bold text-dark fs-6"><?= esc($transfer['nama_cabang_asal'] ?? '-') ?></div>
                <small class="text-muted d-block"><?= esc($transfer['alamat_asal'] ?? '-') ?></small>
                <small class="text-primary fw-semibold d-block mt-1">Petugas: <?= esc($transfer['nama_user_pengirim'] ?? '-') ?></small>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card-modern p-3 h-100">
                <small class="text-muted fw-bold d-block mb-1">CABANG TUJUAN (PENERIMA)</small>
                <div class="fw-bold text-dark fs-6"><?= esc($transfer['nama_cabang_tujuan'] ?? '-') ?></div>
                <small class="text-muted d-block"><?= esc($transfer['alamat_tujuan'] ?? '-') ?></small>
                <small class="text-success fw-semibold d-block mt-1">
                    Petugas: <?= esc($transfer['nama_user_penerima'] ?? ($transfer['status'] === 'Dikirim' ? '(Belum diterima)' : '-')) ?>
                </small>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card-modern p-3 h-100">
                <small class="text-muted fw-bold d-block mb-1">TOTAL KUANTITAS</small>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-4 fw-bold text-primary"><?= number_format($transfer['total_item']) ?></span>
                    <span class="text-muted">Item Barang</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-5 fw-bold text-secondary"><?= (float)$transfer['total_qty'] ?></span>
                    <span class="text-muted">Total Qty (Pcs)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Barang -->
    <div class="card-modern">
        <div class="card-header bg-dark text-white p-3 fw-bold rounded-top-4 d-flex align-items-center gap-2">
            <i class="bi bi-list-check"></i> Rincian Barang Dalam Pengiriman
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Barang</th>
                            <th class="text-center" style="width: 140px;">Qty Dikirim</th>
                            <th class="text-center" style="width: 140px;">Qty Diterima</th>
                            <th class="text-center" style="width: 100px;">Satuan</th>
                            <th>Catatan Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($details as $d): ?>
                            <tr>
                                <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                                <td class="fw-semibold text-dark"><?= esc($d['nama_barang']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-1"><?= (float)$d['qty'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($transfer['status'] === 'Diterima'): ?>
                                        <span class="badge bg-success-subtle text-success fs-6 px-3 py-1"><?= (float)$d['qty_terima'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">0 (In transit)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted"><?= esc($d['satuan'] ?? 'Pcs') ?></td>
                                <td class="text-muted small"><?= esc($d['catatan'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($transfer['catatan'])): ?>
                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-dark small mb-1"><i class="bi bi-chat-left-text me-1"></i> Catatan Ekspedisi / Pengiriman:</div>
                    <div class="text-muted small"><?= nl2br(esc($transfer['catatan'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

