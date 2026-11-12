<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Buku Kas & Biaya Operasional Toko</h4>
        <p class="text-muted small mb-0">Catat dan pantau seluruh pengeluaran rutin toko (listrik, plastik, lakban, jerigen, konsumsi, bensin) serta saldo kas riil toko.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalBiayaKeluar">
            <i class="bi bi-dash-circle me-1"></i> Catat Biaya Pengeluaran
        </button>
        <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalKasMasuk">
            <i class="bi bi-plus-circle me-1"></i> Catat Kas Masuk
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

<!-- Baris Metrik Kas & Laba Riil -->
<div class="row g-3 mb-4">
    <!-- Saldo Kas Hari Ini -->
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Saldo Kas di Toko Hari Ini</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success">Rp <?= number_format($saldo_kas_hari_ini, 0, ',', '.') ?></h3>
                    <small class="text-muted">Kas Awal + Penjualan Tunai - Beban Hari Ini</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pengeluaran Biaya Periode Ini -->
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Biaya Toko (Periode Ini)</span>
                    <h3 class="fw-bold mt-1 mb-0 text-danger">Rp <?= number_format($total_keluar, 0, ',', '.') ?></h3>
                    <small class="text-muted">Total biaya operasional keluar</small>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                    <i class="bi bi-receipt-cutoff fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Laba Bersih Riil Toko (Khusus Admin) -->
    <div class="col-12 col-md-4">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">
                        <?= session()->get('role') === 'admin' ? 'Laba Bersih Riil Toko' : 'Total Pemasukan Kas' ?>
                    </span>
                    <?php if (session()->get('role') === 'admin'): ?>
                        <h3 class="fw-bold mt-1 mb-0 text-primary">Rp <?= number_format($laba_bersih_riil, 0, ',', '.') ?></h3>
                        <small class="text-muted">Margin Kotor - Beban Operasional Toko</small>
                    <?php else: ?>
                        <h3 class="fw-bold mt-1 mb-0 text-primary">Rp <?= number_format($total_masuk, 0, ',', '.') ?></h3>
                        <small class="text-muted">Akumulasi uang masuk periode ini</small>
                    <?php endif; ?>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Periode & Tabel Arus Kas -->
<div class="card-modern p-4">
    <form action="<?= base_url('keuangan') ?>" method="GET" class="row g-2 align-items-center mb-4">
        <div class="col-12 col-md-3">
            <label class="small fw-bold text-muted">Dari Tanggal</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= esc($tgl_mulai) ?>">
        </div>
        <div class="col-12 col-md-3">
            <label class="small fw-bold text-muted">Sampai Tanggal</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= esc($tgl_selesai) ?>">
        </div>
        <div class="col-12 col-md-2 mt-md-4">
            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                <i class="bi bi-funnel me-1"></i> Terapkan Filter
            </button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-keuangan">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Waktu / Tanggal</th>
                    <th>Jenis Arus Kas</th>
                    <th>Nama Pengeluaran / Pemasukan</th>
                    <th>Keterangan / Catatan</th>
                    <th class="text-end">Nominal (Rp)</th>
                    <th>Petugas</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transaksi_list)): ?>
                    <?php foreach ($transaksi_list as $t): ?>
                        <?php
                            $isKasAwal = ($t['jenis_keuangan'] === 'Kas Awal');
                            $isPenjualan = ($t['jenis_keuangan'] === 'Pemasukan' && (stripos($t['nama_keuangan'], 'Penjualan') !== false || stripos($t['nama_keuangan'], 'J2') !== false || stripos($t['keterangan'] ?? '', 'Kasir') !== false));
                            $isLocked = !empty($t['is_locked']);
                        ?>
                        <tr>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                            <td>
                                <?php if ($t['jenis_keuangan'] === 'Pemasukan'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-arrow-down-left me-1"></i> Pemasukan
                                    </span>
                                <?php elseif ($t['jenis_keuangan'] === 'Pengeluaran'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-arrow-up-right me-1"></i> Pengeluaran
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="bi bi-cash-stack me-1"></i> Kas Awal
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <?= esc($t['nama_keuangan']) ?>
                                    <?php if ($isLocked): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 0.72rem;" title="Flag Khusus: Hanya akun Administrator langsung yang berhak menghapus">
                                            <i class="bi bi-lock-fill me-1"></i>Khusus Admin
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="small text-muted"><?= esc($t['keterangan'] ?: '-') ?></td>
                            <td class="text-end fw-bold font-monospace <?= $t['jenis_keuangan'] === 'Pengeluaran' ? 'text-danger' : 'text-success' ?>">
                                <?= $t['jenis_keuangan'] === 'Pengeluaran' ? '-' : '+' ?>Rp <?= number_format($t['total'], 0, ',', '.') ?>
                            </td>
                            <td class="small text-muted"><?= esc($t['kode_user']) ?></td>
                            <td class="text-center">
                                <?php if ($isKasAwal): ?>
                                    <span class="badge bg-secondary-subtle text-muted border py-1 px-2" title="Modal kas awal laci tidak dapat dihapus manual">
                                        <i class="bi bi-shield-lock-fill me-1"></i> Kas Awal
                                    </span>
                                <?php elseif ($isPenjualan): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2" title="Pemasukan dari transaksi kasir otomatis (Batalkan melalui menu Faktur Penjualan)">
                                        <i class="bi bi-receipt me-1"></i> Penjualan
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2 btn-hapus-keuangan"
                                        data-id="<?= $t['id'] ?>"
                                        data-nama="<?= esc($t['nama_keuangan']) ?>"
                                        data-nominal="<?= number_format($t['total'], 0, ',', '.') ?>"
                                        data-locked="<?= $isLocked ? '1' : '0' ?>"
                                        title="<?= $isLocked ? 'Hapus (Khusus Administrator)' : 'Hapus Catatan Kas (Otorisasi Super Admin)' ?>">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. MODAL CATAT BIAYA PENGELUARAN OPERASIONAL   -->
<!-- ============================================== -->
<div class="modal fade" id="modalBiayaKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-dash-circle me-2"></i>Catat Biaya Pengeluaran Toko</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('keuangan/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="jenis_keuangan" value="Pengeluaran">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kategori Beban / Pengeluaran <span class="text-danger">*</span></label>
                        <select name="nama_keuangan" class="form-select select2" required>
                            <option value="">-- Pilih Jenis Pengeluaran --</option>
                            <?php foreach ($kategori_list as $k): ?>
                                <?php if ($k['jenis'] === 'Pengeluaran'): ?>
                                    <option value="<?= esc($k['nama_kategori']) ?>"><?= esc($k['nama_kategori']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="Biaya Operasional Lainnya">Lainnya / Biaya Operasional Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nominal Biaya (Rp) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="total" class="form-control form-control-lg fw-bold text-danger fs-4 input-currency" placeholder="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan / Rincian Pengeluaran</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Beli lakban 2 roll dan plastik packing 1 ikat..."></textarea>
                    </div>

                    <div class="form-check form-switch p-2 bg-light rounded border">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_locked" value="1" id="switch-locked-keluar">
                        <label class="form-check-label small fw-bold text-dark" for="switch-locked-keluar">
                            <i class="bi bi-shield-lock-fill text-danger me-1"></i> Tandai Flag Khusus Admin (Hanya Akun Administrator yang Bisa Menghapus)
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. MODAL CATAT PEMASUKAN KAS LAINNYA           -->
<!-- ============================================== -->
<div class="modal fade" id="modalKasMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Catat Kas Masuk Toko</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('keuangan/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="jenis_keuangan" value="Pemasukan">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Pemasukan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_keuangan" class="form-control" placeholder="Contoh: Tambahan Modal Kas / Kompensasi..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nominal Pemasukan (Rp) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="total" class="form-control form-control-lg fw-bold text-success fs-4 input-currency" placeholder="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan / Catatan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Rincian sumber kas masuk..."></textarea>
                    </div>

                    <div class="form-check form-switch p-2 bg-light rounded border">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_locked" value="1" id="switch-locked-masuk">
                        <label class="form-check-label small fw-bold text-dark" for="switch-locked-masuk">
                            <i class="bi bi-shield-lock-fill text-danger me-1"></i> Tandai Flag Khusus Admin (Hanya Akun Administrator yang Bisa Menghapus)
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Kas Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const currentUserRole = '<?= session()->get('role') ?>';

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-keuangan').DataTable({
                language: {
                    search: "Cari Arus Kas:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" },
                    emptyTable: "Belum ada catatan transaksi arus kas pada periode ini."
                },
                order: [[0, 'desc']]
            });
        }

        // Hapus Catatan Kas dengan Otorisasi
        $(document).on('click', '.btn-hapus-keuangan', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const nominal = $(this).data('nominal');
            const isLocked = $(this).data('locked') == '1';

            // Jika item ditandai flag khusus dan yang login bukan admin, tolak langsung
            if (isLocked && currentUserRole !== 'admin') {
                Swal.fire({
                    title: 'Akses Khusus Administrator',
                    html: `
                        <p class="text-danger small mb-0">
                            <i class="bi bi-shield-lock-fill me-1 fs-5"></i>
                            Catatan kas <strong>${nama}</strong> ditandai dengan <strong>Flag Khusus Admin</strong>.<br>Hanya akun <strong>Administrator</strong> yang sedang login yang memiliki kewenangan menghapus data ini.
                        </p>
                    `,
                    icon: 'warning'
                });
                return;
            }

            if (currentUserRole === 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Kas',
                    html: `
                        <p class="text-danger small mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Anda login sebagai <strong>Administrator</strong>.
                        </p>
                        <p class="small mb-1">Hapus catatan kas <strong>${nama}</strong> sebesar <strong>Rp ${formatRupiah(nominal)}</strong>?</p>
                        <p class="small text-muted mb-0">Pembukuan saldo kas toko akan disesuaikan kembali.</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Catatan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusKeuangan(id, '');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Otorisasi Super Admin',
                    html: `
                        <p class="text-danger small mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Menghapus catatan kas <strong>${nama}</strong> sebesar <strong>Rp ${nominal}</strong> memerlukan izin Super Admin.
                        </p>
                        <label class="form-label small fw-bold d-block text-start">Masukkan Password Super Admin:</label>
                        <input type="password" id="swal-admin-keuangan-pw" class="form-control" placeholder="Password Admin...">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Otorisasi & Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const pw = document.getElementById('swal-admin-keuangan-pw').value;
                        if (!pw) {
                            Swal.showValidationMessage('Password Super Admin wajib diisi!');
                            return false;
                        }
                        return pw;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        eksekusiHapusKeuangan(id, result.value);
                    }
                });
            }
        });

        function eksekusiHapusKeuangan(id, passwordAdmin) {
            Swal.fire({
                title: 'Menghapus Catatan Kas...',
                text: 'Menyesuaikan saldo kas toko...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('<?= base_url('keuangan/hapus') ?>/' + id, { password_admin: passwordAdmin }, function(res) {
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

