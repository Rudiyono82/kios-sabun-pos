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

// Point of Sales (Kasir)
$routes->group('pos', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Pos::index');
    $routes->get('search', 'Pos::search');
    $routes->get('search-member', 'Pos::searchMember');
    $routes->post('simpan-transaksi', 'Pos::simpanTransaksi');
    $routes->get('cetak-struk/(:any)', 'Pos::cetakStruk/$1');
});

// Master Barang
$routes->group('barang', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Barang::index');
    $routes->post('simpan', 'Barang::simpan');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Barang::hapus/$1');
    $routes->post('update-stok-cepat', 'Barang::updateStokCepat', ['filter' => 'admin']);
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
    $routes->post('update', 'Cabang::update', ['filter' => 'admin']);
});

// Pembelian / Restock
$routes->group('pembelian', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Pembelian::index');
    $routes->get('tambah', 'Pembelian::tambah');
    $routes->get('detail/(:segment)', 'Pembelian::detail/$1');
    $routes->post('simpan', 'Pembelian::simpan');
    $routes->post('update-harga-barang', 'Pembelian::updateHargaBarang');
    $routes->post('hapus/(:segment)', 'Pembelian::hapus/$1', ['filter' => 'admin']);
});

// Retur Barang ke Supplier (Rijek / Expired)
$routes->group('retur', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Retur::index');
    $routes->get('detail/(:num)', 'Retur::detail/$1');
    $routes->post('simpan', 'Retur::simpan', ['filter' => 'admin']);
    $routes->post('selesaikan', 'Retur::selesaikan', ['filter' => 'admin']);
    $routes->post('hapus/(:num)', 'Retur::hapus/$1');
});

// Faktur Penjualan Kasir
$routes->group('penjualan', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Penjualan::index');
    $routes->get('detail/(:segment)', 'Penjualan::detail/$1');
    $routes->post('hapus-item/(:num)', 'Penjualan::hapusItem/$1');
    $routes->post('hapus/(:segment)', 'Penjualan::hapus/$1');
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
    $routes->get('kartu-stok', 'Laporan::kartuStok', ['filter' => 'admin']);
    $routes->get('stok-opname', 'Laporan::stokOpname', ['filter' => 'admin']);
});
