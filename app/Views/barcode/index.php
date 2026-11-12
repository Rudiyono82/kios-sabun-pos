<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Include JsBarcode Library for generating high-definition SVG/Canvas barcodes -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
    /* Styling Preview Card Label */
    .barcode-preview-container {
        background-color: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        min-height: 250px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px;
        align-content: flex-start;
        max-height: 520px;
        overflow-y: auto;
    }

    /* Template 1: Label Barcode Produk Kemasan (Stiker Thermal Roll) */
    .label-item-stiker {
        background: #ffffff;
        border: 1px solid #0f172a;
        border-radius: 4px;
        padding: 6px 8px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: inline-flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        box-sizing: border-box;
        page-break-inside: avoid;
    }
    .label-size-40x30 { width: 151px; height: 113px; }
    .label-size-50x30 { width: 189px; height: 113px; }
    .label-size-33x15 { width: 125px; height: 57px; padding: 2px 4px; }

    /* Template 2: Price Tag Rak Toko (Shelf Talker) */
    .label-item-pricetag {
        background: #ffffff;
        border: 2px solid #0284c7;
        border-radius: 6px;
        padding: 8px 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        display: inline-flex;
        flex-direction: column;
        box-sizing: border-box;
        page-break-inside: avoid;
        width: 220px;
        min-height: 130px;
    }

    /* Print Specific Media Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .no-print {
            display: none !important;
        }
        .page-break {
            page-break-after: always;
        }
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 no-print">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-upc-scan text-primary me-2"></i>Cetak Barcode & Label Harga Rak</h4>
        <p class="text-muted small mb-0">Cetak stiker barcode untuk kemasan botol/jerigen sabun curah & parfum atau label harga untuk display rak toko.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-danger rounded-pill px-3" id="btn-kosongkan-antrean">
            <i class="bi bi-trash3 me-1"></i> Kosongkan Antrean
        </button>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn-cetak-langsung">
            <i class="bi bi-printer-fill me-2"></i> Cetak Label Sekarang
        </button>
    </div>
</div>

<div class="row g-4 no-print">
    <!-- KOLOM KIRI: PILIH MASTER BARANG -->
    <div class="col-12 col-lg-5">
        <div class="card-modern p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes me-2 text-primary"></i>Pilih Produk untuk Dicetak</h6>
                <span class="badge bg-primary-subtle text-primary fw-semibold"><?= count($barang_list) ?> Produk Aktif</span>
            </div>

            <!-- Filter Kategori & Pencarian -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <select class="form-select form-select-sm" id="filter-kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= esc($kat) ?>"><?= esc($kat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm" id="search-barang-barcode" placeholder="Ketik nama / barcode...">
                </div>
            </div>

            <!-- List Barang -->
            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                <table class="table table-hover table-sm align-middle mb-0" id="table-pilih-barang">
                    <thead class="table-light small">
                        <tr>
                            <th>Barang</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center" style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($barang_list as $b): ?>
                            <tr class="item-barang-row" data-kategori="<?= esc($b['nama_kategori'] ?? '') ?>" data-search="<?= strtolower(esc($b['nama_barang'] . ' ' . $b['id_barang'])) ?>">
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;"><?= esc($b['nama_barang']) ?></div>
                                    <small class="text-muted font-monospace"><i class="bi bi-upc me-1"></i><?= esc($b['id_barang']) ?> &bull; <?= esc($b['satuan']) ?></small>
                                </td>
                                <td class="text-end fw-bold text-primary small">
                                    Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2 btn-tambah-antrean"
                                        data-id="<?= esc($b['id_barang']) ?>"
                                        data-nama="<?= esc($b['nama_barang']) ?>"
                                        data-satuan="<?= esc($b['satuan']) ?>"
                                        data-harga="<?= (float)$b['harga_jual'] ?>"
                                        data-paket-a="<?= (float)($b['hrg_paket_a'] ?? 0) ?>"
                                        data-qty-a="<?= (float)($b['qty_paket_a'] ?? 0) ?>"
                                        data-paket-b="<?= (float)($b['hrg_paket_b'] ?? 0) ?>"
                                        data-qty-b="<?= (float)($b['qty_paket_b'] ?? 0) ?>"
                                        title="Tambah ke Antrean Cetak">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: PENGATURAN LABEL & ANTREAN CETAK -->
    <div class="col-12 col-lg-7">
        <!-- Panel Pengaturan Template Cetak -->
        <div class="card-modern p-3 mb-3">
            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Pengaturan Format & Ukuran Label</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Jenis Format Cetak</label>
                    <select class="form-select form-select-sm" id="select-jenis-format">
                        <option value="stiker_produk">Stiker Barcode Produk (Kemasan Botol/Jerigen)</option>
                        <option value="price_tag_rak">Price Tag Label Rak Toko (Shelf Talker)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Ukuran Kertas / Label</label>
                    <select class="form-select form-select-sm" id="select-ukuran-kertas">
                        <option value="label-size-40x30">Thermal Roll 40mm x 30mm (Standar)</option>
                        <option value="label-size-50x30">Thermal Roll 50mm x 30mm (Lebar)</option>
                        <option value="label-size-33x15">Thermal Roll 33mm x 15mm (Mini)</option>
                        <option value="sheet-a4">Kertas A4 (Grid Label Stiker)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold mb-2">Opsi Tampilan Informasi:</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="chk-nama-toko" checked>
                            <label class="form-check-label small" for="chk-nama-toko">Nama Toko (Kios Sabun)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="chk-harga-eceran" checked>
                            <label class="form-check-label small" for="chk-harga-eceran">Harga Eceran (Rp)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="chk-harga-grosir" checked>
                            <label class="form-check-label small" for="chk-harga-grosir">Harga Grosir Paket A & B</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Antrean Cetak -->
        <div class="card-modern p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Daftar Antrean Cetak</h6>
                <span class="small text-muted" id="total-lembar-label">Total: <strong>0 Lembar</strong></span>
            </div>
            
            <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                <table class="table table-hover table-sm align-middle mb-0" id="table-antrean">
                    <thead class="table-light small">
                        <tr>
                            <th>Barang</th>
                            <th class="text-center" style="width: 110px;">Qty Lembar</th>
                            <th class="text-end" style="width: 100px;">Harga</th>
                            <th class="text-center" style="width: 50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="antrean-tbody">
                        <tr id="empty-antrean-row">
                            <td colspan="4" class="text-center text-muted py-4 small">
                                <i class="bi bi-tag fs-3 opacity-25 d-block mb-1"></i>
                                Antrean cetak masih kosong. Klik tombol <strong>+</strong> pada daftar produk di sebelah kiri.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Live Preview Container -->
        <div class="card-modern p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-eye me-2 text-primary"></i>Pratinjau Hasil Cetak (Live Preview)</h6>
                <small class="text-muted">Tampilan cetak label stiker aktual</small>
            </div>
            <div class="barcode-preview-container" id="preview-box">
                <div class="w-100 text-center text-muted my-auto py-4">
                    <i class="bi bi-printer opacity-25 fs-1 d-block mb-2"></i>
                    Belum ada label dalam antrean.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PRINT AREA (HANYA DITAMPILKAN SAAT PROSES CETAK BROWSER) -->
<div id="print-area" class="d-none"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let antreanCetak = [];

    $(document).ready(function() {
        // 1. Filter Kategori
        $('#filter-kategori').on('change', function() {
            filterProduk();
        });

        // 2. Search Barang
        $('#search-barang-barcode').on('input', function() {
            filterProduk();
        });

        function filterProduk() {
            const kat = $('#filter-kategori').val().toLowerCase();
            const q = $('#search-barang-barcode').val().toLowerCase().trim();

            $('.item-barang-row').each(function() {
                const itemKat = $(this).data('kategori').toString().toLowerCase();
                const itemSearch = $(this).data('search').toString().toLowerCase();

                const matchKat = !kat || itemKat === kat;
                const matchQ = !q || itemSearch.includes(q);

                if (matchKat && matchQ) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // 3. Tambah ke Antrean Cetak
        $('.btn-tambah-antrean').on('click', function() {
            const b = $(this).data();
            
            // Cek apakah sudah ada di antrean
            const existing = antreanCetak.find(x => x.id === b.id);
            if (existing) {
                existing.qty++;
            } else {
                antreanCetak.push({
                    id: b.id,
                    nama: b.nama,
                    satuan: b.satuan,
                    harga: parseFloat(b.harga) || 0,
                    paket_a: parseFloat(b.paketA) || 0,
                    qty_a: parseFloat(b.qtyA) || 0,
                    paket_b: parseFloat(b.paketB) || 0,
                    qty_b: parseFloat(b.qtyB) || 0,
                    qty: 1
                });
            }

            renderAntrean();
            renderPreview();
        });

        // 4. Ubah Qty di Antrean
        $(document).on('change input', '.input-qty-antrean', function() {
            const id = $(this).data('id');
            const qty = parseInt($(this).val()) || 1;
            const item = antreanCetak.find(x => x.id === id);
            if (item) {
                item.qty = Math.max(1, qty);
                renderPreview();
                updateTotalLembar();
            }
        });

        // 5. Hapus dari Antrean
        $(document).on('click', '.btn-hapus-antrean', function() {
            const id = $(this).data('id');
            antreanCetak = antreanCetak.filter(x => x.id !== id);
            renderAntrean();
            renderPreview();
        });

        // 6. Kosongkan Antrean
        $('#btn-kosongkan-antrean').on('click', function() {
            if (antreanCetak.length === 0) return;
            antreanCetak = [];
            renderAntrean();
            renderPreview();
        });

        // 7. Render Antrean Table
        function renderAntrean() {
            const $tbody = $('#antrean-tbody');
            $tbody.empty();

            if (antreanCetak.length === 0) {
                $tbody.append(`
                    <tr id="empty-antrean-row">
                        <td colspan="4" class="text-center text-muted py-4 small">
                            <i class="bi bi-tag fs-3 opacity-25 d-block mb-1"></i>
                            Antrean cetak masih kosong. Klik tombol <strong>+</strong> pada daftar produk di sebelah kiri.
                        </td>
                    </tr>
                `);
                updateTotalLembar();
                return;
            }

            antreanCetak.forEach(item => {
                $tbody.append(`
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">${item.nama}</div>
                            <small class="text-muted font-monospace">${item.id}</small>
                        </td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center fw-bold input-qty-antrean" 
                                data-id="${item.id}" value="${item.qty}" min="1" max="999">
                        </td>
                        <td class="text-end fw-bold text-primary small">
                            Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle py-0 px-1 btn-hapus-antrean" data-id="${item.id}">
                                <i class="bi bi-x"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            updateTotalLembar();
        }

        function updateTotalLembar() {
            const total = antreanCetak.reduce((acc, curr) => acc + curr.qty, 0);
            $('#total-lembar-label').html(`Total: <strong>${total} Lembar</strong>`);
        }

        // 8. Opsi Format Berubah -> Update Preview
        $('#select-jenis-format, #select-ukuran-kertas, #chk-nama-toko, #chk-harga-eceran, #chk-harga-grosir').on('change', function() {
            renderPreview();
        });

        // 9. Generate & Render Live Preview
        function renderPreview() {
            const $box = $('#preview-box');
            $box.empty();

            if (antreanCetak.length === 0) {
                $box.html(`
                    <div class="w-100 text-center text-muted my-auto py-4">
                        <i class="bi bi-printer opacity-25 fs-1 d-block mb-2"></i>
                        Belum ada label dalam antrean.
                    </div>
                `);
                return;
            }

            const jenisFormat = $('#select-jenis-format').val();
            const ukuranClass = $('#select-ukuran-kertas').val();
            const showToko = $('#chk-nama-toko').is(':checked');
            const showHarga = $('#chk-harga-eceran').is(':checked');
            const showGrosir = $('#chk-harga-grosir').is(':checked');

            let indexBarcode = 0;

            antreanCetak.forEach(item => {
                // Buat preview sejumlah qty (maksimal 3 copy di layar preview agar tidak lag, namun saat print penuh)
                const previewCopies = Math.min(item.qty, 3);

                for (let i = 0; i < previewCopies; i++) {
                    indexBarcode++;
                    const barcodeId = 'barcode-svg-' + indexBarcode;

                    if (jenisFormat === 'stiker_produk') {
                        // FORMAT 1: STIKER PRODUK KEMASAN
                        const isMini = ukuranClass === 'label-size-33x15';
                        const $card = $(`
                            <div class="label-item-stiker ${ukuranClass}">
                                ${showToko ? `<div style="font-size: ${isMini ? '7px' : '9px'}; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #475569;">KIOS SABUN & PARFUM</div>` : ''}
                                <div style="font-size: ${isMini ? '8px' : '10.5px'}; font-weight: bold; line-height: 1.1; margin: 1px 0; max-height: ${isMini ? '12px' : '26px'}; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;">
                                    ${item.nama}
                                </div>
                                <div style="margin: 0; line-height: 0;">
                                    <svg id="${barcodeId}"></svg>
                                </div>
                                ${showHarga ? `
                                    <div style="font-size: ${isMini ? '8.5px' : '12px'}; font-weight: 800; color: #0f172a; line-height: 1.1;">
                                        Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}
                                    </div>
                                ` : ''}
                            </div>
                        `);
                        $box.append($card);

                        // Render Barcode
                        try {
                            JsBarcode('#' + barcodeId, item.id, {
                                format: "CODE128",
                                width: isMini ? 1.0 : 1.3,
                                height: isMini ? 14 : 28,
                                displayValue: !isMini,
                                fontSize: 9,
                                margin: 0
                            });
                        } catch(e) {
                            console.error(e);
                        }

                    } else {
                        // FORMAT 2: PRICE TAG RAK TOKO (SHELF TALKER)
                        const $card = $(`
                            <div class="label-item-pricetag">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                                    <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #0284c7;">
                                        ${showToko ? 'KIOS SABUN & PARFUM' : 'PRICE TAG'}
                                    </span>
                                    <span style="font-size: 8px; color: #64748b;">${item.satuan}</span>
                                </div>
                                <div style="font-size: 12px; font-weight: 800; line-height: 1.2; color: #0f172a; margin-bottom: 4px;">
                                    ${item.nama}
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-auto">
                                    <div>
                                        <svg id="${barcodeId}"></svg>
                                    </div>
                                    <div class="text-end">
                                        <div style="font-size: 8px; color: #64748b; font-weight: 600;">HARGA ECERAN</div>
                                        <div style="font-size: 16px; font-weight: 900; color: #0284c7; line-height: 1;">
                                            Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}
                                        </div>
                                    </div>
                                </div>
                                ${showGrosir && (item.paket_a > 0 || item.paket_b > 0) ? `
                                    <div class="mt-2 pt-1 border-top d-flex justify-content-between" style="font-size: 8px; background: #f8fafc; padding: 2px 4px; border-radius: 3px;">
                                        ${item.paket_a > 0 ? `<span>Paket A (&ge;${item.qty_a}): <strong>Rp ${new Intl.NumberFormat('id-ID').format(item.paket_a)}</strong></span>` : ''}
                                        ${item.paket_b > 0 ? `<span>Paket B (&ge;${item.qty_b}): <strong>Rp ${new Intl.NumberFormat('id-ID').format(item.paket_b)}</strong></span>` : ''}
                                    </div>
                                ` : ''}
                            </div>
                        `);
                        $box.append($card);

                        try {
                            JsBarcode('#' + barcodeId, item.id, {
                                format: "CODE128",
                                width: 1.1,
                                height: 26,
                                displayValue: true,
                                fontSize: 9,
                                margin: 0
                            });
                        } catch(e) {
                            console.error(e);
                        }
                    }
                }
            });
        }

        // 10. CETAK SEKARANG (WINDOW PRINT)
        $('#btn-cetak-langsung').on('click', function() {
            if (antreanCetak.length === 0) {
                Swal.fire('Antrean Kosong', 'Silakan pilih produk yang ingin dicetak terlebih dahulu.', 'warning');
                return;
            }

            const jenisFormat = $('#select-jenis-format').val();
            const ukuranClass = $('#select-ukuran-kertas').val();
            const showToko = $('#chk-nama-toko').is(':checked');
            const showHarga = $('#chk-harga-eceran').is(':checked');
            const showGrosir = $('#chk-harga-grosir').is(':checked');

            const $printArea = $('#print-area');
            $printArea.empty().removeClass('d-none');

            // Susun kontainer cetak
            const $wrap = $('<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-start; align-content: flex-start;"></div>');
            $printArea.append($wrap);

            let printIdx = 0;

            antreanCetak.forEach(item => {
                for (let i = 0; i < item.qty; i++) {
                    printIdx++;
                    const printBarcodeId = 'p-barcode-' + printIdx;

                    if (jenisFormat === 'stiker_produk') {
                        const isMini = ukuranClass === 'label-size-33x15';
                        const $card = $(`
                            <div class="label-item-stiker ${ukuranClass}" style="margin: 4px;">
                                ${showToko ? `<div style="font-size: ${isMini ? '7px' : '9px'}; font-weight: 700; text-transform: uppercase; color: #475569;">KIOS SABUN & PARFUM</div>` : ''}
                                <div style="font-size: ${isMini ? '8px' : '10.5px'}; font-weight: bold; line-height: 1.1; margin: 1px 0; max-height: ${isMini ? '12px' : '26px'}; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;">
                                    ${item.nama}
                                </div>
                                <div style="margin: 0; line-height: 0;">
                                    <svg id="${printBarcodeId}"></svg>
                                </div>
                                ${showHarga ? `
                                    <div style="font-size: ${isMini ? '8.5px' : '12px'}; font-weight: 800; color: #000; line-height: 1.1;">
                                        Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}
                                    </div>
                                ` : ''}
                            </div>
                        `);
                        $wrap.append($card);

                        try {
                            JsBarcode('#' + printBarcodeId, item.id, {
                                format: "CODE128",
                                width: isMini ? 1.0 : 1.3,
                                height: isMini ? 14 : 28,
                                displayValue: !isMini,
                                fontSize: 9,
                                margin: 0
                            });
                        } catch(e) {}

                    } else {
                        const $card = $(`
                            <div class="label-item-pricetag" style="margin: 6px;">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                                    <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #000;">
                                        ${showToko ? 'KIOS SABUN & PARFUM' : 'PRICE TAG'}
                                    </span>
                                    <span style="font-size: 8px; color: #333;">${item.satuan}</span>
                                </div>
                                <div style="font-size: 12px; font-weight: 800; line-height: 1.2; color: #000; margin-bottom: 4px;">
                                    ${item.nama}
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-auto">
                                    <div>
                                        <svg id="${printBarcodeId}"></svg>
                                    </div>
                                    <div class="text-end">
                                        <div style="font-size: 8px; color: #444; font-weight: 600;">HARGA ECERAN</div>
                                        <div style="font-size: 16px; font-weight: 900; color: #000; line-height: 1;">
                                            Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}
                                        </div>
                                    </div>
                                </div>
                                ${showGrosir && (item.paket_a > 0 || item.paket_b > 0) ? `
                                    <div class="mt-2 pt-1 border-top d-flex justify-content-between" style="font-size: 8px; background: #fff; padding: 2px 4px; border: 1px solid #ccc; border-radius: 3px;">
                                        ${item.paket_a > 0 ? `<span>Paket A (&ge;${item.qty_a}): <strong>Rp ${new Intl.NumberFormat('id-ID').format(item.paket_a)}</strong></span>` : ''}
                                        ${item.paket_b > 0 ? `<span>Paket B (&ge;${item.qty_b}): <strong>Rp ${new Intl.NumberFormat('id-ID').format(item.paket_b)}</strong></span>` : ''}
                                    </div>
                                ` : ''}
                            </div>
                        `);
                        $wrap.append($card);

                        try {
                            JsBarcode('#' + printBarcodeId, item.id, {
                                format: "CODE128",
                                width: 1.1,
                                height: 26,
                                displayValue: true,
                                fontSize: 9,
                                margin: 0
                            });
                        } catch(e) {}
                    }
                }
            });

            // Jalankan Print Dialog Browser
            setTimeout(function() {
                window.print();
                $printArea.addClass('d-none').empty();
            }, 300);
        });
    });
</script>
<?= $this->endSection() ?>

