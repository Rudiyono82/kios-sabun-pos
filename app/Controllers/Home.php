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
        
        $item = $db->table('barang')->where('id_barang', 'B2020438')->get()->getRowArray();
        $pending = $db->table('barang_pending_stok')->where('id_barang', 'B2020438')->where('status_eksekusi', 1)->get()->getRowArray();
        $stokCabang = $db->table('stok_cabang')->where('id_barang', 'B2020438')->where('id_cabang', 1)->get()->getRowArray();

        // Let's test what Pos::simpanTransaksi does step by step
        $log = [];
        $log['barang'] = $item;
        $log['pending'] = $pending;
        $log['stok_cabang'] = $stokCabang;

        $db->transStart();
        try {
            $stokCabangModel = new \App\Models\StokCabangModel();
            $pendingModel = new \App\Models\BarangPendingStokModel();

            $sisaStok = $stokCabangModel->updateStok('B2020438', 1, -1);
            $log['sisaStok'] = $sisaStok;

            $activePending = $pendingModel->getActivePending('B2020438');
            $log['activePending'] = $activePending;
            if ($activePending) {
                $stokPending = (float)$activePending['stok_pending'];
                $sisaStokLama = (float)$sisaStok - $stokPending;
                $statusPersetujuan = trim($activePending['status_persetujuan']);
                $isApproved = str_contains($statusPersetujuan, 'Setuju') && ($statusPersetujuan !== 'Belum Setuju');
                $log['stokLama'] = $sisaStokLama;
                $log['isApproved'] = $isApproved;

                if ($sisaStokLama <= 0 || $sisaStok <= 0) {
                    if ($isApproved) {
                        $log['action'] = 'Calling executePromoteToMaster';
                        $promoteRes = $pendingModel->executePromoteToMaster('B2020438', 'TEST_POS');
                        $log['promoteRes'] = $promoteRes;
                    } else {
                        $log['action'] = 'Unapproved zero stock alert';
                    }
                }
            }

            // Test detail record insert simulation
            $log['transStatusBeforeComplete'] = $db->transStatus();
            $db->transComplete();
            $log['transStatusAfterComplete'] = $db->transStatus();
        } catch (\Throwable $e) {
            $db->transRollback();
            $log['exception'] = $e->getMessage();
            $log['trace'] = $e->getTraceAsString();
        }

        return $this->response->setJSON($log);
    }
}
