<?php
require_once __DIR__ . '/aksi_nilai.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/foto_mahasiswa.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    pesanAksi('Metode tidak didukung.', 405);
}
$nim = ($method === 'POST' ? $_POST['nim'] ?? '' : $_GET['nim'] ?? '');
if (!is_string($nim) || !preg_match('/^[0-9]{10}$/', $nim)) {
    pesanAksi('NIM tidak valid.', 422);
}
if ($method === 'POST') {
    cekTokenAksi();
}

// Ambil mahasiswa berdasarkan NIM. Tanda ? diisi oleh bind_param.
$stmt = mysqli_prepare($conn, '
    SELECT nim, namalengkap, nilaiakhir, file_path AS foto
    FROM nilaimhsw
    WHERE nilaimhsw.nim = ?
');
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$row) {
    pesanAksi('Data mahasiswa tidak ditemukan.', 404);
}

$nama = $row['namalengkap'];
$nilai = $row['nilaiakhir'];
$error = '';
// Saat tombol Simpan Perubahan ditekan, baca input dan jalankan UPDATE.
if ($method === 'POST') {
    $nama = $_POST['nama_mhsw'] ?? '';
    $nilai = $_POST['nilai_akhir'] ?? '';
    if (!is_string($nama) || !is_string($nilai)) {
        pesanAksi('Format data tidak valid.', 422);
    }
    $nama = trim($nama);
    $nilai = trim($nilai);
    if ($nama === '' || !preg_match('/^.{1,100}$/us', $nama)) {
        $error = 'Nama wajib diisi, maksimal 100 karakter.';
    } elseif (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/', $nilai) || (float) $nilai > 100) {
        $error = 'Nilai harus antara 0 sampai 100, maksimal 2 angka desimal.';
    } else {
        $angka = (float) $nilai;
        $hasilNilai = hitungHasilNilai($angka);
        $huruf = $hasilNilai['huruf'];
        $predikat = $hasilNilai['predikat'];
        $fotoBaru = null;
        try {
            $fotoBaru = unggahFotoMahasiswa();
        } catch (RuntimeException $uploadError) {
            pesanAksi($uploadError->getMessage(), 422);
        }
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, 'UPDATE nilaimhsw SET namalengkap = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ? WHERE nim = ?');
            mysqli_stmt_bind_param($stmt, 'sdsss', $nama, $angka, $huruf, $predikat, $nim);
            mysqli_stmt_execute($stmt);
            simpanPathFotoMahasiswa($conn, $nim, $fotoBaru);
            mysqli_commit($conn);
        } catch (mysqli_sql_exception $databaseError) {
            mysqli_rollback($conn);
            hapusFotoMahasiswa($fotoBaru);
            pesanAksi('Perubahan data gagal disimpan.', 500);
        }
        mysqli_stmt_close($stmt);
        if ($fotoBaru !== null) {
            hapusFotoMahasiswa($row['foto']);
        }
        mysqli_close($conn);
        // Form modal menerima hasil JSON agar tetap di halaman tabel.
        if (isset($_POST['modal'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }
        header('Location: ../users.php', true, 303);
        exit;
    }
    http_response_code(422);
    if (isset($_POST['modal'])) {
        exit($error);
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Nilai Mahasiswa</title>
</head>
<body>
    <h2>Edit Nilai Mahasiswa</h2>
    <p style="color: red;"><?= escapeNilai($error) ?></p>
    <form method="post" action="editnilai.php" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= escapeNilai($_SESSION['token_aksi']) ?>">
        <p><label>NIM: <input name="nim" value="<?= escapeNilai($nim) ?>" readonly></label></p>
        <p><label>Nama Mahasiswa: <input name="nama_mhsw" maxlength="100" value="<?= escapeNilai($nama) ?>" required></label></p>
        <p><label>Nilai Akhir: <input type="number" name="nilai_akhir" min="0" max="100" step="0.01" value="<?= escapeNilai($nilai) ?>" required></label></p>
        <p><label>Ganti foto: <input type="file" name="foto" accept="image/jpeg,image/png,image/gif"></label><br>JPG, PNG, atau GIF maksimal 2 MB. Kosongkan untuk mempertahankan foto lama.</p>
        <button type="submit">Simpan Perubahan</button>
        <a href="../users.php">Batal</a>
    </form>
</body>
</html>
