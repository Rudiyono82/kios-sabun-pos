<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-truck text-primary me-2"></i>Master Data Supplier</h4>
        <p class="text-muted small mb-0">Kelola daftar pemasok bahan baku, bibit parfum, botol/jerigen, dan produk sabun jadi untuk transaksi pembelian & restock toko.</p>
    </div>
    <?php if (session()->get('role') === 'admin'): ?>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Supplier Baru
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Metrik Supplier -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Total Supplier Mitra</span>
            <h3 class="fw-bold mt-1 mb-0 text-primary"><?= number_format($total_supplier) ?></h3>
            <small class="text-muted">Pemasok terdaftar</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Supplier Bahan / Parfum</span>
            <h3 class="fw-bold mt-1 mb-0 text-success"><?= count(array_filter($suppliers, fn($s) => stripos($s['nama_supplier'], 'kimia') !== false || stripos($s['nama_supplier'], 'aroma') !== false || stripos($s['nama_supplier'], 'parfum') !== false)) ?></h3>
            <small class="text-muted">Produsen & distributor utama</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-info">
            <span class="text-muted small text-uppercase fw-semibold">Supplier Kemasan & Jerigen</span>
            <h3 class="fw-bold mt-1 mb-0 text-info"><?= count(array_filter($suppliers, fn($s) => stripos($s['nama_supplier'], 'plastik') !== false || stripos($s['nama_supplier'], 'jerigen') !== false || stripos($s['nama_supplier'], 'botol') !== false)) ?></h3>
            <small class="text-muted">Vendor kemasan botol & jerigen</small>
        </div>
    </div>
</div>

<!-- Tabel Supplier -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tableSupplier" style="width: 100%;">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th style="width: 120px;">Kode</th>
                    <th>Nama Supplier</th>
                    <th>Alamat Pemasok</th>
                    <th>No. Telepon / WA</th>
                    <th>Kontak PIC</th>
                    <th class="text-center" style="width: 100px;">Transaksi</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $i => $s): ?>
                <tr>
                    <td class="text-center text-muted fw-semibold"><?= $i + 1 ?></td>
                    <td><span class="badge bg-secondary font-monospace"><?= esc($s['kode_supplier']) ?></span></td>
                    <td>
                        <strong class="text-dark d-block"><?= esc($s['nama_supplier']) ?></strong>
                    </td>
                    <td>
                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;" title="<?= esc($s['alamat']) ?>">
                            <?= esc($s['alamat'] ?: '-') ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($s['telepon'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $s['telepon']) ?>" target="_blank" class="text-decoration-none text-success small fw-semibold">
                                <i class="bi bi-whatsapp me-1"></i><?= esc($s['telepon']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= esc($s['contact_person'] ?: 'PIC') ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= $s['total_pembelian'] ?> Faktur</span>
                    </td>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 btn-edit-supplier"
                            data-id="<?= $s['id'] ?>"
                            data-kode="<?= esc($s['kode_supplier']) ?>"
                            data-nama="<?= esc($s['nama_supplier']) ?>"
                            data-alamat="<?= esc($s['alamat']) ?>"
                            data-telepon="<?= esc($s['telepon']) ?>"
                            data-pic="<?= esc($s['contact_person']) ?>"
                            title="Edit Data Supplier">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="<?= base_url('supplier/hapus/' . $s['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 btn-hapus-supplier"
                            data-nama="<?= esc($s['nama_supplier']) ?>"
                            title="Hapus Supplier">
                            <i class="bi bi-trash3"></i>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH SUPPLIER (DRAGGABLE)             -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Supplier Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('supplier/simpan') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Kode Supplier (Otomatis)</label>
                        <input type="text" name="kode_supplier" class="form-control font-monospace bg-light fw-bold" value="<?= esc($kode_otomatis) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Supplier / Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_supplier" class="form-control fw-bold" placeholder="Misal: PT. Aroma Wangi Sentosa" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama PIC / Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Misal: Bpk. Gunawan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">No. Telepon / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="telepon" class="form-control" placeholder="Misal: 081287654321">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat Gudang / Kantor Pemasok</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Jl. Industri Kimia No. 12..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT SUPPLIER (DRAGGABLE)               -->
<!-- ============================================== -->
<div class="modal fade" id="modalEditSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('supplier/simpan') ?>" method="POST">
                <input type="hidden" name="id" id="edit-sup-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Kode Supplier</label>
                        <input type="text" name="kode_supplier" id="edit-sup-kode" class="form-control font-monospace bg-light fw-bold" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Supplier / Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_supplier" id="edit-sup-nama" class="form-control fw-bold" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama PIC / Contact Person</label>
                        <input type="text" name="contact_person" id="edit-sup-pic" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">No. Telepon / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="telepon" id="edit-sup-telepon" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat Gudang / Kantor Pemasok</label>
                        <textarea name="alamat" id="edit-sup-alamat" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tableSupplier').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            search: "_INPUT_",
            searchPlaceholder: "Cari supplier / no telp..."
        },
        pageLength: 15,
        ordering: true,
        order: [[0, 'asc']]
    });

    // Handle Edit Supplier
    $('.btn-edit-supplier').on('click', function() {
        $('#edit-sup-id').val($(this).data('id'));
        $('#edit-sup-kode').val($(this).data('kode'));
        $('#edit-sup-nama').val($(this).data('nama'));
        $('#edit-sup-alamat').val($(this).data('alamat'));
        $('#edit-sup-telepon').val($(this).data('telepon'));
        $('#edit-sup-pic').val($(this).data('pic'));
        $('#modalEditSupplier').modal('show');
    });

    // Handle SweetAlert Delete
    $('.btn-hapus-supplier').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const nama = $(this).data('nama');

        Swal.fire({
            title: 'Konfirmasi Hapus Supplier',
            html: `Apakah Anda yakin ingin menghapus data supplier <strong>${nama}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<?= $this->endSection() ?>

