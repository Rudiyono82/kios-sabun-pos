<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="badge bg-danger-subtle text-danger border border-danger-subtle mb-1">
                <i class="bi bi-shield-lock-fill me-1"></i> Khusus Administrator
            </div>
            <h5 class="fw-bold mb-1">Monitoring Karyawan & Akun Pengguna</h5>
            <small class="text-muted">Pantau aktivitas akun kasir, performa transaksi per kasir, dan kelola hak akses sistem</small>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#modalKaryawan">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Karyawan Baru
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="table-karyawan">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Kode / NIP</th>
                    <th>Nama Karyawan</th>
                    <th>Hak Akses (Role)</th>
                    <th>Cabang Home &amp; Roaming</th>
                    <th>Telepon / WA</th>
                    <th class="text-center">Total Transaksi</th>
                    <th class="text-end">Kontribusi Omzet</th>
                    <th class="text-center">Status Akun</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <span class="fw-bold text-primary"><?= esc($u['kode_user']) ?></span>
                            <?php if (!empty($u['nip'])): ?>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">NIP: <?= esc($u['nip']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= esc($u['nama_user']) ?></div>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-shield-check"></i> Administrator</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-person"></i> Kasir (Operator)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-shop text-primary me-1"></i><?= esc($u['nama_cabang'] ?? 'Cabang Pusat') ?>
                            </span>
                            <?php if ($u['role'] === 'admin' || !empty($u['can_switch_cabang'])): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle d-block mt-1" style="font-size: 0.65rem;" title="Bisa berpindah cabang operasional saat bertugas">
                                    <i class="bi bi-arrow-repeat me-1"></i>Kasir Roaming
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($u['telepon'] ?: '-') ?></td>
                        <td class="text-center fw-bold"><?= (int) $u['total_transaksi'] ?> Transaksi</td>
                        <td class="text-end fw-bold text-dark">Rp <?= number_format($u['total_omzet_kasir'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php if ($u['is_active'] == 1): ?>
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-slash-circle"></i> Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary btn-edit-user" data-user='<?= json_encode($u) ?>' title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <a href="<?= base_url('karyawan/toggle-status/' . $u['id']) ?>" class="btn btn-outline-<?= $u['is_active'] == 1 ? 'warning' : 'success' ?>" title="<?= $u['is_active'] == 1 ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                    <i class="bi bi-<?= $u['is_active'] == 1 ? 'lock-fill' : 'unlock-fill' ?>"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah / Edit Karyawan -->
<div class="modal fade" id="modalKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="<?= base_url('karyawan/simpan') ?>" method="POST">
                <input type="hidden" name="id" id="form-user-id">

                <div class="modal-header bg-primary text-white py-3 px-4" id="modalKaryawanHeader">
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalUserTitle"><i class="bi bi-person-plus-fill me-2"></i>Tambah Karyawan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kode User</label>
                            <input type="text" name="kode_user" id="form-kode_user" class="form-control text-uppercase" placeholder="Contoh: KASIR14" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">NIP Karyawan</label>
                            <input type="text" name="nip" id="form-nip" class="form-control" placeholder="Contoh: P200920002">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Nama Lengkap Karyawan</label>
                            <input type="text" name="nama_user" id="form-nama_user" class="form-control" placeholder="Nama lengkap petugas" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Hak Akses (Role)</label>
                            <select name="role" id="form-role" class="form-select">
                                <option value="kasir">Kasir (Hanya Penjualan POS)</option>
                                <option value="admin">Administrator (Akses Penuh & Profit)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">No. Telepon / WA</label>
                            <input type="text" name="telepon" id="form-telepon" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Cabang Penugasan (Home Branch)</label>
                            <select name="id_cabang" id="form-id_cabang" class="form-select" required>
                                <?php foreach ($cabangList as $cb): ?>
                                    <option value="<?= $cb['id'] ?>">
                                        <?= esc($cb['nama_cabang']) ?> (<?= esc($cb['kode_cabang']) ?>) <?= ($cb['is_pusat'] == 1 ? '- PUSAT' : '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="can_switch_cabang" id="form-can_switch_cabang" value="1">
                                <label class="form-check-label fw-semibold text-dark small" for="form-can_switch_cabang">
                                    Izin Kasir Fleksibel / Roaming (Dapat beralih bertugas di cabang mana saja)
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted" id="label-pass">Password</label>
                            <input type="password" name="password" id="form-password" class="form-control" placeholder="Minimal 4 karakter">
                            <small class="text-muted" id="help-pass" style="font-size: 0.75rem;">Password default: <code>123456</code> (jika dikosongkan untuk user baru)</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="form-is_active" value="1" checked>
                                <label class="form-check-label fw-semibold text-dark" for="form-is_active">
                                    Akun Aktif (Dapat Login ke Program)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium">Simpan Data Karyawan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#table-karyawan').DataTable({
        language: { search: "Cari Karyawan:" }
    });

    $('.btn-edit-user').on('click', function() {
        const u = $(this).data('user');
        $('#form-user-id').val(u.id);
        $('#modalKaryawanHeader').removeClass('bg-primary text-white').addClass('bg-warning text-dark');
        $('#modalKaryawanHeader .btn-close').removeClass('btn-close-white');
        $('#modalUserTitle').removeClass('text-white').addClass('text-dark').html('<i class="bi bi-pencil-square me-2"></i>Edit Karyawan: ' + u.nama_user);
        $('#form-kode_user').val(u.kode_user).prop('readonly', true);
        $('#form-nip').val(u.nip);
        $('#form-nama_user').val(u.nama_user);
        $('#form-role').val(u.role);
        $('#form-telepon').val(u.telepon);
        $('#form-id_cabang').val(u.id_cabang || 1);
        $('#form-can_switch_cabang').prop('checked', u.can_switch_cabang == 1 || u.role === 'admin');
        $('#form-is_active').prop('checked', u.is_active == 1);
        $('#label-pass').text('Reset Password (Kosongkan jika tidak diubah)');
        $('#help-pass').text('Kosongkan jika ingin tetap memakai password lama.');
        $('#form-password').val('');
        $('#modalKaryawan').modal('show');
    });

    $('#modalKaryawan').on('hidden.bs.modal', function() {
        $('#form-user-id').val('');
        $('#modalKaryawanHeader').removeClass('bg-warning text-dark').addClass('bg-primary text-white');
        $('#modalKaryawanHeader .btn-close').addClass('btn-close-white');
        $('#modalUserTitle').removeClass('text-dark').addClass('text-white').html('<i class="bi bi-person-plus-fill me-2"></i>Tambah Karyawan Baru');
        $('#form-kode_user').prop('readonly', false);
        $('#label-pass').text('Password');
        $('#help-pass').html('Password default: <code>123456</code> (jika dikosongkan untuk user baru)');
        $('#form-can_switch_cabang').prop('checked', false);
        $('form')[0].reset();
    });
</script>
<?= $this->endSection() ?>

