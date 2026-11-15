<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBarangPendingStok extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'ref_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tgl_input' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'stok_pending' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'harga_beli_lama' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'harga_beli' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'harga_jual_lama' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'harga_jual' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'qty_paket_a' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'default'    => 0,
            ],
            'harga_paket_a' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'qty_paket_b' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'default'    => 0,
            ],
            'harga_paket_b' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'qty_paket_c' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'default'    => 0,
            ],
            'harga_paket_c' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status_persetujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Belum Setuju', // 'Belum Setuju', 'Setuju', 'Setuju dengan Perubahan'
            ],
            'status_eksekusi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // 1 = Pending (Belum diupdate ke Master), 0 = Selesai (Sudah diupdate ke Master)
            ],
            'user_input' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'KASIR1',
            ],
            'user_approved' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tgl_approved' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_barang');
        $this->forge->addKey('status_eksekusi');
        $this->forge->createTable('barang_pending_stok', true);
    }

    public function down()
    {
        $this->forge->dropTable('barang_pending_stok', true);
    }
}

