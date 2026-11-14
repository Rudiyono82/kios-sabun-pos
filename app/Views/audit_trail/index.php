<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-shield-check text-success me-2"></i>Audit Trail &amp; Jejak Aktivitas Sistem
            </h4>
            <p class="text-muted small mb-0">Rekam jejak forensik seluruh operasi sistem (5W+1H: Who, Where, When, What, Before/After data) untuk keamanan multi-cabang.</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>Cetak Rekap Log</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Log Sesuai Filter</div>
                    <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Aktivitas Transaksi POS</div>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['pos']) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-4">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Mutasi / Transfer Stok</div>
                    <h4 class="fw-bold mb-0 text-info"><?= number_format($stats['transfer']) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-modern p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-4">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Akses Auth &amp; Cabang</div>
                    <h4 class="fw-bold mb-0 text-warning"><?= number_format($stats['auth'] + $stats['cabang']) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-modern mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="<?= base_url('audit-trail') ?>" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold text-muted">Cabang Toko</label>
                    <select name="id_cabang" class="form-select select2-custom">
                        <option value="all">-- Semua Cabang --</option>
                        <?php foreach ($cabangList as $cb): ?>
                            <option value="<?= $cb['id'] ?>" <?= ($filters['id_cabang'] == $cb['id']) ? 'selected' : '' ?>>
                                <?= esc($cb['nama_cabang']) ?> (<?= esc($cb['kode_cabang']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold text-muted">Modul Sistem</label>
                    <select name="modul" class="form-select">
                        <option value="all">-- Semua Modul --</option>
                        <option value="POS" <?= ($filters['modul'] === 'POS') ? 'selected' : '' ?>>Kasir POS</option>
                        <option value="TRANSFER_STOK" <?= ($filters['modul'] === 'TRANSFER_STOK') ? 'selected' : '' ?>>Transfer Stok</option>
                        <option value="CABANG" <?= ($filters['modul'] === 'CABANG') ? 'selected' : '' ?>>Cabang & Toko</option>
                        <option value="AUTH" <?= ($filters['modul'] === 'AUTH') ? 'selected' : '' ?>>Autentikasi</option>
                        <option value="KAS" <?= ($filters['modul'] === 'KAS') ? 'selected' : '' ?>>Kas & Keuangan</option>
                        <option value="BARANG" <?= ($filters['modul'] === 'BARANG') ? 'selected' : '' ?>>Master Barang</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= esc($filters['tgl_mulai']) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?= esc($filters['tgl_selesai']) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="d-flex gap-2">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari nama, aksi, kata kunci..." value="<?= esc($filters['keyword']) ?>">
                        <button type="submit" class="btn btn-primary px-3 fw-semibold">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card-modern">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-audit">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Waktu &amp; Tanggal</th>
                            <th>Cabang</th>
                            <th>Pengguna &amp; Role</th>
                            <th>Modul &amp; Aksi</th>
                            <th>Deskripsi Aktivitas</th>
                            <th class="text-center" style="width: 100px;">Data Detil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-shield-slash fs-2 d-block mb-2 text-secondary"></i>
                                    Tidak ada data jejak audit yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($logs as $l): ?>
                                <tr>
                                    <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d M Y', strtotime($l['created_at'])) ?></div>
                                        <small class="text-muted font-monospace"><?= date('H:i:s', strtotime($l['created_at'])) ?> WIB</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= esc($l['nama_cabang'] ?? 'Cabang #' . $l['id_cabang']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($l['nama_user']) ?></div>
                                        <small class="badge bg-secondary-subtle text-secondary" style="font-size: 0.68rem;"><?= esc(strtoupper($l['role'] ?? 'USER')) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2 py-1">
                                            <?= esc($l['modul']) ?>
                                        </span>
                                        <div class="small fw-bold text-dark mt-1"><?= esc($l['aksi']) ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-dark"><?= esc($l['deskripsi']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;">IP: <?= esc($l['ip_address'] ?? '127.0.0.1') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($l['data_lama']) || !empty($l['data_baru'])): ?>
                                            <button type="button" class="btn btn-outline-info btn-sm btn-view-data" 
                                                    data-aksi="<?= esc($l['aksi']) ?>" 
                                                    data-modul="<?= esc($l['modul']) ?>" 
                                                    data-lama='<?= esc($l['data_lama'], 'attr') ?>' 
                                                    data-baru='<?= esc($l['data_baru'], 'attr') ?>'>
                                                <i class="bi bi-file-code"></i> JSON
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
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

<!-- Modal Inspect JSON Data -->
<div class="modal fade" id="modalDataAudit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3">
                <h5 class="modal-title fw-bold" id="modalAuditTitle">
                    <i class="bi bi-file-earmark-code me-2"></i>Detail Nilai Data Sebelum &amp; Sesudah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="fw-bold small text-muted mb-1"><i class="bi bi-dash-circle text-danger me-1"></i> Data Sebelum (Before):</label>
                        <pre class="bg-white p-3 rounded-3 border font-monospace small mb-0" id="json-data-lama" style="max-height: 320px; overflow-y: auto;">-</pre>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="fw-bold small text-muted mb-1"><i class="bi bi-plus-circle text-success me-1"></i> Data Sesudah (After):</label>
                        <pre class="bg-white p-3 rounded-3 border font-monospace small mb-0" id="json-data-baru" style="max-height: 320px; overflow-y: auto;">-</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-2 bg-white">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($('#table-audit tbody tr').length > 1 || !$('#table-audit tbody tr td').hasClass('text-muted')) {
            $('#table-audit').DataTable({
                language: {
                    search: "Cari Dalam Tabel:",
                    lengthMenu: "Tampil _MENU_ log",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ log",
                    paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                },
                order: [[0, 'asc']]
            });
        }

        $('.btn-view-data').on('click', function() {
            let aksi = $(this).data('aksi');
            let modul = $(this).data('modul');
            let lama = $(this).attr('data-lama');
            let baru = $(this).attr('data-baru');

            $('#modalAuditTitle').html(`<i class="bi bi-file-earmark-code me-2"></i>Inspeksi Data: [${modul}] ${aksi}`);

            try {
                let parsedLama = lama ? JSON.parse(lama) : null;
                $('#json-data-lama').text(parsedLama ? JSON.stringify(parsedLama, null, 2) : 'Tidak ada rekaman data sebelum');
            } catch(e) {
                $('#json-data-lama').text(lama || '-');
            }

            try {
                let parsedBaru = baru ? JSON.parse(baru) : null;
                $('#json-data-baru').text(parsedBaru ? JSON.stringify(parsedBaru, null, 2) : 'Tidak ada rekaman data sesudah');
            } catch(e) {
                $('#json-data-baru').text(baru || '-');
            }

            let modal = new bootstrap.Modal(document.getElementById('modalDataAudit'));
            modal.show();
        });
    });
</script>
<?= $this->endSection() ?>

