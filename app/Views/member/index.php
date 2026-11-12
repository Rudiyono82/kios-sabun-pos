<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Member & Reward Point</h4>
        <p class="text-muted small mb-0">Kelola pelanggan loyal, saldo poin belanja, serta monitoring voucher reward yang bisa memotong nilai belanja di kasir.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMember">
            <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Member Baru
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

<!-- Baris Metrik Monitoring Poin & Voucher -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-semibold">Total Member Aktif</span>
            <h3 class="fw-bold mt-1 mb-0 text-primary"><?= number_format($total_member) ?></h3>
            <small class="text-muted">Pelanggan terdaftar</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-warning">
            <span class="text-muted small text-uppercase fw-semibold">Total Poin Member</span>
            <h3 class="fw-bold mt-1 mb-0 text-warning"><?= number_format($total_poin, 0, ',', '.') ?></h3>
            <small class="text-muted">Saldo poin terkumpul</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-semibold">Potensi Nilai Voucher Beredar</span>
            <h3 class="fw-bold mt-1 mb-0 text-success">Rp <?= number_format($total_nilai_voucher_beredar, 0, ',', '.') ?></h3>
            <small class="text-muted">Nilai tukar (1 Poin = Rp 50)</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card-modern p-3 border-start border-4 border-info">
            <span class="text-muted small text-uppercase fw-semibold">Voucher Aktif Belum Dipakai</span>
            <h3 class="fw-bold mt-1 mb-0 text-info">Rp <?= number_format($total_voucher_aktif_rp, 0, ',', '.') ?></h3>
            <small class="text-muted">Siap memotong transaksi kasir</small>
        </div>
    </div>
</div>

<!-- Tab Navigasi: Data Member vs Monitoring Voucher -->
<div class="card-modern p-4">
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="memberTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="tab-members-btn" data-bs-toggle="pill" data-bs-target="#tab-members" type="button" role="tab">
                <i class="bi bi-people me-1"></i> Data Member & Saldo Poin
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="tab-vouchers-btn" data-bs-toggle="pill" data-bs-target="#tab-vouchers" type="button" role="tab">
                <i class="bi bi-ticket-perforated me-1"></i> Monitoring Voucher Belanja (<?= count($voucher_list) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="memberTabContent">
        <!-- ============================================== -->
        <!-- TAB 1: DATA MEMBER & SALDO POIN               -->
        <!-- ============================================== -->
        <div class="tab-pane fade show active" id="tab-members" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-member">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Kode Member</th>
                            <th>Nama Pelanggan</th>
                            <th>No. WhatsApp</th>
                            <th class="text-end">Total Belanja</th>
                            <th class="text-center">Saldo Poin</th>
                            <th class="text-end">Nilai Voucher (Rp)</th>
                            <th class="text-center">Voucher Aktif</th>
                            <th class="text-center">Aksi / Reward</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace fw-bold"><?= esc($m['kode_member']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= esc($m['nama']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 180px;"><?= esc($m['alamat'] ?: '-') ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-whatsapp text-success me-1"></i>
                                    <span class="small font-monospace"><?= esc($m['telepon'] ?: '-') ?></span>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    Rp <?= number_format($m['total_belanja'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold fs-6 px-3 py-1">
                                        <i class="bi bi-star-fill text-warning me-1"></i><?= number_format($m['point'], 0, ',', '.') ?> Poin
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success font-monospace">
                                    Rp <?= number_format($m['nilai_voucher'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($m['voucher_aktif_count'] > 0): ?>
                                        <span class="badge bg-success rounded-pill px-2 py-1">
                                            <i class="bi bi-ticket-detailed-fill me-1"></i><?= $m['voucher_aktif_count'] ?> Voucher
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Tombol Tukar Poin Jadi Voucher -->
                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold rounded-pill px-2 btn-tukar-poin shadow-sm"
                                            data-kode="<?= esc($m['kode_member']) ?>"
                                            data-nama="<?= esc($m['nama']) ?>"
                                            data-poin="<?= (float)$m['point'] ?>"
                                            title="Tukar Poin jadi Voucher Belanja">
                                            <i class="bi bi-ticket-perforated-fill me-1"></i> Tukar Voucher
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill btn-edit-member" data-member='<?= json_encode($m) ?>' title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <a href="<?= base_url('member/hapus/' . $m['id']) ?>" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Hapus data member ini?')" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: MONITORING VOUCHER BELANJA MEMBER       -->
        <!-- ============================================== -->
        <div class="tab-pane fade" id="tab-vouchers" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-voucher">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Kode Voucher</th>
                            <th>Nama Member</th>
                            <th>Tgl Terbit</th>
                            <th class="text-center">Poin Ditukar</th>
                            <th class="text-end">Nilai Pemotong Belanja (Rp)</th>
                            <th class="text-center">Status Voucher</th>
                            <th>Keterangan Pemakaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($voucher_list)): ?>
                            <?php foreach ($voucher_list as $v): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-primary">
                                        <i class="bi bi-ticket-perforated-fill me-1 text-warning"></i><?= esc($v['kode_voucher']) ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($v['nama_member']) ?></div>
                                        <small class="text-muted font-monospace"><?= esc($v['kode_member']) ?></small>
                                    </td>
                                    <td class="small"><?= date('d/m/Y', strtotime($v['tgl_voucher'])) ?></td>
                                    <td class="text-center font-monospace fw-semibold text-warning-emphasis">
                                        <?= number_format($v['point_ditukar'], 0, ',', '.') ?> Poin
                                    </td>
                                    <td class="text-end fw-bold text-success font-monospace fs-6">
                                        Rp <?= number_format($v['nilai_voucher'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($v['is_aktif'] == 1): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-1 fw-bold">
                                                <i class="bi bi-check2-circle me-1"></i> Aktif (Siap Pakai)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">
                                                <i class="bi bi-check-all me-1"></i> Sudah Digunakan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?php if ($v['is_aktif'] == 0 && !empty($v['no_faktur_terpakai'])): ?>
                                            Terpakai pada Faktur: <strong class="text-primary"><?= esc($v['no_faktur_terpakai']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-success"><i class="bi bi-check-lg me-1"></i>Bisa digunakan saat transaksi di kasir</span>
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

<!-- ============================================== -->
<!-- MODAL TUKAR POIN MENJADI VOUCHER RESMI         -->
<!-- ============================================== -->
<div class="modal fade" id="modalTukarPoin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-ticket-perforated-fill me-2"></i>Tukar Poin Menjadi Voucher Belanja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('member/tukar-poin') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_member" id="tukar-kode-member">

                <div class="modal-body p-4">
                    <!-- Info Member -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Nama Member:</span>
                            <span class="fw-bold text-dark" id="tukar-nama-member">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Saldo Poin Saat Ini:</span>
                            <span class="fw-bold text-warning-emphasis fs-5" id="tukar-saldo-poin">0 Poin</span>
                        </div>
                    </div>

                    <!-- Input Jumlah Poin yang Ingin Ditukar -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jumlah Poin yang Ingin Ditukar <span class="text-danger">*</span></label>
                        <input type="number" name="point_tukar" id="input-point-tukar" class="form-control form-control-lg text-center fw-bold fs-4 text-warning-emphasis" min="1" step="1" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Kurs: <strong>1 Poin = Rp 50</strong></small>
                            <a href="javascript:void(0)" class="small fw-bold text-primary" id="btn-tukar-semua-poin">Tukar Semua Poin</a>
                        </div>
                    </div>

                    <!-- Display Nilai Voucher Rupiah yang Dihasilkan -->
                    <div class="p-3 rounded-3 text-center" style="background-color: #f0fdf4; border: 2px dashed #86efac;">
                        <span class="small text-uppercase text-muted fw-bold">NILAI VOUCHER POTONGAN BELANJA:</span>
                        <h2 class="display-6 fw-bold text-success mb-0" id="display-nilai-voucher-tukar">Rp 0</h2>
                        <small class="text-muted d-block mt-1">Voucher ini langsung bisa memotong tagihan belanja member di kasir.</small>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Terbitkan Voucher Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL DAFTAR & EDIT MEMBER                     -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="<?= base_url('member/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-member-id">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalMemberTitle">Daftar Member Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Kode Member</label>
                        <input type="text" name="kode_member" id="form-kode_member" class="form-control" value="<?= esc($kode_otomatis) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Pelanggan / Usaha</label>
                        <input type="text" name="nama" id="form-nama_member" class="form-control" placeholder="Contoh: Bpk Budi / Laundry Cemerlang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">No. WhatsApp / HP</label>
                        <input type="text" name="telepon" id="form-telepon" class="form-control" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat</label>
                        <textarea name="alamat" id="form-alamat" class="form-control" rows="2" placeholder="Alamat pelanggan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let maxPoinMember = 0;

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-member').DataTable({
                language: {
                    search: "Cari Member:",
                    lengthMenu: "Tampilkan _MENU_ member",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" }
                },
                order: [[3, 'desc']]
            });

            $('#table-voucher').DataTable({
                language: {
                    search: "Cari Voucher:",
                    lengthMenu: "Tampilkan _MENU_ voucher",
                    paginate: { next: "Berikutnya", previous: "Sebelumnya" }
                },
                order: [[2, 'desc']]
            });
        }

        // Buka Modal Tukar Poin
        $(document).on('click', '.btn-tukar-poin', function() {
            const kode = $(this).data('kode');
            const nama = $(this).data('nama');
            maxPoinMember = parseFloat($(this).data('poin')) || 0;

            if (maxPoinMember <= 0) {
                Swal.fire('Poin Kosong', 'Member ini belum memiliki saldo poin belanja.', 'info');
                return;
            }

            $('#tukar-kode-member').val(kode);
            $('#tukar-nama-member').text(nama + ' (' + kode + ')');
            $('#tukar-saldo-poin').text(new Intl.NumberFormat('id-ID').format(maxPoinMember) + ' Poin');
            $('#input-point-tukar').val(maxPoinMember).attr('max', maxPoinMember);

            hitungNilaiVoucher();
            $('#modalTukarPoin').modal('show');
        });

        $('#input-point-tukar').on('input', hitungNilaiVoucher);

        $('#btn-tukar-semua-poin').on('click', function() {
            $('#input-point-tukar').val(maxPoinMember);
            hitungNilaiVoucher();
        });

        function hitungNilaiVoucher() {
            const poin = parseFloat($('#input-point-tukar').val()) || 0;
            const nilai = poin * 50; // 1 Poin = Rp 50
            $('#display-nilai-voucher-tukar').text('Rp ' + new Intl.NumberFormat('id-ID').format(nilai));
        }

        // Edit Member
        $(document).on('click', '.btn-edit-member', function() {
            const data = $(this).data('member');
            $('#form-member-id').val(data.id);
            $('#form-kode_member').val(data.kode_member);
            $('#form-nama_member').val(data.nama);
            $('#form-telepon').val(data.telepon);
            $('#form-alamat').val(data.alamat);
            $('#modalMemberTitle').text('Edit Data Member');
            $('#modalTambahMember').modal('show');
        });

        $('#modalTambahMember').on('hidden.bs.modal', function() {
            $('#form-member-id').val('');
            $('#modalMemberTitle').text('Daftar Member Baru');
        });
    });
</script>
<?= $this->endSection() ?>
