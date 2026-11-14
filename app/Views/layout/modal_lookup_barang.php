<!-- MODAL LOOKUP LIST OF STOCK (POPUP PENCARIAN & PEMILIHAN BARANG) -->
<div class="modal fade" id="modalLookupStock" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1140px;">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <!-- Header Modal -->
            <div class="modal-header py-2 px-3 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%); border-bottom: 2px solid #0284c7;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white p-2 rounded-3 shadow-sm">
                        <i class="bi bi-card-checklist fs-6"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="lookup-modal-title">List of Stock</h6>
                        <small class="text-white-50" style="font-size: 0.72rem;" id="lookup-modal-subtitle">View and manage Item Stocks</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-3 py-1" id="lookup-badge-mode">MODE PENJUALAN</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 bg-light">
                <!-- Baris Filter & Pencarian Sesuai Screenshot VB6 -->
                <div class="card border p-2 px-3 mb-3 bg-white shadow-sm rounded-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <label class="small fw-bold text-dark text-nowrap mb-0" style="font-size: 0.78rem;">Filter Terlebih Dahulu :</label>
                                <select id="lookup-filter-by" class="form-select form-select-sm fw-semibold" style="min-width: 140px;">
                                    <option value="nama" selected>NAMA BARANG</option>
                                    <option value="kode">ID BARANG</option>
                                    <option value="barcode">KODE BARCODE</option>
                                    <option value="kategori">KATEGORI</option>
                                    <option value="all">SEMUA KOLOM</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-5">
                            <div class="d-flex align-items-center gap-2">
                                <label class="small fw-bold text-dark text-nowrap mb-0" style="font-size: 0.78rem;">Kata Cari :</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="lookup-search-keyword" class="form-control" placeholder="Ketik kata yang dicari lalu tekan Enter..." autocomplete="off">
                                    <button class="btn btn-success fw-bold px-3" type="button" id="btn-lookup-do-search" title="Mulai Pencarian">
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-lookup-refresh-data" title="Refresh Data">
                                <i class="bi bi-arrow-repeat me-1"></i> Refresh
                            </button>
                            <small class="text-muted d-none d-lg-inline" style="font-size: 0.72rem;">Gunakan <kbd class="bg-light text-dark border">↑</kbd> <kbd class="bg-light text-dark border">↓</kbd> & <kbd class="bg-primary text-white border">Enter</kbd></small>
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
                                    <thead class="table-dark sticky-top small text-uppercase" style="z-index: 5;">
                                        <tr>
                                            <th style="width: 105px;">ID Barang</th>
                                            <th>Nama Barang</th>
                                            <th style="width: 120px;">Kategori</th>
                                            <th style="width: 75px;">Satuan</th>
                                            <th class="text-end" style="width: 110px;" id="lookup-th-col-harga">Harga Jual</th>
                                            <th class="text-center" style="width: 75px;">Stok</th>
                                            <th class="text-center" style="width: 80px;" id="lookup-th-col-status">Status</th>
                                            <th class="text-center" style="width: 65px;">Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lookup-items-tbody">
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                                <div>Memuat data stok barang...</div>
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
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                        <span class="text-muted">Stok Saat Ini:</span>
                                        <span class="badge bg-success fs-6" id="lookup-detail-stok">0</span>
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
    mode: 'penjualan', // 'penjualan' atau 'pembelian'
    supplierCode: '',
    items: [],
    selectedItem: null,
    onSelectCallback: null,

    // Buka Popup LookUp
    open: function(options) {
        options = options || {};
        this.mode = options.mode || 'penjualan';
        this.supplierCode = options.supplier || '';
        this.onSelectCallback = options.onSelect || null;
        this.selectedItem = null;

        // Atur Judul & Header Kolom Berdasarkan Mode
        if (this.mode === 'penjualan') {
            $('#lookup-modal-title').text('List of Stock (LookUp Penjualan POS)');
            $('#lookup-modal-subtitle').text('Menampilkan barang aktif dengan stok > 0 untuk transaksi kasir');
            $('#lookup-badge-mode').text('MODE PENJUALAN').removeClass('bg-warning-subtle text-warning').addClass('bg-info-subtle text-info');
            $('#lookup-th-col-harga').text('Harga Jual');
            $('#lookup-detail-lbl-harga').text('Harga Jual:');
            $('#lookup-th-col-status').hide(); // Di penjualan semua otomatis Aktif
        } else {
            $('#lookup-modal-title').text('List of Stock (LookUp Pembelian Supplier)');
            $('#lookup-modal-subtitle').text('Menampilkan seluruh barang supplier terpilih (status aktif maupun tidak aktif)');
            $('#lookup-badge-mode').text('MODE PEMBELIAN').removeClass('bg-info-subtle text-info').addClass('bg-warning-subtle text-warning');
            $('#lookup-th-col-harga').text('Harga Modal');
            $('#lookup-detail-lbl-harga').text('Harga Modal (Beli):');
            $('#lookup-th-col-status').show(); // Di pembelian tampilkan status Aktif / Nonaktif
        }

        // Reset input pencarian
        $('#lookup-search-keyword').val('');
        $('#lookup-filter-by').val('nama');
        this.resetPreview();

        // Tampilkan Modal
        $('#modalLookupStock').modal('show');

        // Ambil Data dari Server
        this.fetchData();

        setTimeout(() => {
            $('#lookup-search-keyword').focus().select();
        }, 400);
    },

    // Request Data dari Endpoint /barang/lookup
    fetchData: function() {
        const self = this;
        const q = $('#lookup-search-keyword').val().trim();
        const filterBy = $('#lookup-filter-by').val();

        $('#lookup-items-tbody').html(`
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <div>Memuat data list of stock...</div>
                </td>
            </tr>
        `);

        $.ajax({
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
                if (res.status === 'success') {
                    self.items = res.data || [];
                    self.renderTable(self.items);
                } else {
                    $('#lookup-items-tbody').html(`<tr><td colspan="8" class="text-center py-4 text-danger">Gagal memuat data</td></tr>`);
                }
            },
            error: function(err) {
                console.error('Error lookup:', err);
                $('#lookup-items-tbody').html(`<tr><td colspan="8" class="text-center py-4 text-danger">Terjadi kesalahan koneksi server</td></tr>`);
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
            $tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 opacity-50 d-block mb-1"></i>
                        Tidak ada barang yang cocok dengan filter kriteria pencarian.
                    </td>
                </tr>
            `);
            self.resetPreview();
            return;
        }

        items.forEach((item, index) => {
            const harga = (self.mode === 'penjualan') ? parseFloat(item.harga_jual || 0) : parseFloat(item.harga_beli || 0);
            const stokVal = (self.mode === 'penjualan') ? parseFloat(item.stok_cabang || item.stok || 0) : parseFloat(item.stok || 0);
            const statusClass = item.status === 'Aktif' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';

            const statusCol = (self.mode === 'pembelian') 
                ? `<td class="text-center"><span class="badge ${statusClass} small">${item.status}</span></td>`
                : '';

            const $tr = $(`
                <tr class="lookup-item-row" data-index="${index}" style="cursor: pointer;">
                    <td class="fw-bold text-dark text-nowrap">${item.id_barang}</td>
                    <td class="fw-semibold text-dark text-truncate" style="max-width: 240px;" title="${item.nama_barang}">
                        ${item.nama_barang}
                    </td>
                    <td><span class="badge bg-light text-dark border">${item.nama_kategori || 'Umum'}</span></td>
                    <td><span class="badge bg-secondary-subtle text-secondary">${item.satuan || '-'}</span></td>
                    <td class="text-end fw-bold text-primary text-nowrap">Rp ${new Intl.NumberFormat('id-ID').format(harga)}</td>
                    <td class="text-center fw-bold ${stokVal <= 5 ? 'text-danger' : 'text-success'}">${new Intl.NumberFormat('id-ID').format(stokVal)}</td>
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

        const harga = (this.mode === 'penjualan') ? parseFloat(item.harga_jual || 0) : parseFloat(item.harga_beli || 0);
        $('#lookup-detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(harga));

        const stokVal = (this.mode === 'penjualan') ? parseFloat(item.stok_cabang || item.stok || 0) : parseFloat(item.stok || 0);
        $('#lookup-detail-stok').text(new Intl.NumberFormat('id-ID').format(stokVal) + ' ' + (item.satuan || ''));

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
        $('#lookup-detail-kode').text('-');
        $('#lookup-detail-barcode').text('-');
        $('#lookup-detail-kategori').text('-');
        $('#lookup-detail-satuan').text('-');
        $('#lookup-detail-harga').text('Rp 0');
        $('#lookup-detail-stok').text('0');
        $('#lookup-detail-status').text('-');
    },

    // Konfirmasi Pemilihan Barang
    confirmSelection: function() {
        if (!this.selectedItem) {
            Swal.fire('Pilih Barang', 'Silakan klik dan pilih salah satu barang terlebih dahulu dari daftar.', 'info');
            return;
        }

        if (typeof this.onSelectCallback === 'function') {
            this.onSelectCallback(this.selectedItem);
        }

        $('#modalLookupStock').modal('hide');
    }
};

$(document).ready(function() {
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

    // 5. Input pencarian (Enter atau Klik Tombol Cari)
    $('#lookup-search-keyword').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            StockLookup.fetchData();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const $active = $('#table-lookup-items tr.lookup-item-row.active');
            const $next = $active.length ? $active.next('tr.lookup-item-row') : $('#table-lookup-items tr.lookup-item-row').first();
            if ($next.length) {
                StockLookup.selectRow($next.data('item'), $next);
                $next[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const $active = $('#table-lookup-items tr.lookup-item-row.active');
            const $prev = $active.length ? $active.prev('tr.lookup-item-row') : $('#table-lookup-items tr.lookup-item-row').last();
            if ($prev.length) {
                StockLookup.selectRow($prev.data('item'), $prev);
                $prev[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
    });

    $('#btn-lookup-do-search, #btn-lookup-refresh-data').on('click', function() {
        StockLookup.fetchData();
    });

    $('#lookup-filter-by').on('change', function() {
        StockLookup.fetchData();
    });

    // Navigasi Keyboard global di dalam Modal LookUp (Arrow Up/Down & Enter)
    $('#modalLookupStock').on('keydown', function(e) {
        if (e.key === 'Enter' && !$(e.target).is('#lookup-search-keyword')) {
            e.preventDefault();
            StockLookup.confirmSelection();
        }
    });
});
</script>
