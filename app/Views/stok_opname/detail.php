<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-file-earmark-diff-fill text-primary me-2"></i>Rincian Hasil Audit Stok Opname</h4>
            <?php if ($sto['status'] === 'Dibatalkan'): ?>
                <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Sesi Dibatalkan (Revert)</span>
            <?php else: ?>
                <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check2-all me-1"></i> <?= esc($sto['status']) ?></span>
            <?php endif; ?>
        </div>
        <p class="text-muted small mb-0">No. Dokumen: <strong class="font-monospace text-primary"><?= esc($sto['no_sto']) ?></strong> &bull; Auditor: <strong><?= esc($sto['nama_petugas']) ?></strong> pada <?= date('d/m/Y H:i', strtotime($sto['tanggal'])) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('stok-opname') ?>" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar STO
        </a>
        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen Audit
        </button>
        <?php if ($is_admin && $sto['status'] !== 'Dibatalkan'): ?>
        <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRevertSTO">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Batalkan &amp; Revert Stok
        </button>
        <?php endif; ?>
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

<!-- Kartu Ringkasan Hasil STO -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Total Item Dihitung</span>
            <h3 class="fw-bold mt-1 mb-0 text-dark"><?= $sto['total_item_dihitung'] ?> Produk</h3>
            <small class="text-muted">Item diaudit di rak toko</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-danger">
            <span class="text-muted small text-uppercase fw-semibold">Barang Kurang / Minus</span>
            <h3 class="fw-bold mt-1 mb-0 text-danger"><?= count($items_kurang) ?> Item <span class="fs-6 fw-normal">(-<?= number_format($sto['total_selisih_minus'], 0) ?>)</span></h3>
            <small class="text-danger">Potensi hilang, bocor, atau rusak</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Barang Lebih / Surplus</span>
            <h3 class="fw-bold mt-1 mb-0 text-success"><?= count($items_lebih) ?> Item <span class="fs-6 fw-normal">(+<?= number_format($sto['total_selisih_plus'], 0) ?>)</span></h3>
            <small class="text-success">Kelebihan fisik dibanding komputer</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-info">
            <span class="text-muted small text-uppercase fw-semibold">Total Nilai Selisih HPP</span>
            <h3 class="fw-bold mt-1 mb-0 <?= (float)$sto['total_nilai_selisih'] < 0 ? 'text-danger' : 'text-success' ?>">
                Rp <?= number_format($sto['total_nilai_selisih'], 0, ',', '.') ?>
            </h3>
            <small class="text-muted">Berdasarkan Harga Beli Pokok</small>
        </div>
    </div>
</div>

<!-- Tab Navigasi Hasil STO: Mirip VB6 (Kurang Stok vs Lebih Stok vs Normal) -->
<div class="card-modern p-4">
    <ul class="nav nav-pills gap-2 border-bottom pb-3 mb-4" id="stoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="tab-all-btn" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab">
                <i class="bi bi-grid-fill me-1"></i> Semua Barang (<?= count($items) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold text-danger" id="tab-kurang-btn" data-bs-toggle="pill" data-bs-target="#tab-kurang" type="button" role="tab" style="background-color: #fef2f2;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Kurang Stok / Defisit (<?= count($items_kurang) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold text-success" id="tab-lebih-btn" data-bs-toggle="pill" data-bs-target="#tab-lebih" type="button" role="tab" style="background-color: #f0fdf4;">
                <i class="bi bi-plus-circle-fill me-1"></i> Lebih Stok / Surplus (<?= count($items_lebih) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold text-secondary" id="tab-cocok-btn" data-bs-toggle="pill" data-bs-target="#tab-cocok" type="button" role="tab" style="background-color: #f8fafc;">
                <i class="bi bi-check2-circle me-1"></i> Stok Cocok / Normal (<?= count($items_cocok) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="stoTabsContent">
        <!-- TAB 1: SEMUA BARANG -->
        <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
            <?= view('stok_opname/_table_items', ['itemList' => $items, 'tableId' => 'table-all']) ?>
        </div>

        <!-- TAB 2: BARANG KURANG (MINUS) -->
        <div class="tab-pane fade" id="tab-kurang" role="tabpanel">
            <?php if (empty($items_kurang)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check2-circle text-success fs-1"></i>
                    <p class="mt-2 mb-0 fw-bold">Bagus! Tidak ditemukan barang yang kurang / hilang pada sesi audit ini.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger py-2 small mb-3 border-0">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> Daftar barang berikut mengalami <strong>selisih minus (stok fisik di rak lebih sedikit daripada data sistem)</strong>. Periksa potensi kebocoran, barang expired, atau kesalahan kasir.
                </div>
                <?= view('stok_opname/_table_items', ['itemList' => $items_kurang, 'tableId' => 'table-kurang']) ?>
            <?php endif; ?>
        </div>

        <!-- TAB 3: BARANG LEBIH (SURPLUS) -->
        <div class="tab-pane fade" id="tab-lebih" role="tabpanel">
            <?php if (empty($items_lebih)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-dash-circle text-secondary fs-1"></i>
                    <p class="mt-2 mb-0 fw-bold">Tidak ada barang yang tercatat lebih pada sesi audit ini.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-success py-2 small mb-3 border-0">
                    <i class="bi bi-info-circle-fill me-1"></i> Daftar barang berikut mengalami <strong>selisih plus (stok fisik di rak lebih banyak daripada data sistem)</strong>.
                </div>
                <?= view('stok_opname/_table_items', ['itemList' => $items_lebih, 'tableId' => 'table-lebih']) ?>
            <?php endif; ?>
        </div>

        <!-- TAB 4: BARANG COCOK -->
        <div class="tab-pane fade" id="tab-cocok" role="tabpanel">
            <?php if (empty($items_cocok)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-question-circle text-warning fs-1"></i>
                    <p class="mt-2 mb-0">Seluruh barang yang diperiksa mengalami selisih penyesuaian.</p>
                </div>
            <?php else: ?>
                <?= view('stok_opname/_table_items', ['itemList' => $items_cocok, 'tableId' => 'table-cocok']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL POPUP REVERT STO (DILENGKAPI PASSWORD SUPER ADMIN) -->
<!-- ======================================================== -->
<div class="modal fade" id="modalRevertSTO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Konfirmasi Pembatalan &amp; Revert Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('stok-opname/batalkan/' . $sto['id']) ?>" method="POST" id="form-revert-sto">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small mb-3 border-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                        <strong>Peringatan Otoritas Owner:</strong> Pembatalan sesi STO <strong><?= esc($sto['no_sto']) ?></strong> akan mengembalikan seluruh stok barang ke kondisi semula sebelum opname ini disimpan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Password Super Admin / Owner <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" name="password" id="input-password-revert" class="form-control" placeholder="Masukkan password super admin..." required autofocus>
                        </div>
                        <small class="text-muted">Masukkan password akun admin untuk mengeksekusi pembatalan.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" id="btn-konfirmasi-revert">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Batalkan &amp; Kembalikan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#form-revert-sto').on('submit', function(e) {
        e.preventDefault();
        const pwd = $('#input-password-revert').val().trim();
        if (!pwd) {
            Swal.fire('Password Kosong', 'Harap masukkan password super admin.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Yakin Batalkan STO?',
            html: 'Stok barang akan otomatis di-revert (dikembalikan) ke jumlah sebelum sesi STO ini dijalankan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Revert Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit AJAX
                const form = document.getElementById('form-revert-sto');
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Otorisasi Ditolak', data.message || 'Password salah!', 'error');
                    }
                })
                .catch(() => {
                    form.submit();
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
