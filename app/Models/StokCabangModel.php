<?php

namespace App\Models;

use CodeIgniter\Model;

class StokCabangModel extends Model
{
    protected $table            = 'stok_cabang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_barang',
        'id_cabang',
        'stok',
        'min_stok',
        'lokasi_rak',
        'updated_at'
    ];
    protected $useTimestamps    = false;

    /**
     * Ambil stok barang di cabang tertentu
     */
    public function getStok($idBarang, $idCabang)
    {
        $row = $this->where('id_barang', $idBarang)
                    ->where('id_cabang', $idCabang)
                    ->first();
        return $row ? (float)$row['stok'] : 0.0;
    }

    /**
     * Tambah / kurangi stok barang di cabang tertentu
     */
    public function updateStok($idBarang, $idCabang, $delta)
    {
        $row = $this->where('id_barang', $idBarang)
                    ->where('id_cabang', $idCabang)
                    ->first();

        if ($row) {
            $newStok = (float)$row['stok'] + (float)$delta;
            $this->update($row['id'], [
                'stok' => $newStok,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $newStok;
        } else {
            $newStok = max(0, (float)$delta);
            $this->insert([
                'id_barang' => $idBarang,
                'id_cabang' => $idCabang,
                'stok' => $newStok,
                'min_stok' => 5,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $newStok;
        }
    }
}

