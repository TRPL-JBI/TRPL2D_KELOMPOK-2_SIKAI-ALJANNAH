# Desain: Kirim Tanda Tangan (Role Ketua)

Tanggal: 2026-08-19

## Ringkasan

Ketua mengirim foto tanda tangan sekali selama masa jabatan, bertempat di dalam menu Beranda role ketua (bukan menu sidebar baru), tepat di bawah 4 card angka statistik. Sekretaris tetap yang membubuhkan tanda tangan ke formulir melalui tombol verifikasi yang sudah ada.

## Keputusan yang disetujui user

1. **Boleh diganti kapan saja** — setelah terkirim, halaman menampilkan preview tanda tangan saat ini + tombol "Ganti Tanda Tangan".
2. **Foto diproses otomatis** — resize max tinggi 300px, latar diputihkan, kontras dinaikkan (GD), disimpan sebagai PNG. Hasil bersih & jelas di formulir, ukuran PDF tetap kecil.

## UI

Card full-width baru di `resources/views/dashboard/ketua/index.blade.php`, tepat setelah 4 card statistik.

### Belum ada tanda tangan
- Area preview foto yang dipilih.
- Dua tombol sumber foto:
  - "Pilih Foto" → file input (upload foto yang sudah ada).
  - "Ambil Foto" → modal kamera via `getUserMedia` (live preview → jepret → retake / gunakan).
- Tombol "Kirim Tanda Tangan".

### Sudah ada tanda tangan
- Preview tanda tangan saat ini + status "Tanda tangan terkirim".
- Tombol "Ganti Tanda Tangan" → membuka UI upload yang sama (mengganti foto lama).

### Catatan bawah card
"Disarankan menggunakan foto yang sudah ada. Lebih baik lagi jika foto tanda tangan sudah diedit agar lebih jelas."

## Data flow

- Route baru: `POST /ketua/tanda-tangan` → `KetuaController::storeTandaTangan()`.
- Input: `foto` (file upload) ATAU `foto_data_url` (hasil kamera, base64 PNG).
- Proses GD: load (jpeg/png/webp) → grayscale → latar putih (piksel terang > threshold dijadikan putih) → kontras dinaikkan → resize max tinggi 300px → simpan PNG.
- Simpan: `storage/app/public/tanda-tangan/{userId}.png`, update kolom `users.tanda_tangan`.
- Konsumen (formulir sekretaris) sudah otomatis memakai via `User::ketua()->tandaTanganDataUri()` — tidak perlu ubah sisi sekretaris.
- Log `LogAktivitas` (aksi `kirim_tanda_tangan`, modul `tanda_tangan`).

## Validasi & error handling

- File: image, mimes `jpeg,jpg,png,webp`, max 5MB.
- Data URL wajib cocok pola `^data:image/(jpeg|png|webp);base64,`.
- Kamera gagal (diblokir, tidak tersedia, non-HTTPS) → tampil pesan di modal + arahkan ke "Pilih Foto".
- Respons AJAX JSON `{ success, message }`, memakai toast yang sudah ada di halaman.

## File yang disentuh

- `routes/web.php` — tambah 1 route POST di group ketua.
- `app/Http/Controllers/KetuaController.php` — `storeTandaTangan()` + helper `processSignatureImage()`, tambah `$signature` di `index()`.
- `resources/views/dashboard/ketua/index.blade.php` — card tanda tangan + modal kamera + JS (preview, getUserMedia, AJAX submit).
- Tidak ada migrasi baru (kolom `users.tanda_tangan` sudah ada).

## Verifikasi

- Upload file + upload hasil kamera (data URL) via curl/tampilan → file PNG tersimpan, kolom `users.tanda_tangan` terisi.
- Preview formulir sekretaris menampilkan tanda tangan baru.
- `vendor/bin/pint`, `php -l`, render halaman ketua OK.
