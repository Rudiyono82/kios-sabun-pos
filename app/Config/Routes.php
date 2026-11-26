<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes (Login & Logout)
$routes->get('login', 'Auth::login');
$routes->post('auth/process', 'Auth::process');
$routes->get('logout', 'Auth::logout');
$routes->post('auth/simpan-kas-awal', 'Auth::simpanKasAwal', ['filter' => 'auth']);
$routes->get('profile', 'Auth::profile', ['filter' => 'auth']);
$routes->post('profile/update', 'Auth::updateProfile', ['filter' => 'auth']);

// Home & Dashboard
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('dashboard/grafik', 'Dashboard::grafik', ['filter' => 'auth']);
$routes->get('dashboard/live-kasir', 'Dashboard::liveKasir', ['filter' => 'admin']);
$routes->get('monitoring-kasir', 'Dashboard::liveKasir', ['filter' => 'admin']);

// Point of Sales (Kasir)
$routes->group('pos', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Pos::index');
    $routes->get('search', 'Pos::search');
    $routes->get('search-member', 'Pos::searchMember');
    $routes->get('get-member-vouchers/(:any)', 'Pos::getMemberVouchers/$1');
    $routes->post('tukar-poin-member', 'Pos::tukarPoinMember');
    $routes->post('simpan-transaksi', 'Pos::simpanTransaksi');
    $routes->get('cetak-struk/(:any)', 'Pos::cetakStruk/$1');
});

// Master Barang
$routes->group('barang', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Barang::index');
    $routes->get('lookup', 'Barang::lookup');
    $routes->get('generate-kode', 'Barang::generateKode');
    $routes->post('simpan', 'Barang::simpan');
    $routes->match(['get', 'post'], 'approve/(:num)', 'Barang::approve/$1', ['filter' => 'admin']);
    $routes->match(['get', 'post'], 'reject/(:num)', 'Barang::reject/$1', ['filter' => 'admin']);
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Barang::hapus/$1');
    $routes->post('update-stok-cepat', 'Barang::updateStokCepat', ['filter' => 'admin']);
});

// Antrean Pending Update Stok & Harga Barang (VB6 frmListPendingHargaBarang)
$routes->group('pending-stok', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'PendingStok::index');
    $routes->post('approve/(:num)', 'PendingStok::approve/$1', ['filter' => 'admin']);
    $routes->post('approve-with-change/(:num)', 'PendingStok::approveWithChange/$1', ['filter' => 'admin']);
    $routes->post('reject/(:num)', 'PendingStok::reject/$1', ['filter' => 'admin']);
    $routes->get('check-status/(:segment)', 'PendingStok::checkStatus/$1');
    $routes->post('execute-update', 'PendingStok::executeUpdate');
});

// Master Kategori Barang
$routes->group('kategori', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Kategori::index');
    $routes->post('simpan', 'Kategori::simpan');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Kategori::hapus/$1');
});

// Master Satuan Barang
$routes->group('satuan', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Satuan::index');
    $routes->post('simpan', 'Satuan::simpan');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Satuan::hapus/$1');
});

// Master Data Supplier
$routes->group('supplier', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Supplier::index');
    $routes->post('simpan', 'Supplier::simpan');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Supplier::hapus/$1');
});

// Master Cabang & Profil Toko
$routes->group('cabang', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Cabang::index');
    $routes->post('simpan', 'Cabang::simpan', ['filter' => 'admin']);
    $routes->post('update', 'Cabang::simpan', ['filter' => 'admin']);
    $routes->match(['get', 'post'], 'switch/(:num)', 'Cabang::switchCabang/$1');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Cabang::hapus/$1', ['filter' => 'admin']);
});

// Mutasi & Transfer Stok Antar Cabang
$routes->group('transfer-stok', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'TransferStok::index');
    $routes->get('tambah', 'TransferStok::tambah');
    $routes->post('simpan', 'TransferStok::simpan');
    $routes->get('detail/(:num)', 'TransferStok::detail/$1');
    $routes->match(['get', 'post'], 'terima/(:num)', 'TransferStok::terima/$1');
    $routes->get('cetak/(:num)', 'TransferStok::cetak/$1');
});

// Audit Trail & Jejak Aktivitas Keamanan
$routes->get('audit-trail', 'AuditTrail::index', ['filter' => 'admin']);

// Pembelian / Restock
$routes->group('pembelian', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Pembelian::index');
    $routes->get('tambah', 'Pembelian::tambah');
    $routes->get('detail/(:segment)', 'Pembelian::detail/$1');
    $routes->post('simpan', 'Pembelian::simpan');
    $routes->post('update-status-bayar', 'Pembelian::updateStatusBayar');
    $routes->post('update-harga-barang', 'Pembelian::updateHargaBarang');
    $routes->post('hapus/(:segment)', 'Pembelian::hapus/$1', ['filter' => 'admin']);
});

// Retur Barang ke Supplier (Rijek / Expired)
$routes->group('retur', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Retur::index');
    $routes->get('detail/(:num)', 'Retur::detail/$1');
    $routes->post('simpan', 'Retur::simpan');
    $routes->post('selesaikan', 'Retur::selesaikan');
    $routes->post('hapus/(:num)', 'Retur::hapus/$1');
});

// Faktur Penjualan Kasir
$routes->group('penjualan', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Penjualan::index');
    $routes->get('detail/(:segment)', 'Penjualan::detail/$1');
    $routes->post('hapus-item/(:num)', 'Penjualan::hapusItem/$1');
    $routes->post('hapus/(:segment)', 'Penjualan::hapus/$1');
    $routes->post('update-metode-bayar', 'Penjualan::updateMetodeBayar');
});

// Cetak Barcode & Label Rak
$routes->get('barcode', 'Barcode::index', ['filter' => 'auth']);

// Produksi & Racik Sabun Curah (BOM / Resep)
$routes->group('produksi', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Produksi::index');
    $routes->get('resep', 'Produksi::resep');
    $routes->post('simpan-resep', 'Produksi::simpanResep', ['filter' => 'admin']);
    $routes->get('hapus-resep/(:num)', 'Produksi::hapusResep/$1', ['filter' => 'admin']);
    $routes->get('get-resep-detail/(:num)', 'Produksi::getResepDetail/$1');
    $routes->post('proses-produksi', 'Produksi::prosesProduksi');
});

// Buku Kas & Biaya Operasional Toko
$routes->group('keuangan', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Keuangan::index');
    $routes->post('simpan', 'Keuangan::simpan');
    $routes->post('tambah-kategori', 'Keuangan::tambahKategori');
    $routes->post('hapus/(:num)', 'Keuangan::hapus/$1');
});

// Stok Opname (STO) Fisik Toko
$routes->group('stok-opname', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'StokOpname::index');
    $routes->get('tambah', 'StokOpname::tambah');
    $routes->post('simpan', 'StokOpname::simpan');
    $routes->get('detail/(:num)', 'StokOpname::detail/$1');
    $routes->post('batalkan/(:num)', 'StokOpname::batalkan/$1', ['filter' => 'admin']);
});

// Manajemen Karyawan & User Monitoring (Khusus Admin)
$routes->group('karyawan', ['filter' => 'admin'], function($routes) {
    $routes->get('/', 'Karyawan::index');
    $routes->post('simpan', 'Karyawan::simpan');
    $routes->get('toggle-status/(:num)', 'Karyawan::toggleStatus/$1');
});

// Member & Loyalitas
$routes->group('member', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Member::index');
    $routes->post('simpan', 'Member::simpan');
    $routes->post('tukar-poin', 'Member::tukarPoin');
    $routes->get('hapus/(:num)', 'Member::hapus/$1', ['filter' => 'admin']);
});

// Laporan & Audit
$routes->group('laporan', ['filter' => 'auth'], function($routes) {
    $routes->get('harian', 'Laporan::harian');
    $routes->get('cetak-struk-harian', 'Laporan::cetakStrukHarian');
    $routes->get('barang', 'Laporan::barang');
    $routes->get('barang/cetak', 'Laporan::cetakBarang');
    $routes->get('barang/excel', 'Laporan::excelBarang');
    $routes->get('penjualan', 'Laporan::penjualan');
    $routes->get('penjualan/cetak', 'Laporan::cetakPenjualan');
    $routes->get('penjualan/excel', 'Laporan::excelPenjualan');
    $routes->get('penjualan/detail/(:segment)', 'Laporan::detailPenjualan/$1');
    $routes->get('pembelian', 'Laporan::pembelian');
    $routes->get('pembelian/cetak', 'Laporan::cetakPembelian');
    $routes->get('pembelian/excel', 'Laporan::excelPembelian');
    $routes->get('pembelian/detail/(:segment)', 'Laporan::detailPembelian/$1');
    // Arus Kas & Valuasi Kekayaan Toko (Eksklusif Admin / Owner)
    $routes->get('arus-kas', 'Laporan::arusKas', ['filter' => 'admin']);
    $routes->get('arus-kas/cetak', 'Laporan::cetakArusKas', ['filter' => 'admin']);
    $routes->get('arus-kas/excel', 'Laporan::excelArusKas', ['filter' => 'admin']);
    $routes->get('kartu-stok', 'Laporan::kartuStok', ['filter' => 'admin']);
    $routes->get('stok-opname', 'Laporan::stokOpname', ['filter' => 'admin']);
    
    // Rekap & Valuasi Aset Khusus Owner (Eksklusif Admin / Owner)
    $routes->get('owner', 'LaporanOwner::index', ['filter' => 'admin']);
    $routes->post('owner/simpan-pengeluaran', 'LaporanOwner::simpanPengeluaran', ['filter' => 'admin']);
    $routes->get('owner/cetak', 'LaporanOwner::cetak', ['filter' => 'admin']);
    $routes->get('owner/excel', 'LaporanOwner::excel', ['filter' => 'admin']);
});

// Alias Route Khusus Laporan Owner
$routes->group('laporan-owner', ['filter' => 'admin'], function($routes) {
    $routes->get('/', 'LaporanOwner::index');
    $routes->post('simpan-pengeluaran', 'LaporanOwner::simpanPengeluaran');
    $routes->get('cetak', 'LaporanOwner::cetak');
    $routes->get('excel', 'LaporanOwner::excel');
});
