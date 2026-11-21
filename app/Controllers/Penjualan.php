<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\BarangModel;

class Penjualan extends BaseController
{
    // 1. Tampilkan Daftar List Faktur Penjualan
    public function index()
    {
        $db = \Config\Database::connect();

        $tglMulai = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');
        $metodeBayar = $this->request->getGet('metode_bayar') ?: '';
        $jenisPembeli = $this->request->getGet('jenis_pembeli') ?: '';

        $isAdmin = (session()->get('role') === 'admin');
        $userCabangId = (int)(session()->get('id_cabang') ?: 1);
        $filterCabang = $this->request->getGet('id_cabang');

        $builder = $db->table('penjualan')
            ->where("DATE(tanggal) >=", $tglMulai)
            ->where("DATE(tanggal) <=", $tglSelesai);

        // Isolasi Cabang: Kasir hanya bisa melihat transaksi cabangnya sendiri
        if (!$isAdmin) {
            $builder->where('id_cabang', $userCabangId);
        } else {
            if (!empty($filterCabang) && $filterCabang !== 'semua') {
                $builder->where('id_cabang', (int)$filterCabang);
            }
        }

        if (!empty($metodeBayar)) {
            $builder->where('metode_bayar', $metodeBayar);
        }

        if ($jenisPembeli === 'member') {
            $builder->where('kode_member !=', '')->where('kode_member IS NOT NULL');
        } elseif ($jenisPembeli === 'umum') {
            $builder->groupStart()
                ->where('kode_member', '')
                ->orWhere('kode_member IS NULL')
                ->groupEnd();
        }

        $list = $builder->orderBy('tanggal', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();

        // Ambil daftar cabang untuk filter Admin
        $cabangList = $isAdmin ? $db->table('cabang')->where('is_aktif', 1)->orderBy('id', 'ASC')->get()->getResultArray() : [];

        // Hitung Metrik Ringkasan
        $totalOmset = 0;
        $totalQty = 0;
        $totalPotonganJerigen = 0;
        $totalPotonganVoucher = 0;

        foreach ($list as $p) {
            $totalOmset += (float)$p['grand_total'];
            $totalQty += (float)$p['total_qty'];
            $totalPotonganJerigen += (float)($p['potongan_tukar_jerigen'] ?? 0);
            $totalPotonganVoucher += (float)($p['potongan_voucher'] ?? 0);
        }

        $data = [
            'title' => 'Daftar Faktur Penjualan Kasir',
            'list' => $list,
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'metode_bayar' => $metodeBayar,
            'jenis_pembeli' => $jenisPembeli,
            'is_admin' => $isAdmin,
            'user_cabang_id' => $userCabangId,
            'filter_cabang' => $filterCabang,
            'cabang_list' => $cabangList,
            'total_omset' => $totalOmset,
            'total_transaksi' => count($list),
            'total_qty' => $totalQty,
            'total_potongan_jerigen' => $totalPotonganJerigen,
            'total_potongan_voucher' => $totalPotonganVoucher,
        ];

        return view('penjualan/index', $data);
    }

    // 2. Detail Item Faktur Penjualan (JSON untuk Modal Popup)
    public function detail($noFaktur)
    {
        $db = \Config\Database::connect();
        $penjualan = $db->table('penjualan')->where('no_faktur', $noFaktur)->get()->getRowArray();

        if (!$penjualan) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Faktur penjualan tidak ditemukan!'
            ]);
        }

        // Branch Isolation: Kasir tidak boleh melihat detail faktur cabang lain
        $isAdmin = (session()->get('role') === 'admin');
        $userCabangId = (int)(session()->get('id_cabang') ?: 1);
        if (!$isAdmin && (int)($penjualan['id_cabang'] ?? 1) !== $userCabangId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak! Faktur penjualan ini milik cabang lain.'
            ]);
        }

        // Ambil data member jika ada
        $member = null;
        if (!empty($penjualan['kode_member'])) {
            $member = $db->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
        }

        $items = $db->table('penjualan_detail')->where('no_faktur', $noFaktur)->get()->getResultArray();

        // Keamanan Finansial: Sembunyikan margin profit dari akun kasir (harga beli boleh dilihat untuk operasional pembelian & retur)
        if (!$isAdmin) {
            unset($penjualan['total_profit']);
            foreach ($items as &$it) {
                unset($it['profit']);
            }
            unset($it);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'penjualan' => $penjualan,
            'member' => $member,
            'items' => $items
        ]);
    }

    // 2.5 Hapus Per Item Penjualan (Kembalikan Stok & Rekalkulasi Total Faktur)
    public function hapusItem($idDetail)
    {
        $db = \Config\Database::connect();

        // Cek otorisasi Super Admin jika bukan role admin
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

        // Ambil item yang akan dihapus
        $item = $db->table('penjualan_detail')->where('id', $idDetail)->get()->getRowArray();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Item produk dalam faktur ini tidak ditemukan atau sudah dihapus!'
            ]);
        }

        $noFaktur = $item['no_faktur'];
        $penjualan = $db->table('penjualan')->where('no_faktur', $noFaktur)->get()->getRowArray();
        if (!$penjualan) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Faktur induk ' . $noFaktur . ' tidak ditemukan!'
            ]);
        }

        $userCabangId = (int)(session()->get('id_cabang') ?: 1);
        if (!$isCurrentAdmin && (int)($penjualan['id_cabang'] ?? 1) !== $userCabangId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak! Anda tidak dapat mengubah faktur milik cabang lain.'
            ]);
        }

        $db->transStart();

        // 1. Pulihkan stok barang di cabang bersangkutan & master
        $idFakturCabang = (int)($penjualan['id_cabang'] ?? 1);
        $stokCabangModel = new \App\Models\StokCabangModel();
        $stokBaru = $stokCabangModel->updateStok($item['id_barang'], $idFakturCabang, (float)$item['qty']);
        $db->query("UPDATE barang SET stok = (SELECT COALESCE(SUM(stok), 0) FROM stok_cabang WHERE id_barang = ?) WHERE id_barang = ?", [$item['id_barang'], $item['id_barang']]);

        // Catat Kartu Stok MASUK
        $db->table('kartu_stok')->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'id_barang' => $item['id_barang'],
            'tipe' => 'MASUK',
            'no_referensi' => $noFaktur,
            'masuk' => (float)$item['qty'],
            'keluar' => 0,
            'sisa' => $stokBaru,
            'keterangan' => 'Hapus Item [' . $item['nama_barang'] . '] dari Faktur ' . $noFaktur . ' (Cabang #' . $idFakturCabang . ')'
        ]);

        // Cek sisa item lainnya pada faktur ini
        $sisaItem = $db->table('penjualan_detail')
            ->where('no_faktur', $noFaktur)
            ->where('id !=', $idDetail)
            ->get()->getResultArray();

        $isFakturDeleted = false;

        if (empty($sisaItem)) {
            // Jika ini item terakhir, hapus seluruh faktur
            if (!empty($penjualan['kode_member'])) {
                $member = $db->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
                if ($member) {
                    $grandTotal = (float)$penjualan['grand_total'];
                    $pointKurang = floor($grandTotal / 10000);
                    $pointBaru = max(0, (float)$member['point'] - $pointKurang);
                    $belanjaBaru = max(0, (float)$member['total_belanja'] - $grandTotal);
                    $db->table('member')->where('kode_member', $penjualan['kode_member'])->update([
                        'point' => $pointBaru,
                        'total_belanja' => $belanjaBaru
                    ]);
                }

                if (!empty($penjualan['kode_voucher'])) {
                    $db->table('voucher')->where('kode_voucher', $penjualan['kode_voucher'])->update([
                        'is_aktif' => 1,
                        'tgl_terpakai' => null,
                        'no_faktur_terpakai' => null
                    ]);
                }
            }

            $db->table('data_keuangan')->like('nama_keuangan', $noFaktur)->delete();
            $db->table('penjualan_detail')->where('id', $idDetail)->delete();
            $db->table('penjualan')->where('no_faktur', $noFaktur)->delete();
            $isFakturDeleted = true;
        } else {
            // Hitung ulang totalan faktur
            $newTotalItem = count($sisaItem);
            $newTotalQty = 0;
            $newSubtotal = 0;
            $newTotalProfit = 0;

            foreach ($sisaItem as $si) {
                $newTotalQty += (float)$si['qty'];
                $newSubtotal += (float)$si['subtotal'];
                $newTotalProfit += (float)$si['profit'];
            }

            $potonganJerigen = (float)($penjualan['potongan_tukar_jerigen'] ?? 0);
            $potonganVoucher = (float)($penjualan['potongan_voucher'] ?? 0);
            $newGrandTotal = max(0, $newSubtotal - $potonganJerigen - $potonganVoucher);
            $bayar = (float)$penjualan['bayar'];
            $newKembalian = max(0, $bayar - $newGrandTotal);

            // Update master penjualan
            $db->table('penjualan')->where('no_faktur', $noFaktur)->update([
                'total_item' => $newTotalItem,
                'total_qty' => $newTotalQty,
                'subtotal' => $newSubtotal,
                'grand_total' => $newGrandTotal,
                'total_profit' => $newTotalProfit,
                'kembalian' => $newKembalian
            ]);

            // Sesuaikan catatan arus kas di data_keuangan
            $db->table('data_keuangan')->like('nama_keuangan', $noFaktur)->update([
                'total' => $newGrandTotal
            ]);

            // Sesuaikan poin & riwayat belanja member jika ada
            if (!empty($penjualan['kode_member'])) {
                $member = $db->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
                if ($member) {
                    $oldGrandTotal = (float)$penjualan['grand_total'];
                    $oldPoints = floor($oldGrandTotal / 10000);
                    $newPoints = floor($newGrandTotal / 10000);
                    $diffPoints = $oldPoints - $newPoints;

                    $pointBaru = max(0, (float)$member['point'] - $diffPoints);
                    $belanjaBaru = max(0, (float)$member['total_belanja'] - (float)$item['subtotal']);

                    $db->table('member')->where('kode_member', $penjualan['kode_member'])->update([
                        'point' => $pointBaru,
                        'total_belanja' => $belanjaBaru
                    ]);
                }
            }

            // Hapus detail item
            $db->table('penjualan_detail')->where('id', $idDetail)->delete();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus item produk dari faktur penjualan!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Item [{$item['nama_barang']}] sebanyak {$item['qty']} berhasil dihapus dari faktur {$noFaktur}, stok gudang dikembalikan!",
            'is_faktur_deleted' => $isFakturDeleted,
            'no_faktur' => $noFaktur
        ]);
    }

    // 3. Hapus Faktur Penjualan (Otorisasi Super Admin)
    public function hapus($noFaktur)
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

        $penjualan = $db->table('penjualan')->where('no_faktur', $noFaktur)->get()->getRowArray();
        if (!$penjualan) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Faktur penjualan tidak ditemukan!'
            ]);
        }

        $userCabangId = (int)(session()->get('id_cabang') ?: 1);
        if (!$isCurrentAdmin && (int)($penjualan['id_cabang'] ?? 1) !== $userCabangId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak! Anda tidak dapat membatalkan faktur milik cabang lain.'
            ]);
        }

        $db->transStart();

        // 2. Kembalikan stok setiap barang yang terjual (karena transaksi dihapus/dibatalkan)
        $idFakturCabang = (int)($penjualan['id_cabang'] ?? 1);
        $stokCabangModel = new \App\Models\StokCabangModel();
        $items = $db->table('penjualan_detail')->where('no_faktur', $noFaktur)->get()->getResultArray();
        foreach ($items as $item) {
            $stokBaru = $stokCabangModel->updateStok($item['id_barang'], $idFakturCabang, (float)$item['qty']);
            $db->query("UPDATE barang SET stok = (SELECT COALESCE(SUM(stok), 0) FROM stok_cabang WHERE id_barang = ?) WHERE id_barang = ?", [$item['id_barang'], $item['id_barang']]);

            // Catat Kartu Stok MASUK (Pembatalan Penjualan)
            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $item['id_barang'],
                'tipe' => 'MASUK',
                'no_referensi' => $noFaktur,
                'masuk' => (float)$item['qty'],
                'keluar' => 0,
                'sisa' => $stokBaru,
                'keterangan' => 'Pembatalan Faktur ' . $noFaktur . ' (Cabang #' . $idFakturCabang . ')'
            ]);
        }

        // 3. Jika Transaksi Member: Kembalikan Poin & Aktifkan Kembali Voucher yang Terpakai
        if (!empty($penjualan['kode_member'])) {
            $member = $db->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
            if ($member) {
                $grandTotal = (float)$penjualan['grand_total'];
                $pointKurang = floor($grandTotal / 10000);

                $pointBaru = max(0, (float)$member['point'] - $pointKurang);
                $belanjaBaru = max(0, (float)$member['total_belanja'] - $grandTotal);

                $db->table('member')->where('kode_member', $penjualan['kode_member'])->update([
                    'point' => $pointBaru,
                    'total_belanja' => $belanjaBaru
                ]);
            }

            // Jika transaksi menggunakan voucher belanja, pulihkan kembali status voucher agar tidak hangus
            if (!empty($penjualan['kode_voucher'])) {
                $db->table('voucher')->where('kode_voucher', $penjualan['kode_voucher'])->update([
                    'is_aktif' => 1,
                    'tgl_terpakai' => null,
                    'no_faktur_terpakai' => null
                ]);
            }
        }

        // 4. Hapus catatan kas di data_keuangan jika ada
        $db->table('data_keuangan')->like('nama_keuangan', $noFaktur)->delete();

        // 5. Hapus detail & master penjualan
        $db->table('penjualan_detail')->where('no_faktur', $noFaktur)->delete();
        $db->table('penjualan')->where('no_faktur', $noFaktur)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus faktur penjualan!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Faktur penjualan {$noFaktur} berhasil dihapus, stok barang dipulihkan, dan data member telah disesuaikan kembali!"
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

