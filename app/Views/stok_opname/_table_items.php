<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="<?= esc($tableId ?? 'table-detail-items') ?>" style="width: 100%;">
        <thead class="table-dark small text-uppercase">
            <tr>
                <th style="width: 45px;" class="text-center">No</th>
                <th>Nama Produk Sabun &amp; Parfum</th>
                <th class="text-center" style="width: 90px;">Satuan</th>
                <th class="text-center" style="width: 140px;">Stok Sistem (Awal)</th>
                <th class="text-center" style="width: 140px; background-color: #0d6efd; color: #fff;">Stok Fisik Riil</th>
                <th class="text-center" style="width: 120px;">Selisih QTY</th>
                <th class="text-end" style="width: 150px;">Nilai Selisih (Rp)</th>
                <th style="width: 180px;">Catatan Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itemList as $idx => $it): ?>
                <?php
                    $diff = (float)$it['selisih'];
                    $rowBg = '';
                    if ($diff < 0) {
                        $rowBg = 'table-danger bg-opacity-10';
                    } elseif ($diff > 0) {
                        $rowBg = 'table-success bg-opacity-10';
                    }
                ?>
                <tr class="<?= $rowBg ?>">
                    <td class="text-center text-muted small fw-semibold"><?= $idx + 1 ?></td>
                    <td>
                        <div class="fw-bold text-dark"><?= esc($it['nama_barang']) ?></div>
                        <small class="text-muted font-monospace"><?= esc($it['id_barang']) ?></small>
                    </td>
                    <td class="text-center small text-muted"><?= esc($it['satuan']) ?></td>
                    <td class="text-center font-monospace fw-semibold text-secondary">
                        <?= number_format($it['stok_sistem'], 2, ',', '.') ?>
                    </td>
                    <td class="text-center fw-bold font-monospace fs-6 text-primary">
                        <?= number_format($it['stok_fisik'], 2, ',', '.') ?>
                    </td>
                    <td class="text-center fw-bold">
                        <?php if ($diff > 0): ?>
                            <span class="badge bg-success fs-6">+<?= number_format($diff, 2, ',', '.') ?></span>
                        <?php elseif ($diff < 0): ?>
                            <span class="badge bg-danger fs-6"><?= number_format($diff, 2, ',', '.') ?></span>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border">0 (Cocok)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold font-monospace <?= (float)$it['nilai_selisih'] < 0 ? 'text-danger' : ((float)$it['nilai_selisih'] > 0 ? 'text-success' : 'text-muted') ?>">
                        Rp <?= number_format($it['nilai_selisih'], 0, ',', '.') ?>
                    </td>
                    <td class="small text-muted"><?= esc($it['catatan'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

