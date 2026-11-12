<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\VoucherModel;

class Member extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $memberModel = new MemberModel();
        $voucherModel = new VoucherModel();

        $members = $memberModel->orderBy('total_belanja', 'DESC')->findAll();

        $totalPoin = 0;
        $totalBelanja = 0;

        foreach ($members as &$m) {
            $m['nilai_voucher'] = (float)$m['point'] * 50; // 1 Poin = Rp 50
            $totalPoin += (float)$m['point'];
            $totalBelanja += (float)$m['total_belanja'];

            // Hitung voucher aktif
            $vchCount = $db->table('voucher')
                ->where('kode_member', $m['kode_member'])
                ->where('is_aktif', 1)
                ->countAllResults();
            $m['voucher_aktif_count'] = $vchCount;
        }

        // Ambil daftar seluruh voucher untuk monitoring
        $voucherList = $voucherModel->orderBy('id', 'DESC')->findAll(150);

        $totalNilaiVoucherBeredar = $totalPoin * 50;

        $totalVoucherAktifRp = $db->table('voucher')
            ->select('COALESCE(SUM(nilai_voucher), 0) as total')
            ->where('is_aktif', 1)
            ->get()->getRowArray()['total'] ?? 0;

        $data = [
            'title' => 'Manajemen Member & Reward Point',
            'members' => $members,
            'kode_otomatis' => $memberModel->generateKodeMember(),
            'total_member' => count($members),
            'total_poin' => $totalPoin,
            'total_belanja' => $totalBelanja,
            'total_nilai_voucher_beredar' => $totalNilaiVoucherBeredar,
            'total_voucher_aktif_rp' => $totalVoucherAktifRp,
            'voucher_list' => $voucherList,
        ];

        return view('member/index', $data);
    }

    public function simpan()
    {
        $memberModel = new MemberModel();
        $id = $this->request->getPost('id');

        $data = [
            'kode_member' => trim($this->request->getPost('kode_member')),
            'nama' => trim($this->request->getPost('nama')),
            'alamat' => trim($this->request->getPost('alamat')),
            'telepon' => trim($this->request->getPost('telepon')),
        ];

        if ($id) {
            $memberModel->update($id, $data);
            return redirect()->to('/member')->with('success', 'Data member berhasil diupdate!');
        } else {
            $memberModel->insert($data);
            return redirect()->to('/member')->with('success', 'Member baru berhasil didaftarkan!');
        }
    }

    public function tukarPoin()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $voucherModel = new VoucherModel();
        $kodeMember = $this->request->getPost('kode_member');
        $pointTukar = (float) $this->request->getPost('point_tukar');

        if ($pointTukar <= 0) {
            return redirect()->back()->with('error', 'Jumlah poin yang ditukar harus lebih dari 0.');
        }

        $member = $db->table('member')->where('kode_member', $kodeMember)->get()->getRowArray();
        if (!$member) {
            return redirect()->back()->with('error', 'Member tidak ditemukan.');
        }

        if ((float)$member['point'] < $pointTukar) {
            return redirect()->back()->with('error', "Poin member tidak mencukupi! Saldo poin: {$member['point']} poin.");
        }

        $nilaiVoucher = $pointTukar * 50; // 1 Poin = Rp 50
        $kodeVoucher = $voucherModel->generateKodeVoucher($kodeMember);
        $namaVoucher = "Voucher Belanja " . date('d/m/Y') . " Member: " . $member['nama'];

        // 1. Potong Poin Member
        $sisaPoin = (float)$member['point'] - $pointTukar;
        $db->table('member')->where('kode_member', $kodeMember)->update(['point' => $sisaPoin]);

        // 2. Terbitkan Voucher Resmi
        $voucherModel->insert([
            'kode_voucher' => $kodeVoucher,
            'nama_voucher' => $namaVoucher,
            'tgl_voucher' => date('Y-m-d'),
            'kode_member' => $kodeMember,
            'nama_member' => $member['nama'],
            'point_ditukar' => $pointTukar,
            'nilai_voucher' => $nilaiVoucher,
            'is_aktif' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses penukaran poin menjadi voucher!');
        }

        return redirect()->to('/member')->with('success', "Voucher {$kodeVoucher} senilai Rp " . number_format($nilaiVoucher, 0, ',', '.') . " berhasil diterbitkan untuk {$member['nama']}! Voucher ini langsung dapat digunakan di kasir POS untuk memotong total belanja.");
    }

    public function hapus($id)
    {
        $memberModel = new MemberModel();
        $memberModel->delete($id);
        return redirect()->to('/member')->with('success', 'Data member berhasil dihapus!');
    }
}
