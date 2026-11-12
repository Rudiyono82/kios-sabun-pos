<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table = 'voucher';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_voucher', 'nama_voucher', 'tgl_voucher', 'tgl_kadaluarsa',
        'kode_member', 'nama_member', 'point_ditukar', 'nilai_voucher',
        'is_aktif', 'no_faktur_terpakai', 'tgl_terpakai', 'created_at'
    ];

    public function generateKodeVoucher($kodeMember)
    {
        $prefix = 'VCH-' . $kodeMember . '-';
        $last = $this->like('kode_voucher', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last['kode_voucher'], -5);
            $nextNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '00001';
        }

        return $prefix . $nextNum;
    }

    public function getVoucherAktifMember($kodeMember)
    {
        return $this->where('kode_member', $kodeMember)
            ->where('is_aktif', 1)
            ->orderBy('nilai_voucher', 'DESC')
            ->findAll();
    }
}

