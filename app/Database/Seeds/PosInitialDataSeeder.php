<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PosInitialDataSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Profil Toko
        $db->table('profil_toko')->emptyTable();
        $db->table('profil_toko')->insert([
            'nama_toko' => '(KSP) Kios Sabun & Parfum',
            'alamat_1' => 'Jl. Metland Cileungsi,',
            'alamat_2' => 'Blok BD.04, No.17',
            'kota' => 'Cileungsi - Bogor',
            'telepon' => 'WA: 085711159296 / 081287071352',
            'footer_struk' => 'Terima Kasih Atas Kunjungan Anda! Barang yang sudah dibeli dapat ditukar jika kemasan cacat.',
        ]);

        // 2. Users (Admin & Kasir)
        $db->table('users')->emptyTable();
        $db->table('users')->insertBatch([
            [
                'kode_user' => 'ADMIN',
                'nama_user' => 'Administrator',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_user' => 'KASIR1',
                'nama_user' => 'Kasir Utama',
                'password' => password_hash('kasir123', PASSWORD_DEFAULT),
                'role' => 'kasir',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        // 3. Kategori
        $db->table('kategori')->emptyTable();
        $db->table('kategori')->insertBatch([
            ['id' => 1, 'nama_kategori' => 'Deterjen & Cuci Baju', 'keterangan' => 'Sabun cuci baju cair, bubuk, dan pelembut'],
            ['id' => 2, 'nama_kategori' => 'Pembersih Lantai & Karbol', 'keterangan' => 'Karbol wangi, pembersih lantai'],
            ['id' => 3, 'nama_kategori' => 'Cuci Piring', 'keterangan' => 'Sabun cuci piring lemon, lime'],
            ['id' => 4, 'nama_kategori' => 'Parfum Laundry & Badan', 'keterangan' => 'Bibit parfum, spray laundry'],
            ['id' => 5, 'nama_kategori' => 'Kemasan & Jerigen', 'keterangan' => 'Jerigen 5L, botol spray, botol 600ml'],
        ]);

        // 4. Satuan
        $db->table('satuan')->emptyTable();
        $db->table('satuan')->insertBatch([
            ['id' => 1, 'nama_satuan' => 'PCS'],
            ['id' => 2, 'nama_satuan' => 'BOTOL'],
            ['id' => 3, 'nama_satuan' => 'JERIGEN 5L'],
            ['id' => 4, 'nama_satuan' => 'LITER'],
            ['id' => 5, 'nama_satuan' => 'PAKET'],
        ]);

        // 5. Supplier
        $db->table('supplier')->emptyTable();
        $db->table('supplier')->insertBatch([
            [
                'kode_supplier' => 'SUP-001',
                'nama_supplier' => 'CV. Perkasa Kimia Mandiri',
                'alamat' => 'Kawasan Industri Sentul, Bogor',
                'telepon' => '021-87901234',
                'contact_person' => 'Bpk. Haryanto',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_supplier' => 'SUP-002',
                'nama_supplier' => 'PT. Neinsa Aroma Prima',
                'alamat' => 'Bekasi Timur',
                'telepon' => '021-88345678',
                'contact_person' => 'Ibu Ratna',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_supplier' => 'SUP-003',
                'nama_supplier' => 'Pabrik Plastik Nusantara',
                'alamat' => 'Tangerang',
                'telepon' => '021-55443322',
                'contact_person' => 'Bpk. Agus Jerigen',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        // 6. Member
        $db->table('member')->emptyTable();
        $db->table('member')->insertBatch([
            [
                'kode_member' => 'MBR0001',
                'nama' => 'Laundry Bersih Kilat',
                'alamat' => 'Jl. Metland Raya No. 12',
                'telepon' => '081299887766',
                'point' => 150,
                'total_belanja' => 2450000,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_member' => 'MBR0002',
                'nama' => 'Ibu Siti Khodijah',
                'alamat' => 'Perum Cileungsi Hijau Blok B4',
                'telepon' => '085733221100',
                'point' => 45,
                'total_belanja' => 520000,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_member' => 'MBR0003',
                'nama' => 'Klinik Sehat Bugar (Cleaning)',
                'alamat' => 'Jl. Cileungsi Setu Km 3',
                'telepon' => '087812345678',
                'point' => 90,
                'total_belanja' => 1350000,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        // 7. Master Produk / Barang (dari data asli toko kios sabun & parfum)
        $db->table('barang')->emptyTable();
        $db->table('barang')->insertBatch([
            [
                'id_barang' => 'B2020400',
                'barcode' => '89910010001',
                'nama_barang' => 'Perkasa Sabun Cuci Piring Lemon 600ml',
                'kategori_id' => 3,
                'satuan' => 'BOTOL',
                'harga_beli' => 6000,
                'harga_jual' => 8500,
                'harga_grosir' => 7500,
                'stok' => 48,
                'min_stok' => 10,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'B2020401',
                'barcode' => '89910010002',
                'nama_barang' => 'Perkasa Softener & Pelicin Baju Botol 600ml',
                'kategori_id' => 1,
                'satuan' => 'BOTOL',
                'harga_beli' => 7000,
                'harga_jual' => 9500,
                'harga_grosir' => 8500,
                'stok' => 35,
                'min_stok' => 8,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'B2020402',
                'barcode' => '89910010003',
                'nama_barang' => 'Perkasa Deterjen Cair Matik 600ml',
                'kategori_id' => 1,
                'satuan' => 'BOTOL',
                'harga_beli' => 7500,
                'harga_jual' => 10500,
                'harga_grosir' => 9500,
                'stok' => 52,
                'min_stok' => 12,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'B2020404',
                'barcode' => '89910010004',
                'nama_barang' => 'Perkasa Karbol Sereh & Pinus 600ml',
                'kategori_id' => 2,
                'satuan' => 'BOTOL',
                'harga_beli' => 6500,
                'harga_jual' => 9000,
                'harga_grosir' => 8000,
                'stok' => 28,
                'min_stok' => 5,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'JRG-5L-DET',
                'barcode' => '89920020001',
                'nama_barang' => 'Deterjen Cair Laundry Konsentrat Jerigen 5 Liter',
                'kategori_id' => 1,
                'satuan' => 'JERIGEN 5L',
                'harga_beli' => 35000,
                'harga_jual' => 48000,
                'harga_grosir' => 44000,
                'stok' => 19,
                'min_stok' => 5,
                'is_jerigen' => 1, // Jerigen 5L tukar
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'JRG-5L-SFT',
                'barcode' => '89920020002',
                'nama_barang' => 'Softener Pelembut Laundry Aroma Mawar Jerigen 5 Liter',
                'kategori_id' => 1,
                'satuan' => 'JERIGEN 5L',
                'harga_beli' => 36000,
                'harga_jual' => 50000,
                'harga_grosir' => 45000,
                'stok' => 15,
                'min_stok' => 4,
                'is_jerigen' => 1,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'PRF-LND-1L',
                'barcode' => '89930030001',
                'nama_barang' => 'Parfum Laundry Super Grade A (Snappy / Akasia) 1 Liter',
                'kategori_id' => 4,
                'satuan' => 'LITER',
                'harga_beli' => 24000,
                'harga_jual' => 35000,
                'harga_grosir' => 32000,
                'stok' => 22,
                'min_stok' => 6,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_barang' => 'KMS-JRG-5L',
                'barcode' => '89940040001',
                'nama_barang' => 'Jerigen Kosong 5 Liter Tebal Baru (Refill Sabun)',
                'kategori_id' => 5,
                'satuan' => 'PCS',
                'harga_beli' => 7000,
                'harga_jual' => 10000,
                'harga_grosir' => 9000,
                'stok' => 30,
                'min_stok' => 10,
                'is_jerigen' => 0,
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        // 8. Dummy Transaksi Penjualan untuk data Dashboard Hari Ini & Historis
        $faktur1 = 'J' . date('ymd') . '0001';
        $faktur2 = 'J' . date('ymd') . '0002';

        $db->table('penjualan')->emptyTable();
        $db->table('penjualan')->insertBatch([
            [
                'no_faktur' => $faktur1,
                'tanggal' => date('Y-m-d 09:30:00'),
                'kode_user' => 'KASIR1',
                'nama_kasir' => 'Kasir Utama',
                'kode_member' => 'MBR0001',
                'total_item' => 2,
                'total_qty' => 3,
                'subtotal' => 146000,
                'diskon' => 0,
                'potongan_tukar_jerigen' => 10000, // potongan tukar jerigen kosong
                'grand_total' => 136000,
                'bayar' => 150000,
                'kembalian' => 14000,
                'metode_bayar' => 'Cash',
                'no_kartu' => null,
                'total_profit' => 35000,
                'catatan' => 'Tukar 1 jerigen kosong 5L',
                'created_at' => date('Y-m-d 09:30:00'),
            ],
            [
                'no_faktur' => $faktur2,
                'tanggal' => date('Y-m-d 11:15:00'),
                'kode_user' => 'KASIR1',
                'nama_kasir' => 'Kasir Utama',
                'kode_member' => 'MBR0002',
                'total_item' => 3,
                'total_qty' => 4,
                'subtotal' => 71500,
                'diskon' => 0,
                'potongan_tukar_jerigen' => 0,
                'grand_total' => 71500,
                'bayar' => 71500,
                'kembalian' => 0,
                'metode_bayar' => 'QRIS',
                'no_kartu' => 'QRIS-BCA-98124',
                'total_profit' => 19500,
                'catatan' => 'Bayar via QRIS',
                'created_at' => date('Y-m-d 11:15:00'),
            ],
        ]);

        $db->table('penjualan_detail')->emptyTable();
        $db->table('penjualan_detail')->insertBatch([
            [
                'no_faktur' => $faktur1,
                'id_barang' => 'JRG-5L-DET',
                'nama_barang' => 'Deterjen Cair Laundry Konsentrat Jerigen 5 Liter',
                'harga_beli' => 35000,
                'harga_jual' => 48000,
                'qty' => 2,
                'subtotal' => 96000,
                'profit' => 26000,
            ],
            [
                'no_faktur' => $faktur1,
                'id_barang' => 'JRG-5L-SFT',
                'nama_barang' => 'Softener Pelembut Laundry Aroma Mawar Jerigen 5 Liter',
                'harga_beli' => 36000,
                'harga_jual' => 50000,
                'qty' => 1,
                'subtotal' => 50000,
                'profit' => 14000,
            ],
            [
                'no_faktur' => $faktur2,
                'id_barang' => 'B2020400',
                'nama_barang' => 'Perkasa Sabun Cuci Piring Lemon 600ml',
                'harga_beli' => 6000,
                'harga_jual' => 8500,
                'qty' => 2,
                'subtotal' => 17000,
                'profit' => 5000,
            ],
            [
                'no_faktur' => $faktur2,
                'id_barang' => 'B2020402',
                'nama_barang' => 'Perkasa Deterjen Cair Matik 600ml',
                'harga_beli' => 7500,
                'harga_jual' => 10500,
                'qty' => 1,
                'subtotal' => 10500,
                'profit' => 3000,
            ],
            [
                'no_faktur' => $faktur2,
                'id_barang' => 'PRF-LND-1L',
                'nama_barang' => 'Parfum Laundry Super Grade A (Snappy / Akasia) 1 Liter',
                'harga_beli' => 24000,
                'harga_jual' => 35000,
                'qty' => 1,
                'subtotal' => 35000,
                'profit' => 11000,
            ],
        ]);

        // 9. Data Keuangan (Kas Awal)
        $db->table('data_keuangan')->emptyTable();
        $db->table('data_keuangan')->insertBatch([
            [
                'tanggal' => date('Y-m-d 08:00:00'),
                'jenis_keuangan' => 'Kas Awal',
                'nama_keuangan' => 'Kas Awal Laci Kasir',
                'total' => 200000,
                'keterangan' => 'Modal receh kembalian pagi hari',
                'kode_user' => 'ADMIN',
            ],
            [
                'tanggal' => date('Y-m-d 09:30:00'),
                'jenis_keuangan' => 'Pemasukan',
                'nama_keuangan' => 'Penjualan Kasir ' . $faktur1,
                'total' => 136000,
                'keterangan' => 'Kasir tunai',
                'kode_user' => 'KASIR1',
            ],
            [
                'tanggal' => date('Y-m-d 11:15:00'),
                'jenis_keuangan' => 'Pemasukan',
                'nama_keuangan' => 'Penjualan QRIS ' . $faktur2,
                'total' => 71500,
                'keterangan' => 'Pembayaran non-tunai',
                'kode_user' => 'KASIR1',
            ],
        ]);
    }
}
