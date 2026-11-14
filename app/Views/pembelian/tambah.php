<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-cart-plus-fill text-primary me-2"></i>Input Pembelian Supplier Baru</h4>
        <p class="text-muted small mb-0">Catat transaksi pembelian bahan baku kimia / kemasan / barang jadi dari supplier dengan pembaruan stok otomatis.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="$('#btn-simpan-pembelian').click()" title="Simpan transaksi pembelian ini">
            <i class="bi bi-check-circle-fill"></i> Simpan Transaksi
        </button>
        <a href="<?= base_url('pembelian') ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pembelian
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- FORM HEADER PEMBELIAN -->
    <div class="col-12 col-lg-4">
        <!-- 1. Ringkasan Total Tagihan & Tombol Simpan (Ditaruh di PALING ATAS agar Langsung Kelihatan) -->
        <div class="card-modern p-4 mb-3 text-center" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.15); position: sticky; top: 80px; z-index: 10;">
            <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">TOTAL BIAYA PEMBELIAN</span>
            <h2 class="display-6 fw-bold text-success my-2" id="display-total-biaya">Rp 0</h2>
            <div id="beli-pembulatan-info" class="small text-muted mb-2 d-none font-monospace"></div>
            <span class="badge bg-white text-success border border-success-subtle px-3 py-1 font-monospace" id="display-total-item">0 Item Barang Masuk</span>
            
            <button type="button" class="btn btn-success btn-lg w-100 rounded-pill py-2 mt-3 fw-bold shadow d-flex align-items-center justify-content-center gap-2" id="btn-simpan-pembelian">
                <i class="bi bi-check-circle-fill fs-5"></i> Simpan Transaksi Pembelian
            </button>
        </div>

        <!-- 2. Informasi Faktur Supplier -->
        <div class="card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Informasi Faktur Supplier</h6>
            <div class="mb-3">
                <label class="form-label small fw-bold mb-1">Nomor Nota Beli</label>
                <input type="text" id="beli-no-beli" class="form-control form-control-sm font-monospace fw-bold text-primary bg-light" value="<?= esc($no_beli) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold mb-1">Tanggal Pembelian</label>
                <input type="date" id="beli-tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold mb-1">Pilih Mitra Supplier <span class="text-danger">*</span></label>
                <select id="beli-supplier" class="form-select form-select-sm" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= esc($s['kode_supplier']) ?>" data-nama="<?= esc($s['nama_supplier']) ?>">
                            <?= esc($s['nama_supplier']) ?> (<?= esc($s['kode_supplier']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold mb-1">Status Pembayaran</label>
                <select id="beli-status-bayar" class="form-select form-select-sm">
                    <option value="Lunas">Lunas (Potong Kas)</option>
                    <option value="Hutang / Tempo">Hutang / Tempo (Kredit)</option>
                </select>
            </div>
            <div class="mb-0">
                <label class="form-label small fw-bold mb-1">Catatan Pembelian</label>
                <textarea id="beli-catatan" class="form-control form-control-sm" rows="2" placeholder="No. invoice supplier / ekspedisi / catatan..."></textarea>
            </div>
        </div>
    </div>

    <!-- DAFTAR ITEM BELANJA & INPUT BARANG -->
    <div class="col-12 col-lg-8">
        <div class="card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2"><i class="bi bi-plus-circle me-2 text-primary"></i>Pilih Barang Masuk / Restock</h6>
            
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold mb-1">Cari Produk / Bahan Baku</label>
                    <select id="pilih-barang" class="form-select form-select-sm">
                        <option value="">-- Pilih Barang / Bahan --</option>
                        <?php 
                        $isAdmin = (session()->get('role') === 'admin');
                        foreach ($barangs as $b): 
                            $stokLabel = $isAdmin ? ' - Stok: ' . number_format($b['stok'], 0, ',', '.') . ' ' . esc($b['satuan']) : '';
                        ?>
                            <option value="<?= esc($b['id_barang']) ?>"
                                data-nama="<?= esc($b['nama_barang']) ?>"
                                data-satuan="<?= esc($b['satuan']) ?>"
                                data-hargabeli="<?= (float)$b['harga_beli'] ?>"
                                data-hargajual="<?= (float)$b['harga_jual'] ?>"
                                data-hargagrosir="<?= (float)$b['harga_grosir'] ?>"
                                data-qtypaketa="<?= (float)$b['qty_paket_a'] ?>"
                                data-hrgpaketa="<?= (float)$b['hrg_paket_a'] ?>"
                                data-qtypaketb="<?= (float)$b['qty_paket_b'] ?>"
                                data-hrgpaketb="<?= (float)$b['hrg_paket_b'] ?>"
                                data-qtypaketc="<?= (float)$b['qty_paket_c'] ?>"
                                data-hrgpaketc="<?= (float)$b['hrg_paket_c'] ?>"
                                <?= $isAdmin ? 'data-stok="' . (float)$b['stok'] . '"' : '' ?>>
                                <?= esc($b['nama_barang']) ?> (<?= esc($b['id_barang']) ?>)<?= $stokLabel ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Qty Beli</label>
                    <input type="number" id="input-qty-beli" class="form-control form-control-sm text-center fw-bold" value="1" min="0.1" step="any">
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold mb-0">Harga Beli Modal (Rp)</label>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-primary fw-semibold" id="btn-trigger-ubah-harga" style="font-size: 0.74rem;" title="Klik jika harga dari supplier berubah">
                            <i class="bi bi-pencil-square"></i> Ubah Harga
                        </button>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted">Rp</span>
                        <input type="text" inputmode="numeric" id="input-harga-beli" class="form-control form-control-sm text-end fw-bold bg-light input-currency" placeholder="0" readonly title="Terkunci dari Master Barang. Klik 'Ubah Harga' jika ada perubahan dari supplier.">
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.70rem;"><i class="bi bi-lock-fill text-secondary me-1"></i>Terkunci dari Master Barang</small>
                </div>
                <div class="col-12 col-md-2">
                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btn-tambah-item">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel Item Pembelian Masuk -->
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-basket3 me-2 text-primary"></i>Daftar Barang Masuk (Keranjang Restock)</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-keranjang-beli">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Barang</th>
                            <th class="text-center" style="width: 90px;">Qty</th>
                            <th class="text-end" style="width: 140px;">Harga Beli (Rp)</th>
                            <th class="text-end" style="width: 150px;">Subtotal (Rp)</th>
                            <th class="text-center" style="width: 50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="keranjang-beli-tbody">
                        <tr id="empty-row-beli">
                            <td colspan="6" class="text-center text-muted py-5 small">
                                <i class="bi bi-box-seam fs-1 opacity-25 d-block mb-2"></i>
                                Keranjang pembelian masih kosong. Silakan pilih produk di atas dan tentukan jumlah serta harga belinya.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Khusus Penyesuaian Harga Master Barang (Approval Admin) -->
<div class="modal fade" id="modalUbahHargaBarang" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning text-dark p-2 rounded-3">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Penyesuaian Harga Master Barang</h5>
                        <small class="text-light text-opacity-75" style="font-size: 0.78rem;">Pembaruan harga modal &amp; harga jual dengan proteksi otorisasi Admin</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Barang Terpilih -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-secondary font-monospace" id="modal-info-idbarang">KODE</span>
                            <span class="badge bg-info bg-opacity-10 text-info font-monospace ms-1" id="modal-info-satuan">SATUAN</span>
                            <h6 class="fw-bold text-dark mt-1 mb-0" id="modal-info-namabarang">Nama Produk Sabun</h6>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-3 py-1">
                                <i class="bi bi-shield-check me-1"></i> Privilege: <?= session()->get('role') === 'admin' ? 'Admin (Direct)' : 'Kasir (Perlu Password Admin)' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <form id="form-modal-ubah-harga">
                    <input type="hidden" id="modal-id-barang">

                    <div class="row g-3">
                        <!-- 1. HARGA BELI MODAL -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-primary-subtle bg-primary bg-opacity-10 h-100">
                                <label class="form-label small fw-bold text-primary mb-1">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Harga Beli Modal (Supplier)
                                </label>
                                <div class="mb-2">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Harga Beli Master Saat Ini:</small>
                                    <div class="fw-bold text-dark fs-6" id="display-harga-beli-lama">Rp 0</div>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-dark mb-1">Harga Beli Baru (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" inputmode="numeric" id="modal-harga-beli-baru" class="form-control fw-bold text-end fs-6 input-currency" placeholder="0" required>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">Nominal faktur beli baru dari supplier</small>
                                </div>
                            </div>
                        </div>

                        <!-- 2. HARGA JUAL ECERAN -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-success-subtle bg-success bg-opacity-10 h-100">
                                <label class="form-label small fw-bold text-success mb-1">
                                    <i class="bi bi-tag-fill me-1"></i>Harga Jual Eceran Kasir
                                </label>
                                <div class="mb-2">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Harga Jual Saat Ini:</small>
                                    <div class="fw-bold text-dark fs-6" id="display-harga-jual-lama">Rp 0</div>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-dark mb-1">Harga Jual Baru (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" inputmode="numeric" id="modal-harga-jual-baru" class="form-control fw-bold text-end fs-6 text-success input-currency" placeholder="Kosongkan jika tetap">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">Sesuaikan agar margin laba toko tetap terjaga</small>
                                </div>
                            </div>
                        </div>

                        <!-- 3. HARGA GROSIR RESELLER & PAKET GROSIR -->
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold text-dark mb-0">Harga Grosir Reseller &amp; Paket Diskon Bertingkat</label>
                                    <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaketGrosir">
                                        <i class="bi bi-chevron-down me-1"></i> Opsi Paket A/B/C
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Harga Grosir Standar Saat Ini:</small>
                                        <div class="fw-semibold text-dark small" id="display-harga-grosir-lama">Rp 0</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">Baru Rp</span>
                                            <input type="text" inputmode="numeric" id="modal-harga-grosir-baru" class="form-control text-end fw-semibold input-currency" placeholder="Kosongkan jika tetap">
                                        </div>
                                    </div>
                                </div>

                                <div class="collapse mt-3" id="collapsePaketGrosir">
                                    <div class="border-top pt-2">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <small class="fw-bold text-muted d-block">Paket A</small>
                                                <div class="input-group input-group-sm mb-1">
                                                    <span class="input-group-text">Min Qty</span>
                                                    <input type="number" id="modal-qty-paket-a" class="form-control text-center" placeholder="0">
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" id="modal-hrg-paket-a" class="form-control text-end input-currency" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="fw-bold text-muted d-block">Paket B</small>
                                                <div class="input-group input-group-sm mb-1">
                                                    <span class="input-group-text">Min Qty</span>
                                                    <input type="number" id="modal-qty-paket-b" class="form-control text-center" placeholder="0">
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" id="modal-hrg-paket-b" class="form-control text-end input-currency" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="fw-bold text-muted d-block">Paket C</small>
                                                <div class="input-group input-group-sm mb-1">
                                                    <span class="input-group-text">Min Qty</span>
                                                    <input type="number" id="modal-qty-paket-c" class="form-control text-center" placeholder="0">
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" inputmode="numeric" id="modal-hrg-paket-c" class="form-control text-end input-currency" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (session()->get('role') === 'admin'): ?>
                        <div class="alert alert-success mt-3 mb-0 py-2 px-3 small border-0 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check fs-5"></i>
                            <div><strong>Login sebagai Administrator:</strong> Perubahan harga akan langsung tersimpan dan diterapkan ke Master Barang tanpa verifikasi password.</div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small border-0 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-5 text-warning"></i>
                            <div><strong>Privilege Kasir:</strong> Sistem akan meminta konfirmasi password Supervisor / Admin saat menyimpan untuk menjamin integritas harga jual &amp; beli toko.</div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm" id="btn-simpan-ubah-harga">
                    <i class="bi bi-check2-circle me-1"></i> Simpan &amp; Terapkan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let keranjangBeli = [];
    const isAdmin = <?= session()->get('role') === 'admin' ? 'true' : 'false' ?>;

    /**
     * Logika Pembulatan Otomatis Hasil Perhitungan Akhir (Kelipatan 500)
     */
    function roundPosTotal(amount) {
        amount = parseFloat(amount) || 0;
        if (amount <= 0) return 0;
        if (amount < 1000) {
            if (Math.round(amount) === 500) return 500;
            return 1000;
        }
        let base = Math.floor(amount / 500) * 500;
        let remainder = amount - base;
        if (remainder > 0 && remainder <= 250) {
            return base;
        } else if (remainder > 250) {
            return base + 500;
        }
        return amount;
    }

    $(document).ready(function() {
        // Saat barang dipilih dari dropdown, isi harga beli default (terkunci dari master)
        $('#pilih-barang').on('change', function() {
            const $opt = $(this).find(':selected');
            if ($opt.val()) {
                const harga = parseFloat($opt.data('hargabeli')) || 0;
                $('#input-harga-beli').val(formatRupiah(harga));
                $('#input-qty-beli').focus().select();
            } else {
                $('#input-harga-beli').val('');
            }
        });

        // 1. Trigger Buka Modal Ubah Harga
        $('#btn-trigger-ubah-harga').on('click', function(e) {
            e.preventDefault();
            const $opt = $('#pilih-barang').find(':selected');
            const idBarang = $opt.val();
            if (!idBarang) {
                Swal.fire('Pilih Barang', 'Silakan pilih barang pada dropdown terlebih dahulu untuk mengubah harganya.', 'info');
                $('#pilih-barang').focus();
                return;
            }

            const nama = $opt.data('nama');
            const satuan = $opt.data('satuan');
            const hargabeli = parseFloat($opt.data('hargabeli')) || 0;
            const hargajual = parseFloat($opt.data('hargajual')) || 0;
            const hargagrosir = parseFloat($opt.data('hargagrosir')) || 0;
            const qtypaketa = parseFloat($opt.data('qtypaketa')) || 0;
            const hrgpaketa = parseFloat($opt.data('hrgpaketa')) || 0;
            const qtypaketb = parseFloat($opt.data('qtypaketb')) || 0;
            const hrgpaketb = parseFloat($opt.data('hrgpaketb')) || 0;
            const qtypaketc = parseFloat($opt.data('qtypaketc')) || 0;
            const hrgpaketc = parseFloat($opt.data('hrgpaketc')) || 0;

            $('#modal-id-barang').val(idBarang);
            $('#modal-info-idbarang').text(idBarang);
            $('#modal-info-satuan').text(satuan);
            $('#modal-info-namabarang').text(nama);

            $('#display-harga-beli-lama').text('Rp ' + new Intl.NumberFormat('id-ID').format(hargabeli));
            $('#modal-harga-beli-baru').val(hargabeli > 0 ? formatRupiah(hargabeli) : '');

            $('#display-harga-jual-lama').text('Rp ' + new Intl.NumberFormat('id-ID').format(hargajual));
            $('#modal-harga-jual-baru').val(hargajual > 0 ? formatRupiah(hargajual) : '');

            $('#display-harga-grosir-lama').text('Rp ' + new Intl.NumberFormat('id-ID').format(hargagrosir));
            $('#modal-harga-grosir-baru').val(hargagrosir > 0 ? formatRupiah(hargagrosir) : '');

            $('#modal-qty-paket-a').val(qtypaketa > 0 ? qtypaketa : '');
            $('#modal-hrg-paket-a').val(hrgpaketa > 0 ? formatRupiah(hrgpaketa) : '');
            $('#modal-qty-paket-b').val(qtypaketb > 0 ? qtypaketb : '');
            $('#modal-hrg-paket-b').val(hrgpaketb > 0 ? formatRupiah(hrgpaketb) : '');
            $('#modal-qty-paket-c').val(qtypaketc > 0 ? qtypaketc : '');
            $('#modal-hrg-paket-c').val(hrgpaketc > 0 ? formatRupiah(hrgpaketc) : '');

            $('#modalUbahHargaBarang').modal('show');
            setTimeout(() => { $('#modal-harga-beli-baru').focus().select(); }, 400);
        });

        // 2. Klik Simpan Perubahan Harga pada Modal
        $('#btn-simpan-ubah-harga').on('click', function() {
            const idBarang = $('#modal-id-barang').val();
            const hargaBeliBaru = unformatRupiah($('#modal-harga-beli-baru').val()) || 0;
            const hargaJualBaru = unformatRupiah($('#modal-harga-jual-baru').val()) || 0;
            const hargaGrosirBaru = unformatRupiah($('#modal-harga-grosir-baru').val()) || 0;
            const qtyPaketA = parseFloat($('#modal-qty-paket-a').val()) || 0;
            const hrgPaketA = unformatRupiah($('#modal-hrg-paket-a').val()) || 0;
            const qtyPaketB = parseFloat($('#modal-qty-paket-b').val()) || 0;
            const hrgPaketB = unformatRupiah($('#modal-hrg-paket-b').val()) || 0;
            const qtyPaketC = parseFloat($('#modal-qty-paket-c').val()) || 0;
            const hrgPaketC = unformatRupiah($('#modal-hrg-paket-c').val()) || 0;

            if (hargaBeliBaru <= 0) {
                Swal.fire('Input Tidak Valid', 'Harga Beli Baru harus diisi dan lebih besar dari 0.', 'warning');
                $('#modal-harga-beli-baru').focus();
                return;
            }

            const payload = {
                id_barang: idBarang,
                harga_beli: hargaBeliBaru,
                harga_jual: hargaJualBaru,
                harga_grosir: hargaGrosirBaru,
                qty_paket_a: qtyPaketA,
                hrg_paket_a: hrgPaketA,
                qty_paket_b: qtyPaketB,
                hrg_paket_b: hrgPaketB,
                qty_paket_c: qtyPaketC,
                hrg_paket_c: hrgPaketC
            };

            if (isAdmin) {
                // Jika Admin yang login: Langsung proses tanpa popup password
                eksekusiUpdateHarga(payload);
            } else {
                // Jika Kasir yang login: Wajib popup password Admin
                Swal.fire({
                    title: 'Otorisasi Admin Diperlukan',
                    html: `
                        <div class="text-start small text-muted mb-3">
                            Perubahan harga beli &amp; harga jual master barang memerlukan persetujuan Supervisor / Admin toko. Masukkan password Admin untuk melanjutkan:
                        </div>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="bi bi-shield-lock-fill text-warning"></i></span>
                            <input type="password" id="swal-admin-password" class="form-control text-center fs-5 fw-bold" placeholder="Password Admin" autocomplete="new-password">
                        </div>
                    `,
                    icon: 'shield-lock',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Validasi &amp; Setujui',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#eab308',
                    cancelButtonColor: '#6c757d',
                    focusConfirm: false,
                    didOpen: () => {
                        setTimeout(() => { $('#swal-admin-password').focus(); }, 200);
                    },
                    preConfirm: () => {
                        const pwd = $('#swal-admin-password').val().trim();
                        if (!pwd) {
                            Swal.showValidationMessage('Password Admin wajib dimasukkan!');
                            return false;
                        }
                        return pwd;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        payload.admin_password = result.value;
                        eksekusiUpdateHarga(payload);
                    }
                });
            }
        });

        function eksekusiUpdateHarga(payload) {
            Swal.fire({
                title: 'Menyimpan Perubahan Harga...',
                text: 'Sedang memperbarui Master Barang',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '<?= base_url('pembelian/update-harga-barang') ?>',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    $('#modalUbahHargaBarang').modal('hide');
                    Swal.fire({
                        title: 'Harga Berhasil Diperbarui!',
                        text: res.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Update data pada option terpilih
                    const $opt = $('#pilih-barang').find(`option[value="${res.data.id_barang}"]`);
                    if ($opt.length) {
                        $opt.data('hargabeli', res.data.harga_beli);
                        $opt.data('hargajual', res.data.harga_jual);
                        $opt.data('hargagrosir', res.data.harga_grosir);
                        $opt.data('qtypaketa', res.data.qty_paket_a);
                        $opt.data('hrgpaketa', res.data.hrg_paket_a);
                        $opt.data('qtypaketb', res.data.qty_paket_b);
                        $opt.data('hrgpaketb', res.data.hrg_paket_b);
                        $opt.data('qtypaketc', res.data.qty_paket_c);
                        $opt.data('hrgpaketc', res.data.hrg_paket_c);
                    }

                    // Update nilai input harga beli di form restock
                    $('#input-harga-beli').val(formatRupiah(res.data.harga_beli));
                },
                error: function(xhr) {
                    const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui harga barang!';
                    Swal.fire('Otorisasi Ditolak!', err, 'error');
                }
            });
        }

        // Tombol Tambah Item
        $('#btn-tambah-item').on('click', function() {
            const $opt = $('#pilih-barang').find(':selected');
            const idBarang = $opt.val();
            if (!idBarang) {
                Swal.fire('Peringatan', 'Silakan pilih barang yang akan dibeli.', 'warning');
                return;
            }

            const namaBarang = $opt.data('nama');
            const satuan = $opt.data('satuan');
            const qty = parseFloat($('#input-qty-beli').val()) || 0;
            const hargaBeli = unformatRupiah($('#input-harga-beli').val()) || 0;

            if (qty <= 0) {
                Swal.fire('Peringatan', 'Qty beli harus lebih dari 0.', 'warning');
                return;
            }

            // Cek apakah item sudah ada di keranjang
            const existing = keranjangBeli.find(x => x.id_barang === idBarang);
            if (existing) {
                existing.qty += qty;
                existing.harga_beli = hargaBeli;
                existing.subtotal = existing.qty * hargaBeli;
            } else {
                keranjangBeli.push({
                    id_barang: idBarang,
                    nama_barang: namaBarang,
                    satuan: satuan,
                    qty: qty,
                    harga_beli: hargaBeli,
                    subtotal: qty * hargaBeli
                });
            }

            renderKeranjangBeli();

            // Reset input
            $('#pilih-barang').val('').trigger('change');
            $('#input-qty-beli').val(1);
            $('#input-harga-beli').val('');
        });

        // Hapus Item
        $(document).on('click', '.btn-hapus-item-beli', function() {
            const idx = $(this).data('index');
            keranjangBeli.splice(idx, 1);
            renderKeranjangBeli();
        });

        function renderKeranjangBeli() {
            const $tbody = $('#keranjang-beli-tbody');
            $tbody.empty();

            if (keranjangBeli.length === 0) {
                $tbody.append(`
                    <tr id="empty-row-beli">
                        <td colspan="6" class="text-center text-muted py-5 small">
                            <i class="bi bi-box-seam fs-1 opacity-25 d-block mb-2"></i>
                            Keranjang pembelian masih kosong. Silakan pilih produk di atas dan tentukan jumlah serta harga belinya.
                        </td>
                    </tr>
                `);
                $('#display-total-biaya').text('Rp 0');
                $('#beli-pembulatan-info').addClass('d-none').text('');
                $('#display-total-item').text('0 Item Barang Masuk');
                return;
            }

            let total = 0;
            let totalQty = 0;

            keranjangBeli.forEach((item, idx) => {
                total += item.subtotal;
                totalQty += item.qty;

                $tbody.append(`
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>
                            <div class="fw-semibold text-dark">${item.nama_barang}</div>
                            <small class="text-muted font-monospace">${item.id_barang} &bull; ${item.satuan}</small>
                        </td>
                        <td class="text-center fw-bold">${new Intl.NumberFormat('id-ID').format(item.qty)}</td>
                        <td class="text-end">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_beli)}</td>
                        <td class="text-end fw-bold text-dark">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle py-0 px-1 btn-hapus-item-beli" data-index="${idx}">
                                <i class="bi bi-x"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            let roundedTotal = roundPosTotal(total);
            let pembulatan = roundedTotal - total;

            $('#display-total-biaya').text('Rp ' + new Intl.NumberFormat('id-ID').format(roundedTotal));
            if (pembulatan !== 0) {
                $('#beli-pembulatan-info').removeClass('d-none').text(`Pembulatan: ${pembulatan > 0 ? '+' : ''}Rp ${new Intl.NumberFormat('id-ID').format(pembulatan)} (Asli: Rp ${new Intl.NumberFormat('id-ID').format(total)})`);
            } else {
                $('#beli-pembulatan-info').addClass('d-none').text('');
            }
            $('#display-total-item').text(`${keranjangBeli.length} Macam Produk (${new Intl.NumberFormat('id-ID').format(totalQty)} Satuan)`);
        }

        // Simpan Transaksi Pembelian
        $('#btn-simpan-pembelian').on('click', function() {
            if (keranjangBeli.length === 0) {
                Swal.fire('Keranjang Kosong', 'Tambahkan minimal satu barang ke dalam keranjang sebelum menyimpan.', 'warning');
                return;
            }

            const kodeSupplier = $('#beli-supplier').val();
            const namaSupplier = $('#beli-supplier').find(':selected').data('nama') || '';

            if (!kodeSupplier) {
                Swal.fire('Peringatan', 'Silakan pilih Mitra Supplier terlebih dahulu.', 'warning');
                $('#beli-supplier').focus();
                return;
            }

            const payload = {
                kode_supplier: kodeSupplier,
                nama_supplier: namaSupplier,
                tanggal: $('#beli-tanggal').val(),
                status_bayar: $('#beli-status-bayar').val(),
                catatan: $('#beli-catatan').val(),
                items: keranjangBeli
            };

            Swal.fire({
                title: 'Konfirmasi Simpan Pembelian',
                text: 'Pastikan data dan harga beli sudah sesuai. Stok barang akan langsung otomatis bertambah ke sistem toko.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Pembelian',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#16a34a'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Transaksi...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: '<?= base_url('pembelian/simpan') ?>',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function(resp) {
                            Swal.fire('Berhasil Disimpan!', resp.message, 'success').then(() => {
                                window.location.href = '<?= base_url('pembelian') ?>';
                            });
                        },
                        error: function(xhr) {
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memproses data.';
                            Swal.fire('Gagal Menyimpan!', err, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>

