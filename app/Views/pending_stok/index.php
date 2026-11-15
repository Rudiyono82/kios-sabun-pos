<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4 py-3">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 border-bottom pb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-7 fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> SOP Multi-Tier Otorisasi Harga
                </span>
                <span class="badge <?= $is_admin ? 'bg-success text-white' : 'bg-secondary text-white' ?> px-2 py-1 fs-7">
                    <i class="bi <?= $is_admin ? 'bi-person-check-fill' : 'bi-eye-fill' ?> me-1"></i>
                    <?= $is_admin ? 'Akses Admin (Full Approval)' : 'Akses Kasir (Monitoring Status)' ?>
                </span>
            </div>
            <h3 class="fw-bold text-dark mt-2 mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-split text-warning"></i>
                Daftar Data Pending Update Stok &amp; Harga Barang
            </h3>
            <p class="text-muted small mb-0">
                Monitoring antrean harga belanja baru. Harga baru dipromosikan otomatis ke Master Barang saat stok lama habis dan berstatus <strong>"Setuju"</strong> atau <strong>"Setuju dengan Perubahan"</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus"></i> Input Belanja Restock
            </a>
            <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cash-coin"></i> Ke Layar POS
            </a>
        </div>
    </div>

    <!-- Ringkasan Status Metrik Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="<?= base_url('pending-stok?status=belum_setuju') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-warning h-100 hover-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">BELUM SETUJU</div>
                                <div class="fs-3 fw-bold text-warning"><?= number_format($count_belum_setuju) ?></div>
                                <div class="small text-muted">Menunggu Review Admin</div>
                            </div>
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= base_url('pending-stok?status=setuju') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-success h-100 hover-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">SETUJU</div>
                                <div class="fs-3 fw-bold text-success"><?= number_format($count_setuju) ?></div>
                                <div class="small text-muted">Disetujui Sesuai Usulan</div>
                            </div>
                            <div class="bg-success-subtle text-success p-3 rounded-circle">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= base_url('pending-stok?status=setuju_perubahan') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-info h-100 hover-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">SETUJU DGN PERUBAHAN</div>
                                <div class="fs-3 fw-bold text-info"><?= number_format($count_setuju_ubah) ?></div>
                                <div class="small text-muted">Harga Direvisi Admin</div>
                            </div>
                            <div class="bg-info-subtle text-info p-3 rounded-circle">
                                <i class="bi bi-pencil-square fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= base_url('pending-stok?status=selesai') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 border-start border-4 border-secondary h-100 hover-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">SUDAH MASUK MASTER</div>
                                <div class="fs-3 fw-bold text-secondary"><?= number_format($count_selesai) ?></div>
                                <div class="small text-muted">Selesai Ter-update</div>
                            </div>
                            <div class="bg-secondary-subtle text-secondary p-3 rounded-circle">
                                <i class="bi bi-archive-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= base_url('pending-stok') ?>" class="row g-2 align-items-center">
                <div class="col-12 col-md-7 d-flex flex-wrap gap-1">
                    <a href="<?= base_url('pending-stok?status=all' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">
                        Semua Data
                    </a>
                    <a href="<?= base_url('pending-stok?status=aktif_pending' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'aktif_pending' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        Semua Pending Aktif (<?= $count_total_aktif ?>)
                    </a>
                    <a href="<?= base_url('pending-stok?status=belum_setuju' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'belum_setuju' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
                        Belum Setuju (<?= $count_belum_setuju ?>)
                    </a>
                    <a href="<?= base_url('pending-stok?status=setuju' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'setuju' ? 'btn-success' : 'btn-outline-success' ?>">
                        Setuju (<?= $count_setuju ?>)
                    </a>
                    <a href="<?= base_url('pending-stok?status=setuju_perubahan' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'setuju_perubahan' ? 'btn-info text-dark' : 'btn-outline-info text-dark' ?>">
                        Setuju dgn Perubahan (<?= $count_setuju_ubah ?>)
                    </a>
                    <a href="<?= base_url('pending-stok?status=selesai' . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>" 
                       class="btn btn-sm <?= $filter_status === 'selesai' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                        Selesai (<?= $count_selesai ?>)
                    </a>
                </div>
                <div class="col-12 col-md-5">
                    <input type="hidden" name="status" value="<?= esc($filter_status) ?>">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Cari Kode Barang / Nama / No Ref Nota..." value="<?= esc($keyword) ?>">
                        <button class="btn btn-primary" type="submit">Cari</button>
                        <?php if ($keyword): ?>
                            <a href="<?= base_url('pending-stok?status=' . esc($filter_status)) ?>" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-pending-stok">
                    <thead class="table-light border-bottom text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="ps-3 py-3" style="width: 50px;">No</th>
                            <th>Info Belanja &amp; Ref</th>
                            <th>Barang</th>
                            <th class="text-center">Sisa Stok Toko</th>
                            <th class="text-center">Stok Pending</th>
                            <th>Harga Beli (Modal)</th>
                            <th>Harga Jual (Eceran)</th>
                            <th>Paket Grosir</th>
                            <th class="text-center">Status Persetujuan</th>
                            <th class="text-center">Eksekusi Master</th>
                            <th class="text-center pe-3" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($list)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Tidak ada data pending update stok barang yang cocok dengan kriteria filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($list as $row): ?>
                                <?php
                                    $statusPersetujuan = trim($row['status_persetujuan'] ?? 'Belum Setuju');
                                    $isApproved = str_contains($statusPersetujuan, 'Setuju') && ($statusPersetujuan !== 'Belum Setuju');
                                    $isBelumSetuju = ($statusPersetujuan === 'Belum Setuju');
                                    $isSetujuUbah = ($statusPersetujuan === 'Setuju dengan Perubahan');
                                    $isSelesai = ($row['status_eksekusi'] == 0);
                                    $stokToko = (float)($row['stok_master_saat_ini'] ?? 0);
                                ?>
                                <tr class="<?= $isBelumSetuju ? 'table-warning-subtle' : '' ?>">
                                    <td class="ps-3 text-muted small"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark font-monospace small"><?= esc($row['ref_id'] ?: '-') ?></div>
                                        <div class="text-muted fs-8">
                                            <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($row['tgl_input'] ?: $row['created_at'])) ?>
                                        </div>
                                        <div class="text-muted fs-8">
                                            <i class="bi bi-person me-1"></i>Oleh: <strong><?= esc($row['user_input']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary"><?= esc($row['nama_barang']) ?></div>
                                        <div class="small text-muted font-monospace"><?= esc($row['id_barang']) ?></div>
                                        <span class="badge bg-light text-dark border fs-8"><?= esc($row['satuan'] ?? 'Pcs/Ltr') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $stokToko <= 0 ? 'bg-danger text-white' : 'bg-success-subtle text-success border border-success-subtle' ?> px-2 py-1 fs-7">
                                            <?= number_format($stokToko, 0, ',', '.') ?>
                                        </span>
                                        <?php if ($stokToko <= 0 && !$isSelesai): ?>
                                            <div class="text-danger fw-bold fs-8 mt-1">Stok Habis!</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white px-2 py-1 fs-7">
                                            +<?= number_format((float)$row['stok_pending'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted text-decoration-line-through">Lama: Rp <?= number_format((float)$row['harga_beli_lama'], 0, ',', '.') ?></div>
                                        <div class="fw-bold text-danger">Baru: Rp <?= number_format((float)$row['harga_beli'], 0, ',', '.') ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-muted text-decoration-line-through">Lama: Rp <?= number_format((float)$row['harga_jual_lama'], 0, ',', '.') ?></div>
                                        <div class="fw-bold text-success">Baru: Rp <?= number_format((float)$row['harga_jual'], 0, ',', '.') ?></div>
                                    </td>
                                    <td>
                                        <div class="fs-8">
                                            <?php if ($row['qty_paket_a'] > 0): ?>
                                                <span class="badge bg-light text-dark border">Pkt A: ≥<?= $row['qty_paket_a'] ?> @ Rp <?= number_format((float)$row['harga_paket_a'], 0, ',', '.') ?></span><br>
                                            <?php endif; ?>
                                            <?php if ($row['qty_paket_b'] > 0): ?>
                                                <span class="badge bg-light text-dark border">Pkt B: ≥<?= $row['qty_paket_b'] ?> @ Rp <?= number_format((float)$row['harga_paket_b'], 0, ',', '.') ?></span><br>
                                            <?php endif; ?>
                                            <?php if ($row['qty_paket_c'] > 0): ?>
                                                <span class="badge bg-light text-dark border">Pkt C: ≥<?= $row['qty_paket_c'] ?> @ Rp <?= number_format((float)$row['harga_paket_c'], 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                            <?php if ($row['qty_paket_a'] <= 0 && $row['qty_paket_b'] <= 0 && $row['qty_paket_c'] <= 0): ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isBelumSetuju): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 fs-7">
                                                <i class="bi bi-clock-history me-1"></i> Belum Setuju
                                            </span>
                                        <?php elseif ($isSetujuUbah): ?>
                                            <span class="badge bg-info text-dark px-2 py-1 fs-7">
                                                <i class="bi bi-pencil-square me-1"></i> Setuju dgn Perubahan
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white px-2 py-1 fs-7">
                                                <i class="bi bi-check-circle-fill me-1"></i> Setuju
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($row['user_approved']): ?>
                                            <div class="text-muted fs-8 mt-1">
                                                Oleh: <strong><?= esc($row['user_approved']) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isSelesai): ?>
                                            <span class="badge bg-secondary text-white px-2 py-1 fs-7">
                                                <i class="bi bi-check2-all me-1"></i> Sudah Masuk Master
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-danger border border-danger px-2 py-1 fs-8">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Stok Habis
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-3">
                                        <?php if ($is_admin && !$isSelesai): ?>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($isBelumSetuju): ?>
                                                    <button type="button" class="btn btn-success btn-approve-direct" 
                                                            data-id="<?= $row['id'] ?>" 
                                                            data-nama="<?= esc($row['nama_barang']) ?>"
                                                            title="Setujui Langsung (Status: Setuju)">
                                                        <i class="bi bi-check-lg"></i> Setuju
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-primary btn-open-modal-edit"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-kode="<?= esc($row['id_barang']) ?>"
                                                        data-nama="<?= esc($row['nama_barang']) ?>"
                                                        data-stok-pending="<?= $row['stok_pending'] ?>"
                                                        data-hrg-beli-lama="<?= $row['harga_beli_lama'] ?>"
                                                        data-hrg-beli="<?= $row['harga_beli'] ?>"
                                                        data-hrg-jual-lama="<?= $row['harga_jual_lama'] ?>"
                                                        data-hrg-jual="<?= $row['harga_jual'] ?>"
                                                        data-qty-a="<?= $row['qty_paket_a'] ?>"
                                                        data-hrg-a="<?= $row['harga_paket_a'] ?>"
                                                        data-qty-b="<?= $row['qty_paket_b'] ?>"
                                                        data-hrg-b="<?= $row['harga_paket_b'] ?>"
                                                        data-qty-c="<?= $row['qty_paket_c'] ?>"
                                                        data-hrg-c="<?= $row['harga_paket_c'] ?>"
                                                        data-catatan="<?= esc($row['catatan'] ?? '') ?>"
                                                        title="Ubah Angka Harga & Setujui (Status: Setuju dengan Perubahan)">
                                                    <i class="bi bi-pencil"></i> Revisi
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-reject" 
                                                        data-id="<?= $row['id'] ?>" 
                                                        data-nama="<?= esc($row['nama_barang']) ?>"
                                                        title="Batalkan / Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php elseif (!$is_admin && !$isSelesai): ?>
                                            <span class="badge bg-light text-muted border py-1 px-2 fs-8">
                                                <i class="bi bi-eye me-1"></i> Monitoring Kasir
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-success border py-1 px-2 fs-8">
                                                <i class="bi bi-check-all me-1"></i> Selesai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Otorisasi: Ubah & Setujui Perubahan Harga (Admin) -->
<div class="modal fade" id="modalEditPendingHarga" tabindex="-1" aria-labelledby="modalEditPendingHargaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalEditPendingHargaLabel">
                    <i class="bi bi-shield-shaded"></i> Otorisasi &amp; Revisi Harga Baru (Admin)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit-pending-harga">
                <input type="hidden" id="edit-pending-id" name="id">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 small mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Jika Admin mengubah angka harga ini dan menyetujuinya, status persetujuan otomatis menjadi 
                            <span class="badge bg-info text-dark fw-bold">Setuju dengan Perubahan</span>.
                        </div>
                    </div>

                    <!-- Info Produk -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <span class="text-muted small">Nama Produk:</span>
                                <h6 class="fw-bold text-dark mb-0" id="edit-display-nama">-</h6>
                                <span class="font-monospace text-primary small" id="edit-display-kode">-</span>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="text-muted small">Stok Pending yang Masuk:</span>
                                <h6 class="fw-bold text-primary mb-0" id="edit-display-stok-pending">0</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Input Harga Beli & Jual -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                <i class="bi bi-box-arrow-in-down text-danger me-1"></i> Harga Beli Modal Baru (Rp)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" step="any" class="form-control fw-bold text-danger fs-6" id="edit-harga-beli" name="harga_beli" required>
                            </div>
                            <span class="text-muted fs-8">Harga Modal Sebelumnya: <strong id="edit-display-beli-lama">Rp 0</strong></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                <i class="bi bi-tag-fill text-success me-1"></i> Harga Jual Eceran Baru (Rp)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" step="any" class="form-control fw-bold text-success fs-6" id="edit-harga-jual" name="harga_jual" required>
                            </div>
                            <span class="text-muted fs-8">Harga Jual Sebelumnya: <strong id="edit-display-jual-lama">Rp 0</strong></span>
                        </div>
                    </div>

                    <!-- Input Paket Grosir A, B, C -->
                    <div class="border rounded-3 p-3 bg-white mb-3">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="bi bi-boxes text-warning me-1"></i> Penyesuaian Harga Paket Grosir (Tier A/B/C)
                        </h6>
                        <div class="row g-2">
                            <!-- Paket A -->
                            <div class="col-md-4 border-end">
                                <span class="fw-semibold small text-primary d-block mb-1">Paket A (Grosir 1)</span>
                                <div class="mb-1">
                                    <label class="form-label fs-8 text-muted mb-0">Min Qty:</label>
                                    <input type="number" class="form-control form-control-sm" id="edit-qty-a" name="qty_paket_a" min="0">
                                </div>
                                <div>
                                    <label class="form-label fs-8 text-muted mb-0">Harga Satuan (Rp):</label>
                                    <input type="number" step="any" class="form-control form-control-sm" id="edit-hrg-a" name="harga_paket_a" min="0">
                                </div>
                            </div>

                            <!-- Paket B -->
                            <div class="col-md-4 border-end">
                                <span class="fw-semibold small text-primary d-block mb-1">Paket B (Grosir 2)</span>
                                <div class="mb-1">
                                    <label class="form-label fs-8 text-muted mb-0">Min Qty:</label>
                                    <input type="number" class="form-control form-control-sm" id="edit-qty-b" name="qty_paket_b" min="0">
                                </div>
                                <div>
                                    <label class="form-label fs-8 text-muted mb-0">Harga Satuan (Rp):</label>
                                    <input type="number" step="any" class="form-control form-control-sm" id="edit-hrg-b" name="harga_paket_b" min="0">
                                </div>
                            </div>

                            <!-- Paket C -->
                            <div class="col-md-4">
                                <span class="fw-semibold small text-primary d-block mb-1">Paket C (Grosir 3)</span>
                                <div class="mb-1">
                                    <label class="form-label fs-8 text-muted mb-0">Min Qty:</label>
                                    <input type="number" class="form-control form-control-sm" id="edit-qty-c" name="qty_paket_c" min="0">
                                </div>
                                <div>
                                    <label class="form-label fs-8 text-muted mb-0">Harga Satuan (Rp):</label>
                                    <input type="number" step="any" class="form-control form-control-sm" id="edit-hrg-c" name="harga_paket_c" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Admin -->
                    <div>
                        <label class="form-label fw-semibold text-dark small">Catatan Otorisasi Admin:</label>
                        <input type="text" class="form-control form-control-sm" id="edit-catatan" name="catatan_admin" placeholder="Contoh: Disetujui dengan pembulatan harga jual Rp 35.000">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" id="btn-save-edit-approval">
                        <i class="bi bi-check2-circle me-1"></i> Simpan &amp; Setujui Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Admin Setujui Langsung
    $('.btn-approve-direct').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');

        Swal.fire({
            title: 'Setujui Harga Barang?',
            html: `Yakin ingin menyetujui perubahan harga barang <strong>${nama}</strong>?<br><span class="text-success small">Status akan langsung berubah menjadi <strong>"Setuju"</strong> dan siap masuk master saat stok lama habis.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                $('#global-save-loading').css('display', 'flex').hide().fadeIn(150);
                $.ajax({
                    url: '<?= base_url('pending-stok/approve/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(resp) {
                        $('#global-save-loading').fadeOut(150);
                        if (resp.status === 'success') {
                            Swal.fire('Disetujui!', resp.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#global-save-loading').fadeOut(150);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghubungi server.', 'error');
                    }
                });
            }
        });
    });

    // 2. Buka Modal Edit & Setujui
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEditPendingHarga'));

    $('.btn-open-modal-edit').on('click', function() {
        const d = $(this).data();
        $('#edit-pending-id').val(d.id);
        $('#edit-display-nama').text(d.nama);
        $('#edit-display-kode').text(d.kode);
        $('#edit-display-stok-pending').text('+' + parseFloat(d.stokPending).toLocaleString('id-ID'));
        $('#edit-display-beli-lama').text('Rp ' + parseFloat(d.hrgBeliLama).toLocaleString('id-ID'));
        $('#edit-display-jual-lama').text('Rp ' + parseFloat(d.hrgJualLama).toLocaleString('id-ID'));

        $('#edit-harga-beli').val(d.hrgBeli);
        $('#edit-harga-jual').val(d.hrgJual);
        $('#edit-qty-a').val(d.qtyA);
        $('#edit-hrg-a').val(d.hrgA);
        $('#edit-qty-b').val(d.qtyB);
        $('#edit-hrg-b').val(d.hrgB);
        $('#edit-qty-c').val(d.qtyC);
        $('#edit-hrg-c').val(d.hrgC);
        $('#edit-catatan').val(d.catatan);

        modalEdit.show();
    });

    // 3. Simpan Revisi Harga & Setujui (Status: Setuju dengan Perubahan)
    $('#form-edit-pending-harga').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-pending-id').val();
        const formData = $(this).serialize();

        $('#global-save-loading').css('display', 'flex').hide().fadeIn(150);
        $.ajax({
            url: '<?= base_url('pending-stok/approve-with-change/') ?>' + id,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(resp) {
                $('#global-save-loading').fadeOut(150);
                if (resp.status === 'success') {
                    modalEdit.hide();
                    Swal.fire({
                        title: 'Disetujui dengan Perubahan!',
                        text: resp.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Validasi Gagal', resp.message, 'warning');
                }
            },
            error: function(xhr) {
                $('#global-save-loading').fadeOut(150);
                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan perubahan harga.', 'error');
            }
        });
    });

    // 4. Batalkan Antrean Pending
    $('.btn-reject').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Antrean Pending?',
            html: `Yakin ingin membatalkan/menghapus antrean pending harga barang <strong>${nama}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                $('#global-save-loading').css('display', 'flex').hide().fadeIn(150);
                $.ajax({
                    url: '<?= base_url('pending-stok/reject/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(resp) {
                        $('#global-save-loading').fadeOut(150);
                        if (resp.status === 'success') {
                            Swal.fire('Dihapus!', resp.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#global-save-loading').fadeOut(150);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    });
});
</script>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.fs-7 { font-size: 0.82rem; }
.fs-8 { font-size: 0.75rem; }
</style>
<?= $this->endSection() ?>

