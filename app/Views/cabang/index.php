<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-buildings text-primary me-2"></i>Manajemen Multi-Cabang & Jaringan Toko</h4>
        <p class="text-muted small mb-0">Kelola daftar cabang outlet, tentukan cabang pusat, dan alihkan operasional toko antar cabang secara langsung.</p>
    </div>
    <?php if ($is_admin): ?>
    <div>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCabang" onclick="resetFormCabang()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Cabang Baru
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- DAFTAR CABANG TOKO -->
<div class="row g-3 mb-4">
    <?php 
    $activeCabangId = session()->get('id_cabang') ?? 1;
    foreach ($cabang_list as $c): 
        $isActive = ($c['id'] == $activeCabangId);
    ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-modern p-4 h-100 position-relative <?= $isActive ? 'border-primary border-2 shadow' : '' ?>">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace small mb-1"><?= esc($c['kode_cabang']) ?></span>
                    <h5 class="fw-bold mb-0 text-dark"><?= esc($c['nama_cabang']) ?></h5>
                </div>
                <div>
                    <?php if (!empty($c['is_pusat'])): ?>
                        <span class="badge bg-primary text-white"><i class="bi bi-star-fill me-1"></i>Pusat</span>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">Outlet</span>
                    <?php endif; ?>
                </div>
            </div>

            <p class="text-muted small mb-3">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= esc($c['alamat'] ?: 'Belum ada alamat') ?><br>
                <i class="bi bi-pin-map text-muted me-1"></i><?= esc($c['kota'] ?: '-') ?><br>
                <i class="bi bi-whatsapp text-success me-1"></i><?= esc($c['telepon'] ?: '-') ?>
            </p>

            <div class="border-top pt-3 d-flex justify-content-between align-items-center mt-auto">
                <div>
                    <?php if ($isActive): ?>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i>Sedang Digunakan
                        </span>
                    <?php else: ?>
                        <a href="<?= base_url('cabang/switch/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Beralih ke Sini
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($is_admin): ?>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-light border btn-edit-cabang" 
                        data-cabang='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>' 
                        title="Edit Cabang">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <?php if (empty($c['is_pusat'])): ?>
                    <a href="<?= base_url('cabang/hapus/' . $c['id']) ?>" class="btn btn-light border text-danger" 
                       onclick="return confirm('Yakin ingin menghapus cabang <?= esc($c['nama_cabang']) ?>?');" title="Hapus Cabang">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- MODAL TAMBAH / EDIT CABANG -->
<div class="modal fade" id="modalCabang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="<?= base_url('cabang/simpan') ?>" method="POST" id="formCabang">
                <input type="hidden" name="id" id="cabang-id">
                
                <div class="modal-header bg-primary text-white py-3 px-4" id="modalCabangHeader">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white text-primary p-2 rounded-3 shadow-sm">
                            <i class="bi bi-buildings-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalCabangTitle">Tambah Cabang Baru</h5>
                            <small class="text-white text-opacity-75" id="modalCabangSubtitle">Daftarkan outlet baru ke jaringan sistem POS toko</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Kode Cabang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_cabang" id="cabang-kode" class="form-control text-uppercase fw-bold font-monospace" placeholder="Contoh: CBG-03" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Nama Cabang / Toko <span class="text-danger">*</span></label>
                            <input type="text" name="nama_cabang" id="cabang-nama" class="form-control fw-bold" placeholder="Contoh: KSP Depok (Cabang 3)" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">Alamat Lengkap Toko</label>
                            <input type="text" name="alamat" id="cabang-alamat" class="form-control" placeholder="Nama Jalan, Ruko, No. Rumah">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Kota / Wilayah</label>
                            <input type="text" name="kota" id="cabang-kota" class="form-control" placeholder="Contoh: Depok / Bogor">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">No. Telepon / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" name="telepon" id="cabang-telepon" class="form-control" placeholder="Contoh: 0812-3456-7890">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Status Operasional</label>
                            <select name="status" id="cabang-status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Footer Struk Kasir Khusus Cabang Ini</label>
                            <textarea name="footer_struk" id="cabang-footer" class="form-control" rows="2" placeholder="Ucapan terima kasih atau informasi khusus cabang ini pada kertas struk..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_pusat" id="cabang-is_pusat" value="1">
                                <label class="form-check-label fw-bold text-dark" for="cabang-is_pusat">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Tetapkan Sebagai Cabang Kantor Pusat
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Data Cabang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function resetFormCabang() {
        $('#cabang-id').val('');
        $('#formCabang')[0].reset();
        $('#cabang-status').val('Aktif').trigger('change');
        $('#modalCabangHeader').removeClass('bg-warning text-dark').addClass('bg-primary text-white');
        $('#modalCabangHeader .btn-close').addClass('btn-close-white');
        $('#modalCabangTitle').text('Tambah Cabang Baru');
        $('#modalCabangSubtitle').text('Daftarkan outlet baru ke jaringan sistem POS toko');
    }

    $(document).on('click', '.btn-edit-cabang', function() {
        const c = $(this).data('cabang');
        $('#cabang-id').val(c.id);
        $('#cabang-kode').val(c.kode_cabang);
        $('#cabang-nama').val(c.nama_cabang);
        $('#cabang-alamat').val(c.alamat || '');
        $('#cabang-kota').val(c.kota || '');
        $('#cabang-telepon').val(c.telepon || '');
        $('#cabang-footer').val(c.footer_struk || '');
        $('#cabang-status').val(c.status || 'Aktif').trigger('change');
        $('#cabang-is_pusat').prop('checked', c.is_pusat == 1);

        $('#modalCabangHeader').removeClass('bg-primary text-white').addClass('bg-warning text-dark');
        $('#modalCabangHeader .btn-close').removeClass('btn-close-white');
        $('#modalCabangTitle').text('Edit Cabang: ' + c.nama_cabang);
        $('#modalCabangSubtitle').text('Perbarui alamat, kontak, dan pengaturan operasional cabang');

        $('#modalCabang').modal('show');
    });
</script>
<?= $this->endSection() ?>
