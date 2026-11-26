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
   - **Aturan Input Master Barang Baru & Manipulasi Harga / Stok**:
     - **Format Kode Barang Otomatis**: Kode Barang di-generate otomatis diawali huruf `'B'` + 4 digit tahun sekarang + 3 digit sequence nomor urut per tahun (contoh: `B2026001`, `B2026074`).
     - **Kasir Input Awal Produk Lengkap Termasuk Harga & Paket**:
       - Kasir dapat mendaftarkan produk baru secara lengkap mulai dari Nama, Barcode, Kategori, Satuan, Supplier, Foto, hingga seluruh struktur finansial: `harga_beli` (HPP), `harga_jual`, `harga_grosir`, serta `paket grosir A/B/C` (`qty_paket_*`, `hrg_paket_*`).
       - Status produk baru yang diinput Kasir **OTOMATIS DIKUNCI MENJADI `Pending`** (Menunggu Approval Admin).
       - Jika produk masih berstatus `Pending`, Kasir masih diizinkan mengoreksi usulan harga dan paket grosir.
     - **Proteksi Barang Aktif**:
       - Barang berstatus `Pending` **DILARANG KERAS** dijual di Kasir POS, dibeli di Pembelian Supplier, atau tampil di luar screen Master Barang sampai disetujui (*Approved*) oleh Admin.
       - Kasir **DILARANG KERAS** memanipulasi atau mengubah data finansial & inventori barang yang sudah berstatus `Aktif`: `harga_beli` (HPP), `harga_jual`, `harga_grosir`, paket grosir A/B/C (`qty_paket_*`, `hrg_paket_*`), dan jumlah `stok`.
       - Di backend (`Barang::simpan`), jika yang menyimpan adalah Kasir untuk barang yang sudah `Aktif`, sistem **WAJIB MENGABAIKAN** seluruh inputan harga & stok dari request dan mempertahankan nilai asli dari database.
   - **Hapus Data Master Barang**:
     - Kasir tidak dapat menghapus master barang (wajib otorisasi password Admin).
   - **Menginput atau Mengubah Harga Beli di Form Transaksi Pembelian atau Retur**:
     - Di form Pembelian / Restock dan form Retur Supplier, kasir **TIDAK MENGINPUT** `harga_beli`.
     - `harga_beli` bersifat **MURNI LOOKUP OTOMATIS (READ-ONLY)** dari Master Barang.

2. **Data & Hak Akses yang Diizinkan untuk Kasir (Kebutuhan Operasional Toko)**:
   - **Input Barang Baru dengan Status `Pending`**:
     - Kasir dapat mendaftarkan produk baru lengkap dengan usulan harga modal, harga jual, dan paket grosir. Status otomatis `Pending` sampai disetujui Admin.
   - **Hak Edit Terbatas pada Master Barang**:
     - Untuk barang berstatus `Pending`: Kasir dapat mengedit seluruh informasi termasuk usulan harga & paket grosir.
     - Untuk barang berstatus `Aktif`: Kasir hanya diizinkan mengedit informasi operasional produk: **Nama Barang**, **Barcode / Kode Barang**, **Kategori**, **Satuan Kemasan**, **Supplier (Kode Supplier 1 & 2)**, opsi **Is Jerigen**, dan **Foto / Gambar Produk**. Kolom Harga dan Stok terkunci (`readonly` berlatar abu-abu).
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
- Merupakan **SATU-SATUNYA TEMPAT & ROLE** yang berhak menyetujui (*Approve*) pengajuan barang baru dari status `Pending` menjadi `Aktif`. Admin dapat menyetujui dengan 1 klik atau menyesuaikan harga beli, harga jual, dan paket grosir terlebih dahulu.
- Memiliki hak akses penuh mengubah data `harga_beli` (HPP), harga jual, paket grosir, stok, serta seluruh master data barang pada form Master Barang.
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
