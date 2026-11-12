<?php

namespace App\Controllers;

use App\Models\BarangModel;

class Barang extends BaseController
{
    public function index()
    {
        $barangModel = new BarangModel();
        $db = \Config\Database::connect();

        $barangList = $barangModel->getBarangWithKategori();
        $isAdmin = (session()->get('role') === 'admin');

        // Jika bukan admin (kasir), sembunyikan informasi stok akhir & harga modal
        if (!$isAdmin) {
            foreach ($barangList as &$b) {
                $b['harga_beli'] = null;
                $b['stok'] = null;
                $b['min_stok'] = null;
            }
        }

        $data = [
            'title' => 'Master Data Barang & Stok',
            'barang' => $barangList,
            'is_admin' => $isAdmin,
            'kategori' => $db->table('kategori')->get()->getResultArray(),
            'satuan' => $db->table('satuan')->get()->getResultArray(),
        ];

        return view('barang/index', $data);
    }

    public function simpan()
    {
        $barangModel = new BarangModel();
        $id = $this->request->getPost('id');

        $data = [
            'id_barang' => strtoupper(trim($this->request->getPost('id_barang'))),
            'barcode' => trim($this->request->getPost('barcode')),
            'nama_barang' => trim($this->request->getPost('nama_barang')),
            'kategori_id' => $this->request->getPost('kategori_id'),
            'satuan' => $this->request->getPost('satuan'),
            'harga_beli' => (float) $this->request->getPost('harga_beli'),
            'harga_jual' => (float) $this->request->getPost('harga_jual'),
            'harga_grosir' => (float) $this->request->getPost('harga_grosir'),
            'stok' => (float) $this->request->getPost('stok'),
            'min_stok' => (float) $this->request->getPost('min_stok'),
            'is_jerigen' => $this->request->getPost('is_jerigen') ? 1 : 0,
            'status' => $this->request->getPost('status') ?? 'Aktif',
        ];

        if ($id) {
            $barangModel->update($id, $data);
            return redirect()->to('/barang')->with('success', 'Data barang berhasil diperbarui!');
        } else {
            $barangModel->insert($data);
            return redirect()->to('/barang')->with('success', 'Barang baru berhasil ditambahkan!');
        }
    }

    public function hapus($id)
    {
        $barangModel = new BarangModel();
        $barangModel->delete($id);
        return redirect()->to('/barang')->with('success', 'Barang berhasil dihapus!');
    }

    public function updateStokCepat()
    {
        $id = $this->request->getPost('id');
        $stokBaru = (float) $this->request->getPost('stok');

        $barangModel = new BarangModel();
        $barang = $barangModel->find($id);

        if ($barang) {
            $selisih = $stokBaru - $barang['stok'];
            $barangModel->update($id, ['stok' => $stokBaru]);

            // Catat kartu stok
            $db = \Config\Database::connect();
            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $barang['id_barang'],
                'tipe' => 'PENYESUAIAN',
                'no_referensi' => 'ADJ-' . date('ymdHis'),
                'masuk' => $selisih > 0 ? $selisih : 0,
                'keluar' => $selisih < 0 ? abs($selisih) : 0,
                'sisa' => $stokBaru,
                'keterangan' => 'Penyesuaian stok langsung / STO'
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Stok berhasil diperbarui!']);
        }

        return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
    }
}

