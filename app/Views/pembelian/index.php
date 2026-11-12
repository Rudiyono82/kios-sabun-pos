<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-truck text-primary me-2"></i>Daftar Pembelian Supplier & Restock</h4>
        <p class="text-muted small mb-0">Riwayat faktur pembelian barang masuk dari supplier dengan rincian item barang dan otorisasi hapus transaksi.</p>
    </div>
    <div>
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
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Dari Tanggal:</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= esc($tgl_mulai) ?>">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold mb-1">Sampai Tanggal:</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= esc($tgl_selesai) ?>">
        </div>
        <div class="col-12 col-md-4">
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
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <a href="<?= base_url('pembelian') ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabel Daftar Pembelian -->
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-pembelian">
            <thead class="table-light small text-uppercase">
                <tr>
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
                        <tr>
                            <td class="fw-bold font-monospace text-primary"><?= esc($r['no_beli']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($r['nama_supplier'] ?: 'Supplier Umum') ?></div>
                                <small class="text-muted font-monospace"><?= esc($r['kode_supplier'] ?: '-') ?></small>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($r['kode_user']) ?></span></td>
                            <td>
                                <span class="badge <?= $r['status_bayar'] === 'Lunas' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill">
                                    <?= esc($r['status_bayar']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">
                                Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted"><?= esc($r['catatan'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-detail-pembelian" data-nobeli="<?= esc($r['no_beli']) ?>" title="Lihat Detail Item">
                                        <i class="bi bi-eye-fill"></i> Detail
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
                        <strong class="text-success small" id="detail-status"></strong>
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
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-pembelian').DataTable({
                language: {
                    search: "Cari Nota:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ pembelian",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada transaksi pembelian pada periode ini."
                },
                order: [[1, 'desc']]
            });
        }

        // 1. Klik Lihat Detail Pembelian
        $(document).on('click', '.btn-detail-pembelian', function() {
            const noBeli = $(this).data('nobeli');
            
            $.get('<?= base_url('pembelian/detail') ?>/' + noBeli, function(res) {
                if (res.status === 'success') {
                    const p = res.pembelian;
                    $('#detail-no-beli').text(p.no_beli);
                    $('#detail-tgl').text(p.tanggal);
                    $('#detail-supplier').text(p.nama_supplier || 'Supplier Umum');
                    $('#detail-status').text(p.status_bayar);
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

        // 2. Klik Hapus Pembelian
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
