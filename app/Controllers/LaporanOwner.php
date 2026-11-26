<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\KeuanganModel;
use App\Models\BarangModel;

class LaporanOwner extends BaseController
{
    /**
     * Pastikan hanya ADMIN / OWNER yang boleh mengakses
     */
    private function checkAdminAccess()
    {
        if (session()->get('role') !== 'admin') {
            return false;
        }
        return true;
    }

    public function index()
    {
        if (!$this->checkAdminAccess()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak: Menu Rekap & Aset Toko hanya dapat diakses oleh Akun Pemilik Toko (Admin/Owner).');
        }

        $db = \Config\Database::connect();

        $bulan = $this->request->getGet('bulan') ?: date('m');
        $tahun = $this->request->getGet('tahun') ?: date('Y');
        $filterCabang = $this->request->getGet('id_cabang') ?: 'semua';
        $tabActive = $this->request->getGet('tab') ?: 'profit';

        // Daftar Cabang
        $cabangList = $db->table('cabang')->where('status', 'Aktif')->orderBy('id', 'ASC')->get()->getResultArray();

        // 1. Ambil Data Harian Bulan Terpilih
        $dataHarian = $this->getRekapDataHarian($db, $bulan, $tahun, $filterCabang);

        // 2. Ambil Valuasi Aset Global Toko
        $dataAset = $this->getValuasiAsetGlobal($db);

        // Daftar nama bulan Indonesia
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $data = [
            'title' => 'Rekap Omzet, Profit & Aset Global Toko (Khusus Owner)',
            'bulan' => sprintf('%02d', (int)$bulan),
            'tahun' => (int)$tahun,
            'namaBulan' => $namaBulan,
            'filterCabang' => $filterCabang,
            'cabangList' => $cabangList,
            'rekap' => $dataHarian,
            'aset' => $dataAset,
            'tabActive' => $tabActive,
        ];

        return view('laporan/owner', $data);
    }

    /**
     * Hitung Rekapitulasi Data Harian Sesuai Format Buku Catatan Fisik Owner
     */
    private function getRekapDataHarian($db, $bulan, $tahun, $filterCabang)
    {
        $bulanPad = sprintf('%02d', (int)$bulan);
        $startDate = "{$tahun}-{$bulanPad}-01";
        $daysInMonth = (int)date('t', strtotime("{$tahun}-{$bulanPad}-01"));
        $endDate = "{$tahun}-{$bulanPad}-{$daysInMonth}";

        // Filter Cabang SQL snippet
        $cabangSqlPenjualan = "";
        $cabangSqlKeuangan = "";
        $cabangSqlPembelian = "";
        if (!empty($filterCabang) && $filterCabang !== 'semua') {
            $cabangId = (int)$filterCabang;
            $cabangSqlPenjualan = " AND p.id_cabang = {$cabangId}";
            $cabangSqlKeuangan = " AND k.id_cabang = {$cabangId}";
            $cabangSqlPembelian = " AND pb.id_cabang = {$cabangId}";
        }

        // 1. Query Penjualan per Hari (Gunakan tabel penjualan langsung agar tidak terjadi perkalian baris)
        $qPenjualan = $db->query("
            SELECT 
                DATE(p.tanggal) as tgl,
                COUNT(p.id) as total_trx,
                COALESCE(SUM(p.grand_total), 0) as omzet,
                COALESCE(SUM(CASE WHEN UPPER(p.metode_bayar) LIKE '%QRIS%' THEN p.grand_total ELSE 0 END), 0) as qris,
                COALESCE(SUM(CASE WHEN UPPER(p.metode_bayar) LIKE '%TRF%' OR UPPER(p.metode_bayar) LIKE '%TRANSFER%' THEN p.grand_total ELSE 0 END), 0) as transfer,
                COALESCE(SUM(CASE WHEN UPPER(p.metode_bayar) NOT LIKE '%QRIS%' AND UPPER(p.metode_bayar) NOT LIKE '%TRF%' AND UPPER(p.metode_bayar) NOT LIKE '%TRANSFER%' THEN p.grand_total ELSE 0 END), 0) as cash,
                COALESCE(SUM(p.total_profit), 0) as gross_profit
            FROM penjualan p
            WHERE DATE(p.tanggal) >= '{$startDate}' AND DATE(p.tanggal) <= '{$endDate}' {$cabangSqlPenjualan}
            GROUP BY DATE(p.tanggal)
            ORDER BY DATE(p.tanggal) ASC
        ")->getResultArray();

        $mapPenjualan = [];
        foreach ($qPenjualan as $pj) {
            $omzet = (float)$pj['omzet'];
            $gross = (float)$pj['gross_profit'];
            $hppModal = $omzet - $gross;
            if ($hppModal < 0) {
                $hppModal = 0;
            }
            $pj['hpp_modal'] = $hppModal;
            $pj['gross_profit'] = $gross;
            $mapPenjualan[$pj['tgl']] = $pj;
        }

        // Ambil daftar nama supplier untuk pencocokan otomatis belanja modal
        $supplierList = $db->table('supplier')->select('nama_supplier')->get()->getResultArray();
        $supplierNames = array_map(function($s) {
            return strtolower(trim($s['nama_supplier']));
        }, $supplierList);

        $supplierKeywords = [
            'paket toko', 'bayar paket toko', 'suplier', 'supplier', 'mama farhan', 'farhan',
            'sales', 'restock', 'belanja restock', 'toko mahkota', 'kispray', 'botol', 'jerigen',
            'parfum', 'brm', 'rinso', 'citrun', 'wades', 'siliwangi', 'silih wangi', 'karel',
            'mawar', 'kulakan', 'belanja dari boss', 'chemical boss', 'qela shop',
            'indo sabun', 'bayar baygon', 'bayar barang', 'belanja barang'
        ];

        // 2. Query Pengeluaran (Toko & Pribadi) & Kas Awal dari data_keuangan
        $qKeuangan = $db->query("
            SELECT 
                DATE(k.tanggal) as tgl,
                k.jenis_keuangan,
                k.tipe_pengeluaran,
                k.nama_keuangan,
                k.total
            FROM data_keuangan k
            WHERE DATE(k.tanggal) >= '{$startDate}' AND DATE(k.tanggal) <= '{$endDate}' {$cabangSqlKeuangan}
            ORDER BY k.id ASC
        ")->getResultArray();

        $mapPengeluaran = [];
        $mapKasAwal = [];
        $mapSupplierKeuangan = [];

        foreach ($qKeuangan as $k) {
            $tgl = $k['tgl'];
            if (!isset($mapPengeluaran[$tgl])) {
                $mapPengeluaran[$tgl] = [
                    'toko_items' => [],
                    'toko_total' => 0,
                    'pribadi_items' => [],
                    'pribadi_total' => 0
                ];
            }
            if (!isset($mapKasAwal[$tgl])) {
                $mapKasAwal[$tgl] = 0;
            }
            if (!isset($mapSupplierKeuangan[$tgl])) {
                $mapSupplierKeuangan[$tgl] = [
                    'items' => [],
                    'total' => 0
                ];
            }

            if ($k['jenis_keuangan'] === 'Kas Awal') {
                $mapKasAwal[$tgl] += (float)$k['total'];
            } elseif ($k['jenis_keuangan'] === 'Pengeluaran') {
                $nama = trim($k['nama_keuangan']);
                // Abaikan jika hanya pencatatan non-tunai laci (QRIS / Transfer BANK / Debit BCA) yang bukan pengeluaran riil
                if (in_array(strtoupper($nama), ['QRIS', 'TRANSFER BANK', 'TRANSFER', 'DEBIT EDC BCA', 'EDC', 'DEBIT'])) {
                    continue;
                }

                $nominal = (float)$k['total'];
                $tipe = strtolower($k['tipe_pengeluaran'] ?? 'toko');
                $namaLower = strtolower($nama);

                // A. Klasifikasi Pengeluaran Pribadi / Prive
                $isPribadi = ($tipe === 'pribadi' || strpos($namaLower, 'ibu') !== false || strpos($namaLower, 'pribadi') !== false || strpos($namaLower, 'prive') !== false || strpos($namaLower, 'spp') !== false || strpos($namaLower, 'berobat') !== false || strpos($namaLower, 'baju') !== false);

                // B. Klasifikasi Belanja Barang / Restock Supplier (Perputaran Modal Toko)
                $isSupplier = false;
                if (!$isPribadi) {
                    if ($tipe === 'supplier' || $tipe === 'restock') {
                        $isSupplier = true;
                    } else {
                        foreach ($supplierKeywords as $kw) {
                            if (strpos($namaLower, $kw) !== false) {
                                $isSupplier = true;
                                break;
                            }
                        }
                        if (!$isSupplier) {
                            foreach ($supplierNames as $sup) {
                                if (!empty($sup) && strpos($namaLower, $sup) !== false) {
                                    $isSupplier = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($isPribadi) {
                    $mapPengeluaran[$tgl]['pribadi_items'][] = [
                        'nama' => $nama,
                        'total' => $nominal
                    ];
                    $mapPengeluaran[$tgl]['pribadi_total'] += $nominal;
                } elseif ($isSupplier) {
                    // Masuk ke Belanja Modal Supplier (Tab 2 - Buku Modal & Kas)
                    $mapSupplierKeuangan[$tgl]['items'][] = [
                        'supplier' => $nama,
                        'total' => $nominal
                    ];
                    $mapSupplierKeuangan[$tgl]['total'] += $nominal;
                } else {
                    // Pengeluaran Toko Operasional Murni (Tab 1 - Buku Profit)
                    $mapPengeluaran[$tgl]['toko_items'][] = [
                        'nama' => $nama,
                        'total' => $nominal
                    ];
                    $mapPengeluaran[$tgl]['toko_total'] += $nominal;
                }
            }
        }

        // 3. Query Pembelian / Restock Supplier dari tabel pembelian
        $qPembelian = $db->query("
            SELECT 
                DATE(pb.tanggal) as tgl,
                pb.nama_supplier,
                pb.total_biaya
            FROM pembelian pb
            WHERE DATE(pb.tanggal) >= '{$startDate}' AND DATE(pb.tanggal) <= '{$endDate}' {$cabangSqlPembelian}
            ORDER BY pb.id ASC
        ")->getResultArray();

        $mapPembelian = [];
        foreach ($qPembelian as $pb) {
            $tgl = $pb['tgl'];
            if (!isset($mapPembelian[$tgl])) {
                $mapPembelian[$tgl] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            $tot = (float)$pb['total_biaya'];
            if ($tot > 0) {
                $mapPembelian[$tgl]['items'][] = [
                    'supplier' => $pb['nama_supplier'] ?: 'Supplier',
                    'total' => $tot
                ];
                $mapPembelian[$tgl]['total'] += $tot;
            }
        }

        // Gabungkan belanja supplier dari data_keuangan ke Tab 2 (tanpa menduplikasi nominal yang sama di tanggal yang sama)
        foreach ($mapSupplierKeuangan as $tgl => $kSup) {
            if (!isset($mapPembelian[$tgl])) {
                $mapPembelian[$tgl] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            foreach ($kSup['items'] as $item) {
                $alreadyExists = false;
                foreach ($mapPembelian[$tgl]['items'] as $existing) {
                    if (abs($existing['total'] - $item['total']) < 1) {
                        $alreadyExists = true;
                        break;
                    }
                }
                if (!$alreadyExists) {
                    $mapPembelian[$tgl]['items'][] = $item;
                    $mapPembelian[$tgl]['total'] += $item['total'];
                }
            }
        }

        // 4. Bangun Rekap Baris per Tanggal (1..$daysInMonth) dengan Rolling Balance
        $rows = [];
        $hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $runningProfitAwal = 0;
        $runningCumulativeModal = 0;
        $runningCumulativeOmzet = 0;

        $grandOmzet = 0;
        $grandQRIS = 0;
        $grandTF = 0;
        $grandCash = 0;
        $grandHPP = 0;
        $grandProfitBaru = 0;
        $grandPengeluaranToko = 0;
        $grandPengeluaranPribadi = 0;
        $grandPembelian = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
            $dayOfWeek = date('w', strtotime($dateStr));
            $namaHari = $hariIndonesia[$dayOfWeek];

            // Penjualan
            $pj = $mapPenjualan[$dateStr] ?? null;
            $omzet = $pj ? (float)$pj['omzet'] : 0;
            $qris = $pj ? (float)$pj['qris'] : 0;
            $tf = $pj ? (float)$pj['transfer'] : 0;
            $cash = $pj ? (float)$pj['cash'] : 0;
            $hppModal = $pj ? (float)$pj['hpp_modal'] : 0;
            $profitBaru = $pj ? (float)$pj['gross_profit'] : 0;

            // Pengeluaran
            $peng = $mapPengeluaran[$dateStr] ?? ['toko_items' => [], 'toko_total' => 0, 'pribadi_items' => [], 'pribadi_total' => 0];
            $pengToko = (float)$peng['toko_total'];
            $pengPribadi = (float)$peng['pribadi_total'];

            // Pembelian
            $beli = $mapPembelian[$dateStr] ?? ['items' => [], 'total' => 0];
            $totalBeli = (float)$beli['total'];

            // Kas Awal & Saldo Laci Kasir
            $kasAwal = $mapKasAwal[$dateStr] ?? 0;
            $supplierKas = isset($mapSupplierKeuangan[$dateStr]) ? (float)$mapSupplierKeuangan[$dateStr]['total'] : 0;
            // Saldo Laci Fisik = Kas Awal + Cash Masuk - Beban Toko Tunai - Prive Tunai - Belanja Supplier Tunai
            $saldoLaci = ($kasAwal + $cash) - $pengToko - $pengPribadi - $supplierKas;

            // Rolling Profit (Persis Format Buku Foto 1)
            $profitAwal = $runningProfitAwal;
            $totalProfit = $profitAwal + $profitBaru;
            $profitAkhir = $totalProfit - $pengToko - $pengPribadi;

            // Update rolling balances
            $runningProfitAwal = $profitAkhir;
            $runningCumulativeModal += $hppModal;
            $runningCumulativeOmzet += $omzet;

            // Grand Totals
            $grandOmzet += $omzet;
            $grandQRIS += $qris;
            $grandTF += $tf;
            $grandCash += $cash;
            $grandHPP += $hppModal;
            $grandProfitBaru += $profitBaru;
            $grandPengeluaranToko += $pengToko;
            $grandPengeluaranPribadi += $pengPribadi;
            $grandPembelian += $totalBeli;

            $rows[] = [
                'tgl_num' => $d,
                'tgl_str' => $dateStr,
                'hari' => $namaHari,
                // Buku Foto 1 (Profit & Expense)
                'profit_awal' => $profitAwal,
                'profit_baru' => $profitBaru,
                'total_profit' => $totalProfit,
                'pengeluaran_toko' => $pengToko,
                'pengeluaran_toko_items' => $peng['toko_items'],
                'pengeluaran_pribadi' => $pengPribadi,
                'pengeluaran_pribadi_items' => $peng['pribadi_items'],
                'profit_akhir' => $profitAkhir,
                // Buku Foto 2 (Omzet & Modal / Kas)
                'omzet' => $omzet,
                'qris' => $qris,
                'transfer' => $tf,
                'cash' => $cash,
                'kas_awal' => $kasAwal,
                'saldo_laci' => $saldoLaci,
                'hpp_modal' => $hppModal,
                'total_modal_berjalan' => $runningCumulativeModal,
                'pembelian_items' => $beli['items'],
                'total_pembelian' => $totalBeli,
                'has_data' => ($omzet > 0 || $pengToko > 0 || $pengPribadi > 0 || $totalBeli > 0 || $kasAwal > 0)
            ];
        }

        return [
            'rows' => $rows,
            'summary' => [
                'grand_omzet' => $grandOmzet,
                'grand_qris' => $grandQRIS,
                'grand_tf' => $grandTF,
                'grand_cash' => $grandCash,
                'grand_hpp' => $grandHPP,
                'grand_profit_baru' => $grandProfitBaru,
                'grand_pengeluaran_toko' => $grandPengeluaranToko,
                'grand_pengeluaran_pribadi' => $grandPengeluaranPribadi,
                'grand_profit_bersih_toko' => $grandProfitBaru - $grandPengeluaranToko,
                'grand_profit_akhir' => $grandProfitBaru - $grandPengeluaranToko - $grandPengeluaranPribadi,
                'grand_pembelian' => $grandPembelian,
            ]
        ];
    }

    /**
     * Hitung Valuasi Aset Toko Global (Modal Stok HPP, Nilai Jual Retail, Kas & Margin)
     */
    private function getValuasiAsetGlobal($db)
    {
        // 1. Cabang Aktif
        $cabangs = $db->table('cabang')->where('status', 'Aktif')->orderBy('id', 'ASC')->get()->getResultArray();

        $cabangValuasi = [];
        $globalTotalItem = 0;
        $globalTotalQty = 0;
        $globalNilaiModal = 0;
        $globalNilaiJual = 0;

        foreach ($cabangs as $c) {
            $cabangId = (int)$c['id'];

            $qStok = $db->query("
                SELECT 
                    COUNT(b.id_barang) as total_item,
                    COALESCE(SUM(COALESCE(sc.stok, b.stok)), 0) as total_qty,
                    COALESCE(SUM(COALESCE(sc.stok, b.stok) * b.harga_beli), 0) as total_nilai_modal,
                    COALESCE(SUM(COALESCE(sc.stok, b.stok) * b.harga_jual), 0) as total_nilai_jual
                FROM barang b
                LEFT JOIN stok_cabang sc ON sc.id_barang = b.id_barang AND sc.id_cabang = {$cabangId}
                WHERE b.status = 'Aktif'
            ")->getRowArray();

            $itemCount = (int)($qStok['total_item'] ?? 0);
            $qty = (float)($qStok['total_qty'] ?? 0);
            $nilaiModal = (float)($qStok['total_nilai_modal'] ?? 0);
            $nilaiJual = (float)($qStok['total_nilai_jual'] ?? 0);
            $potensiProfit = $nilaiJual - $nilaiModal;

            // Saldo Kas Berjalan Cabang
            $qKas = $db->query("
                SELECT 
                    COALESCE(SUM(CASE WHEN jenis_keuangan IN ('Pemasukan', 'Kas Awal') THEN total ELSE 0 END), 0) as masuk,
                    COALESCE(SUM(CASE WHEN jenis_keuangan = 'Pengeluaran' THEN total ELSE 0 END), 0) as keluar
                FROM data_keuangan
                WHERE id_cabang = {$cabangId}
            ")->getRowArray();

            $saldoKas = (float)($qKas['masuk'] ?? 0) - (float)($qKas['keluar'] ?? 0);
            if ($saldoKas < 0) $saldoKas = 0;

            $totalAsetCabang = $nilaiModal + $saldoKas;

            $cabangValuasi[] = [
                'id_cabang' => $cabangId,
                'nama_cabang' => $c['nama_cabang'],
                'alamat' => $c['alamat'],
                'total_item' => $itemCount,
                'total_qty' => $qty,
                'nilai_modal' => $nilaiModal,
                'nilai_jual' => $nilaiJual,
                'potensi_profit' => $potensiProfit,
                'saldo_kas' => $saldoKas,
                'total_aset' => $totalAsetCabang
            ];

            $globalTotalItem = max($globalTotalItem, $itemCount);
            $globalTotalQty += $qty;
            $globalNilaiModal += $nilaiModal;
            $globalNilaiJual += $nilaiJual;
        }

        // Hitung Kategori Terbesar (Breakdown Aset per Kategori)
        $kategoriAset = $db->query("
            SELECT 
                COALESCE(k.nama_kategori, 'Tanpa Kategori') as kategori,
                COUNT(b.id_barang) as total_item,
                COALESCE(SUM(b.stok), 0) as total_qty,
                COALESCE(SUM(b.stok * b.harga_beli), 0) as total_modal,
                COALESCE(SUM(b.stok * b.harga_jual), 0) as total_jual
            FROM barang b
            LEFT JOIN kategori k ON k.id = b.kategori_id
            WHERE b.status = 'Aktif'
            GROUP BY k.id, k.nama_kategori
            ORDER BY total_modal DESC
        ")->getResultArray();

        $globalPotensiProfit = $globalNilaiJual - $globalNilaiModal;
        $grandTotalAsetToko = $globalNilaiModal; // HPP Inventory asset

        return [
            'per_cabang' => $cabangValuasi,
            'kategori' => $kategoriAset,
            'global_item' => $globalTotalItem,
            'global_qty' => $globalTotalQty,
            'global_nilai_modal' => $globalNilaiModal,
            'global_nilai_jual' => $globalNilaiJual,
            'global_potensi_profit' => $globalPotensiProfit,
            'grand_total_aset' => $grandTotalAsetToko,
        ];
    }

    /**
     * Simpan Pengeluaran Cepat (Toko / Pribadi) dari Layar Laporan Owner
     */
    public function simpanPengeluaran()
    {
        if (!$this->checkAdminAccess()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Hanya akun Admin / Owner yang berwenang mencatat pengeluaran.'
            ]);
        }

        $db = \Config\Database::connect();

        $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $tipe = $this->request->getPost('tipe_pengeluaran') === 'pribadi' ? 'pribadi' : 'toko';
        $nama = trim($this->request->getPost('nama_keuangan'));
        $total = (float) str_replace(['.', ',', ' '], '', (string)$this->request->getPost('total'));
        $keterangan = trim($this->request->getPost('keterangan') ?: '');
        $idCabang = (int)($this->request->getPost('id_cabang') ?: 1);

        if (empty($nama)) {
            return redirect()->back()->with('error', 'Nama keperluan pengeluaran tidak boleh kosong!');
        }

        if ($total <= 0) {
            return redirect()->back()->with('error', 'Nominal pengeluaran harus lebih besar dari Rp 0!');
        }

        // Simpan ke data_keuangan
        $db->table('data_keuangan')->insert([
            'id_cabang' => $idCabang,
            'tanggal' => $tanggal . ' ' . date('H:i:s'),
            'jenis_keuangan' => 'Pengeluaran',
            'tipe_pengeluaran' => $tipe,
            'nama_keuangan' => $nama,
            'total' => $total,
            'keterangan' => $keterangan,
            'is_locked' => 0,
            'kode_user' => session()->get('kode_user') ?? 'OWNER'
        ]);

        $label = $tipe === 'pribadi' ? 'Pribadi / Prive' : 'Operasional Toko';
        return redirect()->back()->with('success', "Pengeluaran {$label} \"{$nama}\" sebesar Rp " . number_format($total, 0, ',', '.') . " berhasil dicatat!");
    }

    /**
     * Cetak Laporan Rekap Harian Khusus Owner (Print View Bersih)
     */
    public function cetak()
    {
        if (!$this->checkAdminAccess()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $bulan = $this->request->getGet('bulan') ?: date('m');
        $tahun = $this->request->getGet('tahun') ?: date('Y');
        $filterCabang = $this->request->getGet('id_cabang') ?: 'semua';
        $tab = $this->request->getGet('tab') ?: 'profit';

        $dataHarian = $this->getRekapDataHarian($db, $bulan, $tahun, $filterCabang);
        $dataAset = $this->getValuasiAsetGlobal($db);
        $profil = $db->table('profil_toko')->get()->getRowArray();

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return view('laporan/owner_cetak', [
            'title' => 'Cetak Rekap Harian & Aset Owner',
            'bulan' => sprintf('%02d', (int)$bulan),
            'tahun' => (int)$tahun,
            'namaBulan' => $namaBulan,
            'filterCabang' => $filterCabang,
            'rekap' => $dataHarian,
            'aset' => $dataAset,
            'tab' => $tab,
            'profil' => $profil
        ]);
    }

    /**
     * Export Excel
     */
    public function excel()
    {
        if (!$this->checkAdminAccess()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $bulan = $this->request->getGet('bulan') ?: date('m');
        $tahun = $this->request->getGet('tahun') ?: date('Y');
        $filterCabang = $this->request->getGet('id_cabang') ?: 'semua';
        $tab = $this->request->getGet('tab') ?: 'profit';

        $dataHarian = $this->getRekapDataHarian($db, $bulan, $tahun, $filterCabang);
        $dataAset = $this->getValuasiAsetGlobal($db);

        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $filename = "Rekap_Owner_{$namaBulan[$bulan]}_{$tahun}.xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");

        return view('laporan/owner_excel', [
            'bulan' => sprintf('%02d', (int)$bulan),
            'tahun' => (int)$tahun,
            'namaBulan' => $namaBulan,
            'filterCabang' => $filterCabang,
            'rekap' => $dataHarian,
            'aset' => $dataAset,
            'tab' => $tab,
        ]);
    }
}
