<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Laporan Katalog &amp; Harga Barang
        </h4>
        <p class="text-muted small mb-0">
            Saring data katalog barang berdasarkan kategori, kemasan, atau mode tampilan (Brosur Luar / Pricelist Reseller vs Lengkap Internal Toko).
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php
            $queryFilter = http_build_query([
                'kategori_id' => $kategori_id,
                'satuan'      => $satuan,
                'status'      => $status,
                'mode'        => $mode,
                'q'           => $q
            ]);
        ?>
        <a href="<?= base_url('laporan/barang/cetak?' . $queryFilter) ?>" target="_blank" class="btn btn-danger rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </a>
        <a href="<?= base_url('laporan/barang/excel?' . $queryFilter) ?>" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- FORM FILTER LAPORAN BARANG                     -->
<!-- ============================================== -->
<div class="card-modern p-4 mb-4 border-top border-4 border-primary">
    <form action="<?= base_url('laporan/barang') ?>" method="GET" id="form-filter-barang" class="row g-3 align-items-end">
        
        <!-- Mode Laporan -->
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold text-dark mb-1">
                <i class="bi bi-eye-fill text-primary me-1"></i>Mode Tampilan / Target Laporan:
            </label>
            <select name="mode" id="filter-mode" class="form-select select2 fw-semibold">
                <option value="eksternal" <?= $mode === 'eksternal' ? 'selected' : '' ?>>
                    Brosur Luar / Konsumen / Reseller (Hanya Kode, Nama, Kategori, Satuan, Harga Jual &amp; Grosir)
                </option>
                <option value="internal" <?= $mode === 'internal' ? 'selected' : '' ?>>
                    Lengkap Internal Toko (Semua Field: Modal Beli, Jual, Grosir, Paket A/B/C &amp; Stok)
                </option>
            </select>
            <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                <?= $mode === 'eksternal' ? '<span class="text-success fw-bold"><i class="bi bi-shield-check"></i> Aman dibagikan ke orang luar</span> (Harga Modal Beli & Stok disembunyikan)' : '<span class="text-warning-emphasis fw-bold"><i class="bi bi-lock-fill"></i> Khusus Pengelola</span> (Menampilkan rahasia modal HPP & stok)' ?>
            </small>
        </div>

        <!-- Filter Kategori -->
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted mb-1">Kategori Produk:</label>
            <select name="kategori_id" class="form-select select2">
                <option value="semua">-- Semua Kategori --</option>
                <?php foreach ($kategori_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= (string)$kategori_id === (string)$k['id'] ? 'selected' : '' ?>>
                        <?= esc($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Satuan -->
        <div class="col-6 col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Satuan Kemasan:</label>
            <select name="satuan" class="form-select select2">
                <option value="semua">-- Semua Satuan --</option>
                <?php foreach ($satuan_list as $s): ?>
                    <option value="<?= esc($s['nama_satuan']) ?>" <?= $satuan === $s['nama_satuan'] ? 'selected' : '' ?>>
                        <?= esc($s['nama_satuan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Status -->
        <div class="col-6 col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Status Barang:</label>
            <select name="status" class="form-select select2">
                <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif Saja</option>
                <option value="Nonaktif" <?= $status === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif Saja</option>
                <option value="semua" <?= $status === 'semua' ? 'selected' : '' ?>>Semua Status</option>
            </select>
        </div>

        <!-- Tombol Filter & Reset -->
        <div class="col-12 col-md-1 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" title="Terapkan Filter">
                <i class="bi bi-filter"></i>
            </button>
            <a href="<?= base_url('laporan/barang') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- ============================================== -->
<!-- METRIK RINGKASAN DATA TERFILTER               -->
<!-- ============================================== -->
<?php
    $totalItem = count($barang_list);
    $minHargaJual = 0;
    $maxHargaJual = 0;
    if ($totalItem > 0) {
        $prices = array_column($barang_list, 'harga_jual');
        $minHargaJual = min($prices);
        $maxHargaJual = max($prices);
    }
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Jumlah Produk Terfilter</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success"><?= number_format($totalItem, 0, ',', '.') ?> Item</h3>
                    <small class="text-muted">Diurutkan Abjad Nama Barang (A - Z)</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Target Format Laporan</span>
                    <h4 class="fw-bold mt-1 mb-0 <?= $mode === 'eksternal' ? 'text-primary' : 'text-danger' ?>">
                        <?= $mode === 'eksternal' ? '<i class="bi bi-people-fill me-1"></i> Orang Luar / Reseller' : '<i class="bi bi-shield-lock-fill me-1"></i> Lengkap Toko' ?>
                    </h4>
                    <small class="text-muted"><?= $mode === 'eksternal' ? 'Katalog publik & brosur harga' : 'Internal manajemen & audit stok' ?></small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi <?= $mode === 'eksternal' ? 'bi-tags-fill' : 'bi-lock-fill' ?> fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Rentang Harga Jual Eceran</span>
                    <h4 class="fw-bold mt-1 mb-0 text-warning-emphasis">
                        Rp <?= number_format($minHargaJual, 0, ',', '.') ?> - <?= number_format($maxHargaJual, 0, ',', '.') ?>
                    </h4>
                    <small class="text-muted">Harga terendah s/d tertinggi</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning-emphasis p-3 rounded-circle">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- TABEL PRATINJAU LAPORAN                        -->
<!-- ============================================== -->
<div class="card-modern p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-table text-primary me-2"></i>Pratinjau Tabel Katalog Barang
            </h5>
            <small class="text-muted">Data otomatis tersortir abjad nama produk dari A ke Z</small>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-sort-alpha-down text-primary me-1"></i> Urut: Nama Barang A-Z
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-laporan-barang">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="text-center" style="width: 50px;">No.</th>
                    <th style="width: 120px;">Kode Barang</th>
                    <th>Nama Barang</th>
                    <th style="width: 140px;">Kategori</th>
                    <th class="text-center" style="width: 100px;">Satuan</th>
                    
                    <?php if ($mode === 'internal'): ?>
                        <th class="text-end" style="width: 130px;">Harga Beli (Modal)</th>
                    <?php endif; ?>

                    <th class="text-end" style="width: 130px;">Harga Jual</th>
                    <th class="text-end" style="width: 140px;">Harga Grosir</th>

                    <?php if ($mode === 'internal'): ?>
                        <th style="width: 180px;">Paket Grosir A/B/C</th>
                        <th class="text-center" style="width: 90px;">Stok</th>
                        <th class="text-center" style="width: 90px;">Status</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($barang_list)): ?>
                    <?php $no = 1; foreach ($barang_list as $b): ?>
                        <tr>
                            <!-- Nomor Urut -->
                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>

                            <!-- Kode Barang -->
                            <td>
                                <span class="fw-bold font-monospace text-primary"><?= esc($b['id_barang']) ?></span>
                                <?php if ($mode === 'internal' && !empty($b['barcode'])): ?>
                                    <small class="text-muted d-block font-monospace"><i class="bi bi-upc"></i> <?= esc($b['barcode']) ?></small>
                                <?php endif; ?>
                            </td>

                            <!-- Nama Barang -->
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($b['nama_barang']) ?></div>
                                <?php if (!empty($b['is_jerigen'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small" style="font-size: 0.7rem;">
                                        <i class="bi bi-recycle"></i> Tukar Jerigen 5L
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Kategori -->
                            <td>
                                <span class="badge bg-light text-dark border"><?= esc($b['nama_kategori'] ?? 'Umum') ?></span>
                            </td>

                            <!-- Satuan -->
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary"><?= esc($b['satuan']) ?></span>
                            </td>

                            <!-- Harga Beli Modal (Hanya Internal) -->
                            <?php if ($mode === 'internal'): ?>
                                <td class="text-end text-muted font-monospace">
                                    Rp <?= number_format((float)$b['harga_beli'], 0, ',', '.') ?>
                                </td>
                            <?php endif; ?>

                            <!-- Harga Jual Eceran -->
                            <td class="text-end fw-bold text-primary font-monospace">
                                Rp <?= number_format((float)$b['harga_jual'], 0, ',', '.') ?>
                            </td>

                            <!-- Harga Jual Grosir -->
                            <td class="text-end fw-semibold text-success font-monospace">
                                <?php if ((float)$b['harga_grosir'] > 0): ?>
                                    Rp <?= number_format((float)$b['harga_grosir'], 0, ',', '.') ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Paket Bertingkat & Stok & Status (Hanya Internal) -->
                            <?php if ($mode === 'internal'): ?>
                                <td class="small">
                                    <?php if ((float)$b['hrg_paket_a'] > 0): ?>
                                        <div class="text-primary">&bull; Pkt A (&ge;<?= (int)$b['qty_paket_a'] ?>): Rp <?= number_format((float)$b['hrg_paket_a'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if ((float)$b['hrg_paket_b'] > 0): ?>
                                        <div class="text-success">&bull; Pkt B (&ge;<?= (int)$b['qty_paket_b'] ?>): Rp <?= number_format((float)$b['hrg_paket_b'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if ((float)$b['hrg_paket_c'] > 0): ?>
                                        <div class="text-info">&bull; Pkt C (&ge;<?= (int)$b['qty_paket_c'] ?>): Rp <?= number_format((float)$b['hrg_paket_c'], 0, ',', '.') ?></div>
                                    <?php endif; ?>
                                    <?php if ((float)$b['hrg_paket_a'] <= 0 && (float)$b['hrg_paket_b'] <= 0 && (float)$b['hrg_paket_c'] <= 0): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-monospace fw-bold">
                                    <span class="badge <?= (float)$b['stok'] <= (float)$b['min_stok'] ? 'bg-danger' : 'bg-success' ?> rounded-pill">
                                        <?= (float)$b['stok'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $b['status'] === 'Aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                        <?= esc($b['status']) ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $mode === 'internal' ? '12' : '7' ?>" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ada data barang yang sesuai dengan kriteria filter saat ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-laporan-barang').DataTable({
                language: {
                    search: "Cari di Tabel:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ produk",
                    paginate: { first: "«", last: "»", next: "›", previous: "‹" },
                    emptyTable: "Tidak ada data barang yang tersedia."
                },
                order: [], // Pertahankan urutan abjad default dari backend
                pageLength: 25
            });
        }
    });
</script>
<?= $this->endSection() ?>
