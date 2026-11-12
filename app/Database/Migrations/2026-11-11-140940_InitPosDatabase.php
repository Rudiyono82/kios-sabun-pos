<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitPosDatabase extends Migration
{
    public function up()
    {
        // 1. TABEL USER / KARYAWAN
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'kode_user' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'nama_user' => ['type' => 'VARCHAR', 'constraint' => 100],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'kasir'], // admin, kasir, manager
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        // 2. TABEL KATEGORI BARANG
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'nama_kategori' => ['type' => 'VARCHAR', 'constraint' => 100],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kategori', true);

        // 3. TABEL SATUAN
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'nama_satuan' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('satuan', true);

        // 4. TABEL SUPPLIER
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'kode_supplier' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'nama_supplier' => ['type' => 'VARCHAR', 'constraint' => 150],
            'alamat' => ['type' => 'TEXT', 'null' => true],
            'telepon' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'contact_person' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('supplier', true);

        // 5. TABEL MEMBER / PELANGGAN
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'kode_member' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'alamat' => ['type' => 'TEXT', 'null' => true],
            'telepon' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'point' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'total_belanja' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('member', true);

        // 6. TABEL BARANG (Produk Sabun, Parfum, Jerigen, dsb)
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'id_barang' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'barcode' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 200],
            'kategori_id' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'PCS'],
            'harga_beli' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'harga_jual' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'harga_grosir' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'stok' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'min_stok' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 5],
            'is_jerigen' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // Untuk fitur tukar jerigen 5L
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Aktif'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('barang', true);

        // 7. TABEL PENJUALAN MASTER (Transaksi Kasir)
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'no_faktur' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'tanggal' => ['type' => 'DATETIME'],
            'kode_user' => ['type' => 'VARCHAR', 'constraint' => 50],
            'nama_kasir' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kode_member' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'total_item' => ['type' => 'INTEGER', 'constraint' => 11, 'default' => 0],
            'total_qty' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'diskon' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'potongan_tukar_jerigen' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'bayar' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'kembalian' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'metode_bayar' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Cash'], // Cash, QRIS, Transfer, Debit
            'no_kartu' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'total_profit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('penjualan', true);

        // 8. TABEL PENJUALAN DETAIL
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'no_faktur' => ['type' => 'VARCHAR', 'constraint' => 50],
            'id_barang' => ['type' => 'VARCHAR', 'constraint' => 50],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 200],
            'harga_beli' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'harga_jual' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'qty' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 1],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'profit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('penjualan_detail', true);

        // 9. TABEL PEMBELIAN MASTER (Restock / Inflow)
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'no_beli' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'tanggal' => ['type' => 'DATETIME'],
            'kode_supplier' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nama_supplier' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'kode_user' => ['type' => 'VARCHAR', 'constraint' => 50],
            'total_biaya' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status_bayar' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Lunas'], // Lunas, Hutang
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pembelian', true);

        // 10. TABEL PEMBELIAN DETAIL
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'no_beli' => ['type' => 'VARCHAR', 'constraint' => 50],
            'id_barang' => ['type' => 'VARCHAR', 'constraint' => 50],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 200],
            'harga_beli' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'qty' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 1],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pembelian_detail', true);

        // 11. TABEL KARTU STOK / MUTASI
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'tanggal' => ['type' => 'DATETIME'],
            'id_barang' => ['type' => 'VARCHAR', 'constraint' => 50],
            'tipe' => ['type' => 'VARCHAR', 'constraint' => 20], // MASUK, KELUAR, PENYESUAIAN, STO
            'no_referensi' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'masuk' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'keluar' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'sisa' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kartu_stok', true);

        // 12. TABEL ARUS KAS / KEUANGAN (DATA_KEUANGAN)
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'tanggal' => ['type' => 'DATETIME'],
            'jenis_keuangan' => ['type' => 'VARCHAR', 'constraint' => 30], // Pemasukan, Pengeluaran, Kas Awal
            'nama_keuangan' => ['type' => 'VARCHAR', 'constraint' => 150],
            'total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'kode_user' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('data_keuangan', true);

        // 13. TABEL PENGATURAN TOKO (PROFIL TOKO)
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'nama_toko' => ['type' => 'VARCHAR', 'constraint' => 150, 'default' => '(KSP) Kios Sabun & Parfum'],
            'alamat_1' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'Jl. Metland Cileungsi'],
            'alamat_2' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'Blok BD.04, No.17'],
            'kota' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Cileungsi - Bogor'],
            'telepon' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'WA 085711159296 / 081287071352'],
            'footer_struk' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('profil_toko', true);
    }

    public function down()
    {
        $this->forge->dropTable('profil_toko', true);
        $this->forge->dropTable('data_keuangan', true);
        $this->forge->dropTable('kartu_stok', true);
        $this->forge->dropTable('pembelian_detail', true);
        $this->forge->dropTable('pembelian', true);
        $this->forge->dropTable('penjualan_detail', true);
        $this->forge->dropTable('penjualan', true);
        $this->forge->dropTable('barang', true);
        $this->forge->dropTable('member', true);
        $this->forge->dropTable('supplier', true);
        $this->forge->dropTable('satuan', true);
        $this->forge->dropTable('kategori', true);
        $this->forge->dropTable('users', true);
    }
}
