<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function testSimpanEkonomi()
    {
        $db = \Config\Database::connect();
        try {
            $db->transStart();

            $item = [
                'id_barang' => 'B2020438',
                'qty' => 1,
                'harga_jual' => 10000
            ];

            $barang = $db->table('barang')->where('id_barang', $item['id_barang'])->get()->getRowArray();
            if (!$barang) {
                return $this->response->setJSON(['error' => 'Barang not found']);
            }

            if (($barang['status'] ?? '') !== 'Aktif') {
                return $this->response->setJSON(['error' => 'Barang status not Aktif: ' . ($barang['status'] ?? 'null')]);
            }

            $idCabang = 1;
            $stokCabangModel = new \App\Models\StokCabangModel();
            $sisaStok = $stokCabangModel->updateStok($item['id_barang'], $idCabang, -$item['qty']);

            $pendingModel = new \App\Models\BarangPendingStokModel();
            $activePending = $pendingModel->getActivePending($item['id_barang']);
            $promoteRes = null;
            if ($activePending) {
                $stokPending = (float)$activePending['stok_pending'];
                $sisaStokLama = (float)$sisaStok - $stokPending;
                $statusPersetujuan = trim($activePending['status_persetujuan']);
                $isApproved = str_contains($statusPersetujuan, 'Setuju') && ($statusPersetujuan !== 'Belum Setuju');
                if ($sisaStokLama <= 0 || $sisaStok <= 0) {
                    if ($isApproved) {
                        $promoteRes = $pendingModel->executePromoteToMaster($item['id_barang'], 'POS_AUTO_PROMOTE');
                    }
                }
            }

            $penjualanModel = new \App\Models\PenjualanModel();
            $noFaktur = $penjualanModel->generateNoFaktur();

            $db->table('penjualan')->insert([
                'id_cabang' => $idCabang,
                'no_faktur' => $noFaktur,
                'tanggal' => date('Y-m-d H:i:s'),
                'kode_user' => 'TEST_DEBUG',
                'nama_kasir' => 'Test Kasir',
                'total_item' => 1,
                'total_qty' => 1,
                'subtotal' => 10000,
                'grand_total' => 10000,
                'bayar' => 10000,
                'kembalian' => 0,
                'metode_bayar' => 'Cash'
            ]);

            $db->table('penjualan_detail')->insert([
                'no_faktur' => $noFaktur,
                'id_barang' => $item['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'harga_beli' => (float)$barang['harga_beli'],
                'harga_jual' => (float)$item['harga_jual'],
                'qty' => 1,
                'subtotal' => 10000,
                'profit' => 3500
            ]);

            $db->table('kartu_stok')->insert([
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $item['id_barang'],
                'tipe' => 'KELUAR',
                'no_referensi' => $noFaktur,
                'masuk' => 0,
                'keluar' => 1,
                'sisa' => $sisaStok,
                'keterangan' => 'Test Penjualan Kasir ' . $noFaktur
            ]);

            // Always rollback in test
            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'success',
                'sisaStok' => $sisaStok,
                'activePending' => $activePending,
                'promoteRes' => $promoteRes,
                'noFaktur' => $noFaktur
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'exception',
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
