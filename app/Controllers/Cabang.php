<?php

namespace App\Controllers;

use App\Models\CabangModel;
use App\Models\AuditLogModel;

class Cabang extends BaseController
{
    protected $cabangModel;

    public function __construct()
    {
        $this->cabangModel = new CabangModel();
    }

    public function index()
    {
        $cabangList = $this->cabangModel->orderBy('is_pusat', 'DESC')->orderBy('id', 'ASC')->findAll();

        $data = [
            'title'       => 'Manajemen Multi-Cabang & Profil Toko',
            'cabang_list' => $cabangList,
            'is_admin'    => (session()->get('role') === 'admin'),
        ];

        return view('cabang/index', $data);
    }

    public function simpan()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Hanya Administrator yang memiliki akses menambah/mengubah data cabang!');
        }

        $id = $this->request->getPost('id');
        $kodeCabang = strtoupper(trim((string)$this->request->getPost('kode_cabang')));
        $namaCabang = trim((string)$this->request->getPost('nama_cabang'));

        if (empty($kodeCabang) || empty($namaCabang)) {
            return redirect()->back()->with('error', 'Kode Cabang dan Nama Cabang wajib diisi!');
        }

        // Cek duplikasi kode cabang
        $existing = $this->cabangModel->where('kode_cabang', $kodeCabang)->first();
        if ($existing && (!$id || $existing['id'] != $id)) {
            return redirect()->back()->with('error', "Kode Cabang '$kodeCabang' sudah digunakan oleh cabang lain!");
        }

        $data = [
            'kode_cabang'  => $kodeCabang,
            'nama_cabang'  => $namaCabang,
            'alamat'       => trim((string)$this->request->getPost('alamat')),
            'kota'         => trim((string)$this->request->getPost('kota')),
            'telepon'      => trim((string)$this->request->getPost('telepon')),
            'footer_struk' => trim((string)$this->request->getPost('footer_struk')),
            'is_pusat'     => $this->request->getPost('is_pusat') ? 1 : 0,
            'status'       => $this->request->getPost('status') ?? 'Aktif',
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        // Jika diset sebagai pusat, cabang lain otomatis bukan pusat
        if ($data['is_pusat'] == 1) {
            $this->cabangModel->where('id !=', $id ?: 0)->set(['is_pusat' => 0])->update();
        }

        if ($id) {
            $oldData = $this->cabangModel->find($id);
            $this->cabangModel->update($id, $data);
            AuditLogModel::log('EDIT_CABANG', 'CABANG', "Memperbarui profil data cabang: {$namaCabang} ({$kodeCabang})", $oldData, $data, $id);
            $msg = "Data Cabang '{$namaCabang}' berhasil diperbarui!";
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $newId = $this->cabangModel->insert($data);
            AuditLogModel::log('TAMBAH_CABANG', 'CABANG', "Menambahkan cabang baru: {$namaCabang} ({$kodeCabang})", null, $data, $newId);
            $msg = "Cabang baru '{$namaCabang}' berhasil ditambahkan ke jaringan toko!";
        }

        return redirect()->to('/cabang')->with('success', $msg);
    }

    public function switchCabang($id)
    {
        $target = $this->cabangModel->find($id);
        if (!$target) {
            return redirect()->back()->with('error', 'Cabang tujuan tidak ditemukan!');
        }

        $oldCabang = session()->get('nama_cabang') ?? 'Sebelumnya';
        session()->set('id_cabang', (int)$target['id']);
        session()->set('nama_cabang', $target['nama_cabang']);
        session()->set('kode_cabang', $target['kode_cabang']);

        AuditLogModel::log('SWITCH_CABANG', 'SISTEM', "Beralih cabang dari '{$oldCabang}' ke '{$target['nama_cabang']}'", null, ['id_cabang' => $target['id']], $target['id']);

        return redirect()->back()->with('success', "Berhasil beralih operasional ke: {$target['nama_cabang']}");
    }

    public function hapus($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Hanya Administrator yang memiliki akses menghapus cabang!');
        }

        $cabang = $this->cabangModel->find($id);
        if (!$cabang) {
            return redirect()->back()->with('error', 'Cabang tidak ditemukan!');
        }

        if ($cabang['is_pusat'] == 1) {
            return redirect()->back()->with('error', 'Cabang Pusat tidak boleh dihapus!');
        }

        $this->cabangModel->delete($id);
        AuditLogModel::log('HAPUS_CABANG', 'CABANG', "Menghapus cabang: {$cabang['nama_cabang']} ({$cabang['kode_cabang']})", $cabang, null, $id);

        return redirect()->to('/cabang')->with('success', "Cabang '{$cabang['nama_cabang']}' berhasil dihapus.");
    }
}
