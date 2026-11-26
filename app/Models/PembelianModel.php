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
        // Cari nomor nota terakhir berformat NOTAxxxx agar seragam dengan program lama
        $db = \Config\Database::connect();
        $rows = $db->table($this->table)
            ->like('no_beli', 'NOTA', 'after')
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        $maxNum = 0;
        foreach ($rows as $r) {
            if (preg_match('/^NOTA(\d+)$/i', trim($r['no_beli'] ?? ''), $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        if ($maxNum > 0) {
            return 'NOTA' . ($maxNum + 1);
        }

        return 'NOTA' . (date('y') . '001');
    }
}

