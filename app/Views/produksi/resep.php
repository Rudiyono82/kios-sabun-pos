<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-card-checklist text-primary me-2"></i>Master Formula & Resep Sabun</h4>
        <p class="text-muted small mb-0">Atur takaran racikan bahan baku kimia (Texapon, Soda Ash, Pewarna, Bibit Parfum, Jerigen) untuk menghasilkan sabun siap jual.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('produksi') ?>" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Produksi
        </a>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahResep">
            <i class="bi bi-plus-circle me-1"></i> Buat Formula Resep Baru
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Daftar Resep Sabun -->
<div class="row g-3">
    <?php if (empty($templates)): ?>
        <div class="col-12">
            <div class="card-modern p-5 text-center text-muted">
                <i class="bi bi-card-checklist fs-1 opacity-50 d-block mb-2"></i>
                Belum ada master formula resep sabun. Klik tombol <strong>Buat Formula Resep Baru</strong> di atas untuk menambahkan racikan pertama Anda.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($templates as $t): ?>
            <div class="col-12 col-lg-6">
                <div class="card-modern p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace mb-1"><?= esc($t['no_template']) ?></span>
                            <h5 class="fw-bold mb-1 text-dark"><?= esc($t['nama_template']) ?></h5>
                            <small class="text-muted">Target Produk Jadi: <strong class="text-success"><?= esc($t['nama_barang_jadi']) ?></strong> (<?= $t['qty_hasil'] ?> <?= esc($t['satuan_hasil']) ?>)</small>
                        </div>
                        <?php if (session()->get('role') === 'admin'): ?>
                            <a href="<?= base_url('produksi/hapus-resep/' . $t['id']) ?>" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('Hapus resep ini?')" title="Hapus Resep">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3 flex-grow-1">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.75rem;">Komposisi Bahan Baku per Batch:</small>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-light mb-0">
                                <thead class="table-white small">
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th class="text-center" style="width: 35%;">Takaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($t['details'])): ?>
                                        <tr><td colspan="2" class="text-center text-muted small py-2">Belum ada rincian bahan</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($t['details'] as $d): ?>
                                            <tr>
                                                <td class="small fw-semibold text-dark"><?= esc($d['nama_barang_baku']) ?></td>
                                                <td class="small text-center fw-bold text-primary"><?= $d['qty_per_batch'] ?> <?= esc($d['satuan']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($t['keterangan'])): ?>
                        <div class="small text-muted bg-white p-2 rounded border mb-3">
                            <i class="bi bi-info-circle me-1"></i><?= esc($t['keterangan']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-auto pt-2 border-top">
                        <a href="<?= base_url('produksi') ?>" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                            <i class="bi bi-play-circle me-1"></i> Racik Menggunakan Resep Ini
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================== -->
<!-- MODAL BUAT FORMULA RESEP BARU                  -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahResep" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-plus-circle me-2"></i>Buat Formula Resep Sabun Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('produksi/simpan-resep') ?>" method="POST" id="form-tambah-resep">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Nama Formula / Resep <span class="text-danger">*</span></label>
                            <input type="text" name="nama_template" class="form-control" placeholder="Contoh: Resep Deterjen Cair Matic 5 Liter..." required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Satuan Kemasan Hasil <span class="text-danger">*</span></label>
                            <input type="text" name="satuan_hasil" class="form-control" value="JERIGEN 5L" placeholder="Contoh: JERIGEN 5L / BOTOL 1L" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Pilih Produk Barang Jadi Siap Jual <span class="text-danger">*</span></label>
                            <select name="id_barang_jadi" class="form-select select2" required>
                                <option value="">-- Pilih Produk Jadi Hasil Racik --</option>
                                <?php foreach ($barang_list as $b): ?>
                                    <option value="<?= esc($b['id_barang']) ?>">
                                        <?= esc($b['nama_barang']) ?> [Kode: <?= esc($b['id_barang']) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Target Hasil per Batch</label>
                            <input type="number" name="qty_hasil" class="form-control text-center fw-bold" value="1" min="1" required>
                        </div>

                        <!-- Baris Rincian Bahan Baku Dinamis -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold text-dark mb-0">Komposisi Bahan Baku Yang Dibutuhkan:</label>
                                    <button type="button" class="btn btn-success btn-sm py-1 px-3 rounded-pill" id="btn-tambah-baris-bahan">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Bahan
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-0" id="table-bahan-dinamis">
                                        <thead class="table-light small">
                                            <tr>
                                                <th style="width: 55%;">Bahan Baku (Kimia / Jerigen)</th>
                                                <th style="width: 25%;" class="text-center">Takaran (Qty)</th>
                                                <th style="width: 20%;" class="text-center">Satuan</th>
                                                <th style="width: 5%;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-bahan">
                                            <!-- Baris Bahan Baku 1 -->
                                            <tr>
                                                <td>
                                                    <select name="id_barang_baku[]" class="form-select form-select-sm" required>
                                                        <option value="">-- Pilih Bahan Baku --</option>
                                                        <?php foreach ($barang_list as $b): ?>
                                                            <option value="<?= esc($b['id_barang']) ?>">
                                                                <?= esc($b['nama_barang']) ?> [<?= esc($b['satuan']) ?>]
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="qty_per_batch[]" class="form-control form-control-sm text-center" min="0.001" step="any" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="satuan_bahan[]" class="form-control form-control-sm text-center" value="KG">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-hapus-baris"><i class="bi bi-x-circle"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Instruksi Racik / Catatan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Larutkan Texapon dengan air hangat terlebih dahulu sebelum memasukkan pewarna dan bibit..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Formula Resep
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
        // Tambah Baris Bahan Baku Dinamis
        $('#btn-tambah-baris-bahan').on('click', function() {
            const row = `
                <tr>
                    <td>
                        <select name="id_barang_baku[]" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            <?php foreach ($barang_list as $b): ?>
                                <option value="<?= esc($b['id_barang']) ?>">
                                    <?= esc($b['nama_barang']) ?> [<?= esc($b['satuan']) ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="qty_per_batch[]" class="form-control form-control-sm text-center" min="0.001" step="any" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="satuan_bahan[]" class="form-control form-control-sm text-center" value="KG">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-hapus-baris"><i class="bi bi-x-circle"></i></button>
                    </td>
                </tr>
            `;
            $('#tbody-bahan').append(row);
        });

        // Hapus Baris Bahan Baku
        $(document).on('click', '.btn-hapus-baris', function() {
            if ($('#tbody-bahan tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                Swal.fire('Info', 'Resep minimal harus memiliki 1 bahan baku.', 'info');
            }
        });
    });
</script>
<?= $this->endSection() ?>

