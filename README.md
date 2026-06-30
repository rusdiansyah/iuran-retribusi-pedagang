# Sistem Iuran Retribusi Pedagang

Aplikasi berbasis web modern untuk manajemen tagihan dan pembayaran retribusi pedagang pasar/kawasan. Dibangun dengan Laravel 11, Livewire 3, Tailwind CSS, dan SQLite.

## Fitur Utama

- **Dashboard Real-time**: Menampilkan grafik tagihan, pembayaran, dan ringkasan data.
- **Manajemen Multi-Role**: Admin, Staff Penagihan, dan Pedagang.
- **Master Data**: Kelola Lokasi, Jenis, Zonasi, Metode Pembayaran, Pengguna, dan Pengaturan Aplikasi.
- **Manajemen Pedagang**: Data pedagang lengkap dengan perhitungan piutang otomatis.
- **Generate Tagihan**: Pembuatan tagihan bulanan secara massal ke pedagang aktif.
- **Sistem Pembayaran**: Kasir pembayaran dengan pelunasan tagihan secara rinci.
- **Laporan Komprehensif**: Cetak laporan PDF/Print untuk Pedagang, Tagihan, Pembayaran, dan Piutang.

## Teknologi

- PHP 8.3 (atau 8.2 minimum)
- Laravel 11
- Livewire 3 (Volt)
- Tailwind CSS
- Chart.js
- SQLite Database

## Panduan Instalasi

1. **Extract/Clone Project**
   Ekstrak file `Iuran-retribusi-pedagang.zip` ke dalam folder web server Anda (contoh: `htdocs` untuk XAMPP, `www` untuk Laragon, atau folder `public_html` di shared hosting).

2. **Jalankan Terminal/Command Prompt**
   Masuk ke direktori project:
   ```bash
   cd /path/ke/folder/project
   ```

3. **Install Dependencies**
   (Pastikan Anda sudah menginstall Composer)
   ```bash
   composer install
   ```

4. **Konfigurasi Environment**
   Salin `.env.example` menjadi `.env`. Secara default, `.env` sudah dikonfigurasi untuk menggunakan `sqlite`.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Setup Database & Seeding Data Awal**
   Jalankan perintah migrate dan seed untuk membuat tabel dan akun admin default.
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

6. **Build Frontend Asset (Opsional untuk development, sudah dibuild di zip production)**
   ```bash
   npm install
   npm run build
   ```

7. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```
   Akses di browser melalui `http://localhost:8000`.

## Akun Login Default

**Role Admin:**
- **Username**: admin
- **Password**: admin123

## Catatan
Aplikasi ini menggunakan SQLite secara default sehingga sangat mudah untuk di-deploy ke shared hosting. Database tersimpan dalam file `database/database.sqlite`.

---
*Developed with Laravel 11 & Tailwind CSS.*
# iuran-retribusi-pedagang
