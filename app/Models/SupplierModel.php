<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'telepon',
        'contact_person',
        'created_at'
    ];

    public function generateKodeSupplier(): string
    {
        $last = $this->like('kode_supplier', 'SUP-', 'after')->orderBy('id', 'DESC')->first();
        if (!$last) {
            return 'SUP-001';
        }
        $num = (int) substr($last['kode_supplier'], 4);
        return 'SUP-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}
