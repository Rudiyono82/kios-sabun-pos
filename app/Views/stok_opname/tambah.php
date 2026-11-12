<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<style>
/* Optimasi kinerja render tabel STO */
#table-input-sto tbody tr {
    content-visibility: auto;
}
.page-btn {
    min-width: 38px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
}
.btn-status-filter.active {
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clipboard2-check-fill text-success me-2"></i>Sesi Audit Stok Opname (Blind Count)</h4>
        <p class="text-muted small mb-0">Metode Blind Count mencegah kecurangan: Petugas menghitung fisik secara riil di rak toko tanpa melihat sisa stok data komputer.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('stok-opname') ?>" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Batal
        </a>
        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold" id="btn-submit-sto">
            <i class="bi bi-check2-circle me-1"></i> Simpan Hasil Audit STO
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2 px-3 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?= base_url('stok-opname/simpan') ?>" method="POST" id="form-sto">
    <?= csrf_field() ?>

    <!-- Header Dokumen STO -->
    <div class="card-modern p-3 mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">No. Dokumen STO</label>
                <input type="text" name="no_sto" class="form-control font-monospace fw-bold bg-light" value="<?= esc($no_sto) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Petugas Audit</label>
                <input type="text" class="form-control bg-light fw-semibold" value="<?= esc(session()->get('nama_user') ?? 'Petugas STO') ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Keterangan / Tujuan Audit</label>
                <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Audit fisik berkala rak sabun / cek stok mingguan..." autofocus>
            </div>
        </div>
    </div>

    <!-- Alert Keamanan Blind Count & Mode Owner -->
    <div class="alert alert-info py-2 px-3 rounded-3 small d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 border-0 shadow-sm">
        <div>
            <i class="bi bi-shield-lock-fill text-primary me-2 fs-6"></i>
            <strong>Mode Blind Count Aktif:</strong> Stok sistem komputer dirahasiakan selama penghitungan fisik untuk menjamin akurasi dan mencegah kecurangan karyawan.
        </div>
        <?php if ($is_admin): ?>
        <div>
            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" id="btn-toggle-owner-mode">
                <i class="bi bi-eye-fill me-1"></i> Mode Owner: Intip Stok Komputer
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panel Filter, Paging & Pencarian Cepat Barang -->
    <div class="card-modern p-4">
        <!-- Baris Filter Cepat -->
        <div class="row g-3 align-items-center mb-3">
            <!-- Search Box -->
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="filter-search-barang" class="form-control border-start-0 ps-0" placeholder="Cari nama sabun / parfum / barcode / kode...">
                </div>
            </div>

            <!-- Filter Kategori -->
            <div class="col-12 col-md-3">
                <select id="filter-kategori-barang" class="form-select no-select2">
                    <option value="">Semua Kategori Produk</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= esc($kat['nama_kategori']) ?>"><?= esc($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Pengerjaan (Pills) -->
            <div class="col-12 col-md-5 d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-status-filter active" data-status="semua">
                        Semua (<span id="total-semua-badge"><?= count($barang_list) ?></span>)
                    </button>
                    <button type="button" class="btn btn-outline-success btn-status-filter" data-status="diisi">
                        Sudah Diisi (<span id="total-diisi-badge">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-status-filter" data-status="belum">
                        Belum Diisi (<span id="total-belum-badge"><?= count($barang_list) ?></span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Baris Kontrol Paging Atas -->
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-2 mb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Tampilkan:</span>
                <select id="page-size-select" class="form-select form-select-sm no-select2" style="width: 100px;">
                    <option value="15">15 data</option>
                    <option value="25" selected>25 data</option>
                    <option value="50">50 data</option>
                    <option value="100">100 data</option>
                    <option value="all">Semua</option>
                </select>
                <span class="small text-muted ms-2" id="info-halaman-atas">Memuat data...</span>
            </div>
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-1 fw-semibold">
                    <i class="bi bi-pencil-fill me-1"></i> <span id="counter-terisi">0</span> / <?= count($barang_list) ?> Terhitung
                </span>
            </div>
        </div>

        <!-- Tabel Daftar Barang untuk Dihitung Fisik -->
        <div class="table-responsive" style="min-height: 380px;">
            <table class="table table-hover align-middle mb-0 no-truncate" id="table-input-sto">
                <thead class="table-dark small text-uppercase">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Nama Produk Sabun &amp; Parfum</th>
                        <th class="text-center" style="width: 140px;">Kategori</th>
                        <th class="text-center" style="width: 110px;">Satuan</th>
                        
                        <!-- Kolom Stok Komputer (Hanya Tampil Jika Mode Owner Diaktifkan) -->
                        <th class="text-center col-stok-sistem d-none" style="width: 140px; background-color: #1e293b;">
                            Stok Sistem (Owner)
                        </th>
                        
                        <th class="text-center" style="width: 200px; background-color: #0d6efd; color: #ffffff;">
                            <i class="bi bi-pencil-square me-1"></i> Hitungan Fisik Riil <span class="text-warning">*</span>
                        </th>
                        <th style="width: 220px;">Catatan Fisik (Kondisi/Bocor)</th>
                    </tr>
                </thead>
                <tbody id="sto-tbody">
                    <?php foreach ($barang_list as $idx => $b): ?>
                        <tr class="row-sto-item" 
                            data-nama="<?= strtolower(esc($b['nama_barang'] . ' ' . $b['id_barang'] . ' ' . ($b['barcode'] ?? ''))) ?>" 
                            data-kategori="<?= esc($b['nama_kategori'] ?? '') ?>"
                            data-filled="0"
                            style="display: none;">
                            <td class="text-center text-muted small fw-semibold cell-row-number"><?= $idx + 1 ?></td>
                            <td>
                                <input type="hidden" name="id_barang[]" value="<?= esc($b['id_barang']) ?>">
                                <div class="fw-bold text-dark fs-6"><?= esc($b['nama_barang']) ?></div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-secondary border font-monospace"><?= esc($b['id_barang']) ?></span>
                                    <?php if (!empty($b['barcode'])): ?>
                                        <small class="text-muted"><i class="bi bi-upc me-1"></i><?= esc($b['barcode']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center small">
                                <span class="badge bg-info bg-opacity-10 text-info fw-semibold"><?= esc($b['nama_kategori'] ?: 'Umum') ?></span>
                            </td>
                            <td class="text-center text-muted fw-bold small"><?= esc($b['satuan']) ?></td>
                            
                            <!-- Kolom Stok Sistem (Tersembunyi untuk Cegah Kecurangan) -->
                            <td class="text-center font-monospace fw-bold fs-6 text-warning col-stok-sistem d-none" style="background-color: #f8fafc;">
                                <?= number_format($b['stok'], 2, ',', '.') ?>
                            </td>

                            <!-- Input Stok Fisik (Default KOSONG, Wajib Dihitung Fisik) -->
                            <td class="text-center p-2" style="background-color: #f0f7ff;">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="stok_fisik[]" class="form-control text-center fw-bold fs-6 input-stok-fisik border-primary" 
                                        placeholder="Ketik hitungan..." step="any" min="0" autocomplete="off">
                                    <span class="input-group-text bg-white text-muted small"><?= esc($b['satuan']) ?></span>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="catatan_item[]" class="form-control form-control-sm" placeholder="Contoh: Kemasan rembes, segel utuh...">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pesan jika tidak ada hasil pencarian -->
            <div id="no-result-msg" class="text-center py-5 text-muted d-none">
                <i class="bi bi-search fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <h6 class="fw-bold">Tidak ada produk sabun/parfum yang cocok</h6>
                <small>Coba ubah kata kunci pencarian atau ganti filter kategori produk.</small>
            </div>
        </div>

        <!-- Baris Kontrol Paging Bawah -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 pt-3 mt-2 border-top">
            <div class="small text-muted" id="info-halaman-bawah">
                Menampilkan data...
            </div>
            <div class="d-flex align-items-center gap-1" id="pagination-controls">
                <!-- Tombol Pagination di-render secara dinamis oleh JavaScript -->
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Kumpulan State Paging & Filter
    let allRows = Array.from(document.querySelectorAll('.row-sto-item'));
    let matchedRows = [...allRows];
    let currentPage = 1;
    let pageSize = 25;
    let currentStatusFilter = 'semua'; // 'semua', 'diisi', 'belum'
    let isOwnerMode = false;

    // 1. Fungsi Utama Render Halaman (Ringan & Cepat < 5ms)
    function renderPage() {
        const totalItems = matchedRows.length;

        if (totalItems === 0) {
            allRows.forEach(r => r.style.display = 'none');
            $('#no-result-msg').removeClass('d-none');
            $('#info-halaman-atas, #info-halaman-bawah').text('0 dari 0 produk');
            $('#pagination-controls').empty();
            return;
        }

        $('#no-result-msg').addClass('d-none');

        const totalPages = pageSize === 'all' ? 1 : Math.ceil(totalItems / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = pageSize === 'all' ? 0 : (currentPage - 1) * pageSize;
        const endIndex = pageSize === 'all' ? totalItems : Math.min(startIndex + parseInt(pageSize), totalItems);

        // Sembunyikan semua row, lalu tampilkan hanya slice halaman aktif
        allRows.forEach(r => r.style.display = 'none');
        for (let i = startIndex; i < endIndex; i++) {
            matchedRows[i].style.display = '';
        }

        // Update info teks
        const infoText = `Menampilkan <strong>${startIndex + 1} - ${endIndex}</strong> dari <strong>${totalItems}</strong> produk`;
        $('#info-halaman-atas, #info-halaman-bawah').html(infoText);

        // Render Tombol Pagination
        renderPaginationButtons(totalPages);
    }

    // 2. Render Tombol Navigasi Pagination
    function renderPaginationButtons(totalPages) {
        const $container = $('#pagination-controls');
        $container.empty();

        if (totalPages <= 1) return;

        // Tombol First & Prev
        const $firstBtn = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn ${currentPage === 1 ? 'disabled' : ''}" title="Halaman Pertama"><i class="bi bi-chevron-double-left"></i></button>`);
        $firstBtn.on('click', function() { if (currentPage > 1) { currentPage = 1; renderPage(); } });
        $container.append($firstBtn);

        const $prevBtn = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn ${currentPage === 1 ? 'disabled' : ''}" title="Sebelumnya"><i class="bi bi-chevron-left"></i></button>`);
        $prevBtn.on('click', function() { if (currentPage > 1) { currentPage--; renderPage(); } });
        $container.append($prevBtn);

        // Hitung range nomor halaman (maksimal tampil 5 nomor di sekitar currentPage)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            const $p1 = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn">1</button>`);
            $p1.on('click', function() { currentPage = 1; renderPage(); });
            $container.append($p1);
            if (startPage > 2) {
                $container.append($('<span class="px-1 text-muted">...</span>'));
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            const isActive = (p === currentPage);
            const $btn = $(`<button type="button" class="btn btn-sm ${isActive ? 'btn-primary active fw-bold' : 'btn-outline-secondary'} page-btn">${p}</button>`);
            $btn.on('click', (function(pageNumber) {
                return function() {
                    currentPage = pageNumber;
                    renderPage();
                };
            })(p));
            $container.append($btn);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                $container.append($('<span class="px-1 text-muted">...</span>'));
            }
            const $pLast = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn">${totalPages}</button>`);
            $pLast.on('click', function() { currentPage = totalPages; renderPage(); });
            $container.append($pLast);
        }

        // Tombol Next & Last
        const $nextBtn = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn ${currentPage === totalPages ? 'disabled' : ''}" title="Berikutnya"><i class="bi bi-chevron-right"></i></button>`);
        $nextBtn.on('click', function() { if (currentPage < totalPages) { currentPage++; renderPage(); } });
        $container.append($nextBtn);

        const $lastBtn = $(`<button type="button" class="btn btn-sm btn-outline-secondary page-btn ${currentPage === totalPages ? 'disabled' : ''}" title="Halaman Terakhir"><i class="bi bi-chevron-double-right"></i></button>`);
        $lastBtn.on('click', function() { if (currentPage < totalPages) { currentPage = totalPages; renderPage(); } });
        $container.append($lastBtn);
    }

    // 3. Filter Data Cepat (Pencarian, Kategori, Status)
    function applyFiltering() {
        const keyword = $('#filter-search-barang').val().toLowerCase().trim();
        const kategori = $('#filter-kategori-barang').val();

        matchedRows = allRows.filter(row => {
            const rowText = row.getAttribute('data-nama') || '';
            const rowKat = row.getAttribute('data-kategori') || '';
            const isFilled = row.getAttribute('data-filled') === '1';

            const matchKeyword = !keyword || rowText.includes(keyword);
            const matchKategori = !kategori || rowKat === kategori;

            let matchStatus = true;
            if (currentStatusFilter === 'diisi') matchStatus = isFilled;
            if (currentStatusFilter === 'belum') matchStatus = !isFilled;

            return matchKeyword && matchKategori && matchStatus;
        });

        currentPage = 1;
        renderPage();
    }

    // 4. Update Counter Hitungan Fisik
    function updateCounter() {
        let countFilled = 0;
        allRows.forEach(row => {
            const input = row.querySelector('.input-stok-fisik');
            const val = input ? input.value.trim() : '';
            if (val !== '') {
                countFilled++;
                row.setAttribute('data-filled', '1');
                row.classList.add('table-primary', 'bg-opacity-10');
            } else {
                row.setAttribute('data-filled', '0');
                row.classList.remove('table-primary', 'bg-opacity-10');
            }
        });

        const total = allRows.length;
        const countBelum = total - countFilled;

        $('#counter-terisi').text(countFilled);
        $('#total-diisi-badge').text(countFilled);
        $('#total-belum-badge').text(countBelum);
    }

    // Event listener input fisik (delegated event)
    $(document).on('input change', '.input-stok-fisik', function() {
        const val = this.value.trim();
        const row = this.closest('tr');
        if (val !== '') {
            row.setAttribute('data-filled', '1');
            row.classList.add('table-primary', 'bg-opacity-10');
        } else {
            row.setAttribute('data-filled', '0');
            row.classList.remove('table-primary', 'bg-opacity-10');
        }
        updateCounter();
    });

    // 5. Debounce Pencarian untuk Performa Halus
    let searchTimer = null;
    $('#filter-search-barang').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFiltering, 150);
    });

    $('#filter-kategori-barang').on('change', function() {
        applyFiltering();
    });

    // 6. Filter Tombol Status (Semua / Sudah Diisi / Belum Diisi)
    $('.btn-status-filter').on('click', function() {
        $('.btn-status-filter').removeClass('active');
        $(this).addClass('active');
        currentStatusFilter = $(this).data('status');
        applyFiltering();
    });

    // 7. Ganti Page Size
    $('#page-size-select').on('change', function() {
        pageSize = this.value;
        currentPage = 1;
        renderPage();
    });

    // 8. Mode Owner Toggle (Hanya untuk Admin)
    $('#btn-toggle-owner-mode').on('click', function() {
        isOwnerMode = !isOwnerMode;
        const $cols = $('.col-stok-sistem');
        if (isOwnerMode) {
            $cols.removeClass('d-none');
            $(this).html('<i class="bi bi-eye-slash-fill me-1"></i> Mode Owner: Sembunyikan Stok Komputer');
            $(this).removeClass('btn-outline-dark').addClass('btn-warning text-dark fw-bold');
        } else {
            $cols.addClass('d-none');
            $(this).html('<i class="bi bi-eye-fill me-1"></i> Mode Owner: Intip Stok Komputer');
            $(this).removeClass('btn-warning text-dark fw-bold').addClass('btn-outline-dark');
        }
    });

    // 9. Submit STO dengan Konfirmasi Aman
    $('#btn-submit-sto').on('click', function(e) {
        e.preventDefault();
        
        let countFilled = 0;
        allRows.forEach(row => {
            const input = row.querySelector('.input-stok-fisik');
            if (input && input.value.trim() !== '') countFilled++;
        });

        if (countFilled === 0) {
            Swal.fire({
                title: 'Data Masih Kosong',
                text: 'Harap masukkan hasil hitung fisik minimal pada satu barang sebelum menyimpan.',
                icon: 'warning'
            });
            return;
        }

        Swal.fire({
            title: 'Simpan Hasil Audit STO?',
            html: `Sebanyak <strong>${countFilled} produk</strong> telah dihitung fisik.<br><br>Sistem akan otomatis menghitung selisih dan melakukan penyesuaian stok sistem sesuai hitungan fisik di rak toko.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan & Sinkronkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading saat submit
                Swal.fire({
                    title: 'Menyimpan & Menyesuaikan Stok...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $('#form-sto').submit();
            }
        });
    });

    // Inisialisasi awal
    updateCounter();
    renderPage();
});
</script>
<?= $this->endSection() ?>
