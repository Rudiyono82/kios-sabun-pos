<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserSelesaiToReturSupplier extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('retur_supplier')) {
            $fields = $db->getFieldNames('retur_supplier');
            $newFields = [];
            if (!in_array('user_selesai', $fields)) {
                $newFields['user_selesai'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => null,
                ];
            }
            if (!empty($newFields)) {
                $this->forge->addColumn('retur_supplier', $newFields);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('retur_supplier')) {
            $fields = $db->getFieldNames('retur_supplier');
            if (in_array('user_selesai', $fields)) {
                $this->forge->dropColumn('retur_supplier', 'user_selesai');
            }
        }
    }
}
