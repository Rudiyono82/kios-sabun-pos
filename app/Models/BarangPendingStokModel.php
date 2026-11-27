<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangPendingStokModel extends Model
{
    protected $table            = 'barang_pending_stok';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ref_id',
        'tgl_input',
        'id_barang',
        'nama_barang',
        'stok_pending',
        'harga_beli_lama',
        'harga_beli',
        'harga_jual_lama',
        'harga_jual',
        'qty_paket_a',
        'harga_paket_a',
        'qty_paket_b',
        'harga_paket_b',
        'qty_paket_c',
        'harga_paket_c',
        'status_persetujuan',
        'status_eksekusi',
        'user_input',
        'user_approved',
        'tgl_approved',
        'catatan',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung jumlah pending yang berstatus 'Belum Setuju' untuk notifikasi badge sidebar
     */
    public function countBelumSetuju(): int
    {
        return $this->where('status_eksekusi', 1)
            ->where('status_persetujuan', 'Belum Setuju')
            ->countAllResults();
    }

    /**
     * Ambil data pending aktif untuk barang tertentu (eksekusi = 1)
     */
    public function getActivePending(string $idBarang): ?array
    {
        return $this->where('id_barang', $idBarang)
            ->where('status_eksekusi', 1)
            ->orderBy('id', 'ASC')
            ->first();
    }

    /**
     * Eksekusi promosi harga pending ke master barang jika status mengandung kata "Setuju"
     */
    public function executePromoteToMaster(string $idBarang, ?string $executedBy = 'SYSTEM_POS'): array
    {
        $db = \Config\Database::connect();
        $isNested = ($db->transDepth > 0);
        if (!$isNested) {
            $db->transStart();
        }

        $pending = $this->where('id_barang', $idBarang)
            ->where('status_eksekusi', 1)
            ->orderBy('id', 'ASC')
            ->first();

        if (!$pending) {
            if (!$isNested) $db->transRollback();
            return [
                'success' => false,
                'message' => 'Tidak ada antrean pending harga untuk barang ini.'
            ];
        }

        // Cek apakah harga master barang saat ini SUDAH sama dengan harga pending
        $currentMaster = $db->table('barang')->where('id_barang', $idBarang)->get()->getRowArray();
        if ($currentMaster && 
            (float)$currentMaster['harga_beli'] == (float)$pending['harga_beli'] && 
            (float)$currentMaster['harga_jual'] == (float)$pending['harga_jual']) {
            
            // Tandai pending selesai langsung
            $this->update($pending['id'], [
                'status_eksekusi' => 0,
                'status_persetujuan' => 'Setuju',
                'catatan'         => ($pending['catatan'] ? $pending['catatan'] . ' | ' : '') . 'Harga master sudah sesuai (auto-closed)',
                'updated_at'      => date('Y-m-d H:i:s')
            ]);

            if (!$isNested) $db->transComplete();

            return [
                'success'            => true,
                'status_persetujuan' => 'Setuju',
                'harga_beli'         => (float) $pending['harga_beli'],
                'harga_jual'         => (float) $pending['harga_jual'],
                'message'            => "Harga master barang [{$idBarang}] sudah sesuai."
            ];
        }

        // Cek syarat mutlak: Status persetujuan HARUS mengandung kata "Setuju"
        // Mencakup: "Setuju" dan "Setuju dengan Perubahan"
        $statusPersetujuan = trim($pending['status_persetujuan']);
        if (!str_contains($statusPersetujuan, 'Setuju') || $statusPersetujuan === 'Belum Setuju') {
            if (!$isNested) $db->transRollback();
            return [
                'success' => false,
                'is_unapproved' => true,
                'status_persetujuan' => $statusPersetujuan,
                'message' => "Harga baru belum disetujui oleh Admin (Status: {$statusPersetujuan}). Master barang tidak dapat diperbarui dan masih menggunakan harga lama."
            ];
        }

        // Update data master BARANG
        $updateMaster = [
            'harga_beli'    => (float) $pending['harga_beli'],
            'harga_jual'    => (float) $pending['harga_jual'],
            'qty_paket_a'   => (int) ($pending['qty_paket_a'] ?? 0),
            'harga_paket_a' => (float) ($pending['harga_paket_a'] ?? 0),
            'qty_paket_b'   => (int) ($pending['qty_paket_b'] ?? 0),
            'harga_paket_b' => (float) ($pending['harga_paket_b'] ?? 0),
            'qty_paket_c'   => (int) ($pending['qty_paket_c'] ?? 0),
            'harga_paket_c' => (float) ($pending['harga_paket_c'] ?? 0),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $db->table('barang')->where('id_barang', $idBarang)->update($updateMaster);

        // Update status pending menjadi Selesai (status_eksekusi = 0)
        $this->update($pending['id'], [
            'status_eksekusi' => 0,
            'catatan'         => ($pending['catatan'] ? $pending['catatan'] . ' | ' : '') . 'Promoted to Master by ' . $executedBy . ' on ' . date('d/m/Y H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        if (!$isNested) {
            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal memperbarui harga master ke database.'
                ];
            }
        }

        // Catat Audit Trail
        try {
            \App\Models\AuditLogModel::log(
                'UPDATE_HARGA_MASTER',
                'BARANG',
                "Otomatis mempromosikan harga pending barang [{$idBarang}] {$pending['nama_barang']} ke Master (Status: {$statusPersetujuan}, Hrg Beli: Rp " . number_format($pending['harga_beli'], 0, ',', '.') . ", Hrg Jual: Rp " . number_format($pending['harga_jual'], 0, ',', '.') . ")",
                ['harga_beli' => $pending['harga_beli_lama'], 'harga_jual' => $pending['harga_jual_lama']],
                $updateMaster
            );
        } catch (\Throwable $t) {}

        return [
            'success'            => true,
            'status_persetujuan' => $statusPersetujuan,
            'harga_beli'         => (float) $pending['harga_beli'],
            'harga_jual'         => (float) $pending['harga_jual'],
            'message'            => "Sukses! Harga master barang [{$idBarang}] berhasil diperbarui ke harga baru."
        ];
    }
}

