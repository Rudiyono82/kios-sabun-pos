<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table = 'member';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_member', 'nama', 'alamat', 'telepon', 'point', 'total_belanja', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    public function generateKodeMember()
    {
        $last = $this->orderBy('id', 'DESC')->first();
        if ($last) {
            $num = (int) filter_var($last['kode_member'], FILTER_SANITIZE_NUMBER_INT);
            return 'MBR' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'MBR0001';
    }

    public function getTopMember($limit = 10)
    {
        return $this->orderBy('total_belanja', 'DESC')->limit($limit)->findAll();
    }
}

