<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfilTokoModel extends Model
{
    protected $table = 'profil_toko';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'nama_toko',
        'alamat_1',
        'alamat_2',
        'kota',
        'telepon',
        'footer_struk'
    ];
}

