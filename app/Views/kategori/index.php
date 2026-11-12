<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-tag-fill text-info me-2"></i>Master Kategori Produk</h4>
        <p class="text-muted small mb-0">Klasifikasi kelompok produk (Deterjen, Karbol Lantai, Cuci Piring, Bibit Parfum, Kemasan Jerigen) untuk mempermudah pencarian dan filter kasir.</p>
    </div>
    <?php if (session()->get('role') === 'admin'): ?>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kategori Baru
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

<!-- Metrik Kategori -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-info">
            <span class="text-muted small text-uppercase fw-semibold">Total Kategori Produk</span>
            <h3 class="fw-bold mt-1 mb-0 text-info"><?= number_format($total_kategori) ?></h3>
            <small class="text-muted">Kelompok klasifikasi barang</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Total Produk Terklasifikasi</span>
            <h3 class="fw-bold mt-1 mb-0 text-primary"><?= array_sum(array_column($kategori_list, 'total_barang')) ?></h3>
            <small class="text-muted">Item barang terdistribusi di kategori</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Status Master Kategori</span>
            <h3 class="fw-bold mt-1 mb-0 text-success"><i class="bi bi-check2-all me-1"></i>Aktif</h3>
            <small class="text-muted">Terintegrasi dengan POS & Barcode</small>
        </div>
    </div>
</div>

<!-- Tabel Kategori -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tableKategori" style="width: 100%;">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;" class="text-center">No</th>
                    <th>Nama Kategori Produk</th>
                    <th>Keterangan / Deskripsi</th>
                    <th class="text-center" style="width: 150px;">Jumlah Produk</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategori_list as $i => $k): ?>
                <tr>
                    <td class="text-center text-muted fw-semibold"><?= $i + 1 ?></td>
                    <td>
                        <strong class="text-dark fs-6"><?= esc($k['nama_kategori']) ?></strong>
                    </td>
                    <td>
                        <span class="text-muted small"><?= esc($k['keterangan'] ?: '-') ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-1 fw-bold">
                            <i class="bi bi-boxes me-1"></i><?= $k['total_barang'] ?> Produk
                        </span>
                    </td>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 btn-edit-kategori"
                            data-id="<?= $k['id'] ?>"
                            data-nama="<?= esc($k['nama_kategori']) ?>"
                            data-keterangan="<?= esc($k['keterangan']) ?>"
                            title="Edit Kategori">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="<?= base_url('kategori/hapus/' . $k['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 btn-hapus-kategori"
                            data-nama="<?= esc($k['nama_kategori']) ?>"
                            data-total="<?= $k['total_barang'] ?>"
                            title="Hapus Kategori">
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
<!-- MODAL TAMBAH KATEGORI (DRAGGABLE)             -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Kategori Produk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('kategori/simpan') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control fw-bold" placeholder="Misal: Sabun Cuci Tangan (Hand Soap)" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Keterangan / Deskripsi</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Deskripsi peruntukan atau ciri khas kategori ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT KATEGORI (DRAGGABLE)               -->
<!-- ============================================== -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Kategori Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('kategori/simpan') ?>" method="POST">
                <input type="hidden" name="id" id="edit-kat-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="edit-kat-nama" class="form-control fw-bold" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Keterangan / Deskripsi</label>
                        <textarea name="keterangan" id="edit-kat-keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Update Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tableKategori').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            search: "_INPUT_",
            searchPlaceholder: "Cari kategori..."
        },
        pageLength: 10,
        ordering: true,
        order: [[1, 'asc']]
    });

    // Handle Edit Kategori
    $('.btn-edit-kategori').on('click', function() {
        $('#edit-kat-id').val($(this).data('id'));
        $('#edit-kat-nama').val($(this).data('nama'));
        $('#edit-kat-keterangan').val($(this).data('keterangan'));
        $('#modalEditKategori').modal('show');
    });

    // Handle SweetAlert Delete
    $('.btn-hapus-kategori').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const nama = $(this).data('nama');
        const total = parseInt($(this).data('total')) || 0;

        if (total > 0) {
            Swal.fire({
                title: 'Tidak Dapat Dihapus',
                html: `Kategori <strong>${nama}</strong> masih memiliki <strong>${total} produk barang</strong> terdaftar.<br>Silakan ubah kategori produk-produk tersebut terlebih dahulu sebelum menghapus kategori ini.`,
                icon: 'warning'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus Kategori',
            html: `Apakah Anda yakin ingin menghapus kategori <strong>${nama}</strong>?`,
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

