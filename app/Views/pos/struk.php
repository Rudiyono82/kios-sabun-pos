<?php
// =========================================================================
// FORMAT STRUK KASIR THERMAL 58MM (32 KARAKTER) - 100% IDENTIK DENGAN VB6
// =========================================================================

$lines = [];

function centerLine($text, $width = 32) {
    $text = trim($text);
    $len = strlen($text);
    if ($len >= $width) return substr($text, 0, $width);
    $left = (int)floor(($width - $len) / 2);
    return str_repeat(' ', $left) . $text;
}

// 1. Header Profil Toko (Rata Tengah Sesuai Permintaan Owner)
$namaToko = trim($profil['nama_toko'] ?? 'KSP Metland Cileungsi (Pusat)');
if (stripos($namaToko, 'kios') !== false && stripos($namaToko, 'metland') !== false) {
    $namaToko = 'KSP Metland Cileungsi (Pusat)';
}
$lines[] = centerLine($namaToko);
$lines[] = centerLine("Jl. Metland Cileungsi,");
$lines[] = centerLine("Blok BD.04, No.17");
$lines[] = centerLine("Cileungsi - Bogor");

$waRaw = !empty($profil['telepon']) ? trim($profil['telepon']) : '0857-7748-8367';
if (strpos($waRaw, '1115') !== false || strpos($waRaw, '9298') !== false) {
    $waRaw = '0857-7748-8367';
}
$waClean = preg_replace('/[^0-9\-]/', '', $waRaw);
$waLine = "WA." . ($waClean ?: '0857-7748-8367');
$lines[] = centerLine($waLine);

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
    $namaBrg = strtoupper(trim($it['nama_barang']));
    $wrappedName = explode("\n", wordwrap($namaBrg, 32, "\n", true));
    foreach ($wrappedName as $wn) {
        $lines[] = trim($wn);
    }

    // Baris qty, @harga, dan subtotal: format 32 karakter monospace persis VB6
    // Kolom 1 (Qty): 10 karakter (contoh: "   3 PCS  ")
    // Kolom 2 (@Harga): 11 karakter rata kanan (contoh: "    @34,000")
    // Kolom 3 (Subtotal): 11 karakter rata kanan (contoh: "    102,000")
    $satuan = !empty($it['satuan']) ? str_replace('#', '', trim($it['satuan'])) : 'PCS';
    $qtyStr = sprintf("   %s %s", $qty, $satuan);
    if (strlen($qtyStr) > 10) $qtyStr = substr($qtyStr, 0, 10);
    
    $hrgStr = "@" . number_format($harga, 0, '.', ',');
    $subStr = number_format($subtotal, 0, '.', ',');

    $line2 = sprintf("%-10s%11s%11s", $qtyStr, $hrgStr, $subStr);
    $lines[] = $line2;
}

$lines[] = "=============================(+)";

// 4. Rekap Total Transaksi (Persis VB6: Format Number dengan koma)
$lines[] = sprintf("%-16s: %14s", "TOTAL ITEM", $totalItem);
$lines[] = sprintf("%-16s: %14s", "TOTAL QTY", $totalQty);

if (!empty($penjualan['potongan_tukar_jerigen']) && (float)$penjualan['potongan_tukar_jerigen'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "TUKAR JERIGEN", "-" . number_format((float)$penjualan['potongan_tukar_jerigen'], 0, '.', ','));
}
if (!empty($penjualan['potongan_voucher']) && (float)$penjualan['potongan_voucher'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "POT. VOUCHER", "-" . number_format((float)$penjualan['potongan_voucher'], 0, '.', ','));
}
if (!empty($penjualan['diskon']) && (float)$penjualan['diskon'] > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "DISKON", "-" . number_format((float)$penjualan['diskon'], 0, '.', ','));
}

$metode = strtoupper($penjualan['metode_bayar'] ?? 'CASH');
$paymentLabel = $metode . " PAYMENT";
if (strlen($paymentLabel) > 16) $paymentLabel = substr($paymentLabel, 0, 16);

$lines[] = sprintf("%-16s: Rp.%11s", $paymentLabel, number_format((float)$penjualan['bayar'], 0, '.', ','));
$lines[] = sprintf("%-16s: Rp.%11s", "TOTAL PRICE", number_format((float)$penjualan['grand_total'], 0, '.', ','));

$lines[] = "=============================(-)";

$kembalian = (float)$penjualan['kembalian'];
$lines[] = sprintf("%-16s: Rp.%11s", "CHANGE", number_format($kembalian, 0, '.', ','));

if (!empty($penjualan['kode_member'])) {
    $lines[] = "--------------------------------";
    $lines[] = sprintf("Member    : %s", $penjualan['kode_member']);
    $mb = \Config\Database::connect()->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
    if ($mb) {
        $lines[] = sprintf("Poin      : %s Poin", number_format((float)($mb['point'] ?? 0), 0, '.', ','));
    }
}

// 5. Footer Struk
$lines[] = "";
$lines[] = "********* Terima Kasih *********";
$lines[] = "**** Selamat Datang Kembali ****";
$lines[] = "********************************";

// Hitung tinggi kertas pas dalam millimeter untuk printer thermal (ditambah buffer sobek kertas 10mm)
$lineCount = count($lines);
$pageHeightMm = max(55, ceil($lineCount * 3.8 + 10));
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
            size: 58mm <?= $pageHeightMm ?>mm;
        }
        html, body {
            width: 100%;
            height: auto;
            min-height: 100%;
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #000;
            overflow-y: auto; /* Memungkinkan kasir scroll ke bawah dengan leluasa saat preview */
            overflow-x: hidden;
        }
        .action-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 99999;
            padding: 8px 12px;
            background: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            display: flex;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .btn-act {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            font-family: sans-serif;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #000;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-act-print {
            background: #0f172a;
            color: #fff;
        }
        .btn-act-print:hover {
            background: #1e293b;
        }
        .btn-act:hover {
            background: #e2e8f0;
        }
        .receipt-wrapper {
            width: 58mm;
            max-width: 58mm;
            margin: 54px auto 24px auto; /* Memberikan ruang di atas agar tidak tertutup tombol fixed */
            background: #fff;
            padding: 8px 0 24px 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        .receipt-pre {
            font-family: 'Consolas', 'Courier New', 'Lucida Console', Monaco, monospace;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.22;
            color: #000;
            white-space: pre;
            margin: 0 auto;
            padding: 0;
            width: 58mm;
            max-width: 58mm;
            letter-spacing: -0.15px;
            overflow: visible;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: 58mm <?= $pageHeightMm ?>mm;
            }
            html, body {
                width: 58mm !important;
                max-width: 58mm !important;
                height: auto !important;
                min-height: <?= $pageHeightMm ?>mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }
            .receipt-wrapper {
                width: 58mm !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .receipt-pre {
                width: 58mm !important;
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
    <div class="receipt-wrapper">
        <pre class="receipt-pre"><?= esc(implode("\n", $lines)) ?></pre>
    </div>
</body>
</html>
