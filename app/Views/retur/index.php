<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-arrow-left-right text-danger me-2"></i>Retur Barang ke Supplier</h4>
        <p class="text-muted small mb-0">Kelola siklus retur barang rijek/expired dari status <strong>Baru Proses</strong> (menunggu supplier) hingga <strong>Selesai</strong> (diganti barang baru/lain/uang) dengan sinkronisasi stok otomatis.</p>
    </div>
    <div>
        <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalRetur">
            <i class="bi bi-plus-circle me-1"></i> Input Retur Baru
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

<!-- Baris Metrik Status Retur -->
<?php
    $totalRetur = count($retur_list);
    $totalProses = 0;
    $totalSelesai = 0;
    foreach ($retur_list as $r) {
        if ($r['status'] === 'Baru Proses') $totalProses++;
        else $totalSelesai++;
    }
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Dokumen Retur</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary"><?= $totalRetur ?></h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-file-earmark-text fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Status: Baru Proses</span>
                    <h3 class="fw-bold mt-1 mb-0 text-warning"><?= $totalProses ?></h3>
                    <small class="text-muted">Menunggu penggantian dari supplier</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Status: Selesai</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success"><?= $totalSelesai ?></h3>
                    <small class="text-muted">Penggantian barang / uang tuntas</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Riwayat & Status Retur -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-retur">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>No Retur</th>
                    <th>Tgl Retur</th>
                    <th>Supplier</th>
                    <th>Barang Diretur (Rijek)</th>
                    <th class="text-center">Qty Retur</th>
                    <th>Alasan</th>
                    <th>Status Retur</th>
                    <th>Bentuk Penggantian</th>
                    <th class="text-center">Aksi / Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($retur_list)): ?>
                    <?php foreach ($retur_list as $r): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-primary"><?= esc($r['no_retur']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td class="fw-medium"><?= esc($r['nama_supplier']) ?></td>
                            <td>
                                <div class="fw-semibold text-danger"><?= esc($r['nama_barang_retur']) ?></div>
                                <small class="text-muted">Kode: <?= esc($r['id_barang_retur']) ?></small>
                            </td>
                            <td class="text-center fw-bold text-danger fs-6"><?= number_format($r['qty_retur'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <?= esc($r['alasan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'Baru Proses'): ?>
                                    <span class="badge bg-warning text-dark py-2 px-3 rounded-pill fw-bold">
                                        <i class="bi bi-hourglass-split me-1"></i> Baru Proses
                                    </span>
                                    <small class="d-block text-muted mt-1" style="font-size: 0.72rem;">Menunggu barang pengganti</small>
                                <?php else: ?>
                                    <span class="badge bg-success py-2 px-3 rounded-pill fw-bold">
                                        <i class="bi bi-check2-circle me-1"></i> Selesai
                                    </span>
                                    <?php if (!empty($r['tgl_selesai'])): ?>
                                        <small class="d-block text-muted mt-1" style="font-size: 0.72rem;"><?= date('d/m/Y', strtotime($r['tgl_selesai'])) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'Baru Proses'): ?>
                                    <span class="text-muted small fst-italic"><i class="bi bi-clock me-1"></i>Belum diganti supplier</span>
                                <?php else: ?>
                                    <?php if ($r['tipe_penyelesaian'] === 'Ganti_Barang_Sama'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle d-block mb-1">
                                            <i class="bi bi-arrow-repeat me-1"></i> Barang Sama (Baru)
                                        </span>
                                        <small class="text-dark fw-semibold"><?= esc($r['nama_barang_ganti']) ?> (Qty: <?= number_format($r['qty_ganti'], 0, ',', '.') ?>)</small>
                                    <?php elseif ($r['tipe_penyelesaian'] === 'Ganti_Barang_Lain'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-block mb-1">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Barang Lain
                                        </span>
                                        <small class="text-dark fw-semibold"><?= esc($r['nama_barang_ganti']) ?> (Qty: <?= number_format($r['qty_ganti'], 0, ',', '.') ?>)</small>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-block mb-1">
                                            <i class="bi bi-cash me-1"></i> Penggantian Uang Kas
                                        </span>
                                        <small class="text-success fw-bold">Rp <?= number_format($r['nilai_ganti'] ?? 0, 0, ',', '.') ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 btn-detail-retur" data-id="<?= $r['id'] ?>" title="Lihat Rincian Retur">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    
                                    <?php if ($r['status'] === 'Baru Proses'): ?>
                                        <!-- Tombol Selesaikan Retur (Jika supplier baru mengantar pengganti beberapa hari kemudian) -->
                                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-2 fw-bold text-dark btn-selesaikan-retur shadow-sm"
                                            data-id="<?= $r['id'] ?>"
                                            data-noretur="<?= esc($r['no_retur']) ?>"
                                            data-supplier="<?= esc($r['nama_supplier']) ?>"
                                            data-barang="<?= esc($r['nama_barang_retur']) ?>"
                                            data-qty="<?= $r['qty_retur'] ?>"
                                            data-nilai="<?= $r['nilai_retur'] ?>"
                                            title="Terima Penggantian dari Supplier">
                                            <i class="bi bi-box-arrow-in-down-right me-1"></i> Terima Ganti
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 rounded-pill small">
                                            <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                        </span>
                                    <?php endif; ?>

                                    <!-- Tombol Hapus Retur (Wajib Password Super Admin) -->
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2 btn-hapus-retur"
                                        data-id="<?= $r['id'] ?>"
                                        data-noretur="<?= esc($r['no_retur']) ?>"
                                        title="Hapus Retur (Wajib Password Super Admin)">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. MODAL INPUT RETUR BARANG BARU KE SUPPLIER   -->
<!-- ============================================== -->
<div class="modal fade" id="modalRetur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Input Retur Barang ke Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('retur/simpan') ?>" method="POST" id="form-retur">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- No Retur & Supplier -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">No. Bukti Retur</label>
                            <input type="text" class="form-control bg-light font-monospace fw-bold" value="<?= esc($no_retur) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pilih Supplier Tujuan <span class="text-danger">*</span></label>
                            <select name="kode_supplier" class="form-select select2" required>
                                <option value="">-- Pilih Supplier --</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= esc($s['kode_supplier']) ?>"><?= esc($s['nama_supplier']) ?> (<?= esc($s['kode_supplier']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Barang yang Diretur & Qty -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Barang Rijek / Rusak yang Diretur <span class="text-danger">*</span></label>
                            <select name="id_barang_retur" id="select-barang-retur" class="form-select select2" required>
                                <option value="">-- Cari Nama / Kode Barang --</option>
                                <?php 
                                $isAdmin = (session()->get('role') === 'admin');
                                foreach ($barang_list as $b): 
                                    $stokReturLabel = $isAdmin ? ' [Stok: ' . $b['stok'] . ' ' . esc($b['satuan']) . ']' : '';
                                ?>
                                    <option value="<?= esc($b['id_barang']) ?>" <?= $isAdmin ? 'data-stok="' . $b['stok'] . '"' : '' ?> data-satuan="<?= esc($b['satuan']) ?>" data-harga="<?= $b['harga_beli'] ?>">
                                        <?= esc($b['nama_barang']) ?><?= $stokReturLabel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-danger d-block mt-1 fw-semibold"><i class="bi bi-info-circle me-1"></i>Stok barang yang diretur ini akan langsung dikeluarkan dari toko hari ini.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Jumlah (Qty) Retur <span class="text-danger">*</span></label>
                            <input type="number" name="qty_retur" id="input-qty-retur" class="form-control text-center fw-bold fs-5" min="1" step="any" value="1" required>
                        </div>

                        <!-- Alasan Retur -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Alasan Retur <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="alasan" id="alasan1" value="Rijek / Bocor" checked>
                                    <label class="form-check-label" for="alasan1">Rijek / Jerigen Bocor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="alasan" id="alasan2" value="Expired / Kadaluarsa">
                                    <label class="form-check-label" for="alasan2">Expired / Kadaluarsa</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="alasan" id="alasan3" value="Cacat Kemasan / Segel Rusak">
                                    <label class="form-check-label" for="alasan3">Cacat Kemasan / Segel Rusak</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="alasan" id="alasan4" value="Kualitas Tidak Sesuai">
                                    <label class="form-check-label" for="alasan4">Kualitas / Aroma Berubah</label>
                                </div>
                            </div>
                        </div>

                        <!-- Status Awal Retur -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <label class="form-label small fw-bold text-dark mb-2">Status Penanganan Retur Saat Ini: <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check border bg-white p-3 rounded">
                                            <input class="form-check-input" type="radio" name="status" id="status-baru-proses" value="Baru Proses" checked>
                                            <label class="form-check-label fw-bold text-dark" for="status-baru-proses">
                                                <i class="bi bi-hourglass-split text-warning me-1"></i> Baru Proses (Direkomendasikan)
                                            </label>
                                            <small class="text-muted d-block mt-1">Barang rijek diambil sales hari ini, penggantian baru dikirim beberapa hari kemudian.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check border bg-white p-3 rounded">
                                            <input class="form-check-input" type="radio" name="status" id="status-selesai" value="Selesai">
                                            <label class="form-check-label fw-bold text-dark" for="status-selesai">
                                                <i class="bi bi-check2-circle text-success me-1"></i> Langsung Selesai Hari Ini
                                            </label>
                                            <small class="text-muted d-block mt-1">Supplier langsung membawa barang pengganti baru / uang ganti saat itu juga.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box Opsi Penggantian Jika Langsung Selesai Hari Ini -->
                                <div id="box-opsi-langsung" class="d-none mt-3 pt-3 border-top">
                                    <label class="form-label small fw-bold text-dark mb-2">Bentuk Penggantian yang Diterima:</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="form-check border bg-white p-2 px-3 rounded">
                                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="solusi1" value="Ganti_Barang_Sama" checked>
                                                <label class="form-check-label fw-semibold small" for="solusi1">Ganti Barang Sama (Baru)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check border bg-white p-2 px-3 rounded">
                                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="solusi2" value="Ganti_Barang_Lain">
                                                <label class="form-check-label fw-semibold small" for="solusi2">Ganti Barang Lain (B)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check border bg-white p-2 px-3 rounded">
                                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="solusi3" value="Ganti_Uang_Kas">
                                                <label class="form-check-label fw-semibold small" for="solusi3">Penggantian Nilai Uang</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input Tambahan jika Ganti Barang Lain -->
                                    <div id="sub-barang-lain" class="row g-2 mt-2 d-none">
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Pilih Barang Pengganti (Barang B)</label>
                                            <select name="id_barang_ganti" id="select-barang-ganti" class="form-select select2">
                                                <option value="">-- Pilih Produk Pengganti --</option>
                                                <?php foreach ($barang_list as $b): ?>
                                                    <option value="<?= esc($b['id_barang']) ?>">
                                                        <?= esc($b['nama_barang']) ?> [Kode: <?= $b['id_barang'] ?>]
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Qty Barang Pengganti</label>
                                            <input type="number" name="qty_ganti" id="input-qty-ganti" class="form-control text-center" min="1" step="any" value="1">
                                        </div>
                                    </div>

                                    <!-- Input Tambahan jika Ganti Nilai Uang -->
                                    <div id="sub-nilai-uang" class="row g-2 mt-2 d-none">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">Nominal Uang Penggantian (Rp)</label>
                                            <input type="text" inputmode="numeric" name="nilai_ganti" id="input-nilai-ganti" class="form-control input-currency" placeholder="0">
                                            <small class="text-muted">Uang ini akan otomatis dicatat sebagai kas masuk di Buku Kas Toko.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Catatan / Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Barang dibawa oleh sales Bp. Dani, dijanjikan pengganti dikirim hari Selasa depan..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Data Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. MODAL TERIMA PENGGANTIAN / SELESAIKAN RETUR (Setelah Beberapa Hari)     -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalSelesaikanRetur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2"></i>Penyelesaian Penggantian Retur Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('retur/selesaikan') ?>" method="POST" id="form-selesaikan-retur">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="selesaikan-id">

                <div class="modal-body p-4">
                    <!-- Info Ringkas Retur yang Sedang Diproses -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">No. Bukti Retur:</small>
                                <span class="fw-bold text-primary font-monospace" id="selesaikan-label-noretur">-</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">Supplier:</small>
                                <span class="fw-bold text-dark" id="selesaikan-label-supplier">-</span>
                            </div>
                            <div class="col-12 col-md-4">
                                <small class="text-muted d-block">Barang Rijek yang Diretur:</small>
                                <span class="fw-bold text-danger" id="selesaikan-label-barang">-</span>
                            </div>
                            <div class="col-12 col-md-2 text-md-end">
                                <small class="text-muted d-block">Qty Diretur:</small>
                                <span class="fw-bold text-danger fs-5" id="selesaikan-label-qty">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Realisasi Penggantian dari Supplier -->
                    <label class="form-label small fw-bold text-dark mb-2">Pilih Bentuk Penggantian yang Diantar Supplier: <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi1" value="Ganti_Barang_Sama" checked>
                                <label class="form-check-label fw-bold text-success" for="modal2-solusi1">
                                    <i class="bi bi-arrow-repeat me-1"></i> Diganti Barang Sama (Baru)
                                </label>
                                <small class="text-muted d-block mt-1">Supplier mengantar barang baru dari produk yang sama.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi2" value="Ganti_Barang_Lain">
                                <label class="form-check-label fw-bold text-primary" for="modal2-solusi2">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Diganti Barang Lain (B)
                                </label>
                                <small class="text-muted d-block mt-1">Diganti dengan jenis produk lain yang senilai.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi3" value="Ganti_Uang_Kas">
                                <label class="form-check-label fw-bold text-warning-emphasis" for="modal2-solusi3">
                                    <i class="bi bi-cash-stack me-1"></i> Diganti Nilai Uang (Cash)
                                </label>
                                <small class="text-muted d-block mt-1">Supplier mengganti dengan uang tunai / potong nota tagihan.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Input Sesuai Pilihan -->
                    <!-- 1. Jika Barang Sama -->
                    <div id="modal2-box-barang-sama" class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark">Jumlah (Qty) Barang Baru Diterima</label>
                        <input type="number" name="qty_ganti" id="modal2-qty-sama" class="form-control text-center fw-bold fs-5" min="1" step="any" value="1">
                        <small class="text-success mt-1 d-block"><i class="bi bi-check-circle me-1"></i>Stok barang baru akan otomatis ditambahkan ke stok toko.</small>
                    </div>

                    <!-- 2. Jika Barang Lain -->
                    <div id="modal2-box-barang-lain" class="p-3 bg-light rounded-3 border mb-3 d-none">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark">Pilih Barang Pengganti (Barang B)</label>
                                <select name="id_barang_ganti" id="modal2-select-barang-ganti" class="form-select select2">
                                    <option value="">-- Pilih Produk Pengganti --</option>
                                    <?php foreach ($barang_list as $b): ?>
                                        <option value="<?= esc($b['id_barang']) ?>">
                                            <?= esc($b['nama_barang']) ?> [Kode: <?= $b['id_barang'] ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Qty Barang Pengganti</label>
                                <input type="number" name="qty_ganti" id="modal2-qty-lain" class="form-control text-center fw-bold fs-5" min="1" step="any" value="1" disabled>
                            </div>
                        </div>
                        <small class="text-primary mt-1 d-block"><i class="bi bi-check-circle me-1"></i>Stok barang pengganti di atas akan otomatis bertambah ke inventori toko.</small>
                    </div>

                    <!-- 3. Jika Nilai Uang -->
                    <div id="modal2-box-uang" class="p-3 bg-light rounded-3 border mb-3 d-none">
                        <label class="form-label small fw-bold text-dark">Nominal Penggantian Uang dari Supplier (Rp)</label>
                        <input type="text" inputmode="numeric" name="nilai_ganti" id="modal2-input-nilai-uang" class="form-control fw-bold fs-4 text-success input-currency" placeholder="0" disabled>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-wallet2 me-1"></i>Uang ini otomatis masuk ke Buku Kas Pemasukan Toko.</small>
                    </div>

                    <!-- Catatan Penyelesaian -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Catatan Penyelesaian</label>
                        <textarea name="catatan_penyelesaian" class="form-control" rows="2" placeholder="Contoh: Pengganti diantar oleh sopir supplier Bp. Rian, kondisi tersegel bagus dan lengkap..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Selesai & Perbarui Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL DATA RETUR -->
<div class="modal fade" id="modalDetailRetur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Rincian Dokumen Retur: <span id="dtl-retur-noretur"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Tanggal Pengajuan:</span>
                        <strong class="text-dark small" id="dtl-retur-tgl"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Mitra Supplier:</span>
                        <strong class="text-primary small" id="dtl-retur-supplier"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Status Siklus:</span>
                        <span id="dtl-retur-status-badge"></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Petugas Pengaju:</span>
                        <strong class="text-dark small" id="dtl-retur-user"></strong>
                    </div>
                </div>

                <div class="card p-3 mb-3 border-danger bg-danger bg-opacity-10">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-octagon-fill me-2"></i>Barang Rijek / Rusak yang Diretur:</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark fs-6" id="dtl-retur-brg-nama"></div>
                            <small class="text-muted font-monospace" id="dtl-retur-brg-kode"></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger fs-6 py-2 px-3" id="dtl-retur-qty"></span>
                            <small class="d-block text-muted mt-1" id="dtl-retur-alasan"></small>
                        </div>
                    </div>
                </div>

                <div class="card p-3 mb-3 border-success bg-success bg-opacity-10" id="dtl-box-penggantian">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle-fill me-2"></i>Realisasi Penggantian dari Supplier:</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-success mb-1" id="dtl-tipe-ganti"></span>
                            <div class="fw-bold text-dark" id="dtl-nama-ganti"></div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold text-success mb-0" id="dtl-nilai-ganti"></h5>
                            <small class="text-muted" id="dtl-tgl-selesai"></small>
                        </div>
                    </div>
                    <div class="small text-muted mt-2 border-top pt-2" id="dtl-catatan-selesai"></div>
                </div>

                <div class="p-2 bg-light rounded small text-muted">
                    <strong>Catatan Awal:</strong> <span id="dtl-retur-keterangan">-</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-retur').DataTable({
                language: {
                    search: "Cari Data Retur:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data retur",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" }
                },
                order: [[1, 'desc']]
            });
        }

        // 1. Toggle Status Awal di Modal Input Baru
        $('input[name="status"]').on('change', function() {
            if ($(this).val() === 'Selesai') {
                $('#box-opsi-langsung').removeClass('d-none');
            } else {
                $('#box-opsi-langsung').addClass('d-none');
            }
        });

        // Toggle Bentuk Penggantian di Modal Input Baru
        $('input[name="tipe_penyelesaian"]').on('change', function() {
            const val = $(this).val();
            if (val === 'Ganti_Barang_Lain') {
                $('#sub-barang-lain').removeClass('d-none');
                $('#sub-nilai-uang').addClass('d-none');
            } else if (val === 'Ganti_Uang_Kas') {
                $('#sub-nilai-uang').removeClass('d-none');
                $('#sub-barang-lain').addClass('d-none');
            } else {
                $('#sub-barang-lain').addClass('d-none');
                $('#sub-nilai-uang').addClass('d-none');
            }
        });

        $('#input-qty-retur').on('input', function() {
            const qty = $(this).val();
            $('#input-qty-ganti').val(qty);
            
            const harga = $('#select-barang-retur option:selected').data('harga') || 0;
            $('#input-nilai-ganti').val(qty * harga);
        });

        $('#select-barang-retur').on('change', function() {
            const harga = $(this).find('option:selected').data('harga') || 0;
            const qty = $('#input-qty-retur').val() || 1;
            $('#input-nilai-ganti').val(qty * harga);
        });

        // 2. Event Tombol "Terima Penggantian / Selesaikan Retur" (Setelah beberapa hari)
        $(document).on('click', '.btn-selesaikan-retur', function() {
            const id = $(this).data('id');
            const noRetur = $(this).data('noretur');
            const supplier = $(this).data('supplier');
            const barang = $(this).data('barang');
            const qty = $(this).data('qty');
            const nilai = $(this).data('nilai');

            $('#selesaikan-id').val(id);
            $('#selesaikan-label-noretur').text(noRetur);
            $('#selesaikan-label-supplier').text(supplier);
            $('#selesaikan-label-barang').text(barang);
            $('#selesaikan-label-qty').text(qty);

            $('#modal2-qty-sama').val(qty);
            $('#modal2-qty-lain').val(qty);
            $('#modal2-input-nilai-uang').val(nilai);

            // Reset ke pilihan pertama (Ganti Barang Sama)
            $('#modal2-solusi1').prop('checked', true).trigger('change');

            $('#modalSelesaikanRetur').modal('show');
        });

        // Toggle Bentuk Penggantian di Modal Selesaikan Retur
        $('input[name="tipe_penyelesaian"]').on('change', function() {
            const val = $('input[name="tipe_penyelesaian"]:checked').val();
            
            if (val === 'Ganti_Barang_Sama') {
                $('#modal2-box-barang-sama').removeClass('d-none');
                $('#modal2-qty-sama').prop('disabled', false);

                $('#modal2-box-barang-lain').addClass('d-none');
                $('#modal2-qty-lain').prop('disabled', true);
                $('#modal2-select-barang-ganti').prop('required', false);

                $('#modal2-box-uang').addClass('d-none');
                $('#modal2-input-nilai-uang').prop('disabled', true);
            } else if (val === 'Ganti_Barang_Lain') {
                $('#modal2-box-barang-lain').removeClass('d-none');
                $('#modal2-qty-lain').prop('disabled', false);
                $('#modal2-select-barang-ganti').prop('required', true);

                $('#modal2-box-barang-sama').addClass('d-none');
                $('#modal2-qty-sama').prop('disabled', true);

                $('#modal2-box-uang').addClass('d-none');
                $('#modal2-input-nilai-uang').prop('disabled', true);
            } else if (val === 'Ganti_Uang_Kas') {
                $('#modal2-box-uang').removeClass('d-none');
                $('#modal2-input-nilai-uang').prop('disabled', false);

                $('#modal2-box-barang-sama').addClass('d-none');
                $('#modal2-qty-sama').prop('disabled', true);

                $('#modal2-box-barang-lain').addClass('d-none');
                $('#modal2-qty-lain').prop('disabled', true);
                $('#modal2-select-barang-ganti').prop('required', false);
            }
        });

        // 3. Lihat Detail Dokumen Retur
        $(document).on('click', '.btn-detail-retur', function() {
            const id = $(this).data('id');

            $.get('<?= base_url('retur/detail') ?>/' + id, function(res) {
                if (res.status === 'success') {
                    const r = res.retur;
                    $('#dtl-retur-noretur').text(r.no_retur);
                    $('#dtl-retur-tgl').text(r.tanggal);
                    $('#dtl-retur-supplier').text(r.nama_supplier);
                    $('#dtl-retur-user').text(r.kode_user || '-');
                    $('#dtl-retur-keterangan').text(r.keterangan || '-');

                    if (r.status === 'Selesai') {
                        $('#dtl-retur-status-badge').html('<span class="badge bg-success fw-bold"><i class="bi bi-check2-circle me-1"></i>Selesai</span>');
                        $('#dtl-box-penggantian').removeClass('d-none');
                        $('#dtl-tipe-ganti').text(r.tipe_penyelesaian ? r.tipe_penyelesaian.replace(/_/g, ' ') : 'Selesai');
                        $('#dtl-nama-ganti').text(r.nama_barang_ganti ? r.nama_barang_ganti + ' (Qty: ' + r.qty_ganti + ')' : '-');
                        $('#dtl-nilai-ganti').text('Rp ' + new Intl.NumberFormat('id-ID').format(r.nilai_ganti || 0));
                        $('#dtl-tgl-selesai').text('Tuntas: ' + (r.tgl_selesai || '-'));
                        $('#dtl-catatan-selesai').text('Catatan Penggantian: ' + (r.catatan_penyelesaian || '-'));
                    } else {
                        $('#dtl-retur-status-badge').html('<span class="badge bg-warning text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i>Baru Proses</span>');
                        $('#dtl-box-penggantian').addClass('d-none');
                    }

                    $('#dtl-retur-brg-nama').text(r.nama_barang_retur);
                    $('#dtl-retur-brg-kode').text('Kode: ' + r.id_barang_retur);
                    $('#dtl-retur-qty').text(new Intl.NumberFormat('id-ID').format(r.qty_retur) + ' Unit Diretur');
                    $('#dtl-retur-alasan').text('Alasan: ' + r.alasan);

                    $('#modalDetailRetur').modal('show');
                } else {
                    Swal.fire('Gagal', res.message || 'Tidak dapat memuat detail data retur.', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Terjadi gangguan saat memuat data dari server.', 'error');
            });
        });

        // 4. Hapus Transaksi Retur
        const currentUserRole = '<?= session()->get('role') ?>';

        $(document).on('click', '.btn-hapus-retur', function() {
            const id = $(this).data('id');
            const noRetur = $(this).data('noretur');

            if (currentUserRole === 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Retur',
                    html: `
                        <p class="text-danger small mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Anda login sebagai <strong>Administrator</strong>. Menghapus dokumen retur <strong>${noRetur}</strong> akan <strong>mengembalikan kembali posisi stok toko</strong> dan membatalkan mutasi stok terkait.
                        </p>
                        <p class="small text-muted mb-0">Apakah Anda yakin ingin melanjutkan?</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Retur',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusRetur(id, '');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Otorisasi Super Admin',
                    html: `
                        <p class="text-danger small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Menghapus dokumen retur <strong>${noRetur}</strong> akan <strong>mengembalikan kembali posisi stok toko</strong>.
                        </p>
                        <label class="form-label small fw-bold d-block text-start">Masukkan Password Super Admin:</label>
                        <input type="password" id="swal-admin-retur-pw" class="form-control" placeholder="Password Admin...">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Otorisasi & Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pw = document.getElementById('swal-admin-retur-pw').value;
                        if (!pw) {
                            Swal.showValidationMessage('Password Super Admin wajib diisi!');
                            return false;
                        }
                        return pw;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusRetur(id, result.value);
                    }
                });
            }
        });

        function eksekusiHapusRetur(id, passwordAdmin) {
            Swal.fire({
                title: 'Memproses Pembatalan Retur...',
                text: 'Menyesuaikan kembali stok barang toko...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('retur/hapus') ?>/' + id, { password_admin: passwordAdmin }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil Dihapus!', res.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memproses permintaan.';
                Swal.fire('Akses Ditolak!', err, 'error');
            });
        }
    });
</script>
<?= $this->endSection() ?>
