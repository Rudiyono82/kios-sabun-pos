<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Katalog Barang &amp; Manajemen Stok</h5>
            <small class="text-muted">Daftar produk sabun, parfum laundry, harga jual eceran &amp; grosir</small>
        </div>
        <?php if ($is_admin): ?>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
            <i class="bi bi-plus-lg me-1"></i> Tambah Barang Baru
        </button>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="table-barang">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Kode / Barcode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <?php if ($is_admin): ?>
                    <th class="text-end">Harga Beli</th>
                    <?php endif; ?>
                    <th class="text-end">Harga Jual</th>
                    <?php if ($is_admin): ?>
                    <th class="text-center">Stok</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <?php if ($is_admin): ?>
                    <th class="text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barang as $b): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= esc($b['id_barang']) ?></div>
                            <small class="text-muted"><i class="bi bi-upc"></i> <?= esc($b['barcode'] ?: '-') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= esc($b['nama_barang']) ?></div>
                            <?php if ($b['is_jerigen']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-recycle"></i> Tukar Jerigen 5L</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= esc($b['nama_kategori'] ?? 'Umum') ?></span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($b['satuan']) ?></span></td>
                        <?php if ($is_admin): ?>
                        <td class="text-end text-muted">Rp <?= number_format((float)$b['harga_beli'], 0, ',', '.') ?></td>
                        <?php endif; ?>
                        <td class="text-end fw-bold text-primary">Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?></td>
                        <?php if ($is_admin): ?>
                        <td class="text-center">
                            <span class="badge <?= (float)$b['stok'] <= (float)$b['min_stok'] ? 'bg-danger' : 'bg-success' ?> fs-6 px-3 py-2">
                                <?= $b['stok'] ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span class="badge <?= $b['status'] == 'Aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                <?= esc($b['status']) ?>
                            </span>
                        </td>
                        <?php if ($is_admin): ?>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary btn-edit-barang" 
                                    data-barang='<?= json_encode($b) ?>' title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <a href="<?= base_url('barang/hapus/' . $b['id']) ?>" class="btn btn-outline-danger" 
                                    onclick="return confirm('Yakin ingin menghapus barang ini?')" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($is_admin): ?>
<!-- Modal Tambah / Edit Barang -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="<?= base_url('barang/simpan') ?>" method="POST">
                <input type="hidden" name="id" id="form-barang-id">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalBarangTitle">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kode Barang (ID Unique)</label>
                            <input type="text" name="id_barang" id="form-id_barang" class="form-control" placeholder="Contoh: B2020400" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Barcode Scanner</label>
                            <input type="text" name="barcode" id="form-barcode" class="form-control" placeholder="Scan Barcode EAN/UPC">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Nama Barang & Kemasan</label>
                            <input type="text" name="nama_barang" id="form-nama_barang" class="form-control" placeholder="Contoh: Sabun Cuci Piring Lemon 600ml" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kategori</label>
                            <select name="kategori_id" id="form-kategori_id" class="form-select">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= esc($k['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Satuan</label>
                            <select name="satuan" id="form-satuan" class="form-select">
                                <?php foreach ($satuan as $s): ?>
                                    <option value="<?= esc($s['nama_satuan']) ?>"><?= esc($s['nama_satuan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Harga Modal (Beli)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_beli" id="form-harga_beli" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Harga Jual Eceran</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_jual" id="form-harga_jual" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Harga Grosir / Reseller</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_grosir" id="form-harga_grosir" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Jumlah Stok Awal</label>
                            <input type="number" name="stok" id="form-stok" class="form-control" value="0" step="any" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Minimum Stok (Peringatan)</label>
                            <input type="number" name="min_stok" id="form-min_stok" class="form-control" value="5" step="any">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select name="status" id="form-status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_jerigen" id="form-is_jerigen" value="1">
                                <label class="form-check-label fw-semibold text-dark" for="form-is_jerigen">
                                    Termasuk Produk Refill / Tukar Jerigen 5 Liter
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium">Simpan Data Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#table-barang').DataTable({
        language: {
            search: "Cari Barang:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ barang",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    $('.btn-edit-barang').on('click', function() {
        const b = $(this).data('barang');
        $('#form-barang-id').val(b.id);
        $('#modalBarangTitle').text('Edit Barang: ' + b.nama_barang);
        $('#form-id_barang').val(b.id_barang).prop('readonly', true);
        $('#form-barcode').val(b.barcode);
        $('#form-nama_barang').val(b.nama_barang);
        $('#form-kategori_id').val(b.kategori_id);
        $('#form-satuan').val(b.satuan);
        $('#form-harga_beli').val(b.harga_beli);
        $('#form-harga_jual').val(b.harga_jual);
        $('#form-harga_grosir').val(b.harga_grosir);
        $('#form-stok').val(b.stok);
        $('#form-min_stok').val(b.min_stok);
        $('#form-status').val(b.status);
        $('#form-is_jerigen').prop('checked', b.is_jerigen == 1);
        $('#modalTambahBarang').modal('show');
    });

    $('#modalTambahBarang').on('hidden.bs.modal', function () {
        $('#form-barang-id').val('');
        $('#modalBarangTitle').text('Tambah Barang Baru');
        $('#form-id_barang').prop('readonly', false);
        $('form')[0].reset();
    });
</script>
<?= $this->endSection() ?>

