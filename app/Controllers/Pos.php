<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\MemberModel;
use App\Models\KeuanganModel;

class Pos extends BaseController
{
    private function getKasAwalHariIni()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        return $db->table('data_keuangan')
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
            ->get()->getRowArray();
    }

    public function index()
    {
        $penjualanModel = new PenjualanModel();
        $memberModel = new MemberModel();

        $kasAwalRow = $this->getKasAwalHariIni();
        $hasKasAwal = !empty($kasAwalRow);
        $nominalKasAwal = $hasKasAwal ? (float)$kasAwalRow['total'] : 0;

        $data = [
            'title' => 'Mesin Kasir POS Modern',
            'no_faktur' => $penjualanModel->generateNoFaktur(),
            'members' => $memberModel->orderBy('nama', 'ASC')->findAll(),
            'has_kas_awal' => $hasKasAwal,
            'nominal_kas_awal' => $nominalKasAwal,
        ];

        return view('pos/index', $data);
    }

    public function search()
    {
        $keyword = $this->request->getGet('term') ?? '';
        $barangModel = new BarangModel();
        $items = $barangModel->cariBarangPOS($keyword);

        $isAdmin = (session()->get('role') === 'admin');
        if (!$isAdmin) {
            foreach ($items as &$item) {
                // Sembunyikan stok akhir dari kasir / non-admin
                $item['stok'] = null;
            }
        }

        return $this->response->setJSON($items);
    }

    public function searchMember()
    {
        $keyword = trim((string) $this->request->getGet('term'));
        $db = \Config\Database::connect();
        
        $builder = $db->table('member');
        if (!empty($keyword)) {
            $builder->groupStart()
                    ->like('nama', $keyword)
                    ->orLike('telepon', $keyword)
                    ->orLike('kode_member', $keyword)
                    ->groupEnd();
        }
        $members = $builder->orderBy('nama', 'ASC')->limit(10)->get()->getResultArray();

        // Sertakan info voucher aktif & nilai rupiah poin
        foreach ($members as &$m) {
            $m['nilai_rupiah_poin'] = (float)$m['point'] * 50; // 1 Poin = Rp 50
            $m['vouchers'] = $db->table('voucher')
                ->where('kode_member', $m['kode_member'])
                ->where('is_aktif', 1)
                ->orderBy('nilai_voucher', 'DESC')
                ->get()
                ->getResultArray();
        }

        return $this->response->setJSON($members);
    }

    public function simpanTransaksi()
    {
        // 1. Validasi Wajib Kas Awal Hari Ini (SOP Kasir & Alur FrmJual VB6)
        $kasAwalRow = $this->getKasAwalHariIni();
        if (empty($kasAwalRow) || (float)$kasAwalRow['total'] <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'require_kas_awal' => true,
                'message' => 'Uang Kas Kasir masih Rp 0! Kasir wajib menginput saldo kas awal laci hari ini terlebih dahulu untuk uang kembalian konsumen sebelum melakukan transaksi penjualan.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $json = $this->request->getJSON(true);
        if (!$json || empty($json['items'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Keranjang belanja masih kosong!'
            ]);
        }

        $penjualanModel = new PenjualanModel();
        $noFaktur = $penjualanModel->generateNoFaktur();

        $totalItem = count($json['items']);
        $totalQty = 0;
        $subtotal = 0;
        $totalProfit = 0;

        $detailRecords = [];
        $kartuStokRecords = [];

        foreach ($json['items'] as $item) {
            $barang = $db->table('barang')->where('id_barang', $item['id_barang'])->get()->getRowArray();
            if (!$barang) continue;

            $qty = (float) $item['qty'];
            $hargaJual = (float) $item['harga_jual'];
            $hargaBeli = (float) $barang['harga_beli'];
            $itemSubtotal = $qty * $hargaJual;
            $itemProfit = ($hargaJual - $hargaBeli) * $qty;

            $totalQty += $qty;
            $subtotal += $itemSubtotal;
            $totalProfit += $itemProfit;

            $detailRecords[] = [
                'no_faktur' => $noFaktur,
                'id_barang' => $item['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'qty' => $qty,
                'subtotal' => $itemSubtotal,
                'profit' => $itemProfit,
            ];

            // Potong stok barang
            $sisaStok = $barang['stok'] - $qty;
            $db->table('barang')->where('id_barang', $item['id_barang'])->update(['stok' => $sisaStok]);

            // Catat ke kartu stok
            $kartuStokRecords[] = [
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $item['id_barang'],
                'tipe' => 'KELUAR',
                'no_referensi' => $noFaktur,
                'masuk' => 0,
                'keluar' => $qty,
                'sisa' => $sisaStok,
                'keterangan' => 'Penjualan Kasir ' . $noFaktur
            ];
        }

        $diskon = (float) ($json['diskon'] ?? 0);
        $potonganJerigen = (float) ($json['potongan_tukar_jerigen'] ?? 0);
        $kodeVoucher = $json['kode_voucher'] ?? null;
        $potonganVoucher = (float) ($json['potongan_voucher'] ?? 0);

        $grandTotal = $subtotal - $diskon - $potonganJerigen - $potonganVoucher;
        if ($grandTotal < 0) $grandTotal = 0;

        $bayar = (float) ($json['bayar'] ?? 0);
        $kembalian = $bayar - $grandTotal;

        // Insert Master Penjualan
        $db->table('penjualan')->insert([
            'no_faktur' => $noFaktur,
            'tanggal' => date('Y-m-d H:i:s'),
            'kode_user' => session()->get('kode_user') ?? 'KASIR1',
            'nama_kasir' => session()->get('nama_user') ?? 'Kasir Utama',
            'kode_member' => $json['kode_member'] ?: null,
            'total_item' => $totalItem,
            'total_qty' => $totalQty,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'potongan_tukar_jerigen' => $potonganJerigen,
            'kode_voucher' => $kodeVoucher,
            'potongan_voucher' => $potonganVoucher,
            'grand_total' => $grandTotal,
            'bayar' => $bayar,
            'kembalian' => $kembalian >= 0 ? $kembalian : 0,
            'metode_bayar' => $json['metode_bayar'] ?? 'Cash',
            'no_kartu' => $json['no_kartu'] ?? null,
            'total_profit' => $totalProfit,
            'catatan' => $json['catatan'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Jika ada voucher yang digunakan, tandai terpakai
        if (!empty($kodeVoucher)) {
            $db->table('voucher')->where('kode_voucher', $kodeVoucher)->update([
                'is_aktif' => 0,
                'no_faktur_terpakai' => $noFaktur,
                'tgl_terpakai' => date('Y-m-d H:i:s')
            ]);
        }

        // Insert Details
        if (!empty($detailRecords)) {
            $db->table('penjualan_detail')->insertBatch($detailRecords);
        }

        // Insert Kartu Stok
        if (!empty($kartuStokRecords)) {
            $db->table('kartu_stok')->insertBatch($kartuStokRecords);
        }

        // Catat ke Arus Kas
        $db->table('data_keuangan')->insert([
            'tanggal' => date('Y-m-d H:i:s'),
            'jenis_keuangan' => 'Pemasukan',
            'nama_keuangan' => 'Penjualan ' . $noFaktur . ' (' . ($json['metode_bayar'] ?? 'Cash') . ')',
            'total' => $grandTotal,
            'keterangan' => 'Kasir POS',
            'kode_user' => session()->get('kode_user') ?? 'KASIR1'
        ]);

        // Jika ada member, tambah point & total belanja dan siapkan data WA
        $memberData = null;
        $totalPoin = 0;
        $waUrl = null;
        $waText = null;
        $profil = $db->table('profil_toko')->get()->getRowArray() ?? ['nama_toko' => 'KIOS SABUN & PARFUM'];

        if (!empty($json['kode_member'])) {
            $memberData = $db->table('member')->where('kode_member', $json['kode_member'])->get()->getRowArray();
            $pointTambah = floor($grandTotal / 10000); // 1 point per 10rb
            $totalPoin = ($memberData['point'] ?? 0) + $pointTambah;

            $db->query("UPDATE member SET point = point + ?, total_belanja = total_belanja + ? WHERE kode_member = ?", [
                $pointTambah, $grandTotal, $json['kode_member']
            ]);

            // Susun pesan WhatsApp yang rapi
            $phoneRaw = $memberData['telepon'] ?? '';
            $phoneClean = preg_replace('/[^0-9]/', '', $phoneRaw);
            if (str_starts_with($phoneClean, '0')) {
                $phoneClean = '62' . substr($phoneClean, 1);
            }

            $tokoNama = $profil['nama_toko'] ?? 'KIOS SABUN & PARFUM LAUNDRY';
            $tokoAlamat = $profil['alamat'] ?? 'Metland Cileungsi';
            $tokoTelp = $profil['telepon'] ?? '';

            $msg = "*STRUK ELEKTRONIK RESMI*\n";
            $msg .= "*" . strtoupper($tokoNama) . "*\n";
            $msg .= $tokoAlamat . ($tokoTelp ? " | Telp/WA: " . $tokoTelp : "") . "\n";
            $msg .= "----------------------------------------\n";
            $msg .= "No. Nota   : *" . $noFaktur . "*\n";
            $msg .= "Waktu      : " . date('d/m/Y H:i') . " WIB\n";
            $msg .= "Kasir      : " . (session()->get('nama_user') ?? 'Kasir') . "\n";
            $msg .= "Pelanggan  : *" . ($memberData['nama'] ?? 'Member') . "* (" . $json['kode_member'] . ")\n";
            $msg .= "----------------------------------------\n";
            $msg .= "*RINCIAN PEMBELIAN:*\n";
            foreach ($detailRecords as $item) {
                $msg .= "• " . $item['nama_barang'] . "\n";
                $msg .= "   " . $item['qty'] . " x Rp " . number_format($item['harga_jual'], 0, ',', '.') . " = Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
            }
            $msg .= "----------------------------------------\n";
            $msg .= "Subtotal   : Rp " . number_format($subtotal, 0, ',', '.') . "\n";
            if ($diskon > 0) {
                $msg .= "Diskon     : -Rp " . number_format($diskon, 0, ',', '.') . "\n";
            }
            if ($potonganJerigen > 0) {
                $msg .= "Tukar Jerigen 5L : -Rp " . number_format($potonganJerigen, 0, ',', '.') . "\n";
            }
            if ($potonganVoucher > 0) {
                $msg .= "Potongan Voucher (" . $kodeVoucher . ") : -Rp " . number_format($potonganVoucher, 0, ',', '.') . "\n";
            }
            $msg .= "*TOTAL BAYAR : Rp " . number_format($grandTotal, 0, ',', '.') . "*\n";
            $msg .= "Bayar (" . ($json['metode_bayar'] ?? 'Cash') . ") : Rp " . number_format($bayar, 0, ',', '.') . "\n";
            $msg .= "Kembalian  : Rp " . number_format($kembalian > 0 ? $kembalian : 0, 0, ',', '.') . "\n";
            $msg .= "----------------------------------------\n";
            $msg .= "Total Poin Member: *" . number_format($totalPoin, 0, ',', '.') . " Poin* (+" . $pointTambah . ")\n";
            $msg .= "\nTerima kasih telah berbelanja di toko kami! 🙏\n";
            $msg .= "_Struk ini sah dan diterbitkan secara digital oleh Sistem POS Kios Sabun._";

            $waText = $msg;
            if (!empty($phoneClean) && strlen($phoneClean) >= 9) {
                $waUrl = 'https://api.whatsapp.com/send?phone=' . $phoneClean . '&text=' . rawurlencode($msg);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses transaksi penjualan!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Transaksi berhasil diproses!',
            'no_faktur' => $noFaktur,
            'grand_total' => $grandTotal,
            'bayar' => $bayar,
            'kembalian' => $kembalian,
            'is_member' => !empty($json['kode_member']),
            'member_nama' => $memberData['nama'] ?? null,
            'member_phone' => $memberData['telepon'] ?? null,
            'wa_url' => $waUrl,
            'wa_text' => $waText
        ]);
    }

    public function cetakStruk($noFaktur)
    {
        $db = \Config\Database::connect();
        $penjualan = $db->table('penjualan')->where('no_faktur', $noFaktur)->get()->getRowArray();
        if (!$penjualan) {
            return 'Faktur tidak ditemukan!';
        }

        $items = $db->table('penjualan_detail')->where('no_faktur', $noFaktur)->get()->getResultArray();
        $profil = $db->table('profil_toko')->get()->getRowArray();

        $data = [
            'penjualan' => $penjualan,
            'items' => $items,
            'profil' => $profil,
        ];

        return view('pos/struk', $data);
    }
}

