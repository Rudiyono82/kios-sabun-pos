<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Katalog Barang &amp; Manajemen Stok</h5>
            <small class="text-muted">Daftar produk sabun, parfum laundry, harga jual eceran &amp; grosir bertingkat</small>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
            <i class="bi bi-plus-lg me-1"></i> Tambah Barang Baru
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
                    <th class="text-center" style="width: 110px;">Aksi</th>
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
                            <?php if (!empty($b['is_jerigen'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-recycle"></i> Tukar Jerigen 5L</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= esc($b['nama_kategori'] ?? 'Umum') ?></span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($b['satuan']) ?></span></td>
                        <?php if ($is_admin): ?>
                        <td class="text-end text-muted">Rp <?= number_format((float)$b['harga_beli'], 0, ',', '.') ?></td>
                        <?php endif; ?>
                        <td class="text-end fw-bold text-primary">
                            <div>Rp <?= number_format((float)$b['harga_jual'], 0, ',', '.') ?></div>
                            <?php if ((float)($b['harga_grosir'] ?? 0) > 0): ?>
                                <small class="text-muted d-block fw-normal" style="font-size: 0.72rem;">Grosir: Rp <?= number_format((float)$b['harga_grosir'], 0, ',', '.') ?></small>
                            <?php endif; ?>
                            <?php if ((float)($b['hrg_paket_a'] ?? 0) > 0): ?>
                                <small class="text-success d-block fw-normal" style="font-size: 0.70rem;">Pkt A (&ge;<?= (int)$b['qty_paket_a'] ?>): Rp <?= number_format((float)$b['hrg_paket_a'], 0, ',', '.') ?></small>
                            <?php endif; ?>
                        </td>
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
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary btn-edit-barang" 
                                    data-barang='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>' 
                                    title="<?= $is_admin ? 'Edit Data Barang' : 'Lihat / Edit Barang' ?>">
                                    <i class="bi <?= $is_admin ? 'bi-pencil-fill' : 'bi-eye-fill' ?>"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-hapus" 
                                    data-url="<?= base_url('barang/hapus/' . $b['id']) ?>" 
                                    data-nama="<?= esc($b['nama_barang']) ?>"
                                    title="Hapus Barang">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah / Edit / Lihat Barang -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl my-4">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="<?= base_url('barang/simpan') ?>" method="POST" id="formBarang">
                <input type="hidden" name="id" id="form-barang-id">
                
                <div class="modal-header bg-primary text-white py-3 px-4" id="modalBarangHeader">
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalBarangTitle"><i class="bi bi-box-seam me-2"></i>Tambah Barang Baru</h5>
                        <small class="text-white text-opacity-75" id="modalBarangSubtitle">Lengkapi data produk, kategori, satuan, serta harga grosir paket</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Informasi Dasar -->
                        <div class="col-12">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                <i class="bi bi-info-circle text-primary me-2"></i>Informasi Umum Produk
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Kode Barang (ID Unique) <span class="text-danger">*</span></label>
                            <input type="text" name="id_barang" id="form-id_barang" class="form-control text-uppercase fw-bold" placeholder="Contoh: B2020400" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Barcode Scanner</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                <input type="text" name="barcode" id="form-barcode" class="form-control" placeholder="Scan Barcode EAN/UPC">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Status Produk</label>
                            <select name="status" id="form-status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Nama Barang &amp; Kemasan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="form-nama_barang" class="form-control fw-semibold" placeholder="Contoh: Sabun Cuci Piring Lemon 600ml" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Kategori Produk <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="form-kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori (Wajib) --</option>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= esc($k['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Satuan Kemasan <span class="text-danger">*</span></label>
                            <select name="satuan" id="form-satuan" class="form-select" required>
                                <option value="">-- Pilih Satuan (Wajib) --</option>
                                <?php foreach ($satuan as $s): ?>
                                    <option value="<?= esc($s['nama_satuan']) ?>"><?= esc($s['nama_satuan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_jerigen" id="form-is_jerigen" value="1">
                                <label class="form-check-label fw-semibold text-dark" for="form-is_jerigen">
                                    <i class="bi bi-recycle text-success me-1"></i> Termasuk Produk Refill / Tukar Jerigen 5 Liter
                                </label>
                            </div>
                        </div>

                        <!-- Struktur Harga Pokok & Eceran -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-cash-stack text-success me-2"></i>Harga Pokok &amp; Harga Jual Standar
                                </h6>
                                <div id="badge-header-harga">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        <i class="bi bi-pencil-fill me-1"></i>Bisa Diinput
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">
                                Harga Modal (Beli) <span class="text-danger">*</span>
                                <span class="badge bg-secondary small d-none" id="badge-lock-beli">Locked</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" name="harga_beli" id="form-harga_beli" class="form-control input-currency" value="0" required placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">
                                Harga Jual Eceran <span class="text-danger">*</span>
                                <span class="badge bg-secondary small d-none" id="badge-lock-jual">Locked</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary-subtle text-primary fw-bold">Rp</span>
                                <input type="text" inputmode="numeric" name="harga_jual" id="form-harga_jual" class="form-control fw-bold input-currency" value="0" required placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">
                                Harga Grosir Standar / Reseller
                                <span class="badge bg-secondary small d-none" id="badge-lock-grosir">Locked</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" name="harga_grosir" id="form-harga_grosir" class="form-control input-currency" value="0" placeholder="0">
                            </div>
                        </div>

                        <!-- Konfigurasi Harga Jual Grosir Bertingkat (Paket A, B, C) -->
                        <div class="col-12 mt-4">
                            <div class="card border rounded-3 bg-light p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-boxes text-primary me-2"></i>Konfigurasi Harga Jual Grosir Bertingkat (Paket A, B, C)
                                    </h6>
                                    <div id="badge-header-paket">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                            <i class="bi bi-pencil-fill me-1"></i>Bisa Diinput
                                        </span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">
                                    Atur harga promo/grosir otomatis yang langsung berlaku saat kasir menginput jumlah belanja item tertentu di POS.
                                </p>
                                
                                <div class="row g-3">
                                    <!-- Paket A -->
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-white shadow-sm h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-primary small"><i class="bi bi-tag-fill me-1"></i>Paket Grosir A</span>
                                                <span class="badge bg-primary-subtle text-primary small">Tier 1</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Min. Qty Belanja (Item)</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" name="qty_paket_a" id="form-qty_paket_a" class="form-control text-center fw-bold" value="0" placeholder="0">
                                                    <span class="input-group-text">Item</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted mb-1">Harga Satuan Paket A</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" name="hrg_paket_a" id="form-hrg_paket_a" class="form-control text-end fw-bold text-primary input-currency" value="0" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Paket B -->
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-white shadow-sm h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-success small"><i class="bi bi-tag-fill me-1"></i>Paket Grosir B</span>
                                                <span class="badge bg-success-subtle text-success small">Tier 2</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Min. Qty Belanja (Item)</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" name="qty_paket_b" id="form-qty_paket_b" class="form-control text-center fw-bold" value="0" placeholder="0">
                                                    <span class="input-group-text">Item</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted mb-1">Harga Satuan Paket B</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" name="hrg_paket_b" id="form-hrg_paket_b" class="form-control text-end fw-bold text-success input-currency" value="0" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Paket C -->
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-white shadow-sm h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-info small"><i class="bi bi-tag-fill me-1"></i>Paket Grosir C</span>
                                                <span class="badge bg-info-subtle text-info small">Tier 3</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Min. Qty Belanja (Item)</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" name="qty_paket_c" id="form-qty_paket_c" class="form-control text-center fw-bold" value="0" placeholder="0">
                                                    <span class="input-group-text">Item</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted mb-1">Harga Satuan Paket C</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" name="hrg_paket_c" id="form-hrg_paket_c" class="form-control text-end fw-bold text-info input-currency" value="0" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stok Awal & Min Stok (Admin Only) -->
                        <?php if ($is_admin): ?>
                        <div class="col-md-6 mt-3">
                            <label class="form-label small fw-semibold text-muted">Jumlah Stok Saat Ini</label>
                            <input type="number" name="stok" id="form-stok" class="form-control" value="0" step="any" required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label small fw-semibold text-muted">Minimum Stok (Peringatan Habis)</label>
                            <input type="number" name="min_stok" id="form-min_stok" class="form-control" value="5" step="any">
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="stok" id="form-stok" value="0">
                        <input type="hidden" name="min_stok" id="form-min_stok" value="5">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Data Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;

    $('#table-barang').DataTable({
        language: {
            search: "Cari Barang:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ barang",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    // Event Klik Edit / Lihat Data Barang
    $('.btn-edit-barang').on('click', function() {
        const b = $(this).data('barang');
        $('#form-barang-id').val(b.id);
        $('#modalBarangHeader').removeClass('bg-primary text-white').addClass('bg-warning text-dark');
        $('#modalBarangHeader .btn-close').removeClass('btn-close-white');
        $('#modalBarangTitle').removeClass('text-white').addClass('text-dark').html('<i class="bi bi-pencil-square me-2"></i>' + (isAdmin ? ('Edit Barang: ' + b.nama_barang) : ('Lihat / Edit Detail: ' + b.nama_barang)));
        $('#modalBarangSubtitle').removeClass('text-white text-opacity-75').addClass('text-dark text-opacity-75').text(isAdmin ? 'Perbarui informasi produk, harga jual, dan paket grosir' : 'Detail informasi produk (Harga dan Paket hanya dapat diubah oleh Admin)');
        
        $('#form-id_barang').val(b.id_barang).prop('readonly', true);
        $('#form-barcode').val(b.barcode || '');
        $('#form-nama_barang').val(b.nama_barang || '');
        $('#form-kategori_id').val(b.kategori_id || '');
        $('#form-satuan').val(b.satuan || '');
        $('#form-status').val(b.status || 'Aktif');
        $('#form-is_jerigen').prop('checked', b.is_jerigen == 1);

        if (isAdmin) {
            $('#form-harga_beli').val(formatRupiah(b.harga_beli || 0)).prop('disabled', false).removeClass('bg-light text-muted');
            $('#form-harga_jual').val(formatRupiah(b.harga_jual || 0)).prop('disabled', false).removeClass('bg-light text-muted');
            $('#form-harga_grosir').val(formatRupiah(b.harga_grosir || 0)).prop('disabled', false).removeClass('bg-light text-muted');
            $('#form-qty_paket_a').val(b.qty_paket_a || 0).prop('disabled', false).removeClass('bg-light');
            $('#form-hrg_paket_a').val(formatRupiah(b.hrg_paket_a || 0)).prop('disabled', false).removeClass('bg-light');
            $('#form-qty_paket_b').val(b.qty_paket_b || 0).prop('disabled', false).removeClass('bg-light');
            $('#form-hrg_paket_b').val(formatRupiah(b.hrg_paket_b || 0)).prop('disabled', false).removeClass('bg-light');
            $('#form-qty_paket_c').val(b.qty_paket_c || 0).prop('disabled', false).removeClass('bg-light');
            $('#form-hrg_paket_c').val(formatRupiah(b.hrg_paket_c || 0)).prop('disabled', false).removeClass('bg-light');
            $('#form-stok').val(b.stok || 0);
            $('#form-min_stok').val(b.min_stok || 5);
            $('#badge-header-harga').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-unlock-fill me-1"></i>Admin Enabled</span>');
            $('#badge-header-paket').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-unlock-fill me-1"></i>Admin Enabled</span>');
            $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
        } else {
            // Kasir saat Edit: Harga dan Paket di-disabled / locked (hanya read-only)
            $('#form-harga_beli').val(formatRupiah(b.harga_beli || 0)).prop('disabled', true).addClass('bg-light text-muted');
            $('#form-harga_jual').val(formatRupiah(b.harga_jual || 0)).prop('disabled', true).addClass('bg-light text-muted');
            $('#form-harga_grosir').val(formatRupiah(b.harga_grosir || 0)).prop('disabled', true).addClass('bg-light text-muted');
            $('#form-qty_paket_a').val(b.qty_paket_a || 0).prop('disabled', true).addClass('bg-light');
            $('#form-hrg_paket_a').val(formatRupiah(b.hrg_paket_a || 0)).prop('disabled', true).addClass('bg-light');
            $('#form-qty_paket_b').val(b.qty_paket_b || 0).prop('disabled', true).addClass('bg-light');
            $('#form-hrg_paket_b').val(formatRupiah(b.hrg_paket_b || 0)).prop('disabled', true).addClass('bg-light');
            $('#form-qty_paket_c').val(b.qty_paket_c || 0).prop('disabled', true).addClass('bg-light');
            $('#form-hrg_paket_c').val(formatRupiah(b.hrg_paket_c || 0)).prop('disabled', true).addClass('bg-light');
            $('#badge-header-harga').html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle small"><i class="bi bi-lock-fill me-1"></i>Harga Terkunci (Hanya Admin)</span>');
            $('#badge-header-paket').html('<span class="badge bg-warning-subtle text-warning border border-warning-subtle small"><i class="bi bi-lock-fill me-1"></i>Hanya Admin (Kasir Read-Only)</span>');
            $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').removeClass('d-none');
        }

        $('#modalTambahBarang').modal('show');
    });

    $('#modalTambahBarang').on('hidden.bs.modal', function () {
        $('#form-barang-id').val('');
        $('#modalBarangHeader').removeClass('bg-warning text-dark').addClass('bg-primary text-white');
        $('#modalBarangHeader .btn-close').addClass('btn-close-white');
        $('#modalBarangTitle').removeClass('text-dark').addClass('text-white').html('<i class="bi bi-box-seam me-2"></i>Tambah Barang Baru');
        $('#modalBarangSubtitle').removeClass('text-dark text-opacity-75').addClass('text-white text-opacity-75').text('Lengkapi data produk, kategori, satuan, serta harga grosir paket');
        $('#form-id_barang').prop('readonly', false);
        $('#formBarang')[0].reset();

        // Saat Tambah Baru: SEMUA field harga beli, harga jual, grosir, dan paket A/B/C di-ENABLE bisa diinput
        $('#form-harga_beli').prop('disabled', false).prop('required', true).removeClass('bg-light text-muted');
        $('#form-harga_jual').prop('disabled', false).prop('required', true).removeClass('bg-light text-muted');
        $('#form-harga_grosir').prop('disabled', false).removeClass('bg-light text-muted');
        $('#form-qty_paket_a, #form-hrg_paket_a, #form-qty_paket_b, #form-hrg_paket_b, #form-qty_paket_c, #form-hrg_paket_c').prop('disabled', false).removeClass('bg-light');
        $('#badge-header-harga').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-pencil-fill me-1"></i>Bisa Diinput</span>');
        $('#badge-header-paket').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-pencil-fill me-1"></i>Bisa Diinput</span>');
        $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
    });

    // Event Klik Hapus Data (Validasi Password Admin untuk Kasir)
    $(document).on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const nama = $(this).data('nama') || 'barang ini';

        if (isAdmin) {
            Swal.fire({
                title: 'Hapus Barang?',
                html: `Yakin ingin menghapus <b>${nama}</b> dari katalog?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDelete(url, null);
                }
            });
        } else {
            // Kasir: Wajib Input Password Admin!
            Swal.fire({
                title: 'Otorisasi Supervisor',
                html: `Akses Kasir memerlukan otorisasi Supervisor untuk menghapus data <b>${nama}</b>.<br><small class="text-muted">Silakan masukkan Password Admin:</small>`,
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
                    return executeDeletePromise(url, adminPassword);
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.value.message || 'Barang berhasil dihapus!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });

    function executeDeletePromise(url, adminPassword) {
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
    }

    function executeDelete(url, adminPassword) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        if (adminPassword) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'admin_password';
            input.value = adminPassword;
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }
</script>
<?= $this->endSection() ?>
