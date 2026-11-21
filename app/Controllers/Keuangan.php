<?php

namespace App\Controllers;

use App\Models\KeuanganModel;

class Keuangan extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $keuanganModel = new KeuanganModel();

        $tglMulai = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $isAdmin = (session()->get('role') === 'admin');
        $userCabangId = (int)(session()->get('id_cabang') ?: 1);
        $filterCabang = $this->request->getGet('id_cabang');

        $kategoriList = $db->table('kategori_keuangan')->where('is_active', 1)->orderBy('nama_kategori', 'ASC')->get()->getResultArray();

        $builder = $db->table('data_keuangan')
            ->where("DATE(tanggal) >=", $tglMulai)
            ->where("DATE(tanggal) <=", $tglSelesai);

        // Isolasi Cabang
        if (!$isAdmin) {
            $builder->where('id_cabang', $userCabangId);
        } else {
            if (!empty($filterCabang) && $filterCabang !== 'semua') {
                $builder->where('id_cabang', (int)$filterCabang);
            }
        }

        $transaksiList = $builder->orderBy('tanggal', 'DESC')->get()->getResultArray();

        // Hitung Total Masuk & Keluar Periode ini
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalKasAwal = 0;

        foreach ($transaksiList as $t) {
            $nominal = (float)$t['total'];
            $isKasAwal = ($t['jenis_keuangan'] === 'Kas Awal' || stripos($t['nama_keuangan'], 'Kas Awal') !== false || stripos($t['nama_keuangan'], 'Modal Awal') !== false);
            if ($isKasAwal) {
                $totalKasAwal += $nominal;
            } elseif ($t['jenis_keuangan'] === 'Pemasukan') {
                $totalMasuk += $nominal;
            } elseif ($t['jenis_keuangan'] === 'Pengeluaran') {
                $totalKeluar += $nominal;
            }
        }

        // Hitung Saldo Kas Hari Ini terisolasi per cabang
        $ringkasanHariIni = $keuanganModel->getRingkasanHariIni($isAdmin && empty($filterCabang) ? null : ($isAdmin ? (int)$filterCabang : $userCabangId));

        // Hitung Laba Bersih Periode ini (Khusus Admin)
        $penjualanBuilder = $db->table('penjualan')
            ->select('COALESCE(SUM(grand_total), 0) as omzet, COALESCE(SUM(total_profit), 0) as profit_kotor')
            ->where("DATE(tanggal) >=", $tglMulai)
            ->where("DATE(tanggal) <=", $tglSelesai);

        if (!$isAdmin) {
            $penjualanBuilder->where('id_cabang', $userCabangId);
        } else {
            if (!empty($filterCabang) && $filterCabang !== 'semua') {
                $penjualanBuilder->where('id_cabang', (int)$filterCabang);
            }
        }

        $penjualanPeriode = $penjualanBuilder->get()->getRowArray();

        $omzet = (float)($penjualanPeriode['omzet'] ?? 0);
        $profitKotor = (float)($penjualanPeriode['profit_kotor'] ?? 0);
        $labaBersihRiil = $profitKotor - $totalKeluar;

        $cabangList = $isAdmin ? $db->table('cabang')->where('is_aktif', 1)->orderBy('id', 'ASC')->get()->getResultArray() : [];

        $data = [
            'title' => 'Buku Kas & Biaya Operasional Toko',
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'kategori_list' => $kategoriList,
            'transaksi_list' => $transaksiList,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'total_kas_awal' => $totalKasAwal,
            'saldo_kas_hari_ini' => $ringkasanHariIni['saldo_akhir'],
            'omzet_periode' => $omzet,
            'profit_kotor_periode' => $profitKotor,
            'laba_bersih_riil' => $labaBersihRiil,
            'is_admin' => $isAdmin,
            'user_cabang_id' => $userCabangId,
            'filter_cabang' => $filterCabang,
            'cabang_list' => $cabangList,
        ];

        return view('keuangan/index', $data);
    }

    public function simpan()
    {
        $db = \Config\Database::connect();
        
        $jenis = $this->request->getPost('jenis_keuangan');
        $nama = $this->request->getPost('nama_keuangan');
        $total = (float) str_replace(['.', ',', ' '], '', (string)$this->request->getPost('total'));
        $keterangan = $this->request->getPost('keterangan');
        $isLocked = $this->request->getPost('is_locked') ? 1 : 0;
        $idCabang = (int)(session()->get('id_cabang') ?: 1);

        if ($total <= 0) {
            return redirect()->back()->with('error', 'Nominal transaksi harus lebih dari 0.');
        }

        // Cegah Transaksi Duplikat (Double Click / Debounce 3 Detik)
        $threeSecAgo = date('Y-m-d H:i:s', time() - 3);
        $recentDuplicate = $db->table('data_keuangan')
            ->where('id_cabang', $idCabang)
            ->where('jenis_keuangan', $jenis)
            ->where('nama_keuangan', $nama)
            ->where('total', $total)
            ->where('tanggal >=', $threeSecAgo)
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if ($recentDuplicate) {
            return redirect()->to(base_url('keuangan'))->with('success', 'Transaksi kas operasional berhasil dicatat!');
        }

        $tipePengeluaran = $this->request->getPost('tipe_pengeluaran') === 'pribadi' ? 'pribadi' : 'toko';
        $tglCustom = $this->request->getPost('tanggal') ? ($this->request->getPost('tanggal') . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');

        $db->table('data_keuangan')->insert([
            'id_cabang' => $idCabang,
            'tanggal' => $tglCustom,
            'jenis_keuangan' => $jenis,
            'tipe_pengeluaran' => $tipePengeluaran,
            'nama_keuangan' => $nama,
            'total' => $total,
            'keterangan' => $keterangan,
            'is_locked' => $isLocked,
            'kode_user' => session()->get('kode_user') ?? 'KASIR1'
        ]);

        return redirect()->to(base_url('keuangan'))->with('success', 'Transaksi kas operasional berhasil dicatat!');
    }

    public function hapus($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('data_keuangan')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Catatan kas tidak ditemukan!'
            ]);
        }

        // 1. Kas awal laci kasir tidak bisa dihapus
        if ($row['jenis_keuangan'] === 'Kas Awal') {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Kas awal laci kasir adalah modal dasar pembukaan toko dan TIDAK DAPAT dihapus!'
            ]);
        }

        // 2. Pemasukan dari penjualan kasir tidak bisa dihapus dari buku kas
        $nama = $row['nama_keuangan'];
        $ket = $row['keterangan'] ?? '';
        if ($row['jenis_keuangan'] === 'Pemasukan' && (stripos($nama, 'Penjualan') !== false || stripos($nama, 'J2') !== false || stripos($ket, 'Kasir') !== false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Pemasukan dari transaksi kasir tidak dapat dihapus manual! Jika terjadi kesalahan, batalkan faktur dari menu Daftar Faktur Penjualan.'
            ]);
        }

        $isCurrentAdmin = (session()->get('role') === 'admin');
        $userCabangId = (int)(session()->get('id_cabang') ?: 1);

        if (!$isCurrentAdmin && (int)($row['id_cabang'] ?? 1) !== $userCabangId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak! Anda tidak berhak menghapus catatan kas milik cabang lain.'
            ]);
        }

        // 3. Item pengeluaran/pemasukan yang ditandai flag khusus (is_locked = 1) HANYA bisa dihapus oleh akun admin langsung
        if (!empty($row['is_locked'])) {
            if (!$isCurrentAdmin) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Catatan ini ditandai dengan FLAG KHUSUS ADMIN. Hanya user Administrator yang sedang login yang berhak menghapus data ini!'
                ]);
            }
        } else {
            // Jika bukan admin dan tidak terkunci khusus, verifikasi password Super Admin
            if (!$isCurrentAdmin) {
                $password = trim($this->request->getPost('password_admin') ?? '');
                if (!$this->verifySuperAdminPassword($password)) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => 'Akses ditolak! Password Super Admin salah.'
                    ]);
                }
            }
        }

        $db->table('data_keuangan')->where('id', $id)->delete();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Catatan kas '{$row['nama_keuangan']}' senilai Rp " . number_format($row['total'], 0, ',', '.') . " berhasil dihapus!"
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

