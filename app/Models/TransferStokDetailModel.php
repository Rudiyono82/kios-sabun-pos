<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferStokDetailModel extends Model
{
    protected $table            = 'transfer_stok_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'transfer_id',
        'id_barang',
        'nama_barang',
        'satuan',
        'qty',
        'qty_terima',
        'catatan'
    ];
    protected $useTimestamps    = false;
}

