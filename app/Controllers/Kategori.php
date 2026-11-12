<?php

namespace App\Controllers;

use App\Models\KategoriModel;

class Kategori extends BaseController
{
    public function index()
    {
        $kategoriModel = new KategoriModel();
        $db = \Config\Database::connect();

        $kategoriList = $kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();

        foreach ($kategoriList as &$kat) {
            $kat['total_barang'] = $db->table('barang')
                ->where('kategori_id', $kat['id'])
                ->countAllResults();
        }

        $data = [
            'title' => 'Master Kategori Produk',
            'kategori_list' => $kategoriList,
            'total_kategori' => count($kategoriList),
        ];

        return view('kategori/index', $data);
    }

    public function simpan()
    {
        $kategoriModel = new KategoriModel();
        $id = $this->request->getPost('id');

        $data = [
            'nama_kategori' => trim($this->request->getPost('nama_kategori')),
            'keterangan'    => trim($this->request->getPost('keterangan')),
        ];

        if (empty($data['nama_kategori'])) {
            return redirect()->back()->with('error', 'Nama kategori tidak boleh kosong!');
        }

        if ($id) {
            $kategoriModel->update($id, $data);
            return redirect()->to('/kategori')->with('success', 'Kategori produk berhasil diperbarui!');
        } else {
            $kategoriModel->insert($data);
            return redirect()->to('/kategori')->with('success', 'Kategori baru berhasil ditambahkan!');
        }
    }

    public function hapus($id)
    {
        $db = \Config\Database::connect();
        $kategoriModel = new KategoriModel();

        // Cek apakah masih ada barang yang menggunakan kategori ini
        $cekBarang = $db->table('barang')->where('kategori_id', $id)->countAllResults();
        if ($cekBarang > 0) {
            return redirect()->to('/kategori')->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$cekBarang} produk barang!");
        }

        $kategoriModel->delete($id);
        return redirect()->to('/kategori')->with('success', 'Kategori produk berhasil dihapus!');
    }
}

