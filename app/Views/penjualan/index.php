<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Daftar Faktur Penjualan Kasir</h4>
        <p class="text-muted small mb-0">Monitoring seluruh transaksi kasir, rincian produk terjual, cetak ulang struk, dan otorisasi hapus transaksi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('laporan/penjualan') ?>" class="btn btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Laporan &amp; Export
        </a>
        <a href="<?= base_url('pos') ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-cart-check-fill me-2"></i> Buka Kasir (POS)
        </a>
    </div>
</div>

<!-- Card Ringkasan Omset & Metrik -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Omset Penjualan</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary">Rp <?= number_format($total_omset, 0, ',', '.') ?></h3>
                    <small class="text-muted">Total penerimaan kasir</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Jumlah Transaksi</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success"><?= number_format($total_transaksi, 0, ',', '.') ?> Faktur</h3>
                    <small class="text-muted">Struk belanja tercetak</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card-modern p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Produk Terjual</span>
                    <h3 class="fw-bold mt-1 mb-0 text-info"><?= number_format($total_qty, 0, ',', '.') ?> Item</h3>
                    <small class="text-muted">Liter / Botol / Jerigen</small>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Potongan Voucher & Jerigen</span>
                    <h3 class="fw-bold mt-1 mb-0 text-warning-emphasis">Rp <?= number_format($total_potongan_voucher + $total_potongan_jerigen, 0, ',', '.') ?></h3>
                    <small class="text-muted">Diskon loyalitas pelanggan</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-ticket-perforated fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Faktur Penjualan -->
<div class="card-modern p-3 mb-4">
    <form action="<?= base_url('penjualan') ?>" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1">Dari Tanggal:</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= esc($tgl_mulai) ?>">
        </div>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1">Sampai Tanggal:</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= esc($tgl_selesai) ?>">
        </div>
        <?php if (!empty($is_admin)): ?>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1"><i class="bi bi-shop me-1 text-primary"></i>Cabang:</label>
            <select name="id_cabang" class="form-select form-select-sm">
                <option value="semua">-- Semua Cabang --</option>
                <?php foreach ($cabang_list as $cb): ?>
                    <option value="<?= $cb['id'] ?>" <?= ($filter_cabang == $cb['id']) ? 'selected' : '' ?>>
                        <?= esc($cb['nama_cabang']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1"><i class="bi bi-shop me-1 text-success"></i>Cabang Kasir:</label>
            <div class="badge bg-light text-dark border p-2 w-100 text-truncate text-start d-flex align-items-center gap-1" style="height: 31px;">
                <i class="bi bi-lock-fill text-muted"></i>
                <span class="text-truncate"><?= esc(session()->get('nama_cabang') ?? 'KSP Pusat') ?></span>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-2">
            <label class="form-label small fw-bold mb-1">Metode Bayar:</label>
            <select name="metode_bayar" class="form-select form-select-sm">
                <option value="">-- Semua --</option>
                <option value="Tunai" <?= $metode_bayar === 'Tunai' ? 'selected' : '' ?>>Tunai</option>
                <option value="QRIS" <?= $metode_bayar === 'QRIS' ? 'selected' : '' ?>>QRIS</option>
                <option value="Transfer" <?= $metode_bayar === 'Transfer' ? 'selected' : '' ?>>Transfer</option>
                <option value="Debit" <?= $metode_bayar === 'Debit' ? 'selected' : '' ?>>Debit</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small fw-bold mb-1">Jenis Pembeli:</label>
            <select name="jenis_pembeli" class="form-select form-select-sm">
                <option value="">-- Semua Pembeli --</option>
                <option value="umum" <?= $jenis_pembeli === 'umum' ? 'selected' : '' ?>>Pelanggan Umum</option>
                <option value="member" <?= $jenis_pembeli === 'member' ? 'selected' : '' ?>>Member Terdaftar</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <a href="<?= base_url('penjualan') ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabel Daftar Faktur Penjualan -->
<div class="card-modern p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-primary"></i> Data Transaksi Faktur Penjualan
            </h6>
            <span class="text-muted" style="font-size: 0.74rem;">Klik tombol <strong>+</strong> atau nomor faktur untuk membuka / menutup rincian item produk terjual secara instan.</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" id="btn-expand-all" title="Buka seluruh data grid detail item pada tabel ini">
                <i class="bi bi-arrows-expand me-1"></i> Buka Semua Detail
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" id="btn-collapse-all" title="Tutup seluruh data grid detail item pada tabel ini">
                <i class="bi bi-arrows-collapse me-1"></i> Tutup Semua
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-penjualan">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 36px;" class="text-center no-sort" title="Buka/Tutup Rincian Item">#</th>
                    <th>No Faktur</th>
                    <th>Waktu Transaksi</th>
                    <th>Kasir</th>
                    <th>Pembeli</th>
                    <th>Metode</th>
                    <th class="text-center">Item (Qty)</th>
                    <th class="text-end">Diskon / Potongan</th>
                    <th class="text-end">Grand Total</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $p): ?>
                        <tr data-nofaktur="<?= esc($p['no_faktur']) ?>">
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-toggle-child" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Klik untuk Expand / Collapse Rincian Item" style="font-size: 0.72rem; border-radius: 4px;">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </td>
                            <td class="fw-bold font-monospace text-primary">
                                <a href="javascript:void(0)" class="text-decoration-none text-primary btn-toggle-child-link d-inline-flex align-items-center gap-1" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Klik untuk Expand / Collapse Rincian Item">
                                    <i class="bi bi-receipt"></i><span><?= esc($p['no_faktur']) ?></span>
                                </a>
                            </td>
                            <td class="small" data-order="<?= strtotime($p['tanggal']) * 1000000 + ($p['id'] % 1000000) ?>"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($p['kode_user']) ?></span></td>
                            <td>
                                <?php if (!empty($p['kode_member'])): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="bi bi-star-fill me-1"></i> Member: <?= esc($p['kode_member']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Umum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <?= esc($p['metode_bayar'] ?: 'Tunai') ?>
                                </span>
                            </td>
                            <td class="text-center fw-semibold">
                                <?= number_format($p['total_item'], 0, ',', '.') ?> macam (<?= number_format($p['total_qty'], 0, ',', '.') ?>)
                            </td>
                            <td class="text-end small">
                                <?php 
                                    $potongan = (float)($p['potongan_voucher'] ?? 0) + (float)($p['potongan_tukar_jerigen'] ?? 0) + (float)($p['diskon'] ?? 0);
                                    if ($potongan > 0): 
                                ?>
                                    <span class="text-danger fw-bold">- Rp <?= number_format($potongan, 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">
                                Rp <?= number_format($p['grand_total'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-detail-faktur" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Lihat Rincian Item (Popup Modal)">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-cetak-struk-ulang" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Cetak Ulang Struk Thermal">
                                        <i class="bi bi-printer-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-hapus-faktur" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Hapus Faktur (Wajib Password Super Admin)">
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

<!-- MODAL DETAIL FAKTUR PENJUALAN -->
<div class="modal fade" id="modalDetailPenjualan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Rincian Faktur: <span id="dtl-faktur-nofaktur"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 p-3 rounded-3 mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Waktu:</span>
                        <strong class="text-dark small" id="dtl-faktur-tgl"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Pelanggan:</span>
                        <span id="dtl-faktur-pembeli"></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Kasir:</span>
                        <strong class="text-dark small" id="dtl-faktur-kasir"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Metode Bayar:</span>
                        <strong class="text-info small" id="dtl-faktur-metode"></strong>
                    </div>
                </div>

                <!-- Tabel Item Produk -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 45px;" class="text-center">No</th>
                                <th>Produk Sabun / Parfum</th>
                                <th class="text-center" style="width: 75px;">Qty</th>
                                <th class="text-end" style="width: 140px; white-space: nowrap;">Harga Satuan</th>
                                <th class="text-end" style="width: 160px; white-space: nowrap;">Subtotal (Rp)</th>
                                <th class="text-center" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dtl-faktur-items-tbody"></tbody>
                        <tfoot class="table-light small">
                            <tr>
                                <td colspan="4" class="text-end fw-semibold text-muted">Subtotal Produk:</td>
                                <td colspan="2" class="text-end fw-bold text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-faktur-subtotal">Rp 0</td>
                            </tr>
                            <tr id="dtl-row-voucher" class="d-none text-danger">
                                <td colspan="4" class="text-end">Potongan Voucher Member (<span id="dtl-kode-voucher"></span>):</td>
                                <td colspan="2" class="text-end fw-bold text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-potongan-voucher">- Rp 0</td>
                            </tr>
                            <tr id="dtl-row-jerigen" class="d-none text-success">
                                <td colspan="4" class="text-end">Kompensasi Tukar Jerigen Kosong:</td>
                                <td colspan="2" class="text-end fw-bold text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-potongan-jerigen">- Rp 0</td>
                            </tr>
                            <tr class="fs-6 fw-bold table-primary">
                                <td colspan="4" class="text-end text-primary">GRAND TOTAL:</td>
                                <td colspan="2" class="text-end text-primary text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-faktur-grandtotal">Rp 0</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted">Jumlah Bayar Diterima:</td>
                                <td colspan="2" class="text-end text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-faktur-bayar">Rp 0</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted">Uang Kembalian:</td>
                                <td colspan="2" class="text-end text-nowrap px-3" style="white-space: nowrap !important; min-width: 180px;" id="dtl-faktur-kembalian">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-success rounded-pill px-4 fw-bold" id="btn-cetak-dari-modal">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Ulang Struk Thermal
                </button>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let activeFaktur = '';
    let dtPenjualan = null;
    const cacheDetailPenjualan = {};
    const isAdminUser = <?= (session()->get('role') === 'admin') ? 'true' : 'false' ?>;

    /**
     * Render HTML Child Row Data Grid untuk Rincian Faktur Penjualan
     */
    function formatChildRowPenjualan(noFaktur, data) {
        const p = data.penjualan;
        const m = data.member;
        const items = data.items || [];

        let rowsHtml = '';
        items.forEach((it, idx) => {
            let colProfit = '';
            if (isAdminUser && typeof it.profit !== 'undefined') {
                colProfit = `<td class="text-end text-success font-monospace" style="font-size: 0.78rem;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(it.profit)}</td>`;
            }

            rowsHtml += `
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.75rem;">${idx + 1}</td>
                    <td class="font-monospace text-muted" style="font-size: 0.75rem;">${it.id_barang}</td>
                    <td class="fw-semibold text-dark" style="font-size: 0.8rem;">
                        ${it.nama_barang}
                        ${it.satuan ? `<span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">${it.satuan}</span>` : ''}
                    </td>
                    <td class="text-center fw-bold text-primary" style="font-size: 0.8rem;">${new Intl.NumberFormat('id-ID').format(it.qty)}</td>
                    <td class="text-end text-nowrap" style="font-size: 0.8rem;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(it.harga_jual)}</td>
                    <td class="text-end text-nowrap fw-bold text-dark" style="font-size: 0.8rem;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(it.subtotal)}</td>
                    ${colProfit}
                </tr>
            `;
        });

        const headerProfit = isAdminUser ? `<th class="text-end" style="width: 120px;">Est. Margin</th>` : '';

        const badgeList = [];
        if (parseFloat(p.potongan_voucher) > 0) {
            badgeList.push(`<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-tag-fill me-1"></i>Voucher: -Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.potongan_voucher)}</span>`);
        }
        if (parseFloat(p.potongan_tukar_jerigen) > 0) {
            badgeList.push(`<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-recycle me-1"></i>Tukar Jerigen: -Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.potongan_tukar_jerigen)}</span>`);
        }
        if (parseFloat(p.diskon) > 0) {
            badgeList.push(`<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-percent me-1"></i>Diskon: -Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.diskon)}</span>`);
        }

        return `
            <div class="child-detail-wrapper p-3 my-1 rounded-3 shadow-sm" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-left: 5px solid #0284c7 !important;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-primary px-2 py-1"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Data Grid Rincian Faktur: ${noFaktur}</span>
                        <span class="small text-muted"><i class="bi bi-clock me-1"></i>${p.tanggal}</span>
                        <span class="small text-muted"><i class="bi bi-person me-1"></i>Kasir: <strong>${p.kode_user || '-'}</strong></span>
                        ${m ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-star-fill me-1"></i>Member: ${m.nama} (${m.kode_member})</span>` : ''}
                        <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-wallet2 me-1"></i>${p.metode_bayar || 'Tunai'}</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        ${badgeList.join(' ')}
                        <span class="small fw-semibold text-muted">Subtotal: Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.subtotal)}</span>
                        <span class="badge bg-success font-monospace px-2 py-1" style="font-size: 0.85rem;">Grand Total: Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.grand_total)}</span>
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 btn-cetak-struk-ulang" data-nofaktur="${noFaktur}" title="Cetak Ulang Struk Thermal">
                            <i class="bi bi-printer-fill me-1"></i> Cetak Struk
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-detail-faktur" data-nofaktur="${noFaktur}" title="Buka Detail di Popup Modal">
                            <i class="bi bi-arrows-angle-expand me-1"></i> Modal
                        </button>
                    </div>
                </div>
                <div class="table-responsive rounded border bg-white shadow-sm">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase" style="font-size: 0.72rem;">
                            <tr>
                                <th style="width: 35px;" class="text-center">No</th>
                                <th style="width: 120px;">ID Barang</th>
                                <th>Nama Produk Sabun & Parfum</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th class="text-end" style="width: 130px;">Harga Satuan</th>
                                <th class="text-end" style="width: 140px;">Subtotal</th>
                                ${headerProfit}
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml || '<tr><td colspan="7" class="text-center text-muted py-2 small">Tidak ada data item produk.</td></tr>'}
                        </tbody>
                        <tfoot class="table-light small">
                            <tr>
                                <td colspan="3" class="text-muted fw-semibold">Total: ${items.length} Macam Barang</td>
                                <td class="text-center fw-bold text-primary font-monospace">${new Intl.NumberFormat('id-ID').format(p.total_qty || 0)}</td>
                                <td class="text-end text-muted fw-bold">TOTAL AKHIR:</td>
                                <td class="text-end fw-bold text-success font-monospace">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.grand_total)}</td>
                                ${isAdminUser ? '<td></td>' : ''}
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
    }

    /**
     * Handler Toggle Child Row (Expand / Collapse)
     */
    function toggleRowChildPenjualan(tr, noFaktur, btn) {
        if (!dtPenjualan) return;
        const row = dtPenjualan.row(tr);
        const $icon = btn.find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown table-active');
            $icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
            btn.removeClass('btn-primary text-white').addClass('btn-outline-primary');
        } else {
            // Placeholder saat memuat data via AJAX
            const loadingHtml = `
                <div class="p-3 text-center text-muted bg-light rounded border my-1">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span class="small font-monospace">Memuat data grid rincian faktur <strong>${noFaktur}</strong>...</span>
                </div>
            `;
            row.child(loadingHtml).show();
            tr.addClass('shown table-active');
            $icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');
            btn.removeClass('btn-outline-primary').addClass('btn-primary text-white');

            if (cacheDetailPenjualan[noFaktur]) {
                row.child(formatChildRowPenjualan(noFaktur, cacheDetailPenjualan[noFaktur])).show();
            } else {
                $.get('<?= base_url('penjualan/detail') ?>/' + encodeURIComponent(noFaktur), function(res) {
                    if (res.status === 'success') {
                        cacheDetailPenjualan[noFaktur] = res;
                        row.child(formatChildRowPenjualan(noFaktur, res)).show();
                    } else {
                        row.child('<div class="p-2 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Gagal memuat rincian: ' + (res.message || 'Error') + '</div>').show();
                    }
                }).fail(function() {
                    row.child('<div class="p-2 text-danger small"><i class="bi bi-x-circle me-1"></i>Koneksi terputus saat mengambil rincian faktur.</div>').show();
                });
            }
        }
    }

    $(document).ready(function() {
        if ($.fn.DataTable) {
            dtPenjualan = $('#table-penjualan').DataTable({
                language: {
                    search: "Cari Faktur / Kasir:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ faktur",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada transaksi penjualan pada periode ini."
                },
                order: [[2, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 9] }
                ]
            });
        }

        // Event Klik Tombol Chevron Toggle Child Row
        $(document).on('click', '.btn-toggle-child', function(e) {
            e.stopPropagation();
            const tr = $(this).closest('tr');
            const noFaktur = $(this).data('nofaktur');
            toggleRowChildPenjualan(tr, noFaktur, $(this));
        });

        // Event Klik Link Nomor Faktur untuk Expand / Collapse
        $(document).on('click', '.btn-toggle-child-link', function(e) {
            e.preventDefault();
            const tr = $(this).closest('tr');
            const noFaktur = $(this).data('nofaktur');
            const btn = tr.find('.btn-toggle-child');
            toggleRowChildPenjualan(tr, noFaktur, btn);
        });

        // Event Buka Semua Detail
        $('#btn-expand-all').on('click', function() {
            if (!dtPenjualan) return;
            $('#table-penjualan tbody tr[data-nofaktur]').each(function() {
                const tr = $(this);
                const noFaktur = tr.data('nofaktur');
                const btn = tr.find('.btn-toggle-child');
                const row = dtPenjualan.row(tr);
                if (!row.child.isShown()) {
                    toggleRowChildPenjualan(tr, noFaktur, btn);
                }
            });
        });

        // Event Tutup Semua Detail
        $('#btn-collapse-all').on('click', function() {
            if (!dtPenjualan) return;
            $('#table-penjualan tbody tr[data-nofaktur]').each(function() {
                const tr = $(this);
                const noFaktur = tr.data('nofaktur');
                const btn = tr.find('.btn-toggle-child');
                const row = dtPenjualan.row(tr);
                if (row.child.isShown()) {
                    toggleRowChildPenjualan(tr, noFaktur, btn);
                }
            });
        });

        // 1. Lihat Detail Faktur Penjualan (Popup Modal)
        $(document).on('click', '.btn-detail-faktur', function() {
            const noFaktur = $(this).data('nofaktur');
            activeFaktur = noFaktur;

            $.get('<?= base_url('penjualan/detail') ?>/' + noFaktur, function(res) {
                if (res.status === 'success') {
                    const p = res.penjualan;
                    const m = res.member;

                    $('#dtl-faktur-nofaktur').text(p.no_faktur);
                    $('#dtl-faktur-tgl').text(p.tanggal);
                    $('#dtl-faktur-kasir').text(p.kode_user || '-');
                    $('#dtl-faktur-metode').text(p.metode_bayar || 'Tunai');

                    if (m) {
                        $('#dtl-faktur-pembeli').html(`<strong class="text-warning-emphasis">${m.nama}</strong> <small class="text-muted font-monospace d-block">${m.kode_member}</small>`);
                    } else if (p.kode_member) {
                        $('#dtl-faktur-pembeli').html(`<strong class="text-warning-emphasis">Member: ${p.kode_member}</strong>`);
                    } else {
                        $('#dtl-faktur-pembeli').html(`<span class="badge bg-light text-muted border">Umum</span>`);
                    }

                    const $tbody = $('#dtl-faktur-items-tbody');
                    $tbody.empty();

                    res.items.forEach((item, idx) => {
                        $tbody.append(`
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>
                                    <div class="fw-semibold text-dark">${item.nama_barang}</div>
                                    <small class="text-muted font-monospace">${item.id_barang}</small>
                                </td>
                                <td class="text-center fw-bold">${new Intl.NumberFormat('id-ID').format(item.qty)}</td>
                                <td class="text-end text-nowrap" style="white-space: nowrap !important;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(item.harga_jual)}</td>
                                <td class="text-end fw-bold text-dark text-nowrap" style="white-space: nowrap !important;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1 btn-hapus-item-faktur"
                                        data-id="${item.id}"
                                        data-nofaktur="${p.no_faktur}"
                                        data-namabarang="${item.nama_barang}"
                                        data-qty="${item.qty}"
                                        title="Hapus Item ini & Kembalikan Stok">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });

                    $('#dtl-faktur-subtotal').html('Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.subtotal));

                    if (parseFloat(p.potongan_voucher) > 0) {
                        $('#dtl-row-voucher').removeClass('d-none');
                        $('#dtl-kode-voucher').text(p.kode_voucher || 'Voucher');
                        $('#dtl-potongan-voucher').html('- Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.potongan_voucher));
                    } else {
                        $('#dtl-row-voucher').addClass('d-none');
                    }

                    if (parseFloat(p.potongan_tukar_jerigen) > 0) {
                        $('#dtl-row-jerigen').removeClass('d-none');
                        $('#dtl-potongan-jerigen').html('- Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.potongan_tukar_jerigen));
                    } else {
                        $('#dtl-row-jerigen').addClass('d-none');
                    }

                    $('#dtl-faktur-grandtotal').html('Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.grand_total));
                    $('#dtl-faktur-bayar').html('Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.bayar));
                    $('#dtl-faktur-kembalian').html('Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(p.kembalian));

                    $('#modalDetailPenjualan').modal('show');
                } else {
                    Swal.fire('Gagal', res.message || 'Tidak dapat memuat detail faktur.', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error');
            });
        });

        // 2. Cetak Struk Ulang
        $(document).on('click', '.btn-cetak-struk-ulang', function() {
            const noFaktur = $(this).data('nofaktur');
            window.open('<?= base_url('pos/cetak-struk') ?>/' + noFaktur, '_blank', 'width=420,height=600');
        });

        $('#btn-cetak-dari-modal').on('click', function() {
            if (activeFaktur) {
                window.open('<?= base_url('pos/cetak-struk') ?>/' + activeFaktur, '_blank', 'width=420,height=600');
            }
        });

        // 3. Hapus Faktur Penjualan
        const currentUserRole = '<?= session()->get('role') ?>';

        $(document).on('click', '.btn-hapus-faktur', function() {
            const noFaktur = $(this).data('nofaktur');

            if (currentUserRole === 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Faktur',
                    html: `
                        <p class="text-danger small mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Anda login sebagai <strong>Administrator</strong>. Menghapus faktur <strong>${noFaktur}</strong> akan <strong>mengembalikan stok barang kembali ke toko</strong>, memulihkan saldo poin member / voucher yang terpakai, dan membatalkan mutasi kas terkait.
                        </p>
                        <p class="small text-muted mb-0">Apakah Anda yakin ingin melanjutkan?</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Faktur',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusFaktur(noFaktur, '');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Otorisasi Super Admin',
                    html: `
                        <p class="text-danger small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Menghapus faktur <strong>${noFaktur}</strong> akan <strong>mengembalikan stok barang kembali ke toko</strong> dan memulihkan poin member.
                        </p>
                        <label class="form-label small fw-bold d-block text-start">Masukkan Password Super Admin:</label>
                        <input type="password" id="swal-admin-penjualan-pw" class="form-control" placeholder="Password Admin...">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Otorisasi & Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pw = document.getElementById('swal-admin-penjualan-pw').value;
                        if (!pw) {
                            Swal.showValidationMessage('Password Super Admin wajib diisi!');
                            return false;
                        }
                        return pw;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusFaktur(noFaktur, result.value);
                    }
                });
            }
        });

        function eksekusiHapusFaktur(noFaktur, passwordAdmin) {
            Swal.fire({
                title: 'Memproses Pembatalan Faktur...',
                text: 'Mengembalikan stok barang dan menyesuaikan data pembukuan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('penjualan/hapus') ?>/' + noFaktur, { password_admin: passwordAdmin }, function(res) {
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
        // 4. Hapus Per Item Dari Faktur Penjualan (Kembalikan Stok Toko)
        $(document).on('click', '.btn-hapus-item-faktur', function() {
            const id = $(this).data('id');
            const noFaktur = $(this).data('nofaktur');
            const namaBarang = $(this).data('namabarang');
            const qty = $(this).data('qty');

            if (currentUserRole === 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Item',
                    html: `
                        <p class="text-danger small mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Anda login sebagai <strong>Administrator</strong>.
                        </p>
                        <p class="small mb-1">Hapus produk <strong>${namaBarang}</strong> (${qty} pcs) dari faktur <strong>${noFaktur}</strong>?</p>
                        <p class="small text-muted mb-0">Stok toko akan otomatis dikembalikan dan nilai total faktur dihitung ulang.</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Item',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusItemFaktur(id, noFaktur, '');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Otorisasi Super Admin',
                    html: `
                        <p class="text-danger small mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Menghapus item produk <strong>${namaBarang}</strong> (${qty} pcs) dari faktur <strong>${noFaktur}</strong> memerlukan izin Super Admin.
                        </p>
                        <label class="form-label small fw-bold d-block text-start">Masukkan Password Super Admin:</label>
                        <input type="password" id="swal-admin-item-pw" class="form-control" placeholder="Password Admin...">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Otorisasi & Hapus Item',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pw = document.getElementById('swal-admin-item-pw').value;
                        if (!pw) {
                            Swal.showValidationMessage('Password Super Admin wajib diisi!');
                            return false;
                        }
                        return pw;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusItemFaktur(id, noFaktur, result.value);
                    }
                });
            }
        });

        function eksekusiHapusItemFaktur(id, noFaktur, passwordAdmin) {
            Swal.fire({
                title: 'Menghapus Item...',
                text: 'Mengembalikan stok barang dan menghitung ulang faktur...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('penjualan/hapus-item') ?>/' + id, { password_admin: passwordAdmin }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Item Berhasil Dihapus!', res.message, 'success').then(() => {
                        if (res.is_faktur_deleted) {
                            $('#modalDetailPenjualan').modal('hide');
                            window.location.reload();
                        } else {
                            // Muat ulang isi modal faktur dan perbarui tabel utama
                            window.location.reload();
                        }
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

