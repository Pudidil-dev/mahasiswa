# Data Mahasiswa - UTS Pemrograman Web

Aplikasi PHP + MySQL/MariaDB dengan template AdminLTE. Halaman utama: `users.php`. Tidak memerlukan Composer atau npm.

**GitHub (Public):** [Pudidil-dev/mahasiswa](https://github.com/Pudidil-dev/mahasiswa)

## 1. Persiapan lokal

Gunakan PHP 8.1+ dengan ekstensi `mysqli` dan driver `mysqlnd`, serta MySQL/MariaDB.

| Pilihan | Lokasi folder proyek | Jalankan |
| --- | --- | --- |
| XAMPP | `C:\xampp\htdocs\mahasiswa` | Apache dan MySQL dari panel XAMPP |
| Laragon | `C:\laragon\www\mahasiswa` | Klik **Start All** |

Sesuaikan path jika lokasi instalasi berbeda. Jika memakai server bawaan PHP, cukup aktifkan MySQL.

## 2. Import / migrasi database

**Instalasi baru:** impor [`database/mahasiswa.sql`](database/mahasiswa.sql). File ekspor ini berisi struktur tabel dan **9 data mahasiswa asli sesuai database lokal**, termasuk NIM, nama, nilai, dan path foto yang tersimpan.

- **phpMyAdmin:** buka `http://localhost/phpmyadmin`, pilih **Import**, lalu pilih `database/mahasiswa.sql`. Database `mahasiswa` dibuat otomatis.
- **XAMPP, melalui PowerShell** dari folder utama proyek:

  ```powershell
  & "C:\xampp\mysql\bin\mysql.exe" -u root --execute="SOURCE database/mahasiswa.sql"
  ```

- **Laragon:** buka **Terminal** dari panel Laragon, lalu jalankan:

  ```shell
  cd C:\laragon\www\mahasiswa
  mysql -u root --execute="SOURCE database/mahasiswa.sql"
  ```

Tambahkan `-p` jika akun database memakai password. Di PowerShell biasa, jika `mysql` tidak dikenali, gunakan path `C:\laragon\bin\mysql\<versi>\bin\mysql.exe` sesuai instalasi.

**Sudah memiliki database versi lama:** jalankan [`database/setup.sql`](database/setup.sql), lalu [`database/seed.sql`](database/seed.sql) untuk menambahkan data dari snapshot. Gunakan perintah di atas dengan mengganti nama file; untuk seed, pilih database `mahasiswa`:

```shell
mysql -u root --execute="SOURCE database/setup.sql"
mysql -u root mahasiswa --execute="SOURCE database/seed.sql"
```

Pada XAMPP gunakan path lengkap `mysql.exe` seperti contoh PowerShell. Setup mengganti nama tabel `nilaimhs` menjadi **`nilaimhsw`**, menambahkan **`file_path`**, dan menyalin foto dari `photo.namafile` atau kolom `foto` lama. Data lama dipertahankan; setup dan seed dapat diulang tanpa menimpa data yang sudah terisi.

Untuk membuat database kosong, cukup jalankan `setup.sql` tanpa seed.

## 3. Konfigurasi koneksi

Periksa [`php/connection.php`](php/connection.php). Nilai default:

| Host | Port | User | Password | Database |
| --- | --- | --- | --- | --- |
| `localhost` | `3306` | `root` | kosong | `mahasiswa` |

Jika memakai nama database lain, sesuaikan juga `CREATE DATABASE` dan `USE` pada file SQL sebelum import.

## 4. Jalankan aplikasi

- **XAMPP / Laragon (Apache):** buka `http://localhost/mahasiswa/users.php`.
- **Laragon Auto Virtual Hosts:** klik **Reload**, lalu buka `http://mahasiswa.test/users.php` jika fitur tersebut aktif dan memakai akhiran `.test`.
- **Server bawaan PHP:** dari folder utama proyek, jalankan:

  ```powershell
  php -S localhost:8000
  ```

  Jika PHP belum ada di PATH, gunakan `& "C:\xampp\php\php.exe" -S localhost:8000`. Buka `http://localhost:8000/users.php`. MySQL harus tetap aktif.

## 5. Coba fitur UTS

1. Foto kecil tampil di sebelah nama, dalam kolom **Nama Mahasiswa**. Path tersimpan di **`nilaimhsw.file_path`**.
2. Ketik **Ferry** untuk mencari nama, lalu coba dropdown **Predikat**.
3. Klik panah pada **Nilai Akhir** untuk berganti ASC/DESC.
4. Pilih **5, 10, atau 15** per halaman; coba **First Page, Previous, Next, Last Page**. Dengan 9 data snapshot tersedia 2, 1, dan 1 halaman. Untuk perpindahan halaman pada limit 10 dan 15, diperlukan minimal 16 data asli.
5. Coba tambah/edit dengan foto, lalu hapus data uji. Snapshot memakai 3 path foto asli; 6 mahasiswa yang belum memiliki foto memakai gambar bawaan.

Pengujian otomatis: `python tests/check_uts.py` (Python, PHP, dan `mysql` di PATH, misalnya melalui Terminal Laragon). Jika perlu, tambahkan `--mysql "C:\xampp\mysql\bin\mysql.exe"` dan `--php "C:\xampp\php\php.exe"`. Tes memakai database sementara yang dibersihkan setelah selesai.

## Jika ada masalah

- **Koneksi gagal:** pastikan MySQL aktif dan konfigurasi koneksi sesuai.
- **Tabel/kolom belum ditemukan:** jalankan `database/setup.sql`.
- **Foto gagal diunggah:** JPG/PNG/GIF maksimal 2 MB; folder `uploads/mahasiswa/` harus dapat ditulis PHP. Atur `upload_max_filesize` minimal `2M` dan `post_max_size` minimal `8M` di `php.ini`.
- **Memindahkan data sendiri:** ekspor tabel `nilaimhsw` dan salin `uploads/mahasiswa/`. File `database/mahasiswa.sql` berisi snapshot 9 data asli; path yang kosong tetap `NULL`.

Penjelasan kode: [Panduan kode untuk pemula](docs/PANDUAN_KODE.md).
