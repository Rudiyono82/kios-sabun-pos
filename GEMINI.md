# KIOSSABUN POS - SYSTEM MEMORY & CORE ARCHITECTURAL RULES

Dokumen ini berisi aturan permanen dan konsensus sistem yang wajib ditaati oleh AI Agent dan Developer dalam pengembangan aplikasi Kios Sabun & Parfum POS.

---

## 1. KEAMANAN FINANSIAL & HAK AKSES ROLE (FINANCIAL DATA PRIVACY)

### A. Role Kasir (`role = 'kasir'`)
1. **DILARANG KERAS** menampilkan, mengirimkan via JSON API, merender di tampilan HTML, atau mencetak di struk thermal kasir hal-hal berikut:
   - **Estimasi Margin Profit Kotor / Bersih** (`total_profit`, `profit`).
   - **Nomor Laporan Berkode Hex Profit** (warisan VB6 `dechex(totalProfit)`). Untuk kasir, nomor laporan wajib berupa nomor urut tanggal standar (`YYYYMMDD-XXX`).
   - **Grafik Laba / Margin Profit** di Dashboard.
   - **Rincian Laba per Item / Faktur** pada modal detail penjualan (`/penjualan/detail`).
   - **Menu Laporan Owner & Valuasi Aset**.
   - **Mengedit / Mengubah Data Master Harga Beli Barang** (field `harga_beli` saat edit barang wajib di-disabled/readonly untuk kasir; perubahan harga master pada modul pembelian wajib meminta otorisasi password admin).

2. **Data Finansial yang Diizinkan untuk Kasir (Kebutuhan Operasional Lengkap Toko)**:
   - **Melihat & Menginput Harga Beli (Modal / HPP)** pada:
     - Form transaksi Pembelian / Restock barang dari supplier sesuai nota belanja fisik.
     - Form Retur Pembelian ke supplier.
     - Tampilan daftar produk / katalog master barang dan rincian item (sebagai informasi / read-only).
   - Saldo Kas Awal Laci Toko (`kas_awal`).
   - Total Omzet Penjualan (`omzet` / `grand_total`).
   - Total Pengeluaran / Biaya Operasional Toko (`pengeluaran`).
   - Jumlah Transaksi (`total_transaksi` / Faktur) dan Volume Penjualan (`total_qty`).
   - Total Kas Fisik di Laci (`fisik_kas_laci`).
   - Total Non-Tunai (QRIS & Transfer Bank).

### B. Role Administrator / Owner (`role = 'admin'`)
- Memiliki hak akses penuh melihat seluruh metrik finansial, HPP, profitabilitas per barang, estimasi laba kotor/bersih, laporan arus kas lengkap, valuasi aset toko, dan laporan owner.
- Memiliki wewenang mengubah / mengedit master data harga beli dan harga jual produk.

---

## 2. PROTEKSI ANTI DOUBLE-CLICK & INTEGRITAS TRANSAKSI

1. **Frontend Submit Protection**:
   - Setiap form dan tombol aksi penyimpanan (`#btn-proses-transaksi`, `.btn-save`, `button[type="submit"]`) wajib dikunci instan (`prop('disabled', true)` dan `pointer-events: none`) saat diklik.
   - Tombol transaksi POS wajib mengirimkan `client_token` unik untuk setiap keranjang belanja yang diproses.
   - Handler tombol keyboard (`Enter`, `F9`) wajib mengecek status submitting agar tidak memicu submit berulang (double firing).

2. **Backend Concurrency & Idempotency Protection**:
   - Backend controller (`Pos::simpanTransaksi`) wajib menggunakan serialisasi transaksi (database lock `GET_LOCK` per user/cabang) untuk mencegah race condition dari request paralel.
   - Wajib memeriksa `client_token` dan debounce window (minimal 30 detik untuk transaksi identik berturut-turut) agar tidak pernah menciptakan faktur ganda atau pemotongan stok ganda.

---

## 3. ARSITEKTUR MULTI-DATABASE & SYNCHRONIZATION
- Database lokal menggunakan SQLite (`writable/database.db`) untuk mode offline/development.
- Database production di cloud Railway menggunakan MySQL.
- Setiap migrasi atau sinkronisasi struktur wajib memvalidasi kompatibilitas tipe data antara SQLite dan MySQL.
- Branch / mirror repositori:
  - Repositori lokal: `d:\dev\ksr\APLIKASI POS\pos-ci-ci4`
  - Web root lokal: `C:\xampp\htdocs\kios-sabun-parfum-pos`
  - Remote Git: Railway auto-deploy via `main` branch.
