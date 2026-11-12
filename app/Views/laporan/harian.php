<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <!-- Filter Tanggal & Export/Print -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Rekap Laporan Keuangan & Penjualan Harian</h5>
            <small class="text-muted">No. Laporan: <strong class="text-dark font-monospace bg-light px-2 py-0.5 rounded border"><?= esc($no_laporan) ?></strong> &bull; Setara modul cetak laporan kasir & hitung ulang omzet di aplikasi VB asli</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form action="<?= base_url('laporan/harian') ?>" method="GET" class="d-flex gap-2">
                <input type="date" name="tanggal" class="form-control" value="<?= esc($tanggal) ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-filter"></i> Filter</button>
            </form>
            <a href="<?= base_url('laporan/cetak-struk-harian?tanggal=' . $tanggal) ?>" target="_blank" onclick="window.open(this.href, 'StrukHarian', 'width=440,height=700'); return false;" class="btn btn-dark fw-bold text-nowrap shadow-sm">
                <i class="bi bi-receipt me-1 text-warning"></i> Struk Kasir (Thermal)
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak A4</button>
        </div>
    </div>

    <!-- Ringkasan Kas & Omzet Hari Terpilih -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <small class="text-muted fw-semibold d-block text-uppercase">Kas Awal Toko</small>
                <h4 class="fw-bold text-dark mt-1 mb-0">Rp <?= number_format($rekap['kas_awal'], 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <small class="text-muted fw-semibold d-block text-uppercase">Total Omzet Penjualan</small>
                <h4 class="fw-bold text-primary mt-1 mb-0">Rp <?= number_format($rekap['omzet'], 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-light rounded-3 border">
                <small class="text-muted fw-semibold d-block text-uppercase">Total Pengeluaran / Biaya</small>
                <h4 class="fw-bold text-danger mt-1 mb-0">Rp <?= number_format($rekap['pengeluaran'], 0, ',', '.') ?></h4>
            </div>
        </div>
        <?php if (session()->get('role') === 'admin'): ?>
        <div class="col-md-3">
            <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success-subtle">
                <small class="text-success fw-bold d-block text-uppercase">Estimasi Margin Profit</small>
                <h4 class="fw-bold text-success mt-1 mb-0">Rp <?= number_format($rekap['profit'], 0, ',', '.') ?> <span class="badge bg-success text-white fs-6 ms-1" title="Kode No. Laporan Terselubung">[Hex: <?= esc($no_laporan) ?>]</span></h4>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Rincian Transaksi Penjualan -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-receipt me-2"></i>Rincian Faktur Penjualan (<?= date('d M Y', strtotime($tanggal)) ?>)</h6>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
            <i class="bi bi-cart-check me-1"></i> <?= count($penjualan) ?> Faktur Tercatat
        </span>
    </div>
    <div class="table-responsive mb-4">
        <table class="table table-hover align-middle mb-0" id="table-faktur-harian">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>No Faktur</th>
                    <th>Jam</th>
                    <th>Kasir</th>
                    <th>Member</th>
                    <th>Metode</th>
                    <th class="text-center">Total Item</th>
                    <th class="text-end">Potongan Jerigen</th>
                    <th class="text-end">Grand Total</th>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <th class="text-end">Profit</th>
                    <?php endif; ?>
                    <th class="text-center" style="min-width: 140px;">Aksi / Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($penjualan)): ?>
                    <?php foreach ($penjualan as $p): ?>
                        <tr>
                            <td><span class="fw-bold font-monospace text-primary"><?= esc($p['no_faktur']) ?></span></td>
                            <td><?= date('H:i', strtotime($p['tanggal'])) ?></td>
                            <td><?= esc($p['nama_kasir'] ?? $p['kode_user'] ?? 'Kasir') ?></td>
                            <td>
                                <?php if (!empty($p['kode_member'])): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><?= esc($p['kode_member']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Umum</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><?= esc($p['metode_bayar']) ?></span></td>
                            <td class="text-center fw-semibold"><?= number_format($p['total_item'], 0, ',', '.') ?></td>
                            <td class="text-end text-success"><?= $p['potongan_tukar_jerigen'] > 0 ? '-Rp ' . number_format($p['potongan_tukar_jerigen'], 0, ',', '.') : '-' ?></td>
                            <td class="text-end fw-bold text-dark">Rp <?= number_format($p['grand_total'], 0, ',', '.') ?></td>
                            <?php if (session()->get('role') === 'admin'): ?>
                            <td class="text-end fw-semibold text-success">Rp <?= number_format($p['total_profit'], 0, ',', '.') ?></td>
                            <?php endif; ?>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-2 fw-semibold btn-detail-faktur shadow-sm"
                                        data-nofaktur="<?= esc($p['no_faktur']) ?>"
                                        title="Lihat Detail Item Barang & Totalan Faktur">
                                        <i class="bi bi-eye-fill me-1"></i> Detail
                                    </button>
                                    <a href="<?= base_url('pos/cetak-struk/' . $p['no_faktur']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Cetak Struk Thermal">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabel Arus Kas / Keuangan -->
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cash-coin me-2"></i>Buku Kas Toko (Pemasukan & Pengeluaran)</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0" id="table-keuangan-harian">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Keterangan / Transaksi</th>
                    <th>Kasir/User</th>
                    <th class="text-end">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($keuangan)): ?>
                    <?php foreach ($keuangan as $k): ?>
                        <tr>
                            <td><?= date('H:i:s', strtotime($k['tanggal'])) ?></td>
                            <td>
                                <span class="badge <?= $k['jenis_keuangan'] == 'Pemasukan' ? 'bg-success' : ($k['jenis_keuangan'] == 'Kas Awal' ? 'bg-primary' : 'bg-danger') ?>">
                                    <?= esc($k['jenis_keuangan']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($k['nama_keuangan']) ?></div>
                                <small class="text-muted"><?= esc($k['keterangan'] ?: '') ?></small>
                            </td>
                            <td><?= esc($k['kode_user'] ?: 'System') ?></td>
                            <td class="text-end fw-bold <?= $k['jenis_keuangan'] == 'Pengeluaran' ? 'text-danger' : 'text-dark' ?>">
                                <?= $k['jenis_keuangan'] == 'Pengeluaran' ? '-' : '' ?>Rp <?= number_format($k['total'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL DETAIL FAKTUR PENJUALAN KASIR           -->
<!-- ============================================== -->
<div class="modal fade" id="modalDetailPenjualan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2 text-warning"></i>Detail Faktur Penjualan: <span id="dtl-faktur-nofaktur" class="text-warning font-monospace"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Header Faktur -->
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
    let activeFakturHarian = '';
    const currentUserRole = '<?= session()->get('role') ?>';

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-faktur-harian').DataTable({
                language: {
                    search: "Cari Faktur Kasir:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ faktur",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada transaksi penjualan pada tanggal ini."
                },
                order: [[1, 'desc']]
            });

            $('#table-keuangan-harian').DataTable({
                language: {
                    search: "Cari Catatan Kas:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ kas",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada catatan mutasi kas pada tanggal ini."
                },
                order: [[0, 'desc']]
            });
        }

        // Lihat Detail Faktur Penjualan Kasir
        $(document).on('click', '.btn-detail-faktur', function() {
            const noFaktur = $(this).data('nofaktur');
            activeFakturHarian = noFaktur;

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

        // Cetak Struk Ulang dari Modal
        $('#btn-cetak-dari-modal').on('click', function() {
            if (activeFakturHarian) {
                window.open('<?= base_url('pos/cetak-struk') ?>/' + activeFakturHarian, '_blank', 'width=420,height=600');
            }
        });

        // Hapus Per Item Dari Faktur Penjualan (Kembalikan Stok Toko)
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
                        $('#modalDetailPenjualan').modal('hide');
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

