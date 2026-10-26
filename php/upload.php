<?php
require_once __DIR__ . '/aksi_nilai.php';
require_once __DIR__ . '/foto_mahasiswa.php';

// 1. Periksa kiriman form sebelum mengunggah file.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    pesanAksi('Gunakan form upload foto terlebih dahulu.', 405);
}
cekTokenAksi();

$nim = $_POST['nim'] ?? '';
if (!is_string($nim) || !preg_match('/^[0-9]{10}$/', $nim)) {
    pesanAksi('Pilih mahasiswa dengan NIM yang valid.', 422);
}

// 2. Ambil path lama dan pastikan mahasiswa sudah terdaftar.
require_once __DIR__ . '/connectionForm.php';
$stmt = mysqli_prepare($conn, '
    SELECT nilaimhs.nim, photo.namafile AS foto
    FROM nilaimhs
    LEFT JOIN photo ON photo.nim = nilaimhs.nim
    WHERE nilaimhs.nim = ?
');
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$mahasiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$mahasiswa) {
    pesanAksi('Mahasiswa tidak ditemukan.', 404);
}

// 3. Input file pada form referensi bernama photo, bukan foto.
try {
    $fotoBaru = unggahFotoMahasiswa('photo');
} catch (RuntimeException $error) {
    pesanAksi($error->getMessage(), 422);
}
if ($fotoBaru === null) {
    pesanAksi('Pilih foto yang akan diunggah.', 422);
}

// 4. Simpan path pada tabel photo, lalu bersihkan foto sebelumnya.
try {
    simpanPathFotoMahasiswa($conn, $nim, $fotoBaru);
} catch (mysqli_sql_exception $error) {
    hapusFotoMahasiswa($fotoBaru);
    pesanAksi('Path foto gagal disimpan.', 500);
}
mysqli_close($conn);
hapusFotoMahasiswa($mahasiswa['foto']);

header('Location: readphoto.php', true, 303);
exit;
