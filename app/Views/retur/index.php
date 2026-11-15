<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-arrow-left-right text-danger me-2"></i>Retur Barang ke Supplier</h4>
        <p class="text-muted small mb-0">Kelola pengembalian barang rijek/expired multi-barang ke supplier dari status <strong>Baru Proses</strong> hingga <strong>Selesai</strong> dengan pemotongan stok toko otomatis.</p>
    </div>
    <div>
        <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalRetur" id="btn-modal-retur-baru">
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
    $grandTotalNilaiRetur = 0;
    foreach ($retur_list as $r) {
        if ($r['status'] === 'Baru Proses') $totalProses++;
        else $totalSelesai++;
        $grandTotalNilaiRetur += (float)($r['nilai_retur'] ?? 0);
    }
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Dokumen Retur</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary"><?= $totalRetur ?></h3>
                    <small class="text-muted">Total transaksi retur</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-file-earmark-text fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Nilai Modal Retur</span>
                    <h4 class="fw-bold mt-1 mb-0 text-danger">Rp <?= number_format($grandTotalNilaiRetur, 0, ',', '.') ?></h4>
                    <small class="text-muted">Total modal barang rijek</small>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Status: Baru Proses</span>
                    <h3 class="fw-bold mt-1 mb-0 text-warning"><?= $totalProses ?></h3>
                    <small class="text-muted">Menunggu penggantian</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Status: Selesai</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success"><?= $totalSelesai ?></h3>
                    <small class="text-muted">Penggantian tuntas</small>
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
                    <th>Tgl &amp; Petugas Retur</th>
                    <th>Supplier</th>
                    <th>Barang Diretur (Rijek)</th>
                    <th class="text-center">Total Qty</th>
                    <th class="text-end">Total Nilai Modal (Rp)</th>
                    <th>Keterangan / Alasan</th>
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
                            <td class="small">
                                <div class="fw-bold text-dark"><i class="bi bi-calendar-event me-1 text-danger"></i><?= date('d/m/Y', strtotime($r['tanggal'])) ?></div>
                                <small class="text-muted font-monospace"><?= date('H:i', strtotime($r['tanggal'])) ?> WIB</small>
                                <div class="mt-1">
                                    <span class="badge bg-light text-secondary border font-monospace fs-8" title="Petugas Penginput">
                                        <i class="bi bi-person me-1"></i><?= esc($r['kode_user'] ?? 'ADMIN') ?>
                                    </span>
                                </div>
                            </td>
                            <td class="fw-medium text-dark"><?= esc($r['nama_supplier']) ?></td>
                            <td>
                                <div class="fw-semibold text-danger"><?= esc($r['nama_barang_retur']) ?></div>
                                <?php if (!empty($r['total_item']) && $r['total_item'] > 1): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border fs-8 mt-1">
                                        <i class="bi bi-boxes me-1"></i><?= $r['total_item'] ?> Macam Barang
                                    </span>
                                <?php else: ?>
                                    <small class="text-muted font-monospace">Kode: <?= esc($r['id_barang_retur']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold text-danger fs-6"><?= number_format($r['qty_retur'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold text-danger fs-7">
                                Rp <?= number_format((float)($r['nilai_retur'] ?? 0), 0, ',', '.') ?>
                            </td>
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

<!-- ========================================================================= -->
<!-- 1. MODAL INPUT RETUR MULTI-BARANG KE SUPPLIER                             -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRetur" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white text-danger p-2 rounded-3">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Input Retur Multi-Barang ke Supplier</h5>
                        <small class="text-white text-opacity-75 fs-8">Wajib pilih Mitra Supplier terlebih dahulu sebelum memilih barang yang diretur.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('retur/simpan') ?>" method="POST" id="form-retur">
                <?= csrf_field() ?>
                <input type="hidden" name="items_json" id="input-items-json">

                <div class="modal-body p-4">
                    <!-- RINGKASAN BESAR: TOTAL SEMUA BARANG & NILAI MODAL (Sticky Header Kontras) -->
                    <div class="card border-0 rounded-3 p-3 mb-3 text-center" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fca5a5 !important; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.12);">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="text-start">
                                <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">TOTAL ESTIMASI NILAI MODAL SEMUA BARANG RETUR</span>
                                <h2 class="display-6 fw-bold text-danger my-1" id="display-total-nilai-modal">Rp 0</h2>
                            </div>
                            <div class="text-end">
                                <span class="text-uppercase small fw-bold text-muted d-block" style="letter-spacing: 0.5px;">TOTAL KUANTITAS SEMUA BARANG</span>
                                <span class="badge bg-white text-danger border border-danger-subtle px-3 py-2 fs-6 fw-bold font-monospace mt-1" id="display-total-qty-barang">
                                    0 Macam Produk (0 Kuantitas)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 1: INFORMASI SUPPLIER, TANGGAL RETUR & BUKTI RETUR -->
                    <div class="card border-0 bg-light rounded-3 p-3 mb-3 border">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">No. Bukti Retur</label>
                                <input type="text" class="form-control form-control-sm bg-white font-monospace fw-bold text-danger" value="<?= esc($no_retur) ?>" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="bi bi-calendar-check text-danger me-1"></i>Tanggal Retur <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal" id="input-tanggal-retur" class="form-control form-control-sm fw-bold border-danger text-danger bg-white" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="bi bi-truck text-primary me-1"></i>Pilih Supplier Tujuan Retur <span class="text-danger">* (Wajib Dipilih Pertama)</span>
                                </label>
                                <select name="kode_supplier" id="select-supplier-retur" class="form-select form-select-sm fw-bold border-primary" required>
                                    <option value="">-- Pilih Mitra Supplier Tujuan Terlebih Dahulu --</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= esc($s['kode_supplier']) ?>" data-nama="<?= esc($s['nama_supplier']) ?>">
                                            <?= esc($s['nama_supplier']) ?> (<?= esc($s['kode_supplier']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 2: KOTAK INPUT BARANG RIJEK / RUSAK -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3" style="background: #fff8f8; border: 1.5px dashed #fca5a5 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-danger mb-0 d-flex align-items-center gap-1">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Barang Rijek yang Diretur
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btn-open-lookup-retur" style="height: 26px;" disabled title="Wajib pilih supplier terlebih dahulu">
                                <i class="bi bi-card-checklist"></i> [F5] LookUp Barang Supplier
                            </button>
                        </div>

                        <!-- Banner Notifikasi Supplier -->
                        <div id="supplier-notice-retur" class="small text-danger fw-semibold mb-2" style="font-size: 0.78rem;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Pilih Mitra Supplier di atas terlebih dahulu agar pilihan produk milik supplier tersebut aktif.
                        </div>

                        <div class="row g-2 align-items-end">
                            <!-- 1. Dropdown / Pilih Barang -->
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">Pilih Produk Supplier <span class="text-danger">*</span></label>
                                <select id="select-barang-retur" class="form-select form-select-sm" disabled>
                                    <option value="">-- Wajib Pilih Supplier Dahulu --</option>
                                    <?php 
                                    $isAdmin = (session()->get('role') === 'admin');
                                    foreach ($barang_list as $b): 
                                        $stokReturLabel = $isAdmin ? ' [Stok: ' . $b['stok'] . ' ' . esc($b['satuan']) . ']' : '';
                                    ?>
                                        <option value="<?= esc($b['id_barang']) ?>" 
                                                data-nama="<?= esc($b['nama_barang']) ?>"
                                                data-satuan="<?= esc($b['satuan'] ?? 'Pcs') ?>"
                                                data-harga="<?= (float)$b['harga_beli'] ?>"
                                                data-stok="<?= (float)$b['stok'] ?>"
                                                data-supp1="<?= esc($b['kode_supplier_1'] ?? '') ?>"
                                                data-supp2="<?= esc($b['kode_supplier_2'] ?? '') ?>"
                                                class="opt-barang-item d-none">
                                            <?= esc($b['nama_barang']) ?> (<?= esc($b['id_barang']) ?>)<?= $stokReturLabel ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 2. Qty Retur -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold text-dark mb-1">Qty Retur <span class="text-danger">*</span></label>
                                <input type="number" id="input-item-qty" class="form-control form-control-sm text-center fw-bold fs-6 text-danger" min="0.1" step="any" value="1" disabled>
                            </div>

                            <!-- 3. Harga Modal Barang (Beli) -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold text-dark mb-1">Harga Modal (Rp)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white text-muted">Rp</span>
                                    <input type="text" id="input-item-harga-beli" class="form-control form-control-sm text-end fw-bold bg-light" readonly placeholder="0">
                                </div>
                            </div>

                            <!-- 4. Subtotal Nilai Modal -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold text-dark mb-1">Subtotal Modal</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white text-muted">Rp</span>
                                    <input type="text" id="input-item-subtotal-display" class="form-control form-control-sm text-end fw-bold text-danger bg-light" readonly placeholder="0">
                                </div>
                            </div>

                            <!-- 5. Tombol Tambah ke Keranjang -->
                            <div class="col-6 col-md-2">
                                <button type="button" class="btn btn-danger btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1 shadow-sm" id="btn-tambah-item-retur" disabled style="height: 31px;">
                                    <i class="bi bi-plus-lg"></i> Tambahkan
                                </button>
                            </div>

                            <!-- 6. Alasan Retur (Full Baris di Bawahnya) -->
                            <div class="col-12 mt-2">
                                <label class="form-label small fw-bold text-dark mb-1">Alasan Kerusakan / Retur Produk Ini: <span class="text-danger">*</span></label>
                                <select id="input-item-alasan" class="form-select form-select-sm" disabled>
                                    <option value="Rijek / Bocor">Rijek / Jerigen Bocor</option>
                                    <option value="Expired / Kadaluarsa">Expired / Kadaluarsa</option>
                                    <option value="Cacat Kemasan / Segel Rusak">Cacat Kemasan / Segel Rusak</option>
                                    <option value="Kualitas / Aroma Berubah">Kualitas / Aroma Berubah</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BARIS 3: TABEL DAFTAR BARANG YANG DIRETUR (MULTI-BARANG) -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-basket3-fill text-danger me-1"></i>Daftar Barang Rijek yang Diretur (Keranjang Retur)
                            </h6>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8" id="badge-total-items-retur">
                                0 Item Barang
                            </span>
                        </div>

                        <div class="table-responsive border rounded-3 bg-white">
                            <table class="table table-sm table-hover align-middle mb-0" id="table-keranjang-retur">
                                <thead class="table-light fs-8 text-uppercase">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">No</th>
                                        <th>Nama Barang &amp; Kode</th>
                                        <th class="text-center" style="width: 100px;">Qty Retur</th>
                                        <th class="text-end" style="width: 130px;">Harga Modal (Rp)</th>
                                        <th>Alasan Retur</th>
                                        <th class="text-end" style="width: 150px;">Subtotal Nilai Modal (Rp)</th>
                                        <th class="text-center" style="width: 50px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-keranjang-retur">
                                    <tr id="empty-row-retur">
                                        <td colspan="7" class="text-center text-muted py-4 small">
                                            <i class="bi bi-inbox fs-2 opacity-25 d-block mb-1"></i>
                                            Belum ada barang rijek yang dimasukkan. Silakan pilih supplier tujuan di atas lalu tambahkan produk rijek.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold fs-7 border-top d-none" id="tfoot-keranjang-retur">
                                    <tr class="bg-light">
                                        <td colspan="2" class="text-end fw-bold">TOTAL SEMUA BARANG:</td>
                                        <td class="text-center text-danger fw-bold fs-6" id="summary-total-qty-retur">0</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-end text-danger fw-bold fs-6" id="summary-total-nilai-retur">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-danger d-block mt-2 fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i>Seluruh stok barang di atas akan otomatis dipotong keluar dari stok toko pada tanggal bukti retur ini.
                        </small>
                    </div>

                    <!-- BARIS 4: STATUS PENANGANAN & KETERANGAN -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark mb-2">Status Penanganan Retur Saat Ini: <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check border bg-white p-3 rounded h-100">
                                    <input class="form-check-input" type="radio" name="status" id="status-baru-proses" value="Baru Proses" checked>
                                    <label class="form-check-label fw-bold text-dark" for="status-baru-proses">
                                        <i class="bi bi-hourglass-split text-warning me-1"></i> Baru Proses (Direkomendasikan)
                                    </label>
                                    <small class="text-muted d-block mt-1">Barang rijek diambil sales hari ini, penggantian baru dikirim beberapa hari kemudian.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border bg-white p-3 rounded h-100">
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

                    <!-- Catatan / Keterangan -->
                    <div>
                        <label class="form-label small fw-bold text-dark">Catatan / Keterangan Dokumen Retur</label>
                        <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Barang dibawa oleh sales Bp. Dani, dijanjikan pengganti dikirim hari Selasa depan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" id="btn-submit-retur">
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
<div class="modal fade" id="modalSelesaikanRetur" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-check2-circle me-2"></i>Penyelesaian Penggantian Retur Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('retur/selesaikan') ?>" method="POST" id="form-selesaikan-retur">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="selesaikan-id">

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">No. Bukti Retur:</small>
                                <span class="fw-bold text-primary font-monospace" id="selesaikan-label-noretur">-</span>
                            </div>
                            <div class="col-6 col-md-4">
                                <small class="text-muted d-block">Mitra Supplier:</small>
                                <span class="fw-bold text-dark" id="selesaikan-label-supplier">-</span>
                            </div>
                            <div class="col-12 col-md-5">
                                <small class="text-muted d-block">Barang Rijek yang Diretur:</small>
                                <span class="fw-bold text-danger" id="selesaikan-label-barang">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-calendar-check text-success me-1"></i>Tanggal Realisasi Penyelesaian <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tgl_selesai" id="selesaikan-tgl-selesai" class="form-control form-control-sm fw-bold border-success text-success bg-white" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <label class="form-label small fw-bold text-dark mb-2">Bentuk Realisasi Penggantian dari Supplier: <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded h-100">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi1" value="Ganti_Barang_Sama" checked>
                                <label class="form-check-label fw-bold text-dark" for="modal2-solusi1">
                                    <i class="bi bi-arrow-repeat text-success me-1"></i> Barang Sama (Baru)
                                </label>
                                <small class="text-muted d-block mt-1">Diganti dengan barang baru yang sama persis.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded h-100">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi2" value="Ganti_Barang_Lain">
                                <label class="form-check-label fw-bold text-dark" for="modal2-solusi2">
                                    <i class="bi bi-box-arrow-in-right text-primary me-1"></i> Barang Lain
                                </label>
                                <small class="text-muted d-block mt-1">Diganti dengan barang lain (beda aroma/ukuran/jenis).</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check border bg-white p-3 rounded h-100">
                                <input class="form-check-input" type="radio" name="tipe_penyelesaian" id="modal2-solusi3" value="Ganti_Uang_Kas">
                                <label class="form-check-label fw-bold text-dark" for="modal2-solusi3">
                                    <i class="bi bi-cash-stack text-warning me-1"></i> Penggantian Uang
                                </label>
                                <small class="text-muted d-block mt-1">Diganti uang kas atau potong nota tagihan supplier.</small>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Jika Barang Sama -->
                    <div id="modal2-box-barang-sama" class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark">Jumlah Qty Barang Baru yang Diterima</label>
                        <input type="number" name="qty_ganti" id="modal2-qty-sama" class="form-control text-center fw-bold fs-5 text-success" min="1" step="any" value="1">
                        <small class="text-success mt-1 d-block"><i class="bi bi-check-circle me-1"></i>Stok barang di toko akan otomatis bertambah kembali sejumlah barang pengganti ini.</small>
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

                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Selesai &amp; Perbarui Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. MODAL DETAIL DATA RETUR MULTI-BARANG                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailRetur" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark py-3">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-arrow-left-right me-2"></i>Rincian Dokumen Retur: <span id="dtl-retur-noretur"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3 p-3 bg-light rounded-3 border">
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block"><i class="bi bi-calendar-event text-danger me-1"></i>Tanggal Retur:</span>
                        <strong class="text-dark small font-monospace" id="dtl-retur-tgl">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block"><i class="bi bi-truck text-primary me-1"></i>Mitra Supplier:</span>
                        <strong class="text-primary small" id="dtl-retur-supplier">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block"><i class="bi bi-activity text-warning me-1"></i>Status Siklus:</span>
                        <span id="dtl-retur-status-badge"></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block"><i class="bi bi-person text-secondary me-1"></i>Petugas Pembuat:</span>
                        <strong class="text-dark small" id="dtl-retur-user">-</strong>
                    </div>
                    <div class="col-6 col-md-3 d-none" id="dtl-box-user-selesai">
                        <span class="small text-muted d-block"><i class="bi bi-person-check text-success me-1"></i>Diselesaikan Oleh:</span>
                        <strong class="text-success small" id="dtl-retur-user-selesai">-</strong>
                    </div>
                    <div class="col-6 col-md-3 d-none" id="dtl-box-tgl-selesai">
                        <span class="small text-muted d-block"><i class="bi bi-calendar-check text-success me-1"></i>Tanggal Selesai:</span>
                        <strong class="text-success small font-monospace" id="dtl-retur-tgl-selesai">-</strong>
                    </div>
                </div>

                <!-- Tabel Rincian Multi-Barang yang Diretur -->
                <div class="card p-3 mb-3 border-danger bg-danger bg-opacity-10">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-octagon-fill me-2"></i>Daftar Barang Rijek / Rusak yang Diretur:</h6>
                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-sm table-hover mb-0" id="dtl-table-items">
                            <thead class="table-light fs-8 text-uppercase">
                                <tr>
                                    <th class="text-center" style="width: 35px;">No</th>
                                    <th>Nama Produk &amp; Kode</th>
                                    <th class="text-center" style="width: 80px;">Qty</th>
                                    <th class="text-end" style="width: 120px;">Harga Modal (Rp)</th>
                                    <th>Alasan Retur</th>
                                    <th class="text-end" style="width: 140px;">Subtotal Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="dtl-tbody-items">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-2 small">Memuat rincian barang...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Box Realisasi Penggantian Jika Sudah Selesai -->
                <div class="card p-3 mb-3 border-success bg-success bg-opacity-10 d-none" id="dtl-box-penggantian">
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

                <!-- Keterangan Umum -->
                <div class="small text-muted mb-3">
                    <strong>Catatan Dokumen: </strong><span id="dtl-retur-keterangan">-</span>
                </div>

                <!-- Seksi Audit Trail / Rekam Jejak Sistem -->
                <div class="card p-3 mb-0 border rounded-3 bg-white shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-primary"></i> Rekam Jejak Audit Trail (Aktivitas Sistem)
                        </h6>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8" id="dtl-audit-count">0 Riwayat</span>
                    </div>
                    <div id="dtl-audit-trail-list">
                        <!-- Konten log audit disuntikkan via AJAX -->
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
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

        // =========================================================================
        // LOGIKA MULTI-BARANG RETUR, HARGA MODAL, & TOTAL SELURUH BARANG
        // =========================================================================
        let keranjangRetur = [];

        // Helper update input harga beli modal & subtotal di form baris
        function updateItemRowSubtotal() {
            const $opt = $('#select-barang-retur').find('option:selected');
            const hargaBeli = parseFloat($opt.data('harga')) || 0;
            const qty = parseFloat($('#input-item-qty').val()) || 0;
            const subtotal = qty * hargaBeli;

            $('#input-item-harga-beli').val(new Intl.NumberFormat('id-ID').format(hargaBeli));
            $('#input-item-subtotal-display').val(new Intl.NumberFormat('id-ID').format(subtotal));
        }

        $('#select-barang-retur').on('change', updateItemRowSubtotal);
        $('#input-item-qty').on('input', updateItemRowSubtotal);

        // 1. Pilih Supplier Tujuan: Aktifkan Input Barang Khusus Supplier Tersebut
        $('#select-supplier-retur').on('change', function() {
            const kodeSupplier = $(this).val();
            const namaSupplier = $(this).find('option:selected').data('nama') || '';

            if (kodeSupplier) {
                // Aktifkan kontrol input barang
                $('#select-barang-retur').prop('disabled', false);
                $('#input-item-qty').prop('disabled', false);
                $('#input-item-alasan').prop('disabled', false);
                $('#btn-tambah-item-retur').prop('disabled', false);
                $('#btn-open-lookup-retur').prop('disabled', false).attr('title', 'Buka LookUp Barang Supplier: ' + namaSupplier);

                // Perbarui banner
                $('#supplier-notice-retur')
                    .removeClass('text-danger')
                    .addClass('text-success')
                    .html('<i class="bi bi-check-circle-fill me-1"></i>Supplier Terpilih: <strong>' + namaSupplier + '</strong> (' + kodeSupplier + '). Silakan pilih produk rijek milik supplier ini.');

                // Ambil daftar barang yang berelasi dengan supplier terpilih (dari Master & Riwayat Pembelian)
                $('#select-barang-retur').html('<option value="">Memuat produk supplier...</option>');
                $.get('<?= base_url('barang/lookup') ?>', { tipe: 'pembelian', supplier: kodeSupplier }, function(res) {
                    if (res.status === 'success' && res.data && res.data.length > 0) {
                        $('#select-barang-retur').empty().append('<option value="">-- Pilih Barang Supplier (' + res.data.length + ' Produk Tersedia) --</option>');
                        res.data.forEach(function(item) {
                            const stokText = ' [Stok: ' + new Intl.NumberFormat('id-ID').format(item.stok_cabang || item.stok_total || 0) + ' ' + (item.satuan || '') + ']';
                            const optText = item.nama_barang + ' (' + item.id_barang + ')' + stokText;
                            const $opt = $('<option>')
                                .val(item.id_barang)
                                .text(optText)
                                .attr('data-nama', item.nama_barang)
                                .attr('data-satuan', item.satuan || 'Pcs')
                                .attr('data-harga', parseFloat(item.harga_beli) || 0)
                                .attr('data-stok', parseFloat(item.stok_cabang || item.stok_total) || 0)
                                .attr('data-supp1', item.kode_supplier_1 || kodeSupplier)
                                .attr('data-supp2', item.kode_supplier_2 || '')
                                .addClass('opt-barang-item');
                            $('#select-barang-retur').append($opt);
                        });
                    } else {
                        $('#select-barang-retur').empty().append('<option value="">-- Belum ada produk terhubung supplier ini (Gunakan LookUp [F5] untuk melihat semua) --</option>');
                    }
                });

                // Jika supplier diganti saat keranjang sudah ada isi, beri konfirmasi
                if (keranjangRetur.length > 0) {
                    Swal.fire({
                        title: 'Ganti Supplier Tujuan?',
                        text: 'Mengganti supplier akan mengosongkan keranjang retur sebelumnya karena retur per dokumen hanya untuk 1 supplier yang sama.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kosongkan Keranjang',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            keranjangRetur = [];
                            renderKeranjangRetur();
                        }
                    });
                }
            } else {
                // Kunci kembali kontrol jika supplier dikosongkan
                $('#select-barang-retur').prop('disabled', true).val('').trigger('change');
                $('#input-item-qty').prop('disabled', true);
                $('#input-item-alasan').prop('disabled', true);
                $('#btn-tambah-item-retur').prop('disabled', true);
                $('#btn-open-lookup-retur').prop('disabled', true).attr('title', 'Wajib pilih supplier terlebih dahulu');

                $('#supplier-notice-retur')
                    .removeClass('text-success')
                    .addClass('text-danger')
                    .html('<i class="bi bi-exclamation-circle-fill me-1"></i>Pilih Mitra Supplier di atas terlebih dahulu agar pilihan produk milik supplier tersebut aktif.');

                $('#select-barang-retur option.opt-barang-item').addClass('d-none');
            }
        });

        // 2. Buka LookUp Barang (Wajib Pilih Supplier Terlebih Dahulu)
        $('#btn-open-lookup-retur').on('click', function(e) {
            e.preventDefault();
            const selectedSupplier = $('#select-supplier-retur').val();

            if (!selectedSupplier) {
                Swal.fire({
                    title: 'Pilih Mitra Supplier',
                    text: 'Silakan pilih Mitra Supplier Tujuan di atas terlebih dahulu agar daftar produk supplier ditampilkan di LookUp.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $('#select-supplier-retur').focus();
                return;
            }

            // Buka Modal LookUp dalam mode retur khusus supplier terpilih (Semua Status Aktif & Nonaktif)
            const selectedSupplierName = $('#select-supplier-retur').find('option:selected').data('nama') || '';
            StockLookup.open({
                mode: 'retur',
                supplier: selectedSupplier,
                supplierName: selectedSupplierName,
                onSelect: function(item) {
                    let $existingOpt = $('#select-barang-retur').find('option[value="' + item.id_barang + '"]');
                    if ($existingOpt.length === 0) {
                        const stokText = ' [Stok: ' + new Intl.NumberFormat('id-ID').format(item.stok_cabang || item.stok_total || 0) + ' ' + (item.satuan || '') + ']';
                        const optText = item.nama_barang + ' (' + item.id_barang + ')' + stokText;
                        const $newOpt = $('<option>')
                            .val(item.id_barang)
                            .text(optText)
                            .attr('data-nama', item.nama_barang)
                            .attr('data-satuan', item.satuan || 'Pcs')
                            .attr('data-harga', parseFloat(item.harga_beli) || 0)
                            .attr('data-stok', parseFloat(item.stok_cabang || item.stok_total) || 0)
                            .attr('data-supp1', item.kode_supplier_1 || selectedSupplier)
                            .attr('data-supp2', item.kode_supplier_2 || '')
                            .addClass('opt-barang-item');

                        $('#select-barang-retur').append($newOpt);
                    } else {
                        $existingOpt.removeClass('d-none');
                    }

                    $('#select-barang-retur').val(item.id_barang).trigger('change');
                    setTimeout(() => $('#input-item-qty').focus().select(), 200);
                }
            });
        });

        // Shortcut Keyboard F5 untuk Buka LookUp Barang di Retur
        $(document).on('keydown', function(e) {
            if (e.key === 'F5' || e.keyCode === 116) {
                if ($('#modalInputRetur').hasClass('show') && !$('#modalLookupStock').hasClass('show')) {
                    e.preventDefault();
                    $('#btn-open-lookup-retur').trigger('click');
                }
            }
        });

        // 3. Tambah Barang ke Keranjang Retur
        $('#btn-tambah-item-retur').on('click', function() {
            const selectedSupplier = $('#select-supplier-retur').val();
            if (!selectedSupplier) {
                Swal.fire('Peringatan', 'Pilih Mitra Supplier Tujuan terlebih dahulu.', 'warning');
                $('#select-supplier-retur').focus();
                return;
            }

            const idBarang = $('#select-barang-retur').val();
            if (!idBarang) {
                Swal.fire('Pilih Barang', 'Silakan pilih barang rijek yang akan diretur terlebih dahulu.', 'warning');
                $('#select-barang-retur').focus();
                return;
            }

            const $selectedOpt = $('#select-barang-retur').find('option:selected');
            const namaBarang = $selectedOpt.data('nama') || $selectedOpt.text().trim();
            const satuan = $selectedOpt.data('satuan') || 'Pcs';
            const hargaBeli = parseFloat($selectedOpt.data('harga')) || 0;
            const qty = parseFloat($('#input-item-qty').val()) || 0;
            const alasan = $('#input-item-alasan').val() || 'Rijek / Bocor';

            if (qty <= 0) {
                Swal.fire('Qty Tidak Valid', 'Jumlah (Qty) retur harus lebih besar dari 0.', 'warning');
                $('#input-item-qty').focus();
                return;
            }

            // Cek apakah barang sudah ada di keranjang
            const existingIndex = keranjangRetur.findIndex(item => item.id_barang === idBarang);
            if (existingIndex > -1) {
                keranjangRetur[existingIndex].qty += qty;
                keranjangRetur[existingIndex].subtotal = keranjangRetur[existingIndex].qty * hargaBeli;
                keranjangRetur[existingIndex].alasan = alasan;
            } else {
                keranjangRetur.push({
                    id_barang: idBarang,
                    nama_barang: namaBarang,
                    satuan: satuan,
                    qty: qty,
                    harga_beli: hargaBeli,
                    subtotal: qty * hargaBeli,
                    alasan: alasan
                });
            }

            renderKeranjangRetur();

            // Reset input barang
            $('#select-barang-retur').val('').trigger('change');
            $('#input-item-qty').val(1);
        });

        // 4. Render Tabel Keranjang Retur Beserta Total Modal & Kuantitas
        function renderKeranjangRetur() {
            const $tbody = $('#tbody-keranjang-retur');
            $tbody.empty();

            if (keranjangRetur.length === 0) {
                $tbody.append(`
                    <tr id="empty-row-retur">
                        <td colspan="7" class="text-center text-muted py-4 small">
                            <i class="bi bi-inbox fs-2 opacity-25 d-block mb-1"></i>
                            Belum ada barang rijek yang dimasukkan. Silakan pilih supplier tujuan di atas lalu tambahkan produk rijek.
                        </td>
                    </tr>
                `);
                $('#badge-total-items-retur').text('0 Item Barang');
                $('#display-total-nilai-modal').text('Rp 0');
                $('#display-total-qty-barang').text('0 Macam Produk (0 Kuantitas)');
                $('#tfoot-keranjang-retur').addClass('d-none');
                $('#input-items-json').val('');
                return;
            }

            let totalQty = 0;
            let totalNilai = 0;

            keranjangRetur.forEach((item, idx) => {
                totalQty += item.qty;
                totalNilai += item.subtotal;

                $tbody.append(`
                    <tr>
                        <td class="text-center text-muted small">${idx + 1}</td>
                        <td>
                            <div class="fw-bold text-dark fs-7">${item.nama_barang}</div>
                            <small class="text-muted font-monospace">${item.id_barang} &bull; ${item.satuan}</small>
                        </td>
                        <td class="text-center fw-bold text-danger fs-7">
                            ${new Intl.NumberFormat('id-ID').format(item.qty)} ${item.satuan}
                        </td>
                        <td class="text-end fw-semibold text-dark fs-8">
                            Rp ${new Intl.NumberFormat('id-ID').format(item.harga_beli)}
                        </td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">
                                ${item.alasan}
                            </span>
                        </td>
                        <td class="text-end fw-bold text-danger fs-7">
                            Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle py-0 px-1 btn-hapus-item-retur" data-index="${idx}" title="Hapus Barang Ini">
                                <i class="bi bi-x"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Update Semua Tampilan Total
            $('#badge-total-items-retur').text(keranjangRetur.length + ' Macam Produk (' + new Intl.NumberFormat('id-ID').format(totalQty) + ' Satuan)');
            $('#display-total-nilai-modal').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalNilai));
            $('#display-total-qty-barang').text(keranjangRetur.length + ' Macam Produk (' + new Intl.NumberFormat('id-ID').format(totalQty) + ' Satuan)');

            $('#summary-total-qty-retur').text(new Intl.NumberFormat('id-ID').format(totalQty));
            $('#summary-total-nilai-retur').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalNilai));
            $('#tfoot-keranjang-retur').removeClass('d-none');

            // Simpan JSON ke input hidden
            $('#input-items-json').val(JSON.stringify(keranjangRetur));
            $('#input-nilai-ganti').val(new Intl.NumberFormat('id-ID').format(totalNilai));
            $('#input-qty-ganti').val(totalQty);
        }

        // 5. Hapus Item dari Keranjang Retur
        $(document).on('click', '.btn-hapus-item-retur', function() {
            const idx = $(this).data('index');
            keranjangRetur.splice(idx, 1);
            renderKeranjangRetur();
        });

        // 6. Submit Form Retur: Validasi Minimal 1 Barang
        $('#form-retur').on('submit', function(e) {
            const selectedSupplier = $('#select-supplier-retur').val();
            if (!selectedSupplier) {
                e.preventDefault();
                Swal.fire('Supplier Belum Dipilih', 'Silakan pilih Mitra Supplier Tujuan retur terlebih dahulu.', 'warning');
                $('#select-supplier-retur').focus();
                return false;
            }

            if (keranjangRetur.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Keranjang Retur Kosong',
                    text: 'Tambahkan minimal 1 barang rijek ke dalam daftar retur sebelum menyimpan data.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            $('#input-items-json').val(JSON.stringify(keranjangRetur));
            return true;
        });

        // Reset keranjang saat modal retur baru ditutup
        $('#modalRetur').on('hidden.bs.modal', function () {
            keranjangRetur = [];
            renderKeranjangRetur();
            $('#select-supplier-retur').val('').trigger('change');
        });

        // =========================================================================
        // TOGGLE STATUS & PENGGANTIAN
        // =========================================================================
        $('input[name="status"]').on('change', function() {
            if ($(this).val() === 'Selesai') {
                $('#box-opsi-langsung').removeClass('d-none');
            } else {
                $('#box-opsi-langsung').addClass('d-none');
            }
        });

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

        // =========================================================================
        // TERIMA PENGGANTIAN / SELESAIKAN RETUR
        // =========================================================================
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

            $('#modal2-qty-sama').val(qty);
            $('#modal2-qty-lain').val(qty);
            $('#modal2-input-nilai-uang').val(new Intl.NumberFormat('id-ID').format(nilai || 0));

            $('#modal2-solusi1').prop('checked', true).trigger('change');
            $('#modalSelesaikanRetur').modal('show');
        });

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

        // =========================================================================
        // LIHAT DETAIL DOKUMEN RETUR (DENGAN TABEL MULTI-BARANG & HARGA MODAL)
        // =========================================================================
        $(document).on('click', '.btn-detail-retur', function() {
            const id = $(this).data('id');

            $.get('<?= base_url('retur/detail') ?>/' + id, function(res) {
                if (res.status === 'success') {
                    const r = res.retur;
                    const items = res.items || [];

                    $('#dtl-retur-noretur').text(r.no_retur);
                    $('#dtl-retur-tgl').text(r.tanggal);
                    $('#dtl-retur-supplier').text(r.nama_supplier);
                    $('#dtl-retur-user').text(r.kode_user || '-');
                    $('#dtl-retur-keterangan').text(r.keterangan || '-');

                    if (r.status === 'Selesai') {
                        $('#dtl-retur-status-badge').html('<span class="badge bg-success fw-bold"><i class="bi bi-check2-circle me-1"></i>Selesai</span>');
                        $('#dtl-box-penggantian').removeClass('d-none');
                        $('#dtl-box-user-selesai').removeClass('d-none');
                        $('#dtl-box-tgl-selesai').removeClass('d-none');
                        $('#dtl-retur-user-selesai').text(r.user_selesai || '-');
                        $('#dtl-retur-tgl-selesai').text(r.tgl_selesai || '-');
                        $('#dtl-tipe-ganti').text(r.tipe_penyelesaian ? r.tipe_penyelesaian.replace(/_/g, ' ') : 'Selesai');
                        $('#dtl-nama-ganti').text(r.nama_barang_ganti ? r.nama_barang_ganti + ' (Qty: ' + r.qty_ganti + ')' : '-');
                        $('#dtl-nilai-ganti').text('Rp ' + new Intl.NumberFormat('id-ID').format(r.nilai_ganti || 0));
                        $('#dtl-tgl-selesai').text('Tuntas: ' + (r.tgl_selesai || '-'));
                        $('#dtl-catatan-selesai').text('Catatan Penggantian: ' + (r.catatan_penyelesaian || '-'));
                    } else {
                        $('#dtl-retur-status-badge').html('<span class="badge bg-warning text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i>Baru Proses</span>');
                        $('#dtl-box-penggantian').addClass('d-none');
                        $('#dtl-box-user-selesai').addClass('d-none');
                        $('#dtl-box-tgl-selesai').addClass('d-none');
                    }

                    // Render Tabel Detail Multi-Barang
                    const $dtlTbody = $('#dtl-tbody-items');
                    $dtlTbody.empty();

                    if (items.length > 0) {
                        let totalQty = 0;
                        let totalNilai = 0;
                        items.forEach((it, idx) => {
                            totalQty += parseFloat(it.qty_retur);
                            totalNilai += parseFloat(it.subtotal_nilai);
                            $dtlTbody.append(`
                                <tr>
                                    <td class="text-center text-muted small">${idx + 1}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-7">${it.nama_barang_retur}</div>
                                        <small class="text-muted font-monospace">${it.id_barang_retur}</small>
                                    </td>
                                    <td class="text-center fw-bold text-danger fs-7">
                                        ${new Intl.NumberFormat('id-ID').format(it.qty_retur)} ${it.satuan || ''}
                                    </td>
                                    <td class="text-end fw-semibold text-dark fs-8">
                                        Rp ${new Intl.NumberFormat('id-ID').format(it.harga_beli)}
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">
                                            ${it.alasan}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-danger fs-7">
                                        Rp ${new Intl.NumberFormat('id-ID').format(it.subtotal_nilai)}
                                    </td>
                                </tr>
                            `);
                        });
                        $dtlTbody.append(`
                            <tr class="table-light fw-bold border-top">
                                <td colspan="2" class="text-end">TOTAL KESELURUHAN:</td>
                                <td class="text-center text-danger fs-7">${new Intl.NumberFormat('id-ID').format(totalQty)}</td>
                                <td></td>
                                <td></td>
                                <td class="text-end text-danger fs-6">Rp ${new Intl.NumberFormat('id-ID').format(totalNilai)}</td>
                            </tr>
                        `);
                    } else {
                        // Fallback jika data retur tunggal lama
                        $dtlTbody.append(`
                            <tr>
                                <td class="text-center">1</td>
                                <td>
                                    <div class="fw-bold text-dark fs-7">${r.nama_barang_retur}</div>
                                    <small class="text-muted font-monospace">${r.id_barang_retur}</small>
                                </td>
                                <td class="text-center fw-bold text-danger fs-7">${new Intl.NumberFormat('id-ID').format(r.qty_retur)}</td>
                                <td class="text-end fw-semibold text-dark fs-8">Rp ${new Intl.NumberFormat('id-ID').format((r.qty_retur > 0 ? (r.nilai_retur / r.qty_retur) : 0))}</td>
                                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">${r.alasan}</span></td>
                                <td class="text-end fw-bold text-danger fs-7">Rp ${new Intl.NumberFormat('id-ID').format(r.nilai_retur || 0)}</td>
                            </tr>
                        `);
                    }

                    // Render Audit Trail Riwayat Aktivitas Dokumen
                    const auditLogs = res.audit_logs || [];
                    $('#dtl-audit-count').text(auditLogs.length + ' Catatan Aktivitas');
                    const $auditList = $('#dtl-audit-trail-list');
                    $auditList.empty();

                    if (auditLogs.length > 0) {
                        let auditHtml = '<div class="list-group list-group-flush border rounded-2">';
                        auditLogs.forEach((log) => {
                            let badgeClass = 'bg-primary';
                            let icon = 'bi-info-circle';
                            if (log.aksi.indexOf('INPUT') !== -1) {
                                badgeClass = 'bg-success';
                                icon = 'bi-plus-circle-fill';
                            } else if (log.aksi.indexOf('SELESAIKAN') !== -1) {
                                badgeClass = 'bg-info text-dark';
                                icon = 'bi-check-circle-fill';
                            } else if (log.aksi.indexOf('HAPUS') !== -1 || log.aksi.indexOf('BATAL') !== -1) {
                                badgeClass = 'bg-danger';
                                icon = 'bi-trash-fill';
                            }

                            auditHtml += `
                                <div class="list-group-item p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge ${badgeClass} fs-8"><i class="bi ${icon} me-1"></i>${log.aksi}</span>
                                            <strong class="text-dark fs-8">${log.nama_user || log.kode_user || 'User'}</strong>
                                            <span class="badge bg-light text-secondary border font-monospace fs-9">${log.role || 'petugas'}</span>
                                        </div>
                                        <small class="text-muted font-monospace fs-8"><i class="bi bi-clock me-1"></i>${log.created_at}</small>
                                    </div>
                                    <div class="text-dark fs-8">${log.deskripsi}</div>
                                    <div class="text-muted fs-9 mt-1 d-flex gap-3">
                                        <span><i class="bi bi-geo-alt me-1"></i>IP: ${log.ip_address || '-'}</span>
                                        <span class="text-truncate" style="max-width: 340px;" title="${log.user_agent || ''}"><i class="bi bi-display me-1"></i>${log.user_agent || '-'}</span>
                                    </div>
                                </div>
                            `;
                        });
                        auditHtml += '</div>';
                        $auditList.html(auditHtml);
                    } else {
                        $auditList.html(`
                            <div class="p-3 text-center text-muted bg-light rounded-2 border">
                                <i class="bi bi-info-circle text-primary me-1"></i>Dokumen dibuat oleh <strong>${r.kode_user || 'ADMIN'}</strong> pada <strong>${r.created_at || r.tanggal}</strong>.
                            </div>
                        `);
                    }

                    $('#modalDetailRetur').modal('show');
                } else {
                    Swal.fire('Gagal', res.message || 'Tidak dapat memuat detail data retur.', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Terjadi gangguan saat memuat data dari server.', 'error');
            });
        });

        // =========================================================================
        // HAPUS TRANSAKSI RETUR (OTORISASI ADMIN)
        // =========================================================================
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
                            Anda login sebagai <strong>Administrator</strong>. Menghapus dokumen retur <strong>${noRetur}</strong> akan <strong>mengembalikan kembali posisi stok toko</strong> untuk semua barang terkait.
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
                    title: 'Otorisasi Super Admin Diperlukan',
                    html: `
                        <p class="text-muted small mb-2">Menghapus transaksi retur akan mengembalikan stok barang rijek kembali ke toko. Masukkan Password Super Admin untuk verifikasi:</p>
                        <input type="password" id="swal-admin-password" class="form-control text-center fs-5" placeholder="Masukkan Password Admin">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Verifikasi & Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pass = $('#swal-admin-password').val().trim();
                        if (!pass) {
                            Swal.showValidationMessage('Password Admin tidak boleh kosong!');
                        }
                        return pass;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusRetur(id, result.value);
                    }
                });
            }
        });

        function eksekusiHapusRetur(id, password) {
            Swal.fire({
                title: 'Memproses Pembatalan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('retur/hapus') ?>/' + id, { password_admin: password }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil Dihapus!', res.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal Menghapus', res.message, 'error');
                }
            }, 'json').fail(function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat menghapus retur.';
                Swal.fire('Gagal', msg, 'error');
            });
        }
    });
</script>

<style>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.75rem; }
</style>

<?= $this->include('layout/modal_lookup_barang') ?>
<?= $this->endSection() ?>
