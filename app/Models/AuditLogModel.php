<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cabang',
        'nama_cabang',
        'user_id',
        'kode_user',
        'nama_user',
        'role',
        'aksi',
        'modul',
        'deskripsi',
        'data_lama',
        'data_baru',
        'ip_address',
        'user_agent',
        'created_at'
    ];
    protected $useTimestamps    = false;

    /**
     * Catat aktivitas ke buku audit trail
     */
    public static function log($aksi, $modul, $deskripsi, $dataLama = null, $dataBaru = null, $idCabang = null)
    {
        $session = session();
        $db = \Config\Database::connect();
        
        $cabangId = $idCabang ?? $session->get('id_cabang') ?? 1;
        $namaCabang = $session->get('nama_cabang');
        if (!$namaCabang && $cabangId) {
            $cabang = $db->table('cabang')->where('id', $cabangId)->get()->getRowArray();
            $namaCabang = $cabang ? $cabang['nama_cabang'] : "Cabang #$cabangId";
        }

        $userId   = $session->get('user_id');
        $kodeUser = $session->get('kode_user') ?? $session->get('username') ?? 'SYSTEM';
        $namaUser = $session->get('nama_user') ?? $session->get('nama_lengkap') ?? 'Pengguna Sistem';
        $role     = $session->get('role') ?? 'guest';

        $request = \Config\Services::request();
        $ip = $request->getIPAddress();
        $ua = substr((string)$request->getUserAgent(), 0, 255);

        $db->table('audit_logs')->insert([
            'id_cabang'   => $cabangId,
            'nama_cabang' => $namaCabang,
            'user_id'     => $userId,
            'kode_user'   => $kodeUser,
            'nama_user'   => $namaUser,
            'role'        => $role,
            'aksi'        => strtoupper($aksi),
            'modul'       => strtoupper($modul),
            'deskripsi'   => $deskripsi,
            'data_lama'   => is_array($dataLama) ? json_encode($dataLama, JSON_UNESCAPED_UNICODE) : $dataLama,
            'data_baru'   => is_array($dataBaru) ? json_encode($dataBaru, JSON_UNESCAPED_UNICODE) : $dataBaru,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}

