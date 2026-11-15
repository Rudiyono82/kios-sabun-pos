<?php

namespace App\Controllers;

use App\Models\BarangPendingStokModel;
use App\Models\BarangModel;

class PendingStok extends BaseController
{
    protected BarangPendingStokModel $pendingModel;

    public function __construct()
    {
        $this->pendingModel = new BarangPendingStokModel();
    }

    /**
     * Tampilkan screen Daftar Data Pending Update Stok Barang
     * Terbuka untuk dimonitor oleh Admin maupun Kasir
     */
    public function index()
    {
        $filterStatus = $this->request->getGet('status') ?: 'all';
        $keyword      = trim($this->request->getGet('q') ?: '');

        $builder = $this->pendingModel->select('barang_pending_stok.*, barang.stok as stok_master_saat_ini, barang.satuan')
            ->join('barang', 'barang.id_barang = barang_pending_stok.id_barang', 'left');

        if ($filterStatus === 'belum_setuju') {
            $builder->where('barang_pending_stok.status_eksekusi', 1)
                    ->where('barang_pending_stok.status_persetujuan', 'Belum Setuju');
        } elseif ($filterStatus === 'setuju') {
            $builder->where('barang_pending_stok.status_eksekusi', 1)
                    ->where('barang_pending_stok.status_persetujuan', 'Setuju');
        } elseif ($filterStatus === 'setuju_perubahan') {
            $builder->where('barang_pending_stok.status_eksekusi', 1)
                    ->where('barang_pending_stok.status_persetujuan', 'Setuju dengan Perubahan');
        } elseif ($filterStatus === 'selesai') {
            $builder->where('barang_pending_stok.status_eksekusi', 0);
        } elseif ($filterStatus === 'aktif_pending') {
            $builder->where('barang_pending_stok.status_eksekusi', 1);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('barang_pending_stok.id_barang', $keyword)
                ->orLike('barang_pending_stok.nama_barang', $keyword)
                ->orLike('barang_pending_stok.ref_id', $keyword)
                ->groupEnd();
        }

        $list = $builder->orderBy('barang_pending_stok.status_eksekusi', 'DESC')
            ->orderBy('barang_pending_stok.id', 'DESC')
            ->findAll();

        // Hitung Ringkasan Metrik
        $countBelumSetuju = $this->pendingModel->where('status_eksekusi', 1)->where('status_persetujuan', 'Belum Setuju')->countAllResults();
        $countSetuju = $this->pendingModel->where('status_eksekusi', 1)->where('status_persetujuan', 'Setuju')->countAllResults();
        $countSetujuPerubahan = $this->pendingModel->where('status_eksekusi', 1)->where('status_persetujuan', 'Setuju dengan Perubahan')->countAllResults();
        $countSelesai = $this->pendingModel->where('status_eksekusi', 0)->countAllResults();
        $countTotalAktif = $countBelumSetuju + $countSetuju + $countSetujuPerubahan;

        $userRole = strtolower(session()->get('role') ?? 'kasir');
        $isAdmin = ($userRole === 'admin' || $userRole === 'owner');

        $data = [
            'title'                => 'Daftar Pending Update Stok & Harga Barang',
            'list'                 => $list,
            'filter_status'        => $filterStatus,
            'keyword'              => $keyword,
            'is_admin'             => $isAdmin,
            'count_belum_setuju'   => $countBelumSetuju,
            'count_setuju'         => $countSetuju,
            'count_setuju_ubah'    => $countSetujuPerubahan,
            'count_selesai'        => $countSelesai,
            'count_total_aktif'    => $countTotalAktif,
        ];

        return view('pending_stok/index', $data);
    }

    /**
     * Admin Setujui Langsung (Status: "Setuju")
     */
    public function approve($id)
    {
        $userRole = strtolower(session()->get('role') ?? 'kasir');
        if ($userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Hanya Admin/Owner yang berhak menyetujui perubahan harga barang!'
            ]);
        }

        $pending = $this->pendingModel->find($id);
        if (!$pending) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data pending tidak ditemukan.'
            ]);
        }

        $adminUser = session()->get('nama_user') ?? (session()->get('kode_user') ?? 'ADMIN');

        $this->pendingModel->update($id, [
            'status_persetujuan' => 'Setuju',
            'user_approved'      => $adminUser,
            'tgl_approved'       => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        \App\Models\AuditLogModel::log(
            'APPROVE_PENDING_HARGA',
            'BARANG',
            "Admin {$adminUser} menyetujui perubahan harga pending barang [{$pending['id_barang']}] {$pending['nama_barang']} (Status: Setuju)",
            ['status_sebelumnya' => $pending['status_persetujuan']],
            ['status_baru' => 'Setuju', 'user_approved' => $adminUser]
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Perubahan harga barang [{$pending['nama_barang']}] berhasil disetujui (Status: Setuju)."
        ]);
    }

    /**
     * Admin Setujui Dengan Perubahan Harga (Status: "Setuju dengan Perubahan")
     */
    public function approveWithChange($id)
    {
        $userRole = strtolower(session()->get('role') ?? 'kasir');
        if ($userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Hanya Admin/Owner yang berhak menyetujui dan merevisi harga barang!'
            ]);
        }

        $pending = $this->pendingModel->find($id);
        if (!$pending) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data pending tidak ditemukan.'
            ]);
        }

        $json = $this->request->getJSON(true) ?: $this->request->getPost();

        $cleanNumber = function($val) {
            return (float) str_replace(['.', ',', ' ', 'Rp'], '', (string)($val ?? 0));
        };

        $hargaBeli  = $cleanNumber($json['harga_beli'] ?? $pending['harga_beli']);
        $hargaJual  = $cleanNumber($json['harga_jual'] ?? $pending['harga_jual']);
        $qtyPaketA  = (int) ($json['qty_paket_a'] ?? $pending['qty_paket_a']);
        $hrgPaketA  = $cleanNumber($json['harga_paket_a'] ?? $pending['harga_paket_a']);
        $qtyPaketB  = (int) ($json['qty_paket_b'] ?? $pending['qty_paket_b']);
        $hrgPaketB  = $cleanNumber($json['harga_paket_b'] ?? $pending['harga_paket_b']);
        $qtyPaketC  = (int) ($json['qty_paket_c'] ?? $pending['qty_paket_c']);
        $hrgPaketC  = $cleanNumber($json['harga_paket_c'] ?? $pending['harga_paket_c']);
        $catatan    = trim($json['catatan_admin'] ?? ($json['catatan'] ?? ''));

        if ($hargaBeli <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Harga beli modal baru harus lebih besar dari 0!'
            ]);
        }

        if ($hargaJual <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Harga jual baru harus lebih besar dari 0!'
            ]);
        }

        if ($hargaBeli >= $hargaJual) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Harga Beli modal tidak boleh sama dengan atau lebih besar dari Harga Jual eceran!'
            ]);
        }

        $adminUser = session()->get('nama_user') ?? (session()->get('kode_user') ?? 'ADMIN');

        $updateData = [
            'harga_beli'         => $hargaBeli,
            'harga_jual'         => $hargaJual,
            'qty_paket_a'        => $qtyPaketA,
            'harga_paket_a'      => $hrgPaketA,
            'qty_paket_b'        => $qtyPaketB,
            'harga_paket_b'      => $hrgPaketB,
            'qty_paket_c'        => $qtyPaketC,
            'harga_paket_c'      => $hrgPaketC,
            'status_persetujuan' => 'Setuju dengan Perubahan',
            'user_approved'      => $adminUser,
            'tgl_approved'       => date('Y-m-d H:i:s'),
            'catatan'            => $catatan ?: $pending['catatan'],
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        $this->pendingModel->update($id, $updateData);

        \App\Models\AuditLogModel::log(
            'APPROVE_REVISI_PENDING_HARGA',
            'BARANG',
            "Admin {$adminUser} menyetujui dengan perubahan harga untuk [{$pending['id_barang']}] {$pending['nama_barang']} (Hrg Beli: Rp " . number_format($hargaBeli, 0, ',', '.') . ", Hrg Jual: Rp " . number_format($hargaJual, 0, ',', '.') . ")",
            $pending,
            $updateData
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Harga barang [{$pending['nama_barang']}] berhasil direvisi dan disetujui (Status: Setuju dengan Perubahan)."
        ]);
    }

    /**
     * Admin Membatalkan / Menolak Pending Stok
     */
    public function reject($id)
    {
        $userRole = strtolower(session()->get('role') ?? 'kasir');
        if ($userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Hanya Admin/Owner yang berhak menolak antrean pending harga!'
            ]);
        }

        $pending = $this->pendingModel->find($id);
        if (!$pending) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data pending tidak ditemukan.'
            ]);
        }

        $this->pendingModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Data antrean pending barang [{$pending['nama_barang']}] telah dihapus/dibatalkan."
        ]);
    }

    /**
     * API Pemeriksaan Status Pending untuk POS & LookUp
     * Memeriksa apakah barang memiliki antrean pending dan apakah stok lama sudah habis
     */
    public function checkStatus($idBarang)
    {
        $db = \Config\Database::connect();
        $barang = $db->table('barang')->where('id_barang', $idBarang)->get()->getRowArray();
        if (!$barang) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Barang tidak ditemukan.'
            ]);
        }

        $activePending = $this->pendingModel->getActivePending($idBarang);
        if (!$activePending) {
            return $this->response->setJSON([
                'has_pending' => false,
                'stok_master' => (float) $barang['stok']
            ]);
        }

        $statusPersetujuan = trim($activePending['status_persetujuan']);
        $isApproved = str_contains($statusPersetujuan, 'Setuju') && ($statusPersetujuan !== 'Belum Setuju');

        $stokToko = (float) $barang['stok'];
        // Jika stok lama toko sudah habis (<= 0), atau pembelian pending sudah siap masuk
        $isOldStockDepleted = ($stokToko <= 0);

        return $this->response->setJSON([
            'has_pending'           => true,
            'pending_id'            => $activePending['id'],
            'ref_id'                => $activePending['ref_id'],
            'id_barang'             => $idBarang,
            'nama_barang'           => $barang['nama_barang'],
            'stok_master'           => $stokToko,
            'stok_pending'          => (float) $activePending['stok_pending'],
            'harga_beli_lama'       => (float) $activePending['harga_beli_lama'],
            'harga_beli_baru'       => (float) $activePending['harga_beli'],
            'harga_jual_lama'       => (float) $activePending['harga_jual_lama'],
            'harga_jual_baru'       => (float) $activePending['harga_jual'],
            'status_persetujuan'    => $statusPersetujuan,
            'is_approved'           => $isApproved,
            'is_old_stock_depleted' => $isOldStockDepleted,
            // Flag peringatan jika stok lama habis tapi status masih 'Belum Setuju'
            'show_warning'          => ($isOldStockDepleted && !$isApproved),
            'warning_message'       => "⚠️ PERHATIAN: Sisa stok lama barang {$barang['nama_barang']} sudah habis, namun perubahan harga baru masih berstatus 'Belum Setuju' (belum disetujui Admin). Transaksi saat ini masih menggunakan harga lama."
        ]);
    }

    /**
     * API Eksekusi UpdateDataHargaMasterBarang(strIDBarang)
     * Dipanggil oleh POS ketika stok lama habis dan status mengandung kata "Setuju"
     */
    public function executeUpdate()
    {
        $idBarang = $this->request->getPost('id_barang') ?: ($this->request->getJSON(true)['id_barang'] ?? '');
        if (empty($idBarang)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter id_barang wajib diisi.'
            ]);
        }

        $user = session()->get('nama_user') ?? 'POS_CASHIER';
        $result = $this->pendingModel->executePromoteToMaster($idBarang, $user);

        if ($result['success']) {
            return $this->response->setJSON([
                'status'  => 'success',
                'data'    => $result,
                'message' => $result['message']
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status'        => 'error',
                'is_unapproved' => $result['is_unapproved'] ?? false,
                'status_persetujuan' => $result['status_persetujuan'] ?? 'Belum Setuju',
                'message'       => $result['message']
            ]);
        }
    }
}

