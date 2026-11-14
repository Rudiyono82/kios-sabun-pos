<?php

namespace App\Controllers;

use App\Models\TransferStokModel;
use App\Models\TransferStokDetailModel;
use App\Models\CabangModel;
use App\Models\BarangModel;
use App\Models\StokCabangModel;
use App\Models\AuditLogModel;

class TransferStok extends BaseController
{
    protected $transferModel;
    protected $detailModel;
    protected $cabangModel;
    protected $barangModel;
    protected $stokCabangModel;

    public function __construct()
    {
        $this->transferModel   = new TransferStokModel();
        $this->detailModel     = new TransferStokDetailModel();
        $this->cabangModel     = new CabangModel();
        $this->barangModel     = new BarangModel();
        $this->stokCabangModel = new StokCabangModel();
    }

    public function index()
    {
        $session = session();
        $currentCabangId = $session->get('id_cabang') ?? 1;
        $isAdmin = ($session->get('role') === 'admin');

        $db = \Config\Database::connect();
        $builder = $db->table('transfer_stok as t')
            ->select('t.*, ca.nama_cabang as nama_cabang_asal, ca.kode_cabang as kode_cabang_asal, ct.nama_cabang as nama_cabang_tujuan, ct.kode_cabang as kode_cabang_tujuan')
            ->join('cabang as ca', 'ca.id = t.id_cabang_asal', 'left')
            ->join('cabang as ct', 'ct.id = t.id_cabang_tujuan', 'left')
            ->orderBy('t.id', 'DESC');

        if (!$isAdmin) {
            // Kasir/staf hanya melihat transfer yang melibatkan cabangnya
            $builder->groupStart()
                ->where('t.id_cabang_asal', $currentCabangId)
                ->orWhere('t.id_cabang_tujuan', $currentCabangId)
            ->groupEnd();
        }

        $transfers = $builder->get()->getResultArray();

        $data = [
            'title'           => 'Mutasi & Transfer Stok Antar Cabang',
            'transfers'       => $transfers,
            'currentCabangId' => $currentCabangId,
            'isAdmin'         => $isAdmin,
        ];

        return view('transfer_stok/index', $data);
    }

    public function tambah()
    {
        $session = session();
        $currentCabangId = $session->get('id_cabang') ?? 1;
        $currentCabang = $this->cabangModel->find($currentCabangId);

        // Cabang tujuan adalah cabang aktif selain cabang saat ini
        $cabangTujuanList = $this->cabangModel->where('status', 'Aktif')
                                              ->where('id !=', $currentCabangId)
                                              ->findAll();

        // Ambil daftar barang beserta stok di cabang asal
        $db = \Config\Database::connect();
        $barangList = $db->table('barang as b')
            ->select('b.id, b.id_barang, b.id_barang as kode_barang, b.nama_barang, b.satuan, COALESCE(sc.stok, 0) as stok_cabang')
            ->join('stok_cabang as sc', 'sc.id_barang = b.id_barang AND sc.id_cabang = ' . (int)$currentCabangId, 'left')
            ->where('b.status', 'Aktif')
            ->orderBy('b.nama_barang', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title'            => 'Kirim Transfer Stok ke Cabang Lain',
            'no_transfer'      => TransferStokModel::generateNoTransfer(),
            'currentCabang'    => $currentCabang,
            'cabangTujuanList' => $cabangTujuanList,
            'barangList'       => $barangList,
        ];

        return view('transfer_stok/tambah', $data);
    }

    public function simpan()
    {
        $session = session();
        $currentCabangId = (int)($session->get('id_cabang') ?? 1);
        $idCabangTujuan  = (int)$this->request->getPost('id_cabang_tujuan');
        $catatan         = trim((string)$this->request->getPost('catatan'));
        $items           = $this->request->getPost('items');

        if (empty($idCabangTujuan)) {
            return redirect()->back()->with('error', 'Pilih cabang tujuan pengiriman stok!');
        }

        if ($idCabangTujuan === $currentCabangId) {
            return redirect()->back()->with('error', 'Cabang tujuan tidak boleh sama dengan cabang asal!');
        }

        if (empty($items) || !is_array($items)) {
            return redirect()->back()->with('error', 'Tambahkan minimal satu barang untuk ditransfer!');
        }

        $cabangAsal   = $this->cabangModel->find($currentCabangId);
        $cabangTujuan = $this->cabangModel->find($idCabangTujuan);

        $db = \Config\Database::connect();
        $db->transStart();

        $noTransfer = TransferStokModel::generateNoTransfer();
        $totalQty = 0;
        $totalItem = 0;
        $detailToInsert = [];

        foreach ($items as $item) {
            $idBarang = trim((string)($item['id_barang'] ?? ''));
            $qty = (float)($item['qty'] ?? 0);

            if (empty($idBarang) || $qty <= 0) {
                continue;
            }

            // Validasi stok di cabang asal
            $stokAsal = $this->stokCabangModel->getStok($idBarang, $currentCabangId);
            $barang = $this->barangModel->where('id_barang', $idBarang)->first() ?? $this->barangModel->find($idBarang);
            $namaBarang = $barang ? $barang['nama_barang'] : "Barang #$idBarang";

            if ($stokAsal < $qty) {
                $db->transRollback();
                return redirect()->back()->with('error', "Stok barang '{$namaBarang}' tidak mencukupi di {$cabangAsal['nama_cabang']} (Tersedia: {$stokAsal}, Diminta: {$qty})");
            }

            // Potong stok di cabang asal
            $this->stokCabangModel->updateStok($idBarang, $currentCabangId, -$qty);

            $detailToInsert[] = [
                'id_barang'   => $idBarang,
                'nama_barang' => $namaBarang,
                'satuan'      => $barang['satuan'] ?? 'Pcs',
                'qty'         => $qty,
                'qty_terima'  => 0,
                'catatan'     => $item['catatan'] ?? ''
            ];

            $totalQty += $qty;
            $totalItem++;
        }

        if ($totalItem === 0) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Tidak ada barang valid dengan qty > 0!');
        }

        // Simpan header transfer_stok
        $headerData = [
            'no_transfer'        => $noTransfer,
            'tanggal'            => date('Y-m-d H:i:s'),
            'id_cabang_asal'     => $currentCabangId,
            'id_cabang_tujuan'   => $idCabangTujuan,
            'total_item'         => $totalItem,
            'total_qty'          => $totalQty,
            'status'             => 'Dikirim',
            'catatan'            => $catatan,
            'kode_user_pengirim' => $session->get('username') ?? 'KASIR',
            'nama_user_pengirim' => $session->get('nama_user') ?? 'Kasir / Staf',
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        $transferId = $this->transferModel->insert($headerData);

        // Simpan detail item
        foreach ($detailToInsert as $detail) {
            $detail['transfer_id'] = $transferId;
            $this->detailModel->insert($detail);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses transaksi transfer stok!');
        }

        AuditLogModel::log(
            'TRANSFER_KIRIM',
            'TRANSFER_STOK',
            "Pengiriman transfer stok {$noTransfer} ({$totalItem} item, {$totalQty} pcs) dari {$cabangAsal['nama_cabang']} ke {$cabangTujuan['nama_cabang']}",
            null,
            ['no_transfer' => $noTransfer, 'asal' => $cabangAsal['nama_cabang'], 'tujuan' => $cabangTujuan['nama_cabang'], 'total_qty' => $totalQty],
            $currentCabangId
        );

        return redirect()->to('/transfer-stok')->with('success', "Transfer stok {$noTransfer} berhasil dikirim ke {$cabangTujuan['nama_cabang']}!");
    }

    public function detail($id)
    {
        $transfer = $this->getTransferWithBranches($id);
        if (!$transfer) {
            return redirect()->to('/transfer-stok')->with('error', 'Data transfer tidak ditemukan!');
        }

        $details = $this->detailModel->where('transfer_id', $id)->findAll();

        $data = [
            'title'    => "Detail Transfer {$transfer['no_transfer']}",
            'transfer' => $transfer,
            'details'  => $details,
        ];

        return view('transfer_stok/detail', $data);
    }

    public function terima($id)
    {
        $session = session();
        $currentCabangId = (int)($session->get('id_cabang') ?? 1);
        $isAdmin = ($session->get('role') === 'admin');

        $transfer = $this->getTransferWithBranches($id);
        if (!$transfer) {
            return redirect()->to('/transfer-stok')->with('error', 'Data transfer tidak ditemukan!');
        }

        if ($transfer['status'] === 'Diterima') {
            return redirect()->back()->with('warning', 'Barang transfer ini sudah pernah dikonfirmasi diterima!');
        }

        // Verifikasi hak penerimaan: hanya cabang tujuan atau admin
        if (!$isAdmin && (int)$transfer['id_cabang_tujuan'] !== $currentCabangId) {
            return redirect()->back()->with('error', 'Hanya staf di cabang tujuan atau Admin yang berhak mengonfirmasi penerimaan barang!');
        }

        $details = $this->detailModel->where('transfer_id', $id)->findAll();

        $db = \Config\Database::connect();
        $db->transStart();

        // Tambah stok ke cabang tujuan
        foreach ($details as $item) {
            $qty = (float)$item['qty'];
            $this->stokCabangModel->updateStok($item['id_barang'], $transfer['id_cabang_tujuan'], $qty);

            // Update qty_terima di detail
            $this->detailModel->update($item['id'], ['qty_terima' => $qty]);
        }

        // Update status header transfer
        $this->transferModel->update($id, [
            'status'             => 'Diterima',
            'kode_user_penerima' => $session->get('username') ?? 'KASIR',
            'nama_user_penerima' => $session->get('nama_user') ?? 'Kasir / Staf',
            'tgl_terima'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses penerimaan stok!');
        }

        AuditLogModel::log(
            'TRANSFER_TERIMA',
            'TRANSFER_STOK',
            "Konfirmasi penerimaan transfer stok {$transfer['no_transfer']} di cabang {$transfer['nama_cabang_tujuan']}",
            ['status' => 'Dikirim'],
            ['status' => 'Diterima', 'tgl_terima' => date('Y-m-d H:i:s')],
            $transfer['id_cabang_tujuan']
        );

        return redirect()->to('/transfer-stok')->with('success', "Penerimaan transfer stok {$transfer['no_transfer']} berhasil! Stok cabang {$transfer['nama_cabang_tujuan']} telah bertambah.");
    }

    public function cetak($id)
    {
        $transfer = $this->getTransferWithBranches($id);
        if (!$transfer) {
            return 'Data transfer stok tidak ditemukan!';
        }

        $details = $this->detailModel->where('transfer_id', $id)->findAll();

        $data = [
            'transfer' => $transfer,
            'details'  => $details,
        ];

        return view('transfer_stok/cetak', $data);
    }

    private function getTransferWithBranches($id)
    {
        $db = \Config\Database::connect();
        return $db->table('transfer_stok as t')
            ->select('t.*, ca.nama_cabang as nama_cabang_asal, ca.kode_cabang as kode_cabang_asal, ca.alamat as alamat_asal, ca.telepon as telp_asal,
                      ct.nama_cabang as nama_cabang_tujuan, ct.kode_cabang as kode_cabang_tujuan, ct.alamat as alamat_tujuan, ct.telepon as telp_tujuan')
            ->join('cabang as ca', 'ca.id = t.id_cabang_asal', 'left')
            ->join('cabang as ct', 'ct.id = t.id_cabang_tujuan', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();
    }
}

