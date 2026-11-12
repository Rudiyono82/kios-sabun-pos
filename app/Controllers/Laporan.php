<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\KeuanganModel;

class Laporan extends BaseController
{
    public function harian()
    {
        $db = \Config\Database::connect();
        $tgl = $this->request->getGet('tanggal') ?? date('Y-m-d');

        $penjualan = $db->table('penjualan')
            ->where("DATE(tanggal)", $tgl)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $keuangan = $db->table('data_keuangan')
            ->where("DATE(tanggal)", $tgl)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $profil = $db->table('profil_toko')->get()->getRowArray();

        // Rekapitulasi
        $totalOmzet = 0;
        $totalProfit = 0;
        $totalQty = 0;
        $totalJerigenTukar = 0;

        foreach ($penjualan as $p) {
            $totalOmzet += (float) $p['grand_total'];
            $totalProfit += (float) $p['total_profit'];
            $totalQty += (float) $p['total_qty'];
            $totalJerigenTukar += (float) $p['potongan_tukar_jerigen'];
        }

        $kasAwal = 0;
        $pengeluaran = 0;
        foreach ($keuangan as $k) {
            if ($k['jenis_keuangan'] === 'Kas Awal') $kasAwal += (float) $k['total'];
            if ($k['jenis_keuangan'] === 'Pengeluaran') $pengeluaran += (float) $k['total'];
        }

        $noLaporan = !empty($totalProfit) ? strtoupper(dechex((int) round($totalProfit))) : '0';

        $data = [
            'title' => 'Laporan Harian Toko',
            'tanggal' => $tgl,
            'no_laporan' => $noLaporan,
            'penjualan' => $penjualan,
            'keuangan' => $keuangan,
            'profil' => $profil,
            'rekap' => [
                'omzet' => $totalOmzet,
                'profit' => $totalProfit,
                'no_laporan' => $noLaporan,
                'qty' => $totalQty,
                'tukar_jerigen' => $totalJerigenTukar,
                'kas_awal' => $kasAwal,
                'pengeluaran' => $pengeluaran,
                'kas_akhir' => $kasAwal + $totalOmzet - $pengeluaran,
            ]
        ];

        return view('laporan/harian', $data);
    }

    public function cetakStrukHarian()
    {
        $db = \Config\Database::connect();
        $tgl = $this->request->getGet('tanggal') ?? date('Y-m-d');

        // Profil Toko
        $profil = $db->table('profil_toko')->get()->getRowArray();

        // Data Penjualan Hari Terpilih
        $penjualan = $db->table('penjualan')
            ->where("DATE(tanggal)", $tgl)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $totalTransaksi = count($penjualan);
        $totalQty = 0;
        $totalOmzet = 0;
        $totalTunai = 0;
        $totalQris = 0;
        $totalTransfer = 0;
        $totalDiskon = 0;
        $totalTukarJerigen = 0;
        $totalVoucher = 0;
        $totalProfit = 0;

        foreach ($penjualan as $p) {
            $grandTotal = (float) $p['grand_total'];
            $totalOmzet += $grandTotal;
            $totalQty += (float) $p['total_qty'];
            $totalProfit += (float) ($p['total_profit'] ?? 0);
            $totalTukarJerigen += (float) ($p['potongan_tukar_jerigen'] ?? 0);
            $totalVoucher += (float) ($p['potongan_voucher'] ?? 0);
            $totalDiskon += (float) ($p['diskon'] ?? 0);

            $metode = strtolower(trim($p['metode_bayar'] ?? 'cash'));
            if (str_contains($metode, 'qris')) {
                $totalQris += $grandTotal;
            } elseif (str_contains($metode, 'trf') || str_contains($metode, 'transfer')) {
                $totalTransfer += $grandTotal;
            } else {
                $totalTunai += $grandTotal;
            }
        }

        // Data Keuangan (Kas Awal, Pengeluaran, Kas Masuk Lain)
        $keuangan = $db->table('data_keuangan')
            ->where("DATE(tanggal)", $tgl)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $kasAwal = 0;
        $tglKasAwal = null;
        $pemasukanLain = 0;
        $listPemasukanLain = [];
        $totalPengeluaran = 0;
        $listPengeluaran = [];

        foreach ($keuangan as $k) {
            $jenis = trim($k['jenis_keuangan'] ?? '');
            $nama = trim($k['nama_keuangan'] ?? '');
            $total = (float) $k['total'];

            if ($jenis === 'Kas Awal' || (stripos($nama, 'KAS') !== false && $jenis === 'Pemasukan' && stripos($nama, 'Penjualan') === false)) {
                $kasAwal += $total;
                if (!$tglKasAwal) $tglKasAwal = $k['tanggal'];
            } elseif ($jenis === 'Pemasukan') {
                // Jangan hitung ulang jika ini adalah sinkronisasi otomatis penjualan
                if (stripos($nama, 'Penjualan') === false && stripos($nama, 'Omzet') === false) {
                    $pemasukanLain += $total;
                    $listPemasukanLain[] = [
                        'nama' => $nama,
                        'total' => $total,
                        'jam' => date('H:i', strtotime($k['tanggal']))
                    ];
                }
            } elseif ($jenis === 'Pengeluaran') {
                $totalPengeluaran += $total;
                $listPengeluaran[] = [
                    'nama' => $nama,
                    'total' => $total,
                    'jam' => date('H:i', strtotime($k['tanggal']))
                ];
            }
        }

        // Rekapitulasi Kas Fisik Laci (Cash on Hand)
        $fisikKasLaci = $kasAwal + $totalTunai + $pemasukanLain - $totalPengeluaran;

        $kasirName = session()->get('nama_user') ?: session()->get('kode_user') ?: 'Kasir';
        $userRole = session()->get('role') ?: 'kasir';

        // Logika Warisan VB6 (DectoHex): Margin profit diselubungkan ke field No. Laporan
        $noLaporan = !empty($totalProfit) ? strtoupper(dechex((int) round($totalProfit))) : '0';
        $profitAsli = $totalProfit;

        // Jika kasir yang mencetak, sembunyikan nilai profit langsung demi keamanan finansial toko
        if ($userRole !== 'admin') {
            $totalProfit = 0;
        }

        $data = [
            'profil' => $profil,
            'tanggal' => $tgl,
            'waktu_cetak' => date('d/m/Y H:i:s'),
            'kasir' => $kasirName,
            'role' => $userRole,
            'no_laporan' => $noLaporan,
            'total_transaksi' => $totalTransaksi,
            'total_qty' => $totalQty,
            'total_omzet' => $totalOmzet,
            'total_tunai' => $totalTunai,
            'total_qris' => $totalQris,
            'total_transfer' => $totalTransfer,
            'total_diskon' => $totalDiskon,
            'total_tukar_jerigen' => $totalTukarJerigen,
            'total_voucher' => $totalVoucher,
            'total_profit' => $totalProfit,
            'profit_asli' => $profitAsli,
            'kas_awal' => $kasAwal,
            'tgl_kas_awal' => $tglKasAwal,
            'pemasukan_lain' => $pemasukanLain,
            'list_pemasukan_lain' => $listPemasukanLain,
            'total_pengeluaran' => $totalPengeluaran,
            'list_pengeluaran' => $listPengeluaran,
            'fisik_kas_laci' => $fisikKasLaci,
            'penjualan' => $penjualan
        ];

        return view('laporan/struk_harian', $data);
    }

    public function kartuStok()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak: Kartu stok & mutasi barang hanya dapat diakses oleh Admin.');
        }

        $db = \Config\Database::connect();
        $idBarang = $this->request->getGet('id_barang') ?? '';

        $barangs = $db->table('barang')->select('id_barang, nama_barang')->orderBy('nama_barang', 'ASC')->get()->getResultArray();

        $builder = $db->table('kartu_stok ks')
            ->select('ks.*, b.nama_barang')
            ->join('barang b', 'b.id_barang = ks.id_barang', 'left');

        if ($idBarang) {
            $builder->where('ks.id_barang', $idBarang);
        }

        $mutasi = $builder->orderBy('ks.id', 'DESC')->limit(100)->get()->getResultArray();

        $data = [
            'title' => 'Kartu Stok & Audit Mutasi',
            'barangs' => $barangs,
            'selectedBarang' => $idBarang,
            'mutasi' => $mutasi,
        ];

        return view('laporan/kartu_stok', $data);
    }

    public function stokOpname()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak: Laporan valuasi aset toko & STO hanya dapat diakses oleh Admin.');
        }

        $db = \Config\Database::connect();

        $tipePeriode = $this->request->getGet('tipe_periode') ?? 'bulan';
        $bulanParam = $this->request->getGet('bulan') ?? date('Y-m');
        $tglAwalParam = $this->request->getGet('tgl_awal') ?? date('Y-m-01');
        $tglAkhirParam = $this->request->getGet('tgl_akhir') ?? date('Y-m-d');
        $filterSelisih = $this->request->getGet('filter_selisih') ?? 'semua';

        // Tentukan range tanggal awal dan akhir
        if ($tipePeriode === 'rentang') {
            $startDate = $tglAwalParam . ' 00:00:00';
            $endDate = $tglAkhirParam . ' 23:59:59';
            $periodeLabel = date('d/m/Y', strtotime($tglAwalParam)) . ' s/d ' . date('d/m/Y', strtotime($tglAkhirParam));
        } else {
            $startDate = date('Y-m-01 00:00:00', strtotime($bulanParam . '-01'));
            $endDate = date('Y-m-t 23:59:59', strtotime($bulanParam . '-01'));
            
            $namaBulan = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            $parts = explode('-', $bulanParam);
            $thn = $parts[0] ?? date('Y');
            $bln = $parts[1] ?? date('m');
            $periodeLabel = 'Bulan ' . ($namaBulan[$bln] ?? $bln) . ' ' . $thn;
        }

        // 1. Profil Toko
        $profil = $db->table('profil_toko')->get()->getRowArray() ?? [];

        // 2. Valuasi Nilai Total Aset Inventaris Toko Saat Ini (Modal HPP & Omzet)
        $asetSummary = $db->table('barang')
            ->select('COUNT(*) as total_sku, 
                      SUM(stok) as total_unit, 
                      SUM(stok * harga_beli) as total_hpp, 
                      SUM(stok * harga_jual) as total_jual')
            ->get()->getRowArray() ?? [];

        $totalSku = (int) ($asetSummary['total_sku'] ?? 0);
        $totalUnit = (float) ($asetSummary['total_unit'] ?? 0);
        $totalHpp = (float) ($asetSummary['total_hpp'] ?? 0);
        $totalJual = (float) ($asetSummary['total_jual'] ?? 0);
        $potensiMargin = $totalJual - $totalHpp;
        $persenMargin = $totalJual > 0 ? ($potensiMargin / $totalJual) * 100 : 0;

        // 3. Breakdown Aset per Kategori Barang
        $kategoriAset = $db->table('barang b')
            ->select("COALESCE(k.nama_kategori, 'Tanpa Kategori') as nama_kategori,
                      COUNT(b.id_barang) as total_sku,
                      SUM(b.stok) as total_unit,
                      SUM(b.stok * b.harga_beli) as total_hpp,
                      SUM(b.stok * b.harga_jual) as total_jual")
            ->join('kategori k', 'k.id = b.kategori_id', 'left')
            ->groupBy('b.kategori_id')
            ->orderBy('total_hpp', 'DESC')
            ->get()->getResultArray();

        // 4. Daftar Sesi Stok Opname pada Periode Terpilih
        $sesiSto = $db->table('stok_opname')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->orderBy('tanggal', 'DESC')
            ->get()->getResultArray();

        // 5. Audit Selisih Barang (Shrinkage & Cash Flow Discrepancy)
        $detailBuilder = $db->table('stok_opname_detail sod')
            ->select("sod.*, 
                      so.tanggal as tgl_sto, 
                      so.status as status_sto, 
                      so.nama_petugas, 
                      b.harga_jual, 
                      b.barcode, 
                      COALESCE(k.nama_kategori, 'Tanpa Kategori') as nama_kategori")
            ->join('stok_opname so', 'so.no_sto = sod.no_sto', 'inner')
            ->join('barang b', 'b.id_barang = sod.id_barang', 'left')
            ->join('kategori k', 'k.id = b.kategori_id', 'left')
            ->where('so.tanggal >=', $startDate)
            ->where('so.tanggal <=', $endDate);

        $allItems = $detailBuilder->orderBy('sod.id', 'DESC')->get()->getResultArray();

        // Agregasi Dampak Selisih STO terhadap Cash Flow & Laba-Rugi
        $totalItemDihitung = count($allItems);
        $countCocok = 0;
        $countMinus = 0;
        $countPlus = 0;
        $unitMinus = 0;
        $unitPlus = 0;
        $rugiShrinkageHpp = 0; // Total Kebocoran Kas / Kerugian Barang Hilang
        $surplusHpp = 0;        // Total Penambahan Aset Tak Tercatat
        $filteredItems = [];

        foreach ($allItems as $item) {
            $selisih = (float) $item['selisih'];
            $nilaiSelisih = (float) $item['nilai_selisih'];

            if ($selisih < 0) {
                $countMinus++;
                $unitMinus += abs($selisih);
                $rugiShrinkageHpp += abs($nilaiSelisih);
            } elseif ($selisih > 0) {
                $countPlus++;
                $unitPlus += $selisih;
                $surplusHpp += $nilaiSelisih;
            } else {
                $countCocok++;
            }

            // Terapkan filter selisih untuk tampilan tabel rincian
            if ($filterSelisih === 'minus' && $selisih >= 0) continue;
            if ($filterSelisih === 'plus' && $selisih <= 0) continue;
            if ($filterSelisih === 'cocok' && $selisih != 0) continue;
            if ($filterSelisih === 'selisih' && $selisih == 0) continue;

            $filteredItems[] = $item;
        }

        $netDiscrepancy = $surplusHpp - $rugiShrinkageHpp;
        $akurasiSto = $totalItemDihitung > 0 ? ($countCocok / $totalItemDihitung) * 100 : 100;

        $data = [
            'title' => 'Laporan Aset Toko & Audit Stok Opname',
            'tipe_periode' => $tipePeriode,
            'bulan' => $bulanParam,
            'tgl_awal' => $tglAwalParam,
            'tgl_akhir' => $tglAkhirParam,
            'periode_label' => $periodeLabel,
            'filter_selisih' => $filterSelisih,
            'profil' => $profil,
            'aset' => [
                'total_sku' => $totalSku,
                'total_unit' => $totalUnit,
                'total_hpp' => $totalHpp,
                'total_jual' => $totalJual,
                'potensi_margin' => $potensiMargin,
                'persen_margin' => $persenMargin,
            ],
            'kategori_aset' => $kategoriAset,
            'sesi_sto' => $sesiSto,
            'audit' => [
                'total_sesi' => count($sesiSto),
                'total_dihitung' => $totalItemDihitung,
                'count_cocok' => $countCocok,
                'count_minus' => $countMinus,
                'count_plus' => $countPlus,
                'unit_minus' => $unitMinus,
                'unit_plus' => $unitPlus,
                'rugi_shrinkage_hpp' => $rugiShrinkageHpp,
                'surplus_hpp' => $surplusHpp,
                'net_discrepancy' => $netDiscrepancy,
                'akurasi' => $akurasiSto,
            ],
            'detail_items' => $filteredItems,
        ];

        return view('laporan/stok_opname', $data);
    }
}

