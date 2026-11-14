<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\BarangModel;
use App\Models\MemberModel;
use App\Models\KeuanganModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $penjualanModel = new PenjualanModel();
        $barangModel = new BarangModel();
        $memberModel = new MemberModel();
        $keuanganModel = new KeuanganModel();

        $summaryHariIni = $penjualanModel->getSummaryHariIni();
        $grafik7Hari = $penjualanModel->getGrafikPenjualan7Hari();
        $topProduk = $penjualanModel->getTopProdukTerlaris(5);
        $stokMenipis = $barangModel->getStokMenipis();
        $topMember = $memberModel->getTopMember(5);
        $ringkasanKas = $keuanganModel->getRingkasanHariIni();

        // Data total master
        $totalBarang = $barangModel->countAllResults();
        $totalMember = $memberModel->countAllResults();

        $data = [
            'title' => 'Dashboard Operasional',
            'summary' => $summaryHariIni,
            'grafik' => $grafik7Hari,
            'topProduk' => $topProduk,
            'stokMenipis' => $stokMenipis,
            'topMember' => $topMember,
            'kas' => $ringkasanKas,
            'totalBarang' => $totalBarang,
            'totalMember' => $totalMember,
        ];

        return view('dashboard/index', $data);
    }

    public function grafik()
    {
        $penjualanModel = new PenjualanModel();
        $periode = $this->request->getGet('periode') ?? '7_hari';
        $data = $penjualanModel->getGrafikPenjualan($periode);

        return $this->response->setJSON([
            'status'    => 'success',
            'periode'   => $periode,
            'labels'    => array_column($data, 'label'),
            'omzet'     => array_column($data, 'omzet'),
            'profit'    => array_column($data, 'profit'),
            'transaksi' => array_column($data, 'transaksi'),
        ]);
    }

    public function liveKasir()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya Administrator/Pemilik yang memiliki akses memantau kinerja kasir live!');
        }

        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        // Ambil seluruh user dengan role kasir dan admin yang bertugas
        $users = $db->table('users as u')
            ->select('u.id, u.kode_user, u.nama_user, u.role, u.is_active, u.id_cabang, u.last_login, c.nama_cabang, c.kode_cabang')
            ->join('cabang as c', 'c.id = u.id_cabang', 'left')
            ->where('u.is_active', 1)
            ->whereIn('u.role', ['kasir', 'admin'])
            ->orderBy('u.role', 'ASC')
            ->orderBy('u.nama_user', 'ASC')
            ->get()->getResultArray();

        $kasirPerformance = [];
        $totalKasirAktif = 0;
        $totalOmsetHariIni = 0;
        $totalKasLaciSemua = 0;
        $totalTransaksiHariIni = 0;

        foreach ($users as $u) {
            $kodeUser = $u['kode_user'];

            // 1. Data Penjualan Hari Ini
            $salesToday = $db->table('penjualan')
                ->where('kode_user', $kodeUser)
                ->where("DATE(tanggal) =", $today)
                ->select("COUNT(id) as total_trx, COALESCE(SUM(grand_total), 0) as total_omset, COALESCE(SUM(CASE WHEN metode_bayar = 'Tunai' THEN grand_total ELSE 0 END), 0) as total_tunai")
                ->get()->getRowArray();

            $trxCount   = (int)($salesToday['total_trx'] ?? 0);
            $omset      = (float)($salesToday['total_omset'] ?? 0);
            $omsetTunai = (float)($salesToday['total_tunai'] ?? 0);

            $totalTransaksiHariIni += $trxCount;
            $totalOmsetHariIni += $omset;

            // 2. Data Kas Awal Laci Hari Ini
            $kasAwalRow = $db->table('data_keuangan')
                ->where('kode_user', $kodeUser)
                ->where("DATE(tanggal) =", $today)
                ->like('nama_keuangan', 'Kas Awal')
                ->select("COALESCE(SUM(total), 0) as total_kas_awal")
                ->get()->getRowArray();
            $kasAwal = (float)($kasAwalRow['total_kas_awal'] ?? 0);

            // 3. Estimasi Kas di Laci Kasir = Kas Awal + Penjualan Tunai
            $kasDiLaci = $kasAwal + $omsetTunai;
            $totalKasLaciSemua += $kasDiLaci;

            // 4. Aktivitas Terakhir (dari audit_logs atau penjualan)
            $lastActivity = $db->table('audit_logs')
                ->where('kode_user', $kodeUser)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            $lastTrx = $db->table('penjualan')
                ->where('kode_user', $kodeUser)
                ->where("DATE(tanggal) =", $today)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            // Hitung status: Aktif / Standby / Offline
            $status = 'offline';
            $statusLabel = 'Offline / Tidak Bertugas';
            $statusBadge = 'secondary';
            $waktuAktivitas = null;

            if ($lastActivity) {
                $lastTime = strtotime($lastActivity['created_at']);
                $diffMinutes = (time() - $lastTime) / 60;
                $waktuAktivitas = $lastActivity['created_at'];

                if ($diffMinutes <= 15) {
                    $status = 'aktif';
                    $statusLabel = 'Aktif Melayani Pelanggan';
                    $statusBadge = 'success';
                    $totalKasirAktif++;
                } elseif (date('Y-m-d', $lastTime) === $today) {
                    $status = 'standby';
                    $statusLabel = 'Standby / Siaga di Kasir';
                    $statusBadge = 'warning';
                }
            } elseif ($trxCount > 0) {
                $status = 'standby';
                $statusLabel = 'Standby / Siaga di Kasir';
                $statusBadge = 'warning';
            }

            $kasirPerformance[] = [
                'user'            => $u,
                'status'          => $status,
                'status_label'    => $statusLabel,
                'status_badge'    => $statusBadge,
                'total_trx'       => $trxCount,
                'total_omset'     => $omset,
                'kas_awal'        => $kasAwal,
                'kas_di_laci'     => $kasDiLaci,
                'last_activity'   => $lastActivity,
                'last_trx'        => $lastTrx,
                'waktu_aktivitas' => $waktuAktivitas,
            ];
        }

        $data = [
            'title'                 => 'Pantau Kinerja Kasir Real-Time (Live)',
            'kasirPerformance'      => $kasirPerformance,
            'totalKasir'            => count($users),
            'totalKasirAktif'       => $totalKasirAktif,
            'totalOmsetHariIni'     => $totalOmsetHariIni,
            'totalKasLaciSemua'     => $totalKasLaciSemua,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
        ];

        return view('dashboard/live_kasir', $data);
    }
}
