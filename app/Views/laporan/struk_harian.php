<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Laporan Harian - <?= date('d/m/Y', strtotime($tanggal)) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 10px;
            width: 300px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .divider-double {
            border-top: 2px dashed #000;
            margin: 7px 0;
        }
        .store-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .nowrap {
            white-space: nowrap;
        }
        .box-highlight {
            border: 1px dashed #000;
            padding: 4px;
            margin: 5px 0;
        }
        .signature-area {
            margin-top: 14px;
        }

        /* Tombol Aksi saat di layar komputer */
        .action-bar {
            margin-bottom: 12px;
            display: flex;
            gap: 6px;
        }
        .btn-act {
            flex: 1;
            padding: 6px 10px;
            font-family: sans-serif;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #000;
            background: #f1f5f9;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
                width: 100%;
            }
            @page {
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Khusus di Layar (Tidak Tercetak di Struk Kertas) -->
    <div class="action-bar no-print">
        <button class="btn-act btn-print" onclick="window.print()">
            &#128438; CETAK STRUK
        </button>
        <button class="btn-act" onclick="window.close()">
            &#10005; TUTUP
        </button>
    </div>

    <!-- Header Profil Toko -->
    <div class="text-center">
        <div class="store-title"><?= esc($profil['nama_toko'] ?? 'KIOS SABUN & PARFUM') ?></div>
        <div><?= esc($profil['alamat_1'] ?? 'Jl. Metland Cileungsi') ?></div>
        <?php if (!empty($profil['alamat_2'])): ?>
            <div><?= esc($profil['alamat_2']) ?></div>
        <?php endif; ?>
        <div>Telp: <?= esc($profil['telepon'] ?? '-') ?></div>
    </div>

    <div class="divider"></div>

    <div class="text-center fw-bold">
        LAPORAN REKAP HARIAN KASIR
    </div>

    <div class="divider"></div>

    <!-- Meta Laporan -->
    <table>
        <tr>
            <td style="width: 85px;">No. Laporan</td>
            <td>: <b><?= esc($no_laporan) ?></b></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= date('d/m/Y', strtotime($tanggal)) ?></td>
        </tr>
        <tr>
            <td>Waktu Cetak</td>
            <td>: <?= $waktu_cetak ?></td>
        </tr>
        <tr>
            <td>Kasir/Shift</td>
            <td>: <?= esc($kasir) ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- 1. KAS AWAL MODAL LACI -->
    <table>
        <tr class="fw-bold">
            <td>MODAL KAS AWAL LACI</td>
            <td class="text-end nowrap">Rp <?= number_format($kas_awal, 0, ',', '.') ?></td>
        </tr>
        <?php if (!empty($tgl_kas_awal)): ?>
        <tr>
            <td colspan="2" style="font-size: 10px; color: #333;">(Diinput: <?= date('d/m/Y H:i', strtotime($tgl_kas_awal)) ?>)</td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <!-- 2. REKAP PENJUALAN / OMZET -->
    <div class="fw-bold">PENJUALAN TOKO :</div>
    <table>
        <tr>
            <td>Jumlah Transaksi</td>
            <td class="text-end nowrap"><?= number_format($total_transaksi, 0, ',', '.') ?> Struk</td>
        </tr>
        <tr>
            <td>Total Qty Terjual</td>
            <td class="text-end nowrap"><?= number_format($total_qty, 0, ',', '.') ?> Pcs</td>
        </tr>
        <tr>
            <td>- Bayar Tunai (Cash)</td>
            <td class="text-end nowrap">Rp <?= number_format($total_tunai, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>- Bayar QRIS</td>
            <td class="text-end nowrap">Rp <?= number_format($total_qris, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>- Bayar Transfer Bank</td>
            <td class="text-end nowrap">Rp <?= number_format($total_transfer, 0, ',', '.') ?></td>
        </tr>
        <?php if ($total_tukar_jerigen > 0): ?>
        <tr>
            <td>- Pot. Tukar Jerigen</td>
            <td class="text-end nowrap">-Rp <?= number_format($total_tukar_jerigen, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($total_voucher > 0): ?>
        <tr>
            <td>- Pot. Voucher Member</td>
            <td class="text-end nowrap">-Rp <?= number_format($total_voucher, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($total_diskon > 0): ?>
        <tr>
            <td>- Diskon Tambahan</td>
            <td class="text-end nowrap">-Rp <?= number_format($total_diskon, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr class="fw-bold">
            <td>TOTAL OMZET PENJUALAN</td>
            <td class="text-end nowrap">Rp <?= number_format($total_omzet, 0, ',', '.') ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- 3. BIAYA OPERASIONAL / PENGELUARAN KAS -->
    <div class="fw-bold">PENGELUARAN KAS TOKO :</div>
    <?php if (!empty($list_pengeluaran)): ?>
        <table>
            <?php foreach ($list_pengeluaran as $idx => $peng): ?>
                <tr>
                    <td colspan="2"><?= ($idx + 1) ?>. <?= esc($peng['nama']) ?></td>
                </tr>
                <tr>
                    <td style="font-size: 10px; color: #444; padding-left: 14px;">Jam <?= esc($peng['jam']) ?></td>
                    <td class="text-end nowrap">Rp <?= number_format($peng['total'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="fw-bold">
                <td>TOTAL PENGELUARAN</td>
                <td class="text-end nowrap">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
            </tr>
        </table>
    <?php else: ?>
        <div style="font-style: italic; color: #444;">- Tidak ada pengeluaran kas -</div>
    <?php endif; ?>

    <!-- 4. PEMASUKAN KAS LAIN (JIKA ADA) -->
    <?php if (!empty($list_pemasukan_lain)): ?>
        <div class="divider"></div>
        <div class="fw-bold">PEMASUKAN KAS LAIN :</div>
        <table>
            <?php foreach ($list_pemasukan_lain as $idx => $pem): ?>
                <tr>
                    <td><?= ($idx + 1) ?>. <?= esc($pem['nama']) ?></td>
                    <td class="text-end nowrap">Rp <?= number_format($pem['total'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="fw-bold">
                <td>TOTAL PEMASUKAN LAIN</td>
                <td class="text-end nowrap">Rp <?= number_format($pemasukan_lain, 0, ',', '.') ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <div class="divider-double"></div>

    <!-- 5. REKAPITULASI FISIK KAS LACI KASIR (AUDIT OWNER) -->
    <div class="text-center fw-bold" style="font-size: 12px; margin-bottom: 4px;">
        REKAP FISIK KAS LACI
    </div>
    <table>
        <tr>
            <td>Modal Kas Awal</td>
            <td class="text-end nowrap">Rp <?= number_format($kas_awal, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>+ Penjualan Tunai (Cash)</td>
            <td class="text-end nowrap">+Rp <?= number_format($total_tunai, 0, ',', '.') ?></td>
        </tr>
        <?php if ($pemasukan_lain > 0): ?>
        <tr>
            <td>+ Pemasukan Kas Lain</td>
            <td class="text-end nowrap">+Rp <?= number_format($pemasukan_lain, 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>- Pengeluaran Kas Toko</td>
            <td class="text-end nowrap">-Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="fw-bold" style="font-size: 12px;">
        <tr>
            <td>TOTAL FISIK UANG DI LACI</td>
            <td class="text-end nowrap">Rp <?= number_format($fisik_kas_laci, 0, ',', '.') ?></td>
        </tr>
    </table>

    <div class="divider-double"></div>

    <!-- Info Tambahan Non-Tunai Masuk Rekening Toko -->
    <div style="font-size: 10px;">
        <div>* Total QRIS & Transfer: <b>Rp <?= number_format($total_qris + $total_transfer, 0, ',', '.') ?></b> (Masuk Rekening Toko).</div>
        <div>* Total Penjualan Bersih Toko Hari Ini: <b>Rp <?= number_format($total_omzet, 0, ',', '.') ?></b>.</div>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="box-highlight text-center">
        <div class="fw-bold" style="font-size: 10px;">[ KHUSUS OWNER / ADMIN ]</div>
        <div>Estimasi Margin Profit Kotor:</div>
        <div class="fw-bold" style="font-size: 12px;">Rp <?= number_format($profit_asli, 0, ',', '.') ?> <span style="font-size: 10px; font-weight: normal; color: #444;">(Hex: <?= esc($no_laporan) ?>)</span></div>
    </div>
    <?php endif; ?>

    <!-- Tanda Tangan Kasir & Owner -->
    <div class="signature-area">
        <table style="text-align: center;">
            <tr>
                <td style="width: 50%;">Dibuat Oleh:</td>
                <td style="width: 50%;">Mengetahui:</td>
            </tr>
            <tr>
                <td style="height: 38px;"></td>
                <td style="height: 38px;"></td>
            </tr>
            <tr>
                <td>( <?= esc($kasir) ?> )</td>
                <td>( Owner / SPV )</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 10px; margin-top: 6px;">
        <div>Terima Kasih Atas Kerjasama Tim!</div>
        <div>Tetap Semangat & Jaga Integritas Toko.</div>
    </div>

    <script>
        // Otomatis muncul dialog print thermal saat dibuka
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>

