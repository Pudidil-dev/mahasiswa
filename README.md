# Data Mahasiswa — UTS Pemrograman Web

Halaman utama: **`users.php`**. Aplikasi PHP + MySQL/MariaDB ini menggunakan tampilan AdminLTE dan dapat dijalankan tanpa Composer atau npm.

Untuk belajar dari awal, baca **[Panduan kode untuk pemula](docs/PANDUAN_KODE.md)**. Panduan menjelaskan urutan membaca file, pencarian realtime, query SQL, pagination, upload foto, dan proses tambah/edit/hapus dengan contoh.

Query daftar, tampilan tabel, pagination, dan modal tambah/edit ditulis langsung di **`users.php`** mengikuti susunan template. Fungsi bantu, koneksi, serta proses simpan/hapus tetap dipanggil dari file lain.

## Fitur

- Foto tampil di sebelah nama mahasiswa. Upload melalui modal **Tambah data**, **Edit**, atau halaman `php/form_upload.php`; jika belum ada foto, gambar bawaan akan tampil. Path foto disimpan dalam tabel **`photo`** dan dihubungkan ke mahasiswa melalui **NIM**.
- Sorting melalui **satu ikon** di header **Nilai Akhir**. Klik bergantian untuk ASC/DESC; ikon berubah menjadi panah naik untuk ASC dan panah turun untuk DESC. Ikon berwarna biru saat sorting aktif. Nilai yang sama diurutkan berdasarkan NIM.
- Pagination dengan pilihan **5, 10, atau 15** mahasiswa per halaman di footer, di sebelah tombol Sebelumnya/Berikutnya dan nomor halaman, serta informasi rentang data.
- Pencarian nama/NIM **realtime**: ketik dan hasil berubah otomatis setelah jeda 0,3 detik. Tabel diperbarui tanpa memuat ulang halaman sehingga kursor tetap di kolom pencarian. Mengosongkan pencarian akan menampilkan data kembali.
- Filter predikat, sorting, dan pilihan ukuran halaman bekerja bersama pencarian. Perubahan pencarian/filter/ukuran kembali ke halaman pertama; pilihan yang sedang aktif disimpan di URL.
- Tambah, edit, dan hapus mahasiswa; nilai huruf serta predikat dihitung otomatis.

Sorting dan pagination dijalankan di MySQL menggunakan `ORDER BY`, `LIMIT`, dan `OFFSET`. Parameter divalidasi, sedangkan teks pencarian dan filter dikirim dengan prepared statement.

## Kebutuhan

- PHP 8.1 atau lebih baru dengan ekstensi `mysqli` dan driver `mysqlnd` (tersedia pada XAMPP PHP 8.2).
- MySQL 5.7+ atau MariaDB 10.4+.
- Apache/Nginx dengan PHP, atau server pengembangan bawaan PHP.
- Folder `uploads/mahasiswa/` dapat ditulis oleh proses PHP. Foto menerima JPG, PNG, atau GIF maksimal **2 MB**; atur `upload_max_filesize = 2M` atau lebih dan `post_max_size = 8M` atau lebih pada `php.ini`.

## Menjalankan di XAMPP

1. Salin seluruh folder proyek ke `C:\xampp\htdocs\mahasiswa`.
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, pilih menu **Import**, lalu jalankan **`database/setup.sql`**. Bisa juga salin isinya ke tab **SQL** dan tekan **Go/Kirim**.
4. Jika ingin data contoh untuk menguji pagination, pilih database `mahasiswa` dan import **`database/seed.sql`**. Tersedia 16 mahasiswa contoh; import ini opsional dan tidak menimpa NIM yang sudah ada.
5. Sesuaikan `php/connection.php` jika kredensial database berbeda dari default berikut:

   | Pengaturan | Default |
   | --- | --- |
   | Host | `localhost` |
   | Port | `3306` |
   | User | `root` |
   | Password | kosong |
   | Database | `mahasiswa` |

6. Buka **`http://localhost/mahasiswa/users.php`**.

## Execute database/setup.sql

Buka terminal PowerShell di **folder utama proyek** yang berisi `users.php` dan folder `database`. Pastikan server MySQL sudah aktif. Pilih perintah sesuai instalasi MySQL yang digunakan.

**Laragon / MySQL 8.4** (sesuai server yang aktif pada lingkungan proyek ini):

```powershell
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root --execute="SOURCE database/setup.sql"
```

**XAMPP**, jika memakai server MySQL/MariaDB bawaan XAMPP:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root --execute="SOURCE database/setup.sql"
```

Kedua contoh memakai akun `root` tanpa password. Jika akun database memiliki password, tambahkan `-p` setelah `-u root`; password akan diminta oleh terminal. Sesuaikan path `mysql.exe` jika lokasi atau versi instalasi berbeda.

Jika perintah `mysql` sudah tersedia di PATH:

```powershell
mysql -u root -p --execute="SOURCE database/setup.sql"
```

Perintah tersebut menjalankan seluruh isi `database/setup.sql`: membuat database `mahasiswa`, tabel `nilaimhs` dan `photo`, serta menyalin path foto versi lama jika tersedia. Skrip boleh dijalankan ulang tanpa menghapus data mahasiswa atau menimpa foto pada tabel baru.

Alternatif import melalui terminal MySQL (gunakan path proyek yang sesuai):

```sql
SOURCE C:/xampp/htdocs/mahasiswa/database/setup.sql;
USE mahasiswa;
SOURCE C:/xampp/htdocs/mahasiswa/database/seed.sql;
```

Jalankan perintah `SOURCE` di prompt klien MySQL, bukan di tab SQL phpMyAdmin. Untuk membuka klien XAMPP lewat PowerShell:

```powershell
& C:\xampp\mysql\bin\mysql.exe -u root -p
```

Jika database lama sudah memiliki tabel `nilaimhs`, jalankan `database/setup.sql` yang sama. Skrip membuat tabel **`photo`** dan menyalin path dari kolom lama `nilaimhs.foto` jika kolom tersebut tersedia. Data mahasiswa dan file gambar tetap dipertahankan. Skrip boleh dijalankan ulang; foto pada tabel baru tidak ditimpa.

Kolom lama `nilaimhs.foto` tetap ada pada database yang pernah memakai versi sebelumnya, tetapi aplikasi sekarang membaca **`photo.namafile`**. Instalasi baru langsung memakai dua tabel tanpa membuat kolom foto pada `nilaimhs`.

## Alternatif server bawaan PHP

Database tetap perlu dijalankan dan diinisialisasi terlebih dahulu. Dari folder utama proyek:

```powershell
& C:\xampp\php\php.exe -S localhost:8000
```

Buka **`http://localhost:8000/users.php`**. Pada sistem dengan PHP di PATH, gunakan `php -S localhost:8000`. Server bawaan ini digunakan untuk pengembangan lokal.

## Deployment ke hosting/server lain

1. Buat database dan user MySQL melalui panel hosting.
2. Pada salinan `database/setup.sql`, sesuaikan nama database pada `CREATE DATABASE` dan `USE`. Jika hosting tidak mengizinkan `CREATE DATABASE`, hapus baris perintah tersebut dan pilih database yang dibuat panel sebelum import.
3. Import skrip SQL tersebut melalui phpMyAdmin/panel hosting.
4. Upload seluruh folder aplikasi, termasuk `assets/vendor/`, `assets/img/`, `css/`, `js/`, `php/`, dan `uploads/`. Pastikan folder `uploads/mahasiswa/` dapat ditulis oleh PHP sesuai pengaturan hosting.
5. Isi kredensial pada nilai default di `php/connection.php`, atau pasang environment variable **`DB_HOST`**, **`DB_PORT`**, **`DB_USER`**, **`DB_PASS`**, dan **`DB_NAME`**. Environment variable mengalahkan nilai default. File `.env` tidak dibaca otomatis.
6. Buka `https://domain-anda/folder-aplikasi/users.php`. Untuk menjadikannya halaman awal folder di Apache, tambahkan `DirectoryIndex users.php` pada `.htaccess` jika didukung hosting.
7. Uji tambah/edit dengan foto, sorting ASC/DESC, dan setiap ukuran halaman. Saat memindahkan aplikasi yang sudah berisi data, pindahkan dump database **dan folder `uploads/mahasiswa/`** agar foto tetap tersedia.

Seluruh aset tampilan disertakan secara lokal. `users.php`, `php/form_upload.php`, `php/upload.php`, dan `php/readphoto.php` memakai **database `mahasiswa` yang sama** atau nama database yang dikonfigurasi. Database `form` dari latihan lama tidak lagi diperlukan. Data pada database `form` tidak dihapus atau dipasangkan otomatis karena belum memiliki relasi NIM.

## Struktur penting

| File/folder | Fungsi |
| --- | --- |
| `users.php` | Query daftar, form pencarian, tabel, pagination, dan modal dalam satu halaman template |
| `php/connection.php` | Konfigurasi koneksi database |
| `php/connectionForm.php` | Memakai koneksi yang sama untuk file form/upload/galeri referensi |
| `js/users.js` | Pencarian realtime, filter, sorting, pagination, dan pengisian modal edit |
| `js/form-mahasiswa.js` | Mengirim form tambah/edit beserta foto ke PHP |
| `css/users.css` | Penyesuaian tata letak dan ukuran foto; komponen lain memakai template |
| `php/insertnilai.php`, `php/editnilai.php`, `php/deletenilai.php` | Proses tambah, edit, dan hapus data beserta foto |
| `php/aksi_nilai.php` | Fungsi parameter URL, tautan pagination, token, pengamanan teks, pesan, dan perhitungan nilai |
| `php/foto_mahasiswa.php` | Validasi, penyimpanan, foto bawaan, dan penghapusan foto |
| `php/form_upload.php` | Form pilih mahasiswa dan unggah foto |
| `php/upload.php` | Menyimpan file serta path pada tabel `photo` |
| `php/readphoto.php` | Galeri foto beserta NIM, nama, path, dan tanggal unggah |
| `database/setup.sql` | Pembuatan tabel `nilaimhs`/`photo` dan migrasi path foto versi lama |
| `database/seed.sql` | 16 data contoh opsional untuk demo |
| `uploads/mahasiswa/` | File foto mahasiswa; path relatif disimpan di kolom `photo.namafile` |
| `docs/PANDUAN_KODE.md` | Penjelasan kode dan contoh alur untuk mahasiswa pemula |

## Tabel database

| Tabel | Kolom | Fungsi |
| --- | --- | --- |
| `nilaimhs` | `nim`, `namalengkap`, `nilaiakhir`, `nilaihuruf`, `predikat`, `created_date`, `modified_date` | Identitas dan nilai mahasiswa |
| `photo` | `id`, `nim`, `namafile`, `tgl_unggah` | Path foto dan mahasiswa pemiliknya |

`nilaimhs.nim` adalah primary key. `photo.nim` adalah foreign key sekaligus unik, sehingga satu mahasiswa memiliki paling banyak satu foto aktif. Mengganti foto memperbarui baris yang sama. `ON DELETE CASCADE` menghapus baris foto saat mahasiswa dihapus; PHP membersihkan file gambarnya.

Untuk melihat pasangan nama dan path foto melalui phpMyAdmin:

```sql
SELECT nilaimhs.nim, nilaimhs.namalengkap, photo.namafile
FROM nilaimhs
LEFT JOIN photo ON photo.nim = nilaimhs.nim;
```

## Pemeriksaan singkat

1. Tanpa data, tabel menampilkan **Belum ada data mahasiswa** dan rentang **0–0**.
2. Dengan 16 data contoh, ukuran 5 menghasilkan 4 halaman, ukuran 10 menghasilkan 2 halaman, dan ukuran 15 menghasilkan 2 halaman.
3. Klik ikon di header Nilai Akhir beberapa kali untuk berganti ASC/DESC dan periksa urutan angka, termasuk setelah berpindah halaman.
4. Tambah mahasiswa dengan foto; pastikan foto tampil di sebelah namanya. Edit tanpa memilih foto untuk mempertahankannya, lalu coba ganti foto.
5. Ketik nama tanpa menekan Enter; tabel berubah otomatis dan kursor tetap di input. Hapus teks untuk melihat daftar kembali.
6. Cari nama dan pilih predikat, kemudian pindah halaman; pilihan tetap tersimpan di URL.
7. Setelah mencari, tekan Edit pada hasilnya; NIM, nama, dan nilai harus sesuai baris yang dipilih.
8. Buka `php/form_upload.php`, pilih mahasiswa, lalu unggah foto. Periksa hasilnya di `php/readphoto.php` dan foto di sebelah nama pada `users.php`.

## Jika ada masalah

| Gejala | Yang perlu diperiksa |
| --- | --- |
| Koneksi database gagal | MySQL aktif; host, port, database, user, dan password di `php/connection.php` sesuai server |
| Tabel `nilaimhs` atau `photo` tidak ditemukan | Import ulang `database/setup.sql` pada database yang dipakai aplikasi |
| Modal atau ikon tidak tampil | Folder `assets/vendor/` ikut disalin dan CSS/JS dapat diakses |
| Pencarian belum otomatis | JavaScript aktif; muat ulang dengan Ctrl+F5 setelah mengganti file JS |
| Muncul pesan data gagal dimuat | Periksa server PHP/koneksi, lalu coba ketik kembali atau buka `users.php` langsung |
| Foto gagal diunggah | JPG/PNG/GIF maksimal 2 MB; batas `php.ini` cukup dan folder foto dapat ditulis |
