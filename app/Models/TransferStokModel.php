<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferStokModel extends Model
{
    protected $table            = 'transfer_stok';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'no_transfer',
        'tanggal',
        'id_cabang_asal',
        'id_cabang_tujuan',
        'total_item',
        'total_qty',
        'status',
        'catatan',
        'kode_user_pengirim',
        'nama_user_pengirim',
        'kode_user_penerima',
        'nama_user_penerima',
        'tgl_terima',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps    = false;

    public static function generateNoTransfer()
    {
        $prefix = 'TRF-' . date('ymd');
        $db = \Config\Database::connect();
        $count = $db->table('transfer_stok')->like('no_transfer', $prefix, 'after')->countAllResults();
        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}

