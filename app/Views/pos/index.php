<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Baris Petunjuk Shortcut Keyboard Kasir POS Cepat -->
<div class="card-modern p-2 px-3 mb-3 bg-white border d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm">
    <div class="d-flex flex-wrap align-items-center gap-2 small">
        <span class="fw-bold text-dark me-1"><i class="bi bi-keyboard-fill text-primary me-1"></i>Shortcut Cepat:</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-dark text-white px-1">F1</kbd> Umum/Member</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-primary text-white px-1">F2</kbd> Scan Barcode</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-warning text-dark px-1">F3</kbd> Cari Member</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-success text-white px-1">F4</kbd> Tukar Jerigen</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-secondary text-white px-1">F6</kbd> Diskon</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-warning text-dark px-1">F7</kbd> Voucher</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-danger text-white px-1">F8</kbd> Kosongkan</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-success text-white px-1">F9</kbd> Checkout/Bayar</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-info text-dark px-1">F10</kbd> Cetak Struk</span>
        <span class="badge bg-light text-dark border"><kbd class="bg-secondary text-white px-1">Esc</kbd> Batal/Reset</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($has_kas_awal)): ?>
            <span class="badge bg-primary text-white border border-primary px-2 py-1 shadow-sm" id="badge-status-pos" title="Modal Kas Awal Hari Ini: Rp <?= number_format($nominal_kas_awal, 0, ',', '.') ?>">
                <i class="bi bi-unlock-fill me-1"></i>(OPEN TRANSACTION) <span class="d-none d-lg-inline">&bull; Kas: Rp <?= number_format($nominal_kas_awal, 0, ',', '.') ?></span>
            </span>
        <?php else: ?>
            <button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm" id="btn-trigger-kas-awal" title="Uang Kas Kasir Masih Rp 0. Klik untuk input kas awal laci!">
                <i class="bi bi-lock-fill me-1"></i>(CLOSE TRANSACTION) Input Kas Awal!
            </button>
        <?php endif; ?>
        <a href="<?= base_url('laporan/cetak-struk-harian') ?>" target="_blank" onclick="window.open(this.href, 'StrukHarian', 'width=440,height=700'); return false;" class="btn btn-sm btn-outline-dark fw-bold shadow-sm" title="Cetak Struk Rekap Harian / Tutup Shift Kasir untuk Diserahkan ke Owner">
            <i class="bi bi-printer-fill text-warning me-1"></i>Struk Rekap Harian
        </a>
        <span class="badge bg-primary-subtle text-primary border d-none d-md-inline-block"><i class="bi bi-lightning-charge-fill me-1"></i>Kasir Cepat Tanpa Mouse</span>
    </div>
</div>

<div class="row g-3">
    <!-- Panel Kiri: Input & Keranjang Kasir -->
    <div class="col-12 col-lg-8">
        
        <!-- Baris Kontrol: Jenis Pembeli & Pencarian Barang (Desain Ramping & Luas) -->
        <div class="card-modern p-3 mb-3">
            <div class="row g-2 align-items-center">
                <!-- Jenis Pembeli: UMUM vs MEMBER -->
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted fw-bold text-nowrap mb-0">
                            <i class="bi bi-person-badge me-1"></i>Pembeli <kbd class="bg-dark text-white font-monospace" style="font-size: 0.72rem;">[F1]</kbd>:
                        </label>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="customer_type" id="type-umum" value="umum" checked>
                            <label class="btn btn-outline-primary px-3 fw-semibold" for="type-umum">
                                <i class="bi bi-person"></i> Umum
                            </label>

                            <input type="radio" class="btn-check" name="customer_type" id="type-member" value="member">
                            <label class="btn btn-outline-primary px-3 fw-semibold" for="type-member">
                                <i class="bi bi-star-fill text-warning me-1"></i> Member
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Info No Faktur -->
                <div class="col-12 col-md-6 text-md-end">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <span class="text-muted me-1">No Faktur:</span>
                        <span class="fw-bold text-primary font-monospace" id="label-faktur"><?= esc($no_faktur) ?></span>
                    </span>
                </div>
            </div>

            <!-- Area Input Member (Muncul saat tipe Member dipilih) -->
            <div id="member-section" class="mt-2 pt-2 border-top d-none">
                <div class="position-relative">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="member-search-input" class="form-control border-start-0" placeholder="Ketik No HP WhatsApp / Nama Member [F3] (Gunakan Panah Atas/Bawah & Enter)..." autocomplete="off">
                        <span class="input-group-text bg-warning-subtle text-warning-emphasis fw-bold font-monospace border-start-0">[F3]</span>
                        <button class="btn btn-outline-secondary" type="button" id="btn-reset-member" title="Reset Member [Esc]">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                    </div>

                    <!-- Dropdown Suggestion Member -->
                    <div id="member-dropdown" class="list-group position-absolute w-100 shadow-lg mt-1 d-none" style="z-index: 1060; max-height: 240px; overflow-y: auto;"></div>
                </div>

                <!-- Badge Member yang Telah Terpilih -->
                <div id="selected-member-badge" class="mt-2 p-2 bg-success bg-opacity-10 rounded border border-success-subtle d-none d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        <div>
                            <span class="fw-bold text-success text-dark mb-0" id="selected-member-nama">Nama Member</span>
                            <small class="text-muted d-block" id="selected-member-detail">WA: 08xx &bull; Poin: 0</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btn-remove-selected-member" title="Ganti Member [F3]">
                        <i class="bi bi-x"></i> Ganti
                    </button>
                </div>

                <!-- Box Pemakaian Voucher Poin Member Sesuai Kuota Member -->
                <div id="voucher-member-container" class="mt-2 p-2 bg-warning bg-opacity-10 rounded border border-warning-subtle d-none">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="small fw-bold text-dark mb-0">
                            <i class="bi bi-ticket-perforated-fill text-warning me-1"></i> Voucher Poin Member <kbd class="bg-warning text-dark font-monospace" style="font-size: 0.7rem;">[F7]</kbd>:
                        </label>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-warning text-dark font-monospace" id="label-voucher-tersedia">0 Voucher</span>
                            <button type="button" class="btn btn-xs btn-outline-warning text-dark bg-white border-warning fw-bold py-0 px-2 rounded-pill shadow-sm" id="btn-modal-tukar-poin" title="Tukar Poin Member Jadi Voucher Belanja">
                                <i class="bi bi-arrow-repeat text-primary me-1"></i>Tukar Poin
                            </button>
                        </div>
                    </div>
                    <select id="select-voucher-pos" class="form-select form-select-sm fw-semibold">
                        <option value="" data-potongan="0">-- Jangan Gunakan Voucher --</option>
                    </select>
                    <div id="voucher-point-notice" class="small text-muted mt-1 d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                        <span><i class="bi bi-star-fill text-warning me-1"></i>Kuota Poin: <strong id="notice-kuota-poin" class="text-dark">0 Poin</strong></span>
                        <span>Senilai Kuota: <strong class="text-success" id="notice-kuota-rp">Rp 0</strong></span>
                    </div>
                </div>
            </div>

            <!-- Input Barcode / Nama Barang (Dibuat Lebih Ramping & Compact) -->
            <div class="mt-2 pt-2 border-top">
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0 py-1 px-2">
                            <i class="bi bi-upc-scan fs-5 text-primary"></i>
                        </span>
                        <input type="text" id="barcode-input" class="form-control border-start-0 py-2 ps-1" style="font-size: 0.95rem;" placeholder="Scan Barcode / Ketik Nama Barang [F2] (Panah Atas/Bawah & Enter untuk Memilih)..." autofocus autocomplete="off">
                        <button class="btn btn-primary px-3 fw-bold" type="button" id="btn-focus-barcode" title="Fokus Scan Barcode [F2]">
                            <kbd class="bg-white text-primary font-monospace fw-bold">[F2] Scan</kbd>
                        </button>
                    </div>
                    <!-- Dropdown Suggestion Barang -->
                    <div id="search-dropdown" class="list-group position-absolute w-100 shadow-lg mt-1 d-none" style="z-index: 1050; max-height: 280px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>

        <!-- Tabel Item Belanja Kasir -->
        <div class="card-modern p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cart3 me-2 text-primary"></i>Daftar Belanja</h6>
                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" id="btn-kosongkan" title="Kosongkan Keranjang Belanja [F8]">
                    <i class="bi bi-trash"></i> Kosongkan <kbd class="bg-danger text-white font-monospace" style="font-size: 0.7rem;">[F8]</kbd>
                </button>
            </div>

            <div class="table-responsive" style="min-height: 320px; max-height: 440px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="table-cart">
                    <thead class="table-light small text-uppercase sticky-top">
                        <tr>
                            <th style="width: 40%;">Produk</th>
                            <th class="text-end" style="width: 18%;">Harga</th>
                            <th class="text-center" style="width: 18%;">Qty</th>
                            <th class="text-end" style="width: 16%;">Subtotal</th>
                            <th class="text-center" style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart-tbody">
                        <tr id="empty-cart-row">
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-cart-x fs-1 text-secondary opacity-50"></i>
                                <p class="mt-2 mb-0">Keranjang masih kosong. Scan barcode atau cari produk di atas.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Total & Tombol Buka Popup Pembayaran -->
    <div class="col-12 col-lg-4">
        <div class="card-modern p-3 mb-3">
            <span class="text-muted small text-uppercase fw-semibold">Total Tagihan</span>
            <div class="display-6 fw-bold text-primary mt-1 mb-2" id="display-grand-total">Rp 0</div>

            <div class="border-top pt-2">
                <!-- Subtotal -->
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal Produk</span>
                    <span class="fw-semibold text-dark" id="display-subtotal">Rp 0</span>
                </div>

                <!-- Diskon Manual -->
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="text-muted">Potongan Diskon <kbd class="bg-secondary text-white font-monospace" style="font-size: 0.7rem;">[F6]</kbd></span>
                    <input type="text" inputmode="numeric" id="input-diskon" class="form-control form-control-sm text-end w-50 input-currency" value="0">
                </div>

                <!-- Potongan Voucher Belanja Member -->
                <div id="row-potongan-voucher" class="d-flex justify-content-between mb-2 small d-none text-success">
                    <span><i class="bi bi-ticket-perforated-fill me-1"></i>Voucher Member <kbd class="bg-warning text-dark font-monospace" style="font-size: 0.7rem;">[F7]</kbd></span>
                    <span class="fw-bold" id="display-potongan-voucher">-Rp 0</span>
                </div>

                <!-- Tukar Jerigen 5 Liter (@ Rp 1.000) -->
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-check-label small fw-semibold text-dark" for="chk-jerigen">
                            <i class="bi bi-recycle text-success me-1"></i> Tukar Jerigen 5L <kbd class="bg-success text-white font-monospace" style="font-size: 0.7rem;">[F4]</kbd>
                        </label>
                        <input class="form-check-input" type="checkbox" id="chk-jerigen">
                    </div>
                    <div id="jerigen-container" class="d-none mt-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">Qty:</span>
                            <input type="number" id="input-qty-jerigen" class="form-control text-center" value="1" min="1">
                            <span class="input-group-text bg-white">x @Rp 1.000</span>
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Potongan: <span class="fw-bold text-success" id="label-potongan-jerigen">Rp 1.000</span></small>
                    </div>
                </div>

                <!-- Ringkasan Item -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Total Macam Produk:</span>
                        <span class="fw-bold text-dark" id="display-total-item">0 Item</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Total Kuantitas (Qty):</span>
                        <span class="fw-bold text-dark" id="display-total-qty">0 Pcs/Liter</span>
                    </div>
                </div>

                <!-- Tombol Buka Popup Pembayaran (Lebih Lega & Bersih) -->
                <button type="button" id="btn-buka-modal-bayar" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-wallet2 fs-3"></i> BAYAR / CHECKOUT <kbd class="bg-white text-success fs-5 px-2 py-0 ms-2 font-monospace">[F9]</kbd>
                </button>
                <small class="text-muted text-center d-block mt-2" style="font-size: 0.78rem;">Tekan tombol <strong class="text-success">[F9]</strong> pada keyboard untuk checkout instan.</small>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL POPUP WINDOW PEMBAYARAN KASIR (F9) -->
<!-- ========================================== -->
<div class="modal fade" id="modalBayar" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 fs-5"></i>
                    <h6 class="modal-title fw-bold text-white mb-0">Pembayaran Kasir</h6>
                    <span class="badge bg-white text-primary font-monospace ms-2" id="modal-bayar-faktur-label"></span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 pt-2 pb-2">
                <!-- Display Total Tagihan Besar di Popup -->
                <div class="text-center p-2 rounded-3 mb-2" style="background-color: #f0fdf4; border: 2px dashed #86efac;">
                    <div class="d-flex justify-content-between text-success small pb-1 mb-1 border-bottom d-none" id="popup-row-voucher">
                        <span><i class="bi bi-ticket-perforated-fill me-1"></i>Voucher Member (<span id="popup-voucher-code"></span>):</span>
                        <strong id="popup-voucher-discount">-Rp 0</strong>
                    </div>
                    <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">TOTAL HARUS DIBAYAR</span>
                    <h2 class="fw-bold text-success mb-0" id="popup-display-total" style="font-size: 2.2rem;">Rp 0</h2>
                </div>

                <!-- Pilihan Metode Pembayaran -->
                <div class="mb-2">
                    <label class="small text-muted fw-bold mb-1 d-block text-uppercase" style="font-size: 0.72rem;">
                        Metode Pembayaran (Tekan <kbd class="bg-primary text-white">C</kbd> / <kbd class="bg-info text-dark">Q</kbd> / <kbd class="bg-secondary text-white">T</kbd>)
                    </label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="metode_bayar" id="pay-cash" value="Cash" checked>
                        <label class="btn btn-outline-primary py-2 fw-semibold d-flex align-items-center justify-content-center gap-1" for="pay-cash">
                            <i class="bi bi-cash"></i> Cash <kbd class="bg-light text-dark font-monospace border ms-1" style="font-size: 0.72rem;">[C]</kbd>
                        </label>

                        <input type="radio" class="btn-check" name="metode_bayar" id="pay-qris" value="QRIS">
                        <label class="btn btn-outline-primary py-2 fw-semibold d-flex align-items-center justify-content-center gap-1" for="pay-qris">
                            <i class="bi bi-qr-code"></i> QRIS <kbd class="bg-light text-dark font-monospace border ms-1" style="font-size: 0.72rem;">[Q]</kbd>
                        </label>

                        <input type="radio" class="btn-check" name="metode_bayar" id="pay-tf" value="Transfer">
                        <label class="btn btn-outline-primary py-2 fw-semibold d-flex align-items-center justify-content-center gap-1" for="pay-tf">
                            <i class="bi bi-bank"></i> Transfer <kbd class="bg-light text-dark font-monospace border ms-1" style="font-size: 0.72rem;">[T]</kbd>
                        </label>
                    </div>
                </div>

                <!-- Input Uang Diterima (Rp) -->
                <div class="mb-2">
                    <label class="small text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.72rem;">Uang Diterima dari Pelanggan (Rp)</label>
                    <input type="text" inputmode="numeric" id="input-bayar" class="form-control form-control-lg text-end fw-bold fs-3 text-success font-monospace input-currency py-1" placeholder="0">
                </div>

                <!-- Shortcut Tombol Uang Cepat -->
                <div class="d-flex gap-1 mb-2">
                    <button type="button" class="btn btn-light btn-sm flex-fill border btn-quick-cash py-1 fw-semibold" data-cash="pas">Uang Pas <kbd class="bg-white text-dark font-monospace border ms-1" style="font-size: 0.68rem;">[P]</kbd></button>
                    <button type="button" class="btn btn-light btn-sm flex-fill border btn-quick-cash py-1 fw-semibold" data-cash="50000">50k</button>
                    <button type="button" class="btn btn-light btn-sm flex-fill border btn-quick-cash py-1 fw-semibold" data-cash="100000">100k</button>
                    <button type="button" class="btn btn-light btn-sm flex-fill border btn-quick-cash py-1 fw-semibold" data-cash="200000">200k</button>
                    <button type="button" class="btn btn-light btn-sm flex-fill border btn-quick-cash py-1 fw-semibold" data-cash="500000">500k</button>
                </div>

                <!-- Tampilan Kembalian -->
                <div class="p-2 px-3 bg-light rounded-3 border d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-muted small">KEMBALIAN:</span>
                    <span class="fs-3 fw-bold text-dark font-monospace" id="display-kembalian">Rp 0</span>
                </div>

                <!-- Alert Non-Tunai -->
                <div id="alert-non-tunai" class="alert alert-info border-info d-flex align-items-center gap-2 mb-0 py-1 d-none">
                    <i class="bi bi-qr-code fs-5 text-info"></i>
                    <div class="small" style="font-size: 0.75rem;">
                        Mode <strong id="label-mode-non-tunai">QRIS</strong>: Pastikan dana pelanggan sudah masuk di mutasi/aplikasi toko.
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-2 px-3 bg-light d-flex gap-2">
                <button type="button" class="btn btn-light rounded-pill px-3 py-2 border" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i> Batal <kbd class="bg-secondary text-white font-monospace ms-1" style="font-size: 0.7rem;">[Esc]</kbd>
                </button>
                <button type="button" id="btn-proses-transaksi" class="btn btn-success flex-fill rounded-pill py-2 fw-bold shadow">
                    <i class="bi bi-check2-circle fs-5 me-1"></i> SELESAIKAN TRANSAKSI <kbd class="bg-white text-success font-monospace px-2 py-0 ms-1">[Enter]</kbd>
                </button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL TUKAR POIN MEMBER MENJADI VOUCHER BELANJA (F7 / POPUP POS)           -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTukarPoinPOS" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white p-2 rounded-3 shadow-sm text-warning">
                        <i class="bi bi-ticket-perforated-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tukar Poin Member Jadi Voucher</h5>
                        <small class="text-dark text-opacity-75">Konversi poin belanja member menjadi voucher diskon langsung</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Info Member & Saldo Poin -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold text-uppercase">Nama Member:</span>
                        <strong class="text-dark" id="pos-tukar-nama-member">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="text-muted small fw-bold text-uppercase">Saldo Poin Belanja:</span>
                        <span class="badge bg-warning text-dark fs-6" id="pos-tukar-saldo-poin">0 Poin</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted small">Maks. Potongan Kuota:</span>
                        <strong class="text-success small" id="pos-tukar-kuota-rp">Rp 0</strong>
                    </div>
                </div>

                <!-- Input Poin Yang Ingin Ditukar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-dark mb-0">Jumlah Poin Yang Ditukar:</label>
                        <span class="text-muted small">Kurs: <strong>1 Poin = Rp 50</strong></span>
                    </div>
                    <div class="input-group input-group-lg">
                        <input type="number" id="pos-input-poin-tukar" class="form-control text-center fw-bold fs-3 text-warning font-monospace" min="1" step="1" value="0" placeholder="0">
                        <span class="input-group-text bg-light fw-bold text-secondary">Poin</span>
                    </div>
                </div>

                <!-- Preset Cepat Tukar Poin -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rounded-pill btn-quick-poin" data-poin="50">50 Poin (Rp 2.500)</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rounded-pill btn-quick-poin" data-poin="100">100 Poin (Rp 5.000)</button>
                    <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill" id="pos-btn-tukar-semua-poin">Tukar Semua</button>
                </div>

                <!-- Preview Nilai Potongan Voucher Belanja -->
                <div class="p-3 rounded-4 text-center mb-0" style="background-color: #f0fdf4; border: 2px dashed #86efac;">
                    <span class="small text-uppercase text-muted fw-bold">NILAI POTONGAN VOUCHER BELANJA:</span>
                    <h2 class="display-6 fw-bold text-success mb-0" id="pos-display-nilai-voucher">Rp 0</h2>
                    <small class="text-muted d-block mt-1">Voucher akan langsung aktif dan otomatis diterapkan pada transaksi ini.</small>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="pos-btn-konfirmasi-tukar-poin" class="btn btn-warning flex-fill rounded-pill py-2 fw-bold text-dark shadow">
                    <i class="bi bi-check2-circle fs-5 me-1"></i> Konfirmasi & Gunakan Voucher
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL STRUK & WHATSAPP TRANSAKSI SUKSES    -->
<!-- ========================================== -->
<div class="modal fade" id="modalStruk" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0"><i class="bi bi-check-circle-fill me-1"></i> Transaksi Berhasil!</h5>
                    <small class="text-white text-opacity-75" id="modal-faktur-label"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body py-2">
                <!-- Info Member & WhatsApp (Muncul Jika Member Belanja) -->
                <div id="modal-wa-alert" class="alert alert-success border-success-subtle p-2 mb-2 d-none">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp fs-3 text-success"></i>
                        <div>
                            <div class="fw-bold small text-success-emphasis" id="modal-wa-title">Member Terdaftar WhatsApp</div>
                            <small class="text-muted" id="modal-wa-phone"></small>
                        </div>
                    </div>
                </div>

                <!-- Preview Struk Kasir -->
                <iframe id="iframe-struk" src="" style="width: 100%; height: 320px; border: 1px dashed #cbd5e1; border-radius: 8px;"></iframe>
            </div>

            <div class="modal-footer border-0 pt-0 d-flex flex-column gap-2">
                <div class="d-flex w-100 gap-2">
                    <!-- Tombol WhatsApp Struk (Hanya Muncul Jika Member) -->
                    <a href="#" target="_blank" class="btn btn-success flex-fill rounded-pill d-none" id="btn-kirim-wa">
                        <i class="bi bi-whatsapp me-1"></i> Kirim Struk WA
                    </a>
                    <!-- Tombol Cetak Struk Fisik -->
                    <button type="button" class="btn btn-primary flex-fill rounded-pill" id="btn-print-struk">
                        <i class="bi bi-printer-fill me-1"></i> Cetak Struk <kbd class="bg-white text-primary font-monospace px-2 py-0 ms-1">[Enter / F10]</kbd>
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">
                    Transaksi Baru <kbd class="bg-secondary text-white font-monospace px-2 py-0 ms-1">[Esc]</kbd>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL WAJIB INPUT KAS AWAL HARI INI (LOGIKA VB6 SimpanUangKasHarian & FrmJual) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalWajibKasAwalPos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white p-2 rounded-3">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Modal Kas Awal Laci Kasir</h5>
                        <small class="text-white-50">Wajib Diisi Sebelum Membuka Transaksi Penjualan</small>
                    </div>
                </div>
            </div>
            <form id="form-kas-awal-pos">
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 p-2 mb-3 small">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div>
                            <strong>Uang Kas Kasir Masih Rp 0!</strong> Sesuai SOP Toko, kasir wajib menginput saldo kas awal laci hari ini sebagai modal uang kembalian konsumen.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Nominal Kas Awal Laci (Rp):</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light fw-bold text-secondary">Rp</span>
                            <input type="number" id="input-nominal-kas-awal-pos" class="form-control text-end fw-bold fs-3 text-primary" placeholder="200000" min="1000" step="1000" autofocus required>
                        </div>
                        <small class="text-muted d-block mt-1">Contoh: 200000 (Pecahan uang kembalian pagi hari).</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" id="btn-simpan-kas-awal-pos" class="btn btn-primary rounded-pill w-100 py-3 fw-bold fs-6 shadow">
                        <i class="bi bi-unlock-fill me-1"></i> SIMPAN KAS AWAL & BUKA TRANSAKSI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let hasKasAwal = <?= !empty($has_kas_awal) ? 'true' : 'false' ?>;

    function bukaModalKasAwal() {
        let modalEl = bootstrap.Modal.getInstance(document.getElementById('modalWajibKasAwalPos'));
        if (!modalEl) {
            modalEl = new bootstrap.Modal(document.getElementById('modalWajibKasAwalPos'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        modalEl.show();
        setTimeout(() => {
            $('#input-nominal-kas-awal-pos').focus().select();
        }, 400);
    }

    // Jika kasir buka POS dan Kas Awal belum diinput hari ini, langsung munculkan popup wajib
    $(document).ready(function() {
        if (!hasKasAwal) {
            bukaModalKasAwal();
        }
    });

    $(document).on('click', '#btn-trigger-kas-awal', function() {
        bukaModalKasAwal();
    });

    // Submit Kas Awal via AJAX
    $('#form-kas-awal-pos').on('submit', function(e) {
        e.preventDefault();
        const nominal = $('#input-nominal-kas-awal-pos').val().trim();
        if (!nominal || parseFloat(nominal) <= 0) {
            Swal.fire('Perhatian', 'Nominal kas awal harus lebih besar dari Rp 0!', 'warning');
            return;
        }

        $('#btn-simpan-kas-awal-pos').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.post('<?= base_url('auth/simpan-kas-awal') ?>', { nominal_kas_awal: nominal }, function(res) {
            $('#btn-simpan-kas-awal-pos').prop('disabled', false).html('<i class="bi bi-unlock-fill me-1"></i> SIMPAN KAS AWAL & BUKA TRANSAKSI');
            if (res.status === 'success') {
                hasKasAwal = true;
                const modalEl = bootstrap.Modal.getInstance(document.getElementById('modalWajibKasAwalPos'));
                if (modalEl) modalEl.hide();

                // Ubah status badge di top bar
                $('#btn-trigger-kas-awal').replaceWith(`
                    <span class="badge bg-primary text-white border border-primary px-2 py-1 shadow-sm" id="badge-status-pos" title="Modal Kas Awal: ${res.formatted}">
                        <i class="bi bi-unlock-fill me-1"></i>(OPEN TRANSACTION) <span class="d-none d-lg-inline">&bull; Kas: ${res.formatted}</span>
                    </span>
                `);

                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Kasir Terbuka!',
                    text: res.message,
                    timer: 2200,
                    showConfirmButton: false
                });

                setTimeout(() => {
                    $('#barcode-input').focus();
                }, 500);
            } else {
                Swal.fire('Gagal!', res.message || 'Gagal menyimpan kas awal.', 'error');
            }
        }).fail(function(xhr) {
            $('#btn-simpan-kas-awal-pos').prop('disabled', false).html('<i class="bi bi-unlock-fill me-1"></i> SIMPAN KAS AWAL & BUKA TRANSAKSI');
            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat menyimpan kas awal.';
            Swal.fire('Error!', err, 'error');
        });
    });

    let cart = [];
    const HARGA_POTONGAN_JERIGEN = 1000;
    let selectedMember = null;

    // Index navigasi keyboard suggestion
    let searchHighlightIndex = -1;
    let memberHighlightIndex = -1;

    // 1. Tipe Pembeli: Umum vs Member
    $('input[name="customer_type"]').on('change', function() {
        if ($(this).val() === 'member') {
            $('#member-section').removeClass('d-none');
            $('#member-search-input').focus();
        } else {
            $('#member-section').addClass('d-none');
            resetMember();
        }
    });

    // 2. Pencarian Live & Suggestion Member
    let memberTimeout = null;
    $('#member-search-input').on('input', function() {
        clearTimeout(memberTimeout);
        const term = $(this).val().trim();
        memberHighlightIndex = -1;

        if (term.length < 1) {
            $('#member-dropdown').addClass('d-none').empty();
            return;
        }

        memberTimeout = setTimeout(() => {
            $.get('<?= base_url('pos/search-member') ?>', { term: term }, function(data) {
                const $drop = $('#member-dropdown');
                $drop.empty();
                memberHighlightIndex = -1;

                if (data && data.length > 0) {
                    data.forEach((m, idx) => {
                        const noWa = m.telepon || '-';
                        const item = $(`
                            <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2 member-suggest-item" data-idx="${idx}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-person me-1 text-primary"></i>${m.nama}</div>
                                        <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i>${noWa} &bull; Kode: ${m.kode_member}</small>
                                    </div>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>${m.point} Poin</span>
                                </div>
                            </a>
                        `);
                        item.on('click', () => {
                            selectMember(m);
                        });
                        $drop.append(item);
                    });
                    $drop.removeClass('d-none');
                } else {
                    $drop.html('<div class="p-2 text-muted text-center small">Member tidak ditemukan</div>').removeClass('d-none');
                }
            });
        }, 150);
    });

    // Navigasi Keyboard Panah Atas/Bawah & Enter untuk Suggestion Member
    $('#member-search-input').on('keydown', function(e) {
        const $items = $('#member-dropdown .member-suggest-item');
        if (!$items.length || $('#member-dropdown').hasClass('d-none')) {
            if (e.key === 'Escape') {
                $('#member-dropdown').addClass('d-none');
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            memberHighlightIndex++;
            if (memberHighlightIndex >= $items.length) memberHighlightIndex = 0;
            updateMemberHighlight($items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            memberHighlightIndex--;
            if (memberHighlightIndex < 0) memberHighlightIndex = $items.length - 1;
            updateMemberHighlight($items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (memberHighlightIndex >= 0 && memberHighlightIndex < $items.length) {
                $items.eq(memberHighlightIndex).click();
            } else if ($items.length > 0) {
                $items.eq(0).click();
            }
        } else if (e.key === 'Escape') {
            $('#member-dropdown').addClass('d-none');
        }
    });

    function updateMemberHighlight($items) {
        $items.removeClass('active bg-primary text-white');
        const $cur = $items.eq(memberHighlightIndex);
        $cur.addClass('active bg-primary text-white');
        $cur[0].scrollIntoView({ block: 'nearest' });
    }

    function selectMember(m) {
        selectedMember = m;
        $('#member-dropdown').addClass('d-none').empty();
        $('#member-search-input').val('');
        
        const poin = parseFloat(m.point) || 0;
        const nilaiRupiah = m.nilai_rupiah_poin || (poin * 50);

        $('#selected-member-nama').text(m.nama + ' (' + m.kode_member + ')');
        $('#selected-member-detail').html(`
            <i class="bi bi-whatsapp text-success me-1"></i>${m.telepon || 'Tidak ada WA'} &bull; 
            <i class="bi bi-star-fill text-warning me-1"></i>${new Intl.NumberFormat('id-ID').format(poin)} Poin (Senilai Voucher Rp ${new Intl.NumberFormat('id-ID').format(nilaiRupiah)})
        `);
        $('#selected-member-badge').removeClass('d-none');
        
        // Update kuota poin & nilai rupiah di voucher container
        $('#notice-kuota-poin').text(new Intl.NumberFormat('id-ID').format(poin) + ' Poin');
        $('#notice-kuota-rp').text('Rp ' + new Intl.NumberFormat('id-ID').format(nilaiRupiah));
        
        // Selalu tampilkan voucher-member-container untuk member agar kasir bisa lihat kuota & tukar poin
        $('#voucher-member-container').removeClass('d-none');

        // Ambil data voucher terbaru member dari server via AJAX
        loadMemberVouchers(m.kode_member);

        updateSummary();

        // Pindahkan fokus langsung ke scan barcode produk
        $('#barcode-input').focus();
    }

    function loadMemberVouchers(kodeMember, autoSelectKode = null) {
        $.get('<?= base_url('pos/get-member-vouchers') ?>/' + kodeMember, function(res) {
            if (res.status === 'success') {
                const poin = parseFloat(res.point) || 0;
                const kuotaRp = parseFloat(res.kuota_voucher_rp) || (poin * 50);
                
                if (selectedMember) {
                    selectedMember.point = poin;
                    selectedMember.nilai_rupiah_poin = kuotaRp;
                }

                $('#notice-kuota-poin').text(new Intl.NumberFormat('id-ID').format(poin) + ' Poin');
                $('#notice-kuota-rp').text('Rp ' + new Intl.NumberFormat('id-ID').format(kuotaRp));

                const vouchers = res.vouchers || [];
                $('#label-voucher-tersedia').text(vouchers.length + ' Voucher Tersedia');

                const $selectVch = $('#select-voucher-pos');
                $selectVch.empty().append('<option value="" data-potongan="0">-- Jangan Gunakan Voucher --</option>');

                vouchers.forEach(v => {
                    const opt = $(`
                        <option value="${v.kode_voucher}" data-potongan="${v.nilai_voucher}">
                            ${v.kode_voucher} - Potongan Rp ${new Intl.NumberFormat('id-ID').format(v.nilai_voucher)}
                        </option>
                    `);
                    $selectVch.append(opt);
                });

                if (autoSelectKode) {
                    $selectVch.val(autoSelectKode);
                } else if (vouchers.length > 0) {
                    // Auto select voucher pertama yang paling baru
                    $selectVch.val(vouchers[0].kode_voucher);
                }

                $selectVch.trigger('change');
            }
        });
    }

    // Modal Tukar Poin POS
    $('#btn-modal-tukar-poin').on('click', function() {
        if (!selectedMember) {
            Swal.fire('Pilih Member', 'Silakan pilih Member terlebih dahulu sebelum menukar poin!', 'info');
            return;
        }

        const poin = parseFloat(selectedMember.point) || 0;
        if (poin <= 0) {
            Swal.fire({
                icon: 'info',
                title: 'Poin Belum Mencukupi',
                text: 'Member ' + selectedMember.nama + ' belum memiliki saldo poin belanja (0 Poin).'
            });
            return;
        }

        const nilaiRupiah = poin * 50;
        $('#pos-tukar-nama-member').text(selectedMember.nama + ' (' + selectedMember.kode_member + ')');
        $('#pos-tukar-saldo-poin').text(new Intl.NumberFormat('id-ID').format(poin) + ' Poin');
        $('#pos-tukar-kuota-rp').text('Rp ' + new Intl.NumberFormat('id-ID').format(nilaiRupiah));

        let subtotal = 0;
        cart.forEach(i => subtotal += (i.qty * i.harga_jual));
        const maxPoinNeeded = Math.ceil(subtotal / 50);
        const defaultTukar = Math.min(poin, maxPoinNeeded > 0 ? maxPoinNeeded : poin);

        $('#pos-input-poin-tukar').val(defaultTukar).attr('max', poin);
        hitungNilaiVoucherPOS();

        $('#modalTukarPoinPOS').modal('show');
    });

    function hitungNilaiVoucherPOS() {
        const poin = parseFloat($('#pos-input-poin-tukar').val()) || 0;
        const nilai = poin * 50;
        $('#pos-display-nilai-voucher').text('Rp ' + new Intl.NumberFormat('id-ID').format(nilai));
    }

    $('#pos-input-poin-tukar').on('input', hitungNilaiVoucherPOS);

    $('.btn-quick-poin').on('click', function() {
        const val = parseFloat($(this).data('poin')) || 0;
        const maxPoin = parseFloat(selectedMember ? selectedMember.point : 0) || 0;
        $('#pos-input-poin-tukar').val(Math.min(val, maxPoin));
        hitungNilaiVoucherPOS();
    });

    $('#pos-btn-tukar-semua-poin').on('click', function() {
        const maxPoin = parseFloat(selectedMember ? selectedMember.point : 0) || 0;
        $('#pos-input-poin-tukar').val(maxPoin);
        hitungNilaiVoucherPOS();
    });

    $('#pos-btn-konfirmasi-tukar-poin').on('click', function() {
        if (!selectedMember) return;
        const poin = parseInt($('#pos-input-poin-tukar').val(), 10) || 0;
        const maxPoin = parseFloat(selectedMember.point) || 0;

        if (poin <= 0) {
            Swal.fire('Perhatian', 'Jumlah poin yang ditukar harus lebih dari 0!', 'warning');
            return;
        }

        if (poin > maxPoin) {
            Swal.fire('Poin Kurang', `Saldo poin member hanya ${maxPoin} poin!`, 'error');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menukar Poin...');

        $.post('<?= base_url('pos/tukar-poin-member') ?>', {
            kode_member: selectedMember.kode_member,
            point: poin
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5 me-1"></i> Konfirmasi & Gunakan Voucher');
            if (res.status === 'success') {
                $('#modalTukarPoinPOS').modal('hide');
                
                // Update poin member
                selectedMember.point = res.sisa_poin;
                selectedMember.nilai_rupiah_poin = res.sisa_poin * 50;
                
                $('#selected-member-detail').html(`
                    <i class="bi bi-whatsapp text-success me-1"></i>${selectedMember.telepon || 'Tidak ada WA'} &bull; 
                    <i class="bi bi-star-fill text-warning me-1"></i>${new Intl.NumberFormat('id-ID').format(res.sisa_poin)} Poin (Senilai Voucher Rp ${new Intl.NumberFormat('id-ID').format(res.sisa_poin * 50)})
                `);

                // Reload member vouchers dan auto-select voucher baru
                loadMemberVouchers(selectedMember.kode_member, res.voucher.kode_voucher);

                Swal.fire({
                    icon: 'success',
                    title: 'Voucher Berhasil Dibuat!',
                    html: `Berhasil menukar <b>${res.voucher.point_ditukar} Poin</b> menjadi Voucher <b>${res.voucher.kode_voucher}</b> senilai <b>Rp ${new Intl.NumberFormat('id-ID').format(res.voucher.nilai_voucher)}</b>. Voucher langsung diterapkan ke kasir!`,
                    timer: 2500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal Tukar Poin', res.message || 'Terjadi kesalahan sistem.', 'error');
            }
        }).fail(function(xhr) {
            $btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5 me-1"></i> Konfirmasi & Gunakan Voucher');
            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi server.';
            Swal.fire('Error', err, 'error');
        });
    });

    function resetMember() {
        selectedMember = null;
        $('#selected-member-badge').addClass('d-none');
        $('#voucher-member-container').addClass('d-none');
        $('#select-voucher-pos').empty().append('<option value="" data-potongan="0">-- Jangan Gunakan Voucher --</option>').trigger('change');
        $('#member-search-input').val('');
        $('#member-dropdown').addClass('d-none').empty();
        $('#row-potongan-voucher').addClass('d-none');
        $('#popup-row-voucher').addClass('d-none');
        updateSummary();
    }

    $('#select-voucher-pos').on('change', function() {
        const opt = $(this).find('option:selected');
        const potVch = parseFloat(opt.data('potongan')) || 0;
        const kodeVch = $(this).val();

        if (potVch > 0 && kodeVch) {
            $('#row-potongan-voucher').removeClass('d-none');
            $('#display-potongan-voucher').text('-Rp ' + new Intl.NumberFormat('id-ID').format(potVch));

            $('#popup-row-voucher').removeClass('d-none');
            $('#popup-voucher-code').text(kodeVch);
            $('#popup-voucher-discount').text('-Rp ' + new Intl.NumberFormat('id-ID').format(potVch));
        } else {
            $('#row-potongan-voucher').addClass('d-none');
            $('#popup-row-voucher').addClass('d-none');
        }
        updateSummary();
    });

    $('#btn-reset-member, #btn-remove-selected-member').on('click', function() {
        resetMember();
        $('#member-search-input').focus();
    });

    // 3. Search / Scan Barcode Produk & Navigasi Panah Keyboard
    let searchTimeout = null;
    $('#barcode-input').on('input', function() {
        clearTimeout(searchTimeout);
        const term = $(this).val().trim();
        searchHighlightIndex = -1;

        if (term.length < 2) {
            $('#search-dropdown').addClass('d-none').empty();
            return;
        }

        searchTimeout = setTimeout(() => {
            $.get('<?= base_url('pos/search') ?>', { term: term }, function(data) {
                const $drop = $('#search-dropdown');
                $drop.empty();
                searchHighlightIndex = -1;

                if (data && data.length > 0) {
                    data.forEach((item, idx) => {
                        const stokInfo = (item.stok !== null && item.stok !== undefined) ? ` | Stok: ${item.stok} ${item.satuan}` : '';
                        const row = $(`
                            <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2 barang-suggest-item" data-idx="${idx}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold text-dark">${item.nama_barang}</div>
                                        <small class="text-muted">Kode: ${item.id_barang}${stokInfo}</small>
                                    </div>
                                    <span class="badge bg-primary fs-6">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_jual)}</span>
                                </div>
                            </a>
                        `);
                        row.on('click', () => {
                            addToCart(item);
                            $drop.addClass('d-none').empty();
                            $('#barcode-input').val('').focus();
                        });
                        $drop.append(row);
                    });
                    $drop.removeClass('d-none');
                } else {
                    $drop.html('<div class="p-3 text-muted text-center small">Barang tidak ditemukan</div>').removeClass('d-none');
                }
            });
        }, 150);
    });

    // Navigasi Keyboard Panah Atas/Bawah & Enter untuk Suggestion Barang
    $('#barcode-input').on('keydown', function(e) {
        const $items = $('#search-dropdown .barang-suggest-item');
        const isDropdownOpen = !$items.length || $('#search-dropdown').hasClass('d-none');

        if (!isDropdownOpen) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                searchHighlightIndex++;
                if (searchHighlightIndex >= $items.length) searchHighlightIndex = 0;
                updateBarangHighlight($items);
                return;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                searchHighlightIndex--;
                if (searchHighlightIndex < 0) searchHighlightIndex = $items.length - 1;
                updateBarangHighlight($items);
                return;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (searchHighlightIndex >= 0 && searchHighlightIndex < $items.length) {
                    $items.eq(searchHighlightIndex).click();
                    return;
                }
            } else if (e.key === 'Escape') {
                $('#search-dropdown').addClass('d-none');
                return;
            }
        }

        // Enter untuk scan barcode langsung jika tidak ada yang di-highlight
        if (e.key === 'Enter') {
            e.preventDefault();
            const term = $(this).val().trim();
            if (!term) return;

            $.get('<?= base_url('pos/search') ?>', { term: term }, function(data) {
                if (data && data.length > 0) {
                    addToCart(data[0]);
                    $('#barcode-input').val('').focus();
                    $('#search-dropdown').addClass('d-none');
                } else {
                    Swal.fire('Tidak Ditemukan', 'Barcode atau kode barang tidak cocok!', 'warning');
                }
            });
        }
    });

    function updateBarangHighlight($items) {
        $items.removeClass('active bg-primary text-white');
        const $cur = $items.eq(searchHighlightIndex);
        $cur.addClass('active bg-primary text-white');
        $cur[0].scrollIntoView({ block: 'nearest' });
    }

    // Shortcut F2 untuk fokus barcode
    $('#btn-focus-barcode').on('click', function() {
        $('#barcode-input').focus();
    });

    // Menambah barang ke keranjang
    function addToCart(item) {
        const existing = cart.find(x => x.id_barang === item.id_barang);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id_barang: item.id_barang,
                nama_barang: item.nama_barang,
                harga_normal: parseFloat(item.harga_jual),
                harga_jual: parseFloat(item.harga_jual),
                qty_paket_a: parseFloat(item.qty_paket_a) || 0,
                hrg_paket_a: parseFloat(item.hrg_paket_a) || 0,
                qty_paket_b: parseFloat(item.qty_paket_b) || 0,
                hrg_paket_b: parseFloat(item.hrg_paket_b) || 0,
                satuan: item.satuan,
                stok: parseFloat(item.stok),
                qty: 1
            });
        }
        applyTieredPricing();
        renderCart();
    }

    // Hitung diskon harga grosir bertingkat (Paket A & Paket B)
    function applyTieredPricing() {
        cart.forEach(item => {
            let hargaPakai = item.harga_normal;
            if (item.qty_paket_b > 0 && item.hrg_paket_b > 0 && item.qty >= item.qty_paket_b) {
                hargaPakai = item.hrg_paket_b;
            } else if (item.qty_paket_a > 0 && item.hrg_paket_a > 0 && item.qty >= item.qty_paket_a) {
                hargaPakai = item.hrg_paket_a;
            }
            item.harga_jual = hargaPakai;
        });
    }

    // Render isi keranjang
    function renderCart() {
        const $tbody = $('#cart-tbody');
        $tbody.empty();

        if (cart.length === 0) {
            $tbody.html(`
                <tr id="empty-cart-row">
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-cart-x fs-1 text-secondary opacity-50"></i>
                        <p class="mt-2 mb-0">Keranjang masih kosong. Scan barcode atau cari produk di atas.</p>
                    </td>
                </tr>
            `);
            updateSummary();
            return;
        }

        cart.forEach((item, idx) => {
            const subtotal = item.qty * item.harga_jual;
            const stokBadge = (item.stok !== null && item.stok !== undefined && !isNaN(item.stok)) ? ` &bull; Stok: ${item.stok}` : '';
            const tr = $(`
                <tr>
                    <td>
                        <div class="fw-semibold text-dark">${item.nama_barang}</div>
                        <small class="text-muted">${item.id_barang}${stokBadge}</small>
                    </td>
                    <td class="text-end fw-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_jual)}</td>
                    <td class="text-center">
                        <div class="input-group input-group-sm justify-content-center" style="width: 105px; margin: auto;">
                            <button class="btn btn-outline-secondary btn-minus" type="button"><i class="bi bi-dash"></i></button>
                            <input type="number" class="form-control text-center input-qty px-1" value="${item.qty}" min="0.1" step="any">
                            <button class="btn btn-outline-secondary btn-plus" type="button"><i class="bi bi-plus"></i></button>
                        </div>
                    </td>
                    <td class="text-end fw-bold text-primary">Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm border-0 btn-delete" title="Hapus"><i class="bi bi-x-circle-fill"></i></button>
                    </td>
                </tr>
            `);

            tr.find('.btn-minus').on('click', () => {
                if (item.qty > 1) {
                    item.qty -= 1;
                } else {
                    cart.splice(idx, 1);
                }
                applyTieredPricing();
                renderCart();
            });

            tr.find('.btn-plus').on('click', () => {
                item.qty += 1;
                applyTieredPricing();
                renderCart();
            });

            tr.find('.input-qty').on('change', function() {
                const val = parseFloat($(this).val()) || 1;
                item.qty = val > 0 ? val : 1;
                applyTieredPricing();
                renderCart();
            });

            tr.find('.btn-delete').on('click', () => {
                cart.splice(idx, 1);
                renderCart();
            });

            $tbody.append(tr);
        });

        updateSummary();
    }

    // Hitung total dan kembalian
    function updateSummary() {
        let subtotal = 0;
        let totalQty = 0;
        cart.forEach(i => {
            subtotal += (i.qty * i.harga_jual);
            totalQty += i.qty;
        });

        const diskon = unformatRupiah($('#input-diskon').val()) || 0;
        let potonganJerigen = 0;

        if ($('#chk-jerigen').is(':checked')) {
            const qtyJerigen = parseInt($('#input-qty-jerigen').val()) || 1;
            potonganJerigen = qtyJerigen * HARGA_POTONGAN_JERIGEN;
            $('#label-potongan-jerigen').text('Rp ' + new Intl.NumberFormat('id-ID').format(potonganJerigen));
        }

        const potonganVoucher = parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0;

        let grandTotal = subtotal - diskon - potonganJerigen - potonganVoucher;
        if (grandTotal < 0) grandTotal = 0;

        $('#display-subtotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(subtotal));
        $('#display-grand-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal));
        $('#popup-display-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal));
        
        $('#display-total-item').text(cart.length + ' Item');
        $('#display-total-qty').text(totalQty + ' Pcs/Liter');

        // Kembalian
        const bayar = unformatRupiah($('#input-bayar').val()) || 0;
        const kembalian = bayar - grandTotal;
        $('#display-kembalian').text('Rp ' + new Intl.NumberFormat('id-ID').format(kembalian > 0 ? kembalian : 0));
    }

    $('#input-diskon, #input-bayar, #input-qty-jerigen').on('input', updateSummary);

    $('#chk-jerigen').on('change', function() {
        if ($(this).is(':checked')) {
            $('#jerigen-container').removeClass('d-none');
        } else {
            $('#jerigen-container').addClass('d-none');
        }
        updateSummary();
    });

    // 4. BUKA POPUP MODAL PEMBAYARAN (F9)
    function bukaModalBayar() {
        if (!hasKasAwal) {
            Swal.fire({
                icon: 'warning',
                title: 'Transaksi Terkunci!',
                text: 'Uang Kas Kasir masih Rp 0! Kasir wajib menginput saldo kas awal laci hari ini terlebih dahulu untuk uang kembalian konsumen.',
                confirmButtonText: '<i class="bi bi-wallet2 me-1"></i> Input Kas Awal Sekarang',
                confirmButtonColor: '#0284c7'
            }).then(() => {
                bukaModalKasAwal();
            });
            return;
        }

        if (cart.length === 0) {
            Swal.fire('Keranjang Kosong', 'Silakan pilih produk yang akan dibeli sebelum melakukan pembayaran.', 'warning');
            return;
        }

        updateSummary();

        // Default uang pas jika belum terisi
        let subtotal = 0;
        cart.forEach(i => subtotal += (i.qty * i.harga_jual));
        const diskon = unformatRupiah($('#input-diskon').val()) || 0;
        const potJerigen = $('#chk-jerigen').is(':checked') ? (parseInt($('#input-qty-jerigen').val()) || 1) * HARGA_POTONGAN_JERIGEN : 0;
        const potVoucher = parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0;
        const grandTotal = Math.max(0, subtotal - diskon - potJerigen - potVoucher);

        if (!unformatRupiah($('#input-bayar').val())) {
            $('#input-bayar').val(formatRupiah(grandTotal));
        }

        $('#modal-bayar-faktur-label').text('Faktur: ' + $('#label-faktur').text());
        $('#modalBayar').modal('show');

        // Fokus ke input bayar
        setTimeout(() => {
            $('#input-bayar').focus().select();
        }, 400);
    }

    $('#btn-buka-modal-bayar').on('click', bukaModalBayar);

    // Tombol Uang Cepat di Popup
    $('.btn-quick-cash').on('click', function() {
        const val = $(this).data('cash');
        let subtotal = 0;
        cart.forEach(i => subtotal += (i.qty * i.harga_jual));
        const diskon = parseFloat($('#input-diskon').val()) || 0;
        const potJerigen = $('#chk-jerigen').is(':checked') ? (parseInt($('#input-qty-jerigen').val()) || 1) * HARGA_POTONGAN_JERIGEN : 0;
        const potVoucher = parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0;
        const grandTotal = Math.max(0, subtotal - diskon - potJerigen - potVoucher);

        if (val === 'pas') {
            $('#input-bayar').val(formatRupiah(grandTotal));
        } else {
            $('#input-bayar').val(formatRupiah(val));
        }
        updateSummary();
    });

    // Event perubahan metode bayar (Cash, QRIS, Transfer) di Popup
    $('input[name="metode_bayar"]').on('change', function() {
        const val = $(this).val();
        if (val === 'QRIS' || val === 'Transfer') {
            $('#alert-non-tunai').removeClass('d-none');
            $('#label-mode-non-tunai').text(val);
            // Non tunai auto-fill uang pas
            let subtotal = 0;
            cart.forEach(i => subtotal += (i.qty * i.harga_jual));
            const diskon = unformatRupiah($('#input-diskon').val()) || 0;
            const potJerigen = $('#chk-jerigen').is(':checked') ? (parseInt($('#input-qty-jerigen').val()) || 1) * HARGA_POTONGAN_JERIGEN : 0;
            const potVoucher = parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0;
            const grandTotal = Math.max(0, subtotal - diskon - potJerigen - potVoucher);
            $('#input-bayar').val(formatRupiah(grandTotal));
        } else {
            $('#alert-non-tunai').addClass('d-none');
        }
        updateSummary();
    });

    // Enter di input bayar untuk langsung memproses transaksi
    $('#input-bayar').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#btn-proses-transaksi').click();
        }
    });

    // Kosongkan keranjang
    $('#btn-kosongkan').on('click', function() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Kosongkan keranjang?',
            text: 'Semua item di keranjang akan dihapus',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                renderCart();
            }
        });
    });

    // 5. Proses Transaksi ke Server & Kirim WhatsApp jika Member
    $('#btn-proses-transaksi').on('click', function() {
        if (cart.length === 0) {
            Swal.fire('Keranjang Kosong', 'Silakan pilih produk yang akan dibeli.', 'warning');
            return;
        }

        let subtotal = 0;
        cart.forEach(i => subtotal += (i.qty * i.harga_jual));
        const diskon = unformatRupiah($('#input-diskon').val()) || 0;
        const potJerigen = $('#chk-jerigen').is(':checked') ? (parseInt($('#input-qty-jerigen').val()) || 1) * HARGA_POTONGAN_JERIGEN : 0;
        const potVoucher = parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0;
        const grandTotal = Math.max(0, subtotal - diskon - potJerigen - potVoucher);
        const bayar = unformatRupiah($('#input-bayar').val()) || 0;

        if (bayar < grandTotal) {
            Swal.fire('Uang Kurang', 'Nominal uang pembayaran kurang dari total tagihan!', 'warning');
            return;
        }

        const kodeMember = ($('input[name="customer_type"]:checked').val() === 'member' && selectedMember) ? selectedMember.kode_member : null;
        const kodeVoucher = ($('input[name="customer_type"]:checked').val() === 'member') ? ($('#select-voucher-pos').val() || null) : null;
        const potonganVoucher = ($('input[name="customer_type"]:checked').val() === 'member') ? (parseFloat($('#select-voucher-pos option:selected').data('potongan')) || 0) : 0;

        const payload = {
            items: cart,
            kode_member: kodeMember,
            diskon: diskon,
            potongan_tukar_jerigen: potJerigen,
            kode_voucher: kodeVoucher,
            potongan_voucher: potonganVoucher,
            metode_bayar: $('input[name="metode_bayar"]:checked').val(),
            bayar: bayar
        };

        $('#btn-proses-transaksi').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: '<?= base_url('pos/simpan-transaksi') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                $('#btn-proses-transaksi').prop('disabled', false).html('<i class="bi bi-check2-circle fs-5 me-1"></i> SELESAIKAN TRANSAKSI (Enter)');
                
                // Tutup Modal Bayar
                $('#modalBayar').modal('hide');

                // Tampilkan Modal Cetak Struk
                const strukUrl = '<?= base_url('pos/cetak-struk') ?>/' + res.no_faktur;
                $('#iframe-struk').attr('src', strukUrl);
                $('#modal-faktur-label').text('No Faktur: ' + res.no_faktur);

                // Cek jika Member & punya nomor WhatsApp
                if (res.is_member && res.wa_url) {
                    $('#modal-wa-alert').removeClass('d-none');
                    $('#modal-wa-title').text('Member: ' + res.member_nama);
                    $('#modal-wa-phone').html('<i class="bi bi-whatsapp text-success me-1"></i>Kirim struk ke <strong>' + res.member_phone + '</strong>');
                    $('#btn-kirim-wa').attr('href', res.wa_url).removeClass('d-none');
                    
                    // Otomatis buka WhatsApp di tab baru untuk kemudahan kasir
                    window.open(res.wa_url, '_blank');
                } else if (res.is_member && !res.wa_url) {
                    $('#modal-wa-alert').removeClass('d-none');
                    $('#modal-wa-title').text('Member: ' + res.member_nama);
                    $('#modal-wa-phone').text('Nomor WhatsApp belum tercatat di data member');
                    $('#btn-kirim-wa').addClass('d-none');
                } else {
                    $('#modal-wa-alert').addClass('d-none');
                    $('#btn-kirim-wa').addClass('d-none');
                }

                $('#modalStruk').modal('show');

                // Reset Keranjang
                cart = [];
                $('#input-diskon').val(0);
                $('#chk-jerigen').prop('checked', false).trigger('change');
                $('#input-bayar').val('');
                renderCart();
            },
            error: function(err) {
                $('#btn-proses-transaksi').prop('disabled', false).html('<i class="bi bi-check2-circle fs-5 me-1"></i> SELESAIKAN TRANSAKSI (Enter)');
                const resJson = err.responseJSON;
                if (resJson && resJson.require_kas_awal) {
                    $('#modalBayar').modal('hide');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kas Awal Diperlukan',
                        text: resJson.message,
                        confirmButtonText: '<i class="bi bi-wallet2 me-1"></i> Input Kas Awal Sekarang',
                        confirmButtonColor: '#0284c7'
                    }).then(() => {
                        bukaModalKasAwal();
                    });
                } else {
                    Swal.fire('Gagal', resJson?.message || 'Terjadi kesalahan sistem!', 'error');
                }
            }
        });
    });

    // Cetak Struk Fisik
    $('#btn-print-struk').on('click', function() {
        const iframe = document.getElementById('iframe-struk');
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    });

    // ========================================================
    // SHORTCUT KEYBOARD LENGKAP UNTUK KASIR TANPA MOUSE
    // ========================================================
    $(document).on('keydown', function(e) {
        // 1. F1: Ganti Tipe Pembeli (Umum <-> Member)
        if (e.key === 'F1') {
            e.preventDefault();
            const currentType = $('input[name="customer_type"]:checked').val();
            if (currentType === 'umum') {
                $('#type-member').prop('checked', true).trigger('change');
                setTimeout(() => $('#member-search-input').focus().select(), 150);
            } else {
                $('#type-umum').prop('checked', true).trigger('change');
                setTimeout(() => $('#barcode-input').focus().select(), 150);
            }
        }
        // 2. F2: Fokus Scan Barcode / Input Nama Produk
        else if (e.key === 'F2') {
            e.preventDefault();
            $('#barcode-input').focus().select();
        }
        // 3. F3: Cari Member (Aktifkan Mode Member & Fokus Input Member)
        else if (e.key === 'F3') {
            e.preventDefault();
            $('#type-member').prop('checked', true).trigger('change');
            setTimeout(() => $('#member-search-input').focus().select(), 150);
        }
        // 4. F4: Toggle Tukar Jerigen Kosong 5L & Fokus Input Qty
        else if (e.key === 'F4') {
            e.preventDefault();
            const isChecked = $('#chk-jerigen').is(':checked');
            $('#chk-jerigen').prop('checked', !isChecked).trigger('change');
            if (!isChecked) {
                setTimeout(() => $('#input-qty-jerigen').focus().select(), 150);
            } else {
                $('#barcode-input').focus();
            }
        }
        // 5. F6: Input Potongan Diskon Manual
        else if (e.key === 'F6') {
            e.preventDefault();
            $('#input-diskon').focus().select();
        }
        // 6. F7: Pilih Voucher Belanja Member
        else if (e.key === 'F7') {
            e.preventDefault();
            if (!$('#voucher-member-container').hasClass('d-none')) {
                $('#select-voucher-pos').focus();
            } else {
                Swal.fire({
                    title: 'Info Voucher Member',
                    text: 'Pilih tipe pembeli Member [F1 / F3] terlebih dahulu untuk memakai voucher belanja.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
        // 7. F8: Kosongkan Keranjang Belanja
        else if (e.key === 'F8') {
            e.preventDefault();
            $('#btn-kosongkan').click();
        }
        // 8. F9: Buka Modal Pembayaran (Checkout) / Selesaikan Transaksi
        else if (e.key === 'F9') {
            e.preventDefault();
            if ($('#modalBayar').hasClass('show')) {
                $('#btn-proses-transaksi').click();
            } else {
                bukaModalBayar();
            }
        }
        // 9. F10: Cetak Struk Terakhir
        else if (e.key === 'F10') {
            e.preventDefault();
            if ($('#modalStruk').hasClass('show')) {
                $('#btn-print-struk').click();
            } else {
                const lastFaktur = $('#modal-faktur-label').text().replace('No Faktur: ', '').trim();
                if (lastFaktur) {
                    window.open('<?= base_url('pos/cetak-struk') ?>/' + lastFaktur, '_blank', 'width=420,height=600');
                } else {
                    window.location.href = '<?= base_url('penjualan') ?>';
                }
            }
        }
        // 10. Esc: Tutup Modal / Dropdown & Fokus ke Scan Barcode
        else if (e.key === 'Escape') {
            if ($('#modalBayar').hasClass('show')) {
                $('#modalBayar').modal('hide');
                setTimeout(() => $('#barcode-input').focus(), 300);
            } else if ($('#modalStruk').hasClass('show')) {
                $('#modalStruk').modal('hide');
                setTimeout(() => $('#barcode-input').focus(), 300);
            } else {
                $('#search-dropdown').addClass('d-none');
                $('#member-dropdown').addClass('d-none');
                $('#barcode-input').focus();
            }
        }
        // 11. Enter: Selesaikan Transaksi jika di Modal Bayar, atau Cetak Struk jika di Modal Struk
        else if (e.key === 'Enter') {
            if ($('#modalBayar').hasClass('show')) {
                // Jika sedang mengetik di input-bayar dan menekan Enter, langsung proses transaksi
                e.preventDefault();
                $('#btn-proses-transaksi').click();
            } else if ($('#modalStruk').hasClass('show')) {
                e.preventDefault();
                $('#btn-print-struk').click();
            }
        }
    });

    // Otomatis fokus & select nominal bayar saat modal bayar dibuka
    $('#modalBayar').on('shown.bs.modal', function() {
        $('#input-bayar').focus().select();
    });

    // Shortcut Cepat di Dalam Modal Pembayaran
    // C = Cash, Q = QRIS, T = Transfer, P = Uang Pas, Enter = Selesaikan Transaksi
    $(document).on('keydown', function(e) {
        if ($('#modalBayar').hasClass('show')) {
            const key = e.key.toLowerCase();

            // P = Uang Pas
            if (key === 'p') {
                e.preventDefault();
                $('.btn-quick-cash[data-cash="pas"]').click();
                return;
            }

            // C = Cash
            if (key === 'c') {
                e.preventDefault();
                $('#pay-cash').prop('checked', true).trigger('change');
                return;
            }

            // Q = QRIS
            if (key === 'q') {
                e.preventDefault();
                $('#pay-qris').prop('checked', true).trigger('change');
                return;
            }

            // T = Transfer
            if (key === 't') {
                e.preventDefault();
                $('#pay-tf').prop('checked', true).trigger('change');
                return;
            }

            // Angka 1, 2, 3 jika tidak sedang fokus di input uang bayar
            if (!$('#input-bayar').is(':focus')) {
                if (key === '1') {
                    e.preventDefault();
                    $('#pay-cash').prop('checked', true).trigger('change');
                } else if (key === '2') {
                    e.preventDefault();
                    $('#pay-qris').prop('checked', true).trigger('change');
                } else if (key === '3') {
                    e.preventDefault();
                    $('#pay-tf').prop('checked', true).trigger('change');
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>
