<?php

namespace App\Controllers;

use App\Models\SatuanModel;

class Satuan extends BaseController
{
    public function index()
    {
        $satuanModel = new SatuanModel();
        $db = \Config\Database::connect();

        $satuanList = $satuanModel->orderBy('nama_satuan', 'ASC')->findAll();

        foreach ($satuanList as &$sat) {
            $sat['total_barang'] = $db->table('barang')
                ->where('UPPER(satuan)', strtoupper($sat['nama_satuan']))
                ->countAllResults();
        }

        $data = [
            'title' => 'Master Satuan Barang',
            'satuan_list' => $satuanList,
            'total_satuan' => count($satuanList),
        ];

        return view('satuan/index', $data);
    }

    public function simpan()
    {
        $satuanModel = new SatuanModel();
        $id = $this->request->getPost('id');

        $namaSatuan = strtoupper(trim($this->request->getPost('nama_satuan')));

        if (empty($namaSatuan)) {
            return redirect()->back()->with('error', 'Nama satuan tidak boleh kosong!');
        }

        $data = [
            'nama_satuan' => $namaSatuan,
        ];

        if ($id) {
            $satuanModel->update($id, $data);
            return redirect()->to('/satuan')->with('success', 'Satuan barang berhasil diperbarui!');
        } else {
            // Cek duplikasi
            $existing = $satuanModel->where('UPPER(nama_satuan)', $namaSatuan)->first();
            if ($existing) {
                return redirect()->back()->with('error', "Satuan '{$namaSatuan}' sudah ada di database!");
            }
            $satuanModel->insert($data);
            return redirect()->to('/satuan')->with('success', 'Satuan baru berhasil ditambahkan!');
        }
    }

    public function hapus($id)
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            $adminPassword = trim($this->request->getPost('admin_password') ?? $this->request->getGet('admin_password') ?? '');
            if (!$this->verifyAdminPassword($adminPassword)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => 'Otorisasi ditolak! Password Admin salah, satuan tidak dapat dihapus.'
                    ]);
                }
                return redirect()->to('/satuan')->with('error', 'Otorisasi ditolak! Password Admin salah, satuan tidak dapat dihapus.');
            }
        }

        $db = \Config\Database::connect();
        $satuanModel = new SatuanModel();

        $sat = $satuanModel->find($id);
        if (!$sat) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Satuan tidak ditemukan!']);
            }
            return redirect()->to('/satuan')->with('error', 'Satuan tidak ditemukan!');
        }

        $cekBarang = $db->table('barang')
            ->where('UPPER(satuan)', strtoupper($sat['nama_satuan']))
            ->countAllResults();

        if ($cekBarang > 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => "Satuan '{$sat['nama_satuan']}' tidak dapat dihapus karena sedang dipakai oleh {$cekBarang} produk!"
                ]);
            }
            return redirect()->to('/satuan')->with('error', "Satuan '{$sat['nama_satuan']}' tidak dapat dihapus karena sedang dipakai oleh {$cekBarang} produk!");
        }

        $satuanModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Satuan barang berhasil dihapus!'
            ]);
        }
        return redirect()->to('/satuan')->with('success', 'Satuan barang berhasil dihapus!');
    }
}

