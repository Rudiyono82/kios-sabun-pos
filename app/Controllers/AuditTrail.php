<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\CabangModel;

class AuditTrail extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya Administrator yang memiliki akses ke modul Audit Trail!');
        }

        $cabangModel = new CabangModel();
        $cabangList  = $cabangModel->findAll();

        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs');

        // Filter Cabang
        $idCabang = $this->request->getGet('id_cabang');
        if (!empty($idCabang) && $idCabang !== 'all') {
            $builder->where('id_cabang', (int)$idCabang);
        }

        // Filter Modul
        $modul = $this->request->getGet('modul');
        if (!empty($modul) && $modul !== 'all') {
            $builder->where('modul', strtoupper($modul));
        }

        // Filter Tanggal
        $tglMulai = $this->request->getGet('tgl_mulai') ?? date('Y-m-d', strtotime('-7 days'));
        $tglSelesai = $this->request->getGet('tgl_selesai') ?? date('Y-m-d');
        if (!empty($tglMulai)) {
            $builder->where('DATE(created_at) >=', $tglMulai);
        }
        if (!empty($tglSelesai)) {
            $builder->where('DATE(created_at) <=', $tglSelesai);
        }

        // Filter Keyword
        $keyword = trim((string)$this->request->getGet('keyword'));
        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('deskripsi', $keyword)
                ->orLike('nama_user', $keyword)
                ->orLike('kode_user', $keyword)
                ->orLike('aksi', $keyword)
            ->groupEnd();
        }

        $builder->orderBy('id', 'DESC')->limit(150);
        $logs = $builder->get()->getResultArray();

        // Rekapitulasi Statistik
        $stats = [
            'total'    => count($logs),
            'pos'      => 0,
            'transfer' => 0,
            'cabang'   => 0,
            'auth'     => 0
        ];
        foreach ($logs as $l) {
            $m = strtoupper($l['modul']);
            if (str_contains($m, 'POS')) $stats['pos']++;
            elseif (str_contains($m, 'TRANSFER')) $stats['transfer']++;
            elseif (str_contains($m, 'CABANG')) $stats['cabang']++;
            elseif (str_contains($m, 'AUTH')) $stats['auth']++;
        }

        $data = [
            'title'      => 'Audit Trail & Jejak Aktivitas Sistem',
            'logs'       => $logs,
            'cabangList' => $cabangList,
            'stats'      => $stats,
            'filters'    => [
                'id_cabang'   => $idCabang,
                'modul'       => $modul,
                'tgl_mulai'   => $tglMulai,
                'tgl_selesai' => $tglSelesai,
                'keyword'     => $keyword
            ]
        ];

        return view('audit_trail/index', $data);
    }
}

