<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaGatewayToProfilToko extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // 1. Tambah kolom ke profil_toko
        if ($db->tableExists('profil_toko')) {
            $fields = $db->getFieldNames('profil_toko');
            $newFields = [];
            if (!in_array('wa_gateway_token', $fields)) {
                $newFields['wa_gateway_token'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ];
            }
            if (!in_array('wa_gateway_provider', $fields)) {
                $newFields['wa_gateway_provider'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => 'fonnte',
                ];
            }
            if (!empty($newFields)) {
                $this->forge->addColumn('profil_toko', $newFields);
            }
        }

        // 2. Tambah kolom ke cabang
        if ($db->tableExists('cabang')) {
            $fieldsC = $db->getFieldNames('cabang');
            $newFieldsC = [];
            if (!in_array('wa_gateway_token', $fieldsC)) {
                $newFieldsC['wa_gateway_token'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ];
            }
            if (!in_array('wa_gateway_provider', $fieldsC)) {
                $newFieldsC['wa_gateway_provider'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => 'fonnte',
                ];
            }
            if (!empty($newFieldsC)) {
                $this->forge->addColumn('cabang', $newFieldsC);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('profil_toko')) {
            $fields = $db->getFieldNames('profil_toko');
            if (in_array('wa_gateway_token', $fields)) {
                $this->forge->dropColumn('profil_toko', 'wa_gateway_token');
            }
            if (in_array('wa_gateway_provider', $fields)) {
                $this->forge->dropColumn('profil_toko', 'wa_gateway_provider');
            }
        }
        if ($db->tableExists('cabang')) {
            $fieldsC = $db->getFieldNames('cabang');
            if (in_array('wa_gateway_token', $fieldsC)) {
                $this->forge->dropColumn('cabang', 'wa_gateway_token');
            }
            if (in_array('wa_gateway_provider', $fieldsC)) {
                $this->forge->dropColumn('cabang', 'wa_gateway_provider');
            }
        }
    }
}
