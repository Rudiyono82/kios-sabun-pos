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
            'auto_kode_barang' => $barangModel->generateKodeBarang(),
            'pending_count' => $db->table('barang')->where('status', 'Pending')->countAllResults(),
            'kategori' => $db->table('kategori')->get()->getResultArray(),
            'satuan' => $db->table('satuan')->get()->getResultArray(),
            'suppliers' => $db->table('supplier')->orderBy('nama_supplier', 'ASC')->get()->getResultArray(),
        ];

        return view('barang/index', $data);
    }

    /**
     * Endpoint API JSON untuk generate kode barang otomatis B{YYYY}{NNN}
     */
    public function generateKode()
    {
        $barangModel = new BarangModel();
        return $this->response->setJSON([
            'status' => 'success',
            'kode'   => $barangModel->generateKodeBarang()
        ]);
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

        // Otomatis generate kode barang jika kosong atau 'AUTO'
        if (empty($idBarang) || $idBarang === 'AUTO') {
            $idBarang = $barangModel->generateKodeBarang();
        }

        if (empty($idBarang) || empty($namaBarang)) {
            return redirect()->back()->withInput()->with('error', 'Kode Barang dan Nama Barang wajib diisi!');
        }

        // Cek duplikasi kode barang
        $existingByKode = $barangModel->where('id_barang', $idBarang)->first();
        if ($existingByKode && (!$id || $existingByKode['id'] != $id)) {
            if (!$id) {
                $idBarang = $barangModel->generateKodeBarang();
            } else {
                return redirect()->back()->withInput()->with('error', "Kode Barang '{$idBarang}' sudah digunakan oleh produk lain!");
            }
        }

        // Satuan dan Kategori di Master Barang = Mandatori
        if (empty($kategoriId)) {
            return redirect()->back()->withInput()->with('error', 'Kategori Barang wajib dipilih!');
        }
        if (empty($satuan)) {
            return redirect()->back()->withInput()->with('error', 'Satuan Kemasan Barang wajib dipilih!');
        }

        // Ambil data existing dari database
        $existing = $id ? $barangModel->find($id) : null;
        if ($id && !$existing) {
            return redirect()->to('/barang')->with('error', 'Data barang tidak ditemukan!');
        }

        // Harga Beli (Modal) = Mandatori saat tambah barang baru
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
        ];

        // Helper pembersih angka mata uang (rupiah) berformat titik
        $cleanNumber = function($val) {
            if ($val === null || $val === '') return 0.0;
            return (float) str_replace(['.', ',', ' '], '', (string)$val);
        };

        if ($isAdmin) {
            // Pengaturan harga, paket grosir, dan stok (Hanya Admin yang berhak input dan ubah)
            $data['status']       = $this->request->getPost('status') ?? 'Aktif';
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
            // Kasir:
            if (empty($id)) {
                // TAMBAH BARANG BARU OLEH KASIR:
                // Status OTOMATIS = 'Pending' (Menunggu Approval Admin)
                // Barang berstatus Pending tidak bisa dijual di POS atau dibeli di Pembelian
                $data['status']       = 'Pending';
                $data['harga_beli']   = $cleanNumber($this->request->getPost('harga_beli'));
                $data['harga_jual']   = $cleanNumber($this->request->getPost('harga_jual'));
                $data['harga_grosir'] = $cleanNumber($this->request->getPost('harga_grosir'));
                $data['qty_paket_a']  = (float) $this->request->getPost('qty_paket_a');
                $data['hrg_paket_a']  = $cleanNumber($this->request->getPost('hrg_paket_a'));
                $data['qty_paket_b']  = (float) $this->request->getPost('qty_paket_b');
                $data['hrg_paket_b']  = $cleanNumber($this->request->getPost('hrg_paket_b'));
                $data['qty_paket_c']  = (float) $this->request->getPost('qty_paket_c');
                $data['hrg_paket_c']  = $cleanNumber($this->request->getPost('hrg_paket_c'));
                $data['stok']         = 0; // Kasir tidak dapat menentukan stok awal langsung
                $data['min_stok']     = (float) ($this->request->getPost('min_stok') ?: 5);
            } else {
                // EDIT BARANG OLEH KASIR:
                $currentStatus = $existing['status'] ?? 'Aktif';
                if ($currentStatus === 'Pending') {
                    // Jika masih status Pending, kasir boleh mengedit informasi usulan harga
                    $data['status']       = 'Pending';
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
                    $data['min_stok']     = (float) ($this->request->getPost('min_stok') ?: 5);
                } else {
                    // Barang SUDAH AKTIF atau TIDAK AKTIF: pertahankan status asli dan kunci total harga serta stok
                    $data['status']       = $currentStatus;
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
                }
            }
        }

        // Handle Upload Foto / Gambar Barang
        $fileFoto = $this->request->getFile('foto_barang');
        $hapusFoto = $this->request->getPost('hapus_foto') == '1';
        $gambarLama = trim((string)$this->request->getPost('gambar_lama'));

        $uploadDir = FCPATH . 'uploads/barang';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $xamppDir = 'C:/xampp/htdocs/kios-sabun-parfum-pos/public/uploads/barang';
        if (!is_dir($xamppDir) && is_dir('C:/xampp/htdocs/kios-sabun-parfum-pos')) {
            @mkdir($xamppDir, 0777, true);
        }

        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $mime = $fileFoto->getMimeType();
            if (!in_array($mime, $allowedMimes)) {
                return redirect()->back()->withInput()->with('error', 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP!');
            }
            if ($fileFoto->getSize() > 3 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'Ukuran file foto maksimal 3MB!');
            }

            $ext = $fileFoto->guessExtension() ?: 'jpeg';
            $newFileName = $idBarang . '.' . $ext;

            if ($gambarLama && $gambarLama !== $newFileName) {
                @unlink($uploadDir . '/' . $gambarLama);
                if (is_dir($xamppDir)) @unlink($xamppDir . '/' . $gambarLama);
            }

            $fileFoto->move($uploadDir, $newFileName, true);
            if (is_dir($xamppDir) && file_exists($uploadDir . '/' . $newFileName)) {
                @copy($uploadDir . '/' . $newFileName, $xamppDir . '/' . $newFileName);
            }

            $data['gambar'] = $newFileName;
            $data['path_file'] = $newFileName;
        } elseif ($hapusFoto) {
            if ($gambarLama) {
                @unlink($uploadDir . '/' . $gambarLama);
                if (is_dir($xamppDir)) @unlink($xamppDir . '/' . $gambarLama);
            }
            $data['gambar'] = null;
            $data['path_file'] = null;
        } else {
            if ($existing) {
                $data['gambar'] = $existing['gambar'] ?? ($gambarLama ?: null);
            } elseif ($gambarLama) {
                $data['gambar'] = $gambarLama;
            }
        }

        if ($id) {
            $barangModel->update($id, $data);
            $msg = (!$isAdmin && ($existing['status'] ?? '') === 'Pending')
                ? "Data pengajuan barang '{$namaBarang}' berhasil diperbarui (Status: PENDING menunggu persetujuan Admin)."
                : 'Data barang berhasil diperbarui!';
            return redirect()->to('/barang')->with('success', $msg);
        } else {
            // Cek duplikasi kode barang
            $cekKode = $barangModel->where('id_barang', $idBarang)->first();
            if ($cekKode) {
                return redirect()->back()->withInput()->with('error', "Kode Barang '{$idBarang}' sudah ada di database!");
            }
            $barangModel->insert($data);
            $msg = !$isAdmin
                ? "Barang baru '{$namaBarang}' berhasil diajukan dengan status PENDING! Menunggu persetujuan Admin/Owner sebelum dapat dijual di POS atau dibeli."
                : 'Barang baru berhasil ditambahkan dan langsung aktif!';
            return redirect()->to('/barang')->with('success', $msg);
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
            ->select('b.id, b.id_barang, b.barcode, b.nama_barang, b.kategori_id, k.nama_kategori, b.satuan, b.harga_beli, b.harga_jual, b.harga_grosir, b.qty_paket_a, b.hrg_paket_a, b.qty_paket_b, b.hrg_paket_b, b.qty_paket_c, b.hrg_paket_c, b.status, b.gambar, b.path_file, b.kode_supplier_1, b.kode_supplier_2, s1.nama_supplier as nama_supplier_1, s2.nama_supplier as nama_supplier_2, CASE WHEN COALESCE(sc.stok, 0) > 0 THEN sc.stok ELSE b.stok END as stok_cabang, b.stok as stok_total')
            ->join('kategori k', 'k.id = b.kategori_id', 'left')
            ->join('supplier s1', 's1.kode_supplier = b.kode_supplier_1', 'left')
            ->join('supplier s2', 's2.kode_supplier = b.kode_supplier_2', 'left')
            ->join('stok_cabang sc', 'sc.id_barang = b.id_barang AND sc.id_cabang = ' . (int)$idCabang, 'left');

        if ($tipe === 'penjualan') {
            // Permintaan User:
            // Di penjualan: hanya data barang yang AKTIF dan STOKNYA TIDAK = 0 (stok > 0)
            $builder->where('b.status', 'Aktif')
                    ->where('(COALESCE(sc.stok, 0) > 0 OR COALESCE(b.stok, 0) > 0)');
        } else {
            // Permintaan User:
            // Di pembelian / retur: semua barang sesuai suppliernya (master barang & riwayat pembelian) dan SEMUA STATUS (Aktif maupun Nonaktif)
            $allSuppliers = ($this->request->getGet('all_suppliers') == '1');
            if (!empty($kodeSupplier) && !$allSuppliers) {
                $suppRow = $db->table('supplier')->where('kode_supplier', $kodeSupplier)->get()->getRowArray();
                $namaSupplier = $suppRow ? trim($suppRow['nama_supplier']) : '';

                // Ambil daftar id_barang yang pernah dibeli dari supplier ini melalui riwayat pembelian
                $subBeliBuilder = $db->table('pembelian_detail pd')
                    ->select('pd.id_barang')
                    ->join('pembelian p', 'p.no_beli = pd.no_beli')
                    ->where('p.kode_supplier', $kodeSupplier);
                if (!empty($namaSupplier)) {
                    $subBeliBuilder->orWhere('p.nama_supplier', $namaSupplier);
                }
                $listIdBeli = array_column($subBeliBuilder->get()->getResultArray(), 'id_barang');

                $builder->groupStart()
                    ->where('b.kode_supplier_1', $kodeSupplier)
                    ->orWhere('b.kode_supplier_2', $kodeSupplier)
                    ->orLike('b.kode_supplier_1', $kodeSupplier)
                    ->orLike('b.kode_supplier_2', $kodeSupplier);

                if (!empty($namaSupplier)) {
                    $builder->orLike('b.kode_supplier_1', $namaSupplier)
                            ->orLike('b.kode_supplier_2', $namaSupplier)
                            ->orLike('s1.nama_supplier', $namaSupplier)
                            ->orLike('s2.nama_supplier', $namaSupplier);
                }

                if (!empty($listIdBeli)) {
                    $builder->orWhereIn('b.id_barang', array_unique($listIdBeli));
                }

                $builder->groupEnd();
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

    /**
     * Persetujuan (Approval) Barang Baru oleh Admin
     */
    public function approve($id)
    {
        if (session()->get('role') !== 'admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Hanya Admin/Owner yang berhak menyetujui barang baru!'
                ]);
            }
            return redirect()->to('/barang')->with('error', 'Hanya Admin/Owner yang berhak menyetujui barang baru!');
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

        $barangModel->update($id, [
            'status' => 'Aktif',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Pastikan record stok_cabang terdaftar untuk setiap cabang aktif
        $db = \Config\Database::connect();
        $cabangs = $db->table('cabang')->where('status', 'Aktif')->get()->getResultArray();
        foreach ($cabangs as $c) {
            $cek = $db->table('stok_cabang')
                ->where('id_barang', $barang['id_barang'])
                ->where('id_cabang', $c['id'])
                ->get()->getRowArray();
            if (!$cek) {
                $db->table('stok_cabang')->insert([
                    'id_barang' => $barang['id_barang'],
                    'id_cabang' => $c['id'],
                    'stok'      => (float)($barang['stok'] ?? 0),
                    'min_stok'  => (float)($barang['min_stok'] ?? 5),
                ]);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Barang '{$barang['nama_barang']}' berhasil disetujui (Approved)! Sekarang barang sudah aktif dan siap dijual di POS serta pembelian."
            ]);
        }

        return redirect()->to('/barang')->with('success', "Barang '{$barang['nama_barang']}' berhasil disetujui (Approved)! Sekarang barang sudah aktif dan siap dijual di POS serta pembelian.");
    }

    /**
     * Penolakan (Reject) Barang Baru oleh Admin
     */
    public function reject($id)
    {
        if (session()->get('role') !== 'admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Hanya Admin/Owner yang berhak menolak barang!'
                ]);
            }
            return redirect()->to('/barang')->with('error', 'Hanya Admin/Owner yang berhak menolak barang!');
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

        $barangModel->update($id, [
            'status' => 'Nonaktif',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Pengajuan barang '{$barang['nama_barang']}' telah ditolak (Status diubah ke Nonaktif)."
            ]);
        }

        return redirect()->to('/barang')->with('success', "Pengajuan barang '{$barang['nama_barang']}' telah ditolak (Status diubah ke Nonaktif).");
    }
}
