# Panduan membaca kode Data Mahasiswa

Panduan ini untuk mahasiswa yang sedang belajar PHP, JavaScript, dan MySQL. Jalankan aplikasi mengikuti [README](../README.md) terlebih dahulu, lalu baca file sambil mencoba fiturnya.

## 1. Mulai dari file mana?

Baca sesuai urutan berikut. Aset template dalam `assets/vendor/`, `css/adminlte.css`, dan `js/adminlte.js` tidak perlu dipelajari dahulu untuk memahami fitur mahasiswa.

| Urutan | File | Pertanyaan yang dijawab |
| --- | --- | --- |
| 1 | `users.php` | Bagaimana query, pencarian, tabel, pagination, dan modal disusun dalam satu template? |
| 2 | `php/connection.php` | Bagaimana PHP terhubung ke database? |
| 3 | `php/aksi_nilai.php`, `php/foto_mahasiswa.php` | Fungsi apa yang dipanggil dari halaman utama dan proses form? |
| 4 | `js/users.js` | Bagaimana tabel diperbarui saat pengguna mengetik? |
| 5 | `js/form-mahasiswa.js` | Bagaimana form dikirim ke PHP? |
| 6 | `php/insertnilai.php`, `php/editnilai.php`, `php/deletenilai.php` | Bagaimana data disimpan, diubah, dan dihapus? |

`users.php` mengikuti susunan template: bagian PHP untuk query ada di atas, lalu HTML header, pencarian, tabel, pagination, modal, dan pemanggilan JavaScript. Fungsi bantu tetap berada di file lain dan dipanggil menggunakan `require_once`.

## 2. Saat halaman pertama dibuka

1. Browser meminta `users.php`.
2. `aksi_nilai.php` membuka sesi dan menyiapkan token untuk form.
3. `connection.php` membuat koneksi MySQL dalam variabel `$conn`.
4. Bagian PHP di atas `users.php` membaca URL, membaca data dan path foto dari `nilaimhsw`, lalu menyimpan hasil ke array `$daftarMahasiswa`.
5. Koneksi ditutup karena hasil query sudah ada di array.
6. Bagian HTML pada file yang sama menampilkan halaman, termasuk tabel dan form modal.
7. Browser menjalankan JavaScript untuk mengaktifkan pencarian, dropdown, dan modal.

`require_once` memuat file PHP sekali saja. `__DIR__` adalah folder tempat file PHP berada, sehingga path include tetap benar ketika aplikasi dipindah.

## 3. Cara pencarian realtime bekerja

Kolom pencarian mempunyai ID `user-search`. JavaScript mendengarkan event `input`, yaitu kejadian saat teks berubah.

Contoh ketika pengguna mengetik **Andi**:

1. Ketikan pertama memulai timer.
2. Ketikan berikutnya membatalkan timer sebelumnya.
3. Setelah pengguna berhenti mengetik selama 300 milidetik, fungsi `muatDaftar()` dijalankan.
4. `fetch()` meminta hasil pencarian ke PHP.
5. PHP mencari nama/NIM pada seluruh tabel database, lalu mengambil halaman pertama hasilnya.
6. HTML tabel dan pagination diganti di dalam `#hasil-mahasiswa`.

Jeda ini disebut **debounce**. Tujuannya agar aplikasi tidak meminta data untuk setiap huruf yang diketik dengan cepat.

```javascript
const jedaPencarian = 300;
```

Nilai `300` berarti 0,3 detik. Untuk mengubah jeda, ubah nilai tersebut di `js/users.js`.

Contoh URL permintaan:

```text
users.php?q=Andi&predikat=&sort=asc&size=5&page=1&ajax=1
```

`ajax=1` membuat `users.php` mengirim **hanya HTML tabel dan footer**. Input pencarian berada di luar bagian yang diganti, sehingga fokus dan posisi kursor tetap terjaga.

Variabel `$permintaanAjax` mengatur respons dari template yang sama. Pada permintaan biasa, header dan form ditampilkan lebih dahulu. Pada permintaan AJAX, bagian itu dilewati; setelah tabel dan pagination dikirim, `exit` menghentikan proses sebelum modal dan penutup halaman. Kode tabel hanya ditulis satu kali dalam `users.php`.

`AbortController` membatalkan permintaan yang sudah tidak diperlukan. Pemeriksaan `permintaan.signal.aborted` juga mencegah hasil lama digunakan setelah pengguna mengetik pencarian baru.

Saat berhasil, `history.replaceState()` menyimpan pilihan di alamat browser tanpa memuat ulang halaman. Parameter `ajax` tidak ikut disimpan. URL tersebut bisa disalin atau dimuat ulang.

Menekan Enter langsung mencari tanpa menunggu timer. Mengosongkan input juga memicu pencarian dan mengembalikan daftar sesuai filter yang masih aktif.

## 4. Parameter URL dan variabel PHP

| Parameter | Contoh | Variabel | Keterangan |
| --- | --- | --- | --- |
| `q` | `Andi` | `$pencarian` | Potongan nama atau NIM; kosong berarti semua data |
| `predikat` | `Memuaskan` | `$filterPredikat` | Hanya menerima salah satu pilihan predikat |
| `sort` | `asc` | `$urutan` | `asc` atau `desc`; kosong berarti urutan NIM |
| `size` | `5` | `$ukuran` | Hanya 5, 10, atau 15 |
| `page` | `2` | `$halaman` | Nomor halaman; minimal 1 |
| `ajax` | `1` | Dibaca di `users.php` | Meminta tabel saja untuk pembaruan realtime |

`ambilParameterTeks()` dalam `php/aksi_nilai.php` membaca input URL sebagai string dan menghapus spasi di awal/akhir. Input berbentuk array tidak digunakan. `urlDaftarMahasiswa()` pada file yang sama menyusun tautan halaman/sorting dengan pilihan yang sedang aktif.

Nilai dari browser tetap diperiksa oleh PHP. Mengubah URL menjadi `size=1000` tidak membuat server mengirim 1000 data; aplikasi memakai ukuran default 5.

## 5. Memahami query pencarian

Bagian PHP di atas `users.php` menjalankan dua query:

1. `SELECT COUNT(*)` menghitung seluruh hasil yang cocok.
2. `SELECT * ... LIMIT ... OFFSET ...` mengambil baris pada halaman aktif.

Keduanya memakai filter yang sama, agar jumlah data dan isi halaman sesuai.

```sql
WHERE (? = '' OR LOCATE(?, nilaimhsw.namalengkap) > 0 OR LOCATE(?, nilaimhsw.nim) > 0)
  AND (? = '' OR nilaimhsw.predikat = ?)
```

`LOCATE('Andi', namalengkap)` mencari posisi teks Andi pada nama. Posisi lebih dari 0 berarti cocok. Jika pencarian kosong, bagian `? = ''` membuat semua nama/NIM dapat ditampilkan.

Tanda `?` adalah tempat untuk nilai input. `mysqli_stmt_bind_param()` mengisinya tanpa menggabungkan input pengguna langsung ke SQL.

| Huruf pada bind_param | Arti | Contoh |
| --- | --- | --- |
| `s` | String/teks | Nama, NIM, predikat |
| `i` | Integer/bilangan bulat | LIMIT dan OFFSET |
| `d` | Bilangan desimal | Nilai akhir saat INSERT/UPDATE |

Query daftar memakai `sssssii`: lima parameter teks untuk filter dan dua bilangan bulat untuk batas data.

## 6. Sorting dengan satu ikon

| Keadaan saat ini | Ikon | Hasil klik berikutnya |
| --- | --- | --- |
| Belum memilih sorting nilai | Panah naik abu-abu | ASC |
| ASC | Panah naik biru | DESC |
| DESC | Panah turun biru | ASC |

PHP menentukan `$urutanBerikut`, `$ikonUrutan`, dan `$statusUrutan` sebelum HTML ditampilkan. JavaScript membaca atribut `data-urutan` saat ikon diklik.

```sql
ORDER BY nilaimhsw.nilaiakhir ASC, nilaimhsw.nim ASC
```

ASC mengurutkan nilai terkecil lebih dahulu; DESC mengurutkan nilai terbesar lebih dahulu. NIM menjadi urutan kedua ketika nilainya sama. Kolom `nilaiakhir` bertipe `DECIMAL`, sehingga urutannya berdasarkan angka.

Pilihan pencarian, predikat, dan ukuran halaman tetap dibawa saat sorting berganti.

## 7. Pagination dengan contoh angka

Misalnya ada **16** hasil pencarian dan ukuran halaman **5**:

```text
jumlah halaman = ceil(16 / 5) = 4
offset halaman 1 = (1 - 1) x 5 = 0
offset halaman 2 = (2 - 1) x 5 = 5
offset halaman 3 = (3 - 1) x 5 = 10
offset halaman 4 = (4 - 1) x 5 = 15
```

Halaman ketiga memakai batas setara `LIMIT 5 OFFSET 10`: melewati 10 baris pertama, lalu mengambil paling banyak 5 baris.

`ceil()` membulatkan ke atas. `max(1, ...)` memastikan perhitungan tetap memiliki satu halaman ketika tabel kosong. Nomor halaman terlalu besar dibatasi ke halaman terakhir.

Dropdown footer mempunyai `form="filter-mahasiswa"`. Atribut itu menghubungkan dropdown ke form header meskipun letaknya berbeda. `FormData` tetap membaca nilai ukuran halaman.

Perubahan pencarian, predikat, atau ukuran halaman memulai hasil dari halaman 1. Tombol nomor halaman mempertahankan filter yang aktif.

## 8. Tombol Edit setelah pencarian

Setelah pencarian, baris tabel lama diganti dengan HTML baru. Listener pada tombol lama ikut hilang.

Karena itu, `js/users.js` memasang listener pada pembungkus tetap `#hasil-mahasiswa`. Klik dari tombol baru diteruskan ke pembungkus tersebut. Cara ini disebut **event delegation**.

Saat tombol Edit dipilih, `isiFormEdit()` menyalin `data-nim`, `data-nama`, `data-nilai`, dan `data-token` ke input modal. Modal berada di luar bagian yang diganti, sehingga formnya tetap tersedia.

## 9. Alur tambah, edit, dan hapus

**Tambah:** isi modal → `js/form-mahasiswa.js` mengirim POST → `php/insertnilai.php` memvalidasi input → hitung huruf/predikat → simpan file jika dipilih → INSERT mahasiswa ke `nilaimhsw` dan simpan path pada `file_path` → kembali ke `users.php`.

**Edit:** pilih Edit → data masuk ke modal → POST ke `php/editnilai.php` → validasi token/input → hitung ulang huruf/predikat → UPDATE → kirim JSON sukses → browser memuat ulang daftar dengan filter saat ini.

**Hapus:** tekan Hapus dan konfirmasi → POST ke `php/deletenilai.php` → validasi token/NIM → DELETE → hapus file foto terkait → kembali ke daftar.

Tambah/edit memakai transaksi ketika menyimpan data mahasiswa dan foto. `mysqli_begin_transaction()` memulai proses bersama, `mysqli_commit()` menyetujui hasilnya, dan `mysqli_rollback()` membatalkan perubahan jika salah satu query gagal. Foto baru juga dibersihkan jika penyimpanannya gagal.

Form tambah/edit menggunakan `enctype="multipart/form-data"`. `new FormData(form)` membawa teks dan file sekaligus. Browser mengatur format kiriman file secara otomatis.

NIM disimpan sebagai teks agar panjang dan angka awal tetap terjaga. NIM harus 10 angka. Nama wajib diisi, maksimal 100 karakter. Nilai dibatasi 0 sampai 100. NIM yang sudah terdaftar ditolak saat tambah.

## 10. Huruf dan predikat dihitung di satu tempat

Fungsi `hitungHasilNilai()` berada di `php/aksi_nilai.php` dan dipakai oleh tambah serta edit.

| Rentang nilai dengan 2 desimal | Huruf | Predikat |
| --- | --- | --- |
| 0–34,50 | E | Tidak Lulus |
| 34,51–50,50 | D | Kurang |
| 50,51–60,50 | C | Cukup |
| 60,51–80,50 | B | Memuaskan |
| 80,51–100 | A | Sangat Memuaskan |

```php
$hasilNilai = hitungHasilNilai(87.90);
$huruf = $hasilNilai['huruf'];       // A
$predikat = $hasilNilai['predikat']; // Sangat Memuaskan
```

Jika aturan penilaian berubah, ubah fungsi itu agar tambah dan edit selalu memakai aturan yang sama.

## 11. Foto mahasiswa

| Fungsi dalam php/foto_mahasiswa.php | Kegunaan |
| --- | --- |
| `unggahFotoMahasiswa()` | Memeriksa dan menyimpan foto; mengembalikan path atau NULL jika tidak memilih file |
| `simpanPathFotoMahasiswa()` | Memperbarui `nilaimhsw.file_path` berdasarkan NIM |
| `urlFotoMahasiswa()` | Memberikan path foto yang tersedia atau gambar bawaan |
| `hapusFotoMahasiswa()` | Menghapus foto milik aplikasi saat diganti/dihapus |
| `pathFotoMahasiswa()` | Memastikan path sesuai pola folder foto mahasiswa |

Foto menerima JPG, PNG, atau GIF maksimal 2 MB. PHP memeriksa isi gambar dengan `getimagesize()`, bukan hanya nama ekstensi.

File unggahan diberi nama acak dan disimpan dalam `uploads/mahasiswa/`. Database menyimpan path relatifnya pada kolom **`nilaimhsw.file_path`**, bukan isi file gambar. Foto kecil berukuran 44 x 44 piksel ditampilkan pada kolom yang sama dengan Nama Mahasiswa.

Query daftar membaca `nilaimhsw.file_path AS foto`, sehingga tampilan dapat membaca `$mahasiswa['foto']`. Data contoh memakai avatar yang sudah tersedia pada `assets/img/`. Avatar bersama tidak dihapus saat mahasiswa dihapus atau fotonya diganti.

Untuk database versi sebelumnya, `database/setup.sql` mengganti nama tabel `nilaimhs` menjadi `nilaimhsw`, menambahkan `file_path`, lalu menyalin path dari `photo.namafile` atau kolom `foto` lama. Data mahasiswa, file foto, dan tabel lama lainnya dipertahankan. Path yang sudah terisi tidak ditimpa saat migrasi diulang. Aplikasi selanjutnya membaca dan menulis `nilaimhsw.file_path`.

Edit tanpa file mempertahankan foto lama. Ketika mengganti foto, foto lama dihapus setelah UPDATE berhasil. Jika penyimpanan database gagal, foto baru dibersihkan.

## 12. Teks HTML dan token form

Nama ditampilkan melalui `escapeNilai()`, yang menggunakan `htmlspecialchars()`. Teks seperti `<script>` akan tampil sebagai tulisan, bukan dijalankan browser. Data disimpan sebagai teks biasa lalu diamankan ketika ditampilkan.

Token sesi disimpan pada `$_SESSION['token_aksi']`, dimasukkan ke form tersembunyi, lalu diperiksa oleh `cekTokenAksi()` saat POST. Token digunakan oleh tambah/edit/hapus; token bukan sistem login.

`pesanAksi()` mengirim status HTTP dan pesan ketika validasi gagal. Contohnya 422 untuk input salah, 409 untuk NIM duplikat, dan 403 untuk token tidak sesuai.

## 13. Bagian yang sering diubah saat belajar

| Ingin mengubah | Lokasi |
| --- | --- |
| Judul dan form pencarian | `users.php` |
| Jeda pencarian | `jedaPencarian` di `js/users.js` |
| Kolom atau isi tabel | Bagian `<table>` pada `users.php` |
| Pilihan ukuran halaman | Validasi `$ukuran` dan dropdown footer `[5, 10, 15]` pada `users.php` |
| Query pencarian/sorting | Bagian PHP paling atas pada `users.php` |
| Input form tambah/edit | Bagian modal pada `users.php`, lalu sesuaikan proses PHP terkait |
| Batas huruf/predikat | `hitungHasilNilai()` di `php/aksi_nilai.php` |
| Bentuk foto | `.mahasiswa-foto` di `css/users.css` |
| Batas upload | `php/foto_mahasiswa.php` dan pengaturan `php.ini` |
| Struktur tabel dan data contoh | `database/setup.sql` dan `database/seed.sql` |

Saat menambah ukuran halaman, ubah validasi PHP dan pilihan HTML bersama-sama. Pilihan di browser harus tetap diizinkan server.

## 14. Latihan pemeriksaan

1. Import data contoh opsional, lalu ketik **Andi**. Hasil muncul otomatis tanpa Enter.
2. Hapus pencarian. Daftar kembali muncul.
3. Ketik nama yang tidak ada. Muncul pesan tidak ada hasil dan jumlah 0.
4. Ketik cepat, lalu ganti pencarian. Hasil akhir harus sesuai ketikan terakhir.
5. Cari nama, lalu klik ikon sorting. Pencarian tetap aktif dan ikon berganti.
6. Ganti ukuran 5, 10, dan 15. Coba First Page, Previous, Next, dan Last Page; tombol di batas halaman harus nonaktif.
7. Klik Edit setelah pencarian. Data modal harus sesuai hasil yang dipilih.
8. Tambah mahasiswa dengan/tanpa foto, kemudian coba ganti foto melalui Edit.
9. Muat ulang halaman. Pilihan pada URL tetap digunakan.

Untuk kesalahan JavaScript, buka Developer Tools browser dan lihat Console. Untuk kesalahan PHP/database, periksa terminal server PHP atau log Apache XAMPP. Mulai dari pesan kesalahan pertama.

## 15. Form upload dan galeri referensi

File referensi sekarang memakai database `mahasiswa` yang sama:

1. `php/form_upload.php` menampilkan pilihan NIM/nama dan input foto.
2. `php/upload.php` memvalidasi NIM/token, menyimpan file, dan memperbarui `nilaimhsw.file_path`.
3. `php/readphoto.php` membaca mahasiswa yang memiliki `file_path` pada tabel `nilaimhsw`.
4. Foto yang sama tampil di `users.php`, karena keduanya membaca tabel yang sama.

`connectionForm.php` memuat `connection.php`, sehingga kredensial cukup diatur pada satu file. Nama input pada form referensi adalah `photo`, jadi handler memanggil `unggahFotoMahasiswa('photo')`. Form modal memakai nama `foto` dan memanggil fungsi tanpa argumen.

Database `form` dari latihan sebelumnya tidak dihapus. Foto di sana belum memiliki NIM sehingga tidak dipasangkan otomatis; unggah melalui form yang baru untuk memilih pemiliknya.

`php/mahasiswa.php` tetap menyediakan JSON/baris tabel untuk latihan lama dan kini ikut membaca `nilaimhsw.file_path`.
