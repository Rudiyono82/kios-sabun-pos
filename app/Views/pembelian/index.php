<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-truck text-primary me-2"></i>Daftar Pembelian Supplier & Restock</h4>
        <p class="text-muted small mb-0">Riwayat faktur pembelian barang masuk dari supplier dengan rincian item barang dan otorisasi hapus transaksi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('laporan/pembelian') ?>" class="btn btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Laporan &amp; Export
        </a>
        <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-circle-fill me-2"></i> Input Pembelian Supplier Baru
        </a>
    </div>
</div>

<!-- Card Ringkasan Metrik -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Biaya Pembelian</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary">Rp <?= number_format($total_biaya, 0, ',', '.') ?></h3>
                    <small class="text-muted">Periode filter terpilih</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Jumlah Nota Pembelian</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success"><?= number_format($total_nota, 0, ',', '.') ?> Nota</h3>
                    <small class="text-muted">Faktur restock tersimpan</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card-modern p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Supplier Terdaftar</span>
                    <h3 class="fw-bold mt-1 mb-0 text-info"><?= count($suppliers) ?> Mitra</h3>
                    <small class="text-muted">Distributor & produsen aktif</small>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bi bi-buildings fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Pembelian -->
<div class="card-modern p-3 mb-4">
    <form action="<?= base_url('pembelian') ?>" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1">Dari Tanggal:</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= esc($tgl_mulai) ?>">
        </div>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold mb-1">Sampai Tanggal:</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= esc($tgl_selesai) ?>">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Filter Supplier:</label>
            <select name="kode_supplier" class="form-select form-select-sm">
                <option value="">-- Semua Supplier Mitra --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= esc($s['kode_supplier']) ?>" <?= $kode_supplier_selected === $s['kode_supplier'] ? 'selected' : '' ?>>
                        <?= esc($s['nama_supplier']) ?> (<?= esc($s['kode_supplier']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Status Pembayaran:</label>
            <select name="status_bayar" class="form-select form-select-sm">
                <option value="">-- Semua Status --</option>
                <option value="Lunas" <?= ($status_bayar_selected ?? '') === 'Lunas' ? 'selected' : '' ?>>Lunas (Tunai - Potong Kas)</option>
                <option value="Lunas (Transfer)" <?= ($status_bayar_selected ?? '') === 'Lunas (Transfer)' ? 'selected' : '' ?>>Lunas (Transfer - Tidak Potong Kas)</option>
                <option value="Lunas (Tidak Potong Kas)" <?= ($status_bayar_selected ?? '') === 'Lunas (Tidak Potong Kas)' ? 'selected' : '' ?>>Lunas (Non-Kas / Tidak Potong Kas)</option>
                <option value="Hutang" <?= ($status_bayar_selected ?? '') === 'Hutang' ? 'selected' : '' ?>>Hutang / Tempo</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold d-inline-flex align-items-center justify-content-center" style="height: 31px;">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <a href="<?= base_url('pembelian') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center px-2 flex-shrink-0" title="Reset Filter (Awal Bulan s/d Hari Ini)" style="height: 31px; width: 36px;">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>

        <!-- Tombol Pintas Preset Periode -->
        <div class="col-12 d-flex flex-wrap align-items-center gap-1 mt-2 pt-2 border-top">
            <span class="small text-muted me-2 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pintas Cepat:</span>
            <a href="<?= base_url('pembelian?tgl_mulai=' . date('Y-m-01') . '&tgl_selesai=' . date('Y-m-d')) ?>" class="btn btn-xs rounded-pill px-2 py-1 <?= ($tgl_mulai === date('Y-m-01') && $tgl_selesai === date('Y-m-d')) ? 'btn-primary fw-bold' : 'btn-outline-secondary' ?>">Bulan Ini (<?= date('M Y') ?>)</a>
            <a href="<?= base_url('pembelian?tgl_mulai=' . date('Y-m-d') . '&tgl_selesai=' . date('Y-m-d')) ?>" class="btn btn-xs rounded-pill px-2 py-1 <?= ($tgl_mulai === date('Y-m-d') && $tgl_selesai === date('Y-m-d')) ? 'btn-primary fw-bold' : 'btn-outline-secondary' ?>">Hari Ini</a>
            <a href="<?= base_url('pembelian?tgl_mulai=' . date('Y-m-d', strtotime('-1 day')) . '&tgl_selesai=' . date('Y-m-d')) ?>" class="btn btn-xs rounded-pill px-2 py-1 <?= ($tgl_mulai === date('Y-m-d', strtotime('-1 day')) && $tgl_selesai === date('Y-m-d')) ? 'btn-primary fw-bold' : 'btn-outline-secondary' ?>">2 Hari Terakhir</a>
            <a href="<?= base_url('pembelian?tgl_mulai=' . date('Y-m-d', strtotime('-7 days')) . '&tgl_selesai=' . date('Y-m-d')) ?>" class="btn btn-xs rounded-pill px-2 py-1">7 Hari Terakhir</a>
            <a href="<?= base_url('pembelian?tgl_mulai=' . date('Y-m-01', strtotime('-1 month')) . '&tgl_selesai=' . date('Y-m-t', strtotime('-1 month'))) ?>" class="btn btn-xs rounded-pill px-2 py-1">Bulan Lalu</a>
        </div>
    </form>
</div>

<!-- Tabel Daftar Pembelian -->
<div class="card-modern p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-primary"></i> Data Riwayat Pembelian & Restock
            </h6>
            <span class="text-muted" style="font-size: 0.74rem;">Klik tombol <strong>+</strong> atau nomor nota beli untuk membuka / menutup rincian item barang masuk secara instan.</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" id="btn-expand-all-beli" title="Buka seluruh data grid detail item pada tabel ini">
                <i class="bi bi-arrows-expand me-1"></i> Buka Semua Detail
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" id="btn-collapse-all-beli" title="Tutup seluruh data grid detail item pada tabel ini">
                <i class="bi bi-arrows-collapse me-1"></i> Tutup Semua
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-pembelian">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 36px;" class="text-center no-sort" title="Buka/Tutup Rincian Item">#</th>
                    <th>No Nota Beli</th>
                    <th>Tanggal</th>
                    <th>Supplier Mitra</th>
                    <th>Petugas / User</th>
                    <th>Status Bayar</th>
                    <th class="text-end">Total Biaya (Rp)</th>
                    <th>Catatan</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $r): ?>
                        <tr data-nobeli="<?= esc($r['no_beli']) ?>">
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-toggle-child-beli" data-nobeli="<?= esc($r['no_beli']) ?>" title="Klik untuk Expand / Collapse Rincian Item Restock" style="font-size: 0.72rem; border-radius: 4px;">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </td>
                            <td class="fw-bold font-monospace text-primary">
                                <a href="javascript:void(0)" class="text-decoration-none text-primary btn-toggle-child-beli-link d-inline-flex align-items-center gap-1" data-nobeli="<?= esc($r['no_beli']) ?>" title="Klik untuk Expand / Collapse Rincian Restock">
                                    <i class="bi bi-receipt"></i><span><?= esc($r['no_beli']) ?></span>
                                </a>
                            </td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($r['nama_supplier'] ?: 'Supplier Umum') ?></div>
                                <small class="text-muted font-monospace"><?= esc($r['kode_supplier'] ?: '-') ?></small>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($r['kode_user']) ?></span></td>
                            <td>
                                <?php if ($r['status_bayar'] === 'Lunas (Transfer)' || $r['status_bayar'] === 'Lunas (Tidak Potong Kas)'): ?>
                                    <span class="badge bg-info text-dark rounded-pill">
                                        <i class="bi bi-bank me-1"></i><?= esc($r['status_bayar']) ?>
                                    </span>
                                <?php elseif ($r['status_bayar'] === 'Lunas' || $r['status_bayar'] === 'Lunas (Tunai)'): ?>
                                    <span class="badge bg-success rounded-pill">
                                        <i class="bi bi-cash-stack me-1"></i>Lunas (Tunai)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill">
                                        <i class="bi bi-clock-history me-1"></i><?= esc($r['status_bayar']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">
                                Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted"><?= esc($r['catatan'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-detail-pembelian" data-nobeli="<?= esc($r['no_beli']) ?>" title="Lihat Detail Item (Popup Modal)">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-hapus-pembelian" data-nobeli="<?= esc($r['no_beli']) ?>" title="Hapus (Wajib Password Super Admin)">
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

<!-- MODAL DETAIL ITEM PEMBELIAN -->
<div class="modal fade" id="modalDetailPembelian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt-cutoff me-2"></i>Detail Pembelian: <span id="detail-no-beli"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Header Nota -->
                <div class="row g-3 p-3 rounded-3 mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Tanggal:</span>
                        <strong class="text-dark small" id="detail-tgl"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Supplier:</span>
                        <strong class="text-primary small" id="detail-supplier"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Status Bayar:</span>
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <strong class="text-success small" id="detail-status"></strong>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-bold" id="btn-edit-status-bayar" title="Klik untuk mengubah status pembayaran nota ini" style="font-size: 0.68rem; height: 22px;">
                                <i class="bi bi-pencil-square me-1"></i>Ubah
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="small text-muted d-block">Petugas Kasir:</span>
                        <strong class="text-dark small" id="detail-petugas"></strong>
                    </div>
                </div>

                <!-- Tabel Item Barang -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Barang / Bahan</th>
                                <th class="text-center" style="width: 90px;">Qty Beli</th>
                                <th class="text-end" style="width: 140px;">Harga Beli Satuan</th>
                                <th class="text-end" style="width: 150px;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-tbody"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">TOTAL KESELURUHAN:</td>
                                <td class="text-end text-primary fs-6" id="detail-total-biaya">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-3 p-2 bg-light rounded small text-muted">
                    <strong>Catatan Pembelian:</strong> <span id="detail-catatan">-</span>
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
    let dtPembelian = null;
    const cacheDetailPembelian = {};

    /**
     * Render HTML Child Row Data Grid untuk Rincian Nota Pembelian
     */
    function formatChildRowPembelian(noBeli, data) {
        const p = data.pembelian;
        const items = data.items || [];

        let rowsHtml = '';
        items.forEach((it, idx) => {
            rowsHtml += `
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.75rem;">${idx + 1}</td>
                    <td class="font-monospace text-muted" style="font-size: 0.75rem;">${it.id_barang}</td>
                    <td class="fw-semibold text-dark" style="font-size: 0.8rem;">${it.nama_barang}</td>
                    <td class="text-center fw-bold text-primary" style="font-size: 0.8rem;">${new Intl.NumberFormat('id-ID').format(it.qty)}</td>
                    <td class="text-end text-nowrap" style="font-size: 0.8rem;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(it.harga_beli)}</td>
                    <td class="text-end text-nowrap fw-bold text-dark" style="font-size: 0.8rem;">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(it.subtotal)}</td>
                </tr>
            `;
        });

        let statusBadge = '';
        if (p.status_bayar === 'Lunas (Transfer)') {
            statusBadge = '<span class="badge bg-info text-dark rounded-pill"><i class="bi bi-bank me-1"></i>Lunas (Transfer)</span>';
        } else if (p.status_bayar === 'Lunas (Tidak Potong Kas)') {
            statusBadge = '<span class="badge bg-info text-dark rounded-pill"><i class="bi bi-wallet2 me-1"></i>Lunas (Tidak Potong Kas)</span>';
        } else if (p.status_bayar === 'Lunas' || p.status_bayar === 'Lunas (Tunai)') {
            statusBadge = '<span class="badge bg-success rounded-pill"><i class="bi bi-cash-stack me-1"></i>Lunas (Tunai)</span>';
        } else {
            statusBadge = '<span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-clock-history me-1"></i>' + (p.status_bayar || 'Hutang / Tempo') + '</span>';
        }

        return `
            <div class="child-detail-wrapper p-3 my-1 rounded-3 shadow-sm" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-left: 5px solid #059669 !important;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-success px-2 py-1"><i class="bi bi-truck me-1"></i> Data Grid Restock: ${noBeli}</span>
                        <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>${p.tanggal}</span>
                        <span class="small text-muted"><i class="bi bi-person me-1"></i>Petugas: <strong>${p.kode_user || '-'}</strong></span>
                        <span class="small text-dark fw-bold"><i class="bi bi-buildings me-1 text-primary"></i>${p.nama_supplier || 'Supplier Umum'}</span>
                        ${statusBadge}
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="small text-muted">${p.catatan ? 'Catatan: ' + p.catatan : ''}</span>
                        <span class="badge bg-primary font-monospace px-2 py-1" style="font-size: 0.85rem;">Total Biaya: Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.total_biaya)}</span>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-detail-pembelian" data-nobeli="${noBeli}" title="Buka Detail di Popup Modal">
                            <i class="bi bi-arrows-angle-expand me-1"></i> Modal Detail
                        </button>
                    </div>
                </div>
                <div class="table-responsive rounded border bg-white shadow-sm">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase" style="font-size: 0.72rem;">
                            <tr>
                                <th style="width: 35px;" class="text-center">No</th>
                                <th style="width: 120px;">ID Barang</th>
                                <th>Nama Barang Masuk / Bahan Baku</th>
                                <th class="text-center" style="width: 80px;">Qty Beli</th>
                                <th class="text-end" style="width: 140px;">Harga Beli Satuan</th>
                                <th class="text-end" style="width: 150px;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml || '<tr><td colspan="6" class="text-center text-muted py-2 small">Tidak ada data item pembelian.</td></tr>'}
                        </tbody>
                        <tfoot class="table-light small">
                            <tr>
                                <td colspan="3" class="text-muted fw-semibold">Total: ${items.length} Item Bahan / Produk</td>
                                <td colspan="2" class="text-end text-muted fw-bold">TOTAL KESELURUHAN:</td>
                                <td class="text-end fw-bold text-success font-monospace">Rp&nbsp;${new Intl.NumberFormat('id-ID').format(p.total_biaya)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
    }

    /**
     * Handler Toggle Child Row Pembelian (Expand / Collapse)
     */
    function toggleRowChildPembelian(tr, noBeli, btn) {
        if (!dtPembelian) return;
        const row = dtPembelian.row(tr);
        const $icon = btn.find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown table-active');
            $icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
            btn.removeClass('btn-primary text-white').addClass('btn-outline-primary');
        } else {
            const loadingHtml = `
                <div class="p-3 text-center text-muted bg-light rounded border my-1">
                    <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                    <span class="small font-monospace">Memuat data grid rincian restock <strong>${noBeli}</strong>...</span>
                </div>
            `;
            row.child(loadingHtml).show();
            tr.addClass('shown table-active');
            $icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');
            btn.removeClass('btn-outline-primary').addClass('btn-primary text-white');

            if (cacheDetailPembelian[noBeli]) {
                row.child(formatChildRowPembelian(noBeli, cacheDetailPembelian[noBeli])).show();
            } else {
                $.get('<?= base_url('pembelian/detail') ?>/' + encodeURIComponent(noBeli), function(res) {
                    if (res.status === 'success') {
                        cacheDetailPembelian[noBeli] = res;
                        row.child(formatChildRowPembelian(noBeli, res)).show();
                    } else {
                        row.child('<div class="p-2 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Gagal memuat rincian pembelian: ' + (res.message || 'Error') + '</div>').show();
                    }
                }).fail(function() {
                    row.child('<div class="p-2 text-danger small"><i class="bi bi-x-circle me-1"></i>Koneksi terputus saat mengambil rincian pembelian.</div>').show();
                });
            }
        }
    }

    $(document).ready(function() {
        if ($.fn.DataTable) {
            dtPembelian = $('#table-pembelian').DataTable({
                language: {
                    search: "Cari Nota:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ pembelian",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada transaksi pembelian pada periode ini."
                },
                order: [[2, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 8] }
                ]
            });
        }

        // Event Klik Tombol Chevron Toggle Child Row Pembelian
        $(document).on('click', '.btn-toggle-child-beli', function(e) {
            e.stopPropagation();
            const tr = $(this).closest('tr');
            const noBeli = $(this).data('nobeli');
            toggleRowChildPembelian(tr, noBeli, $(this));
        });

        // Event Klik Link Nomor Nota untuk Expand / Collapse
        $(document).on('click', '.btn-toggle-child-beli-link', function(e) {
            e.preventDefault();
            const tr = $(this).closest('tr');
            const noBeli = $(this).data('nobeli');
            const btn = tr.find('.btn-toggle-child-beli');
            toggleRowChildPembelian(tr, noBeli, btn);
        });

        // Event Buka Semua Detail Pembelian
        $('#btn-expand-all-beli').on('click', function() {
            if (!dtPembelian) return;
            $('#table-pembelian tbody tr[data-nobeli]').each(function() {
                const tr = $(this);
                const noBeli = tr.data('nobeli');
                const btn = tr.find('.btn-toggle-child-beli');
                const row = dtPembelian.row(tr);
                if (!row.child.isShown()) {
                    toggleRowChildPembelian(tr, noBeli, btn);
                }
            });
        });

        // Event Tutup Semua Detail Pembelian
        $('#btn-collapse-all-beli').on('click', function() {
            if (!dtPembelian) return;
            $('#table-pembelian tbody tr[data-nobeli]').each(function() {
                const tr = $(this);
                const noBeli = tr.data('nobeli');
                const btn = tr.find('.btn-toggle-child-beli');
                const row = dtPembelian.row(tr);
                if (row.child.isShown()) {
                    toggleRowChildPembelian(tr, noBeli, btn);
                }
            });
        });

        // 1. Klik Lihat Detail Pembelian (Popup Modal)
        $(document).on('click', '.btn-detail-pembelian', function() {
            const noBeli = $(this).data('nobeli');
            
            $.get('<?= base_url('pembelian/detail') ?>/' + noBeli, function(res) {
                if (res.status === 'success') {
                    const p = res.pembelian;
                    $('#detail-no-beli').text(p.no_beli);
                    $('#detail-tgl').text(p.tanggal);
                    $('#detail-supplier').text(p.nama_supplier || 'Supplier Umum');
                    if (p.status_bayar === 'Lunas (Transfer)') {
                        $('#detail-status').attr('class', 'text-info small fw-bold').html('<i class="bi bi-bank me-1"></i>Lunas (Transfer)');
                    } else if (p.status_bayar === 'Lunas (Tidak Potong Kas)') {
                        $('#detail-status').attr('class', 'text-info small fw-bold').html('<i class="bi bi-wallet2 me-1"></i>Lunas (Tidak Potong Kas)');
                    } else if (p.status_bayar === 'Lunas' || p.status_bayar === 'Lunas (Tunai)') {
                        $('#detail-status').attr('class', 'text-success small fw-bold').html('<i class="bi bi-cash-stack me-1"></i>Lunas (Tunai)');
                    } else {
                        $('#detail-status').attr('class', 'text-warning text-dark small fw-bold').html('<i class="bi bi-clock-history me-1"></i>' + (p.status_bayar || 'Hutang / Tempo'));
                    }
                    $('#btn-edit-status-bayar').data('nobeli', p.no_beli).data('status', p.status_bayar).data('tanggal', p.tanggal);
                    $('#detail-petugas').text(p.kode_user);
                    $('#detail-catatan').text(p.catatan || 'Tidak ada catatan.');
                    $('#detail-total-biaya').text('Rp ' + new Intl.NumberFormat('id-ID').format(p.total_biaya));

                    const $tbody = $('#detail-items-tbody');
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
                                <td class="text-end">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_beli)}</td>
                                <td class="text-end fw-bold text-dark">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                            </tr>
                        `);
                    });

                    $('#modalDetailPembelian').modal('show');
                } else {
                    Swal.fire('Gagal', res.message || 'Tidak dapat memuat detail pembelian.', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error');
            });
        });

        // 2. Klik Ubah Status Pembayaran
        $(document).on('click', '#btn-edit-status-bayar', function() {
            const noBeli = $(this).data('nobeli');
            const currentStatus = $(this).data('status') || 'Lunas';
            const tglNotaRaw = $(this).data('tanggal') || '';
            const tglNota = tglNotaRaw ? tglNotaRaw.substring(0, 10) : '';
            const todayStr = '<?= date('Y-m-d') ?>';
            const isPastDate = tglNota && (tglNota < todayStr);
            const isAdmin = <?= (session()->get('role') === 'admin') ? 'true' : 'false' ?>;

            let warningHtml = '';
            if (isPastDate) {
                warningHtml = `
                    <div class="alert alert-warning py-2 px-3 small text-start mb-3 d-flex align-items-center gap-2 border-warning shadow-sm">
                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                        <div>
                            <strong class="text-dark">Nota Tanggal Lampau (${tglNota})</strong><br>
                            <span class="text-muted">Perubahan transaksi hari lampau ${isAdmin ? 'akan dicatat ke Audit Log.' : '<strong class="text-danger">memerlukan Password Otorisasi Admin / Owner!</strong>'}</span>
                        </div>
                    </div>
                `;
            }

            let passwordHtml = '';
            if (isPastDate && !isAdmin) {
                passwordHtml = `
                    <div class="text-start mt-3 p-2 bg-light rounded border border-danger">
                        <label class="small fw-bold text-danger d-block mb-1">
                            <i class="bi bi-key-fill me-1"></i>Password Otorisasi Admin / Owner <span class="text-danger">*</span>:
                        </label>
                        <input type="password" id="swal-password-admin" class="form-control form-control-sm" placeholder="Masukkan password Admin / Owner...">
                        <small class="text-muted" style="font-size: 0.72rem;">Wajib otorisasi Admin untuk mengubah transaksi yang sudah tutup hari.</small>
                    </div>
                `;
            }

            let tglPotongHtml = `
                <div id="wrapper-tgl-potong" class="text-start mt-2" style="${(currentStatus.indexOf('Lunas') !== -1 && currentStatus.indexOf('Transfer') === -1) ? '' : 'display: none;'}">
                    <label class="small fw-bold text-muted mb-1">
                        <i class="bi bi-calendar2-check text-primary me-1"></i>Pemotongan Buku Kas Fisik:
                    </label>
                    <select id="swal-tgl-potong" class="form-select form-select-sm">
                        <option value="hari_ini">Potong Kas Hari Ini (<?= date('d/m/Y') ?>) - Rekomendasi Kas Riil</option>
                        <option value="tgl_nota">Potong Kas di Tanggal Nota (${tglNota}) - Koreksi Riwayat</option>
                    </select>
                </div>
            `;

            Swal.fire({
                title: 'Ubah Status Pembayaran',
                html: `
                    ${warningHtml}
                    <div class="text-start small text-muted mb-2">
                        Pilih status pembayaran baru untuk nota pembelian <strong>${noBeli}</strong>:
                    </div>
                    <select id="swal-select-status" class="form-select form-select-sm fw-bold">
                        <option value="Lunas" ${currentStatus === 'Lunas' || currentStatus === 'Lunas (Tunai)' ? 'selected' : ''}>Lunas (Tunai - Potong Kas Toko)</option>
                        <option value="Lunas (Transfer)" ${currentStatus === 'Lunas (Transfer)' ? 'selected' : ''}>Lunas (Transfer - TIDAK Potong Kas Toko)</option>
                        <option value="Lunas (Tidak Potong Kas)" ${currentStatus === 'Lunas (Tidak Potong Kas)' ? 'selected' : ''}>Lunas (Non-Kas / TIDAK Potong Kas)</option>
                        <option value="Hutang / Tempo" ${currentStatus.indexOf('Hutang') !== -1 || currentStatus.indexOf('Tempo') !== -1 ? 'selected' : ''}>Hutang / Tempo (Kredit)</option>
                    </select>
                    ${tglPotongHtml}
                    <div class="text-start small mt-2 text-danger">
                        <i class="bi bi-info-circle me-1"></i>Jika diubah ke <strong>Transfer / Tidak Potong Kas</strong>, sistem otomatis membatalkan pemotongan buku kas laci.
                    </div>
                    ${passwordHtml}
                `,
                didOpen: () => {
                    $('#swal-select-status').on('change', function() {
                        const val = $(this).val();
                        if (val === 'Lunas') {
                            $('#wrapper-tgl-potong').slideDown(150);
                        } else {
                            $('#wrapper-tgl-potong').slideUp(150);
                        }
                    });
                },
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Simpan Perubahan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#2563eb',
                preConfirm: () => {
                    const selected = $('#swal-select-status').val();
                    if (!selected) {
                        Swal.showValidationMessage('Silakan pilih status pembayaran!');
                        return false;
                    }
                    const pwd = $('#swal-password-admin').length ? $('#swal-password-admin').val() : '';
                    if (isPastDate && !isAdmin && !pwd) {
                        Swal.showValidationMessage('Password otorisasi Admin / Owner wajib diisi!');
                        return false;
                    }
                    const tglPotong = $('#swal-tgl-potong').val() || 'hari_ini';
                    return {
                        status: selected,
                        password_admin: pwd,
                        tgl_potong_kas: tglPotong
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const payload = {
                        no_beli: noBeli,
                        status_bayar: result.value.status,
                        password_admin: result.value.password_admin,
                        tgl_potong_kas: result.value.tgl_potong_kas
                    };
                    $.post('<?= base_url('pembelian/update-status-bayar') ?>', payload, function(resp) {
                        if (resp.status === 'success') {
                            Swal.fire('Berhasil Diperbarui!', resp.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', resp.message || 'Terjadi kesalahan.', 'error');
                        }
                    }).fail(function(xhr) {
                        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal mengubah status bayar.';
                        Swal.fire('Otorisasi Ditolak!', err, 'error');
                    });
                }
            });
        });

        // 3. Klik Hapus Pembelian
        const currentUserRole = '<?= session()->get('role') ?>';

        $(document).on('click', '.btn-hapus-pembelian', function() {
            const noBeli = $(this).data('nobeli');

            // Jika Super Admin, langsung tampilkan konfirmasi tanpa input password
            if (currentUserRole === 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Pembelian',
                    html: `
                        <p class="text-danger small mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Anda login sebagai <strong>Administrator</strong>. Menghapus nota <strong>${noBeli}</strong> akan <strong>mengurangi kembali stok barang</strong> sejumlah item yang dibeli dan membatalkan catatan kas pengeluaran terkait.
                        </p>
                        <p class="small text-muted mb-0">Apakah Anda yakin ingin melanjutkan?</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Pembelian',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusPembelian(noBeli, '');
                    }
                });
            } else {
                // Jika kasir/user lain, wajibkan input password Super Admin
                Swal.fire({
                    title: 'Otorisasi Super Admin',
                    html: `
                        <p class="text-danger small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Menghapus nota <strong>${noBeli}</strong> akan <strong>mengurangi kembali stok barang</strong> sejumlah item yang dibeli.
                        </p>
                        <label class="form-label small fw-bold d-block text-start">Masukkan Password Super Admin:</label>
                        <input type="password" id="swal-admin-password" class="form-control" placeholder="Password Admin...">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Otorisasi & Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pw = document.getElementById('swal-admin-password').value;
                        if (!pw) {
                            Swal.showValidationMessage('Password Super Admin wajib diisi!');
                            return false;
                        }
                        return pw;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusPembelian(noBeli, result.value);
                    }
                });
            }
        });

        function eksekusiHapusPembelian(noBeli, passwordAdmin) {
            Swal.fire({
                title: 'Memproses Pembatalan...',
                text: 'Menyesuaikan kembali stok dan pembukuan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('pembelian/hapus') ?>/' + noBeli, { password_admin: passwordAdmin }, function(res) {
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
