<?php
require_once __DIR__ . '/aksi_nilai.php';
require_once __DIR__ . '/foto_mahasiswa.php';

// 1. Terima hanya kiriman form dan periksa token dari sesi pengguna.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    pesanAksi('Silakan isi form tambah data terlebih dahulu.', 405);
}
cekTokenAksi();

// 2. Baca input sebagai teks. trim menghapus spasi di awal/akhir.
$nim = $_POST['nim'] ?? '';
$nama = $_POST['nama_mhsw'] ?? '';
$nilai = $_POST['nilai_akhir'] ?? '';
if (!is_string($nim) || !is_string($nama) || !is_string($nilai)) {
    pesanAksi('Format data tidak valid.', 422);
}
$nim = trim($nim);
$nama = trim($nama);
$nilai = trim($nilai);

// 3. Periksa NIM, nama, dan nilai.
if (!preg_match('/^[0-9]{10}$/', $nim) || $nama == ''
    || !preg_match('/^.{1,100}$/us', $nama)
    || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
    http_response_code(422);
    exit('Isi NIM 10 angka, nama maksimal 100 karakter, dan nilai 0 sampai 100.
        <br><a href="../users.php">Kembali ke form</a>');
}
$nilai = round((float) $nilai, 2);

// 4. Gunakan aturan penilaian yang sama dengan proses edit.
$hasilNilai = hitungHasilNilai($nilai);
$huruf = $hasilNilai['huruf'];
$predikat = $hasilNilai['predikat'];

// 5. Hubungkan database dan simpan satu mahasiswa.
require_once __DIR__ . '/connection.php';
try {
    $foto = unggahFotoMahasiswa();
} catch (RuntimeException $error) {
    pesanAksi($error->getMessage(), 422);
}
$sql = 'INSERT INTO nilaimhs (nim, namalengkap, nilaiakhir, nilaihuruf, predikat)
        VALUES (?, ?, ?, ?, ?)';
// Mahasiswa dan path foto harus berhasil disimpan bersama-sama.
mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, $sql);
    // Tanda ? diisi dari variabel, agar input tidak menjadi bagian perintah SQL.
    mysqli_stmt_bind_param($stmt, 'ssdss', $nim, $nama, $nilai, $huruf, $predikat);
    mysqli_stmt_execute($stmt);
    simpanPathFotoMahasiswa($conn, $nim, $foto);
    mysqli_commit($conn);
} catch (mysqli_sql_exception $error) {
    mysqli_rollback($conn);
    hapusFotoMahasiswa($foto);
    if ($error->getCode() == 1062) {
        http_response_code(409);
        exit('NIM sudah terdaftar. <a href="../users.php">Kembali ke form</a>');
    }
    http_response_code(500);
    exit('Data gagal disimpan. <a href="../users.php">Kembali ke form</a>');
}
mysqli_stmt_close($stmt);
mysqli_close($conn);

// 6. Kembali ke halaman asal form. Hanya izinkan halaman tabel aplikasi.
$kembali = $_POST['kembali'] ?? '../users.php';
if (!in_array($kembali, ['../users.php'], true)) {
    $kembali = '../users.php';
}
header('Location: ' . $kembali, true, 303);
exit;
