<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-cart-check-fill text-info me-2"></i>Laporan Penjualan per Periode
        </h4>
        <p class="text-muted small mb-0">
            Analisis omzet penjualan kasir, margin profit, volume item terjual, dan rekapitulasi pembayaran per rentang tanggal.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php
            $queryFilter = http_build_query([
                'tgl_mulai'    => $tgl_mulai,
                'tgl_selesai'  => $tgl_selesai,
                'metode_bayar' => $metode_bayar,
                'kasir'        => $kasir,
                'tipe'         => $tipe,
                'q'            => $q
            ]);
        ?>
        <a href="<?= base_url('laporan/penjualan/cetak?' . $queryFilter) ?>" target="_blank" class="btn btn-danger rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </a>
        <a href="<?= base_url('laporan/penjualan/excel?' . $queryFilter) ?>" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- FORM FILTER LAPORAN PENJUALAN                  -->
<!-- ============================================== -->
<div class="card-modern p-4 mb-4 border-top border-4 border-info">
    <form action="<?= base_url('laporan/penjualan') ?>" method="GET" id="form-filter-penjualan">
        <div class="row g-3 align-items-end">
            
            <!-- Tanggal Mulai & Selesai -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-calendar-event me-1 text-primary"></i>Tanggal Mulai:
                </label>
                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control fw-semibold" value="<?= esc($tgl_mulai) ?>">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-calendar-check me-1 text-primary"></i>Tanggal Selesai:
                </label>
                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control fw-semibold" value="<?= esc($tgl_selesai) ?>">
            </div>

            <!-- Tipe Tampilan Laporan -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-layout-text-window-reverse me-1 text-info"></i>Format Rekapitulasi:
                </label>
                <select name="tipe" class="form-select select2 fw-semibold">
                    <option value="transaksi" <?= $tipe === 'transaksi' ? 'selected' : '' ?>>
                        Per Faktur Transaksi (Nota Kasir)
                    </option>
                    <option value="barang" <?= $tipe === 'barang' ? 'selected' : '' ?>>
                        Per Item Produk (Paling Laris / Omzet)
                    </option>
                </select>
            </div>

            <!-- Filter Metode Pembayaran -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Metode Bayar:</label>
                <select name="metode_bayar" class="form-select select2">
                    <option value="semua">-- Semua Metode --</option>
                    <option value="Cash" <?= $metode_bayar === 'Cash' ? 'selected' : '' ?>>Tunai / Cash</option>
                    <option value="QRIS" <?= $metode_bayar === 'QRIS' ? 'selected' : '' ?>>QRIS</option>
                    <option value="Transfer" <?= $metode_bayar === 'Transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                </select>
            </div>

            <!-- Filter Kasir -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Petugas Kasir:</label>
                <select name="kasir" class="form-select select2">
                    <option value="semua">-- Semua Kasir --</option>
                    <?php foreach ($kasir_list as $k): ?>
                        <option value="<?= esc($k['kode_user']) ?>" <?= $kasir === $k['kode_user'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kasir'] ?: $k['kode_user']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kata Kunci Pencarian -->
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold text-muted mb-1">Pencarian Kata Kunci:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="<?= $tipe === 'barang' ? 'Cari nama produk / kode barang...' : 'Cari no. faktur / nama pembeli / kasir...' ?>" value="<?= esc($q) ?>">
                </div>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
                <a href="<?= base_url('laporan/penjualan') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

        </div>

        <!-- Tombol Pintas Preset Periode -->
        <div class="d-flex flex-wrap align-items-center gap-1 mt-3 pt-3 border-top">
            <span class="small text-muted me-2 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Preset Periode:</span>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-preset" data-preset="hari-ini">Hari Ini</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-preset" data-preset="kemarin">Kemarin</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-preset" data-preset="7-hari">7 Hari Terakhir</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-preset" data-preset="bulan-ini">Bulan Ini</button>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 btn-preset" data-preset="bulan-lalu">Bulan Lalu</button>
        </div>
    </form>
</div>

<!-- ============================================== -->
<!-- METRICS / KPI SUMMARY                          -->
<!-- ============================================== -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="text-muted small fw-semibold text-uppercase">Total Omzet Penjualan</div>
            <div class="h4 fw-bold text-primary mb-0 mt-1">
                Rp <?= number_format($total_omzet, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                Periode: <?= date('d M Y', strtotime($tgl_mulai)) ?> - <?= date('d M Y', strtotime($tgl_selesai)) ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="text-muted small fw-semibold text-uppercase">
                <?= $tipe === 'barang' ? 'Total Varian Terjual' : 'Total Transaksi (Nota)' ?>
            </div>
            <div class="h4 fw-bold text-success mb-0 mt-1">
                <?= number_format($tipe === 'barang' ? count($list_barang) : $total_transaksi, 0, ',', '.') ?>
                <span class="small fw-normal text-muted"><?= $tipe === 'barang' ? 'Produk' : 'Faktur' ?></span>
            </div>
            <div class="text-muted small mt-1">
                Total Volume: <strong><?= number_format($total_qty, 0, ',', '.') ?></strong> Item/Pcs
            </div>
        </div>
    </div>

    <?php if ($is_admin): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="text-muted small fw-semibold text-uppercase">Estimasi Margin Profit</div>
            <div class="h4 fw-bold text-warning-emphasis mb-0 mt-1">
                Rp <?= number_format($total_profit, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                Margin Rata-rata: <strong><?= $total_omzet > 0 ? round(($total_profit / $total_omzet) * 100, 1) : 0 ?>%</strong>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-secondary">
            <div class="text-muted small fw-semibold text-uppercase">Rata-rata Transaksi</div>
            <div class="h4 fw-bold text-dark mb-0 mt-1">
                Rp <?= number_format($total_transaksi > 0 ? ($total_omzet / $total_transaksi) : 0, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">Nilai Belanja per Pelanggan</div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-info">
            <div class="text-muted small fw-semibold text-uppercase">Rincian Pembayaran</div>
            <div class="d-flex justify-content-between small mt-1">
                <span>Tunai:</span>
                <strong>Rp <?= number_format($total_tunai ?? 0, 0, ',', '.') ?></strong>
            </div>
            <div class="d-flex justify-content-between small">
                <span>QRIS:</span>
                <strong>Rp <?= number_format($total_qris ?? 0, 0, ',', '.') ?></strong>
            </div>
            <div class="d-flex justify-content-between small">
                <span>Transfer:</span>
                <strong>Rp <?= number_format($total_transfer ?? 0, 0, ',', '.') ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- TABEL DATA LAPORAN                             -->
<!-- ============================================== -->
<div class="card-modern p-4">
    <?php if ($tipe === 'barang'): ?>
        <!-- ========================================== -->
        <!-- TABEL REKAP PRODUK TERJUAL                 -->
        <!-- ========================================== -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-seam me-1 text-primary"></i> Rekapitulasi Penjualan per Varian Produk
            </h6>
            <span class="badge bg-light text-dark border">
                Total: <?= count($list_barang) ?> Produk Terjual
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 50px;" class="text-center">No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-center">Total Terjual</th>
                        <th class="text-end">Rata-rata Harga</th>
                        <th class="text-end">Total Omzet</th>
                        <?php if ($is_admin): ?>
                            <th class="text-end">Total Profit</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($list_barang as $b): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-light text-primary font-monospace fw-bold border">
                                    <?= esc($b['id_barang']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark"><?= esc($b['nama_barang']) ?></td>
                            <td class="small text-secondary"><?= esc($b['nama_kategori'] ?? 'Umum') ?></td>
                            <td class="text-muted small"><?= esc($b['satuan'] ?? 'Pcs') ?></td>
                            <td class="text-center fw-bold text-primary">
                                <?= number_format((float)$b['total_qty'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end text-muted small">
                                Rp <?= number_format((float)$b['avg_harga_jual'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format((float)$b['total_omzet'], 0, ',', '.') ?>
                            </td>
                            <?php if ($is_admin): ?>
                                <td class="text-end fw-semibold text-success">
                                    Rp <?= number_format((float)$b['total_profit'], 0, ',', '.') ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- TABEL REKAP TRANSAKSI / FAKTUR             -->
        <!-- ========================================== -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-receipt me-1 text-info"></i> Daftar Faktur Penjualan Kasir
            </h6>
            <span class="badge bg-light text-dark border">
                Total: <?= count($list) ?> Transaksi
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 50px;" class="text-center">No.</th>
                        <th>No. Faktur</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-center">Metode Bayar</th>
                        <?php if ($is_admin): ?>
                            <th class="text-end">Profit</th>
                        <?php endif; ?>
                        <th class="text-center" style="width: 80px;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($list as $p): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary font-monospace fw-bold">
                                    <?= esc($p['no_faktur']) ?>
                                </span>
                            </td>
                            <td class="small">
                                <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($p['tanggal'])) ?> WIB</div>
                            </td>
                            <td class="small text-secondary fw-medium">
                                <?= esc($p['nama_kasir'] ?: $p['kode_user']) ?>
                            </td>
                            <td class="small">
                                <?php if (!empty($p['kode_member'])): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold">
                                        <i class="bi bi-person-badge me-1"></i><?= esc($p['kode_member']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Umum</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small"><?= $p['total_item'] ?></td>
                            <td class="text-center fw-semibold"><?= number_format((float)$p['total_qty'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format((float)$p['grand_total'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $m = strtolower(trim($p['metode_bayar'] ?? 'cash'));
                                    if (str_contains($m, 'qris')) {
                                        echo '<span class="badge bg-info text-dark fw-bold">QRIS</span>';
                                    } elseif (str_contains($m, 'trf') || str_contains($m, 'transfer')) {
                                        echo '<span class="badge bg-primary fw-bold">Transfer</span>';
                                    } else {
                                        echo '<span class="badge bg-success fw-bold">Tunai</span>';
                                    }
                                ?>
                            </td>
                            <?php if ($is_admin): ?>
                                <td class="text-end fw-bold text-success small">
                                    Rp <?= number_format((float)$p['total_profit'], 0, ',', '.') ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-detail-faktur" data-faktur="<?= esc($p['no_faktur']) ?>" title="Lihat Rincian Struk">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================== -->
<!-- MODAL DETAIL STRUK PENJUALAN                   -->
<!-- ============================================== -->
<div class="modal fade" id="modalDetailFaktur" tabindex="-1" aria-labelledby="modalDetailFakturLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-info text-white rounded-top-4 px-4 py-3">
                <h5 class="modal-title fw-bold" id="modalDetailFakturLabel">
                    <i class="bi bi-receipt me-2"></i>Rincian Faktur Penjualan <span id="detail-no-faktur" class="font-monospace"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Info Faktur -->
                <div class="bg-light rounded-3 p-3 mb-3 border">
                    <div class="row g-2 small">
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Tanggal:</span>
                            <div class="fw-bold" id="detail-tanggal">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Kasir:</span>
                            <div class="fw-bold" id="detail-kasir">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Pelanggan:</span>
                            <div class="fw-bold" id="detail-member">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Metode Bayar:</span>
                            <div class="fw-bold text-primary" id="detail-metode">-</div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Produk -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Produk</th>
                                <th class="text-end" style="width: 120px;">Harga Satuan</th>
                                <th class="text-center" style="width: 70px;">Qty</th>
                                <th class="text-end" style="width: 130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-body">
                            <tr><td colspan="5" class="text-center text-muted">Memuat rincian item...</td></tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">Subtotal Belanja:</td>
                                <td class="text-end" id="detail-subtotal">Rp 0</td>
                            </tr>
                            <tr id="row-diskon" style="display: none;">
                                <td colspan="4" class="text-end text-danger">Potongan Diskon:</td>
                                <td class="text-end text-danger" id="detail-diskon">Rp 0</td>
                            </tr>
                            <tr id="row-voucher" style="display: none;">
                                <td colspan="4" class="text-end text-danger">Potongan Voucher:</td>
                                <td class="text-end text-danger" id="detail-voucher">Rp 0</td>
                            </tr>
                            <tr id="row-jerigen" style="display: none;">
                                <td colspan="4" class="text-end text-success">Potongan Tukar Jerigen:</td>
                                <td class="text-end text-success" id="detail-jerigen">Rp 0</td>
                            </tr>
                            <tr class="table-primary h6 mb-0">
                                <td colspan="4" class="text-end fw-bold">Total Pembayaran (Grand Total):</td>
                                <td class="text-end fw-bold text-primary" id="detail-grand-total">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <div class="modal-footer bg-light rounded-bottom-4 px-4 py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Preset Periode Cepat
    const btnPresets = document.querySelectorAll('.btn-preset');
    const inputTglMulai = document.getElementById('tgl_mulai');
    const inputTglSelesai = document.getElementById('tgl_selesai');

    function formatDate(d) {
        return d.toISOString().split('T')[0];
    }

    btnPresets.forEach(btn => {
        btn.addEventListener('click', function() {
            const preset = this.getAttribute('data-preset');
            const now = new Date();
            let start = new Date();
            let end = new Date();

            if (preset === 'hari-ini') {
                // start = now, end = now
            } else if (preset === 'kemarin') {
                start.setDate(now.getDate() - 1);
                end.setDate(now.getDate() - 1);
            } else if (preset === '7-hari') {
                start.setDate(now.getDate() - 6);
            } else if (preset === 'bulan-ini') {
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            } else if (preset === 'bulan-lalu') {
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
            }

            inputTglMulai.value = formatDate(start);
            inputTglSelesai.value = formatDate(end);
            document.getElementById('form-filter-penjualan').submit();
        });
    });

    // 2. Modal AJAX Detail Faktur
    const modalDetailFaktur = new bootstrap.Modal(document.getElementById('modalDetailFaktur'));
    $(document).on('click', '.btn-detail-faktur', function() {
        const noFaktur = $(this).data('faktur');
        $('#detail-no-faktur').text('#' + noFaktur);
        $('#detail-items-body').html('<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data faktur...</td></tr>');
        
        modalDetailFaktur.show();

        fetch('<?= base_url('laporan/penjualan/detail/') ?>' + encodeURIComponent(noFaktur))
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const p = data.penjualan;
                    const items = data.items;

                    $('#detail-tanggal').text(p.tanggal);
                    $('#detail-kasir').text(p.nama_kasir || p.kode_user || '-');
                    $('#detail-member').text(p.kode_member ? (p.kode_member) : 'Konsumen Umum');
                    $('#detail-metode').text(p.metode_bayar || 'Cash');

                    let rows = '';
                    items.forEach((item, idx) => {
                        rows += `
                            <tr>
                                <td class="text-center text-muted">${idx + 1}</td>
                                <td>
                                    <strong>${item.nama_barang}</strong>
                                    <div class="text-muted font-monospace" style="font-size: 0.75rem;">${item.id_barang}</div>
                                </td>
                                <td class="text-end">Rp ${Number(item.harga_jual).toLocaleString('id-ID')}</td>
                                <td class="text-center fw-bold">${Number(item.qty).toLocaleString('id-ID')} ${item.satuan || ''}</td>
                                <td class="text-end fw-semibold">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });
                    $('#detail-items-body').html(rows);

                    $('#detail-subtotal').text('Rp ' + Number(p.subtotal).toLocaleString('id-ID'));
                    $('#detail-grand-total').text('Rp ' + Number(p.grand_total).toLocaleString('id-ID'));

                    if (Number(p.diskon) > 0) {
                        $('#detail-diskon').text('- Rp ' + Number(p.diskon).toLocaleString('id-ID'));
                        $('#row-diskon').show();
                    } else {
                        $('#row-diskon').hide();
                    }

                    if (Number(p.potongan_voucher) > 0) {
                        $('#detail-voucher').text('- Rp ' + Number(p.potongan_voucher).toLocaleString('id-ID'));
                        $('#row-voucher').show();
                    } else {
                        $('#row-voucher').hide();
                    }

                    if (Number(p.potongan_tukar_jerigen) > 0) {
                        $('#detail-jerigen').text('- Rp ' + Number(p.potongan_tukar_jerigen).toLocaleString('id-ID'));
                        $('#row-jerigen').show();
                    } else {
                        $('#row-jerigen').hide();
                    }
                } else {
                    $('#detail-items-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat rincian faktur.</td></tr>');
                }
            })
            .catch(err => {
                $('#detail-items-body').html('<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan koneksi.</td></tr>');
            });
    });
});
</script>

<?= $this->endSection() ?>

