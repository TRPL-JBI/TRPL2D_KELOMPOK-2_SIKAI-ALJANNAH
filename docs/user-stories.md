# User Stories — Website Manajemen RKM Al-Jannah Banyuwangi

Dokumen ini berisi user stories untuk sistem "Rancang Bangun Website Manajemen Keuangan, Anggota, Aset, dan Pusat Informasi RKM Al-Jannah Banyuwangi".

Format: **Sebagai [peran], saya ingin [kemampuan], agar [manfaat].**

## Peran (Roles)

| Peran | Deskripsi |
|---|---|
| Publik / Calon Anggota | Masyarakat yang ingin mendaftar menjadi anggota |
| Anggota | Anggota RKM yang sudah terverifikasi |
| Sekretaris | Mengelola data anggota dan verifikasi |
| Bendahara | Mengelola keuangan, iuran, dan laporan |
| Ketua | Menyetujui izin dan memantau seluruh kegiatan |
| Logistik | Mengelola stok barang dan aset |
| Admin Web | Mengelola konten website (CMS) |
| Super Admin | Mengelola pengguna sistem dan file |

---

## 1. Autentikasi & Akses

### US-01 — Login Pengguna
**Sebagai** pengguna (pengurus/admin),
**saya ingin** masuk ke sistem menggunakan email dan kata sandi,
**agar** saya dapat mengakses halaman sesuai peran saya.

**Kriteria Penerimaan:**
- Login berhasil jika email dan kata sandi benar.
- Login gagal menampilkan pesan kesalahan yang jelas.
- Percobaan login gagal berulang dibatasi (rate limiting) untuk mencegah brute force.
- Setelah login, pengguna diarahkan ke dashboard sesuai perannya.
- Pengguna dapat keluar (logout) dengan aman.

### US-02 — Akses Anggota dengan Kode Akses
**Sebagai** anggota RKM,
**saya ingin** masuk ke dashboard anggota menggunakan kode akses,
**agar** saya dapat melihat informasi keanggotaan tanpa perlu akun email.

**Kriteria Penerimaan:**
- Anggota memasukkan kode akses yang valid untuk masuk.
- Kode akses yang salah menampilkan pesan kesalahan.
- Setelah valid, anggota diarahkan ke dashboard anggota.

---

## 2. Pendaftaran Calon Anggota

### US-03 — Pendaftaran Calon Anggota
**Sebagai** calon anggota,
**saya ingin** mengisi formulir pendaftaran secara online,
**agar** saya dapat mendaftar menjadi anggota RKM tanpa datang langsung.

**Kriteria Penerimaan:**
- Formulir pendaftaran tersedia untuk publik.
- Data yang diisi tervalidasi (wajib diisi, format benar).
- Setelah dikirim, data calon anggota tersimpan dan menunggu verifikasi sekretaris.

---

## 3. Sekretaris — Manajemen Anggota

### US-04 — Verifikasi Calon Anggota
**Sebagai** sekretaris,
**saya ingin** memverifikasi data calon anggota,
**agar** hanya data yang valid yang menjadi anggota resmi.

**Kriteria Penerimaan:**
- Sekretaris melihat daftar calon anggota yang menunggu verifikasi.
- Sekretaris dapat menyetujui atau menolak calon anggota.
- Calon yang disetujui berstatus menjadi anggota.

### US-05 — Kelola Data Anggota
**Sebagai** sekretaris,
**saya ingin** melihat, mengedit, menonaktifkan, dan mengaktifkan kembali data anggota,
**agar** data keanggotaan selalu akurat dan terkini.

**Kriteria Penerimaan:**
- Sekretaris dapat melihat daftar seluruh anggota.
- Sekretaris dapat mengubah data anggota (mis. alamat, kontak, keluarga).
- Sekretaris dapat menonaktifkan anggota (mis. keluar/meninggal) beserta alasan.
- Anggota yang dinonaktifkan dapat diaktifkan kembali.

### US-06 — Cetak Formulir Anggota
**Sebagai** sekretaris,
**saya ingin** mencetak formulir calon anggota dan formulir anggota dalam bentuk PDF,
**agar** dokumen fisik dapat diarsipkan atau ditandatangani.

**Kriteria Penerimaan:**
- Formulir calon anggota dapat diunduh sebagai PDF.
- Formulir anggota dapat diunduh sebagai PDF.
- Data pada formulir sesuai dengan data anggota yang tersimpan.

### US-07 — Import Anggota dari Excel
**Sebagai** sekretaris,
**saya ingin** mengunduh template Excel dan mengimpor data anggota dari file Excel,
**agar** saya tidak perlu memasukkan data lama satu per satu.

**Kriteria Penerimaan:**
- Template Excel tersedia untuk diunduh.
- File Excel sesuai template dapat diimpor.
- Data yang tidak valid dilaporkan/ditangani tanpa mengganggu data valid.

### US-08 — Riwayat Aktivitas (Audit Log)
**Sebagai** sekretaris,
**saya ingin** melihat log aktivitas sistem,
**agar** saya dapat menelusuri perubahan data yang terjadi.

**Kriteria Penerimaan:**
- Log menampilkan aktivitas pengguna (siapa, kapan, aksi apa).
- Log dapat difilter/dicari.

---

## 4. Bendahara — Keuangan & Iuran

### US-09 — Catat Transaksi Keuangan
**Sebagai** bendahara,
**saya ingin** mencatat transaksi pemasukan dan pengeluaran,
**agar** seluruh arus kas RKM tercatat rapi.

**Kriteria Penerimaan:**
- Bendahara dapat mencatat transaksi pemasukan (jenis, jumlah, tanggal, keterangan).
- Bendahara dapat mencatat transaksi pengeluaran.
- Data transaksi tersimpan dan tampil pada daftar pemasukan/pengeluaran.

### US-10 — Kelola Iuran Tahunan
**Sebagai** bendahara,
**saya ingin** mencatat pembayaran iuran anggota per tahun,
**agar** status iuran setiap anggota terpantau.

**Kriteria Penerimaan:**
- Bendahara dapat memilih tahun iuran dan melihat data iuran anggota.
- Bendahara dapat mencatat pembayaran iuran anggota.
- Bendahara dapat membuat kode akses untuk pembayaran iuran.

### US-11 — Kirim Tagihan & Kwitansi Iuran via WhatsApp
**Sebagai** bendahara,
**saya ingin** mengirim tagihan iuran dan kwitansi pembayaran melalui WhatsApp,
**agar** anggota menerima notifikasi tanpa perlu datang ke sekretariat.

**Kriteria Penerimaan:**
- Tagihan iuran dapat dikirim ke nomor WhatsApp anggota.
- Kwitansi pembayaran dapat dikirim setelah pembayaran tercatat.
- Status pengiriman terlihat oleh bendahara.

### US-12 — Verifikasi Pembayaran
**Sebagai** bendahara,
**saya ingin** memverifikasi pembayaran yang masuk,
**agar** status pembayaran anggota akurat.

**Kriteria Penerimaan:**
- Bendahara melihat daftar pembayaran yang menunggu verifikasi.
- Pembayaran dapat diverifikasi (dikonfirmasi) atau ditolak.

### US-13 — Laporan Keuangan
**Sebagai** bendahara,
**saya ingin** melihat dan mengekspor laporan keuangan,
**agar** saya dapat menyajikan laporan kepada ketua/pengurus.

**Kriteria Penerimaan:**
- Laporan menampilkan ringkasan pemasukan, pengeluaran, dan saldo.
- Laporan dapat difilter berdasarkan periode.
- Laporan dapat diekspor ke file (Excel/PDF).

### US-14 — Import Transaksi dari Excel
**Sebagai** bendahara,
**saya ingin** mengunduh template transaksi dan mengimpor transaksi dari Excel,
**agar** data transaksi lama dapat dimasukkan secara massal.

**Kriteria Penerimaan:**
- Template transaksi tersedia untuk diunduh.
- File sesuai template dapat diimpor.
- Data tidak valid ditangani tanpa merusak data valid.

### US-15 — Permintaan Izin ke Ketua
**Sebagai** bendahara,
**saya ingin** mengirim permintaan izin (mis. pengeluaran tertentu) kepada ketua,
**agar** transaksi penting mendapat persetujuan sebelum/agar tercatat sah.

**Kriteria Penerimaan:**
- Bendahara dapat mengirim permintaan izin dengan keterangan.
- Status permintaan (menunggu/disetujui/ditolak) terlihat.

---

## 5. Ketua — Pengawasan & Persetujuan

### US-16 — Dashboard Pengawasan
**Sebagai** ketua,
**saya ingin** melihat ringkasan data anggota, keuangan, aset, dan log aktivitas,
**agar** saya dapat memantau kondisi RKM secara menyeluruh.

**Kriteria Penerimaan:**
- Dashboard menampilkan ringkasan anggota.
- Dashboard menampilkan ringkasan keuangan.
- Dashboard menampilkan ringkasan aset.
- Ketua dapat melihat log aktivitas.

### US-17 — Persetujuan Izin
**Sebagai** ketua,
**saya ingin** menyetujui atau menolak permintaan izin dari bendahara,
**agar** pengeluaran/transaksi penting terkontrol.

**Kriteria Penerimaan:**
- Ketua melihat daftar permintaan izin yang menunggu.
- Ketua dapat menyetujui atau menolak dengan catatan.
- Status keputusan tersimpan dan terlihat oleh pengirim.

### US-18 — Tanda Tangan Digital
**Sebagai** ketua,
**saya ingin** menyimpan tanda tangan digital,
**agar** tanda tangan tersebut tercantum pada formulir/dokumen yang dicetak.

**Kriteria Penerimaan:**
- Ketua dapat mengunggah/menyimpan gambar tanda tangan.
- Tanda tangan tampil pada dokumen yang membutuhkannya.

---

## 6. Logistik — Stok Barang & Aset

### US-19 — Kelola Stok Barang
**Sebagai** petugas logistik,
**saya ingin** menambah, mengubah, dan menghapus data barang,
**agar** inventaris barang RKM tercatat.

**Kriteria Penerimaan:**
- Barang baru dapat ditambahkan (nama, jumlah, satuan, stok minimal).
- Data barang dapat diubah dan dihapus.
- Daftar stok menampilkan jumlah barang terkini.

### US-20 — Catat Barang Masuk & Keluar
**Sebagai** petugas logistik,
**saya ingin** mencatat barang masuk dan barang keluar,
**agar** stok selalu sesuai kondisi nyata.

**Kriteria Penerimaan:**
- Barang masuk dapat dicatat (jumlah bertambah).
- Barang keluar dapat dicatat (jumlah berkurang).
- Stok tidak boleh negatif.

### US-21 — Kelola Aset
**Sebagai** petugas logistik,
**saya ingin** menambah, mengubah, menghapus, dan mengubah status aset (mis. kendaraan),
**agar** data aset RKM lengkap dan terkini.

**Kriteria Penerimaan:**
- Aset dapat ditambahkan dengan detail (nama, kondisi, status).
- Data aset dapat diubah dan dihapus.
- Status aset (aktif/rusak/dipinjam/dll.) dapat diperbarui.

### US-22 — Riwayat Barang
**Sebagai** petugas logistik,
**saya ingin** melihat riwayat pergerakan barang,
**agar** saya dapat menelusuri kapan barang masuk/keluar.

**Kriteria Penerimaan:**
- Riwayat menampilkan catatan barang masuk dan keluar beserta waktu.

---

## 7. Admin Web — CMS & Pusat Informasi

### US-23 — Kelola Berita/Postingan
**Sebagai** admin web,
**saya ingin** membuat, mengedit, menerbitkan, dan menghapus postingan berita,
**agar** informasi kegiatan RKM dapat disampaikan ke publik.

**Kriteria Penerimaan:**
- Postingan baru dapat dibuat dengan judul, konten, dan kategori.
- Postingan dapat diedit dan dihapus.
- Postingan dapat diterbitkan (publish) atau disembunyikan.
- Kategori postingan dapat dikelola.

### US-24 — Kelola Halaman Website
**Sebagai** admin web,
**saya ingin** membuat, mengedit, menerbitkan, dan menghapus halaman website,
**agar** konten statis (profil, kontak, dll.) dapat diperbarui.

**Kriteria Penerimaan:**
- Halaman baru dapat dibuat.
- Halaman dapat diedit dan dihapus.
- Halaman dapat diterbitkan/disembunyikan.

### US-25 — Page Builder
**Sebagai** admin web,
**saya ingin** menyusun halaman menggunakan page builder,
**agar** tata letak halaman dapat disesuaikan tanpa menulis kode.

**Kriteria Penerimaan:**
- Admin dapat membuka mode builder pada halaman.
- Susunan/komponen halaman dapat disimpan.

### US-26 — Kelola Menu Navigasi
**Sebagai** admin web,
**saya ingin** menambah, mengubah, menghapus, dan mengaktifkan/menonaktifkan menu,
**agar** navigasi website sesuai struktur informasi.

**Kriteria Penerimaan:**
- Menu baru dapat ditambahkan.
- Menu dapat diedit, dihapus, dan diurutkan.
- Menu dapat diaktifkan/dinonaktifkan (toggle).

### US-27 — Media Library
**Sebagai** admin web,
**saya ingin** mengunggah, menghapus, dan memulihkan file media (gambar/dokumen),
**agar** aset media untuk konten website terkelola.

**Kriteria Penerimaan:**
- File media dapat diunggah ke pustaka media.
- File dapat dihapus (soft delete) dan dipulihkan dari tempat sampah.

---

## 8. Super Admin — Manajemen Sistem

### US-28 — Kelola Pengguna Sistem
**Sebagai** super admin,
**saya ingin** menambah dan memperbarui akun pengguna beserta perannya,
**agar** akses sistem sesuai jabatan pengurus.

**Kriteria Penerimaan:**
- Pengguna baru dapat ditambahkan dengan peran tertentu.
- Data pengguna (nama, email, peran, status) dapat diperbarui.

### US-29 — Pusat File
**Sebagai** super admin,
**saya ingin** mengunggah, mengunduh, dan mengelola file (termasuk tempat sampah),
**agar** dokumen organisasi tersimpan terpusat.

**Kriteria Penerimaan:**
- File dapat diunggah dan diunduh.
- File yang dihapus masuk tempat sampah dan dapat dipulihkan.

### US-30 — Log Aktivitas Sistem
**Sebagai** super admin,
**saya ingin** melihat log aktivitas seluruh pengguna,
**agar** saya dapat mengawasi penggunaan sistem.

**Kriteria Penerimaan:**
- Log menampilkan aktivitas pengguna secara lengkap.
- Log dapat dicari/difilter.

---

## 9. Infrastruktur Pendukung

### US-31 — Gateway WhatsApp
**Sebagai** pengurus,
**saya ingin** menghubungkan sistem ke gateway WhatsApp (mulai/hentikan server, scan QR, kirim pesan),
**agar** notifikasi tagihan/kwitansi dapat dikirim otomatis ke anggota.

**Kriteria Penerimaan:**
- Status server gateway terlihat.
- Server dapat dijalankan/dihentikan.
- Koneksi dapat dibuat melalui QR.
- Pesan dan file uji dapat dikirim.

### US-32 — Unduhan & Penyajian File
**Sebagai** pengguna sistem,
**saya ingin** mengunduh file (dokumen, media, formulir) yang dilindungi akses,
**agar** dokumen tidak dapat diakses langsung tanpa otorisasi.

**Kriteria Penerimaan:**
- File hanya dapat diunduh melalui rute yang diautentikasi.
- Media dari editor dapat diunggah dan disajikan dengan benar.

---

## Ringkasan per Peran

| Peran | User Stories |
|---|---|
| Publik / Calon Anggota | US-03 |
| Anggota | US-02 |
| Semua pengguna (auth) | US-01, US-31, US-32 |
| Sekretaris | US-04 s.d. US-08 |
| Bendahara | US-09 s.d. US-15 |
| Ketua | US-16 s.d. US-18 |
| Logistik | US-19 s.d. US-22 |
| Admin Web | US-23 s.d. US-27 |
| Super Admin | US-28 s.d. US-30 |