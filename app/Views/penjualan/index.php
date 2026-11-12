<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Daftar Faktur Penjualan Kasir</h4>
        <p class="text-muted small mb-0">Monitoring seluruh transaksi kasir, rincian produk terjual, cetak ulang struk, dan otorisasi hapus transaksi.</p>
    </div>
    <div>
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
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Dari Tanggal:</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= esc($tgl_mulai) ?>">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Sampai Tanggal:</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= esc($tgl_selesai) ?>">
        </div>
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
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-penjualan">
            <thead class="table-light small text-uppercase">
                <tr>
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
                        <tr>
                            <td class="fw-bold font-monospace text-primary"><?= esc($p['no_faktur']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
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
                                    <button type="button" class="btn btn-outline-primary btn-detail-faktur" data-nofaktur="<?= esc($p['no_faktur']) ?>" title="Lihat Rincian Item">
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

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-penjualan').DataTable({
                language: {
                    search: "Cari Faktur / Kasir:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ faktur",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada transaksi penjualan pada periode ini."
                },
                order: [[1, 'desc']]
            });
        }

        // 1. Lihat Detail Faktur Penjualan
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

