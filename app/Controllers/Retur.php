<?php

namespace App\Controllers;

use App\Models\ReturSupplierModel;
use App\Models\BarangModel;
use App\Models\SupplierModel;

class Retur extends BaseController
{
    public function index()
    {
        $returModel = new ReturSupplierModel();
        $barangModel = new BarangModel();
        $supplierModel = new SupplierModel();

        $data = [
            'title' => 'Retur Barang ke Supplier (Rijek / Expired)',
            'no_retur' => $returModel->generateNoRetur(),
            'retur_list' => $returModel->orderBy('tanggal', 'DESC')->findAll(150),
            'suppliers' => $supplierModel->orderBy('nama_supplier', 'ASC')->findAll(),
            'barang_list' => $barangModel->where('status', 'Aktif')->orderBy('nama_barang', 'ASC')->findAll(),
        ];

        return view('retur/index', $data);
    }

    public function simpan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $returModel = new ReturSupplierModel();
        $noRetur = $returModel->generateNoRetur();

        $idBarangRetur = $this->request->getPost('id_barang_retur');
        $qtyRetur = (float) $this->request->getPost('qty_retur');
        $kodeSupplier = $this->request->getPost('kode_supplier');
        $alasan = $this->request->getPost('alasan');
        $statusAwal = $this->request->getPost('status') ?: 'Baru Proses';
        $tipePenyelesaian = $this->request->getPost('tipe_penyelesaian');
        $keterangan = $this->request->getPost('keterangan');

        $barangRetur = $db->table('barang')->where('id_barang', $idBarangRetur)->get()->getRowArray();
        if (!$barangRetur) {
            return redirect()->back()->with('error', 'Barang yang diretur tidak valid.');
        }

        $supplier = $db->table('supplier')->where('kode_supplier', $kodeSupplier)->get()->getRowArray();
        $namaSupplier = $supplier ? $supplier['nama_supplier'] : 'Supplier Umum';

        $nilaiRetur = $qtyRetur * (float)$barangRetur['harga_beli'];

        // 1. MUTASI STOK: Barang rijek/expired SELALU DIPOTONG KELUAR dari stok toko hari ini
        $sisaStokRetur = $barangRetur['stok'] - $qtyRetur;
        $db->table('barang')->where('id_barang', $idBarangRetur)->update(['stok' => $sisaStokRetur]);

        // Catat kartu stok KELUAR untuk barang rijek
        $db->table('kartu_stok')->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'id_barang' => $idBarangRetur,
            'tipe' => 'KELUAR',
            'no_referensi' => $noRetur,
            'masuk' => 0,
            'keluar' => $qtyRetur,
            'sisa' => $sisaStokRetur,
            'keterangan' => 'Retur ke Supplier (' . $alasan . ') - ' . $namaSupplier . ' [' . $statusAwal . ']'
        ]);

        $idBarangGanti = null;
        $namaBarangGanti = null;
        $qtyGanti = 0;
        $nilaiGanti = 0;
        $tglSelesai = null;

        // 2. Jika Langsung Selesai di Hari yang Sama (Supplier langsung membawa pengganti)
        if ($statusAwal === 'Selesai') {
            $tglSelesai = date('Y-m-d H:i:s');

            if ($tipePenyelesaian === 'Ganti_Barang_Sama') {
                $idBarangGanti = $idBarangRetur;
                $namaBarangGanti = $barangRetur['nama_barang'];
                $qtyGanti = (float) ($this->request->getPost('qty_ganti') ?: $qtyRetur);
                $nilaiGanti = $qtyGanti * (float)$barangRetur['harga_beli'];

                $sisaSetelahGanti = $sisaStokRetur + $qtyGanti;
                $db->table('barang')->where('id_barang', $idBarangRetur)->update(['stok' => $sisaSetelahGanti]);

                $db->table('kartu_stok')->insert([
                    'tanggal' => date('Y-m-d H:i:s'),
                    'id_barang' => $idBarangRetur,
                    'tipe' => 'MASUK',
                    'no_referensi' => $noRetur,
                    'masuk' => $qtyGanti,
                    'keluar' => 0,
                    'sisa' => $sisaSetelahGanti,
                    'keterangan' => 'Penggantian Barang Baru dari Retur ' . $noRetur . ' (' . $namaSupplier . ')'
                ]);
            } elseif ($tipePenyelesaian === 'Ganti_Barang_Lain') {
                $idBarangGanti = $this->request->getPost('id_barang_ganti');
                $qtyGanti = (float) $this->request->getPost('qty_ganti');

                $barangGanti = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
                if ($barangGanti) {
                    $namaBarangGanti = $barangGanti['nama_barang'];
                    $nilaiGanti = $qtyGanti * (float)$barangGanti['harga_beli'];

                    $sisaBarangGanti = $barangGanti['stok'] + $qtyGanti;
                    $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $sisaBarangGanti]);

                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $idBarangGanti,
                        'tipe' => 'MASUK',
                        'no_referensi' => $noRetur,
                        'masuk' => $qtyGanti,
                        'keluar' => 0,
                        'sisa' => $sisaBarangGanti,
                        'keterangan' => 'Barang Pengganti dari Retur ' . $noRetur . ' (' . $barangRetur['nama_barang'] . ')'
                    ]);
                }
            } elseif ($tipePenyelesaian === 'Ganti_Uang_Kas') {
                $rawNilaiGanti = $this->request->getPost('nilai_ganti');
                $nilaiGanti = $rawNilaiGanti !== null && $rawNilaiGanti !== '' ? (float) str_replace(['.', ',', ' '], '', (string)$rawNilaiGanti) : (float)$nilaiRetur;
                $db->table('data_keuangan')->insert([
                    'tanggal' => date('Y-m-d H:i:s'),
                    'jenis_keuangan' => 'Pemasukan',
                    'nama_keuangan' => 'Kompensasi Uang Retur ' . $noRetur . ' (' . $namaSupplier . ')',
                    'total' => $nilaiGanti,
                    'keterangan' => 'Penggantian Nilai Uang Retur: ' . $barangRetur['nama_barang'] . ' (' . $qtyRetur . ' ' . $barangRetur['satuan'] . ')',
                    'kode_user' => session()->get('kode_user') ?? 'ADMIN'
                ]);
            }
        } else {
            // Status: Baru Proses (Menunggu Supplier membawa pengganti beberapa hari ke depan)
            $tipePenyelesaian = 'Menunggu Penggantian Supplier';
        }

        // Simpan Record Retur
        $returModel->insert([
            'no_retur' => $noRetur,
            'tanggal' => date('Y-m-d H:i:s'),
            'tgl_selesai' => $tglSelesai,
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $namaSupplier,
            'id_barang_retur' => $idBarangRetur,
            'nama_barang_retur' => $barangRetur['nama_barang'],
            'qty_retur' => $qtyRetur,
            'nilai_retur' => $nilaiRetur,
            'alasan' => $alasan,
            'tipe_penyelesaian' => $tipePenyelesaian,
            'id_barang_ganti' => $idBarangGanti,
            'nama_barang_ganti' => $namaBarangGanti,
            'qty_ganti' => $qtyGanti,
            'nilai_ganti' => $nilaiGanti,
            'status' => $statusAwal,
            'keterangan' => $keterangan,
            'kode_user' => session()->get('kode_user') ?? 'ADMIN',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses data retur barang!');
        }

        $pesan = ($statusAwal === 'Baru Proses')
            ? 'Retur nomor ' . $noRetur . ' dicatat dengan status BARU PROSES. Barang rijek telah dikeluarkan dari stok toko dan menunggu penggantian dari supplier.'
            : 'Transaksi retur nomor ' . $noRetur . ' berhasil diproses dan diselesaikan langsung.';

        return redirect()->to(base_url('retur'))->with('success', $pesan);
    }

    public function selesaikan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $id = $this->request->getPost('id');
        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);

        if (!$retur) {
            return redirect()->back()->with('error', 'Data retur tidak ditemukan.');
        }

        if ($retur['status'] === 'Selesai') {
            return redirect()->back()->with('error', 'Retur ini sudah diselesaikan sebelumnya.');
        }

        $tipePenyelesaian = $this->request->getPost('tipe_penyelesaian');
        $catatanPenyelesaian = $this->request->getPost('catatan_penyelesaian');

        $idBarangGanti = null;
        $namaBarangGanti = null;
        $qtyGanti = 0;
        $nilaiGanti = 0;

        if ($tipePenyelesaian === 'Ganti_Barang_Sama') {
            // Supplier mengganti dengan barang yang sama (barang baru)
            $idBarangGanti = $retur['id_barang_retur'];
            $namaBarangGanti = $retur['nama_barang_retur'];
            $qtyGanti = (float) $this->request->getPost('qty_ganti');

            $barang = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
            $stokBaru = $barang['stok'] + $qtyGanti;
            $nilaiGanti = $qtyGanti * (float)($barang['harga_beli'] ?? 0);

            $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $stokBaru]);

            // Catat Kartu Stok MASUK
            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $idBarangGanti,
                'tipe' => 'MASUK',
                'no_referensi' => $retur['no_retur'],
                'masuk' => $qtyGanti,
                'keluar' => 0,
                'sisa' => $stokBaru,
                'keterangan' => 'Penerimaan Barang Baru Pengganti Retur ' . $retur['no_retur'] . ' (' . $retur['nama_supplier'] . ')'
            ]);
        } elseif ($tipePenyelesaian === 'Ganti_Barang_Lain') {
            // Supplier mengganti dengan barang lain (Barang B)
            $idBarangGanti = $this->request->getPost('id_barang_ganti');
            $qtyGanti = (float) $this->request->getPost('qty_ganti');

            $barangB = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
            if (!$barangB) {
                return redirect()->back()->with('error', 'Barang pengganti tidak valid.');
            }

            $namaBarangGanti = $barangB['nama_barang'];
            $stokBaruB = $barangB['stok'] + $qtyGanti;
            $nilaiGanti = $qtyGanti * (float)($barangB['harga_beli'] ?? 0);

            $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $stokBaruB]);

            // Catat Kartu Stok MASUK
            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $idBarangGanti,
                'tipe' => 'MASUK',
                'no_referensi' => $retur['no_retur'],
                'masuk' => $qtyGanti,
                'keluar' => 0,
                'sisa' => $stokBaruB,
                'keterangan' => 'Penerimaan Barang Lain Pengganti Retur ' . $retur['no_retur'] . ' (' . $retur['nama_barang_retur'] . ')'
            ]);
        } elseif ($tipePenyelesaian === 'Ganti_Uang_Kas') {
            // Supplier mengganti dengan nilai uang (Cash / Potong Tagihan)
            $nilaiGanti = (float) str_replace(['.', ',', ' '], '', (string)$this->request->getPost('nilai_ganti'));

            $db->table('data_keuangan')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'jenis_keuangan' => 'Pemasukan',
                'nama_keuangan' => 'Kompensasi Penggantian Uang Retur ' . $retur['no_retur'],
                'total' => $nilaiGanti,
                'keterangan' => 'Penggantian Nilai Uang Retur ' . $retur['nama_barang_retur'] . ' (' . $retur['qty_retur'] . ') dari ' . $retur['nama_supplier'],
                'kode_user' => session()->get('kode_user') ?? 'ADMIN'
            ]);
        }

        // Update Record Retur Menjadi Selesai
        $returModel->update($id, [
            'status' => 'Selesai',
            'tgl_selesai' => date('Y-m-d H:i:s'),
            'tipe_penyelesaian' => $tipePenyelesaian,
            'id_barang_ganti' => $idBarangGanti,
            'nama_barang_ganti' => $namaBarangGanti,
            'qty_ganti' => $qtyGanti,
            'nilai_ganti' => $nilaiGanti,
            'catatan_penyelesaian' => $catatanPenyelesaian,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses penyelesaian retur barang!');
        }

        return redirect()->to(base_url('retur'))->with('success', 'Retur nomor ' . $retur['no_retur'] . ' telah SELESAI. Stok barang pengganti / kompensasi uang telah masuk ke sistem.');
    }

    // Detail Data Retur (JSON untuk Modal Popup)
    public function detail($id)
    {
        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);

        if (!$retur) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data retur tidak ditemukan!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'retur' => $retur
        ]);
    }

    // Hapus Transaksi Retur Supplier (Otorisasi Super Admin)
    public function hapus($id)
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

        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);
        if (!$retur) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data retur tidak ditemukan!'
            ]);
        }

        $db->transStart();

        // 2. Kembalikan stok barang yang diretur (karena retur dibatalkan/dihapus)
        $barangRetur = $db->table('barang')->where('id_barang', $retur['id_barang_retur'])->get()->getRowArray();
        if ($barangRetur) {
            $stokBaruRetur = $barangRetur['stok'] + (float)$retur['qty_retur'];
            $db->table('barang')->where('id_barang', $retur['id_barang_retur'])->update(['stok' => $stokBaruRetur]);

            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $retur['id_barang_retur'],
                'tipe' => 'MASUK',
                'no_referensi' => $retur['no_retur'],
                'masuk' => (float)$retur['qty_retur'],
                'keluar' => 0,
                'sisa' => $stokBaruRetur,
                'keterangan' => 'Pembatalan Retur Supplier ' . $retur['no_retur'] . ' (Otorisasi Super Admin)'
            ]);
        }

        // 3. Jika status retur sudah SELESAI, batalkan barang pengganti atau uang kompensasi yang sudah diterima
        if ($retur['status'] === 'Selesai') {
            if (in_array($retur['tipe_penyelesaian'], ['Ganti_Barang_Sama', 'Ganti_Barang_Lain']) && !empty($retur['id_barang_ganti'])) {
                $barangGanti = $db->table('barang')->where('id_barang', $retur['id_barang_ganti'])->get()->getRowArray();
                if ($barangGanti) {
                    $stokBaruGanti = max(0, $barangGanti['stok'] - (float)$retur['qty_ganti']);
                    $db->table('barang')->where('id_barang', $retur['id_barang_ganti'])->update(['stok' => $stokBaruGanti]);

                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $retur['id_barang_ganti'],
                        'tipe' => 'KELUAR',
                        'no_referensi' => $retur['no_retur'],
                        'masuk' => 0,
                        'keluar' => (float)$retur['qty_ganti'],
                        'sisa' => $stokBaruGanti,
                        'keterangan' => 'Pembatalan Penerimaan Pengganti Retur ' . $retur['no_retur'] . ' (Otorisasi Super Admin)'
                    ]);
                }
            } elseif ($retur['tipe_penyelesaian'] === 'Ganti_Uang_Kas') {
                $db->table('data_keuangan')->like('nama_keuangan', $retur['no_retur'])->delete();
            }
        }

        // 4. Hapus data dari tabel retur_supplier
        $returModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses pembatalan retur supplier!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Data retur {$retur['no_retur']} berhasil dihapus dan posisi stok barang telah disesuaikan kembali!"
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
