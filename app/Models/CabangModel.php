<?php

namespace App\Models;

use CodeIgniter\Model;

class CabangModel extends Model
{
    protected $table            = 'cabang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'kota',
        'telepon',
        'footer_struk',
        'is_pusat',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps    = false;

    public function getActiveBranches()
    {
        return $this->where('status', 'Aktif')->orderBy('is_pusat', 'DESC')->orderBy('id', 'ASC')->findAll();
    }

    public function getBranchName($id)
    {
        if (!$id) return 'Semua Cabang (Konsolidasi)';
        $b = $this->find($id);
        return $b ? $b['nama_cabang'] : "Cabang #$id";
    }
}

