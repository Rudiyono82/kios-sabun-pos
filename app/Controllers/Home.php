<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function checkEkonomi()
    {
        $db = \Config\Database::connect();
        $barang = $db->table('barang')->where('id_barang', 'B2020438')->get()->getRowArray();
        $pending = $db->table('barang_pending_stok')->where('id_barang', 'B2020438')->get()->getResultArray();
        $stokCabang = $db->table('stok_cabang')->where('id_barang', 'B2020438')->get()->getResultArray();
        return $this->response->setJSON([
            'driver' => $db->DBDriver,
            'barang' => $barang,
            'pending' => $pending,
            'stok_cabang' => $stokCabang
        ]);
    }
}
