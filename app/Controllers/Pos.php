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
        $idCabang = session()->get('id_cabang') ?: 1;
        $barangModel = new BarangModel();
        $items = $barangModel->cariBarangPOS($keyword, $idCabang);

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

    public function getMemberVouchers($kodeMember)
    {
        $db = \Config\Database::connect();
        $member = $db->table('member')->where('kode_member', $kodeMember)->get()->getRowArray();
        if (!$member) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Member tidak ditemukan']);
        }

        $vouchers = $db->table('voucher')
            ->where('kode_member', $kodeMember)
            ->where('is_aktif', 1)
            ->orderBy('nilai_voucher', 'DESC')
            ->get()->getResultArray();

        $point = (float)($member['point'] ?? 0);
        $kuotaVoucherRp = $point * 50; // 1 Poin = Rp 50

        return $this->response->setJSON([
            'status'            => 'success',
            'kode_member'       => $kodeMember,
            'nama_member'       => $member['nama'],
            'telepon'           => $member['telepon'],
            'point'             => $point,
            'kuota_voucher_rp'  => $kuotaVoucherRp,
            'vouchers'          => $vouchers,
            'voucher_count'     => count($vouchers)
        ]);
    }

    public function tukarPoinMember()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $kodeMember = trim((string)($this->request->getPost('kode_member') ?? ''));
        $pointTukar = (float)($this->request->getPost('point_tukar') ?? 0);

        if (empty($kodeMember)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Member belum dipilih!'
            ]);
        }

        if ($pointTukar <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Jumlah poin yang ditukar harus lebih dari 0!'
            ]);
        }

        $member = $db->table('member')->where('kode_member', $kodeMember)->get()->getRowArray();
        if (!$member) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data member tidak ditemukan!'
            ]);
        }

        $currentPoint = (float)($member['point'] ?? 0);
        if ($currentPoint < $pointTukar) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => "Poin member tidak mencukupi! Saldo poin: " . number_format($currentPoint, 0, ',', '.') . " Poin."
            ]);
        }

        $nilaiVoucher = $pointTukar * 50; // 1 Poin = Rp 50
        $voucherModel = new \App\Models\VoucherModel();
        $kodeVoucher = $voucherModel->generateKodeVoucher($kodeMember);
        $namaVoucher = "Voucher Belanja POS " . date('d/m/Y') . " Member: " . $member['nama'];

        // 1. Potong Poin Member
        $sisaPoin = $currentPoint - $pointTukar;
        $db->table('member')->where('kode_member', $kodeMember)->update(['point' => $sisaPoin]);

        // 2. Terbitkan Voucher Baru untuk Member
        $voucherData = [
            'kode_voucher'   => $kodeVoucher,
            'nama_voucher'   => $namaVoucher,
            'tgl_voucher'    => date('Y-m-d'),
            'kode_member'    => $kodeMember,
            'nama_member'    => $member['nama'],
            'point_ditukar'  => $pointTukar,
            'nilai_voucher'  => $nilaiVoucher,
            'is_aktif'       => 1,
            'created_at'     => date('Y-m-d H:i:s')
        ];
        $voucherModel->insert($voucherData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menerbitkan voucher poin member!'
            ]);
        }

        return $this->response->setJSON([
            'status'            => 'success',
            'message'           => "Voucher {$kodeVoucher} senilai Rp " . number_format($nilaiVoucher, 0, ',', '.') . " berhasil diterbitkan!",
            'voucher'           => $voucherData,
            'sisa_point'        => $sisaPoin,
            'kuota_voucher_rp'  => $sisaPoin * 50
        ]);
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

        $idCabang = (int)(session()->get('id_cabang') ?: 1);
        $stokCabangModel = new \App\Models\StokCabangModel();

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

            // Potong stok barang di cabang aktif
            $sisaStok = $stokCabangModel->updateStok($item['id_barang'], $idCabang, -$qty);

            // Update akumulasi stok di tabel master barang (untuk konsolidasi total)
            $db->query("UPDATE barang SET stok = (SELECT COALESCE(SUM(stok), 0) FROM stok_cabang WHERE id_barang = ?) WHERE id_barang = ?", [$item['id_barang'], $item['id_barang']]);

            // Catat ke kartu stok
            $kartuStokRecords[] = [
                'tanggal' => date('Y-m-d H:i:s'),
                'id_barang' => $item['id_barang'],
                'tipe' => 'KELUAR',
                'no_referensi' => $noFaktur,
                'masuk' => 0,
                'keluar' => $qty,
                'sisa' => $sisaStok,
                'keterangan' => 'Penjualan Kasir ' . $noFaktur . ' (Cabang #' . $idCabang . ')'
            ];
        }

        $diskon = (float) str_replace(['.', ',', ' '], '', (string)($json['diskon'] ?? 0));
        $potonganJerigen = (float) ($json['potongan_tukar_jerigen'] ?? 0);
        $kodeVoucher = !empty($json['kode_voucher']) ? trim((string)$json['kode_voucher']) : null;
        $potonganVoucher = 0;

        // Validasi Voucher Point Member
        if (!empty($kodeVoucher)) {
            $vch = $db->table('voucher')
                ->where('kode_voucher', $kodeVoucher)
                ->where('is_aktif', 1)
                ->get()->getRowArray();

            if ($vch) {
                if (!empty($json['kode_member']) && !empty($vch['kode_member']) && $vch['kode_member'] !== $json['kode_member']) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'Voucher ' . $kodeVoucher . ' bukan milik member yang dipilih!'
                    ]);
                }
                $maxPotongan = max(0, $subtotal - $diskon - $potonganJerigen);
                $potonganVoucher = min((float)$vch['nilai_voucher'], $maxPotongan);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Voucher ' . $kodeVoucher . ' tidak aktif atau sudah pernah digunakan!'
                ]);
            }
        }

        $grandTotal = $subtotal - $diskon - $potonganJerigen - $potonganVoucher;
        if ($grandTotal < 0) $grandTotal = 0;

        $bayar = (float) str_replace(['.', ',', ' '], '', (string)($json['bayar'] ?? 0));
        $kembalian = $bayar - $grandTotal;

        // Insert Master Penjualan
        $db->table('penjualan')->insert([
            'id_cabang' => $idCabang,
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
            'id_cabang' => $idCabang,
            'tanggal' => date('Y-m-d H:i:s'),
            'jenis_keuangan' => 'Pemasukan',
            'nama_keuangan' => 'Penjualan ' . $noFaktur . ' (' . ($json['metode_bayar'] ?? 'Cash') . ')',
            'total' => $grandTotal,
            'keterangan' => 'Kasir POS (' . (session()->get('nama_cabang') ?? 'Cabang') . ')',
            'kode_user' => session()->get('kode_user') ?? 'KASIR1'
        ]);

        // Catat ke Audit Trail
        \App\Models\AuditLogModel::log('TRANSAKSI_POS', 'POS', "Penjualan Faktur {$noFaktur} senilai Rp " . number_format($grandTotal, 0, ',', '.') . " (" . ($json['metode_bayar'] ?? 'Cash') . ") oleh " . (session()->get('nama_user') ?? 'Kasir'), null, ['no_faktur' => $noFaktur, 'grand_total' => $grandTotal, 'total_item' => $totalItem], $idCabang);

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
        
        // Prioritaskan informasi cabang tempat transaksi dibuat
        $cabang = null;
        if (!empty($penjualan['id_cabang'])) {
            $cabang = $db->table('cabang')->where('id', $penjualan['id_cabang'])->get()->getRowArray();
        }
        if (!$cabang) {
            $cabang = $db->table('cabang')->where('is_pusat', 1)->get()->getRowArray();
        }

        $profil = $db->table('profil_toko')->get()->getRowArray();
        if ($cabang) {
            $profil = [
                'nama_toko'    => $cabang['nama_cabang'],
                'alamat_1'     => $cabang['alamat'],
                'alamat_2'     => $cabang['kota'],
                'telepon'      => $cabang['telepon'],
                'footer_struk' => $cabang['footer_struk'] ?? ($profil['footer_struk'] ?? 'Terima Kasih atas Kunjungan Anda!')
            ];
        }

        $data = [
            'penjualan' => $penjualan,
            'items'     => $items,
            'profil'    => $profil,
            'cabang'    => $cabang
        ];

        return view('pos/struk', $data);
    }
}

