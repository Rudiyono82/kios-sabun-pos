<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturSupplierDetail extends Migration
{
    public function up()
    {
        // 1. Buat Tabel retur_supplier_detail untuk menampung multi-barang dalam 1 transaksi retur
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'no_retur' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_barang_retur' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_barang_retur' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'qty_retur' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 1,
            ],
            'harga_beli' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'subtotal_nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'alasan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Rijek / Bocor',
            ],
            'id_barang_ganti' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nama_barang_ganti' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'qty_ganti' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'nilai_ganti' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status_item' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Baru Proses',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('no_retur');
        $this->forge->addKey('id_barang_retur');
        $this->forge->createTable('retur_supplier_detail', true);

        // 2. Tambahkan kolom total_item di tabel master retur_supplier jika belum ada
        $db = \Config\Database::connect();
        if ($db->tableExists('retur_supplier')) {
            $fields = $db->getFieldNames('retur_supplier');
            $newFields = [];
            if (!in_array('total_item', $fields)) {
                $newFields['total_item'] = ['type' => 'INTEGER', 'constraint' => 11, 'default' => 1];
            }
            if (!empty($newFields)) {
                $this->forge->addColumn('retur_supplier', $newFields);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('retur_supplier_detail', true);
    }
}
