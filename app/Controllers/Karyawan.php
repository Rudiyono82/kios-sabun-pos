<?php

namespace App\Controllers;

class Karyawan extends BaseController
{
    public function index()
    {
        $session = session();
        // Proteksi: Hanya role admin yang boleh mengakses menu ini
        if ($session->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak! Menu Manajemen Karyawan hanya dapat diakses oleh Administrator.');
        }

        $db = \Config\Database::connect();
        
        // Ambil list karyawan beserta riwayat performa penjualan masing-masing kasir
        $users = $db->table('users u')
            ->select('u.*, 
                COUNT(p.id) as total_transaksi, 
                COALESCE(SUM(p.grand_total), 0) as total_omzet_kasir')
            ->join('penjualan p', 'p.kode_user = u.kode_user', 'left')
            ->groupBy('u.id')
            ->orderBy('u.role', 'ASC')
            ->orderBy('u.kode_user', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title' => 'Monitoring Karyawan & Hak Akses User',
            'users' => $users,
        ];

        return view('karyawan/index', $data);
    }

    public function simpan()
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak!');
        }

        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');

        $kodeUser = strtoupper(trim($this->request->getPost('kode_user')));
        $namaUser = trim($this->request->getPost('nama_user'));
        $role = $this->request->getPost('role') ?? 'kasir';
        $nip = trim($this->request->getPost('nip'));
        $telepon = trim($this->request->getPost('telepon'));
        $password = trim($this->request->getPost('password'));
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        $data = [
            'kode_user' => $kodeUser,
            'nama_user' => $namaUser,
            'role' => $role,
            'nip' => $nip,
            'telepon' => $telepon,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $db->table('users')->where('id', $id)->update($data);
            return redirect()->to('/karyawan')->with('success', "Akun karyawan {$namaUser} ({$kodeUser}) berhasil diupdate!");
        } else {
            $data['password'] = password_hash($password ?: '123456', PASSWORD_DEFAULT);
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->table('users')->insert($data);
            return redirect()->to('/karyawan')->with('success', "Karyawan baru {$namaUser} ({$kodeUser}) berhasil didaftarkan!");
        }
    }

    public function toggleStatus($id)
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak!');
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRowArray();
        if ($user) {
            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $db->table('users')->where('id', $id)->update(['is_active' => $newStatus]);
            $statusText = $newStatus == 1 ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->to('/karyawan')->with('success', "Status akun {$user['nama_user']} berhasil {$statusText}!");
        }
        return redirect()->to('/karyawan')->with('error', 'User tidak ditemukan.');
    }
}

