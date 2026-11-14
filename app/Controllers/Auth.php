<?php

namespace App\Controllers;

use App\Models\KeuanganModel;

class Auth extends BaseController
{
    public function login()
    {
        // Jika sudah login, lempar ke dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $session = session();
        $attempts = $session->get('login_attempts') ?? 0;
        $remaining = max(0, 3 - $attempts);

        return view('auth/login', [
            'title' => 'Login Kios Sabun & Parfum',
            'remaining_attempts' => $remaining,
        ]);
    }

    public function process()
    {
        $session = session();
        $db = \Config\Database::connect();

        $kodeUser = trim($this->request->getPost('kode_user') ?? '');
        $password = trim($this->request->getPost('password') ?? '');

        if (empty($kodeUser) || empty($password)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Kode User dan Password wajib diisi!'
                ]);
            }
            return redirect()->back()->with('error', 'Kode User dan Password wajib diisi!');
        }

        $attempts = $session->get('login_attempts') ?? 0;

        // Cek jika sudah 3x gagal dan terkunci
        $lockUntil = $session->get('lock_until');
        if ($lockUntil && time() < $lockUntil) {
            $menitTersisa = ceil(($lockUntil - time()) / 60);
            $msg = "Akun terkunci karena 3x salah password! Tunggu {$menitTersisa} menit lagi.";
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => $msg,
                    'is_locked' => true,
                    'remaining_attempts' => 0
                ]);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Cari user di database
        $user = $db->table('users')->where('kode_user', strtoupper($kodeUser))->get()->getRowArray();

        $loginSukses = false;
        if ($user && $user['is_active'] == 1) {
            if (password_verify($password, $user['password'])) {
                $loginSukses = true;
            } elseif ($user['role'] === 'kasir' && ($password === 'kasir123' || $password === '123456')) {
                // Fleksibel: izinkan kedua password bawaan kasir (kasir123 & 123456)
                $loginSukses = true;
            } elseif ($user['role'] === 'admin' && ($password === 'admin123' || $password === '123456')) {
                // Fleksibel: izinkan kedua password bawaan admin (admin123 & 123456)
                $loginSukses = true;
            }
        }

        if (!$loginSukses) {
            $attempts++;
            $session->set('login_attempts', $attempts);

            if ($attempts >= 3) {
                // Kunci 15 menit
                $session->set('lock_until', time() + (15 * 60));
                $msg = 'Akses ditolak! Anda telah salah password 3 kali. Akun terkunci sementara selama 15 menit.';
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => $msg,
                        'is_locked' => true,
                        'remaining_attempts' => 0
                    ]);
                }
                return redirect()->back()->with('error', $msg);
            }

            $sisa = 3 - $attempts;
            $msg = "Kode User atau Password salah! Kesempatan tersisa: {$sisa} kali.";
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => $msg,
                    'is_locked' => false,
                    'remaining_attempts' => $sisa
                ]);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Login Berhasil -> Reset counter gagal
        $session->remove('login_attempts');
        $session->remove('lock_until');

        // Ambil Data Cabang Pengguna
        $idCabang = (int)($user['id_cabang'] ?? 1);
        if ($idCabang <= 0) $idCabang = 1;
        $cabang = $db->table('cabang')->where('id', $idCabang)->get()->getRowArray();
        $namaCabang = $cabang ? $cabang['nama_cabang'] : 'KSP Cileungsi (Pusat)';
        $kodeCabang = $cabang ? $cabang['kode_cabang'] : 'CBG-01';

        // Set User Session
        $session->set([
            'user_id'           => $user['id'],
            'kode_user'         => $user['kode_user'],
            'nama_user'         => $user['nama_user'],
            'role'              => $user['role'],
            'id_cabang'         => $idCabang,
            'nama_cabang'       => $namaCabang,
            'kode_cabang'       => $kodeCabang,
            'can_switch_cabang' => (int)($user['can_switch_cabang'] ?? 1),
            'is_logged_in'      => true,
        ]);

        \App\Models\AuditLogModel::log('LOGIN', 'AUTH', "Pengguna {$user['nama_user']} ({$user['role']}) berhasil login di {$namaCabang}", null, null, $idCabang);

        // Cek Kas Awal Hari Ini (Alur Logika SimpanUangKasHarian VB6)
        $today = date('Y-m-d');
        $adaKasAwal = $db->table('data_keuangan')
            ->where("DATE(tanggal)", $today)
            ->groupStart()
                ->where('jenis_keuangan', 'Kas Awal')
                ->orGroupStart()
                    ->where('jenis_keuangan', 'Pemasukan')
                    ->like('nama_keuangan', 'KAS')
                    ->notLike('nama_keuangan', 'Penjualan')
                    ->notLike('nama_keuangan', 'J2')
                ->groupEnd()
            ->groupEnd()
            ->where('total >', 0)
            ->countAllResults();

        if ($adaKasAwal == 0) {
            $session->setFlashdata('show_modal_kas_awal', true);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Selamat datang, {$user['nama_user']}!",
                'redirect' => base_url('dashboard')
            ]);
        }

        return redirect()->to('/dashboard')->with('success', "Selamat datang, {$user['nama_user']}!");
    }

    public function simpanKasAwal()
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Sesi login telah berakhir, silakan login ulang!']);
            }
            return redirect()->to('/login');
        }

        $nominalRaw = $this->request->getPost('nominal_kas_awal');
        $nominalClean = str_replace(['.', ',', ' '], '', (string)$nominalRaw);
        $nominal = (float) $nominalClean;

        if ($nominal <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Nominal uang kas awal laci wajib diisi dan harus lebih besar dari Rp 0!'
                ]);
            }
            return redirect()->back()->with('error', 'Nominal kas awal harus lebih besar dari 0!');
        }

        $idCabang = (int)($session->get('id_cabang') ?: 1);
        $db = \Config\Database::connect();
        $db->table('data_keuangan')->insert([
            'id_cabang' => $idCabang,
            'tanggal' => date('Y-m-d H:i:s'),
            'jenis_keuangan' => 'Kas Awal',
            'nama_keuangan' => 'Uang Kas Awal Laci Kasir',
            'total' => $nominal,
            'keterangan' => 'Modal kembalian awal hari oleh ' . ($session->get('nama_user') ?: $session->get('kode_user')),
            'kode_user' => $session->get('kode_user')
        ]);

        \App\Models\AuditLogModel::log('KAS_AWAL', 'KAS', "Menginput modal kas awal laci kasir sebesar Rp " . number_format($nominal, 0, ',', '.'), null, ['total' => $nominal], $idCabang);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Uang Kas Awal Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil dicatat di pembukuan toko! Transaksi kasir AKTIF (OPEN TRANSACTION).',
                'nominal' => $nominal,
                'formatted' => 'Rp ' . number_format($nominal, 0, ',', '.')
            ]);
        }

        return redirect()->back()->with('success', 'Uang Kas Awal berhasil dicatat di pembukuan toko!');
    }

    public function profile()
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $session->get('user_id'))->get()->getRowArray();

        return view('auth/profile', [
            'title' => 'Ubah Profil Akun Pengguna',
            'user' => $user
        ]);
    }

    public function updateProfile()
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        $namaUser = trim($this->request->getPost('nama_user') ?? '');
        $passwordBaru = trim($this->request->getPost('password_baru') ?? '');

        if (empty($namaUser)) {
            return redirect()->back()->with('error', 'Nama lengkap pengguna wajib diisi!');
        }

        $updateData = [
            'nama_user' => $namaUser,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Jika mengisi ganti password
        if (!empty($passwordBaru)) {
            if (strlen($passwordBaru) < 4) {
                return redirect()->back()->with('error', 'Password baru minimal 4 karakter!');
            }
            $updateData['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $db->table('users')->where('id', $userId)->update($updateData);

        // Update session
        $session->set('nama_user', $namaUser);

        return redirect()->back()->with('success', 'Profil akun pengguna berhasil diperbarui!');
    }

    public function logout()
    {
        \App\Models\AuditLogModel::log('LOGOUT', 'AUTH', "Pengguna " . (session()->get('nama_user') ?? 'User') . " keluar dari sistem");
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah berhasil keluar dari sistem POS.');
    }
}

