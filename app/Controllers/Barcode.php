<?php

namespace App\Controllers;

use App\Models\BarangModel;

class Barcode extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $barangModel = new BarangModel();

        $kategoriList = $db->table('kategori')
            ->orderBy('nama_kategori', 'ASC')
            ->get()->getResultArray();

        $barangList = $barangModel->getBarangWithKategori();

        $data = [
            'title' => 'Cetak Barcode & Label Harga Rak',
            'kategori_list' => array_column($kategoriList, 'nama_kategori'),
            'barang_list' => $barangList,
        ];

        return view('barcode/index', $data);
    }
}

