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
        $today = date('Y-m-d');
        
        $kasBuilder = $this->select('COALESCE(SUM(total), 0) as kas')
            ->where('jenis_keuangan', 'Kas Awal')
            ->where("DATE(tanggal)", $today);
        if (!empty($idCabang)) {
            $kasBuilder->where('id_cabang', (int)$idCabang);
        }
        $kasAwal = $kasBuilder->first();

        $masukBuilder = $this->select('COALESCE(SUM(total), 0) as masuk')
            ->where('jenis_keuangan', 'Pemasukan')
            ->where("DATE(tanggal)", $today);
        if (!empty($idCabang)) {
            $masukBuilder->where('id_cabang', (int)$idCabang);
        }
        $pemasukan = $masukBuilder->first();

        $keluarBuilder = $this->select('COALESCE(SUM(total), 0) as keluar')
            ->where('jenis_keuangan', 'Pengeluaran')
            ->where("DATE(tanggal)", $today);
        if (!empty($idCabang)) {
            $keluarBuilder->where('id_cabang', (int)$idCabang);
        }
        $pengeluaran = $keluarBuilder->first();

        $kas = (float) ($kasAwal['kas'] ?? 0);
        $masuk = (float) ($pemasukan['masuk'] ?? 0);
        $keluar = (float) ($pengeluaran['keluar'] ?? 0);

        return [
            'kas_awal' => $kas,
            'pemasukan' => $masuk,
            'pengeluaran' => $keluar,
            'saldo_akhir' => $kas + $masuk - $keluar
        ];
    }
}

