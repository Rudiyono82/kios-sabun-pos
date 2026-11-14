<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-send-plus-fill text-primary me-2"></i>Kirim Transfer Stok Baru
            </h4>
            <p class="text-muted small mb-0">Kirim mutasi stok barang dari cabang Anda ke cabang tujuan di jaringan toko.</p>
        </div>
        <div>
            <a href="<?= base_url('transfer-stok') ?>" class="btn btn-outline-secondary px-3 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('transfer-stok/simpan') ?>" method="POST" id="form-transfer">
        <?= csrf_field() ?>

        <div class="row g-4 mb-4">
            <!-- Informasi Pengiriman -->
            <div class="col-12 col-lg-4">
                <div class="card-modern h-100">
                    <div class="card-header bg-primary text-white p-3 fw-bold rounded-top-4 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill"></i> Rute Mutasi Stok
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">No. Dokumen Transfer</label>
                            <input type="text" class="form-control fw-bold bg-light text-primary" name="no_transfer" value="<?= esc($no_transfer) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Cabang Asal (Pengirim)</label>
                            <div class="p-2 px-3 bg-light rounded-3 border d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark"><?= esc($currentCabang['nama_cabang'] ?? 'Cabang Pusat') ?></div>
                                    <small class="text-muted"><?= esc($currentCabang['kode_cabang'] ?? 'CBG-01') ?> &bull; <?= esc($currentCabang['kota'] ?? '-') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Cabang Tujuan (Penerima) <span class="text-danger">*</span></label>
                            <select name="id_cabang_tujuan" class="form-select select2-custom" required>
                                <option value="">-- Pilih Cabang Penerima --</option>
                                <?php foreach ($cabangTujuanList as $cb): ?>
                                    <option value="<?= $cb['id'] ?>">
                                        <?= esc($cb['nama_cabang']) ?> (<?= esc($cb['kode_cabang']) ?>) - <?= esc($cb['kota'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Catatan Pengiriman / Ekspedisi</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Titip kirim via mobil operasional kantor, driver Pak Budi"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Barang Ditransfer -->
            <div class="col-12 col-lg-8">
                <div class="card-modern h-100">
                    <div class="card-header bg-dark text-white p-3 fw-bold rounded-top-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-boxes"></i> Daftar Barang Yang Akan Ditransfer
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btn-tambah-baris">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Item
                        </button>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="table-items">
                                <thead>
                                    <tr>
                                        <th style="min-width: 250px;">Nama Barang</th>
                                        <th class="text-center" style="width: 110px;">Stok Asal</th>
                                        <th class="text-center" style="width: 120px;">Qty Transfer</th>
                                        <th class="text-center" style="width: 90px;">Satuan</th>
                                        <th style="min-width: 140px;">Keterangan</th>
                                        <th class="text-center" style="width: 50px;"><i class="bi bi-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="item-tbody">
                                    <!-- Baris item akan di-generate via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <span class="fw-bold text-dark">Ringkasan:</span>
                                <span class="ms-2 badge bg-primary-subtle text-primary" id="summary-total-item">0 Item</span>
                                <span class="ms-1 badge bg-secondary-subtle text-secondary" id="summary-total-qty">0 Pcs</span>
                            </div>
                            <button type="submit" class="btn btn-success px-4 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" id="btn-submit">
                                <i class="bi bi-check-lg fs-5"></i>
                                <span>Konfirmasi &amp; Kirim Transfer Stok</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const BARANG_LIST = <?= json_encode($barangList) ?>;
    let rowIndex = 0;

    function buildItemRow(index) {
        let options = '<option value="">-- Pilih Barang --</option>';
        BARANG_LIST.forEach(b => {
            let val = b.id_barang || b.id;
            options += `<option value="${val}" data-stok="${b.stok_cabang}" data-satuan="${b.satuan || 'Pcs'}">${b.nama_barang} (Stok: ${b.stok_cabang})</option>`;
        });

        return `
            <tr id="row-${index}">
                <td>
                    <select name="items[${index}][id_barang]" class="form-select select-barang" required data-row="${index}">
                        ${options}
                    </select>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6" id="stok-label-${index}">0</span>
                </td>
                <td>
                    <input type="number" name="items[${index}][qty]" class="form-control text-center input-qty fw-bold text-primary" min="1" step="any" value="1" required data-row="${index}">
                </td>
                <td class="text-center text-muted font-monospace small" id="satuan-label-${index}">
                    Pcs
                </td>
                <td>
                    <input type="text" name="items[${index}][catatan]" class="form-control form-control-sm" placeholder="Kondisi / batch">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-hapus-baris" data-row="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    function updateSummary() {
        let totalItem = 0;
        let totalQty = 0;

        $('#item-tbody tr').each(function() {
            let row = $(this);
            let select = row.find('.select-barang');
            let qtyInput = row.find('.input-qty');

            if (select.val()) {
                totalItem++;
                totalQty += parseFloat(qtyInput.val()) || 0;
            }
        });

        $('#summary-total-item').text(`${totalItem} Item`);
        $('#summary-total-qty').text(`${totalQty} Qty`);
    }

    $(document).ready(function() {
        // Inisialisasi baris pertama
        $('#item-tbody').append(buildItemRow(rowIndex++));
        $('.select-barang').select2({ theme: 'bootstrap-5', width: '100%', minimumResultsForSearch: 8 });

        $('#btn-tambah-baris').on('click', function() {
            let newRow = $(buildItemRow(rowIndex++));
            $('#item-tbody').append(newRow);
            newRow.find('.select-barang').select2({ theme: 'bootstrap-5', width: '100%', minimumResultsForSearch: 8 });
            updateSummary();
        });

        $(document).on('click', '.btn-hapus-baris', function() {
            if ($('#item-tbody tr').length <= 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Minimal harus ada 1 baris barang untuk ditransfer!'
                });
                return;
            }
            let rowId = $(this).data('row');
            $(`#row-${rowId}`).remove();
            updateSummary();
        });

        $(document).on('change', '.select-barang', function() {
            let rowId = $(this).data('row');
            let selected = $(this).find(':selected');
            let stok = parseFloat(selected.data('stok')) || 0;
            let satuan = selected.data('satuan') || 'Pcs';

            $(`#stok-label-${rowId}`).text(stok);
            $(`#satuan-label-${rowId}`).text(satuan);

            let qtyInput = $(`#row-${rowId} .input-qty`);
            qtyInput.attr('max', stok);
            if (stok <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Kosong',
                    text: 'Barang ini memiliki stok 0 di cabang asal Anda dan tidak bisa ditransfer.'
                });
                qtyInput.val(0);
            } else if (parseFloat(qtyInput.val()) > stok) {
                qtyInput.val(stok);
            }

            updateSummary();
        });

        $(document).on('input', '.input-qty', function() {
            let rowId = $(this).data('row');
            let row = $(`#row-${rowId}`);
            let stok = parseFloat(row.find('.select-barang :selected').data('stok')) || 0;
            let qty = parseFloat($(this).val()) || 0;

            if (qty > stok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Melebihi Stok',
                    text: `Qty transfer (${qty}) tidak boleh melebihi stok yang tersedia (${stok})!`
                });
                $(this).val(stok);
            }
            updateSummary();
        });

        $('#form-transfer').on('submit', function(e) {
            let hasItem = false;
            let invalidStock = false;

            $('#item-tbody tr').each(function() {
                let row = $(this);
                let idBarang = row.find('.select-barang').val();
                let qty = parseFloat(row.find('.input-qty').val()) || 0;
                let stok = parseFloat(row.find('.select-barang :selected').data('stok')) || 0;

                if (idBarang && qty > 0) {
                    hasItem = true;
                    if (qty > stok) {
                        invalidStock = true;
                    }
                }
            });

            if (!hasItem) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Barang Kosong',
                    text: 'Silakan pilih minimal satu barang dengan qty transfer lebih dari 0!'
                });
                return false;
            }

            if (invalidStock) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Tidak Mencukupi',
                    text: 'Terdapat barang yang qty transfernya melebihi stok tersedia di cabang asal!'
                });
                return false;
            }
        });
    });
</script>
<?= $this->endSection() ?>

