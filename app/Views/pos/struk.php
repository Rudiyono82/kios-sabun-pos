<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk <?= esc($penjualan['no_faktur']) ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 8px;
            width: 260px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="header-title"><?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?></div>
        <div><?= esc($profil['alamat_1'] ?? 'Jl. Metland Cileungsi') ?></div>
        <div><?= esc($profil['alamat_2'] ?? '') ?></div>
        <div>Telp: <?= esc($profil['telepon'] ?? '-') ?></div>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No Faktur</td>
            <td>: <?= esc($penjualan['no_faktur']) ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])) ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: <?= esc($penjualan['nama_kasir'] ?? 'Kasir') ?></td>
        </tr>
        <?php if (!empty($penjualan['kode_member'])): ?>
        <tr>
            <td>Member</td>
            <td>: <?= esc($penjualan['kode_member']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <table>
        <?php foreach ($items as $it): ?>
            <tr>
                <td colspan="2"><b><?= esc($it['nama_barang']) ?></b></td>
            </tr>
            <tr>
                <td><?= (float) $it['qty'] ?> x @<?= number_format($it['harga_jual'], 0, ',', '.') ?></td>
                <td class="text-end">Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-end">Rp <?= number_format($penjualan['subtotal'], 0, ',', '.') ?></td>
        </tr>
        <?php if ($penjualan['potongan_tukar_jerigen'] > 0): ?>
        <tr>
            <td>Tukar Jerigen 5L</td>
            <td class="text-end">-Rp <?= number_format($penjualan['potongan_tukar_jerigen'], 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($penjualan['potongan_voucher']) && $penjualan['potongan_voucher'] > 0): ?>
        <tr>
            <td>Voucher (<?= esc($penjualan['kode_voucher'] ?? 'Member') ?>)</td>
            <td class="text-end">-Rp <?= number_format($penjualan['potongan_voucher'], 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($penjualan['diskon'] > 0): ?>
        <tr>
            <td>Diskon</td>
            <td class="text-end">-Rp <?= number_format($penjualan['diskon'], 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr style="font-weight: bold; font-size: 12px;">
            <td>TOTAL</td>
            <td class="text-end">Rp <?= number_format($penjualan['grand_total'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Bayar (<?= esc($penjualan['metode_bayar']) ?>)</td>
            <td class="text-end">Rp <?= number_format($penjualan['bayar'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="text-end">Rp <?= number_format($penjualan['kembalian'], 0, ',', '.') ?></td>
        </tr>
        <?php if (!empty($penjualan['kode_member'])): ?>
            <?php 
                $mb = \Config\Database::connect()->table('member')->where('kode_member', $penjualan['kode_member'])->get()->getRowArray();
            ?>
            <?php if ($mb): ?>
            <tr>
                <td colspan="2" class="text-center" style="font-size: 9px; padding-top: 6px;">
                    *** Saldo Poin Member: <b><?= number_format((float)($mb['point'] ?? 0), 0, ',', '.') ?> Poin</b> ***
                </td>
            </tr>
            <?php endif; ?>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 10px;">
        <div>Terima Kasih Atas Kunjungan Anda!</div>
        <div>Barang yang dibeli dapat ditukar jika kemasan cacat / bocor saat pembelian.</div>
    </div>
</body>
</html>

