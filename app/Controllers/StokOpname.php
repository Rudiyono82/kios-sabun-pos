<?php

namespace App\Controllers;

use App\Models\BarangModel;

class StokOpname extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $filterBulan = $this->request->getGet('bulan') ?? 'semua';

        $builder = $db->table('stok_opname');

        if ($filterBulan === 'bulan_ini') {
            $builder->where("strftime('%Y-%m', tanggal)", date('Y-m'));
        } elseif ($filterBulan === '1_bulan_lalu') {
            $prevMonth = date('Y-m', strtotime('-1 month'));
            $builder->where("strftime('%Y-%m', tanggal)", $prevMonth);
        }

        $stoList = $builder->orderBy('id', 'DESC')->get(100)->getResultArray();

        $data = [
            'title' => 'Stok Opname Fisik Toko (STO)',
            'sto_list' => $stoList,
            'filter_bulan' => $filterBulan,
        ];

        return view('stok_opname/index', $data);
    }

    public function tambah()
    {
        $db = \Config\Database::connect();
        $barangModel = new BarangModel();

        // Generate No STO: STO + YYMMDD + Urutan 3 digit
        $todayPrefix = 'STO' . date('ymd');
        $lastSto = $db->table('stok_opname')
            ->like('no_sto', $todayPrefix, 'after')
            ->orderBy('id', 'DESC')
            ->get(1)->getRowArray();

        if ($lastSto) {
            $lastNum = (int) substr($lastSto['no_sto'], -3);
            $noSto = $todayPrefix . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $noSto = $todayPrefix . '001';
        }

        $kategoriList = $db->table('kategori')->orderBy('nama_kategori', 'ASC')->get()->getResultArray();
        $barangList = $barangModel->getBarangWithKategori();

        $data = [
            'title' => 'Input Sesi Stok Opname (Blind Count)',
            'no_sto' => $noSto,
            'barang_list' => $barangList,
            'kategori_list' => $kategoriList,
            'is_admin' => session()->get('role') === 'admin',
        ];

        return view('stok_opname/tambah', $data);
    }

    public function simpan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $noSto = trim($this->request->getPost('no_sto'));
        $keterangan = trim($this->request->getPost('keterangan') ?? '');

        $idBarangList = $this->request->getPost('id_barang');
        $stokFisikList = $this->request->getPost('stok_fisik');
        $catatanList = $this->request->getPost('catatan_item');

        if (empty($idBarangList) || !is_array($idBarangList)) {
            return redirect()->back()->with('error', 'Tidak ada data barang yang diaudit.');
        }

        $totalItem = 0;
        $totalPlus = 0;
        $totalMinus = 0;
        $totalNilaiSelisih = 0;
        $detailRecords = [];

        foreach ($idBarangList as $idx => $idBarang) {
            // Cek apakah input fisik diisi
            $rawFisik = $stokFisikList[$idx] ?? '';
            if ($rawFisik === '' || $rawFisik === null) {
                // Lewati item yang tidak dihitung / tidak diinput
                continue;
            }

            $barang = $db->table('barang')->where('id_barang', $idBarang)->get()->getRowArray();
            if (!$barang) continue;

            $stokSistem = (float)$barang['stok'];
            $stokFisik = (float)$rawFisik;
            if ($stokFisik < 0) $stokFisik = 0;

            $selisih = $stokFisik - $stokSistem;
            $hargaBeli = (float)$barang['harga_beli'];
            $nilaiSelisih = $selisih * $hargaBeli;

            $totalItem++;
            if ($selisih > 0) $totalPlus += $selisih;
            if ($selisih < 0) $totalMinus += abs($selisih);
            $totalNilaiSelisih += $nilaiSelisih;

            // Jika ada selisih, update stok sistem dan catat mutasi kartu stok
            if ($selisih != 0) {
                $db->table('barang')->where('id_barang', $idBarang)->update([
                    'stok' => $stokFisik,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if ($selisih > 0) {
                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $idBarang,
                        'tipe' => 'MASUK',
                        'no_referensi' => $noSto,
                        'masuk' => $selisih,
                        'keluar' => 0,
                        'sisa' => $stokFisik,
                        'keterangan' => 'Penyesuaian STO (Fisik Surplus/Lebih) ' . $noSto
                    ]);
                } else {
                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $idBarang,
                        'tipe' => 'KELUAR',
                        'no_referensi' => $noSto,
                        'masuk' => 0,
                        'keluar' => abs($selisih),
                        'sisa' => $stokFisik,
                        'keterangan' => 'Penyesuaian STO (Fisik Defisit/Kurang/Hilang) ' . $noSto
                    ]);
                }
            }

            $detailRecords[] = [
                'no_sto' => $noSto,
                'id_barang' => $idBarang,
                'nama_barang' => $barang['nama_barang'],
                'satuan' => $barang['satuan'],
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'harga_beli' => $hargaBeli,
                'nilai_selisih' => $nilaiSelisih,
                'catatan' => $catatanList[$idx] ?? null
            ];
        }

        if ($totalItem === 0) {
            return redirect()->back()->with('error', 'Belum ada stok fisik barang yang diisi. Masukkan hasil hitungan fisik minimal pada satu barang.');
        }

        // Simpan Header STO
        $db->table('stok_opname')->insert([
            'no_sto' => $noSto,
            'tanggal' => date('Y-m-d H:i:s'),
            'kode_user' => session()->get('kode_user') ?? 'ADMIN',
            'nama_petugas' => session()->get('nama_user') ?? 'Petugas STO',
            'total_item_dihitung' => $totalItem,
            'total_selisih_plus' => $totalPlus,
            'total_selisih_minus' => $totalMinus,
            'total_nilai_selisih' => $totalNilaiSelisih,
            'status' => 'Selesai',
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $stoId = $db->insertID();

        foreach ($detailRecords as &$d) {
            $d['sto_id'] = $stoId;
        }
        $db->table('stok_opname_detail')->insertBatch($detailRecords);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses sesi stok opname!');
        }

        return redirect()->to(base_url('stok-opname/detail/' . $stoId))->with('success', "Stok Opname {$noSto} berhasil disimpan! Sebanyak {$totalItem} produk telah dihitung dan stok sistem otomatis disinkronkan.");
    }

    public function detail($id)
    {
        $db = \Config\Database::connect();
        $sto = $db->table('stok_opname')->where('id', $id)->get()->getRowArray();
        if (!$sto) {
            return redirect()->to(base_url('stok-opname'))->with('error', 'Data STO tidak ditemukan.');
        }

        $items = $db->table('stok_opname_detail')->where('sto_id', $id)->get()->getResultArray();

        $itemsKurang = [];
        $itemsLebih = [];
        $itemsCocok = [];

        foreach ($items as $it) {
            $diff = (float)$it['selisih'];
            if ($diff < 0) {
                $itemsKurang[] = $it;
            } elseif ($diff > 0) {
                $itemsLebih[] = $it;
            } else {
                $itemsCocok[] = $it;
            }
        }

        $data = [
            'title' => 'Rincian Hasil Stok Opname: ' . $sto['no_sto'],
            'sto' => $sto,
            'items' => $items,
            'items_kurang' => $itemsKurang,
            'items_lebih' => $itemsLebih,
            'items_cocok' => $itemsCocok,
            'is_admin' => session()->get('role') === 'admin',
        ];

        return view('stok_opname/detail', $data);
    }

    public function batalkan($id)
    {
        $db = \Config\Database::connect();
        $password = trim($this->request->getPost('password') ?? '');

        // Verifikasi Password Super Admin (ADMIN / ADMIN01 / admin123)
        $adminUser = $db->table('users')->where('role', 'admin')->get()->getResultArray();
        $authOk = false;
        foreach ($adminUser as $adm) {
            if (password_verify($password, $adm['password'])) {
                $authOk = true;
                break;
            }
        }
        if (!$authOk && ($password === 'admin123' || $password === 'Admin01')) {
            $authOk = true;
        }

        if (!$authOk) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Password Super Admin salah! Otorisasi dibatalkan.']);
            }
            return redirect()->back()->with('error', 'Password Super Admin salah! Otorisasi dibatalkan.');
        }

        $sto = $db->table('stok_opname')->where('id', $id)->get()->getRowArray();
        if (!$sto) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Data STO tidak ditemukan.']);
            }
            return redirect()->to(base_url('stok-opname'))->with('error', 'Data STO tidak ditemukan.');
        }

        if ($sto['status'] === 'Dibatalkan') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Sesi STO ini sudah dibatalkan sebelumnya.']);
            }
            return redirect()->back()->with('error', 'Sesi STO ini sudah dibatalkan sebelumnya.');
        }

        $db->transStart();

        // Ambil rincian STO untuk mengembalikan stok ke kondisi sebelum STO
        $items = $db->table('stok_opname_detail')->where('sto_id', $id)->get()->getResultArray();

        foreach ($items as $it) {
            $idBarang = $it['id_barang'];
            $stokSistemSemula = (float)$it['stok_sistem'];
            $selisih = (float)$it['selisih'];

            // Jika pernah ada penyesuaian (selisih != 0), kembalikan ke stok sistem semula
            if ($selisih != 0) {
                $db->table('barang')->where('id_barang', $idBarang)->update([
                    'stok' => $stokSistemSemula,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Catat mutasi pembatalan di kartu stok
                if ($selisih > 0) {
                    // Semula surplus (+), dibatalkan berarti KELUAR
                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $idBarang,
                        'tipe' => 'KELUAR',
                        'no_referensi' => 'REVERT-' . $sto['no_sto'],
                        'masuk' => 0,
                        'keluar' => $selisih,
                        'sisa' => $stokSistemSemula,
                        'keterangan' => 'Pembatalan STO ' . $sto['no_sto'] . ' (Revert ke Stok Asal)'
                    ]);
                } else {
                    // Semula defisit (-), dibatalkan berarti MASUK
                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $idBarang,
                        'tipe' => 'MASUK',
                        'no_referensi' => 'REVERT-' . $sto['no_sto'],
                        'masuk' => abs($selisih),
                        'keluar' => 0,
                        'sisa' => $stokSistemSemula,
                        'keterangan' => 'Pembatalan STO ' . $sto['no_sto'] . ' (Revert ke Stok Asal)'
                    ]);
                }
            }
        }

        // Tandai status STO menjadi 'Dibatalkan'
        $db->table('stok_opname')->where('id', $id)->update([
            'status' => 'Dibatalkan',
            'keterangan' => ($sto['keterangan'] ? $sto['keterangan'] . ' | ' : '') . 'Dibatalkan oleh ' . (session()->get('nama_user') ?? 'Admin') . ' pada ' . date('d/m/Y H:i')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal memproses pembatalan sesi STO!']);
            }
            return redirect()->back()->with('error', 'Gagal memproses pembatalan sesi STO!');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Sesi Stok Opname {$sto['no_sto']} berhasil dibatalkan dan seluruh stok barang telah dikembalikan ke kondisi semula!",
                'redirect' => base_url('stok-opname/detail/' . $id)
            ]);
        }

        return redirect()->to(base_url('stok-opname/detail/' . $id))->with('success', "Sesi Stok Opname {$sto['no_sto']} berhasil dibatalkan dan seluruh stok barang telah dikembalikan ke kondisi semula!");
    }
}
