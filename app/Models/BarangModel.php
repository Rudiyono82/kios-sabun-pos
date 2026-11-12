<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_barang', 'barcode', 'nama_barang', 'kategori_id', 'satuan',
        'harga_beli', 'harga_jual', 'harga_grosir', 'stok', 'min_stok',
        'is_jerigen', 'status', 'qty_paket_a', 'hrg_paket_a',
        'qty_paket_b', 'hrg_paket_b', 'qty_paket_c', 'hrg_paket_c',
        'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    public function getBarangWithKategori($id = null)
    {
        $builder = $this->db->table('barang b')
            ->select('b.*, k.nama_kategori')
            ->join('kategori k', 'k.id = b.kategori_id', 'left');
        
        if ($id !== null) {
            return $builder->where('b.id', $id)->get()->getRowArray();
        }
        return $builder->orderBy('b.nama_barang', 'ASC')->get()->getResultArray();
    }

    public function cariBarangPOS($keyword)
    {
        return $this->db->table('barang')
            ->where('status', 'Aktif')
            ->groupStart()
                ->like('id_barang', $keyword)
                ->orLike('barcode', $keyword)
                ->orLike('nama_barang', $keyword)
            ->groupEnd()
            ->limit(15)
            ->get()->getResultArray();
    }

    public function getStokMenipis()
    {
        return $this->db->table('barang')
            ->where('stok <= min_stok')
            ->where('status', 'Aktif')
            ->orderBy('stok', 'ASC')
            ->get()->getResultArray();
    }
}

