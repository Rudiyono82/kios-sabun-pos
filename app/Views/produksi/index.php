<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Produksi & Racik Sabun Curah (BOM)</h4>
        <p class="text-muted small mb-0">Konversi bahan baku mentah (Texapon, Bibit Parfum, Pewarna, Garam, dll) menjadi Jerigen Sabun 5L / Botol 1L dengan pemotongan stok otomatis.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('produksi/resep') ?>" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold">
            <i class="bi bi-card-checklist me-1"></i> Kelola Formula Resep
        </a>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalCatatProduksi">
            <i class="bi bi-play-circle-fill me-1"></i> Catat Racik Sabun Baru
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

<!-- Tabel Riwayat Produksi Batch -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-produksi">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>No Produksi</th>
                    <th>Waktu / Tanggal</th>
                    <th>Produk Sabun yang Dihasilkan</th>
                    <th class="text-center">Jumlah Hasil</th>
                    <th class="text-end">Total Biaya HPP</th>
                    <th class="text-end">HPP / Satuan</th>
                    <th>Keterangan</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produksi_list)): ?>
                    <?php foreach ($produksi_list as $p): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-primary"><?= esc($p['no_produksi']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($p['nama_barang_jadi']) ?></div>
                                <small class="text-muted">Kode: <?= esc($p['id_barang_jadi']) ?></small>
                            </td>
                            <td class="text-center fw-bold text-success fs-6">
                                <?= number_format($p['qty_hasil'], 0, ',', '.') ?> <?= esc($p['satuan_hasil']) ?>
                            </td>
                            <td class="text-end fw-semibold">
                                Rp <?= number_format($p['total_biaya_hpp'], 0, ',', '.') ?>
                            </td>
                            <td class="text-end fw-bold text-primary font-monospace">
                                Rp <?= number_format($p['hpp_per_satuan'], 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted"><?= esc($p['keterangan'] ?: '-') ?></td>
                            <td class="small text-muted"><?= esc($p['kode_user']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EKSEKUSI CATAT PRODUKSI BARU            -->
<!-- ============================================== -->
<div class="modal fade" id="modalCatatProduksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-play-circle me-2"></i>Catat Produksi / Racik Sabun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('produksi/proses-produksi') ?>" method="POST" id="form-proses-produksi">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Pilih Resep / Formula -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Pilih Formula / Resep Sabun <span class="text-danger">*</span></label>
                            <select name="template_id" id="select-template-prod" class="form-select select2" required>
                                <option value="">-- Pilih Resep Formulasi --</option>
                                <?php foreach ($template_list as $t): ?>
                                    <option value="<?= $t['id'] ?>" data-satuan="<?= esc($t['satuan_hasil']) ?>" data-batch="<?= $t['qty_hasil'] ?>">
                                        <?= esc($t['nama_template']) ?> (Hasil: <?= esc($t['nama_barang_jadi']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Target Jumlah Hasil -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Jumlah Diproduksi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="qty_hasil" id="input-qty-hasil" class="form-control text-center fw-bold fs-5" min="1" step="any" value="1" required>
                                <span class="input-group-text bg-white" id="label-satuan-hasil">JERIGEN</span>
                            </div>
                        </div>

                        <!-- Preview Bahan Baku yang Akan Dipotong Otomatis -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold text-dark mb-0">Kebutuhan Bahan Baku yang Akan Dipotong Otomatis:</label>
                                    <span class="badge bg-secondary" id="badge-status-stok">Pilih resep dulu</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-0" id="table-preview-bahan">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>Nama Bahan Baku</th>
                                                <th class="text-center">Takaran / Batch</th>
                                                <th class="text-center">Total Kebutuhan</th>
                                                <th class="text-center"><?= session()->get('role') === 'admin' ? 'Stok Toko Saat Ini' : 'Ketersediaan Bahan' ?></th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-preview-bahan">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3 small">
                                                    Silakan pilih formula resep di atas untuk melihat takaran bahan baku.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan / Catatan -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Catatan / Keterangan Batch</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Racik 10 jerigen deterjen wangi sakura untuk stok etalase kasir..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-produksi" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-gear-fill me-1"></i> Proses Racik Sabun Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentResep = null;

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-produksi').DataTable({
                language: {
                    search: "Cari Produksi:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ batch produksi",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" }
                },
                order: [[1, 'desc']]
            });
        }

        // Ketika Resep Dipilih
        $('#select-template-prod').on('change', function() {
            const id = $(this).val();
            if (!id) {
                $('#tbody-preview-bahan').html('<tr><td colspan="5" class="text-center text-muted py-3 small">Silakan pilih formula resep di atas.</td></tr>');
                $('#badge-status-stok').text('Pilih resep dulu').removeClass('bg-success bg-danger').addClass('bg-secondary');
                return;
            }

            $.get('<?= base_url('produksi/get-resep-detail') ?>/' + id, function(data) {
                currentResep = data;
                $('#label-satuan-hasil').text(data.satuan_hasil || 'JERIGEN');
                $('#input-qty-hasil').val(data.qty_hasil || 1);
                renderBahanPreview();
            });
        });

        $('#input-qty-hasil').on('input', function() {
            if (currentResep) {
                renderBahanPreview();
            }
        });

        function renderBahanPreview() {
            if (!currentResep || !currentResep.bahan) return;

            const targetQty = parseFloat($('#input-qty-hasil').val()) || 1;
            const baseQty = parseFloat(currentResep.qty_hasil) || 1;
            const rasio = targetQty / baseQty;

            const $tbody = $('#tbody-preview-bahan');
            $tbody.empty();

            let allSufficient = true;

            const isAdminRole = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;
            currentResep.bahan.forEach(b => {
                const kebutuhan = (parseFloat(b.qty_per_batch) * rasio).toFixed(3);
                const stokToko = parseFloat(b.stok_sekarang) || 0;
                const isCukup = stokToko >= kebutuhan;

                if (!isCukup) allSufficient = false;

                const stokDisplay = isAdminRole 
                    ? `${stokToko} ${b.satuan}` 
                    : (isCukup ? '<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Tersedia</span>' : '<span class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Kurang</span>');

                const tr = $(`
                    <tr>
                        <td class="fw-semibold text-dark">${b.nama_barang_baku}</td>
                        <td class="text-center">${b.qty_per_batch} ${b.satuan}</td>
                        <td class="text-center fw-bold text-primary">${kebutuhan} ${b.satuan}</td>
                        <td class="text-center font-monospace">${stokDisplay}</td>
                        <td class="text-center">
                            ${isCukup 
                                ? '<span class="badge bg-success"><i class="bi bi-check-lg"></i> Cukup</span>'
                                : '<span class="badge bg-danger"><i class="bi bi-x-lg"></i> Kurang</span>'}
                        </td>
                    </tr>
                `);
                $tbody.append(tr);
            });

            if (allSufficient) {
                $('#badge-status-stok').text('Semua Bahan Baku Siap').removeClass('bg-secondary bg-danger').addClass('bg-success');
                $('#btn-submit-produksi').prop('disabled', false);
            } else {
                $('#badge-status-stok').text('Ada Bahan Baku Kurang!').removeClass('bg-secondary bg-success').addClass('bg-danger');
                $('#btn-submit-produksi').prop('disabled', true);
            }
        }
    });
</script>
<?= $this->endSection() ?>

