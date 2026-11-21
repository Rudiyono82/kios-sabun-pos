# KIOSSABUN POS - SYSTEM MEMORY & CORE ARCHITECTURAL RULES

Dokumen ini berisi aturan permanen dan konsensus sistem yang wajib ditaati oleh AI Agent dan Developer dalam pengembangan aplikasi Kios Sabun & Parfum POS.

---

## 1. KEAMANAN FINANSIAL & HAK AKSES ROLE (FINANCIAL DATA PRIVACY)

### A. Role Kasir (`role = 'kasir'`)
1. **DILARANG KERAS**:
   - Menampilkan, mengirimkan via JSON API, merender di tampilan HTML, atau mencetak di struk kasir:
     - **Teks Eksplisit Estimasi Margin Profit Kotor / Bersih** (`total_profit`, `profit`).
     - **Grafik Laba / Margin Profit** di Dashboard.
     - **Rincian Laba per Item / Faktur** pada modal detail penjualan (`/penjualan/detail`).
     - **Menu Laporan Owner & Valuasi Aset**.
   - **Menambah, Mengedit, atau Mengubah Data Master Barang** (kasir sama sekali tidak diizinkan mengedit/menambah master data barang; kasir hanya berhak melihat katalog barang / detail read-only).
   - **Menginput atau Mengubah Harga Beli di Form Transaksi Pembelian atau Retur**:
     - Di form Pembelian / Restock dan form Retur Supplier, kasir **TIDAK MENGINPUT** `harga_beli`.
     - `harga_beli` bersifat **MURNI LOOKUP OTOMATIS (READ-ONLY)** dari Master Barang.

2. **Data & Hak Akses yang Diizinkan untuk Kasir (Kebutuhan Operasional Toko)**:
   - **Nomor Laporan Berkode Hex Profit** (warisan sistem VB6 `dechex(totalProfit)`):
     - Nomor Laporan pada Struk Thermal Rekap Kasir dan Laporan Harian **TETAP menggunakan format kode Hex**.
     - Bagi kasir, kode ini tampak seperti nomor seri laporan biasa tanpa mengetahui maknanya.
     - Bagi Owner, kode hex ini adalah sarana rahasia untuk membaca profit harian dari kertas struk printout (`hexdec(...)`) maupun dari web login admin.
   - Melihat daftar produk / katalog master barang dan rincian item secara *read-only* (termasuk melihat `harga_beli` sebagai informasi pengecekan tanpa izin edit).
   - Menjalankan transaksi Pembelian / Restock barang dari supplier dan Retur Supplier dengan `harga_beli` yang terisi otomatis dari lookup Master Barang.
   - Saldo Kas Awal Laci Toko (`kas_awal`).
   - Total Omzet Penjualan (`omzet` / `grand_total`).
   - Total Pengeluaran / Biaya Operasional Toko (`pengeluaran`).
   - Jumlah Transaksi (`total_transaksi` / Faktur) dan Volume Penjualan (`total_qty`).
   - Total Kas Fisik di Laci (`fisik_kas_laci`).
   - Total Non-Tunai (QRIS & Transfer Bank).

### B. Role Administrator / Owner (`role = 'admin'`)
- Merupakan **SATU-SATUNYA TEMPAT & ROLE** yang berhak menginput, menentukan, dan mengedit data `harga_beli` (HPP), harga jual, paket grosir, stok, serta seluruh master data barang pada form Master Barang.
- Memiliki hak akses penuh melihat seluruh metrik finansial, HPP, profitabilitas per barang, estimasi laba kotor/bersih, laporan arus kas lengkap, valuasi aset toko, dan laporan owner.

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
