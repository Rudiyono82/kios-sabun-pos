<?php

namespace App\Controllers;

use App\Models\ProfilTokoModel;

class Cabang extends BaseController
{
    public function index()
    {
        $profilModel = new ProfilTokoModel();
        $profil = $profilModel->first();

        if (!$profil) {
            $defaultData = [
                'nama_toko'    => 'Kios Sabun & Parfum Curah',
                'alamat_1'     => 'Jl. Raya Utama No. 1',
                'alamat_2'     => 'Ruko Blok A1',
                'kota'         => 'Jakarta',
                'telepon'      => '0812-3456-7890',
                'footer_struk' => 'Terima Kasih Atas Kunjungan Anda! Barang yang sudah dibeli tidak dapat ditukar kecuali cacat kemasan.',
            ];
            $profilModel->insert($defaultData);
            $profil = $profilModel->first();
        }

        $data = [
            'title'  => 'Master Cabang & Profil Identitas Toko',
            'profil' => $profil,
        ];

        return view('cabang/index', $data);
    }

    public function update()
    {
        $profilModel = new ProfilTokoModel();
        $id = $this->request->getPost('id') ?? 1;

        $data = [
            'nama_toko'    => trim($this->request->getPost('nama_toko')),
            'alamat_1'     => trim($this->request->getPost('alamat_1')),
            'alamat_2'     => trim($this->request->getPost('alamat_2')),
            'kota'         => trim($this->request->getPost('kota')),
            'telepon'      => trim($this->request->getPost('telepon')),
            'footer_struk' => trim($this->request->getPost('footer_struk')),
        ];

        if (empty($data['nama_toko'])) {
            return redirect()->back()->with('error', 'Nama Toko / Cabang wajib diisi!');
        }

        $profilModel->update($id, $data);

        return redirect()->to('/cabang')->with('success', 'Identitas Profil Cabang & Toko berhasil diperbarui!');
    }
}

