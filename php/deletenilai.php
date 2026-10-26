<?php
require_once __DIR__ . '/aksi_nilai.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    pesanAksi('Gunakan tombol Hapus Data pada tabel.', 405);
}
cekTokenAksi();
$nim = $_POST['nim'] ?? '';
if (!is_string($nim) || !preg_match('/^[0-9]{10}$/', $nim)) {
    pesanAksi('NIM tidak valid.', 422);
}
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/foto_mahasiswa.php';
$stmt = mysqli_prepare($conn, 'SELECT namafile AS foto FROM photo WHERE nim = ?');
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
// Foreign key ON DELETE CASCADE ikut menghapus baris pada tabel photo.
$stmt = mysqli_prepare($conn, 'DELETE FROM nilaimhs WHERE nim = ?');
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$jumlah = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
if ($jumlah === 0) {
    pesanAksi('Data mahasiswa tidak ditemukan.', 404);
}
hapusFotoMahasiswa($row['foto'] ?? null);
header('Location: ../users.php', true, 303);
exit;
