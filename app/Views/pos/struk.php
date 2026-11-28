<?php
// =========================================================================
// FORMAT STRUK KASIR THERMAL 58MM (32 KARAKTER) - IDENTIK DENGAN VB6 ASLI
// =========================================================================

$lines = [];

// 1. Header Profil Toko
$namaToko = trim($profil['nama_toko'] ?? '(KSP) Kios Sabun & Parfum');
if (stripos($namaToko, 'kios') === false && stripos($namaToko, 'ksp') === false) {
    $namaToko = "(KSP) " . $namaToko;
}
$lines[] = $namaToko;

if (!empty($profil['alamat_1'])) {
    $addr1 = trim($profil['alamat_1']);
    // Pisahkan baris alamat agar rapi sesuai format asli
    $addrParts = preg_split('/(?<=,)\s*/', $addr1);
    foreach ($addrParts as $ap) {
        $ap = trim($ap);
        if (!empty($ap)) {
            $lines[] = $ap;
        }
    }
} else {
    $lines[] = "Jl. Metland Cileungsi,";
    $lines[] = "Blok BD.04,No.17";
}

if (!empty($profil['alamat_2'])) {
    $lines[] = trim($profil['alamat_2']);
} else {
    $lines[] = "Cileungsi-Bogor";
}

if (!empty($profil['telepon'])) {
    $lines[] = "WA." . str_replace(' ', '', trim($profil['telepon']));
} else {
    $lines[] = "WA.085711159298,081287071352";
}

$lines[] = "================================";

// 2. Metadata Transaksi
$lines[] = sprintf("No. Struk : %s", $penjualan['no_faktur']);
$lines[] = sprintf("Kasir     : %s", $penjualan['nama_kasir'] ?? 'WARDAH');
$lines[] = sprintf("Tanggal   : %s", date('d/m/Y H:i:s', strtotime($penjualan['tanggal'])));

$lines[] = "--------------------------------";
$lines[] = "Nama Item        Qty     Harga  ";
$lines[] = "--------------------------------";

// 3. Rincian Item Barang
$totalItem = 0;
$totalQty = 0;

foreach ($items as $it) {
    $totalItem++;
    $qty = (float)$it['qty'];
    $totalQty += $qty;
    $harga = (float)$it['harga_jual'];
    $subtotal = (float)$it['subtotal'];

    // Nama barang (jika > 32 karakter, wrap ke baris berikutnya)
    $namaBrg = trim($it['nama_barang']);
    $wrappedName = explode("\n", wordwrap($namaBrg, 32, "\n", true));
    foreach ($wrappedName as $wn) {
        $lines[] = trim($wn);
    }

    // Baris qty, @harga, dan subtotal: format 32 karakter
    // Kolom 1 (Qty): 11 karakter (contoh: "   3 PCS   ")
    // Kolom 2 (@Harga): 10 karakter rata kanan (contoh: "   @34.000")
    // Kolom 3 (Subtotal): 11 karakter rata kanan (contoh: "    102.000")
    $satuan = !empty($it['satuan']) ? str_replace('#', '', trim($it['satuan'])) : 'PCS';
    $qtyStr = sprintf("   %s %s", $qty, $satuan);
    if (strlen($qtyStr) > 11) $qtyStr = substr($qtyStr, 0, 11);
    
    $hrgStr = "@" . number_format($harga, 0, ',', '.');
    $subStr = number_format($subtotal, 0, ',', '.');

    $line2 = sprintf("%-11s%10s%11s", $qtyStr, $hrgStr, $subStr);
    $lines[] = $line2;
}

$lines[] = "=============================(+)";

// 4. Rekap Total Transaksi
$lines[] = sprintf("%-16s: %14s", "TOTAL ITEM", $totalItem);
$lines[] = sprintf("%-16s: %14s", "TOTAL QTY", $totalQty);

if (!empty($penjualan['potongan_tukar_jerigen']) && (float)$penjualan['potongan_tukar_jerigen'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "TUKAR JERIGEN", "-" . number_format((float)$penjualan['potongan_tukar_jerigen'], 0, ',', '.'));
}
if (!empty($penjualan['potongan_voucher']) && (float)$penjualan['potongan_voucher'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "POT. VOUCHER", "-" . number_format((float)$penjualan['potongan_voucher'], 0, ',', '.'));
}
if (!empty($penjualan['diskon']) && (float)$penjualan['diskon'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "DISKON", "-" . number_format((float)$penjualan['diskon'], 0, ',', '.'));
}

$metode = strtoupper($penjualan['metode_bayar'] ?? 'CASH');
$paymentLabel = $metode . " PAYMENT";
if (strlen($paymentLabel) > 16) $paymentLabel = substr($paymentLabel, 0, 16);

$lines[] = sprintf("%-16s: Rp.%11s", $paymentLabel, number_format((float)$penjualan['bayar'], 0, ',', '.'));
$lines[] = sprintf("%-16s: Rp.%11s", "TOTAL PRICE", number_format((float)$penjualan['grand_total'], 0, ',', '.'));

$lines[] = "=============================(-)";

$kembalian = (float)$penjualan['kembalian'];
$lines[] = sprintf("%-16s: Rp.%11s", "CHANGE", number_format($kembalian, 0, ',', '.'));

if (!empty($penjualan['kode_member'])) {
    $lines[] = "--------------------------------";
    $lines[] = sprintf("Member    : %s", $penjualan['kode_member']);
    $mb = \Config\Database::connect()->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
    if ($mb) {
        $lines[] = sprintf("Poin      : %s Poin", number_format((float)($mb['point'] ?? 0), 0, ',', '.'));
    }
}

// 5. Footer Struk
$lines[] = "";
$lines[] = "********* Terima Kasih *********";
$lines[] = "**** Selamat Datang Kembali ****";
$lines[] = "********************************";
$lines[] = "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= esc($penjualan['no_faktur']) ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            margin: 0;
            size: 58mm auto;
        }
        html, body {
            width: 100%;
            max-width: 58mm;
            margin: 0 auto;
            padding: 0;
            background: #fff;
            color: #000;
        }
        .receipt-pre {
            font-family: 'Consolas', 'Courier New', 'Lucida Console', Monaco, monospace;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.25;
            color: #000;
            white-space: pre;
            margin: 0 auto;
            padding: 4px 0 0 0;
            width: 100%;
            max-width: 58mm;
            overflow: hidden;
            letter-spacing: -0.1px;
        }
        .action-bar {
            padding: 6px;
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
        }
        .btn-act {
            flex: 1;
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
            font-family: sans-serif;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #000;
            background: #fff;
        }
        .btn-act-print {
            background: #0f172a;
            color: #fff;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                width: 100% !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                overflow: hidden !important;
            }
            .receipt-pre {
                width: 100% !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar no-print">
        <button type="button" class="btn-act btn-act-print" onclick="window.print()">
            &#128438; CETAK STRUK
        </button>
        <button type="button" class="btn-act" onclick="window.close()">
            &#10005; TUTUP
        </button>
    </div>
    <pre class="receipt-pre"><?= esc(implode("\n", $lines)) ?></pre>
</body>
</html>
