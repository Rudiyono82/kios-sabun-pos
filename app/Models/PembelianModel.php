<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianModel extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'no_beli', 'tanggal', 'kode_supplier', 'nama_supplier', 'kode_user',
        'total_biaya', 'status_bayar', 'catatan', 'created_at'
    ];

    public function generateNoBeli()
    {
        $prefix = 'B' . date('ymd');
        $last = $this->like('no_beli', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($last) {
            $lastNum = (int) substr($last['no_beli'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }
}

