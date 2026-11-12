<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card-modern p-4">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-person-gear fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Ubah Profil Pengguna</h5>
                    <small class="text-muted">Kelola nama tampilan kasir dan kata sandi login Anda</small>
                </div>
            </div>

            <form action="<?= base_url('profile/update') ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Kode User (Username)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="form-control bg-light fw-bold" value="<?= esc($user['kode_user'] ?? session()->get('kode_user')) ?>" readonly>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Kode User unik tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Role / Hak Akses</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                        <input type="text" class="form-control bg-light text-capitalize" value="<?= esc($user['role'] ?? session()->get('role')) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nama Lengkap Tampilan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                        <input type="text" name="nama_user" class="form-control" value="<?= esc($user['nama_user'] ?? session()->get('nama_user')) ?>" placeholder="Nama lengkap kasir" required>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <label class="form-label small fw-semibold text-dark mb-1">
                        <i class="bi bi-key-fill text-warning me-1"></i> Ganti Password Baru (Opsional)
                    </label>
                    <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                        Kosongkan bagian ini jika Anda tidak ingin mengubah password saat ini.
                    </p>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password_baru" id="input-password-baru" class="form-control" placeholder="Ketik kata sandi baru (minimal 4 karakter)">
                        <button class="btn btn-outline-secondary border-start-0 border" type="button" id="btn-toggle-new-pass">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-light rounded-pill px-4 flex-fill">Kembali</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-fill fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const btnToggle = document.getElementById('btn-toggle-new-pass');
    const inputPass = document.getElementById('input-password-baru');
    btnToggle.addEventListener('click', function() {
        if (inputPass.type === 'password') {
            inputPass.type = 'text';
            btnToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
        } else {
            inputPass.type = 'password';
            btnToggle.innerHTML = '<i class="bi bi-eye"></i>';
        }
    });
</script>
<?= $this->endSection() ?>

