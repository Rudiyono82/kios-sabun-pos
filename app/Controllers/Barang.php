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

        // Jika bukan admin (kasir), sembunyikan informasi stok akhir saja (harga beli/modal tetap ditampilkan untuk pengecekan kasir)
        if (!$isAdmin) {
            foreach ($barangList as &$b) {
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
            'suppliers' => $db->table('supplier')->orderBy('nama_supplier', 'ASC')->get()->getResultArray(),
        ];

        return view('barang/index', $data);
    }

    public function simpan()
    {
        $barangModel = new BarangModel();
        $id = $this->request->getPost('id');
        $isAdmin = (session()->get('role') === 'admin');

        $idBarang   = strtoupper(trim((string)$this->request->getPost('id_barang')));
        $namaBarang = trim((string)$this->request->getPost('nama_barang'));
        $kategoriId = trim((string)$this->request->getPost('kategori_id'));
        $satuan     = trim((string)$this->request->getPost('satuan'));
        $hargaBeli  = $this->request->getPost('harga_beli');

        if (empty($idBarang) || empty($namaBarang)) {
            return redirect()->back()->withInput()->with('error', 'Kode Barang dan Nama Barang wajib diisi!');
        }

        // Satuan dan Kategori di Master Barang = Mandatori
        if (empty($kategoriId)) {
            return redirect()->back()->withInput()->with('error', 'Kategori Barang wajib dipilih!');
        }
        if (empty($satuan)) {
            return redirect()->back()->withInput()->with('error', 'Satuan Kemasan Barang wajib dipilih!');
        }

        // Harga Beli (Modal) = Mandatori saat tambah barang baru
        $existing = $id ? $barangModel->find($id) : null;
        if (!$existing && ($hargaBeli === null || $hargaBeli === '')) {
            return redirect()->back()->withInput()->with('error', 'Harga Modal (Beli) wajib diisi saat tambah barang baru!');
        }

        $data = [
            'id_barang'       => $idBarang,
            'barcode'         => trim((string)$this->request->getPost('barcode')),
            'nama_barang'     => $namaBarang,
            'kategori_id'     => $kategoriId,
            'satuan'          => $satuan,
            'kode_supplier_1' => trim((string)$this->request->getPost('kode_supplier_1')) ?: null,
            'kode_supplier_2' => trim((string)$this->request->getPost('kode_supplier_2')) ?: null,
            'is_jerigen'      => $this->request->getPost('is_jerigen') ? 1 : 0,
            'status'          => $this->request->getPost('status') ?? 'Aktif',
        ];

        // Helper pembersih angka mata uang (rupiah) berformat titik
        $cleanNumber = function($val) {
            if ($val === null || $val === '') return 0.0;
            return (float) str_replace(['.', ',', ' '], '', (string)$val);
        };

        // Pengaturan harga, paket grosir, dan stok:
        // Saat TAMBAH BARU: Field harga beli, harga jual, grosir, dan paket A/B/C SEMUANYA ENABLED bisa di-input (Admin maupun Kasir)
        // Saat EDIT:
        //   - Jika Admin: Bebas menentukan dan mengubah seluruh harga & stok
        //   - Jika Kasir: Pertahankan harga lama dari database (karena field harga di-lock/disabled saat edit)
        if ($isAdmin) {
            $data['harga_beli']   = $cleanNumber($this->request->getPost('harga_beli'));
            $data['harga_jual']   = $cleanNumber($this->request->getPost('harga_jual'));
            $data['harga_grosir'] = $cleanNumber($this->request->getPost('harga_grosir'));
            $data['qty_paket_a']  = (float) $this->request->getPost('qty_paket_a');
            $data['hrg_paket_a']  = $cleanNumber($this->request->getPost('hrg_paket_a'));
            $data['qty_paket_b']  = (float) $this->request->getPost('qty_paket_b');
            $data['hrg_paket_b']  = $cleanNumber($this->request->getPost('hrg_paket_b'));
            $data['qty_paket_c']  = (float) $this->request->getPost('qty_paket_c');
            $data['hrg_paket_c']  = $cleanNumber($this->request->getPost('hrg_paket_c'));
            $data['stok']         = (float) $this->request->getPost('stok');
            $data['min_stok']     = (float) ($this->request->getPost('min_stok') ?: 5);
        } else {
            // Role Kasir
            if ($existing) {
                // Pada Edit: Pertahankan harga & paket eksisting agar tidak ter-reset
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
                // Pada Tambah Baru: Kasir diizinkan input harga beli, harga jual, grosir & paket A/B/C
                $data['harga_beli']   = $cleanNumber($this->request->getPost('harga_beli'));
                $data['harga_jual']   = $cleanNumber($this->request->getPost('harga_jual'));
                $data['harga_grosir'] = $cleanNumber($this->request->getPost('harga_grosir'));
                $data['qty_paket_a']  = (float) $this->request->getPost('qty_paket_a');
                $data['hrg_paket_a']  = $cleanNumber($this->request->getPost('hrg_paket_a'));
                $data['qty_paket_b']  = (float) $this->request->getPost('qty_paket_b');
                $data['hrg_paket_b']  = $cleanNumber($this->request->getPost('hrg_paket_b'));
                $data['qty_paket_c']  = (float) $this->request->getPost('qty_paket_c');
                $data['hrg_paket_c']  = $cleanNumber($this->request->getPost('hrg_paket_c'));
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

    // LookUp List of Stock (Popup Pencarian Barang untuk Penjualan & Pembelian)
    public function lookup()
    {
        $db = \Config\Database::connect();
        $tipe = $this->request->getGet('tipe') ?: 'penjualan'; // 'penjualan' atau 'pembelian'
        $keyword = trim((string)$this->request->getGet('q'));
        $filterBy = $this->request->getGet('filter_by') ?: 'all';
        $kodeSupplier = trim((string)$this->request->getGet('supplier'));
        $idCabang = session()->get('id_cabang') ?: 1;

        $builder = $db->table('barang b')
            ->select('b.id, b.id_barang, b.barcode, b.nama_barang, b.kategori_id, k.nama_kategori, b.satuan, b.harga_beli, b.harga_jual, b.harga_grosir, b.qty_paket_a, b.hrg_paket_a, b.qty_paket_b, b.hrg_paket_b, b.qty_paket_c, b.hrg_paket_c, b.status, b.kode_supplier_1, b.kode_supplier_2, s1.nama_supplier as nama_supplier_1, s2.nama_supplier as nama_supplier_2, COALESCE(sc.stok, b.stok) as stok_cabang, b.stok as stok_total')
            ->join('kategori k', 'k.id = b.kategori_id', 'left')
            ->join('supplier s1', 's1.kode_supplier = b.kode_supplier_1', 'left')
            ->join('supplier s2', 's2.kode_supplier = b.kode_supplier_2', 'left')
            ->join('stok_cabang sc', 'sc.id_barang = b.id_barang AND sc.id_cabang = ' . (int)$idCabang, 'left');

        if ($tipe === 'penjualan') {
            // Permintaan User:
            // Di penjualan: hanya data barang yang AKTIF dan STOKNYA TIDAK = 0 (stok > 0)
            $builder->where('b.status', 'Aktif')
                    ->where('COALESCE(sc.stok, b.stok) >', 0);
        } else {
            // Permintaan User:
            // Di pembelian: semua barang sesuai suppliernya dan SEMUA STATUS (Aktif maupun Nonaktif)
            if (!empty($kodeSupplier)) {
                $builder->groupStart()
                    ->where('b.kode_supplier_1', $kodeSupplier)
                    ->orWhere('b.kode_supplier_2', $kodeSupplier)
                    ->groupEnd();
            }
        }

        if (!empty($keyword)) {
            if ($filterBy === 'nama') {
                $builder->like('b.nama_barang', $keyword);
            } elseif ($filterBy === 'kode') {
                $builder->like('b.id_barang', $keyword);
            } elseif ($filterBy === 'barcode') {
                $builder->like('b.barcode', $keyword);
            } elseif ($filterBy === 'kategori') {
                $builder->like('k.nama_kategori', $keyword);
            } else {
                $builder->groupStart()
                    ->like('b.nama_barang', $keyword)
                    ->orLike('b.id_barang', $keyword)
                    ->orLike('b.barcode', $keyword)
                    ->orLike('k.nama_kategori', $keyword)
                    ->groupEnd();
            }
        }

        $items = $builder->orderBy('b.nama_barang', 'ASC')->limit(200)->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'tipe' => $tipe,
            'total' => count($items),
            'data' => $items
        ]);
    }
}
