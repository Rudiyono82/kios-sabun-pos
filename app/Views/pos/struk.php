<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= esc($penjualan['no_faktur']) ?></title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            margin: 0;
            size: 58mm auto;
        }
        html, body {
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
            font-family: 'Consolas', 'Courier New', 'Lucida Console', monospace;
            font-size: 11px;
            line-height: 1.35;
        }
        .receipt-wrapper {
            width: 100%;
            max-width: 58mm;
            margin: 0 auto;
            padding: 6px 6px 25mm 6px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }

        .store-name {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .store-addr {
            font-size: 10px;
            line-height: 1.25;
        }

        hr.divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
            height: 1px;
            width: 100%;
        }
        hr.divider-double {
            border: none;
            border-top: 2px dashed #000;
            margin: 6px 0;
            height: 2px;
            width: 100%;
        }

        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.receipt-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 11px;
        }
        .nowrap {
            white-space: nowrap;
        }

        /* Action bar saat struk dibuka di window/tab terpisah */
        .action-bar {
            padding: 6px 8px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }
        .btn-act {
            flex: 1;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            font-family: sans-serif;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #0f172a;
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
            }
            .receipt-wrapper {
                width: 100% !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 4px 4px 28mm 4px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Aksi saat dibuka langsung di tab/layar baru -->
    <div class="action-bar no-print">
        <button type="button" class="btn-act btn-act-print" onclick="window.print()">
            &#128438; CETAK STRUK
        </button>
        <button type="button" class="btn-act" onclick="window.close()">
            &#10005; TUTUP
        </button>
    </div>

    <div class="receipt-wrapper">
        <!-- Header Profil Toko / Cabang -->
        <div class="text-center">
            <div class="store-name"><?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?></div>
            <div class="store-addr"><?= esc($profil['alamat_1'] ?? 'Jl. Metland Cileungsi') ?></div>
            <?php if (!empty($profil['alamat_2'])): ?>
                <div class="store-addr"><?= esc($profil['alamat_2']) ?></div>
            <?php endif; ?>
            <div class="store-addr">Telp: <?= esc($profil['telepon'] ?? '-') ?></div>
        </div>

        <hr class="divider">

        <!-- Info Faktur & Kasir -->
        <table class="receipt-table" width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 70px;" align="left">No Faktur</td>
                <td align="left">: <b><?= esc($penjualan['no_faktur']) ?></b></td>
            </tr>
            <tr>
                <td align="left">Tanggal</td>
                <td align="left">: <?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])) ?></td>
            </tr>
            <tr>
                <td align="left">Kasir</td>
                <td align="left">: <?= esc($penjualan['nama_kasir'] ?? 'Kasir') ?></td>
            </tr>
            <?php if (!empty($penjualan['kode_member'])): ?>
            <tr>
                <td align="left">Member</td>
                <td align="left">: <?= esc($penjualan['kode_member']) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <hr class="divider">

        <!-- Rincian Item Belanja -->
        <table class="receipt-table" width="100%" border="0" cellpadding="0" cellspacing="0">
            <?php foreach ($items as $it): ?>
                <tr>
                    <td colspan="2" style="font-weight: bold; word-break: break-word;" align="left">
                        <?= esc($it['nama_barang']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 52%;" align="left">
                        <?= (float) $it['qty'] ?> x @<?= number_format($it['harga_jual'], 0, ',', '.') ?>
                    </td>
                    <td style="width: 48%; text-align: right;" align="right" class="nowrap">
                        Rp <?= number_format($it['subtotal'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <hr class="divider">

        <!-- Rincian Pembayaran / Total -->
        <table class="receipt-table" width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 50%;" align="left">Subtotal</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    Rp <?= number_format($penjualan['subtotal'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php if ($penjualan['potongan_tukar_jerigen'] > 0): ?>
            <tr>
                <td style="width: 50%;" align="left">Tukar Jerigen 5L</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    -Rp <?= number_format($penjualan['potongan_tukar_jerigen'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($penjualan['potongan_voucher']) && $penjualan['potongan_voucher'] > 0): ?>
            <tr>
                <td style="width: 50%;" align="left">Voucher (<?= esc($penjualan['kode_voucher'] ?? 'Member') ?>)</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    -Rp <?= number_format($penjualan['potongan_voucher'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($penjualan['diskon'] > 0): ?>
            <tr>
                <td style="width: 50%;" align="left">Diskon</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    -Rp <?= number_format($penjualan['diskon'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php 
                $rawTotal = (float)$penjualan['subtotal'] - (float)$penjualan['diskon'] - (float)$penjualan['potongan_tukar_jerigen'] - (float)($penjualan['potongan_voucher'] ?? 0);
                $pembulatan = (float)$penjualan['grand_total'] - $rawTotal;
            ?>
            <?php if ($pembulatan != 0): ?>
            <tr>
                <td style="width: 50%;" align="left">Pembulatan</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    <?= ($pembulatan > 0 ? '+' : '') . 'Rp ' . number_format($pembulatan, 0, ',', '.') ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr style="font-weight: bold; font-size: 12px;">
                <td style="width: 45%; padding: 3px 0;" align="left">TOTAL</td>
                <td style="width: 55%; text-align: right; padding: 3px 0;" align="right" class="nowrap">
                    Rp <?= number_format($penjualan['grand_total'], 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;" align="left">Bayar (<?= esc($penjualan['metode_bayar']) ?>)</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    Rp <?= number_format($penjualan['bayar'], 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;" align="left">Kembalian</td>
                <td style="width: 50%; text-align: right;" align="right" class="nowrap">
                    Rp <?= number_format($penjualan['kembalian'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php if (!empty($penjualan['kode_member'])): ?>
                <?php 
                    $mb = \Config\Database::connect()->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
                ?>
                <?php if ($mb): ?>
                <tr>
                    <td colspan="2" class="text-center" style="font-size: 10px; padding-top: 6px;" align="center">
                        *** Poin Member: <b><?= number_format((float)($mb['point'] ?? 0), 0, ',', '.') ?> Poin</b> ***
                    </td>
                </tr>
                <?php endif; ?>
            <?php endif; ?>
        </table>

        <hr class="divider">

        <!-- Footer Struk & Kebijakan Toko -->
        <div class="text-center" style="font-size: 10px; line-height: 1.35;">
            <div class="fw-bold"><?= esc($profil['footer_struk'] ?? 'Terima Kasih Atas Kunjungan Anda!') ?></div>
            <div>Barang yang dibeli dapat ditukar jika kemasan cacat/bocor saat pembelian.</div>
        </div>

        <!-- Space Trailing Paper Feed agar teks footer tidak terpotong pisau cutter -->
        <div class="feed-space" style="height: 25mm; line-height: 25mm;">&nbsp;</div>
    </div>
</body>
</html>
