<!-- MODAL LOOKUP LIST OF STOCK (POPUP PENCARIAN & PEMILIHAN BARANG) -->
<style>
/* High-Contrast Active Selection for LookUp Stock Grid */
#table-lookup-items tr.lookup-item-row {
    transition: background-color 0.08s ease, color 0.08s ease;
    user-select: none;
}

#table-lookup-items tr.lookup-item-row.active,
#table-lookup-items tr.lookup-item-row.active > td,
#table-lookup-items tr.lookup-item-row.active:hover,
#table-lookup-items tr.lookup-item-row.active:hover > td {
    background-color: #0284c7 !important; /* Biru Terang Kontras Tinggi */
    color: #ffffff !important;
}

#table-lookup-items tr.lookup-item-row.active td,
#table-lookup-items tr.lookup-item-row.active td *,
#table-lookup-items tr.lookup-item-row.active:hover td,
#table-lookup-items tr.lookup-item-row.active:hover td * {
    color: #ffffff !important;
}

#table-lookup-items tr.lookup-item-row.active {
    box-shadow: inset 5px 0 0 0 #f59e0b; /* Aksen garis oranye-emas di kiri */
}

#table-lookup-items tr.lookup-item-row.active .badge {
    background-color: rgba(255, 255, 255, 0.28) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
}

#table-lookup-items tr.lookup-item-row.active .btn-choose-row {
    background-color: #ffffff !important;
    color: #0284c7 !important;
    border-color: #ffffff !important;
    font-weight: 700 !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.25) !important;
}

#table-lookup-items tr.lookup-item-row:hover:not(.active) {
    background-color: #f0f9ff !important;
}
</style>
<div class="modal fade" id="modalLookupStock" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1140px;">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <!-- Header Modal -->
            <div class="modal-header modal-header-draggable py-2 px-3 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0284c7 100%); border-bottom: 3px solid #38bdf8;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-primary p-2 rounded-3 shadow-sm">
                        <i class="bi bi-card-checklist fs-6"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="modal-title fw-bold text-white mb-0" id="lookup-modal-title">List of Stock</h6>
                            <span class="badge bg-white bg-opacity-25 text-white px-2 py-0 small d-inline-flex align-items-center gap-1 border border-white-50" style="font-size: 0.68rem; font-weight: 500;" title="Klik dan tahan untuk menggeser popup ini">
                                <i class="bi bi-arrows-move"></i> Geser
                            </span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.72rem;" id="lookup-modal-subtitle">View and manage Item Stocks</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-primary border font-monospace px-3 py-1 fw-bold shadow-xs" id="lookup-badge-mode">MODE PENJUALAN</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 bg-light">
                <!-- Baris Filter & Pencarian Sesuai Screenshot VB6 (Sejajar 1 Baris Penuh) -->
                <div class="card border p-2 px-3 mb-3 bg-white shadow-sm rounded-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                            <!-- Filter Dropdown (Sejajar Horisontal) -->
                            <div class="input-group input-group-sm flex-nowrap" style="width: 260px; min-width: 260px;">
                                <span class="input-group-text bg-light text-dark fw-bold text-nowrap" style="font-size: 0.78rem;">
                                    <i class="bi bi-funnel me-1 text-primary"></i> Filter :
                                </span>
                                <select id="lookup-filter-by" class="form-select form-select-sm fw-semibold">
                                    <option value="nama" selected>NAMA BARANG</option>
                                    <option value="kode">ID BARANG</option>
                                    <option value="barcode">KODE BARCODE</option>
                                    <option value="kategori">KATEGORI</option>
                                    <option value="all">SEMUA KOLOM</option>
                                </select>
                            </div>

                            <!-- Input Pencarian (Sejajar Horisontal & Fleksibel) -->
                            <div class="input-group input-group-sm flex-nowrap flex-grow-1" style="min-width: 260px; max-width: 520px;">
                                <span class="input-group-text bg-light text-dark fw-bold text-nowrap" style="font-size: 0.78rem;">
                                    <i class="bi bi-search me-1 text-success"></i> Kata Cari :
                                </span>
                                <input type="text" id="lookup-search-keyword" class="form-control" placeholder="Ketik kata yang dicari lalu tekan Enter..." autocomplete="off">
                                <button class="btn btn-sm btn-success fw-bold px-3" type="button" id="btn-lookup-do-search" title="Mulai Pencarian [Enter]">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Refresh & Petunjuk Keyboard -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-lookup-refresh-data" title="Refresh Data">
                                <i class="bi bi-arrow-repeat me-1"></i> Refresh
                            </button>
                            <small class="text-muted d-none d-lg-inline text-nowrap" style="font-size: 0.72rem;">
                                Gunakan <kbd class="bg-light text-dark border">↑</kbd> <kbd class="bg-light text-dark border">↓</kbd> & <kbd class="bg-primary text-white border">Enter</kbd>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Konten Ganda: Tabel Data Barang (Kiri) & Box Preview Foto (Kanan) -->
                <div class="row g-3">
                    <!-- 1. Grid / Tabel Data Barang -->
                    <div class="col-12 col-lg-8">
                        <div class="card border shadow-sm rounded-3 overflow-hidden bg-white">
                            <div class="table-responsive" style="max-height: 420px; min-height: 320px; overflow-y: auto;" id="lookup-table-container">
                                <table class="table table-hover align-middle mb-0 font-monospace-cells" id="table-lookup-items" style="font-size: 0.82rem;">
                                    <thead class="sticky-top small text-uppercase text-white" style="background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); z-index: 5;">
                                        <tr>
                                            <th style="width: 105px;">ID Barang</th>
                                            <th>Nama Barang</th>
                                            <th style="width: 125px;">Kategori</th>
                                            <th style="width: 85px;">Satuan</th>
                                            <th class="text-end" style="width: 120px;" id="lookup-th-col-harga">Harga Jual</th>
                                            <th class="text-center" style="width: 80px;" id="lookup-th-col-status">Status</th>
                                            <th class="text-center" style="width: 75px;">Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lookup-items-tbody">
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                                <div>Memuat data barang...</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Box Preview Foto & Detail Sesuai Screenshot VB6 -->
                    <div class="col-12 col-lg-4">
                        <div class="card border shadow-sm rounded-3 overflow-hidden h-100 bg-white d-flex flex-column">
                            <!-- Judul Preview Cyan Berwarna -->
                            <div id="lookup-preview-nama" class="p-2 px-3 text-center fw-bold text-dark text-truncate shadow-sm" style="background-color: #38bdf8 !important; font-size: 1.05rem; letter-spacing: 0.3px;">
                                PILIH BARANG
                            </div>

                            <!-- Area Foto (Placeholder Foto Tidak Ada) -->
                            <div id="lookup-preview-box" class="d-flex flex-column align-items-center justify-content-center p-3 border-bottom" style="height: 180px; background-color: #ffedd5;">
                                <i class="bi bi-image text-muted opacity-50 fs-1 mb-1"></i>
                                <span class="fw-bold text-dark fs-5" id="lookup-preview-foto-text">Foto Tidak Ada</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Belum ada gambar katalog produk</small>
                            </div>

                            <!-- Detail Spesifikasi Barang -->
                            <div class="p-3 bg-light flex-grow-1 small d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted">Kode Barang:</span>
                                        <strong class="text-dark font-monospace" id="lookup-detail-kode">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted">Kode Barcode:</span>
                                        <strong class="text-dark font-monospace" id="lookup-detail-barcode">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted">Kategori:</span>
                                        <strong class="text-dark" id="lookup-detail-kategori">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted">Kemasan / Satuan:</span>
                                        <span class="badge bg-secondary-subtle text-secondary" id="lookup-detail-satuan">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted" id="lookup-detail-lbl-harga">Harga Jual:</span>
                                        <strong class="text-primary fs-6 font-monospace" id="lookup-detail-harga">Rp 0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Status Produk:</span>
                                        <span class="badge bg-light text-dark border" id="lookup-detail-status">-</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-top">
                                    <button type="button" class="btn btn-primary w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-lookup-preview-select">
                                        <i class="bi bi-check2-circle fs-5"></i> Pilih Barang Ini [Enter]
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal Sesuai Screenshot VB6 -->
            <div class="modal-footer py-2 px-3 bg-white border-top d-flex justify-content-between align-items-center">
                <!-- Record Count Info di Kiri -->
                <div class="fw-bold text-primary font-monospace small d-flex align-items-center gap-2" id="lookup-footer-records">
                    <i class="bi bi-binoculars fs-5 text-secondary"></i>
                    <span>LookUp List Of Stock / <span id="lookup-count-number" class="text-dark">0</span> Records</span>
                </div>

                <!-- Tombol Aksi di Kanan -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" id="btn-lookup-main-pilih">
                        <i class="bi bi-check-lg me-1"></i> Pilih
                    </button>
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// OBJEK GLOBAL PENGENDALI LOOKUP LIST OF STOCK (POPUP VB6 STYLE)
const StockLookup = {
    mode: 'penjualan', // 'penjualan', 'pembelian', atau 'retur'
    supplierCode: '',
    supplierName: '',
    items: [],
    selectedItem: null,
    onSelectCallback: null,
    activeAjaxRequest: null,

    // Buka Popup LookUp
    open: function(options) {
        options = options || {};
        this.mode = options.mode || 'penjualan';
        this.supplierCode = options.supplier || '';
        this.supplierName = options.supplierName || '';
        this.onSelectCallback = options.onSelect || null;
        this.selectedItem = null;

        // Atur Judul & Header Kolom Berdasarkan Mode
        if (this.mode === 'penjualan') {
            $('#lookup-modal-title').text('List of Stock (LookUp Penjualan POS)');
            $('#lookup-modal-subtitle').text('Menampilkan barang aktif dengan stok > 0 untuk transaksi kasir');
            $('#lookup-badge-mode').text('MODE PENJUALAN').removeClass('bg-warning bg-danger text-white text-dark').addClass('bg-white text-primary');
            $('#lookup-th-col-harga').text('Harga Jual');
            $('#lookup-detail-lbl-harga').text('Harga Jual:');
            $('#lookup-th-col-status').hide(); // Di penjualan semua otomatis Aktif
        } else if (this.mode === 'retur') {
            const suppLabel = this.supplierName ? (this.supplierName + ' [' + this.supplierCode + ']') : (this.supplierCode || 'Supplier Terpilih');
            $('#lookup-modal-title').text('List of Stock (LookUp Retur: ' + suppLabel + ')');
            $('#lookup-modal-subtitle').text('Menampilkan produk yang disuplai atau pernah dibeli dari ' + suppLabel);
            $('#lookup-badge-mode').text('MODE RETUR').removeClass('bg-white bg-warning text-primary text-dark').addClass('bg-danger text-white');
            $('#lookup-th-col-harga').text('Harga Modal');
            $('#lookup-detail-lbl-harga').text('Harga Modal (Beli):');
            $('#lookup-th-col-status').show();
        } else {
            const suppLabel = this.supplierName ? (this.supplierName + ' [' + this.supplierCode + ']') : (this.supplierCode || 'Supplier Terpilih');
            $('#lookup-modal-title').text('List of Stock (LookUp Pembelian: ' + suppLabel + ')');
            $('#lookup-modal-subtitle').text('Menampilkan seluruh barang supplier terpilih (status aktif maupun tidak aktif)');
            $('#lookup-badge-mode').text('MODE PEMBELIAN').removeClass('bg-white bg-danger text-primary text-white').addClass('bg-warning text-dark');
            $('#lookup-th-col-harga').text('Harga Modal');
            $('#lookup-detail-lbl-harga').text('Harga Modal (Beli):');
            $('#lookup-th-col-status').show(); // Di pembelian tampilkan status Aktif / Nonaktif
        }

        // Reset input pencarian
        $('#lookup-search-keyword').val('');
        $('#lookup-filter-by').val('nama');
        if (typeof lastSearchKeyword !== 'undefined') {
            lastSearchKeyword = '';
        }
        this.resetPreview();

        // Tampilkan Modal
        $('#modalLookupStock').modal('show');

        // Ambil Data dari Server
        this.fetchData(false);

        // Pastikan kursor langsung fokus dan terpilih di textbox pencarian
        const focusInput = () => {
            const input = document.getElementById('lookup-search-keyword');
            if (input) {
                input.focus();
                input.select();
            }
        };
        setTimeout(focusInput, 50);
        setTimeout(focusInput, 150);
        setTimeout(focusInput, 300);
    },

    // Request Data dari Endpoint /barang/lookup
    fetchData: function(isLiveSearch) {
        const self = this;
        const q = $('#lookup-search-keyword').val().trim();
        const filterBy = $('#lookup-filter-by').val();

        if (self.activeAjaxRequest) {
            self.activeAjaxRequest.abort();
        }

        if (!isLiveSearch) {
            $('#lookup-items-tbody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div>Memuat data list of stock...</div>
                    </td>
                </tr>
            `);
        }

        self.activeAjaxRequest = $.ajax({
            url: '<?= base_url('barang/lookup') ?>',
            type: 'GET',
            data: {
                tipe: self.mode,
                supplier: self.supplierCode,
                q: q,
                filter_by: filterBy
            },
            dataType: 'json',
            success: function(res) {
                self.activeAjaxRequest = null;
                if (res.status === 'success') {
                    self.items = res.data || [];
                    self.renderTable(self.items);
                } else {
                    $('#lookup-items-tbody').html(`<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat data</td></tr>`);
                }
            },
            error: function(err) {
                if (err.statusText === 'abort') return;
                self.activeAjaxRequest = null;
                console.error('Error lookup:', err);
                $('#lookup-items-tbody').html(`<tr><td colspan="7" class="text-center py-4 text-danger">Terjadi kesalahan koneksi server</td></tr>`);
            }
        });
    },

    // Render Tabel Hasil Pencarian
    renderTable: function(items) {
        const self = this;
        const $tbody = $('#lookup-items-tbody');
        $tbody.empty();

        $('#lookup-count-number').text(items.length);

        if (!items || items.length === 0) {
            let emptyMsg = 'Tidak ada barang yang cocok dengan kriteria pencarian.';
            let actionBtn = '';
            if (self.supplierCode) {
                const suppTxt = self.supplierName ? (self.supplierName + ' (' + self.supplierCode + ')') : self.supplierCode;
                emptyMsg = 'Belum ada produk yang berelasi dengan supplier <strong>' + suppTxt + '</strong>.';
                actionBtn = '<div class="mt-2"><button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btn-lookup-show-all-stock"><i class="bi bi-box-seam me-1"></i>Tampilkan Semua Barang Toko</button></div>';
            }
            $tbody.html(`
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 text-warning d-block mb-1"></i>
                        <div>${emptyMsg}</div>
                        ${actionBtn}
                    </td>
                </tr>
            `);
            self.resetPreview();
            return;
        }

        items.forEach((item, index) => {
            const harga = (self.mode === 'penjualan') ? parseFloat(item.harga_jual || 0) : parseFloat(item.harga_beli || 0);
            const statusClass = item.status === 'Aktif' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';

            const statusCol = (self.mode !== 'penjualan') 
                ? `<td class="text-center"><span class="badge ${statusClass} small">${item.status}</span></td>`
                : '';

            const itemImg = item.gambar || item.path_file;
            const fotoThumbnail = itemImg 
                ? `<img src="<?= base_url('uploads/barang/') ?>/${encodeURIComponent(itemImg)}" class="rounded border shadow-xs" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none'">` 
                : `<div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.75rem;"><i class="bi bi-image"></i></div>`;

            const $tr = $(`
                <tr class="lookup-item-row" data-index="${index}" style="cursor: pointer;">
                    <td class="fw-bold text-dark text-nowrap">${item.id_barang}</td>
                    <td class="fw-semibold text-dark text-truncate" style="max-width: 300px;" title="${item.nama_barang}">
                        <div class="d-flex align-items-center gap-2">
                            ${fotoThumbnail}
                            <span class="text-truncate">${item.nama_barang}</span>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border">${item.nama_kategori || 'Umum'}</span></td>
                    <td><span class="badge bg-secondary-subtle text-secondary">${item.satuan || '-'}</span></td>
                    <td class="text-end fw-bold text-primary text-nowrap">Rp ${new Intl.NumberFormat('id-ID').format(harga)}</td>
                    ${statusCol}
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-choose-row py-0 px-2 fw-semibold" title="Pilih Barang">
                            Pilih
                        </button>
                    </td>
                </tr>
            `);

            $tr.data('item', item);
            $tbody.append($tr);
        });

        // Pilih baris pertama secara default
        const $firstRow = $tbody.find('tr.lookup-item-row').first();
        if ($firstRow.length) {
            self.selectRow(items[0], $firstRow);
        }
    },

    // Sorot Baris Terpilih & Update Box Preview Kanan
    selectRow: function(item, $row) {
        this.selectedItem = item;
        $('#table-lookup-items tr.lookup-item-row').removeClass('table-primary active border-primary');
        $row.addClass('table-primary active');

        // Update Box Preview
        $('#lookup-preview-nama').text(item.nama_barang || 'PILIH BARANG');
        $('#lookup-detail-kode').text(item.id_barang || '-');
        $('#lookup-detail-barcode').text(item.barcode || '-');
        $('#lookup-detail-kategori').text(item.nama_kategori || 'Umum');
        $('#lookup-detail-satuan').text(item.satuan || '-');

        // Update Foto Box Preview
        const itemImg = item.gambar || item.path_file;
        if (itemImg) {
            const imgUrl = '<?= base_url('uploads/barang/') ?>/' + encodeURIComponent(itemImg);
            $('#lookup-preview-box').html(`
                <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center p-1">
                    <img src="${imgUrl}" alt="${item.nama_barang}" class="img-fluid rounded shadow-sm" style="max-height: 165px; max-width: 100%; object-fit: contain; cursor: zoom-in;" onclick="window.open('${imgUrl}', '_blank')" title="Klik untuk perbesar foto produk" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'d-flex flex-column align-items-center justify-content-center text-muted\\'><i class=\\'bi bi-image fs-1 mb-1\\'></i><span class=\\'fw-bold text-dark fs-6\\'>Foto Tidak Ada</span></div>';">
                </div>
            `).css('background-color', '#f8fafc');
        } else {
            $('#lookup-preview-box').html(`
                <i class="bi bi-image text-muted opacity-50 fs-1 mb-1"></i>
                <span class="fw-bold text-dark fs-5" id="lookup-preview-foto-text">Foto Tidak Ada</span>
                <small class="text-muted" style="font-size: 0.72rem;">Belum ada gambar katalog produk</small>
            `).css('background-color', '#ffedd5');
        }

        const harga = (this.mode === 'penjualan') ? parseFloat(item.harga_jual || 0) : parseFloat(item.harga_beli || 0);
        $('#lookup-detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(harga));

        if (item.status === 'Aktif') {
            $('#lookup-detail-status').text('Aktif').removeClass().addClass('badge bg-success-subtle text-success border border-success-subtle');
        } else {
            $('#lookup-detail-status').text('Nonaktif').removeClass().addClass('badge bg-danger-subtle text-danger border border-danger-subtle');
        }
    },

    // Reset Box Preview
    resetPreview: function() {
        this.selectedItem = null;
        $('#lookup-preview-nama').text('PILIH BARANG');
        $('#lookup-preview-box').html(`
            <i class="bi bi-image text-muted opacity-50 fs-1 mb-1"></i>
            <span class="fw-bold text-dark fs-5" id="lookup-preview-foto-text">Foto Tidak Ada</span>
            <small class="text-muted" style="font-size: 0.72rem;">Belum ada gambar katalog produk</small>
        `).css('background-color', '#ffedd5');
        $('#lookup-detail-kode').text('-');
        $('#lookup-detail-barcode').text('-');
        $('#lookup-detail-kategori').text('-');
        $('#lookup-detail-satuan').text('-');
        $('#lookup-detail-harga').text('Rp 0');
        $('#lookup-detail-status').text('-');
    },

    // Konfirmasi Pemilihan Barang
    confirmSelection: function() {
        if (!this.selectedItem) {
            Swal.fire('Pilih Barang', 'Silakan klik dan pilih salah satu barang terlebih dahulu dari daftar.', 'info');
            return;
        }

        const selected = Object.assign({}, this.selectedItem);
        $('#modalLookupStock').modal('hide');

        if (typeof this.onSelectCallback === 'function') {
            this.onSelectCallback(selected);
        }
    }
};

$(document).ready(function() {
    let lookupSearchTimer = null;
    let lastSearchKeyword = '';

    // A. Otomatis fokus dan pilih teks ke input pencarian saat modal LookUp terbuka
    $('#modalLookupStock').on('shown.bs.modal', function() {
        const $input = $('#lookup-search-keyword');
        $input.trigger('focus').select();
        setTimeout(() => $input.trigger('focus').select(), 50);
        setTimeout(() => $input.trigger('focus').select(), 150);
    });

    // B. Realtime Live Search: Ketik 1 huruf atau hapus (backspace) satu-satu langsung proses mencari & memfilter grid
    $('#lookup-search-keyword').on('input', function() {
        const val = $(this).val();
        if (val === lastSearchKeyword) return;
        lastSearchKeyword = val;
        clearTimeout(lookupSearchTimer);
        lookupSearchTimer = setTimeout(function() {
            StockLookup.fetchData(true);
        }, 100);
    });

    // C. Navigasi Keyboard di Kotak Pencarian: Panah Atas / Bawah untuk pilih baris, Enter untuk konfirmasi
    $('#lookup-search-keyword').on('keydown', function(e) {
        const $rows = $('#table-lookup-items tr.lookup-item-row');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!$rows.length) return;
            const $active = $rows.filter('.active');
            let $target;
            if (!$active.length) {
                $target = $rows.first();
            } else {
                const nextIndex = $rows.index($active) + 1;
                $target = (nextIndex < $rows.length) ? $rows.eq(nextIndex) : $rows.last();
            }
            if ($target.length) {
                StockLookup.selectRow($target.data('item'), $target);
                $target[0].scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!$rows.length) return;
            const $active = $rows.filter('.active');
            let $target;
            if (!$active.length) {
                $target = $rows.last();
            } else {
                const prevIndex = $rows.index($active) - 1;
                $target = (prevIndex >= 0) ? $rows.eq(prevIndex) : $rows.first();
            }
            if ($target.length) {
                StockLookup.selectRow($target.data('item'), $target);
                $target[0].scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(lookupSearchTimer);

            // Jika ada baris aktif terpilih, langsung pilih dan tutup modal
            if (StockLookup.selectedItem) {
                StockLookup.confirmSelection();
            } else if ($rows.length) {
                const $first = $rows.first();
                StockLookup.selectRow($first.data('item'), $first);
                StockLookup.confirmSelection();
            }
        }
    });

    // 1. Klik pada baris tabel
    $(document).on('click', '.lookup-item-row', function() {
        const item = $(this).data('item');
        StockLookup.selectRow(item, $(this));
    });

    // 2. Dobel klik baris tabel langsung konfirmasi pilihan
    $(document).on('dblclick', '.lookup-item-row', function() {
        const item = $(this).data('item');
        StockLookup.selectRow(item, $(this));
        StockLookup.confirmSelection();
    });

    // 3. Klik tombol "Pilih" pada baris
    $(document).on('click', '.btn-choose-row', function(e) {
        e.stopPropagation();
        const $row = $(this).closest('tr');
        const item = $row.data('item');
        StockLookup.selectRow(item, $row);
        StockLookup.confirmSelection();
    });

    // 4. Klik tombol "Pilih" utama (Footer dan Box Preview)
    $('#btn-lookup-main-pilih, #btn-lookup-preview-select').on('click', function() {
        StockLookup.confirmSelection();
    });

    // 5. Tombol Cari Manual & Refresh
    $('#btn-lookup-do-search, #btn-lookup-refresh-data').on('click', function() {
        StockLookup.fetchData(false);
    });

    $(document).on('click', '#btn-lookup-show-all-stock', function() {
        StockLookup.supplierCode = '';
        $('#lookup-modal-subtitle').text('Menampilkan seluruh produk di toko');
        StockLookup.fetchData(false);
    });

    $('#lookup-filter-by').on('change', function() {
        StockLookup.fetchData(false);
    });

    // 6. Navigasi Keyboard global di dalam Modal LookUp (Arrow Up/Down & Enter)
    $('#modalLookupStock').on('keydown', function(e) {
        if ($(e.target).is('#lookup-search-keyword')) {
            return; // sudah ditangani di handler search input
        }

        const $rows = $('#table-lookup-items tr.lookup-item-row');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!$rows.length) return;
            const $active = $rows.filter('.active');
            let $target;
            if (!$active.length) {
                $target = $rows.first();
            } else {
                const nextIndex = $rows.index($active) + 1;
                $target = (nextIndex < $rows.length) ? $rows.eq(nextIndex) : $rows.last();
            }
            if ($target.length) {
                StockLookup.selectRow($target.data('item'), $target);
                $target[0].scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!$rows.length) return;
            const $active = $rows.filter('.active');
            let $target;
            if (!$active.length) {
                $target = $rows.last();
            } else {
                const prevIndex = $rows.index($active) - 1;
                $target = (prevIndex >= 0) ? $rows.eq(prevIndex) : $rows.first();
            }
            if ($target.length) {
                StockLookup.selectRow($target.data('item'), $target);
                $target[0].scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            StockLookup.confirmSelection();
        }
    });
});
</script>
