# Data Mahasiswa

Aplikasi PHP + MySQL/MariaDB untuk mengelola data, nilai, dan foto mahasiswa. Halaman utama: `users.php`. Tidak memerlukan Composer atau npm.

## 1. Persiapan lokal

- Gunakan XAMPP atau Laragon dengan PHP 8.1+ (ekstensi `mysqli` dan driver `mysqlnd`) serta MySQL/MariaDB.
- Untuk XAMPP, salin proyek ke `C:\xampp\htdocs\mahasiswa`.
- Untuk Laragon, salin proyek ke `C:\laragon\www\mahasiswa` (sesuaikan jika lokasi instalasi berbeda).
- **XAMPP:** jalankan **Apache** dan **MySQL** melalui panel XAMPP.
- **Laragon:** klik **Start All** untuk menjalankan web server dan MySQL. Jika memakai server bawaan PHP, cukup aktifkan MySQL.

## 2. Setup / migrasi database

**Melalui phpMyAdmin (jika tersedia):**

1. Buka `http://localhost/phpmyadmin`.
2. Pilih **Import**, lalu impor [`database/setup.sql`](database/setup.sql). Skrip otomatis membuat database `mahasiswa` dan tabel yang diperlukan.
3. Opsional: pilih database `mahasiswa`, lalu impor [`database/seed.sql`](database/seed.sql) untuk menambahkan 16 data contoh.

**Alternatif melalui PowerShell** dari folder utama proyek:

```powershell
# XAMPP
& "C:\xampp\mysql\bin\mysql.exe" -u root --execute="SOURCE database/setup.sql"

# Opsional: tambahkan data contoh
& "C:\xampp\mysql\bin\mysql.exe" -u root mahasiswa --execute="SOURCE database/seed.sql"
```

**Laragon:** buka **Terminal** dari panel Laragon, lalu masuk ke folder proyek dan jalankan:

```shell
cd C:\laragon\www\mahasiswa
mysql -u root --execute="SOURCE database/setup.sql"
```

Opsional, tambahkan data contoh:

```shell
mysql -u root mahasiswa --execute="SOURCE database/seed.sql"
```

Terminal Laragon menyediakan perintah `mysql` tanpa perlu menulis path lengkap. Jika memakai PowerShell biasa dan `mysql` tidak dikenali, gunakan path lengkap `mysql.exe` di folder `C:\laragon\bin\mysql\<versi>\bin\` sesuai instalasi.

Tambahkan `-p` jika akun database memakai password. Jika phpMyAdmin tidak tersedia di Laragon, gunakan cara terminal di atas.

**Database versi lama:** jalankan `setup.sql` yang sama. Skrip menyalin path foto dari `nilaimhs.foto` ke tabel `photo` jika kolom lama tersedia. Skrip dapat dijalankan ulang tanpa menghapus data mahasiswa atau menimpa foto yang sudah ada di tabel `photo`.

## 3. Konfigurasi koneksi

Sesuaikan [`php/connection.php`](php/connection.php) dengan database lokal:

| Pengaturan | Nilai default |
| --- | --- |
| Host | `localhost` |
| Port | `3306` |
| User | `root` |
| Password | kosong |
| Database | `mahasiswa` |

Jika memakai nama database lain, sesuaikan juga `CREATE DATABASE` dan `USE` pada `database/setup.sql` sebelum import.

## 4. Jalankan aplikasi

**XAMPP (Apache):** buka `http://localhost/mahasiswa/users.php`.

**Laragon (Apache):** buka `http://localhost/mahasiswa/users.php`. Jika fitur **Auto Virtual Hosts** aktif, klik **Reload** pada Laragon lalu buka `http://mahasiswa.test/users.php` (sesuaikan jika akhiran domain diubah).

**Alternatif server bawaan PHP:** jalankan dari folder utama proyek:

```powershell
& "C:\xampp\php\php.exe" -S localhost:8000
```

Jika PHP sudah ada di PATH, gunakan `php -S localhost:8000`. Buka `http://localhost:8000/users.php`. MySQL harus tetap aktif.

Coba tambah, edit, dan hapus data untuk memastikan aplikasi terhubung ke database.

## Jika ada masalah

- **Koneksi gagal:** pastikan MySQL aktif dan konfigurasi `php/connection.php` sesuai.
- **Tabel belum ditemukan:** impor `database/setup.sql`.
- **Foto gagal diunggah:** gunakan JPG/PNG/GIF maksimal 2 MB dan pastikan `uploads/mahasiswa/` dapat ditulis oleh PHP. Atur `upload_max_filesize` minimal `2M` dan `post_max_size` minimal `8M` di `php.ini`.
- **Memindahkan proyek berisi data:** salin dump database dan folder `uploads/mahasiswa/` agar foto tetap tersedia.

Penjelasan kode: [Panduan kode untuk pemula](docs/PANDUAN_KODE.md).
