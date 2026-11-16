<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPartialSupportToReturDetail extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('retur_supplier_detail')) {
            $fields = $db->getFieldNames('retur_supplier_detail');
            $newFields = [];

            if (!in_array('tipe_penyelesaian', $fields)) {
                $newFields['tipe_penyelesaian'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => null,
                ];
            }
            if (!in_array('tgl_selesai', $fields)) {
                $newFields['tgl_selesai'] = [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ];
            }
            if (!in_array('catatan', $fields)) {
                $newFields['catatan'] = [
                    'type' => 'TEXT',
                    'null' => true,
                ];
            }

            if (!empty($newFields)) {
                $this->forge->addColumn('retur_supplier_detail', $newFields);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('retur_supplier_detail')) {
            $fields = $db->getFieldNames('retur_supplier_detail');
            foreach (['tipe_penyelesaian', 'tgl_selesai', 'catatan'] as $col) {
                if (in_array($col, $fields)) {
                    $this->forge->dropColumn('retur_supplier_detail', $col);
                }
            }
        }
    }
}

