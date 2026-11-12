<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-rulers text-warning me-2"></i>Master Satuan Barang</h4>
        <p class="text-muted small mb-0">Standarisasi satuan ukuran kemasan (PCS, BOTOL, JERIGEN 5L, LITER, PAKET, DUS) untuk akurasi stok dan penjualan kasir.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSatuan">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Satuan Baru
        </button>
    </div>
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

<!-- Metrik Satuan -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <span class="text-muted small text-uppercase fw-semibold">Total Satuan Terdaftar</span>
            <h3 class="fw-bold mt-1 mb-0 text-warning"><?= number_format($total_satuan) ?></h3>
            <small class="text-muted">Unit ukuran operasional toko</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Total Produk Terkait</span>
            <h3 class="fw-bold mt-1 mb-0 text-primary"><?= array_sum(array_column($satuan_list, 'total_barang')) ?></h3>
            <small class="text-muted">Item produk yang menggunakan satuan ini</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Konsistensi Unit Ukuran</span>
            <h3 class="fw-bold mt-1 mb-0 text-success"><i class="bi bi-shield-check me-1"></i>Tervalidasi</h3>
            <small class="text-muted">Sinkron dengan inventaris & POS</small>
        </div>
    </div>
</div>

<!-- Tabel Satuan -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tableSatuan" style="width: 100%;">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;" class="text-center">No</th>
                    <th>Nama Satuan Kemasan</th>
                    <th class="text-center" style="width: 180px;">Jumlah Produk Terkait</th>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($satuan_list as $i => $sat): ?>
                <tr>
                    <td class="text-center text-muted fw-semibold"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning fs-6 px-3 py-1 font-monospace fw-bold">
                                <?= esc($sat['nama_satuan']) ?>
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-1 fw-bold">
                            <i class="bi bi-box-seam me-1"></i><?= $sat['total_barang'] ?> Produk
                        </span>
                    </td>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 btn-edit-satuan"
                            data-id="<?= $sat['id'] ?>"
                            data-nama="<?= esc($sat['nama_satuan']) ?>"
                            title="Edit / Lihat Satuan">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="<?= base_url('satuan/hapus/' . $sat['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 btn-hapus-satuan"
                            data-nama="<?= esc($sat['nama_satuan']) ?>"
                            data-total="<?= $sat['total_barang'] ?>"
                            title="Hapus Satuan">
                            <i class="bi bi-trash3"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH SATUAN (DRAGGABLE)               -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahSatuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Satuan Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('satuan/simpan') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Satuan (Otomatis Huruf Besar) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satuan" class="form-control fw-bold font-monospace text-uppercase" placeholder="Misal: PAIL 20L, DUS, PACK" required autofocus>
                        <small class="text-muted">Contoh: BOTOL, JERIGEN 5L, LITER, PCS, DUS</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Simpan Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT SATUAN (DRAGGABLE)                 -->
<!-- ============================================== -->
<div class="modal fade" id="modalEditSatuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Satuan Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('satuan/simpan') ?>" method="POST">
                <input type="hidden" name="id" id="edit-sat-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satuan" id="edit-sat-nama" class="form-control fw-bold font-monospace text-uppercase" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Update Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tableSatuan').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            search: "_INPUT_",
            searchPlaceholder: "Cari satuan..."
        },
        pageLength: 10,
        ordering: true,
        order: [[1, 'asc']]
    });

    // Handle Edit Satuan
    $('.btn-edit-satuan').on('click', function() {
        $('#edit-sat-id').val($(this).data('id'));
        $('#edit-sat-nama').val($(this).data('nama'));
        $('#modalEditSatuan').modal('show');
    });

    // Handle SweetAlert Delete
    const isAdmin = <?= (session()->get('role') === 'admin') ? 'true' : 'false' ?>;

    $('.btn-hapus-satuan').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const nama = $(this).data('nama');
        const total = parseInt($(this).data('total')) || 0;

        if (total > 0) {
            Swal.fire({
                title: 'Tidak Dapat Dihapus',
                html: `Satuan <strong>${nama}</strong> masih dipakai oleh <strong>${total} produk barang</strong>.<br>Silakan perbarui satuan barang terkait terlebih dahulu sebelum menghapus satuan ini.`,
                icon: 'warning'
            });
            return;
        }

        if (isAdmin) {
            Swal.fire({
                title: 'Konfirmasi Hapus Satuan',
                html: `Apakah Anda yakin ingin menghapus satuan <strong>${nama}</strong>?`,
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
        } else {
            // Kasir: Wajib Input Password Admin
            Swal.fire({
                title: 'Otorisasi Supervisor',
                html: `Penghapusan satuan <b>${nama}</b> oleh kasir memerlukan otorisasi Supervisor.<br><small class="text-muted">Silakan masukkan Password Admin:</small>`,
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocomplete: 'new-password',
                    placeholder: 'Masukkan Password Admin...'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Verifikasi &amp; Hapus',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (adminPassword) => {
                    if (!adminPassword) {
                        Swal.showValidationMessage('Password Admin wajib diisi!');
                        return false;
                    }
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ admin_password: adminPassword })
                    })
                    .then(async (response) => {
                        const data = await response.json().catch(() => null);
                        if (!response.ok || (data && data.status === 'error')) {
                            const msg = (data && data.message) ? data.message : 'Password Admin salah atau otorisasi ditolak!';
                            throw new Error(msg);
                        }
                        return data;
                    })
                    .catch((error) => {
                        Swal.showValidationMessage(error.message || 'Gagal memproses otorisasi admin!');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Berhasil Terhapus!',
                        text: result.value.message || 'Satuan berhasil dihapus.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    });
});
</script>

<?= $this->endSection() ?>

