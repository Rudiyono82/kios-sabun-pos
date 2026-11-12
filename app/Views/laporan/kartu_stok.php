<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Kartu Stok & Audit Mutasi Barang</h5>
            <small class="text-muted">Melacak setiap pergerakan stok: barang masuk dari pembelian, keluar dari kasir, atau penyesuaian</small>
        </div>
        <form action="<?= base_url('laporan/kartu-stok') ?>" method="GET" class="d-flex gap-2">
            <select name="id_barang" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Barang --</option>
                <?php foreach ($barangs as $b): ?>
                    <option value="<?= esc($b['id_barang']) ?>" <?= $selectedBarang == $b['id_barang'] ? 'selected' : '' ?>>
                        <?= esc($b['nama_barang']) ?> (<?= $b['id_barang'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="table-kartu-stok">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Tanggal & Waktu</th>
                    <th>Nama Barang</th>
                    <th>Tipe Mutasi</th>
                    <th>No. Referensi</th>
                    <th class="text-center text-success">Masuk (+)</th>
                    <th class="text-center text-danger">Keluar (-)</th>
                    <th class="text-center fw-bold">Sisa Stok</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mutasi)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada riwayat mutasi stok.</td></tr>
                <?php else: ?>
                    <?php foreach ($mutasi as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['tanggal'])) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($m['nama_barang'] ?? $m['id_barang']) ?></div>
                                <small class="text-muted"><?= esc($m['id_barang']) ?></small>
                            </td>
                            <td>
                                <?php if ($m['tipe'] == 'MASUK'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-box-arrow-in-down"></i> Masuk</span>
                                <?php elseif ($m['tipe'] == 'KELUAR'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-box-arrow-up"></i> Keluar</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-sliders"></i> Penyesuaian</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="fw-semibold text-dark"><?= esc($m['no_referensi'] ?: '-') ?></span></td>
                            <td class="text-center fw-bold text-success"><?= $m['masuk'] > 0 ? '+' . $m['masuk'] : '-' ?></td>
                            <td class="text-center fw-bold text-danger"><?= $m['keluar'] > 0 ? '-' . $m['keluar'] : '-' ?></td>
                            <td class="text-center fw-bold fs-6 bg-light"><?= $m['sisa'] ?></td>
                            <td><small class="text-muted"><?= esc($m['keterangan'] ?: '-') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#table-kartu-stok').DataTable({
        language: { search: "Filter Catatan:" }
    });
</script>
<?= $this->endSection() ?>

