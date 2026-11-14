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
        'is_jerigen', 'status', 'kode_supplier_1', 'kode_supplier_2',
        'qty_paket_a', 'hrg_paket_a',
        'qty_paket_b', 'hrg_paket_b', 'qty_paket_c', 'hrg_paket_c',
        'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    public function getBarangWithKategori($id = null)
    {
        $builder = $this->db->table('barang b')
            ->select('b.*, k.nama_kategori, s1.nama_supplier as nama_supplier_1, s2.nama_supplier as nama_supplier_2')
            ->join('kategori k', 'k.id = b.kategori_id', 'left')
            ->join('supplier s1', 's1.kode_supplier = b.kode_supplier_1', 'left')
            ->join('supplier s2', 's2.kode_supplier = b.kode_supplier_2', 'left');
        
        if ($id !== null) {
            return $builder->where('b.id', $id)->get()->getRowArray();
        }
        return $builder->orderBy('b.nama_barang', 'ASC')->get()->getResultArray();
    }

    public function cariBarangPOS($keyword, $idCabang = 1)
    {
        return $this->db->table('barang b')
            ->select('b.*, COALESCE(sc.stok, b.stok) as stok, COALESCE(sc.min_stok, b.min_stok) as min_stok')
            ->join('stok_cabang sc', 'sc.id_barang = b.id_barang AND sc.id_cabang = ' . (int)$idCabang, 'left')
            ->where('b.status', 'Aktif')
            ->groupStart()
                ->like('b.id_barang', $keyword)
                ->orLike('b.barcode', $keyword)
                ->orLike('b.nama_barang', $keyword)
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

