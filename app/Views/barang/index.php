<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Katalog Barang &amp; Manajemen Stok</h5>
            <small class="text-muted">Daftar produk sabun, parfum laundry, harga jual eceran &amp; grosir bertingkat</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('laporan/barang') ?>" class="btn btn-outline-primary rounded-pill px-3 fw-medium shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Cetak / Export Laporan
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                <i class="bi bi-plus-lg me-1"></i> <?= $is_admin ? 'Tambah Barang Baru' : 'Ajukan Barang Baru (Pending)' ?>
            </button>
        </div>
    </div>

    <?php if (!empty($pending_count) && $pending_count > 0): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 gap-2">
            <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-25 text-dark rounded-circle p-2 me-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Ada <?= $pending_count ?> Pengajuan Barang Baru Menunggu Persetujuan Admin</h6>
                    <small class="text-muted">Barang berstatus Pending belum bisa dijual di POS atau dibeli sebelum disetujui (Approved) Admin/Owner.</small>
                </div>
            </div>
            <button type="button" id="btn-filter-pending" class="btn btn-warning rounded-pill px-3 fw-bold text-dark shadow-sm">
                <i class="bi bi-funnel me-1"></i> Filter Barang Pending
            </button>
        </div>
    <?php endif; ?>

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
                    <th style="width: 50px;" class="text-center">Foto</th>
                    <th>Kode / Barcode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Beli</th>
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
                        <td class="text-center">
                            <?php $imgDisplay = !empty($b['gambar']) ? $b['gambar'] : (!empty($b['path_file']) ? $b['path_file'] : ''); ?>
                            <?php if (!empty($imgDisplay)): ?>
                                <img src="<?= base_url('uploads/barang/' . $imgDisplay) ?>" alt="<?= esc($b['nama_barang']) ?>" data-zoom-title="<?= esc($b['nama_barang']) ?>" class="rounded border shadow-xs img-zoom-hover" style="width: 36px; height: 36px; object-fit: cover; cursor: zoom-in;" title="Klik untuk perbesar foto (Popup)" onerror="this.parentElement.innerHTML='<div class=\'rounded border bg-light d-flex align-items-center justify-content-center text-muted mx-auto\' style=\'width: 36px; height: 36px; font-size: 0.8rem;\'><i class=\'bi bi-image\'></i></div>';">
                            <?php else: ?>
                                <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted mx-auto" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= esc($b['id_barang']) ?></div>
                            <small class="text-muted"><i class="bi bi-upc"></i> <?= esc($b['barcode'] ?: '-') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= esc($b['nama_barang']) ?></div>
                            <?php 
                                $is5L = preg_match('/5\s*l/i', $b['nama_barang'] ?? '');
                            ?>
                            <?php if (!empty($b['is_jerigen']) && $is5L): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-recycle"></i> Tukar Jerigen 5 L</span>
                            <?php endif; ?>
                            <?php if (!empty($b['kode_supplier_1'])): ?>
                                <div class="mt-1" style="font-size: 0.72rem;">
                                    <span class="text-primary fw-medium"><i class="bi bi-truck me-1"></i><?= esc($b['nama_supplier_1'] ?? $b['kode_supplier_1']) ?></span>
                                    <?php if (!empty($b['kode_supplier_2'])): ?>
                                        <span class="text-muted ms-1">/ <?= esc($b['nama_supplier_2'] ?? $b['kode_supplier_2']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= esc($b['nama_kategori'] ?? 'Umum') ?></span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($b['satuan']) ?></span></td>
                        <td class="text-end text-muted">Rp <?= number_format((float)$b['harga_beli'], 0, ',', '.') ?></td>
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
                            <?php if ($b['status'] === 'Pending'): ?>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-sm">
                                    <i class="bi bi-clock-history me-1"></i>Pending
                                </span>
                            <?php elseif ($b['status'] === 'Aktif'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i><?= esc($b['status']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if ($is_admin && $b['status'] === 'Pending'): ?>
                                <a href="<?= base_url('barang/approve/' . $b['id']) ?>" 
                                   class="btn btn-success fw-bold btn-approve-barang" 
                                   data-nama="<?= esc($b['nama_barang']) ?>"
                                   title="Setujui Barang Ini Sekarang">
                                    <i class="bi bi-check-circle-fill me-1"></i> Approve
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-primary btn-edit-barang" 
                                    data-barang='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>' 
                                    title="<?= $is_admin ? 'Edit Data Barang' : 'Edit Info Produk' ?>">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                                <?php if ($is_admin): ?>
                                <button type="button" class="btn btn-outline-danger btn-hapus" 
                                    data-url="<?= base_url('barang/hapus/' . $b['id']) ?>" 
                                    data-nama="<?= esc($b['nama_barang']) ?>"
                                    title="Hapus Barang">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                <?php endif; ?>
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
            <form action="<?= base_url('barang/simpan') ?>" method="POST" id="formBarang" enctype="multipart/form-data">
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
                            <div class="input-group">
                                <input type="text" name="id_barang" id="form-id_barang" class="form-control text-uppercase fw-bold" value="<?= esc($auto_kode_barang ?? '') ?>" placeholder="B<?= date('Y') ?>..." required>
                                <button class="btn btn-outline-secondary" type="button" id="btn-refresh-kode" title="Generate Ulang Kode Barang Otomatis">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-magic me-1"></i>Format otomatis: B + Tahun (<?= date('Y') ?>) + Urut (3 digit)</small>
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
                            <?php if ($is_admin): ?>
                            <select name="status" id="form-status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Pending">Pending (Menunggu Persetujuan)</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                            <?php else: ?>
                            <input type="text" class="form-control bg-light fw-bold text-warning" id="form-status-display" value="Pending (Menunggu Persetujuan Admin)" readonly>
                            <input type="hidden" name="status" id="form-status" value="Pending">
                            <?php endif; ?>
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

                        <!-- Kode Supplier 1 & 2 (Sesuai VB6) -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">
                                <i class="bi bi-truck text-primary me-1"></i>Kode Supplier 1 (Utama)
                            </label>
                            <select name="kode_supplier_1" id="form-kode_supplier_1" class="form-select">
                                <option value="">-- Tidak Ada / Pilih Supplier 1 --</option>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= esc($sup['kode_supplier']) ?>">
                                            <?= esc($sup['kode_supplier']) ?> | <?= esc($sup['nama_supplier']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">
                                <i class="bi bi-truck text-secondary me-1"></i>Kode Supplier 2 (Alternatif)
                            </label>
                            <select name="kode_supplier_2" id="form-kode_supplier_2" class="form-select">
                                <option value="">-- Tidak Ada / Pilih Supplier 2 --</option>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= esc($sup['kode_supplier']) ?>">
                                            <?= esc($sup['kode_supplier']) ?> | <?= esc($sup['nama_supplier']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

                        <!-- Upload Foto Produk -->
                        <div class="col-12">
                            <div class="card border rounded-3 bg-light p-3">
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="bi bi-image text-primary me-2"></i>Foto / Gambar Produk Kemasan
                                </h6>
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <div id="preview-foto-box" class="rounded border bg-white d-flex align-items-center justify-content-center overflow-hidden position-relative shadow-sm" style="width: 100px; height: 100px;">
                                            <i class="bi bi-image text-muted fs-1" id="preview-foto-icon"></i>
                                            <img id="preview-foto-img" src="" alt="Preview Foto" data-zoom-title="Preview Foto Barang" class="img-fluid d-none img-zoom-hover" style="width: 100%; height: 100%; object-fit: contain; cursor: zoom-in;">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="hidden" name="gambar_lama" id="form-gambar_lama">
                                        <label for="form-foto_barang" class="form-label small fw-semibold text-muted mb-1">Pilih File Foto Baru</label>
                                        <input type="file" name="foto_barang" id="form-foto_barang" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg">
                                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                            Format: <strong>JPG, JPEG, PNG, WEBP</strong> (Maks. 3MB). Foto akan otomatis muncul pada kasir POS, popup lookup, dan kartu stok.
                                        </small>
                                        <div class="form-check mt-2 d-none" id="container-hapus-foto">
                                            <input class="form-check-input" type="checkbox" name="hapus_foto" id="form-hapus_foto" value="1">
                                            <label class="form-check-label small text-danger fw-semibold" for="form-hapus_foto">
                                                <i class="bi bi-trash me-1"></i>Hapus Foto Produk Ini
                                            </label>
                                        </div>
                                    </div>
                                </div>
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
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" id="btn-simpan-barang" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm">
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
    let defaultAutoKode = '<?= esc($auto_kode_barang ?? '') ?>';

    $('#table-barang').DataTable({
        language: {
            search: "Cari Barang:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ barang",
            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
        }
    });

    // Refresh Kode Barang Otomatis
    $('#btn-refresh-kode').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).find('i').addClass('spinner-border spinner-border-sm').removeClass('bi bi-arrow-clockwise');
        $.getJSON('<?= base_url('barang/generate-kode') ?>', function(res) {
            if (res && res.kode) {
                $('#form-id_barang').val(res.kode);
                defaultAutoKode = res.kode;
            }
        }).always(function() {
            $btn.prop('disabled', false).find('i').removeClass('spinner-border spinner-border-sm').addClass('bi bi-arrow-clockwise');
        });
    });

    // Event Klik Edit (Admin & Kasir)
    $(document).on('click', '.btn-edit-barang', function() {
        const b = $(this).data('barang');
        $('#form-barang-id').val(b.id);

        if (isAdmin) {
            $('#modalBarangHeader').removeClass('bg-primary text-white bg-info bg-opacity-10').addClass('bg-warning text-dark');
            $('#modalBarangHeader .btn-close').removeClass('btn-close-white');
            $('#modalBarangTitle').removeClass('text-white').addClass('text-dark').html('<i class="bi bi-pencil-square me-2"></i>Edit Barang: ' + b.nama_barang);
            $('#modalBarangSubtitle').removeClass('text-white text-opacity-75').addClass('text-dark text-opacity-75').text('Perbarui informasi produk, harga jual, dan paket grosir');
            $('#btn-simpan-barang').removeClass('d-none').html('<i class="bi bi-save me-1"></i> Simpan Data Barang');
            $('#modalTambahBarang input, #modalTambahBarang select').prop('disabled', false);

            $('#form-harga_beli, #form-harga_jual, #form-harga_grosir').prop('readonly', false).removeClass('bg-light text-muted');
            $('#form-qty_paket_a, #form-hrg_paket_a, #form-qty_paket_b, #form-hrg_paket_b, #form-qty_paket_c, #form-hrg_paket_c').prop('readonly', false).removeClass('bg-light text-muted');
            $('#form-stok, #form-min_stok').prop('readonly', false).removeClass('bg-light text-muted');
            $('#badge-header-harga').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-unlock-fill me-1"></i>Admin Enabled</span>');
            $('#badge-header-paket').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-unlock-fill me-1"></i>Admin Enabled</span>');
            $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
        } else {
            // Kasir
            $('#modalBarangHeader').removeClass('bg-primary text-white bg-warning text-dark').addClass('bg-primary bg-opacity-10 text-primary');
            $('#modalBarangHeader .btn-close').removeClass('btn-close-white');
            
            // Aktifkan input informasi umum
            $('#modalTambahBarang input, #modalTambahBarang select').prop('disabled', false);
            $('#form-barcode, #form-nama_barang, #form-kategori_id, #form-satuan, #form-kode_supplier_1, #form-kode_supplier_2, #form-is_jerigen, #form-foto_barang, #form-hapus_foto').prop('readonly', false);

            if (b.status === 'Pending') {
                // Barang Pending: Kasir masih berhak mengubah usulan harga dan paket grosir
                $('#modalBarangTitle').removeClass('text-white text-dark').addClass('text-primary').html('<i class="bi bi-pencil-square me-2"></i>Edit Pengajuan Barang: ' + b.nama_barang);
                $('#modalBarangSubtitle').removeClass('text-white text-opacity-75 text-dark').addClass('text-muted').html('<span class="badge bg-warning text-dark me-1"><i class="bi bi-clock-history"></i> Status Pending</span> Anda masih dapat mengubah informasi produk, harga beli, jual, dan paket grosir sebelum disetujui Admin.');
                $('#btn-simpan-barang').removeClass('d-none').html('<i class="bi bi-save me-1"></i> Simpan Perubahan Pengajuan');

                $('#form-harga_beli, #form-harga_jual, #form-harga_grosir').prop('readonly', false).removeClass('bg-light text-muted');
                $('#form-qty_paket_a, #form-hrg_paket_a, #form-qty_paket_b, #form-hrg_paket_b, #form-qty_paket_c, #form-hrg_paket_c').prop('readonly', false).removeClass('bg-light text-muted');
                $('#form-stok, #form-min_stok').prop('readonly', true).addClass('bg-light text-muted');

                $('#badge-header-harga').html('<span class="badge bg-info-subtle text-info border border-info-subtle small"><i class="bi bi-pencil-fill me-1"></i>Usulan Harga Kasir</span>');
                $('#badge-header-paket').html('<span class="badge bg-info-subtle text-info border border-info-subtle small"><i class="bi bi-pencil-fill me-1"></i>Usulan Paket Grosir</span>');
                $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
            } else {
                // Barang Aktif: Kunci harga dan stok demi integritas finansial
                $('#modalBarangTitle').removeClass('text-white text-dark').addClass('text-primary').html('<i class="bi bi-pencil-square me-2"></i>Edit Info Produk: ' + b.nama_barang);
                $('#modalBarangSubtitle').removeClass('text-white text-opacity-75 text-dark').addClass('text-muted').html('<span class="badge bg-warning text-dark me-1"><i class="bi bi-shield-lock-fill"></i> Akses Kasir</span> Anda dapat mengubah Nama, Barcode, Kategori, Satuan, Supplier & Foto. <strong>Harga & Stok dikunci demi keamanan.</strong>');
                $('#btn-simpan-barang').removeClass('d-none').html('<i class="bi bi-save me-1"></i> Simpan Info Produk');

                $('#form-harga_beli, #form-harga_jual, #form-harga_grosir').prop('readonly', true).addClass('bg-light text-muted');
                $('#form-qty_paket_a, #form-hrg_paket_a, #form-qty_paket_b, #form-hrg_paket_b, #form-qty_paket_c, #form-hrg_paket_c').prop('readonly', true).addClass('bg-light text-muted');
                $('#form-stok, #form-min_stok').prop('readonly', true).addClass('bg-light text-muted');
                
                $('#badge-header-harga').html('<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small"><i class="bi bi-lock-fill me-1"></i>Harga Terkunci (Hanya Admin)</span>');
                $('#badge-header-paket').html('<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small"><i class="bi bi-lock-fill me-1"></i>Paket Grosir Terkunci (Hanya Admin)</span>');
                $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').removeClass('d-none');
            }
        }
        
        $('#form-id_barang').val(b.id_barang).prop('readonly', true);
        $('#form-barcode').val(b.barcode || '');
        $('#form-nama_barang').val(b.nama_barang || '');
        $('#form-kategori_id').val(b.kategori_id || '').trigger('change');
        $('#form-satuan').val(b.satuan || '').trigger('change');
        $('#form-kode_supplier_1').val(b.kode_supplier_1 || '').trigger('change');
        $('#form-kode_supplier_2').val(b.kode_supplier_2 || '').trigger('change');
        $('#form-status').val(b.status || 'Aktif').trigger('change');
        $('#form-is_jerigen').prop('checked', b.is_jerigen == 1);

        $('#form-harga_beli').val(formatRupiah(b.harga_beli || 0));
        $('#form-harga_jual').val(formatRupiah(b.harga_jual || 0));
        $('#form-harga_grosir').val(formatRupiah(b.harga_grosir || 0));
        $('#form-qty_paket_a').val(b.qty_paket_a || 0);
        $('#form-hrg_paket_a').val(formatRupiah(b.hrg_paket_a || 0));
        $('#form-qty_paket_b').val(b.qty_paket_b || 0);
        $('#form-hrg_paket_b').val(formatRupiah(b.hrg_paket_b || 0));
        $('#form-qty_paket_c').val(b.qty_paket_c || 0);
        $('#form-hrg_paket_c').val(formatRupiah(b.hrg_paket_c || 0));
        $('#form-stok').val(b.stok || 0);
        $('#form-min_stok').val(b.min_stok || 5);

        $('#modalTambahBarang').modal('show');

        // Setup Foto Produk
        $('#form-foto_barang').val('');
        $('#form-hapus_foto').prop('checked', false);
        const fotoToShow = b.gambar || b.path_file;
        if (fotoToShow) {
            $('#form-gambar_lama').val(fotoToShow);
            $('#preview-foto-img').attr('src', '<?= base_url('uploads/barang/') ?>/' + encodeURIComponent(fotoToShow)).removeClass('d-none');
            $('#preview-foto-icon').addClass('d-none');
            $('#container-hapus-foto').removeClass('d-none');
        } else {
            $('#form-gambar_lama').val('');
            $('#preview-foto-img').attr('src', '').addClass('d-none');
            $('#preview-foto-icon').removeClass('d-none');
            $('#container-hapus-foto').addClass('d-none');
        }

        // Pastikan seluruh input currency terformat dengan ribuan titik
        setTimeout(() => {
            $('#modalTambahBarang .input-currency').each(function() {
                $(this).val(formatRupiah($(this).val()));
            });
        }, 150);
    });

    $('#modalTambahBarang').on('hidden.bs.modal', function () {
        $('#form-barang-id').val('');
        $('#modalBarangHeader').removeClass('bg-warning bg-info bg-opacity-10 text-dark').addClass('bg-primary text-white');
        $('#modalBarangHeader .btn-close').addClass('btn-close-white');
        
        if (isAdmin) {
            $('#modalBarangTitle').removeClass('text-dark text-primary').addClass('text-white').html('<i class="bi bi-box-seam me-2"></i>Tambah Barang Baru');
            $('#modalBarangSubtitle').removeClass('text-dark text-opacity-75').addClass('text-white text-opacity-75').text('Lengkapi data produk, kategori, satuan, serta harga grosir paket');
            $('#btn-simpan-barang').removeClass('d-none').html('<i class="bi bi-save me-1"></i> Simpan Data Barang');
            $('#form-status').val('Aktif').trigger('change');
            $('#form-stok, #form-min_stok').prop('readonly', false).removeClass('bg-light text-muted');
        } else {
            $('#modalBarangTitle').removeClass('text-dark text-primary').addClass('text-white').html('<i class="bi bi-box-seam me-2"></i>Ajukan Barang Baru (Pending Approval)');
            $('#modalBarangSubtitle').removeClass('text-dark text-opacity-75').addClass('text-white text-opacity-75').text('Barang akan diajukan dengan status PENDING dan harus disetujui Admin sebelum aktif di POS.');
            $('#btn-simpan-barang').removeClass('d-none').html('<i class="bi bi-send me-1"></i> Ajukan Barang Baru');
            $('#form-status').val('Pending');
            $('#form-status-display').val('Pending (Menunggu Persetujuan Admin)');
            $('#form-stok').val('0').prop('readonly', true).addClass('bg-light text-muted');
        }

        $('#form-id_barang').val(defaultAutoKode).prop('readonly', false);
        $('#formBarang')[0].reset();
        $('#form-id_barang').val(defaultAutoKode);
        $('#form-foto_barang').val('');
        $('#form-gambar_lama').val('');
        $('#preview-foto-img').attr('src', '').addClass('d-none');
        $('#preview-foto-icon').removeClass('d-none');
        $('#form-hapus_foto').prop('checked', false);
        $('#container-hapus-foto').addClass('d-none');
        $('#form-kategori_id').val('').trigger('change');
        $('#form-satuan').val('').trigger('change');
        $('#form-kode_supplier_1').val('').trigger('change');
        $('#form-kode_supplier_2').val('').trigger('change');
        $('#form-harga_beli, #form-harga_jual, #form-harga_grosir, #form-hrg_paket_a, #form-hrg_paket_b, #form-hrg_paket_c').val('0');

        // Saat Tambah Baru: field harga bisa diisi
        $('#form-harga_beli').prop('disabled', false).prop('required', isAdmin).removeClass('bg-light text-muted');
        $('#form-harga_jual').prop('disabled', false).prop('required', false).removeClass('bg-light text-muted');
        $('#form-harga_grosir').prop('disabled', false).removeClass('bg-light text-muted');
        $('#form-qty_paket_a, #form-hrg_paket_a, #form-qty_paket_b, #form-hrg_paket_b, #form-qty_paket_c, #form-hrg_paket_c').prop('disabled', false).removeClass('bg-light text-muted');
        
        if (isAdmin) {
            $('#badge-header-harga').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-pencil-fill me-1"></i>Bisa Diinput</span>');
            $('#badge-header-paket').html('<span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-pencil-fill me-1"></i>Bisa Diinput</span>');
            $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
        } else {
            $('#badge-header-harga').html('<span class="badge bg-info-subtle text-info border border-info-subtle small"><i class="bi bi-pencil-fill me-1"></i>Usulan Harga Kasir</span>');
            $('#badge-header-paket').html('<span class="badge bg-info-subtle text-info border border-info-subtle small"><i class="bi bi-pencil-fill me-1"></i>Usulan Paket Grosir</span>');
            $('#badge-lock-beli, #badge-lock-jual, #badge-lock-grosir').addClass('d-none');
        }
    });

    // Tombol Filter Barang Pending
    $('#btn-filter-pending').on('click', function() {
        $('#table-barang').DataTable().search('Pending').draw();
    });

    // Konfirmasi Approval Barang Baru oleh Admin
    $(document).on('click', '.btn-approve-barang', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const nama = $(this).data('nama');
        Swal.fire({
            title: 'Setujui Barang Baru?',
            html: `Apakah Anda yakin ingin menyetujui barang <strong>${nama}</strong>?<br><br><small class="text-muted">Setelah disetujui, status menjadi <strong>Aktif</strong> dan barang langsung bisa dijual di POS serta dipesan di menu Pembelian.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Setujui Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // Live Preview Upload Foto Produk Baru
    $('#form-foto_barang').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-foto-img').attr('src', e.target.result).removeClass('d-none');
                $('#preview-foto-icon').addClass('d-none');
            };
            reader.readAsDataURL(file);
            $('#form-hapus_foto').prop('checked', false);
        }
    });

    // Checkbox Hapus Foto
    $('#form-hapus_foto').on('change', function() {
        if ($(this).is(':checked')) {
            $('#preview-foto-img').addClass('d-none');
            $('#preview-foto-icon').removeClass('d-none');
            $('#form-foto_barang').val('');
        } else {
            const oldImg = $('#form-gambar_lama').val();
            if (oldImg) {
                $('#preview-foto-img').attr('src', '<?= base_url('uploads/barang/') ?>/' + encodeURIComponent(oldImg)).removeClass('d-none');
                $('#preview-foto-icon').addClass('d-none');
            }
        }
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
