<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-bag-plus-fill text-primary me-2"></i>Laporan Pembelian &amp; Restock Supplier
        </h4>
        <p class="text-muted small mb-0">
            Rekapitulasi total belanja stok barang dari supplier, tagihan pembayaran, dan volume restock per rentang periode.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php
            $queryFilter = http_build_query([
                'tgl_mulai'     => $tgl_mulai,
                'tgl_selesai'   => $tgl_selesai,
                'kode_supplier' => $kode_supplier,
                'status_bayar'  => $status_bayar,
                'tipe'          => $tipe,
                'q'             => $q
            ]);
        ?>
        <a href="<?= base_url('laporan/pembelian/cetak?' . $queryFilter) ?>" target="_blank" class="btn btn-danger rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </a>
        <a href="<?= base_url('laporan/pembelian/excel?' . $queryFilter) ?>" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- FORM FILTER LAPORAN PEMBELIAN                  -->
<!-- ============================================== -->
<div class="card-modern p-4 mb-4 border-top border-4 border-primary">
    <form action="<?= base_url('laporan/pembelian') ?>" method="GET" id="form-filter-pembelian">
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
                        Per Nota Pembelian / Faktur Supplier
                    </option>
                    <option value="barang" <?= $tipe === 'barang' ? 'selected' : '' ?>>
                        Per Item Produk Kulakan
                    </option>
                </select>
            </div>

            <!-- Filter Supplier -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Supplier:</label>
                <select name="kode_supplier" class="form-select select2">
                    <option value="semua">-- Semua Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= esc($s['kode_supplier']) ?>" <?= $kode_supplier === $s['kode_supplier'] ? 'selected' : '' ?>>
                            <?= esc($s['nama_supplier']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Bayar -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Pembayaran:</label>
                <select name="status_bayar" class="form-select select2">
                    <option value="semua">-- Semua Status --</option>
                    <option value="Lunas" <?= $status_bayar === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                    <option value="Hutang" <?= ($status_bayar === 'Hutang' || $status_bayar === 'Tempo') ? 'selected' : '' ?>>Hutang / Tempo</option>
                </select>
            </div>

            <!-- Kata Kunci Pencarian -->
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold text-muted mb-1">Pencarian Kata Kunci:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="<?= $tipe === 'barang' ? 'Cari nama produk kulakan / kode barang...' : 'Cari no. beli / nama supplier / catatan...' ?>" value="<?= esc($q) ?>">
                </div>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
                <a href="<?= base_url('laporan/pembelian') ?>" class="btn btn-outline-secondary" title="Reset Filter">
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
            <div class="text-muted small fw-semibold text-uppercase">Total Pengeluaran Kulakan</div>
            <div class="h4 fw-bold text-primary mb-0 mt-1">
                Rp <?= number_format($total_biaya, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                Periode: <?= date('d M Y', strtotime($tgl_mulai)) ?> - <?= date('d M Y', strtotime($tgl_selesai)) ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="text-muted small fw-semibold text-uppercase">
                <?= $tipe === 'barang' ? 'Total Varian Dibeli' : 'Total Nota Pembelian' ?>
            </div>
            <div class="h4 fw-bold text-success mb-0 mt-1">
                <?= number_format($tipe === 'barang' ? count($list_barang) : $total_nota, 0, ',', '.') ?>
                <span class="small fw-normal text-muted"><?= $tipe === 'barang' ? 'Produk' : 'Nota' ?></span>
            </div>
            <div class="text-muted small mt-1">
                <?= $tipe === 'barang' ? 'Total Volume: <strong>' . number_format($total_qty, 0, ',', '.') . '</strong> Qty' : 'Restock Masuk ke Toko' ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-info">
            <div class="text-muted small fw-semibold text-uppercase">Status Pembayaran Lunas</div>
            <div class="h4 fw-bold text-info mb-0 mt-1">
                Rp <?= number_format($total_lunas ?? $total_biaya, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">Tagihan Sudah Dibayarkan</div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <div class="text-muted small fw-semibold text-uppercase">Sisa Hutang / Tempo</div>
            <div class="h4 fw-bold <?= ($total_hutang ?? 0) > 0 ? 'text-danger' : 'text-muted' ?> mb-0 mt-1">
                Rp <?= number_format($total_hutang ?? 0, 0, ',', '.') ?>
            </div>
            <div class="text-muted small mt-1">
                <?= ($total_hutang ?? 0) > 0 ? '<span class="text-danger fw-bold"><i class="bi bi-exclamation-circle"></i> Perlu Pelunasan</span>' : '<span class="text-success"><i class="bi bi-check-circle"></i> Tidak Ada Hutang</span>' ?>
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
        <!-- TABEL REKAP PRODUK KULAKAN                 -->
        <!-- ========================================== -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-seam me-1 text-primary"></i> Rekapitulasi Pembelian per Item Produk
            </h6>
            <span class="badge bg-light text-dark border">
                Total: <?= count($list_barang) ?> Varian Produk
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
                        <th class="text-center">Total Qty Masuk</th>
                        <th class="text-end">Rata-rata Harga Beli</th>
                        <th class="text-end">Total Biaya Pembelian</th>
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
                                Rp <?= number_format((float)$b['avg_harga_beli'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format((float)$b['total_biaya'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- TABEL REKAP NOTA PEMBELIAN                 -->
        <!-- ========================================== -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-receipt me-1 text-primary"></i> Daftar Nota Pembelian Supplier &amp; Restock
            </h6>
            <span class="badge bg-light text-dark border">
                Total: <?= count($list) ?> Nota
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 50px;" class="text-center">No.</th>
                        <th>No. Beli / Nota</th>
                        <th>Tanggal Nota</th>
                        <th>Nama Supplier</th>
                        <th>Petugas Input</th>
                        <th class="text-center">Status Bayar</th>
                        <th class="text-end">Total Biaya</th>
                        <th>Catatan</th>
                        <th class="text-center" style="width: 80px;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($list as $b): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary font-monospace fw-bold">
                                    <?= esc($b['no_beli']) ?>
                                </span>
                            </td>
                            <td class="small">
                                <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($b['tanggal'])) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($b['tanggal'])) ?> WIB</div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= esc($b['nama_supplier']) ?></span>
                                <div class="text-muted small font-monospace" style="font-size: 0.75rem;"><?= esc($b['kode_supplier']) ?></div>
                            </td>
                            <td class="small text-secondary fw-medium">
                                <?= esc($b['kode_user'] ?: 'Admin') ?>
                            </td>
                            <td class="text-center">
                                <?php if (strtolower(trim($b['status_bayar'] ?? '')) === 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">Hutang / Tempo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format((float)$b['total_biaya'], 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted">
                                <?= esc($b['catatan'] ?: '-') ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-detail-beli" data-nobeli="<?= esc($b['no_beli']) ?>" title="Lihat Rincian Item">
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
<!-- MODAL DETAIL NOTA PEMBELIAN                    -->
<!-- ============================================== -->
<div class="modal fade" id="modalDetailBeli" tabindex="-1" aria-labelledby="modalDetailBeliLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4 px-4 py-3">
                <h5 class="modal-title fw-bold" id="modalDetailBeliLabel">
                    <i class="bi bi-truck me-2"></i>Rincian Nota Pembelian <span id="detail-no-beli" class="font-monospace"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Info Nota -->
                <div class="bg-light rounded-3 p-3 mb-3 border">
                    <div class="row g-2 small">
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Tanggal:</span>
                            <div class="fw-bold" id="detail-beli-tanggal">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Supplier:</span>
                            <div class="fw-bold text-primary" id="detail-beli-supplier">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Petugas Input:</span>
                            <div class="fw-bold" id="detail-beli-user">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Status Pembayaran:</span>
                            <div class="fw-bold" id="detail-beli-status">-</div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Produk -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Produk / Barang Kulakan</th>
                                <th class="text-end" style="width: 130px;">Harga Beli</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th class="text-end" style="width: 140px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-beli-items-body">
                            <tr><td colspan="5" class="text-center text-muted">Memuat rincian item...</td></tr>
                        </tbody>
                        <tfoot class="table-primary fw-bold h6">
                            <tr>
                                <td colspan="4" class="text-end">Total Biaya Pembelian:</td>
                                <td class="text-end text-primary" id="detail-beli-total">Rp 0</td>
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
                // today
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
            document.getElementById('form-filter-pembelian').submit();
        });
    });

    // 2. Modal AJAX Detail Nota Pembelian
    const modalDetailBeli = new bootstrap.Modal(document.getElementById('modalDetailBeli'));
    $(document).on('click', '.btn-detail-beli', function() {
        const noBeli = $(this).data('nobeli');
        $('#detail-no-beli').text('#' + noBeli);
        $('#detail-beli-items-body').html('<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data nota...</td></tr>');
        
        modalDetailBeli.show();

        fetch('<?= base_url('laporan/pembelian/detail/') ?>' + encodeURIComponent(noBeli))
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const b = data.pembelian;
                    const items = data.items;

                    $('#detail-beli-tanggal').text(b.tanggal);
                    $('#detail-beli-supplier').text(b.nama_supplier + ' (' + b.kode_supplier + ')');
                    $('#detail-beli-user').text(b.kode_user || 'Admin');
                    $('#detail-beli-status').html(
                        b.status_bayar === 'Lunas' 
                        ? '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Lunas</span>' 
                        : '<span class="text-danger fw-bold"><i class="bi bi-exclamation-circle"></i> Hutang / Tempo</span>'
                    );

                    let rows = '';
                    items.forEach((item, idx) => {
                        rows += `
                            <tr>
                                <td class="text-center text-muted">${idx + 1}</td>
                                <td>
                                    <strong>${item.nama_barang}</strong>
                                    <div class="text-muted font-monospace" style="font-size: 0.75rem;">${item.id_barang}</div>
                                </td>
                                <td class="text-end">Rp ${Number(item.harga_beli).toLocaleString('id-ID')}</td>
                                <td class="text-center fw-bold">${Number(item.qty).toLocaleString('id-ID')} ${item.satuan || ''}</td>
                                <td class="text-end fw-semibold">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });
                    $('#detail-beli-items-body').html(rows);
                    $('#detail-beli-total').text('Rp ' + Number(b.total_biaya).toLocaleString('id-ID'));
                } else {
                    $('#detail-beli-items-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat rincian pembelian.</td></tr>');
                }
            })
            .catch(err => {
                $('#detail-beli-items-body').html('<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan koneksi.</td></tr>');
            });
    });
});
</script>

<?= $this->endSection() ?>

