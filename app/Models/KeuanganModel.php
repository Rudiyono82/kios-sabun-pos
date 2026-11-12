<?php

namespace App\Models;

use CodeIgniter\Model;

class KeuanganModel extends Model
{
    protected $table = 'data_keuangan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal', 'jenis_keuangan', 'nama_keuangan', 'total', 'keterangan', 'kode_user'
    ];

    public function getRingkasanHariIni()
    {
        $today = date('Y-m-d');
        
        $kasAwal = $this->select('COALESCE(SUM(total), 0) as kas')
            ->where('jenis_keuangan', 'Kas Awal')
            ->where("DATE(tanggal)", $today)
            ->first();

        $pemasukan = $this->select('COALESCE(SUM(total), 0) as masuk')
            ->where('jenis_keuangan', 'Pemasukan')
            ->where("DATE(tanggal)", $today)
            ->first();

        $pengeluaran = $this->select('COALESCE(SUM(total), 0) as keluar')
            ->where('jenis_keuangan', 'Pengeluaran')
            ->where("DATE(tanggal)", $today)
            ->first();

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

