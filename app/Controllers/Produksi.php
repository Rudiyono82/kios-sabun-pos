<?php

namespace App\Controllers;

use App\Models\BarangModel;

class Produksi extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $barangModel = new BarangModel();

        // Ambil riwayat produksi
        $produksiList = $db->table('produksi_header')
            ->orderBy('tanggal', 'DESC')
            ->get(100)
            ->getResultArray();

        // Ambil template resep yang tersedia
        $templateList = $db->table('template_produksi')
            ->orderBy('nama_template', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Produksi & Racik Sabun Curah (BOM)',
            'produksi_list' => $produksiList,
            'template_list' => $templateList,
            'barang_list' => $barangModel->where('status', 'Aktif')->orderBy('nama_barang', 'ASC')->findAll(),
        ];

        return view('produksi/index', $data);
    }

    public function resep()
    {
        $db = \Config\Database::connect();
        $barangModel = new BarangModel();

        $templates = $db->table('template_produksi')->orderBy('id', 'DESC')->get()->getResultArray();

        foreach ($templates as &$t) {
            $t['details'] = $db->table('template_produksi_detail')
                ->where('template_id', $t['id'])
                ->get()
                ->getResultArray();
        }

        $data = [
            'title' => 'Master Resep & Formula Sabun (BOM)',
            'templates' => $templates,
            'barang_list' => $barangModel->where('status', 'Aktif')->orderBy('nama_barang', 'ASC')->findAll(),
        ];

        return view('produksi/resep', $data);
    }

    public function simpanResep()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $namaTemplate = $this->request->getPost('nama_template');
        $idBarangJadi = $this->request->getPost('id_barang_jadi');
        $qtyHasil = (float) ($this->request->getPost('qty_hasil') ?: 1);
        $satuanHasil = $this->request->getPost('satuan_hasil') ?: 'JERIGEN 5L';
        $keterangan = $this->request->getPost('keterangan');

        $barangJadi = $db->table('barang')->where('id_barang', $idBarangJadi)->get()->getRowArray();
        if (!$barangJadi) {
            return redirect()->back()->with('error', 'Produk barang jadi tidak valid.');
        }

        $noTemplate = 'TMP' . date('ymd') . rand(100, 999);

        $db->table('template_produksi')->insert([
            'no_template' => $noTemplate,
            'nama_template' => $namaTemplate,
            'id_barang_jadi' => $idBarangJadi,
            'nama_barang_jadi' => $barangJadi['nama_barang'],
            'qty_hasil' => $qtyHasil,
            'satuan_hasil' => $satuanHasil,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $templateId = $db->insertID();

        // Detail Bahan Baku
        $idBahan = $this->request->getPost('id_barang_baku');
        $qtyBahan = $this->request->getPost('qty_per_batch');
        $satuanBahan = $this->request->getPost('satuan_bahan');

        if (!empty($idBahan) && is_array($idBahan)) {
            $batchDetail = [];
            for ($i = 0; $i < count($idBahan); $i++) {
                if (empty($idBahan[$i])) continue;
                $baku = $db->table('barang')->where('id_barang', $idBahan[$i])->get()->getRowArray();
                $batchDetail[] = [
                    'template_id' => $templateId,
                    'id_barang_baku' => $idBahan[$i],
                    'nama_barang_baku' => $baku ? $baku['nama_barang'] : $idBahan[$i],
                    'qty_per_batch' => (float) ($qtyBahan[$i] ?? 1),
                    'satuan' => $satuanBahan[$i] ?? ($baku['satuan'] ?? 'KG')
                ];
            }
            if (!empty($batchDetail)) {
                $db->table('template_produksi_detail')->insertBatch($batchDetail);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan formula resep sabun!');
        }

        return redirect()->to(base_url('produksi/resep'))->with('success', 'Formula resep ' . $namaTemplate . ' berhasil disimpan!');
    }

    public function hapusResep($id)
    {
        $db = \Config\Database::connect();
        $db->table('template_produksi')->where('id', $id)->delete();
        $db->table('template_produksi_detail')->where('template_id', $id)->delete();

        return redirect()->to(base_url('produksi/resep'))->with('success', 'Resep formula sabun berhasil dihapus.');
    }

    public function getResepDetail($id)
    {
        $db = \Config\Database::connect();
        $template = $db->table('template_produksi')->where('id', $id)->get()->getRowArray();
        if (!$template) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Resep tidak ditemukan']);
        }

        $details = $db->table('template_produksi_detail as d')
            ->select('d.*, b.stok as stok_sekarang, b.harga_beli')
            ->join('barang as b', 'b.id_barang = d.id_barang_baku', 'left')
            ->where('d.template_id', $id)
            ->get()
            ->getResultArray();

        $template['bahan'] = $details;
        return $this->response->setJSON($template);
    }

    public function prosesProduksi()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $templateId = $this->request->getPost('template_id');
        $qtyHasil = (float) $this->request->getPost('qty_hasil');
        $keterangan = $this->request->getPost('keterangan');

        if ($qtyHasil <= 0) {
            return redirect()->back()->with('error', 'Jumlah hasil produksi harus lebih dari 0.');
        }

        $template = $db->table('template_produksi')->where('id', $templateId)->get()->getRowArray();
        if (!$template) {
            return redirect()->back()->with('error', 'Resep formula tidak ditemukan.');
        }

        $bahanList = $db->table('template_produksi_detail')->where('template_id', $templateId)->get()->getResultArray();
        if (empty($bahanList)) {
            return redirect()->back()->with('error', 'Resep ini belum memiliki rincian bahan baku.');
        }

        $noProduksi = 'PRD' . date('ymd') . rand(1000, 9999);
        $totalHpp = 0;
        $detailRecords = [];

        // 1. Validasi kecukupan stok seluruh bahan baku terlebih dahulu
        $rasioBatch = $qtyHasil / (float)($template['qty_hasil'] ?: 1);

        foreach ($bahanList as $b) {
            $barangBaku = $db->table('barang')->where('id_barang', $b['id_barang_baku'])->get()->getRowArray();
            $kebutuhan = (float)$b['qty_per_batch'] * $rasioBatch;

            if (!$barangBaku || (float)$barangBaku['stok'] < $kebutuhan) {
                $stokAda = $barangBaku ? (float)$barangBaku['stok'] : 0;
                $db->transRollback();
                return redirect()->back()->with('error', "Stok bahan baku [{$b['nama_barang_baku']}] tidak mencukupi! Dibutuhkan: {$kebutuhan} {$b['satuan']}, Stok gudang saat ini: {$stokAda} {$b['satuan']}. Silakan restock terlebih dahulu.");
            }
        }

        // 2. Eksekusi Pengurangan Stok Bahan Baku
        foreach ($bahanList as $b) {
            $barangBaku = $db->table('barang')->where('id_barang', $b['id_barang_baku'])->get()->getRowArray();
            $qtyPakai = (float)$b['qty_per_batch'] * $rasioBatch;
            $hargaBeli = (float)$barangBaku['harga_beli'];
            $subtotalBahan = $qtyPakai * $hargaBeli;
            $totalHpp += $subtotalBahan;

            $sisaStokBaku = (float)$barangBaku['stok'] - $qtyPakai;
            $db->table('barang')->where('id_barang', $b['id_barang_baku'])->update(['stok' => $sisaStokBaku]);

            // Catat Kartu Stok KELUAR untuk bahan baku
            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $b['id_barang_baku'],
                'tipe' => 'KELUAR',
                'no_referensi' => $noProduksi,
                'masuk' => 0,
                'keluar' => $qtyPakai,
                'sisa' => $sisaStokBaku,
                'keterangan' => 'Bahan Baku Racik Sabun: ' . $template['nama_barang_jadi'] . ' (' . $noProduksi . ')'
            ]);

            $detailRecords[] = [
                'no_produksi' => $noProduksi,
                'id_barang_baku' => $b['id_barang_baku'],
                'nama_barang_baku' => $b['nama_barang_baku'],
                'harga_beli' => $hargaBeli,
                'qty_pakai' => $qtyPakai,
                'satuan' => $b['satuan'],
                'subtotal' => $subtotalBahan
            ];
        }

        $hppPerSatuan = $totalHpp / $qtyHasil;

        // 3. Tambah Stok Barang Jadi yang Dihasilkan
        $barangJadi = $db->table('barang')->where('id_barang', $template['id_barang_jadi'])->get()->getRowArray();
        $stokBaruJadi = (float)($barangJadi['stok'] ?? 0) + $qtyHasil;

        $db->table('barang')->where('id_barang', $template['id_barang_jadi'])->update([
            'stok' => $stokBaruJadi,
            'harga_beli' => $hppPerSatuan // Update HPP riil modal barang jadi
        ]);

        // Catat Kartu Stok MASUK untuk barang jadi
        $db->table('kartu_stok')->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'id_barang' => $template['id_barang_jadi'],
            'tipe' => 'MASUK',
            'no_referensi' => $noProduksi,
            'masuk' => $qtyHasil,
            'keluar' => 0,
            'sisa' => $stokBaruJadi,
            'keterangan' => 'Hasil Produksi / Racik Sabun: ' . $template['nama_barang_jadi'] . ' (' . $qtyHasil . ' ' . $template['satuan_hasil'] . ')'
        ]);

        // 4. Simpan Header & Detail Produksi
        $db->table('produksi_header')->insert([
            'no_produksi' => $noProduksi,
            'tanggal' => date('Y-m-d H:i:s'),
            'template_id' => $templateId,
            'id_barang_jadi' => $template['id_barang_jadi'],
            'nama_barang_jadi' => $template['nama_barang_jadi'],
            'qty_hasil' => $qtyHasil,
            'satuan_hasil' => $template['satuan_hasil'],
            'total_biaya_hpp' => $totalHpp,
            'hpp_per_satuan' => $hppPerSatuan,
            'keterangan' => $keterangan,
            'kode_user' => session()->get('kode_user') ?? 'ADMIN',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $produksiId = $db->insertID();

        foreach ($detailRecords as &$rec) {
            $rec['produksi_id'] = $produksiId;
        }
        $db->table('produksi_detail')->insertBatch($detailRecords);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses produksi sabun!');
        }

        return redirect()->to(base_url('produksi'))->with('success', "Produksi nomor {$noProduksi} berhasil! Telah ditambahkan {$qtyHasil} {$template['satuan_hasil']} {$template['nama_barang_jadi']} ke inventori toko dengan HPP Rp " . number_format($hppPerSatuan, 0, ',', '.') . "/satuan.");
    }
}

