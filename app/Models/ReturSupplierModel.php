<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturSupplierModel extends Model
{
    protected $table = 'retur_supplier';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'no_retur', 'tanggal', 'tgl_selesai', 'kode_supplier', 'nama_supplier',
        'id_barang_retur', 'nama_barang_retur', 'qty_retur', 'nilai_retur',
        'alasan', 'tipe_penyelesaian', 'id_barang_ganti', 'nama_barang_ganti',
        'qty_ganti', 'nilai_ganti', 'status', 'keterangan', 'catatan_penyelesaian',
        'kode_user', 'created_at'
    ];

    public function generateNoRetur()
    {
        $prefix = 'RT' . date('ymd');
        $last = $this->like('no_retur', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($last) {
            $lastNum = (int) substr($last['no_retur'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }
}

