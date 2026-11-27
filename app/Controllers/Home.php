<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function testSimpanEkonomi()
    {
        $db = \Config\Database::connect();
        $items = $db->table('barang')
            ->like('nama_barang', 'ekonomi')
            ->orLike('nama_barang', 'cupir')
            ->orLike('nama_barang', '650')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'count' => count($items),
            'items' => array_map(function($b) {
                return [
                    'id_barang' => $b['id_barang'],
                    'nama_barang' => $b['nama_barang'],
                    'barcode' => $b['barcode'],
                    'status' => $b['status'],
                    'stok' => $b['stok'],
                    'harga_beli' => $b['harga_beli'],
                    'harga_jual' => $b['harga_jual'],
                ];
            }, $items)
        ]);
    }
}
