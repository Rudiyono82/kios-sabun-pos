<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class Supplier extends BaseController
{
    public function index()
    {
        $supplierModel = new SupplierModel();
        $db = \Config\Database::connect();

        $suppliers = $supplierModel->orderBy('id', 'DESC')->findAll();

        // Hitung transaksi pembelian per supplier jika ada
        foreach ($suppliers as &$sup) {
            $sup['total_pembelian'] = $db->table('pembelian')
                ->where('kode_supplier', $sup['kode_supplier'])
                ->countAllResults();
        }

        $data = [
            'title' => 'Master Data Supplier',
            'suppliers' => $suppliers,
            'total_supplier' => count($suppliers),
            'kode_otomatis' => $supplierModel->generateKodeSupplier(),
        ];

        return view('supplier/index', $data);
    }

    public function simpan()
    {
        $supplierModel = new SupplierModel();
        $id = $this->request->getPost('id');

        $data = [
            'kode_supplier'  => trim($this->request->getPost('kode_supplier')),
            'nama_supplier'  => trim($this->request->getPost('nama_supplier')),
            'alamat'         => trim($this->request->getPost('alamat')),
            'telepon'        => trim($this->request->getPost('telepon')),
            'contact_person' => trim($this->request->getPost('contact_person')),
        ];

        if (empty($data['nama_supplier'])) {
            return redirect()->back()->with('error', 'Nama supplier tidak boleh kosong!');
        }

        if ($id) {
            $supplierModel->update($id, $data);
            return redirect()->to('/supplier')->with('success', 'Data supplier berhasil diperbarui!');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $supplierModel->insert($data);
            return redirect()->to('/supplier')->with('success', 'Supplier baru berhasil ditambahkan!');
        }
    }

    public function hapus($id)
    {
        $db = \Config\Database::connect();
        $supplierModel = new SupplierModel();

        $sup = $supplierModel->find($id);
        if (!$sup) {
            return redirect()->to('/supplier')->with('error', 'Supplier tidak ditemukan!');
        }

        // Cek apakah supplier sudah digunakan dalam transaksi pembelian atau retur
        $cekBeli = $db->table('pembelian')->where('kode_supplier', $sup['kode_supplier'])->countAllResults();
        $cekRetur = $db->table('retur_supplier')->where('kode_supplier', $sup['kode_supplier'])->countAllResults();

        if ($cekBeli > 0 || $cekRetur > 0) {
            return redirect()->to('/supplier')->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat transaksi pembelian / retur!');
        }

        $supplierModel->delete($id);
        return redirect()->to('/supplier')->with('success', 'Data supplier berhasil dihapus!');
    }
}
