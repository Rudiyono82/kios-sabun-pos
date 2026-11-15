<?php

namespace App\Controllers;

use App\Models\ReturSupplierModel;
use App\Models\BarangModel;
use App\Models\SupplierModel;
use App\Models\AuditLogModel;

class Retur extends BaseController
{
    public function index()
    {
        $returModel = new ReturSupplierModel();
        $barangModel = new BarangModel();
        $supplierModel = new SupplierModel();

        $data = [
            'title' => 'Retur Barang ke Supplier (Rijek / Expired)',
            'no_retur' => $returModel->generateNoRetur(),
            'retur_list' => $returModel->orderBy('tanggal', 'DESC')->findAll(150),
            'suppliers' => $supplierModel->orderBy('nama_supplier', 'ASC')->findAll(),
            'barang_list' => $barangModel->where('status', 'Aktif')->orderBy('nama_barang', 'ASC')->findAll(),
        ];

        return view('retur/index', $data);
    }

    public function simpan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $returModel = new ReturSupplierModel();
        $noRetur = $returModel->generateNoRetur();

        $inputTanggal = trim((string)$this->request->getPost('tanggal'));
        if (!empty($inputTanggal)) {
            $tanggalRetur = (strlen($inputTanggal) === 10) ? $inputTanggal . ' ' . date('H:i:s') : date('Y-m-d H:i:s', strtotime($inputTanggal));
        } else {
            $tanggalRetur = date('Y-m-d H:i:s');
        }

        $kodeSupplier = trim((string)$this->request->getPost('kode_supplier'));
        if (empty($kodeSupplier)) {
            return redirect()->back()->with('error', 'Wajib memilih Mitra Supplier Tujuan retur!');
        }

        $supplier = $db->table('supplier')->where('kode_supplier', $kodeSupplier)->get()->getRowArray();
        $namaSupplier = $supplier ? $supplier['nama_supplier'] : 'Supplier Umum';

        $statusAwal = $this->request->getPost('status') ?: 'Baru Proses';
        $tipePenyelesaian = $this->request->getPost('tipe_penyelesaian');
        $keterangan = $this->request->getPost('keterangan');

        // Ambil data items (bisa dari JSON string di input items_json atau POST items)
        $rawItemsJson = $this->request->getPost('items_json');
        $items = [];
        if (!empty($rawItemsJson)) {
            $items = json_decode($rawItemsJson, true);
        }

        // Fallback untuk single-item (jika submit langsung)
        if (empty($items)) {
            $idBarangSingle = $this->request->getPost('id_barang_retur');
            $qtySingle = (float) $this->request->getPost('qty_retur');
            $alasanSingle = $this->request->getPost('alasan') ?: 'Rijek / Bocor';
            if (!empty($idBarangSingle) && $qtySingle > 0) {
                $items[] = [
                    'id_barang' => $idBarangSingle,
                    'qty'       => $qtySingle,
                    'alasan'    => $alasanSingle,
                ];
            }
        }

        if (empty($items)) {
            return redirect()->back()->with('error', 'Daftar barang yang diretur tidak boleh kosong! Tambahkan minimal 1 barang ke dalam tabel keranjang retur.');
        }

        $totalQtyRetur = 0;
        $totalNilaiRetur = 0;
        $detailRecords = [];
        $namaBarangSummary = [];

        foreach ($items as $item) {
            $idBarang = $item['id_barang'];
            $qty = (float) $item['qty'];
            $alasan = $item['alasan'] ?? 'Rijek / Bocor';

            $barang = $db->table('barang')->where('id_barang', $idBarang)->get()->getRowArray();
            if (!$barang) continue;

            $hargaBeli = (float) $barang['harga_beli'];
            $subtotal = $qty * $hargaBeli;

            $totalQtyRetur += $qty;
            $totalNilaiRetur += $subtotal;
            $namaBarangSummary[] = $barang['nama_barang'] . " (" . number_format($qty, 0, ',', '.') . ")";

            // 1. MUTASI STOK: Barang rijek/expired SELALU DIPOTONG KELUAR dari stok toko hari ini
            $sisaStok = $barang['stok'] - $qty;
            $db->table('barang')->where('id_barang', $idBarang)->update(['stok' => $sisaStok]);

            // Catat kartu stok KELUAR untuk barang rijek
            $db->table('kartu_stok')->insert([
                'tanggal'      => $tanggalRetur,
                'id_barang'    => $idBarang,
                'tipe'         => 'KELUAR',
                'no_referensi' => $noRetur,
                'masuk'        => 0,
                'keluar'       => $qty,
                'sisa'         => $sisaStok,
                'keterangan'   => 'Retur ke Supplier (' . $alasan . ') - ' . $namaSupplier . ' [' . $statusAwal . ']'
            ]);

            $detailRecords[] = [
                'no_retur'          => $noRetur,
                'id_barang_retur'   => $idBarang,
                'nama_barang_retur' => $barang['nama_barang'],
                'satuan'            => $barang['satuan'] ?? 'Pcs',
                'qty_retur'         => $qty,
                'harga_beli'        => $hargaBeli,
                'subtotal_nilai'    => $subtotal,
                'alasan'            => $alasan,
                'status_item'       => $statusAwal,
                'created_at'        => $tanggalRetur
            ];
        }

        if (empty($detailRecords)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Semua barang dalam keranjang retur tidak valid di sistem.');
        }

        $db->table('retur_supplier_detail')->insertBatch($detailRecords);

        $idBarangGanti = null;
        $namaBarangGanti = null;
        $qtyGanti = 0;
        $nilaiGanti = 0;
        $tglSelesai = null;

        // 2. Jika Langsung Selesai di Hari yang Sama (Supplier langsung membawa pengganti)
        if ($statusAwal === 'Selesai') {
            $tglSelesai = date('Y-m-d H:i:s');

            if ($tipePenyelesaian === 'Ganti_Barang_Sama') {
                // Semua barang di detail otomatis diganti dengan barang baru yang sama
                foreach ($detailRecords as $det) {
                    $brg = $db->table('barang')->where('id_barang', $det['id_barang_retur'])->get()->getRowArray();
                    if ($brg) {
                        $sisaSetelahGanti = $brg['stok'] + (float)$det['qty_retur'];
                        $db->table('barang')->where('id_barang', $det['id_barang_retur'])->update(['stok' => $sisaSetelahGanti]);

                        $db->table('kartu_stok')->insert([
                            'tanggal'      => date('Y-m-d H:i:s'),
                            'id_barang'    => $det['id_barang_retur'],
                            'tipe'         => 'MASUK',
                            'no_referensi' => $noRetur,
                            'masuk'        => $det['qty_retur'],
                            'keluar'       => 0,
                            'sisa'         => $sisaSetelahGanti,
                            'keterangan'   => 'Penggantian Barang Baru dari Retur ' . $noRetur . ' (' . $namaSupplier . ')'
                        ]);
                    }
                }
                $namaBarangGanti = 'Barang Sama Sesuai Daftar Item Retur';
                $qtyGanti = $totalQtyRetur;
                $nilaiGanti = $totalNilaiRetur;
            } elseif ($tipePenyelesaian === 'Ganti_Barang_Lain') {
                $idBarangGanti = $this->request->getPost('id_barang_ganti');
                $qtyGanti = (float) $this->request->getPost('qty_ganti');

                $barangGanti = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
                if ($barangGanti) {
                    $namaBarangGanti = $barangGanti['nama_barang'];
                    $nilaiGanti = $qtyGanti * (float)$barangGanti['harga_beli'];

                    $sisaBarangGanti = $barangGanti['stok'] + $qtyGanti;
                    $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $sisaBarangGanti]);

                    $db->table('kartu_stok')->insert([
                        'tanggal'      => date('Y-m-d H:i:s'),
                        'id_barang'    => $idBarangGanti,
                        'tipe'         => 'MASUK',
                        'no_referensi' => $noRetur,
                        'masuk'        => $qtyGanti,
                        'keluar'       => 0,
                        'sisa'         => $sisaBarangGanti,
                        'keterangan'   => 'Barang Pengganti dari Retur ' . $noRetur . ' (' . $namaSupplier . ')'
                    ]);
                }
            } elseif ($tipePenyelesaian === 'Ganti_Uang_Kas') {
                $rawNilaiGanti = $this->request->getPost('nilai_ganti');
                $nilaiGanti = $rawNilaiGanti !== null && $rawNilaiGanti !== '' ? (float) str_replace(['.', ',', ' '], '', (string)$rawNilaiGanti) : (float)$totalNilaiRetur;
                $db->table('data_keuangan')->insert([
                    'tanggal'        => date('Y-m-d H:i:s'),
                    'jenis_keuangan' => 'Pemasukan',
                    'nama_keuangan'  => 'Kompensasi Uang Retur ' . $noRetur . ' (' . $namaSupplier . ')',
                    'total'          => $nilaiGanti,
                    'keterangan'     => 'Penggantian Nilai Uang Retur: ' . count($detailRecords) . ' Macam Barang (' . $namaSupplier . ')',
                    'kode_user'      => session()->get('kode_user') ?? 'ADMIN'
                ]);
            }
        } else {
            $tipePenyelesaian = 'Menunggu Penggantian Supplier';
        }

        // Ringkasan Tampilan Barang Diretur di Master Record
        $summaryBarang = (count($namaBarangSummary) > 1) 
            ? count($namaBarangSummary) . ' Macam Barang: ' . implode(', ', array_slice($namaBarangSummary, 0, 2)) . (count($namaBarangSummary) > 2 ? '...' : '')
            : ($namaBarangSummary[0] ?? '-');

        $userPengaju = session()->get('kode_user') ?? session()->get('username') ?? 'ADMIN';
        $userSelesai = ($statusAwal === 'Selesai') ? $userPengaju : null;

        // Simpan Record Retur Master
        $returModel->insert([
            'no_retur'          => $noRetur,
            'tanggal'           => $tanggalRetur,
            'tgl_selesai'       => $tglSelesai,
            'kode_supplier'     => $kodeSupplier,
            'nama_supplier'     => $namaSupplier,
            'id_barang_retur'   => $detailRecords[0]['id_barang_retur'] ?? '',
            'nama_barang_retur' => $summaryBarang,
            'qty_retur'         => $totalQtyRetur,
            'nilai_retur'       => $totalNilaiRetur,
            'total_item'        => count($detailRecords),
            'alasan'            => (count($detailRecords) > 1) ? 'Multi-Barang (' . count($detailRecords) . ' Item)' : ($detailRecords[0]['alasan'] ?? 'Rijek / Bocor'),
            'tipe_penyelesaian' => $tipePenyelesaian,
            'id_barang_ganti'   => $idBarangGanti,
            'nama_barang_ganti' => $namaBarangGanti,
            'qty_ganti'         => $qtyGanti,
            'nilai_ganti'       => $nilaiGanti,
            'status'            => $statusAwal,
            'keterangan'        => $keterangan,
            'kode_user'         => $userPengaju,
            'user_selesai'      => $userSelesai,
            'created_at'        => $tanggalRetur
        ]);

        // Audit Trail Pencatatan Retur
        AuditLogModel::log(
            'INPUT_RETUR',
            'RETUR_SUPPLIER',
            "Mencatat Dokumen Retur {$noRetur} (" . count($detailRecords) . " macam barang, " . number_format($totalQtyRetur, 0, ',', '.') . " unit) ke Supplier {$namaSupplier} senilai Rp " . number_format($totalNilaiRetur, 0, ',', '.') . " [Tgl: " . date('d/m/Y', strtotime($tanggalRetur)) . ", Status: {$statusAwal}]",
            null,
            [
                'no_retur'      => $noRetur,
                'tanggal'       => $tanggalRetur,
                'kode_supplier' => $kodeSupplier,
                'nama_supplier' => $namaSupplier,
                'total_item'    => count($detailRecords),
                'total_qty'     => $totalQtyRetur,
                'total_nilai'   => $totalNilaiRetur,
                'status'        => $statusAwal,
                'kode_user'     => $userPengaju,
                'items'         => array_map(function($d) {
                    return [
                        'id_barang'   => $d['id_barang_retur'],
                        'nama_barang' => $d['nama_barang_retur'],
                        'qty'         => $d['qty_retur'],
                        'harga_beli'  => $d['harga_beli'],
                        'subtotal'    => $d['subtotal_nilai'],
                        'alasan'      => $d['alasan'],
                    ];
                }, $detailRecords)
            ]
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses data retur barang!');
        }

        $pesan = ($statusAwal === 'Baru Proses')
            ? 'Retur nomor ' . $noRetur . ' (' . count($detailRecords) . ' macam barang) dicatat dengan status BARU PROSES. Seluruh barang rijek telah dikeluarkan dari stok toko dan menunggu penggantian dari supplier.'
            : 'Transaksi retur nomor ' . $noRetur . ' (' . count($detailRecords) . ' macam barang) berhasil diproses dan diselesaikan langsung.';

        return redirect()->to(base_url('retur'))->with('success', $pesan);
    }

    public function selesaikan()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $id = $this->request->getPost('id');
        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);

        if (!$retur) {
            return redirect()->back()->with('error', 'Data retur tidak ditemukan.');
        }

        if ($retur['status'] === 'Selesai') {
            return redirect()->back()->with('error', 'Retur ini sudah diselesaikan sebelumnya secara tuntas.');
        }

        $inputTglSelesai = trim((string)$this->request->getPost('tgl_selesai'));
        if (!empty($inputTglSelesai)) {
            $tglSelesai = (strlen($inputTglSelesai) === 10) ? $inputTglSelesai . ' ' . date('H:i:s') : date('Y-m-d H:i:s', strtotime($inputTglSelesai));
        } else {
            $tglSelesai = date('Y-m-d H:i:s');
        }
        $userSelesai = session()->get('kode_user') ?? session()->get('username') ?? 'ADMIN';
        $catatanPenyelesaian = $this->request->getPost('catatan_penyelesaian');

        // Pastikan tabel detail memiliki data barang untuk dokumen retur ini
        $existingDetails = $db->table('retur_supplier_detail')->where('no_retur', $retur['no_retur'])->get()->getResultArray();
        if (empty($existingDetails) && !empty($retur['id_barang_retur'])) {
            $db->table('retur_supplier_detail')->insert([
                'no_retur'          => $retur['no_retur'],
                'id_barang_retur'   => $retur['id_barang_retur'],
                'nama_barang_retur' => $retur['nama_barang_retur'],
                'satuan'            => 'Pcs',
                'qty_retur'         => (float)$retur['qty_retur'],
                'harga_beli'        => ($retur['qty_retur'] > 0 ? ((float)$retur['nilai_retur'] / (float)$retur['qty_retur']) : 0),
                'subtotal_nilai'    => (float)$retur['nilai_retur'],
                'alasan'            => $retur['alasan'] ?? 'Rijek / Bocor',
                'qty_ganti'         => (float)$retur['qty_ganti'],
                'nilai_ganti'       => (float)$retur['nilai_ganti'],
                'status_item'       => $retur['status'],
                'created_at'        => $retur['tanggal']
            ]);
            $existingDetails = $db->table('retur_supplier_detail')->where('no_retur', $retur['no_retur'])->get()->getResultArray();
        }

        $rawItemsJson = $this->request->getPost('items_json');
        $items = !empty($rawItemsJson) ? json_decode($rawItemsJson, true) : [];

        $processedCount = 0;
        $processedSummary = [];

        if (!empty($items)) {
            // MODE MULTI-ITEM / PENYELESAIAN PARSIAL (BERTAHAP)
            foreach ($items as $item) {
                $idDetail = (int)($item['id_detail'] ?? 0);
                $tipe = $item['tipe'] ?? 'Ganti_Barang_Sama';
                $qtyDatang = (float)($item['qty_datang'] ?? 0);
                $nilaiUang = (float)str_replace(['.', ',', ' '], '', (string)($item['nilai_uang'] ?? 0));
                $idBarangGanti = $item['id_barang_ganti'] ?? null;

                $det = $db->table('retur_supplier_detail')->where('id', $idDetail)->get()->getRowArray();
                if (!$det) continue;

                $sisaQty = (float)$det['qty_retur'] - (float)$det['qty_ganti'];
                if ($sisaQty <= 0 && $det['status_item'] === 'Selesai') continue;

                if ($tipe === 'Ganti_Barang_Sama') {
                    if ($qtyDatang <= 0) continue;
                    if ($qtyDatang > $sisaQty) $qtyDatang = $sisaQty;

                    $idBrg = $det['id_barang_retur'];
                    $brg = $db->table('barang')->where('id_barang', $idBrg)->get()->getRowArray();
                    if ($brg) {
                        $stokBaru = (float)$brg['stok'] + $qtyDatang;
                        $db->table('barang')->where('id_barang', $idBrg)->update(['stok' => $stokBaru]);

                        $db->table('kartu_stok')->insert([
                            'tanggal'      => $tglSelesai,
                            'id_barang'    => $idBrg,
                            'tipe'         => 'MASUK',
                            'no_referensi' => $retur['no_retur'],
                            'masuk'        => $qtyDatang,
                            'keluar'       => 0,
                            'sisa'         => $stokBaru,
                            'keterangan'   => 'Penerimaan Parsial Barang Baru Pengganti Retur ' . $retur['no_retur'] . ' (' . $retur['nama_supplier'] . ')'
                        ]);
                    }

                    $newQtyGanti = (float)$det['qty_ganti'] + $qtyDatang;
                    $newNilaiGanti = (float)$det['nilai_ganti'] + ($qtyDatang * (float)$det['harga_beli']);
                    $newStatus = ($newQtyGanti >= (float)$det['qty_retur']) ? 'Selesai' : 'Proses Sebagian';

                    $db->table('retur_supplier_detail')->where('id', $idDetail)->update([
                        'qty_ganti'          => $newQtyGanti,
                        'nilai_ganti'        => $newNilaiGanti,
                        'status_item'        => $newStatus,
                        'tipe_penyelesaian'  => 'Ganti_Barang_Sama',
                        'tgl_selesai'        => $tglSelesai
                    ]);

                    $processedCount++;
                    $processedSummary[] = "{$det['nama_barang_retur']} (Datang: {$qtyDatang} {$det['satuan']}, Status: {$newStatus})";

                } elseif ($tipe === 'Ganti_Barang_Lain') {
                    if ($qtyDatang <= 0) continue;
                    if ($qtyDatang > $sisaQty) $qtyDatang = $sisaQty;

                    $brgB = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
                    if (!$brgB) continue;

                    $stokBaruB = (float)$brgB['stok'] + $qtyDatang;
                    $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $stokBaruB]);

                    $db->table('kartu_stok')->insert([
                        'tanggal'      => $tglSelesai,
                        'id_barang'    => $idBarangGanti,
                        'tipe'         => 'MASUK',
                        'no_referensi' => $retur['no_retur'],
                        'masuk'        => $qtyDatang,
                        'keluar'       => 0,
                        'sisa'         => $stokBaruB,
                        'keterangan'   => 'Penerimaan Barang Lain Pengganti Retur ' . $retur['no_retur'] . ' (' . $brgB['nama_barang'] . ' x ' . $qtyDatang . ') dari ' . $retur['nama_supplier']
                    ]);

                    $newQtyGanti = (float)$det['qty_ganti'] + $qtyDatang;
                    $newNilaiGanti = (float)$det['nilai_ganti'] + ($qtyDatang * (float)($brgB['harga_beli'] ?? 0));
                    $newStatus = ($newQtyGanti >= (float)$det['qty_retur']) ? 'Selesai' : 'Proses Sebagian';

                    $db->table('retur_supplier_detail')->where('id', $idDetail)->update([
                        'id_barang_ganti'    => $idBarangGanti,
                        'nama_barang_ganti'  => $brgB['nama_barang'],
                        'qty_ganti'          => $newQtyGanti,
                        'nilai_ganti'        => $newNilaiGanti,
                        'status_item'        => $newStatus,
                        'tipe_penyelesaian'  => 'Ganti_Barang_Lain',
                        'tgl_selesai'        => $tglSelesai
                    ]);

                    $processedCount++;
                    $processedSummary[] = "{$det['nama_barang_retur']} diganti {$brgB['nama_barang']} (Datang: {$qtyDatang}, Status: {$newStatus})";

                } elseif ($tipe === 'Ganti_Uang_Kas') {
                    if ($nilaiUang <= 0) {
                        $nilaiUang = $sisaQty * (float)$det['harga_beli'];
                    }

                    $db->table('data_keuangan')->insert([
                        'tanggal'        => $tglSelesai,
                        'jenis_keuangan' => 'Pemasukan',
                        'nama_keuangan'  => 'Kompensasi Penggantian Uang Retur ' . $retur['no_retur'],
                        'total'          => $nilaiUang,
                        'keterangan'     => 'Penggantian Nilai Uang Retur ' . $det['nama_barang_retur'] . ' dari ' . $retur['nama_supplier'],
                        'kode_user'      => $userSelesai
                    ]);

                    $db->table('retur_supplier_detail')->where('id', $idDetail)->update([
                        'qty_ganti'          => (float)$det['qty_retur'], // Dianggap tuntas karena sudah dikompensasi uang
                        'nilai_ganti'        => (float)$det['nilai_ganti'] + $nilaiUang,
                        'status_item'        => 'Selesai',
                        'tipe_penyelesaian'  => 'Ganti_Uang_Kas',
                        'tgl_selesai'        => $tglSelesai
                    ]);

                    $processedCount++;
                    $processedSummary[] = "{$det['nama_barang_retur']} diganti uang Rp " . number_format($nilaiUang, 0, ',', '.') . " (Status: Selesai)";
                }
            }
        } else {
            // FALLBACK MODE LEGACY
            $tipePenyelesaian = $this->request->getPost('tipe_penyelesaian') ?: 'Ganti_Barang_Sama';
            $qtyGanti = (float)$this->request->getPost('qty_ganti');
            $idBarangGanti = null;
            $namaBarangGanti = null;
            $nilaiGanti = 0;

            if ($tipePenyelesaian === 'Ganti_Barang_Sama') {
                $idBarangGanti = $retur['id_barang_retur'];
                $namaBarangGanti = $retur['nama_barang_retur'];
                $barang = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
                if ($barang) {
                    $stokBaru = (float)$barang['stok'] + $qtyGanti;
                    $nilaiGanti = $qtyGanti * (float)($barang['harga_beli'] ?? 0);
                    $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $stokBaru]);

                    $db->table('kartu_stok')->insert([
                        'tanggal' => $tglSelesai,
                        'id_barang' => $idBarangGanti,
                        'tipe' => 'MASUK',
                        'no_referensi' => $retur['no_retur'],
                        'masuk' => $qtyGanti,
                        'keluar' => 0,
                        'sisa' => $stokBaru,
                        'keterangan' => 'Penerimaan Barang Baru Pengganti Retur ' . $retur['no_retur'] . ' (' . $retur['nama_supplier'] . ')'
                    ]);
                }
            } elseif ($tipePenyelesaian === 'Ganti_Barang_Lain') {
                $idBarangGanti = $this->request->getPost('id_barang_ganti');
                $barangB = $db->table('barang')->where('id_barang', $idBarangGanti)->get()->getRowArray();
                if ($barangB) {
                    $namaBarangGanti = $barangB['nama_barang'];
                    $stokBaruB = (float)$barangB['stok'] + $qtyGanti;
                    $nilaiGanti = $qtyGanti * (float)($barangB['harga_beli'] ?? 0);
                    $db->table('barang')->where('id_barang', $idBarangGanti)->update(['stok' => $stokBaruB]);

                    $db->table('kartu_stok')->insert([
                        'tanggal' => $tglSelesai,
                        'id_barang' => $idBarangGanti,
                        'tipe' => 'MASUK',
                        'no_referensi' => $retur['no_retur'],
                        'masuk' => $qtyGanti,
                        'keluar' => 0,
                        'sisa' => $stokBaruB,
                        'keterangan' => 'Penerimaan Barang Lain Pengganti Retur ' . $retur['no_retur'] . ' (' . $barangB['nama_barang'] . ')'
                    ]);
                }
            } elseif ($tipePenyelesaian === 'Ganti_Uang_Kas') {
                $nilaiGanti = (float)str_replace(['.', ',', ' '], '', (string)$this->request->getPost('nilai_ganti'));
                $db->table('data_keuangan')->insert([
                    'tanggal' => $tglSelesai,
                    'jenis_keuangan' => 'Pemasukan',
                    'nama_keuangan' => 'Kompensasi Penggantian Uang Retur ' . $retur['no_retur'],
                    'total' => $nilaiGanti,
                    'keterangan' => 'Penggantian Nilai Uang Retur ' . $retur['nama_barang_retur'] . ' dari ' . $retur['nama_supplier'],
                    'kode_user' => $userSelesai
                ]);
            }

            if (!empty($existingDetails)) {
                $detId = $existingDetails[0]['id'];
                $db->table('retur_supplier_detail')->where('id', $detId)->update([
                    'id_barang_ganti'   => $idBarangGanti,
                    'nama_barang_ganti' => $namaBarangGanti,
                    'qty_ganti'         => $qtyGanti,
                    'nilai_ganti'       => $nilaiGanti,
                    'status_item'       => 'Selesai',
                    'tipe_penyelesaian' => $tipePenyelesaian,
                    'tgl_selesai'       => $tglSelesai
                ]);
            }
            $processedCount++;
            $processedSummary[] = "Selesai langsung: Qty {$qtyGanti}";
        }

        if ($processedCount === 0) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Tidak ada barang pengganti yang diproses. Pastikan Anda mencentang minimal 1 barang yang belum lunas dan mengisi Qty datang.');
        }

        // EVALUASI STATUS KESELURUHAN DOKUMEN RETUR
        $allDetails = $db->table('retur_supplier_detail')->where('no_retur', $retur['no_retur'])->get()->getResultArray();
        $totalDetails = count($allDetails);
        $finishedDetails = 0;
        $totalSumQtyGanti = 0;
        $totalSumNilaiGanti = 0;

        foreach ($allDetails as $d) {
            $totalSumQtyGanti += (float)$d['qty_ganti'];
            $totalSumNilaiGanti += (float)$d['nilai_ganti'];
            if ($d['status_item'] === 'Selesai') {
                $finishedDetails++;
            }
        }

        $isAllDone = ($totalDetails > 0 && $finishedDetails >= $totalDetails);
        $newMasterStatus = $isAllDone ? 'Selesai' : 'Proses Sebagian';
        $finalTglSelesai = $isAllDone ? $tglSelesai : null;

        $returModel->update($id, [
            'status'               => $newMasterStatus,
            'tgl_selesai'          => $finalTglSelesai,
            'user_selesai'         => $userSelesai,
            'qty_ganti'            => $totalSumQtyGanti,
            'nilai_ganti'          => $totalSumNilaiGanti,
            'catatan_penyelesaian' => $catatanPenyelesaian ?: $retur['catatan_penyelesaian'],
        ]);

        // Catat Audit Trail Lengkap
        AuditLogModel::log(
            $isAllDone ? 'SELESAIKAN_RETUR_TUNTAS' : 'SELESAIKAN_RETUR_PARSIAL',
            'RETUR_SUPPLIER',
            "Realisasi Penggantian Retur {$retur['no_retur']} dari Supplier {$retur['nama_supplier']} [Status: {$newMasterStatus}] - " . implode('; ', $processedSummary),
            $retur,
            [
                'no_retur'             => $retur['no_retur'],
                'status'               => $newMasterStatus,
                'total_qty_ganti'      => $totalSumQtyGanti,
                'total_nilai_ganti'    => $totalSumNilaiGanti,
                'catatan_penyelesaian' => $catatanPenyelesaian
            ]
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses realisasi retur barang!');
        }

        $successMsg = $isAllDone
            ? "Dokumen retur {$retur['no_retur']} telah TUNTAS SELESAI. Seluruh barang pengganti / kompensasi uang telah masuk ke sistem."
            : "Penerimaan pengganti retur {$retur['no_retur']} berhasil disimpan sebagai PROSES SEBAGIAN (PARSIAL). Sisa barang yang belum diganti masih tercatat dan dapat diproses kembali.";

        return redirect()->to(base_url('retur'))->with('success', $successMsg);
    }

    // Detail Data Retur (JSON untuk Modal Popup)
    public function detail($id)
    {
        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);

        if (!$retur) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data retur tidak ditemukan!'
            ]);
        }

        $db = \Config\Database::connect();
        $items = $db->table('retur_supplier_detail')
            ->where('no_retur', $retur['no_retur'])
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        if (empty($items) && !empty($retur['id_barang_retur'])) {
            $db->table('retur_supplier_detail')->insert([
                'no_retur'          => $retur['no_retur'],
                'id_barang_retur'   => $retur['id_barang_retur'],
                'nama_barang_retur' => $retur['nama_barang_retur'],
                'satuan'            => 'Pcs',
                'qty_retur'         => (float)$retur['qty_retur'],
                'harga_beli'        => ($retur['qty_retur'] > 0 ? ((float)$retur['nilai_retur'] / (float)$retur['qty_retur']) : 0),
                'subtotal_nilai'    => (float)$retur['nilai_retur'],
                'alasan'            => $retur['alasan'] ?? 'Rijek / Bocor',
                'qty_ganti'         => (float)$retur['qty_ganti'],
                'nilai_ganti'       => (float)$retur['nilai_ganti'],
                'status_item'       => $retur['status'],
                'created_at'        => $retur['tanggal']
            ]);
            $items = $db->table('retur_supplier_detail')
                ->where('no_retur', $retur['no_retur'])
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
        }

        $auditLogs = $db->table('audit_logs')
            ->groupStart()
                ->like('deskripsi', $retur['no_retur'])
                ->orLike('data_baru', $retur['no_retur'])
                ->orLike('data_lama', $retur['no_retur'])
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status'     => 'success',
            'retur'      => $retur,
            'items'      => $items,
            'audit_logs' => $auditLogs
        ]);
    }

    // Hapus Transaksi Retur Supplier (Otorisasi Super Admin)
    public function hapus($id)
    {
        $db = \Config\Database::connect();
        
        // Jika user yang sedang login adalah Super Admin (role = admin), langsung izinkan
        $isCurrentAdmin = (session()->get('role') === 'admin');
        if (!$isCurrentAdmin) {
            $password = trim($this->request->getPost('password_admin') ?? '');
            if (!$this->verifySuperAdminPassword($password)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak! Password Super Admin salah.'
                ]);
            }
        }

        $returModel = new ReturSupplierModel();
        $retur = $returModel->find($id);
        if (!$retur) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data retur tidak ditemukan!'
            ]);
        }

        $db->transStart();

        // 2. Kembalikan stok barang yang diretur (karena retur dibatalkan/dihapus)
        $detailItems = $db->table('retur_supplier_detail')->where('no_retur', $retur['no_retur'])->get()->getResultArray();
        if (!empty($detailItems)) {
            foreach ($detailItems as $det) {
                $brg = $db->table('barang')->where('id_barang', $det['id_barang_retur'])->get()->getRowArray();
                if ($brg) {
                    $stokBaru = $brg['stok'] + (float)$det['qty_retur'];
                    $db->table('barang')->where('id_barang', $det['id_barang_retur'])->update(['stok' => $stokBaru]);

                    $db->table('kartu_stok')->insert([
                        'tanggal'      => date('Y-m-d H:i:s'),
                        'id_barang'    => $det['id_barang_retur'],
                        'tipe'         => 'MASUK',
                        'no_referensi' => $retur['no_retur'],
                        'masuk'        => (float)$det['qty_retur'],
                        'keluar'       => 0,
                        'sisa'         => $stokBaru,
                        'keterangan'   => 'Pembatalan Retur Supplier ' . $retur['no_retur'] . ' (Otorisasi Super Admin)'
                    ]);
                }
            }
            $db->table('retur_supplier_detail')->where('no_retur', $retur['no_retur'])->delete();
        } else {
            $barangRetur = $db->table('barang')->where('id_barang', $retur['id_barang_retur'])->get()->getRowArray();
            if ($barangRetur) {
                $stokBaruRetur = $barangRetur['stok'] + (float)$retur['qty_retur'];
                $db->table('barang')->where('id_barang', $retur['id_barang_retur'])->update(['stok' => $stokBaruRetur]);

                $db->table('kartu_stok')->insert([
                    'tanggal'      => date('Y-m-d H:i:s'),
                    'id_barang'    => $retur['id_barang_retur'],
                    'tipe'         => 'MASUK',
                    'no_referensi' => $retur['no_retur'],
                    'masuk'        => (float)$retur['qty_retur'],
                    'keluar'       => 0,
                    'sisa'         => $stokBaruRetur,
                    'keterangan'   => 'Pembatalan Retur Supplier ' . $retur['no_retur'] . ' (Otorisasi Super Admin)'
                ]);
            }
        }

        // 3. Jika status retur sudah SELESAI, batalkan barang pengganti atau uang kompensasi yang sudah diterima
        if ($retur['status'] === 'Selesai') {
            if (in_array($retur['tipe_penyelesaian'], ['Ganti_Barang_Sama', 'Ganti_Barang_Lain']) && !empty($retur['id_barang_ganti'])) {
                $barangGanti = $db->table('barang')->where('id_barang', $retur['id_barang_ganti'])->get()->getRowArray();
                if ($barangGanti) {
                    $stokBaruGanti = max(0, $barangGanti['stok'] - (float)$retur['qty_ganti']);
                    $db->table('barang')->where('id_barang', $retur['id_barang_ganti'])->update(['stok' => $stokBaruGanti]);

                    $db->table('kartu_stok')->insert([
                        'tanggal' => date('Y-m-d H:i:s'),
                        'id_barang' => $retur['id_barang_ganti'],
                        'tipe' => 'KELUAR',
                        'no_referensi' => $retur['no_retur'],
                        'masuk' => 0,
                        'keluar' => (float)$retur['qty_ganti'],
                        'sisa' => $stokBaruGanti,
                        'keterangan' => 'Pembatalan Penerimaan Pengganti Retur ' . $retur['no_retur'] . ' (Otorisasi Super Admin)'
                    ]);
                }
            } elseif ($retur['tipe_penyelesaian'] === 'Ganti_Uang_Kas') {
                $db->table('data_keuangan')->like('nama_keuangan', $retur['no_retur'])->delete();
            }
        }

        // 4. Hapus data dari tabel retur_supplier
        $returModel->delete($id);

        // Audit Trail Pembatalan / Hapus Retur
        AuditLogModel::log(
            'HAPUS_RETUR',
            'RETUR_SUPPLIER',
            "Membatalkan / Menghapus Dokumen Retur {$retur['no_retur']} (Supplier: {$retur['nama_supplier']}) serta mengembalikan posisi stok toko",
            $retur,
            [
                'dihapus_oleh' => session()->get('nama_user') ?? session()->get('kode_user') ?? 'ADMIN',
                'waktu_hapus'  => date('Y-m-d H:i:s'),
                'no_retur'     => $retur['no_retur'],
                'supplier'     => $retur['nama_supplier'],
                'total_item'   => $retur['total_item'] ?? 1,
                'total_qty'    => $retur['qty_retur'],
                'total_nilai'  => $retur['nilai_retur']
            ]
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses pembatalan retur supplier!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Data retur {$retur['no_retur']} berhasil dihapus dan posisi stok barang telah disesuaikan kembali!"
        ]);
    }

    // Helper Otorisasi Password Super Admin
    private function verifySuperAdminPassword($password)
    {
        if (empty($password)) return false;

        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->get()->getResultArray();

        foreach ($admins as $adm) {
            if (password_verify($password, $adm['password'])) {
                return true;
            }
        }

        return false;
    }
}
