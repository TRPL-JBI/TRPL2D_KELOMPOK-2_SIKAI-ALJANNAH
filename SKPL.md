# SKPL — Backup Database Harian (NF-04)

## Ringkasan

Sistem backup otomatis menggunakan `spatie/laravel-backup` v10.3. Backup mencakup database SQLite + seluruh file proyek (kecuali vendor, node_modules, logs). Jadwal harian pukul 02:00 WIB, cleanup pukul 01:00 WIB. Retensi 7 hari.

## Lokasi Backup

```
storage/app/private/Laravel/    ← file zip backup
storage/app/backup-temp/        ← file sementara saat proses
```

Format nama: `YYYY-MM-DD-HH-mm-ss.zip`

## Jadwal Otomatis

| Waktu  | Command             | Keterangan                    |
| ------ | ------------------- | ----------------------------- |
| 01:00  | `backup:clean`      | Hapus backup > 7 hari         |
| 02:00  | `backup:run`        | Buat backup baru              |

Cron server harus menjalankan per menit:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Konfigurasi

File: `config/backup.php`

| Setting                | Nilai                                         |
| ---------------------- | --------------------------------------------- |
| Nama aplikasi          | Al-Jannah (dari `APP_NAME`)                   |
| Database               | `env('DB_CONNECTION', 'sqlite')`               |
| Disk penyimpanan       | `local` → `storage/app/private`               |
| Retensi harian         | 7 hari                                        |
| Retensi mingguan       | 8 minggu                                      |
| Retensi bulanan        | 4 bulan                                       |
| Retensi tahunan        | 2 tahun                                       |
| Kompresi               | ZipArchive::CM_DEFAULT, level 9               |
| Encrypt                | AES-256 (default)                             |
| Notifikasi mail        | Disabled (menggunakan WhatsApp)               |

### Direktori yang di-include

- `base_path()` — seluruh project root
- `storage_path()` — semua file di storage

### Direktori yang di-exclude

- `vendor/`
- `node_modules/`
- `.git/`
- `.Panduan-AI/`
- `.deepseek/`
- `storage/framework/`
- `storage/logs/`
- `storage/app/backups/`
- `storage/app/backup-temp/`

## Notifikasi WhatsApp

Backup notifikasi dikirim ke Super Admin via WhatsApp menggunakan `WhatsAppService` yang sudah ada.

**Setup:**
1. Tambahkan nomor WA Super Admin di `.env`:
   ```
   BACKUP_WHATSAPP_ADMIN_PHONE=628xxxxxxxxxx
   ```
2. Format nomor: `628xxxxxxxxxx` (tanpa `+` prefix)

**Event yang di-notifikasi:**
- ✅ `BackupWasSuccessful` — notifikasi sukses
- ❌ `BackupHasFailed` — notifikasi gagal + pesan error

Listener: `app/Listeners/BackupNotificationListener.php`

## Perintah Manual

```bash
# Jalankan backup sekarang
php artisan backup:run

# Bersihkan backup lama
php artisan backup:clean

# Cek status backup (monitoring)
php artisan backup:monitor

# Lihat daftar backup
php artisan backup:list
```

## Restore Backup

### SQLite

```bash
# 1. Ekstrak zip backup
unzip storage/app/private/Laravel/2026-09-22-05-33-28.zip -d /tmp/restore

# 2. Copy database
cp /tmp/restore/database/database.sqlite database/database.sqlite

# 3. Copy file storage (jika diperlukan)
cp -r /tmp/restore/storage/app/private/* storage/app/private/
```

### MySQL/PostgreSQL (jika migrasi ke DB lain)

```bash
# 1. Ekstrak zip backup
unzip storage/app/private/Laravel/2026-09-22-05-33-28.zip -d /tmp/restore

# 2. Import dump SQL
mysql -u root -p database_name < /tmp/restore/database/database-dump.sql

# 3. Copy file storage
cp -r /tmp/restore/storage/app/private/* storage/app/private/
```

## Troubleshooting

| Masalah | Solusi |
| ------- | ------ |
| Backup timeout | Kurangi file di exclude atau naikkan timeout PHP |
| WhatsApp notifikasi gagal | Cek `BACKUP_WHATSAPP_ADMIN_PHONE` di .env, pastikan WhatsApp sidecar running |
| File backup corrupt | Jalankan `php artisan backup:run --verify` |
| Cleanup tidak jalan | Cek cron server, pastikan `schedule:run` berjalan tiap menit |

## File Terkait

| File | Fungsi |
| ---- | ------ |
| `config/backup.php` | Konfigurasi backup spatie |
| `routes/console.php` | Jadwal backup & cleanup |
| `app/Listeners/BackupNotificationListener.php` | Notifikasi WhatsApp |
| `app/Services/WhatsAppService.php` | Service WhatsApp (existing) |
