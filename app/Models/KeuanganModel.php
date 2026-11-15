<?php

namespace App\Models;

use CodeIgniter\Model;

class KeuanganModel extends Model
{
    protected $table = 'data_keuangan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal', 'jenis_keuangan', 'nama_keuangan', 'total', 'keterangan', 'kode_user', 'id_cabang', 'is_locked'
    ];

    public function getRingkasanHariIni($idCabang = null)
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        
        // 1. Kas Awal Hari Ini (Mendukung 'Kas Awal', 'Uang Kas Awal', maupun 'Modal Awal')
        $kasBuilder = $db->table('data_keuangan')
            ->select('COALESCE(SUM(total), 0) as kas')
            ->where("DATE(tanggal)", $today)
            ->groupStart()
                ->where('jenis_keuangan', 'Kas Awal')
                ->orGroupStart()
                    ->like('nama_keuangan', 'Kas Awal')
                ->groupEnd()
                ->orGroupStart()
                    ->like('nama_keuangan', 'Modal Awal')
                ->groupEnd()
            ->groupEnd();
        if (!empty($idCabang)) {
            $kasBuilder->where('id_cabang', (int)$idCabang);
        }
        $kasAwalRow = $kasBuilder->get()->getRowArray();
        $kasAwal = (float)($kasAwalRow['kas'] ?? 0);

        // 2. Penjualan Tunai / Cash Hari Ini (Uang fisik riil masuk ke laci kasir)
        $penjualanBuilder = $db->table('penjualan')
            ->select("COALESCE(SUM(grand_total), 0) as total_tunai")
            ->where("DATE(tanggal)", $today)
            ->whereIn('metode_bayar', ['Cash', 'Tunai']);
        if (!empty($idCabang)) {
            $penjualanBuilder->where('id_cabang', (int)$idCabang);
        }
        $penjualanRow = $penjualanBuilder->get()->getRowArray();
        $penjualanTunai = (float)($penjualanRow['total_tunai'] ?? 0);

        // 3. Pemasukan Kas Lainnya Hari Ini (di luar kas awal & di luar omzet penjualan agar tidak dobel)
        $masukBuilder = $db->table('data_keuangan')
            ->select('COALESCE(SUM(total), 0) as masuk')
            ->where('jenis_keuangan', 'Pemasukan')
            ->where("DATE(tanggal)", $today)
            ->notLike('nama_keuangan', 'Kas Awal')
            ->notLike('nama_keuangan', 'Modal Awal')
            ->notLike('nama_keuangan', 'Total Omzet')
            ->notLike('nama_keuangan', 'Total Profit')
            ->notLike('nama_keuangan', 'Penjualan');
        if (!empty($idCabang)) {
            $masukBuilder->where('id_cabang', (int)$idCabang);
        }
        $pemasukanRow = $masukBuilder->get()->getRowArray();
        $pemasukanLain = (float)($pemasukanRow['masuk'] ?? 0);

        // 4. Pengeluaran Riil Kas Toko (Abaikan catatan non-tunai settlement seperti QRIS / Transfer BANK / EDC)
        $keluarBuilder = $db->table('data_keuangan')
            ->select('COALESCE(SUM(total), 0) as keluar')
            ->where('jenis_keuangan', 'Pengeluaran')
            ->where("DATE(tanggal)", $today)
            ->whereNotIn('UPPER(TRIM(nama_keuangan))', ['QRIS', 'TRANSFER BANK', 'DEBIT EDC BCA', 'EDC']);
        if (!empty($idCabang)) {
            $keluarBuilder->where('id_cabang', (int)$idCabang);
        }
        $pengeluaranRow = $keluarBuilder->get()->getRowArray();
        $pengeluaranRiil = (float)($pengeluaranRow['keluar'] ?? 0);

        // Saldo Kas Fisik di Toko (Laci Kasir) = Kas Awal + Penjualan Tunai + Pemasukan Lain - Pengeluaran Riil
        $saldoAkhir = $kasAwal + $penjualanTunai + $pemasukanLain - $pengeluaranRiil;

        return [
            'kas_awal' => $kasAwal,
            'penjualan_tunai' => $penjualanTunai,
            'pemasukan' => $pemasukanLain,
            'pengeluaran' => $pengeluaranRiil,
            'saldo_akhir' => $saldoAkhir
        ];
    }
}

