<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-shop-window text-secondary me-2"></i>Master Cabang & Profil Identitas Toko</h4>
        <p class="text-muted small mb-0">Atur identitas resmi outlet/cabang toko, alamat lengkap, kontak WhatsApp, dan teks ucapan/kebijakan retur pada struk thermal kasir.</p>
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

<div class="row g-4">
    <!-- FORM EDIT PROFIL IDENTITAS TOKO / CABANG -->
    <div class="col-12 col-lg-7">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                <i class="bi bi-pencil-square text-primary me-2"></i>Formulir Identitas Cabang & Toko
            </h5>
            
            <form action="<?= base_url('cabang/update') ?>" method="POST">
                <input type="hidden" name="id" value="<?= esc($profil['id'] ?? 1) ?>">

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nama Cabang / Toko Resmi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shop"></i></span>
                        <input type="text" name="nama_toko" id="input-nama-toko" class="form-control fw-bold fs-6" value="<?= esc($profil['nama_toko'] ?? '') ?>" required>
                    </div>
                    <small class="text-muted">Nama ini akan tercetak paling atas dengan huruf tebal pada semua struk dan laporan.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Alamat Baris 1</label>
                        <input type="text" name="alamat_1" id="input-alamat-1" class="form-control" value="<?= esc($profil['alamat_1'] ?? '') ?>" placeholder="Misal: Jl. Metland Cileungsi,">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Alamat Baris 2 (Blok / No)</label>
                        <input type="text" name="alamat_2" id="input-alamat-2" class="form-control" value="<?= esc($profil['alamat_2'] ?? '') ?>" placeholder="Misal: Blok BD.04, No.17">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Kota / Kabupaten</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" name="kota" id="input-kota" class="form-control" value="<?= esc($profil['kota'] ?? '') ?>" placeholder="Misal: Cileungsi - Bogor">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">No. Telepon / WhatsApp CS</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" name="telepon" id="input-telepon" class="form-control" value="<?= esc($profil['telepon'] ?? '') ?>" placeholder="Misal: WA: 085711159296">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Catatan Footer Struk Kasir</label>
                    <textarea name="footer_struk" id="input-footer" class="form-control" rows="3" placeholder="Pesan ucapan terima kasih, kebijakan penukaran kemasan jerigen, atau akun sosial media..."><?= esc($profil['footer_struk'] ?? '') ?></textarea>
                    <small class="text-muted">Teks ini akan tercetak di bagian paling bawah struk thermal transaksi kasir.</small>
                </div>

                <?php if (session()->get('role') === 'admin'): ?>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Identitas Toko
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Mode Kasir: Hanya akun level Administrator yang memiliki otorisasi mengubah profil identitas toko.
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- LIVE PREVIEW SIMULATOR STRUK THERMAL -->
    <div class="col-12 col-lg-5">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                <i class="bi bi-receipt text-warning me-2"></i>Live Simulator Struk Kasir (58mm)
            </h5>
            <p class="text-muted small">Pratinjau tampilan header & footer struk yang otomatis diperbarui saat Anda mengetik data di formulir sebelah kiri:</p>

            <div class="p-3 mx-auto shadow-sm" style="width: 290px; background-color: #fff; border: 1px dashed #94a3b8; font-family: 'Courier New', Courier, monospace; font-size: 11px; line-height: 1.4; color: #000;">
                <!-- Header Struk -->
                <div class="text-center pb-2 border-bottom border-dark border-dashed">
                    <div class="fw-bold fs-6" id="prev-nama-toko"><?= esc($profil['nama_toko'] ?? '(KSP) Kios Sabun & Parfum') ?></div>
                    <div id="prev-alamat-1"><?= esc($profil['alamat_1'] ?? 'Jl. Metland Cileungsi,') ?></div>
                    <div id="prev-alamat-2"><?= esc($profil['alamat_2'] ?? 'Blok BD.04, No.17') ?></div>
                    <div id="prev-kota"><?= esc($profil['kota'] ?? 'Cileungsi - Bogor') ?></div>
                    <div id="prev-telepon"><?= esc($profil['telepon'] ?? 'WA: 085711159296') ?></div>
                </div>

                <!-- Info Dummy Transaksi -->
                <div class="py-2 border-bottom border-dark border-dashed small" style="font-size: 10px;">
                    <div>No. Faktur : J2611120001</div>
                    <div>Kasir      : <?= esc(session()->get('nama_user') ?? 'KASIR1') ?></div>
                    <div>Waktu      : <?= date('d/m/Y H:i') ?></div>
                </div>

                <!-- Dummy Item -->
                <div class="py-2 border-bottom border-dark border-dashed small" style="font-size: 10px;">
                    <div class="fw-bold">Deterjen Cair Laundry 5L</div>
                    <div class="d-flex justify-content-between">
                        <span>1 x 48.000</span>
                        <span>48.000</span>
                    </div>
                    <div class="fw-bold mt-1">Parfum Downy Black 1L</div>
                    <div class="d-flex justify-content-between">
                        <span>1 x 35.000</span>
                        <span>35.000</span>
                    </div>
                </div>

                <!-- Dummy Total -->
                <div class="py-2 border-bottom border-dark border-dashed small" style="font-size: 10px;">
                    <div class="d-flex justify-content-between">
                        <span>TOTAL</span>
                        <span class="fw-bold">Rp 83.000</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>BAYAR (CASH)</span>
                        <span>Rp 100.000</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>KEMBALI</span>
                        <span>Rp 17.000</span>
                    </div>
                </div>

                <!-- Footer Struk -->
                <div class="text-center pt-2" style="font-size: 10px;">
                    <div id="prev-footer" class="fst-italic"><?= esc($profil['footer_struk'] ?? 'Terima Kasih Atas Kunjungan Anda!') ?></div>
                    <div class="mt-2 text-muted" style="font-size: 9px;">*** Layanan Konsumen Terdaftar ***</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updatePreview() {
        $('#prev-nama-toko').text($('#input-nama-toko').val() || 'Nama Toko');
        $('#prev-alamat-1').text($('#input-alamat-1').val());
        $('#prev-alamat-2').text($('#input-alamat-2').val());
        $('#prev-kota').text($('#input-kota').val());
        $('#prev-telepon').text($('#input-telepon').val());
        $('#prev-footer').text($('#input-footer').val());
    }

    $('#input-nama-toko, #input-alamat-1, #input-alamat-2, #input-kota, #input-telepon, #input-footer').on('input', updatePreview);
});
</script>

<?= $this->endSection() ?>

