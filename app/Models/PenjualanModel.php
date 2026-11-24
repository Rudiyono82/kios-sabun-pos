<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanModel extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'no_faktur', 'tanggal', 'kode_user', 'nama_kasir', 'kode_member',
        'total_item', 'total_qty', 'subtotal', 'diskon', 'potongan_tukar_jerigen',
        'grand_total', 'bayar', 'kembalian', 'metode_bayar', 'no_kartu',
        'total_profit', 'catatan', 'created_at', 'kode_voucher', 'potongan_voucher', 'id_cabang'
    ];

    public function generateNoFaktur()
    {
        $prefix = 'J' . date('ymd');
        $last = $this->like('no_faktur', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($last) {
            $lastNum = (int) substr($last['no_faktur'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function getSummaryHariIni($idCabang = null)
    {
        $today = date('Y-m-d');
        $builder = $this->db->table('penjualan')
            ->select("
                COUNT(id) as total_transaksi,
                COALESCE(SUM(grand_total), 0) as total_omzet,
                COALESCE(SUM(total_profit), 0) as total_profit,
                COALESCE(SUM(total_qty), 0) as total_terjual,
                COALESCE(SUM(potongan_tukar_jerigen), 0) as total_tukar_jerigen,
                COALESCE(SUM(CASE WHEN UPPER(TRIM(metode_bayar)) IN ('CASH', 'TUNAI') THEN grand_total ELSE 0 END), 0) as omzet_cash,
                COALESCE(SUM(CASE WHEN UPPER(TRIM(metode_bayar)) = 'QRIS' THEN grand_total ELSE 0 END), 0) as omzet_qris,
                COALESCE(SUM(CASE WHEN UPPER(TRIM(metode_bayar)) = 'TRANSFER' THEN grand_total ELSE 0 END), 0) as omzet_transfer
            ")
            ->where("DATE(tanggal)", $today);
        
        if (!empty($idCabang)) {
            $builder->where('id_cabang', (int)$idCabang);
        }
        
        return $builder->get()->getRowArray();
    }

    public function getGrafikPenjualan($periode = '7_hari', $idCabang = null)
    {
        $data = [];

        if ($periode === '1_minggu') {
            // 7 Hari dalam Minggu Ini (Senin s/d Minggu)
            $namaHariIndo = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $mondayTimestamp = strtotime('monday this week');
            for ($i = 0; $i < 7; $i++) {
                $currentTs = strtotime("+$i days", $mondayTimestamp);
                $date = date('Y-m-d', $currentTs);
                $dayEng = date('l', $currentTs);
                $hariIndo = $namaHariIndo[$dayEng] ?? $dayEng;
                $label = $hariIndo . ' (' . date('d/m', $currentTs) . ')';

                $builder = $this->db->table('penjualan')
                    ->select('COALESCE(SUM(grand_total), 0) as omzet, COALESCE(SUM(total_profit), 0) as profit, COUNT(id) as jml_trx')
                    ->where("DATE(tanggal)", $date);

                if (!empty($idCabang)) {
                    $builder->where('id_cabang', (int)$idCabang);
                }

                $row = $builder->get()->getRowArray();

                $data[] = [
                    'tanggal'   => $date,
                    'label'     => $label,
                    'omzet'     => (float) $row['omzet'],
                    'profit'    => (float) $row['profit'],
                    'transaksi' => (int) $row['jml_trx']
                ];
            }
        } elseif ($periode === '1_bulan') {
            // 30 Hari Terakhir
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $label = date('d M', strtotime($date));

                $builder = $this->db->table('penjualan')
                    ->select('COALESCE(SUM(grand_total), 0) as omzet, COALESCE(SUM(total_profit), 0) as profit, COUNT(id) as jml_trx')
                    ->where("DATE(tanggal)", $date);

                if (!empty($idCabang)) {
                    $builder->where('id_cabang', (int)$idCabang);
                }

                $row = $builder->get()->getRowArray();

                $data[] = [
                    'tanggal'   => $date,
                    'label'     => $label,
                    'omzet'     => (float) $row['omzet'],
                    'profit'    => (float) $row['profit'],
                    'transaksi' => (int) $row['jml_trx']
                ];
            }
        } elseif ($periode === '1_tahun') {
            // 12 Bulan Terakhir
            $bulanIndo = [
                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
                '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
            ];
            for ($i = 11; $i >= 0; $i--) {
                $time = strtotime("first day of -$i month");
                $ym = date('Y-m', $time);
                $b = date('m', $time);
                $y = date('y', $time);
                $label = ($bulanIndo[$b] ?? date('M', $time)) . " '" . $y;

                $builder = $this->db->table('penjualan')
                    ->select('COALESCE(SUM(grand_total), 0) as omzet, COALESCE(SUM(total_profit), 0) as profit, COUNT(id) as jml_trx');

                $isMySQL = ($this->db->DBDriver === 'MySQLi');
                if ($isMySQL) {
                    $builder->where("DATE_FORMAT(tanggal, '%Y-%m') =", $ym);
                } else {
                    $builder->where("strftime('%Y-%m', tanggal) =", $ym);
                }

                if (!empty($idCabang)) {
                    $builder->where('id_cabang', (int)$idCabang);
                }

                $row = $builder->get()->getRowArray();

                $data[] = [
                    'tanggal'   => $ym,
                    'label'     => $label,
                    'omzet'     => (float) $row['omzet'],
                    'profit'    => (float) $row['profit'],
                    'transaksi' => (int) $row['jml_trx']
                ];
            }
        } else {
            // Default 7 Hari Terakhir
            return $this->getGrafikPenjualan7Hari($idCabang);
        }

        return $data;
    }

    public function getGrafikPenjualan7Hari($idCabang = null)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('d M', strtotime($date));
            
            $builder = $this->db->table('penjualan')
                ->select('COALESCE(SUM(grand_total), 0) as omzet, COALESCE(SUM(total_profit), 0) as profit, COUNT(id) as jml_trx')
                ->where("DATE(tanggal)", $date);

            if (!empty($idCabang)) {
                $builder->where('id_cabang', (int)$idCabang);
            }
            
            $row = $builder->get()->getRowArray();
            
            $data[] = [
                'tanggal' => $date,
                'label'   => $label,
                'omzet'   => (float) $row['omzet'],
                'profit'  => (float) $row['profit'],
                'transaksi' => (int) $row['jml_trx']
            ];
        }
        return $data;
    }

    public function getTopProdukTerlaris($limit = 5, $idCabang = null)
    {
        $builder = $this->db->table('penjualan_detail pd')
            ->select('pd.id_barang, pd.nama_barang, SUM(pd.qty) as total_qty, SUM(pd.subtotal) as total_nilai')
            ->join('penjualan p', 'p.no_faktur = pd.no_faktur', 'inner');

        if (!empty($idCabang)) {
            $builder->where('p.id_cabang', (int)$idCabang);
        }

        return $builder->groupBy('pd.id_barang, pd.nama_barang')
            ->orderBy('total_qty', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}

