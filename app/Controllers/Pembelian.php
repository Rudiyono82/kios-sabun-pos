<?php

namespace App\Controllers;

use App\Models\PembelianModel;
use App\Models\BarangModel;

class Pembelian extends BaseController
{
    // 1. Tampilkan Daftar List Pembelian yang Sudah Tersimpan
    public function index()
    {
        $db = \Config\Database::connect();

        $tglMulai = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');
        $kodeSupplier = $this->request->getGet('kode_supplier') ?: '';

        $builder = $db->table('pembelian')
            ->where("DATE(tanggal) >=", $tglMulai)
            ->where("DATE(tanggal) <=", $tglSelesai);

        if (!empty($kodeSupplier)) {
            $builder->where('kode_supplier', $kodeSupplier);
        }

        $list = $builder->orderBy('id', 'DESC')->get()->getResultArray();

        // Hitung Metrik
        $totalBiaya = 0;
        foreach ($list as $r) {
            $totalBiaya += (float) $r['total_biaya'];
        }

        $suppliers = $db->table('supplier')->orderBy('nama_supplier', 'ASC')->get()->getResultArray();

        $data = [
            'title' => 'Daftar Pembelian Supplier & Restock',
            'list' => $list,
            'suppliers' => $suppliers,
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'kode_supplier_selected' => $kodeSupplier,
            'total_biaya' => $totalBiaya,
            'total_nota' => count($list),
        ];

        return view('pembelian/index', $data);
    }

    // 2. Form Input Baru Pembelian Supplier
    public function tambah()
    {
        $db = \Config\Database::connect();
        $pembelianModel = new PembelianModel();

        $data = [
            'title' => 'Input Pembelian Supplier Baru',
            'no_beli' => $pembelianModel->generateNoBeli(),
            'suppliers' => $db->table('supplier')->orderBy('nama_supplier', 'ASC')->get()->getResultArray(),
            // Sesuai permintaan user: input pembelian menampilkan semua status (Aktif maupun Nonaktif)
            'barangs' => $db->table('barang')->orderBy('nama_barang', 'ASC')->get()->getResultArray(),
        ];

        return view('pembelian/tambah', $data);
    }

    // 3. Simpan Transaksi Pembelian Baru
    public function simpan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $json = $this->request->getJSON(true);
        if (!$json || empty($json['items'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Daftar barang belanja tidak boleh kosong!'
            ]);
        }

        $pembelianModel = new PembelianModel();

        // Cegah Transaksi Duplikat (Double Click / Debounce 3 Detik)
        $threeSecAgo = date('Y-m-d H:i:s', time() - 3);
        $recentDuplicate = $db->table('pembelian')
            ->where('kode_supplier', $json['kode_supplier'])
            ->where('kode_user', session()->get('kode_user') ?? 'KASIR1')
            ->where('created_at >=', $threeSecAgo)
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if ($recentDuplicate) {
            $db->transRollback();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Transaksi pembelian berhasil disimpan!',
                'no_beli' => $recentDuplicate['no_beli']
            ]);
        }

        $noBeli = $pembelianModel->generateNoBeli();

        $totalBiaya = 0;
        $detailRecords = [];
        $kartuStokRecords = [];
        $idCabang = session()->get('id_cabang') ?: 1;
        $stokCabangModel = new \App\Models\StokCabangModel();
        $userRole = strtolower((string) session()->get('role'));
        $userName = session()->get('nama_user') ?? session()->get('kode_user') ?? 'KASIR1';

        foreach ($json['items'] as $item) {
            $barang = $db->table('barang')->where('id_barang', $item['id_barang'])->get()->getRowArray();
            if (!$barang) continue;

            $qty = (float) $item['qty'];
            // Harga beli item yang diinput pada keranjang
            $hargaBeliInput = isset($item['harga_beli']) ? (float)$item['harga_beli'] : (float)$barang['harga_beli'];
            $hargaBeliMaster = (float) $barang['harga_beli'];
            
            $subtotal = $qty * $hargaBeliInput;
            $totalBiaya += $subtotal;

            $detailRecords[] = [
                'no_beli' => $noBeli,
                'id_barang' => $item['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'harga_beli' => $hargaBeliInput,
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];

            // Tambah stok barang ke cabang aktif
            $sisaStokCabang = $stokCabangModel->updateStok($item['id_barang'], $idCabang, $qty);

            // Update akumulasi stok di tabel master barang (untuk konsolidasi total)
            $db->query("UPDATE barang SET stok = (SELECT COALESCE(SUM(stok), 0) FROM stok_cabang WHERE id_barang = ?) WHERE id_barang = ?", [$item['id_barang'], $item['id_barang']]);
            $stokBaru = $sisaStokCabang;

            // Catat Kartu Stok
            $kartuStokRecords[] = [
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $item['id_barang'],
                'tipe' => 'MASUK',
                'no_referensi' => $noBeli,
                'masuk' => $qty,
                'keluar' => 0,
                'sisa' => $stokBaru,
                'keterangan' => 'Pembelian Nota ' . $noBeli . ' dari ' . ($json['nama_supplier'] ?? 'Supplier')
            ];

            // SOP MULTI-TIER: Jika harga beli belanja berbeda dengan harga beli master saat stok lama masih ada
            if ($hargaBeliInput > 0 && abs($hargaBeliInput - $hargaBeliMaster) > 0.01) {
                $statusPersetujuan = ($userRole === 'admin' || $userRole === 'owner') ? 'Setuju' : 'Belum Setuju';
                $userApproved = ($userRole === 'admin' || $userRole === 'owner') ? $userName : null;
                $tglApproved = ($userRole === 'admin' || $userRole === 'owner') ? date('Y-m-d H:i:s') : null;

                $db->table('barang_pending_stok')->insert([
                    'ref_id'             => $noBeli,
                    'tgl_input'          => date('Y-m-d H:i:s'),
                    'id_barang'          => $item['id_barang'],
                    'nama_barang'        => $barang['nama_barang'],
                    'stok_pending'       => $qty,
                    'harga_beli_lama'    => $hargaBeliMaster,
                    'harga_beli'         => $hargaBeliInput,
                    'harga_jual_lama'    => (float)$barang['harga_jual'],
                    'harga_jual'         => isset($item['harga_jual']) && $item['harga_jual'] > 0 ? (float)$item['harga_jual'] : (float)$barang['harga_jual'],
                    'qty_paket_a'        => (int)($barang['qty_paket_a'] ?? 0),
                    'harga_paket_a'      => (float)($barang['hrg_paket_a'] ?? 0),
                    'qty_paket_b'        => (int)($barang['qty_paket_b'] ?? 0),
                    'harga_paket_b'      => (float)($barang['hrg_paket_b'] ?? 0),
                    'qty_paket_c'        => (int)($barang['qty_paket_c'] ?? 0),
                    'harga_paket_c'      => (float)($barang['hrg_paket_c'] ?? 0),
                    'status_persetujuan' => $statusPersetujuan,
                    'status_eksekusi'    => 1,
                    'user_input'         => $userName,
                    'user_approved'      => $userApproved,
                    'tgl_approved'       => $tglApproved,
                    'catatan'            => 'Auto-generated dari Pembelian Nota ' . $noBeli,
                    'created_at'         => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $finalTotalBiaya = roundPosTotal($totalBiaya);

        // Insert Master
        $db->table('pembelian')->insert([
            'no_beli' => $noBeli,
            'tanggal' => date('Y-m-d H:i:s'),
            'id_cabang' => $idCabang,
            'kode_supplier' => $json['kode_supplier'] ?? null,
            'nama_supplier' => $json['nama_supplier'] ?? null,
            'kode_user' => session()->get('kode_user') ?? 'ADMIN',
            'total_biaya' => $finalTotalBiaya,
            'status_bayar' => $json['status_bayar'] ?? 'Lunas',
            'catatan' => $json['catatan'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($detailRecords)) {
            $db->table('pembelian_detail')->insertBatch($detailRecords);
        }

        if (!empty($kartuStokRecords)) {
            $db->table('kartu_stok')->insertBatch($kartuStokRecords);
        }

        // Catat pengeluaran di keuangan jika lunas
        if (($json['status_bayar'] ?? 'Lunas') === 'Lunas') {
            $db->table('data_keuangan')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'jenis_keuangan' => 'Pengeluaran',
                'nama_keuangan' => 'Belanja Restock ' . $noBeli,
                'total' => $totalBiaya,
                'keterangan' => 'Supplier: ' . ($json['nama_supplier'] ?? '-'),
                'kode_user' => session()->get('kode_user') ?? 'ADMIN'
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses transaksi pembelian!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pembelian berhasil dicatat dan stok bertambah!',
            'no_beli' => $noBeli,
            'total_biaya' => $finalTotalBiaya,
            'raw_total' => $totalBiaya,
            'pembulatan' => $finalTotalBiaya - $totalBiaya
        ]);
    }

    // 4. Detail Item Pembelian (JSON untuk Modal Popup)
    public function detail($noBeli)
    {
        $db = \Config\Database::connect();
        $pembelian = $db->table('pembelian')->where('no_beli', $noBeli)->get()->getRowArray();

        if (!$pembelian) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data pembelian tidak ditemukan!'
            ]);
        }

        $items = $db->table('pembelian_detail')->where('no_beli', $noBeli)->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'pembelian' => $pembelian,
            'items' => $items
        ]);
    }

    // 5. Hapus Transaksi Pembelian (Otorisasi Super Admin)
    public function hapus($noBeli)
    {
        $db = \Config\Database::connect();
        
        // Jika user yang sedang login adalah Super Admin (role = admin), langsung izinkan
        $isCurrentAdmin = (session()->get('role') === 'admin');
        if (!$isCurrentAdmin) {
            $password = trim($this->request->getPost('password_admin') ?? '');
            if (!$this->verifySuperAdminPassword($password)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak! Password Super Admin salah.'
                ]);
            }
        }

        $pembelian = $db->table('pembelian')->where('no_beli', $noBeli)->get()->getRowArray();
        if (!$pembelian) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data pembelian tidak ditemukan!'
            ]);
        }

        $db->transStart();

        // 1. Ambil detail barang dan kurangi stok kembali (karena pembelian dibatalkan)
        $idCabang = (int)($pembelian['id_cabang'] ?? (session()->get('id_cabang') ?: 1));
        $stokCabangModel = new \App\Models\StokCabangModel();
        $items = $db->table('pembelian_detail')->where('no_beli', $noBeli)->get()->getResultArray();
        foreach ($items as $item) {
            $barang = $db->table('barang')->where('id_barang', $item['id_barang'])->get()->getRowArray();
            if ($barang) {
                $stokBaruCabang = $stokCabangModel->updateStok($item['id_barang'], $idCabang, -(float)$item['qty']);
                $db->query("UPDATE barang SET stok = (SELECT COALESCE(SUM(stok), 0) FROM stok_cabang WHERE id_barang = ?) WHERE id_barang = ?", [$item['id_barang'], $item['id_barang']]);
                $stokBaru = $stokBaruCabang;

                // Catat Kartu Stok Keluar (Pembatalan Pembelian)
                $db->table('kartu_stok')->insert([
                    'tanggal' => date('Y-m-d H:i:s'),
                    'id_barang' => $item['id_barang'],
                    'tipe' => 'KELUAR',
                    'no_referensi' => $noBeli,
                    'masuk' => 0,
                    'keluar' => (float)$item['qty'],
                    'sisa' => $stokBaru,
                    'keterangan' => 'Hapus Pembelian ' . $noBeli . ' (Otorisasi Super Admin)'
                ]);
            }
        }

        // 2. Hapus detail & master pembelian
        $db->table('pembelian_detail')->where('no_beli', $noBeli)->delete();
        $db->table('pembelian')->where('no_beli', $noBeli)->delete();

        // 3. Hapus catatan kas pengeluaran jika ada
        $db->table('data_keuangan')->like('nama_keuangan', $noBeli)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus transaksi pembelian!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Transaksi pembelian {$noBeli} berhasil dihapus dan stok barang telah disesuaikan kembali!"
        ]);
    }

    // 6. Penyesuaian Harga Master Barang dari Screen Pembelian (Dengan Approval Admin)
    public function updateHargaBarang()
    {
        $db = \Config\Database::connect();

        $cleanNumber = function($val) {
            if ($val === null || $val === '') return 0.0;
            return (float) str_replace(['.', ',', ' '], '', (string)$val);
        };

        $idBarang = trim($this->request->getPost('id_barang') ?? '');
        $hargaBeliBaru = $cleanNumber($this->request->getPost('harga_beli'));
        $hargaJualBaru = $cleanNumber($this->request->getPost('harga_jual'));
        $hargaGrosirBaru = $cleanNumber($this->request->getPost('harga_grosir'));
        $qtyPaketA = (float) ($this->request->getPost('qty_paket_a') ?? 0);
        $hrgPaketA = $cleanNumber($this->request->getPost('hrg_paket_a'));
        $qtyPaketB = (float) ($this->request->getPost('qty_paket_b') ?? 0);
        $hrgPaketB = $cleanNumber($this->request->getPost('hrg_paket_b'));
        $qtyPaketC = (float) ($this->request->getPost('qty_paket_c') ?? 0);
        $hrgPaketC = $cleanNumber($this->request->getPost('hrg_paket_c'));

        if (empty($idBarang) || $hargaBeliBaru <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Barang dan Harga Beli Baru wajib diisi dengan benar (> 0)!'
            ]);
        }

        $barang = $db->table('barang')->where('id_barang', $idBarang)->get()->getRowArray();
        if (!$barang) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => "Barang dengan ID {$idBarang} tidak ditemukan!"
            ]);
        }

        $userRole = strtolower((string) session()->get('role'));
        $approverUser = session()->get('nama_user') ?? session()->get('kode_user') ?? 'Admin';

        $statusPersetujuan = 'Setuju';
        $userApproved = $approverUser;
        $tglApproved = date('Y-m-d H:i:s');

        // Jika user yang login BUKAN Admin (misal Kasir) dan tidak memasukkan password admin, masuk sebagai 'Belum Setuju'
        if ($userRole !== 'admin' && $userRole !== 'owner') {
            $adminPassword = trim($this->request->getPost('admin_password') ?? '');
            if (empty($adminPassword) || !$this->verifySuperAdminPassword($adminPassword)) {
                $statusPersetujuan = 'Belum Setuju';
                $userApproved = null;
                $tglApproved = null;
            } else {
                $statusPersetujuan = 'Setuju';
                $userApproved = 'Admin (Otorisasi Password)';
                $tglApproved = date('Y-m-d H:i:s');
            }
        }

        // Catat ke antrean pending harga barang (VB6 frmListPendingHargaBarang & frmUbahHargaBarang)
        $db->table('barang_pending_stok')->insert([
            'ref_id'             => 'UHB-' . date('YmdHis'),
            'tgl_input'          => date('Y-m-d H:i:s'),
            'id_barang'          => $idBarang,
            'nama_barang'        => $barang['nama_barang'],
            'stok_pending'       => (float)($this->request->getPost('stok_pending') ?? 0),
            'harga_beli_lama'    => (float)$barang['harga_beli'],
            'harga_beli'         => $hargaBeliBaru,
            'harga_jual_lama'    => (float)$barang['harga_jual'],
            'harga_jual'         => $hargaJualBaru > 0 ? $hargaJualBaru : (float)$barang['harga_jual'],
            'qty_paket_a'        => (int)$qtyPaketA,
            'harga_paket_a'      => (float)$hrgPaketA,
            'qty_paket_b'        => (int)$qtyPaketB,
            'harga_paket_b'      => (float)$hrgPaketB,
            'qty_paket_c'        => (int)$qtyPaketC,
            'harga_paket_c'      => (float)$hrgPaketC,
            'status_persetujuan' => $statusPersetujuan,
            'status_eksekusi'    => 1,
            'user_input'         => session()->get('nama_user') ?? (session()->get('kode_user') ?? 'KASIR1'),
            'user_approved'      => $userApproved,
            'tgl_approved'       => $tglApproved,
            'catatan'            => $this->request->getPost('catatan') ?? 'Input Penyesuaian Harga Belanja',
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        // Jika statusnya 'Setuju', perbarui juga master barang
        if ($statusPersetujuan === 'Setuju') {
            $updateData = [
                'harga_beli' => $hargaBeliBaru,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($hargaJualBaru > 0) {
                $updateData['harga_jual'] = $hargaJualBaru;
            }
            if ($hargaGrosirBaru > 0) {
                $updateData['harga_grosir'] = $hargaGrosirBaru;
            }
            if ($qtyPaketA > 0 || $hrgPaketA > 0) {
                $updateData['qty_paket_a'] = $qtyPaketA;
                $updateData['hrg_paket_a'] = $hrgPaketA;
            }
            if ($qtyPaketB > 0 || $hrgPaketB > 0) {
                $updateData['qty_paket_b'] = $qtyPaketB;
                $updateData['hrg_paket_b'] = $hrgPaketB;
            }
            if ($qtyPaketC > 0 || $hrgPaketC > 0) {
                $updateData['qty_paket_c'] = $qtyPaketC;
                $updateData['hrg_paket_c'] = $hrgPaketC;
            }

            $db->table('barang')->where('id_barang', $idBarang)->update($updateData);
        }

        $msg = ($statusPersetujuan === 'Setuju')
            ? "Harga Master Produk ({$barang['nama_barang']}) berhasil disetujui & diperbarui (Status: Setuju)!"
            : "Usulan harga baru dicatat ke Antrean Pending dengan status 'Belum Setuju'. Admin dapat mereview & menyetujuinya di menu Pending Update Stok.";

        return $this->response->setJSON([
            'status' => 'success',
            'status_persetujuan' => $statusPersetujuan,
            'message' => $msg,
            'data' => [
                'id_barang' => $idBarang,
                'nama_barang' => $barang['nama_barang'],
                'satuan' => $barang['satuan'],
                'harga_beli' => $hargaBeliBaru,
                'harga_jual' => $updateData['harga_jual'] ?? (float)$barang['harga_jual'],
                'harga_grosir' => $updateData['harga_grosir'] ?? (float)$barang['harga_grosir'],
                'qty_paket_a' => $updateData['qty_paket_a'] ?? (float)$barang['qty_paket_a'],
                'hrg_paket_a' => $updateData['hrg_paket_a'] ?? (float)$barang['hrg_paket_a'],
                'qty_paket_b' => $updateData['qty_paket_b'] ?? (float)$barang['qty_paket_b'],
                'hrg_paket_b' => $updateData['hrg_paket_b'] ?? (float)$barang['hrg_paket_b'],
                'qty_paket_c' => $updateData['qty_paket_c'] ?? (float)$barang['qty_paket_c'],
                'hrg_paket_c' => $updateData['hrg_paket_c'] ?? (float)$barang['hrg_paket_c'],
                'approver' => $approverUser
            ]
        ]);
    }

    // Helper Otorisasi Password Super Admin
    private function verifySuperAdminPassword($password)
    {
        if (empty($password)) return false;

        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->get()->getResultArray();

        foreach ($admins as $adm) {
            if (password_verify($password, $adm['password'])) {
                return true;
            }
        }

        return false;
    }
}
