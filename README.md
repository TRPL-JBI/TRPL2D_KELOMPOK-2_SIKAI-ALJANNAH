# Rancang Bangun Website Manajemen Keuangan, Anggota, Aset, dan Pusat Informasi RKM Al-Jannah Banyuwangi

## Kelompok GeoDev Creator

| No  | Nama                   |
| --- | ---------------------- |
| 1   | Rusydi Jabir Al-awfa   |
| 2   | M. Rakha Widya Ardhana |
| 3   | Alfin Nazatil Kirom    |
| 4   | Leni Ayu Pratiwi       |
| 5   | Intan Rahma Safira     |

## Deskripsi

Sistem informasi organisasi RKM (Rukun Kematian) Al-Jannah Banyuwangi berbasis web. Aplikasi ini mengelola data anggota, keuangan (iuran, pemasukan, pengeluaran), aset/inventaris, logistik, serta konten website organisasi. Mendukung multi-role (Ketua, Sekretaris, Bendahara, Logistik, Admin Web, Anggota, Super Admin) dengan dashboard dan akses yang berbeda sesuai jabatan.

## Fitur

- **Manajemen Anggota** — Pendaftaran calon anggota, verifikasi oleh sekretaris, data anggota aktif/nonaktif, data keluarga/tanggungan, import data Excel
- **Manajemen Keuangan** — Pencatatan pemasukan & pengeluaran, iuran tahunan anggota, rekapitulasi & laporan keuangan, export laporan
- **Manajemen Aset & Logistik** — Inventaris barang, stok minimal (early warning), aset kendaraan, riwayat masuk/keluar barang
- **CMS Website** — Artikel/berita, halaman statis, kategori, menu navigasi, pengumuman, media (gambar/file)
- **Multi-Role Dashboard** — Dashboard khusus per role dengan widget dan grafik (Chart.js)
- **Sistem Autentikasi** — Login dengan rate limiting, role-based middleware, akses anggota via kode akses
- **Audit & Log** — Log aktivitas untuk ketua, log superadmin untuk pengawasan sistem
- **Manajemen Pengguna** — CRUD user, reset password manual oleh super admin
- **Persetujuan Ketua** — Permintaan izin edit data yang memerlukan approval ketua

## Tech Stack

| Bagian           | Teknologi                                                       |
| ---------------- | --------------------------------------------------------------- |
| Backend          | Laravel 12, PHP ^8.2                                            |
| Frontend         | Blade Templating, Tailwind CSS v4, Vite 7                       |
| Admin Panel      | Filament 5.3                                                    |
| Database         | SQLite (default),支持 MySQL / MariaDB / PostgreSQL / SQL Server |
| Editor           | Filament RichEditor + Livewire                                  |
| Media            | Spatie Laravel MediaLibrary                                     |
| Charts           | Chart.js 4.5                                                    |
| Export           | OpenSpout                                                       |
| Testing          | PHPUnit 11                                                      |

## Panduan Instalasi

### Prasyarat

- PHP ^8.2
- Composer
- Node.js & npm
- SQLite (default) / MySQL / PostgreSQL

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/username/al-jannah.git
cd al-jannah

# 2. Install dependency PHP
composer install

# 3. Install dependency frontend
npm install

# 4. Salin file env
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
#    Default menggunakan SQLite (buat file database/database.sqlite)
#    Atau ubah ke MySQL sesuai kebutuhan

# 7. Jalankan migrasi dan seeder
php artisan migrate --seed

# 8. Build asset frontend
npm run build

# 9. Jalankan server
php artisan serve

# 10. Buka di browser: http://localhost:8000
```

## Akun Demo

Semua akun menggunakan password: `password`

| Role         | Email                         |
| ------------ | ----------------------------- |
| Ketua        | ketua@aljannah.or.id          |
| Super Admin  | superadmin@aljannah.or.id     |
| Bendahara    | bendahara@aljannah.or.id      |
| Sekretaris   | sekretaris@aljannah.or.id     |
| Logistik     | logistik@aljannah.or.id       |
| Admin Web    | adminweb@aljannah.or.id       |

## Struktur Folder

```
al-jannah/
├── app/
│   ├── Console/Commands/         → Artisan commands
│   ├── Filament/                 → Filament admin panel customization
│   ├── Http/Controllers/         → Controllers per role & modul
│   ├── Livewire/                 → Livewire components
│   ├── Models/                   → Eloquent models (24 model)
│   ├── Providers/                → Service providers
│   ├── Services/                 → Business logic services
│   └── View/Components/          → Blade components
├── bootstrap/                    → Laravel bootstrap
├── config/                       → Konfigurasi aplikasi
├── database/
│   ├── factories/                → Model factories
│   ├── migrations/               → Migrasi database (35 file)
│   └── seeders/                  → Data awal & demo (16 seeder)
├── public/                       → Entry point web
├── resources/
│   ├── css/                      → Stylesheet
│   ├── js/                       → JavaScript
│   └── views/                    → Blade templates
│       ├── layouts/              → Layout utama (app, public)
│       ├── auth/                 → Halaman login, register
│       ├── public/               → Halaman publik (beranda, profil)
│       ├── dashboard/            → Dashboard per role
│       ├── anggota/              → Manajemen anggota
│       ├── keuangan/             → Pemasukan, pengeluaran, iuran
│       ├── aset/                 → Aset & stok barang
│       ├── konten/               → Artikel, kategori, pengumuman
│       ├── audit/                → Log aktivitas
│       ├── user/                 → Manajemen user
│       ├── laporan/              → Download laporan
│       └── partials/             → Komponen reusable (sidebar, navbar)
├── routes/
│   ├── web.php                   → Route web (188 baris)
│   └── console.php               → Route console
├── storage/                      → Log, cache, upload
├── tests/                        → PHPUnit tests
├── composer.json
├── package.json
└── vite.config.js
```
