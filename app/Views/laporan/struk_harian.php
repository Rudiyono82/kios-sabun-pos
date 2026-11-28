<?php
// =========================================================================
// STRUK LAPORAN HARIAN KASIR THERMAL 58MM (32 KARAKTER MONOSPACE)
// IDENTIK DENGAN VB6 ASLI & MENCEGAH KERTAS KELUAR PANJANG
// =========================================================================

$lines = [];

function centerLine($text, $width = 32) {
    $text = trim($text);
    $len = strlen($text);
    if ($len >= $width) return substr($text, 0, $width);
    $left = (int)floor(($width - $len) / 2);
    return str_repeat(' ', $left) . $text;
}

// 1. Header Profil Toko
$namaToko = trim($profil['nama_toko'] ?? '(KSP) Kios Sabun & Parfum');
if (stripos($namaToko, 'kios') === false && stripos($namaToko, 'ksp') === false) {
    $namaToko = "(KSP) " . $namaToko;
}
$lines[] = centerLine($namaToko);

if (!empty($profil['alamat_1'])) {
    $addr1 = trim($profil['alamat_1']);
    $addrParts = preg_split('/(?<=,)\s*/', $addr1);
    foreach ($addrParts as $ap) {
        $ap = trim($ap);
        if (!empty($ap)) {
            $lines[] = centerLine($ap);
        }
    }
} else {
    $lines[] = centerLine("Jl. Metland Cileungsi,");
    $lines[] = centerLine("Blok BD.04,No.17");
}

if (!empty($profil['alamat_2'])) {
    $lines[] = centerLine(trim($profil['alamat_2']));
} else {
    $lines[] = centerLine("Cileungsi-Bogor");
}

if (!empty($profil['telepon'])) {
    $lines[] = centerLine("WA." . str_replace(' ', '', trim($profil['telepon'])));
} else {
    $lines[] = centerLine("WA.085711159298");
}

$lines[] = "================================";
$lines[] = centerLine("LAPORAN REKAP HARIAN KASIR");
$lines[] = "================================";

// 2. Metadata Laporan
$lines[] = sprintf("%-10s: %s", "No.Laporan", $no_laporan);
$lines[] = sprintf("%-10s: %s", "Tanggal", date('d/m/Y', strtotime($tanggal)));
$lines[] = sprintf("%-10s: %s", "Waktu", $waktu_cetak);
$lines[] = sprintf("%-10s: %s", "Kasir", substr($kasir, 0, 20));

$lines[] = "--------------------------------";

// 3. Modal Kas Awal Laci
$lines[] = "MODAL KAS AWAL LACI";
$lines[] = sprintf("%-16s: Rp.%11s", "Kas Awal", number_format($kas_awal, 0, '.', ','));
if (!empty($tgl_kas_awal)) {
    $lines[] = sprintf("  (Diinput: %s)", date('d/m/Y H:i', strtotime($tgl_kas_awal)));
}

$lines[] = "--------------------------------";

// 4. Rekap Penjualan Toko
$lines[] = "PENJUALAN TOKO :";
$lines[] = sprintf("%-16s: %8s Struk", "Jml Transaksi", number_format($total_transaksi, 0, '.', ','));
$lines[] = sprintf("%-16s: %10s Pcs", "Total Qty", number_format($total_qty, 0, '.', ','));
$lines[] = sprintf("%-16s: Rp.%11s", "- Tunai (Cash)", number_format($total_tunai, 0, '.', ','));
$lines[] = sprintf("%-16s: Rp.%11s", "- QRIS", number_format($total_qris, 0, '.', ','));
$lines[] = sprintf("%-16s: Rp.%11s", "- Transfer Bank", number_format($total_transfer, 0, '.', ','));

if ($total_tukar_jerigen > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "- Tukar Jerigen", "-" . number_format($total_tukar_jerigen, 0, '.', ','));
}
if ($total_voucher > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "- Pot. Voucher", "-" . number_format($total_voucher, 0, '.', ','));
}
if ($total_diskon > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "- Diskon Tambahan", "-" . number_format($total_diskon, 0, '.', ','));
}

$lines[] = sprintf("%-16s: Rp.%11s", "TOTAL OMZET", number_format($total_omzet, 0, '.', ','));

$lines[] = "--------------------------------";

// 5. Pengeluaran Kas Toko
$lines[] = "PENGELUARAN KAS TOKO :";
if (!empty($list_pengeluaran)) {
    foreach ($list_pengeluaran as $idx => $peng) {
        $namaP = ($idx + 1) . ". " . $peng['nama'];
        if (strlen($namaP) > 32) $namaP = substr($namaP, 0, 32);
        $lines[] = $namaP;
        $lines[] = sprintf("   Jam %-7s     Rp.%10s", $peng['jam'] ?? '', number_format($peng['total'], 0, '.', ','));
    }
    $lines[] = sprintf("%-16s: Rp.%11s", "TOT. PENGELUARAN", number_format($total_pengeluaran, 0, '.', ','));
} else {
    $lines[] = "  (Tidak ada pengeluaran kas)";
}

// 6. Pemasukan Lain (Jika Ada)
$cleanPemasukanLain = [];
$totalCleanPemasukanLain = 0;
if (!empty($list_pemasukan_lain)) {
    foreach ($list_pemasukan_lain as $p) {
        $n = strtolower($p['nama'] ?? '');
        if (strpos($n, 'profit') !== false || strpos($n, 'laba') !== false || strpos($n, 'omzet') !== false) {
            continue;
        }
        $cleanPemasukanLain[] = $p;
        $totalCleanPemasukanLain += (float)($p['total'] ?? 0);
    }
}
if (!empty($cleanPemasukanLain)) {
    $lines[] = "--------------------------------";
    $lines[] = "PEMASUKAN KAS LAIN :";
    foreach ($cleanPemasukanLain as $idx => $pem) {
        $lines[] = ($idx + 1) . ". " . substr($pem['nama'], 0, 28);
        $lines[] = sprintf("                Rp.%11s", number_format($pem['total'], 0, '.', ','));
    }
    $lines[] = sprintf("%-16s: Rp.%11s", "TOT. PEMASUKAN", number_format($totalCleanPemasukanLain, 0, '.', ','));
}

$lines[] = "================================";
$lines[] = centerLine("REKAP FISIK KAS LACI");
$lines[] = "================================";

// 7. Rekap Fisik Kas Laci (Audit Owner)
$kasBersihHariIni = $total_tunai + $totalCleanPemasukanLain - $total_pengeluaran;

$lines[] = sprintf("%-16s: Rp.%11s", "Penjualan Tunai", number_format($total_tunai, 0, '.', ','));
if ($totalCleanPemasukanLain > 0) {
    $lines[] = sprintf("%-16s: Rp.%11s", "Pemasukan Lain", "+" . number_format($totalCleanPemasukanLain, 0, '.', ','));
}
$lines[] = sprintf("%-16s: Rp.%11s", "Pengeluaran Kas", "-" . number_format($total_pengeluaran, 0, '.', ','));
$lines[] = "--------------------------------";
$lines[] = sprintf("%-16s: Rp.%11s", "Sisa Kas Hr Ini", number_format($kasBersihHariIni, 0, '.', ','));
$lines[] = sprintf("%-16s: Rp.%11s", "Modal Kas Awal", "+" . number_format($kas_awal, 0, '.', ','));
$lines[] = "================================";
$lines[] = sprintf("%-16s: Rp.%11s", "TOTAL UANG LACI", number_format($fisik_kas_laci, 0, '.', ','));
$lines[] = "================================";

$lines[] = "* QRIS & Trf: Rp. " . number_format($total_qris + $total_transfer, 0, '.', ',');
$lines[] = "  (Tersimpan di Rekening Toko)";

// 8. Khusus Admin / Owner (Sesuai Aturan Financial Privacy)
if (!empty($role) && strtolower($role) === 'admin') {
    $lines[] = "--------------------------------";
    $lines[] = "[ KHUSUS OWNER / ADMIN ]";
    $lines[] = sprintf("%-16s: Rp.%11s", "Est. Profit Kotor", number_format($profit_asli, 0, '.', ','));
    $lines[] = sprintf("(Hex Profit : %s)", $no_laporan);
}

// 9. Tanda Tangan
$lines[] = "";
$lines[] = "Dibuat Oleh:        Mengetahui:";
$lines[] = "";
$lines[] = "";
$kasirShort = substr($kasir, 0, 10);
$lines[] = sprintf("( %-10s )  ( %-10s )", $kasirShort, "Owner/SPV");

// 10. Footer
$lines[] = "--------------------------------";
$lines[] = centerLine("Terima Kasih Kerjasamanya");
$lines[] = centerLine("Tetap Semangat & Jaga Toko");
$lines[] = "********************************";

// Hitung tinggi kertas pas dalam millimeter (mencegah buang kertas 30cm)
$lineCount = count($lines);
$pageHeightMm = max(60, ceil($lineCount * 3.7 + 6));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Laporan Harian - <?= date('d/m/Y', strtotime($tanggal)) ?></title>
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
            width: 58mm;
            max-width: 58mm;
            height: <?= $pageHeightMm ?>mm;
            max-height: <?= $pageHeightMm ?>mm;
            margin: 0 auto;
            padding: 0;
            background: #fff;
            color: #000;
            overflow: hidden;
        }
        .receipt-pre {
            font-family: 'Consolas', 'Courier New', 'Lucida Console', Monaco, monospace;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.22;
            color: #000;
            white-space: pre;
            margin: 0 auto;
            padding: 3px 0 0 0;
            width: 58mm;
            max-width: 58mm;
            letter-spacing: -0.15px;
            overflow: hidden;
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
            @page {
                margin: 0;
                size: 58mm <?= $pageHeightMm ?>mm;
            }
            html, body {
                width: 58mm !important;
                max-width: 58mm !important;
                height: <?= $pageHeightMm ?>mm !important;
                max-height: <?= $pageHeightMm ?>mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
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
    <pre class="receipt-pre"><?= esc(implode("\n", $lines)) ?></pre>
</body>
</html>
