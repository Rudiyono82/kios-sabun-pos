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
        $isAdmin = (session()->get('role') === 'admin');

        $idBarang = strtoupper(trim((string)$this->request->getPost('id_barang')));
        $namaBarang = trim((string)$this->request->getPost('nama_barang'));

        if (empty($idBarang) || empty($namaBarang)) {
            return redirect()->back()->withInput()->with('error', 'Kode Barang dan Nama Barang wajib diisi!');
        }

        $existing = $id ? $barangModel->find($id) : null;

        $data = [
            'id_barang'   => $idBarang,
            'barcode'     => trim((string)$this->request->getPost('barcode')),
            'nama_barang' => $namaBarang,
            'kategori_id' => $this->request->getPost('kategori_id') ?: null,
            'satuan'      => $this->request->getPost('satuan') ?: 'PCS',
            'is_jerigen'  => $this->request->getPost('is_jerigen') ? 1 : 0,
            'status'      => $this->request->getPost('status') ?? 'Aktif',
        ];

        // Pengaturan harga, paket grosir, dan stok:
        // Jika Admin: Bebas menentukan dan mengubah seluruh harga & stok
        // Jika Kasir:
        //   - Pada Edit: Pertahankan harga lama dari database agar tidak berubah
        //   - Pada Tambah Baru: Harga dan stok awal default 0 (diisi oleh Admin atau saat restock/pembelian)
        if ($isAdmin) {
            $data['harga_beli']   = (float) $this->request->getPost('harga_beli');
            $data['harga_jual']   = (float) $this->request->getPost('harga_jual');
            $data['harga_grosir'] = (float) $this->request->getPost('harga_grosir');
            $data['qty_paket_a']  = (float) $this->request->getPost('qty_paket_a');
            $data['hrg_paket_a']  = (float) $this->request->getPost('hrg_paket_a');
            $data['qty_paket_b']  = (float) $this->request->getPost('qty_paket_b');
            $data['hrg_paket_b']  = (float) $this->request->getPost('hrg_paket_b');
            $data['qty_paket_c']  = (float) $this->request->getPost('qty_paket_c');
            $data['hrg_paket_c']  = (float) $this->request->getPost('hrg_paket_c');
            $data['stok']         = (float) $this->request->getPost('stok');
            $data['min_stok']     = (float) $this->request->getPost('min_stok');
        } else {
            // Role Kasir
            if ($existing) {
                $data['harga_beli']   = (float) ($existing['harga_beli'] ?? 0);
                $data['harga_jual']   = (float) ($existing['harga_jual'] ?? 0);
                $data['harga_grosir'] = (float) ($existing['harga_grosir'] ?? 0);
                $data['qty_paket_a']  = (float) ($existing['qty_paket_a'] ?? 0);
                $data['hrg_paket_a']  = (float) ($existing['hrg_paket_a'] ?? 0);
                $data['qty_paket_b']  = (float) ($existing['qty_paket_b'] ?? 0);
                $data['hrg_paket_b']  = (float) ($existing['hrg_paket_b'] ?? 0);
                $data['qty_paket_c']  = (float) ($existing['qty_paket_c'] ?? 0);
                $data['hrg_paket_c']  = (float) ($existing['hrg_paket_c'] ?? 0);
                $data['stok']         = (float) ($existing['stok'] ?? 0);
                $data['min_stok']     = (float) ($existing['min_stok'] ?? 5);
            } else {
                $data['harga_beli']   = 0;
                $data['harga_jual']   = 0;
                $data['harga_grosir'] = 0;
                $data['qty_paket_a']  = 0;
                $data['hrg_paket_a']  = 0;
                $data['qty_paket_b']  = 0;
                $data['hrg_paket_b']  = 0;
                $data['qty_paket_c']  = 0;
                $data['hrg_paket_c']  = 0;
                $data['stok']         = 0;
                $data['min_stok']     = 5;
            }
        }

        if ($id) {
            $barangModel->update($id, $data);
            return redirect()->to('/barang')->with('success', 'Data barang berhasil diperbarui!');
        } else {
            // Cek duplikasi kode barang
            $cekKode = $barangModel->where('id_barang', $idBarang)->first();
            if ($cekKode) {
                return redirect()->back()->withInput()->with('error', "Kode Barang '{$idBarang}' sudah ada di database!");
            }
            $barangModel->insert($data);
            return redirect()->to('/barang')->with('success', 'Barang baru berhasil ditambahkan!');
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
                        'message' => 'Otorisasi ditolak! Password Admin salah, data barang tidak dapat dihapus.'
                    ]);
                }
                return redirect()->to('/barang')->with('error', 'Otorisasi ditolak! Password Admin salah, data barang tidak dapat dihapus.');
            }
        }

        $barangModel = new BarangModel();
        $barang = $barangModel->find($id);
        if (!$barang) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Barang tidak ditemukan!'
                ]);
            }
            return redirect()->to('/barang')->with('error', 'Barang tidak ditemukan!');
        }

        $barangModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Barang "' . esc($barang['nama_barang']) . '" berhasil dihapus!'
            ]);
        }
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
