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
}

