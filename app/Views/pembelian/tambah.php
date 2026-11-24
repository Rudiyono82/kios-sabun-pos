<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    /* Styling seragam untuk komponen input pembelian & Select2 */
    .restock-ctrl {
        height: 36px !important;
        min-height: 36px !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 36px !important;
        min-height: 36px !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        font-size: 0.82rem !important;
    }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <div>
        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-cart-plus-fill text-primary"></i>Input Pembelian Supplier Baru
        </h5>
        <span class="text-muted" style="font-size: 0.74rem;">Catat transaksi pembelian bahan baku kimia / kemasan / barang jadi dengan pembaruan stok otomatis.</span>
    </div>
    <div class="d-flex align-items-stretch gap-2" style="min-height: 56px; height: 56px;">
        <!-- BOX TOTAL BIAYA PEMBELIAN (Pojok Kanan Atas - Jelas & Menonjol Seperti POS) -->
        <div class="d-flex align-items-center justify-content-between px-3 shadow-sm" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1.5px solid #86efac !important; border-radius: 8px; min-width: 320px; height: 100%;">
            <div class="d-flex flex-column justify-content-center me-3 text-start">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.70rem; line-height: 1.1; letter-spacing: 0.5px;">TOTAL PEMBELIAN:</span>
                <span class="badge bg-white text-success border border-success-subtle font-monospace mt-1 py-1 px-2" id="display-total-item" style="font-size: 0.72rem; line-height: 1.1;">0 Item Masuk</span>
            </div>
            <div class="d-flex flex-column justify-content-center text-end">
                <div class="fw-bolder text-success mb-0 font-monospace" id="display-total-biaya" style="font-size: 2.1rem; line-height: 1; letter-spacing: -0.5px;">Rp 0</div>
                <div id="beli-pembulatan-info" class="text-muted d-none font-monospace mt-1" style="font-size: 0.65rem; line-height: 1;"></div>
            </div>
        </div>

        <!-- Tombol Simpan Transaksi & Kembali (Tinggi & Radius Seragam) -->
        <button type="button" class="btn btn-success px-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1 text-nowrap" id="btn-simpan-pembelian" style="border-radius: 8px; height: 100%; font-size: 0.90rem;" title="Simpan transaksi pembelian (Shortcut: F9 atau Ctrl+S)">
            <i class="bi bi-check-circle-fill fs-5"></i> Simpan Transaksi <span class="badge bg-white bg-opacity-25 ms-1" style="font-size: 0.72rem;">[F9]</span>
        </button>
        <a href="<?= base_url('pembelian') ?>" class="btn btn-outline-secondary px-3 fw-semibold d-flex align-items-center justify-content-center text-nowrap" style="border-radius: 8px; height: 100%; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-2">
    <!-- FORM INFORMASI FAKTUR SUPPLIER (Kolom Kiri Padat & Ringkas) -->
    <div class="col-12 col-lg-4">
        <div class="card-modern p-3 mb-2">
            <h6 class="fw-bold mb-2 text-dark border-bottom pb-1" style="font-size: 0.85rem;"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Informasi Faktur Supplier</h6>
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Nomor Nota Beli</label>
                <input type="text" id="beli-no-beli" class="form-control form-control-sm font-monospace fw-bold text-primary bg-light" value="<?= esc($no_beli) ?>" readonly>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Tanggal Pembelian</label>
                <input type="date" id="beli-tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="mb-2">
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
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Status Pembayaran</label>
                <select id="beli-status-bayar" class="form-select form-select-sm fw-semibold">
                    <option value="Lunas (Transfer)" selected>Lunas (Transfer - Tidak Potong Kas Hari Ini)</option>
                    <option value="Lunas">Lunas (Tunai - Potong Kas Toko Hari Ini)</option>
                    <option value="Lunas (Tidak Potong Kas)">Lunas (Non-Kas / Tidak Potong Kas Hari Ini)</option>
                    <option value="Hutang / Tempo">Hutang / Tempo (Kredit)</option>
                </select>
                <div id="status-bayar-keterangan" class="form-text small mt-1" style="font-size: 0.72rem;">
                    <span class="text-info fw-bold"><i class="bi bi-bank me-1"></i>Dibayar via Transfer Rekening (TIDAK memotong kas fisik laci).</span>
                </div>
            </div>
            <div class="mb-0">
                <label class="form-label small fw-bold mb-1">Catatan Pembelian</label>
                <textarea id="beli-catatan" class="form-control form-control-sm" rows="2" placeholder="No. invoice supplier / ekspedisi / catatan..."></textarea>
            </div>
        </div>

        <div class="card border-0 bg-light p-2 text-muted" style="border-radius: 10px; font-size: 0.72rem;">
            <div class="d-flex align-items-center gap-1 mb-1 fw-bold text-dark">
                <i class="bi bi-keyboard-fill text-primary"></i> Shortcut Keyboard Cepat:
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span><kbd class="bg-secondary">F2</kbd> LookUp Barang</span>
                <span><kbd class="bg-secondary">Enter</kbd> Tambah Item</span>
                <span><kbd class="bg-success">F9</kbd> Simpan Transaksi</span>
            </div>
        </div>
    </div>

    <!-- DAFTAR ITEM BELANJA & INPUT BARANG -->
    <div class="col-12 col-lg-8">
        <div class="card-modern p-3 mb-2">
            <h6 class="fw-bold mb-2 text-dark border-bottom pb-1" style="font-size: 0.85rem;"><i class="bi bi-plus-circle me-2 text-primary"></i>Pilih Barang Masuk / Restock</h6>
            
            <!-- Baris Kontrol Input Restock (Sejajar Sempurna) -->
            <div class="row g-2 align-items-start">
                <!-- 1. Cari Produk -->
                <div class="col-12 col-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="height: 22px; line-height: 22px;">
                        <label class="form-label small fw-bold mb-0 text-truncate">
                            Cari Produk / Bahan Baku <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btn-open-lookup-beli" style="font-size: 0.72rem; height: 22px;" title="Buka LookUp List of Stock Modal Popup (Shortcut: F2)">
                            <i class="bi bi-card-checklist"></i> LookUp Barang <span class="badge bg-primary-subtle text-primary py-0 px-1" style="font-size: 0.65rem;">[F2]</span>
                        </button>
                    </div>
                    <select id="pilih-barang" class="form-select restock-ctrl" disabled>
                        <option value="">-- Wajib Pilih Mitra Supplier Dahulu --</option>
                    </select>
                </div>

                <!-- 2. Qty Beli -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1 text-truncate d-block" style="height: 22px; line-height: 22px;">
                        Qty Beli <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="input-qty-beli" class="form-control text-center fw-bold restock-ctrl" value="1" min="0.1" step="any" title="Tekan Enter untuk langsung menambahkan ke keranjang">
                </div>

                <!-- 3. Harga Beli Modal -->
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1 text-truncate" style="height: 22px; line-height: 22px;">
                        Harga Modal / Beli
                    </label>
                    <div class="input-group restock-ctrl">
                        <span class="input-group-text bg-light text-muted fw-bold py-0 px-2 restock-ctrl" style="font-size: 0.75rem;">Rp</span>
                        <input type="text" inputmode="numeric" id="input-harga-beli" class="form-control text-end fw-bold input-currency restock-ctrl" placeholder="0" title="Kasir dapat mengisi/mengubah harga modal baru jika ada perubahan harga dari supplier">
                    </div>
                </div>

                <!-- 4. Tombol Tambah -->
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold mb-1 d-none d-md-block" style="height: 22px; line-height: 22px; visibility: hidden;">Aksi</label>
                    <button type="button" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-1 shadow-sm restock-ctrl" id="btn-tambah-item" style="font-size: 0.78rem;" title="Tambah item ini ke keranjang belanja (Shortcut: Enter)">
                        <i class="bi bi-plus-lg fs-6"></i> Tambah <span class="badge bg-white bg-opacity-25 py-0 px-1" style="font-size: 0.65rem;">[Enter]</span>
                    </button>
                </div>
            </div>

            <!-- Panel Opsi 3: Penyesuaian Harga Jual & Paket Grosir Baru (Jika Ada Perubahan) -->
            <div class="mt-2 border rounded-3 p-2 bg-light-subtle" id="panel-penyesuaian-harga" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-primary" style="font-size: 0.75rem;">
                        <i class="bi bi-tags-fill me-1"></i>Penyesuaian Harga Jual & Paket Grosir Baru (Jika Ada Perubahan)
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-0 px-1" style="font-size: 0.65rem;">
                        <i class="bi bi-shield-check me-1"></i>Opsi 3: Pending Approval Owner
                    </span>
                </div>
                <div class="row g-1">
                    <!-- Harga Jual Eceran Baru -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold mb-0" style="font-size: 0.72rem;">Harga Jual Eceran Baru</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text py-0 px-1 text-muted" style="font-size: 0.7rem;">Rp</span>
                            <input type="text" inputmode="numeric" id="input-harga-jual" class="form-control text-end fw-bold input-currency py-0" placeholder="0">
                        </div>
                    </div>
                    <!-- Paket Grosir A -->
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold mb-0" style="font-size: 0.72rem;">Paket A (Min Qty & Hrg/Item)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="input-qty-a" class="form-control text-center py-0" placeholder="Min" min="0" style="max-width: 48px;">
                            <span class="input-group-text py-0 px-1 text-muted" style="font-size: 0.65rem;">Rp</span>
                            <input type="text" inputmode="numeric" id="input-hrg-a" class="form-control text-end input-currency py-0" placeholder="0">
                        </div>
                    </div>
                    <!-- Paket Grosir B -->
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold mb-0" style="font-size: 0.72rem;">Paket B (Min Qty & Hrg/Item)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="input-qty-b" class="form-control text-center py-0" placeholder="Min" min="0" style="max-width: 48px;">
                            <span class="input-group-text py-0 px-1 text-muted" style="font-size: 0.65rem;">Rp</span>
                            <input type="text" inputmode="numeric" id="input-hrg-b" class="form-control text-end input-currency py-0" placeholder="0">
                        </div>
                    </div>
                    <!-- Paket Grosir C -->
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold mb-0" style="font-size: 0.72rem;">Paket C (Min Qty & Hrg/Item)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="input-qty-c" class="form-control text-center py-0" placeholder="Min" min="0" style="max-width: 48px;">
                            <span class="input-group-text py-0 px-1 text-muted" style="font-size: 0.65rem;">Rp</span>
                            <input type="text" inputmode="numeric" id="input-hrg-c" class="form-control text-end input-currency py-0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 0.68rem;">
                        <i class="bi bi-info-circle me-1"></i>Kasir menginput harga baru dari nota/supplier. Owner akan memeriksa & menyetujui di menu Pending Stok.
                    </small>
                    <div id="badge-harga-berubah-status" class="small fw-semibold text-secondary" style="font-size: 0.68rem;">
                        (Harga Sesuai Master)
                    </div>
                </div>
            </div>

            <!-- Baris Keterangan & Status (Di Bawah Input agar Seluruh Box Sejajar) -->
            <div class="row mt-1 g-1 align-items-center">
                <div class="col-12 col-md-8">
                    <div id="filter-supplier-badge" class="small text-danger fw-semibold" style="font-size: 0.72rem;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Pilih Mitra Supplier di sebelah kiri terlebih dahulu agar daftar produk muncul.
                    </div>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <small class="text-muted" style="font-size: 0.68rem;">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i>Harga beli otomatis dari Master Barang (Lookup)
                    </small>
                </div>
            </div>
        </div>

        <!-- Tabel Item Pembelian Masuk -->
        <div class="card-modern p-3">
            <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.85rem;"><i class="bi bi-basket3 me-2 text-primary"></i>Daftar Barang Masuk (Keranjang Restock)</h6>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="table-keranjang-beli">
                    <thead class="table-light small text-uppercase" style="position: sticky; top: 0; z-index: 5;">
                        <tr>
                            <th style="width: 35px;" class="text-center">No</th>
                            <th>Barang</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end" style="width: 130px;">Harga Beli (Rp)</th>
                            <th class="text-end" style="width: 140px;">Subtotal (Rp)</th>
                            <th class="text-center" style="width: 45px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="keranjang-beli-tbody">
                        <tr id="empty-row-beli">
                            <td colspan="6" class="text-center text-muted py-4 small">
                                <i class="bi bi-box-seam fs-2 opacity-25 d-block mb-1"></i>
                                Keranjang pembelian masih kosong. Silakan pilih produk di atas dan tentukan jumlah serta harga belinya.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light border-top border-2" id="tfoot-keranjang-beli" style="display: none;">
                        <tr>
                            <td colspan="4" class="text-end fw-bold text-uppercase py-2" style="font-size: 0.85rem;">Total Tagihan Pembelian:</td>
                            <td class="text-end fw-bold text-success font-monospace py-2" style="font-size: 1.25rem;" id="footer-total-biaya">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
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
        if (amount <= 500) return 500;

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
        // Sumber Master Data Barang & Role
        const allProducts = <?= json_encode($barangs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const isAdmin = <?= (session()->get('role') === 'admin') ? 'true' : 'false' ?>;
        let currentSelectedSupplier = $('#beli-supplier').val() || '';

        // Fungsi Render Ulang Dropdown Barang: Wajib Pilih Supplier Dulu, Kosong Jika Supplier Belum Dipilih
        function refreshProductDropdown(supplierCode) {
            $('#pilih-barang').empty();

            // Jika supplier belum dipilih: KOSONGKAN LIST DATA & KUNCI (DISABLED)
            if (!supplierCode) {
                $('#pilih-barang').prop('disabled', true);
                $('#pilih-barang').append('<option value="">-- Wajib Pilih Mitra Supplier Dahulu --</option>');
                $('#filter-supplier-badge').removeClass('text-success text-warning').addClass('text-danger fw-semibold')
                    .html('<i class="bi bi-exclamation-circle-fill me-1"></i>Pilih Mitra Supplier di sebelah kiri terlebih dahulu agar daftar produk muncul.');
                $('#input-harga-beli').val('');
                
                if ($('#pilih-barang').hasClass('select2-hidden-accessible')) {
                    $('#pilih-barang').select2('destroy');
                }
                $('#pilih-barang').select2({ theme: 'bootstrap-5', width: '100%', minimumResultsForSearch: 8 });
                return;
            }

            // Filter produk berdasarkan supplier terpilih (kode_supplier_1 atau kode_supplier_2)
            const matching = allProducts.filter(p => (p.kode_supplier_1 === supplierCode || p.kode_supplier_2 === supplierCode));

            if (matching.length > 0) {
                $('#pilih-barang').prop('disabled', false);
                $('#pilih-barang').append('<option value="">-- Pilih Barang / Bahan (' + matching.length + ' Produk Tersedia) --</option>');

                matching.forEach(b => {
                    const stokText = isAdmin ? ' - Stok: ' + new Intl.NumberFormat('id-ID').format(b.stok || 0) + ' ' + (b.satuan || '') : '';
                    const optText = b.nama_barang + ' (' + b.id_barang + ')' + stokText;
                    const $opt = $('<option>')
                        .val(b.id_barang)
                        .text(optText)
                        .attr('data-nama', b.nama_barang)
                        .attr('data-satuan', b.satuan)
                        .attr('data-supp1', b.kode_supplier_1 || '')
                        .attr('data-supp2', b.kode_supplier_2 || '')
                        .attr('data-hargabeli', parseFloat(b.harga_beli) || 0)
                        .attr('data-hargajual', parseFloat(b.harga_jual) || 0)
                        .attr('data-hargagrosir', parseFloat(b.harga_grosir) || 0)
                        .attr('data-qtypaketa', parseFloat(b.qty_paket_a) || 0)
                        .attr('data-hrgpaketa', parseFloat(b.hrg_paket_a) || 0)
                        .attr('data-qtypaketb', parseFloat(b.qty_paket_b) || 0)
                        .attr('data-hrgpaketb', parseFloat(b.hrg_paket_b) || 0)
                        .attr('data-qtypaketc', parseFloat(b.qty_paket_c) || 0)
                        .attr('data-hrgpaketc', parseFloat(b.hrg_paket_c) || 0)
                        .attr('data-stok', parseFloat(b.stok) || 0);

                    $('#pilih-barang').append($opt);
                });

                $('#filter-supplier-badge').removeClass('text-danger text-warning').addClass('text-success fw-semibold')
                    .html('<i class="bi bi-check-circle-fill me-1"></i>' + matching.length + ' produk siap dipilih dari supplier ini.');
            } else {
                $('#pilih-barang').prop('disabled', true);
                $('#pilih-barang').append('<option value="">-- Tidak ada produk terdaftar untuk supplier ini --</option>');
                $('#filter-supplier-badge').removeClass('text-danger text-success').addClass('text-warning fw-semibold')
                    .html('<i class="bi bi-info-circle-fill me-1"></i>Belum ada produk yang terdaftar untuk supplier ini di Master Barang.');
                $('#input-harga-beli').val('');
            }

            if ($('#pilih-barang').hasClass('select2-hidden-accessible')) {
                $('#pilih-barang').select2('destroy');
            }
            $('#pilih-barang').select2({ theme: 'bootstrap-5', width: '100%', minimumResultsForSearch: 8 });
        }

        // Event saat Mitra Supplier dipilih / diubah
        $('#beli-supplier').on('change', function() {
            const newSupp = $(this).val();

            // Jika sudah ada item di keranjang belanja, konfirmasi sebelum menghapus
            if (keranjangBeli.length > 0 && newSupp !== currentSelectedSupplier) {
                Swal.fire({
                    title: 'Ganti Mitra Supplier?',
                    text: 'Keranjang belanja saat ini berisi ' + keranjangBeli.length + ' item dari supplier sebelumnya. Mengganti supplier akan mengosongkan keranjang belanja.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Ganti Supplier',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        keranjangBeli = [];
                        renderKeranjangBeli();
                        currentSelectedSupplier = newSupp;
                        refreshProductDropdown(newSupp);
                    } else {
                        $('#beli-supplier').val(currentSelectedSupplier).trigger('change.select2');
                    }
                });
                return;
            }

            currentSelectedSupplier = newSupp;
            refreshProductDropdown(newSupp);
        });

        // Saat barang dipilih dari dropdown, isi harga beli default (terkunci dari master)
        function populatePriceFieldsFromSelected() {
            const $opt = $('#pilih-barang').find(':selected');
            if ($opt.val()) {
                const hargaBeli = parseFloat($opt.data('hargabeli')) || 0;
                const hargaJual = parseFloat($opt.data('hargajual')) || 0;
                const qtyA = parseInt($opt.data('qtypaketa'), 10) || 0;
                const hrgA = parseFloat($opt.data('hrgpaketa')) || 0;
                const qtyB = parseInt($opt.data('qtypaketb'), 10) || 0;
                const hrgB = parseFloat($opt.data('hrgpaketb')) || 0;
                const qtyC = parseInt($opt.data('qtypaketc'), 10) || 0;
                const hrgC = parseFloat($opt.data('hrgpaketc')) || 0;

                $('#input-harga-beli').val(formatRupiah(hargaBeli));
                $('#input-harga-jual').val(formatRupiah(hargaJual));
                $('#input-qty-a').val(qtyA > 0 ? qtyA : '');
                $('#input-hrg-a').val(hrgA > 0 ? formatRupiah(hrgA) : '');
                $('#input-qty-b').val(qtyB > 0 ? qtyB : '');
                $('#input-hrg-b').val(hrgB > 0 ? formatRupiah(hrgB) : '');
                $('#input-qty-c').val(qtyC > 0 ? qtyC : '');
                $('#input-hrg-c').val(hrgC > 0 ? formatRupiah(hrgC) : '');

                $('#panel-penyesuaian-harga').slideDown(150);
                checkPriceDifferenceBadge();
                $('#input-qty-beli').focus().select();
            } else {
                $('#input-harga-beli').val('');
                $('#input-harga-jual').val('');
                $('#input-qty-a').val('');
                $('#input-hrg-a').val('');
                $('#input-qty-b').val('');
                $('#input-hrg-b').val('');
                $('#input-qty-c').val('');
                $('#input-hrg-c').val('');
                $('#panel-penyesuaian-harga').slideUp(150);
            }
        }

        function checkPriceDifferenceBadge() {
            const $opt = $('#pilih-barang').find(':selected');
            if (!$opt.val()) return;

            const masterBeli = parseFloat($opt.data('hargabeli')) || 0;
            const masterJual = parseFloat($opt.data('hargajual')) || 0;
            const masterQtyA = parseInt($opt.data('qtypaketa'), 10) || 0;
            const masterHrgA = parseFloat($opt.data('hrgpaketa')) || 0;
            const masterQtyB = parseInt($opt.data('qtypaketb'), 10) || 0;
            const masterHrgB = parseFloat($opt.data('hrgpaketb')) || 0;
            const masterQtyC = parseInt($opt.data('qtypaketc'), 10) || 0;
            const masterHrgC = parseFloat($opt.data('hrgpaketc')) || 0;

            const inputBeli = unformatRupiah($('#input-harga-beli').val()) || 0;
            const inputJual = unformatRupiah($('#input-harga-jual').val()) || 0;
            const inputQtyA = parseInt($('#input-qty-a').val(), 10) || 0;
            const inputHrgA = unformatRupiah($('#input-hrg-a').val()) || 0;
            const inputQtyB = parseInt($('#input-qty-b').val(), 10) || 0;
            const inputHrgB = unformatRupiah($('#input-hrg-b').val()) || 0;
            const inputQtyC = parseInt($('#input-qty-c').val(), 10) || 0;
            const inputHrgC = unformatRupiah($('#input-hrg-c').val()) || 0;

            const isDiff = (
                Math.abs(inputBeli - masterBeli) > 0.01 ||
                Math.abs(inputJual - masterJual) > 0.01 ||
                Math.abs(inputHrgA - masterHrgA) > 0.01 ||
                Math.abs(inputHrgB - masterHrgB) > 0.01 ||
                Math.abs(inputHrgC - masterHrgC) > 0.01 ||
                inputQtyA !== masterQtyA ||
                inputQtyB !== masterQtyB ||
                inputQtyC !== masterQtyC
            );

            if (isDiff) {
                $('#badge-harga-berubah-status').html('<span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Harga Berubah (Akan Masuk Pending Approval Owner)</span>');
            } else {
                $('#badge-harga-berubah-status').html('<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Harga Sesuai Master</span>');
            }
        }

        $('#input-harga-beli, #input-harga-jual, #input-qty-a, #input-hrg-a, #input-qty-b, #input-hrg-b, #input-qty-c, #input-hrg-c').on('input change keyup', checkPriceDifferenceBadge);

        $('#pilih-barang').on('change', function() {
            populatePriceFieldsFromSelected();
        });

        // Inisialisasi awal: jika supplier belum dipilih, dropdown barang KOSONG dan DISABLED
        refreshProductDropdown(currentSelectedSupplier);

        // Tombol Tambah Item
        $('#btn-tambah-item').on('click', function() {
            const selectedSupplier = $('#beli-supplier').val();
            if (!selectedSupplier) {
                Swal.fire('Pilih Mitra Supplier', 'Wajib memilih Mitra Supplier terlebih dahulu sebelum memilih produk.', 'warning');
                return;
            }

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

            const masterBeli = parseFloat($opt.data('hargabeli')) || 0;
            const masterJual = parseFloat($opt.data('hargajual')) || 0;
            const masterQtyA = parseInt($opt.data('qtypaketa'), 10) || 0;
            const masterHrgA = parseFloat($opt.data('hrgpaketa')) || 0;
            const masterQtyB = parseInt($opt.data('qtypaketb'), 10) || 0;
            const masterHrgB = parseFloat($opt.data('hrgpaketb')) || 0;
            const masterQtyC = parseInt($opt.data('qtypaketc'), 10) || 0;
            const masterHrgC = parseFloat($opt.data('hrgpaketc')) || 0;

            const inputJual = unformatRupiah($('#input-harga-jual').val()) || masterJual;
            const inputQtyA = parseInt($('#input-qty-a').val(), 10) || 0;
            const inputHrgA = unformatRupiah($('#input-hrg-a').val()) || 0;
            const inputQtyB = parseInt($('#input-qty-b').val(), 10) || 0;
            const inputHrgB = unformatRupiah($('#input-hrg-b').val()) || 0;
            const inputQtyC = parseInt($('#input-qty-c').val(), 10) || 0;
            const inputHrgC = unformatRupiah($('#input-hrg-c').val()) || 0;

            const isPriceChanged = (
                Math.abs(hargaBeli - masterBeli) > 0.01 ||
                Math.abs(inputJual - masterJual) > 0.01 ||
                Math.abs(inputHrgA - masterHrgA) > 0.01 ||
                Math.abs(inputHrgB - masterHrgB) > 0.01 ||
                Math.abs(inputHrgC - masterHrgC) > 0.01 ||
                inputQtyA !== masterQtyA ||
                inputQtyB !== masterQtyB ||
                inputQtyC !== masterQtyC
            );

            // Cek apakah item sudah ada di keranjang
            const existing = keranjangBeli.find(x => x.id_barang === idBarang);
            if (existing) {
                existing.qty += qty;
                existing.harga_beli = hargaBeli;
                existing.harga_jual = inputJual;
                existing.qty_paket_a = inputQtyA;
                existing.harga_paket_a = inputHrgA;
                existing.qty_paket_b = inputQtyB;
                existing.harga_paket_b = inputHrgB;
                existing.qty_paket_c = inputQtyC;
                existing.harga_paket_c = inputHrgC;
                existing.subtotal = existing.qty * hargaBeli;
                existing.is_price_changed = isPriceChanged;
            } else {
                keranjangBeli.push({
                    id_barang: idBarang,
                    nama_barang: namaBarang,
                    satuan: satuan,
                    qty: qty,
                    harga_beli: hargaBeli,
                    harga_beli_lama: masterBeli,
                    harga_jual: inputJual,
                    harga_jual_lama: masterJual,
                    qty_paket_a: inputQtyA,
                    harga_paket_a: inputHrgA,
                    qty_paket_b: inputQtyB,
                    harga_paket_b: inputHrgB,
                    qty_paket_c: inputQtyC,
                    harga_paket_c: inputHrgC,
                    subtotal: qty * hargaBeli,
                    is_price_changed: isPriceChanged
                });
            }

            renderKeranjangBeli();

            // Reset input
            $('#pilih-barang').val('').trigger('change');
            $('#input-qty-beli').val(1);
            $('#input-harga-beli').val('');
            $('#input-harga-jual').val('');
            $('#input-qty-a').val('');
            $('#input-hrg-a').val('');
            $('#input-qty-b').val('');
            $('#input-hrg-b').val('');
            $('#input-qty-c').val('');
            $('#input-hrg-c').val('');
            $('#panel-penyesuaian-harga').slideUp(150);
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
                $('#footer-total-biaya').text('Rp 0');
                $('#tfoot-keranjang-beli').hide();
                $('#beli-pembulatan-info').addClass('d-none').text('');
                $('#display-total-item').text('0 Item Barang Masuk');
                return;
            }

            let total = 0;
            let totalQty = 0;

            keranjangBeli.forEach((item, idx) => {
                total += item.subtotal;
                totalQty += item.qty;

                const badgePending = item.is_price_changed 
                    ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1 py-0 px-1" style="font-size: 0.65rem;" title="Harga diubah kasir, menunggu approval Owner"><i class="bi bi-clock-history me-1"></i>Pending Approval</span>'
                    : '';

                $tbody.append(`
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>
                            <div class="fw-semibold text-dark">${item.nama_barang} ${badgePending}</div>
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
            $('#footer-total-biaya').text('Rp ' + new Intl.NumberFormat('id-ID').format(roundedTotal));
            $('#tfoot-keranjang-beli').show();
            if (pembulatan !== 0) {
                $('#beli-pembulatan-info').removeClass('d-none').text(`Pembulatan: ${pembulatan > 0 ? '+' : ''}Rp ${new Intl.NumberFormat('id-ID').format(pembulatan)} (Asli: Rp ${new Intl.NumberFormat('id-ID').format(total)})`);
            } else {
                $('#beli-pembulatan-info').addClass('d-none').text('');
            }
            $('#display-total-item').text(`${keranjangBeli.length} Macam Produk (${new Intl.NumberFormat('id-ID').format(totalQty)} Satuan)`);
        }

        // Keterangan Status Bayar Dinamis
        $('#beli-status-bayar').on('change', function() {
            const val = $(this).val();
            const $ket = $('#status-bayar-keterangan');
            if (val === 'Lunas') {
                $ket.html('<span class="text-success"><i class="bi bi-cash-stack me-1"></i>Memotong saldo kas fisik laci kasir hari ini.</span>');
            } else if (val === 'Lunas (Transfer)') {
                $ket.html('<span class="text-info fw-bold"><i class="bi bi-bank me-1"></i>Dibayar via Transfer Rekening (TIDAK memotong kas fisik laci).</span>');
            } else {
                $ket.html('<span class="text-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pembelian tempo/kredit (belum lunas, tidak potong kas).</span>');
            }
        });

        let isSavingPembelian = false;

        // Simpan Transaksi Pembelian
        $('#btn-simpan-pembelian').on('click', function(e) {
            e.preventDefault();
            if (isSavingPembelian) return false;

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
                    if (isSavingPembelian) return false;
                    isSavingPembelian = true;
                    $('#btn-simpan-pembelian').prop('disabled', true).addClass('disabled');

                    if (typeof showSaveLoading === 'function') {
                        showSaveLoading('Menyimpan Transaksi Pembelian...', 'Mohon tunggu sebentar, data pembelian dan penambahan stok barang sedang diproses...');
                    } else {
                        Swal.fire({
                            title: 'Menyimpan Transaksi...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                    }

                    $.ajax({
                        url: '<?= base_url('pembelian/simpan') ?>',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        success: function(resp) {
                            if (typeof hideSaveLoading === 'function') hideSaveLoading();
                            Swal.fire('Berhasil Disimpan!', resp.message, 'success').then(() => {
                                window.location.href = '<?= base_url('pembelian') ?>';
                            });
                        },
                        error: function(xhr) {
                            isSavingPembelian = false;
                            $('#btn-simpan-pembelian').prop('disabled', false).removeClass('disabled');
                            if (typeof hideSaveLoading === 'function') hideSaveLoading();
                            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memproses data.';
                            Swal.fire('Gagal Menyimpan!', err, 'error');
                        }
                    });
                }
            });
        });

        // Buka Modal LookUp Barang (Mode Pembelian: Khusus Supplier Terpilih, Semua Status Aktif & Nonaktif)
        $('#btn-open-lookup-beli').on('click', function(e) {
            e.preventDefault();
            const selectedSupplier = $('#beli-supplier').val();
            if (!selectedSupplier) {
                Swal.fire({
                    title: 'Pilih Mitra Supplier',
                    text: 'Silakan pilih Mitra Supplier di sebelah kiri terlebih dahulu agar daftar produk supplier ditampilkan di LookUp.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $('#beli-supplier').focus();
                return;
            }

            StockLookup.open({
                mode: 'pembelian',
                supplier: selectedSupplier,
                onSelect: function(item) {
                    // Cari atau tambahkan opsi di dropdown select2
                    let $existingOpt = $('#pilih-barang').find('option[value="' + item.id_barang + '"]');
                    if ($existingOpt.length === 0) {
                        const stokText = isAdmin ? ' - Stok: ' + new Intl.NumberFormat('id-ID').format(item.stok_cabang || item.stok_total || 0) + ' ' + (item.satuan || '') : '';
                        const optText = item.nama_barang + ' (' + item.id_barang + ')' + stokText;
                        const $newOpt = $('<option>')
                            .val(item.id_barang)
                            .text(optText)
                            .attr('data-nama', item.nama_barang)
                            .attr('data-satuan', item.satuan)
                            .attr('data-supp1', item.kode_supplier_1 || '')
                            .attr('data-supp2', item.kode_supplier_2 || '')
                            .attr('data-hargabeli', parseFloat(item.harga_beli) || 0)
                            .attr('data-hargajual', parseFloat(item.harga_jual) || 0)
                            .attr('data-hargagrosir', parseFloat(item.harga_grosir) || 0)
                            .attr('data-qtypaketa', parseFloat(item.qty_paket_a) || 0)
                            .attr('data-hrgpaketa', parseFloat(item.hrg_paket_a) || 0)
                            .attr('data-qtypaketb', parseFloat(item.qty_paket_b) || 0)
                            .attr('data-hrgpaketb', parseFloat(item.hrg_paket_b) || 0)
                            .attr('data-qtypaketc', parseFloat(item.qty_paket_c) || 0)
                            .attr('data-hrgpaketc', parseFloat(item.hrg_paket_c) || 0)
                            .attr('data-stok', parseFloat(item.stok_cabang || item.stok_total) || 0);

                        $('#pilih-barang').append($newOpt);
                    }
                    $('#pilih-barang').prop('disabled', false).val(item.id_barang).trigger('change');
                    setTimeout(() => $('#input-qty-beli').focus().select(), 250);
                }
            });
        });

        // ============================================================
        // KEYBOARD SHORTCUT TRANSAKSI PEMBELIAN
        // ============================================================
        // 1. Enter pada input Qty Beli: langsung klik Tambah Item
        $('#input-qty-beli').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#btn-tambah-item').trigger('click');
            }
        });

        // 2. Global Shortcut: F2 (LookUp Barang), F9 / Ctrl+S (Simpan Transaksi)
        $(document).on('keydown', function(e) {
            // Abaikan jika modal SweetAlert sedang terbuka
            if (Swal.isVisible()) return;

            // F2: Buka LookUp Barang
            if (e.key === 'F2') {
                e.preventDefault();
                $('#btn-open-lookup-beli').trigger('click');
                return false;
            }

            // F9 atau Ctrl+S: Simpan Transaksi Pembelian
            if (e.key === 'F9' || (e.ctrlKey && (e.key === 's' || e.key === 'S'))) {
                e.preventDefault();
                $('#btn-simpan-pembelian').trigger('click');
                return false;
            }
        });
    });
</script>

<?= $this->include('layout/modal_lookup_barang') ?>
<?= $this->endSection() ?>

