<?php
require_once __DIR__ . '/aksi_nilai.php';
require_once __DIR__ . '/connectionForm.php';

$hasil = mysqli_query($conn, 'SELECT nim, namalengkap FROM nilaimhsw ORDER BY nim');
$daftarMahasiswa = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
mysqli_free_result($hasil);
mysqli_close($conn);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Foto Mahasiswa</title>
    <link rel="stylesheet" href="../css/adminlte.css">
</head>
<body class="bg-body-tertiary">
    <main class="container py-4">
        <h1 class="fs-4">Upload Foto Mahasiswa</h1>
        <p>Pilih mahasiswa dan foto yang akan ditampilkan di sebelah namanya.</p>
        <?php if (count($daftarMahasiswa) === 0): ?>
            <p class="alert alert-info">Tambahkan data mahasiswa terlebih dahulu melalui halaman Data Mahasiswa.</p>
        <?php else: ?>
            <form action="upload.php" method="post" enctype="multipart/form-data" class="card card-body mb-3">
                <input type="hidden" name="token" value="<?= escapeNilai($_SESSION['token_aksi']) ?>">
                <div class="mb-3">
                    <label for="nim" class="form-label">Mahasiswa</label>
                    <select name="nim" id="nim" class="form-select" required>
                        <option value="">Pilih mahasiswa</option>
                        <?php foreach ($daftarMahasiswa as $mahasiswa): ?>
                            <option value="<?= escapeNilai($mahasiswa['nim']) ?>">
                                <?= escapeNilai($mahasiswa['nim'] . ' - ' . $mahasiswa['namalengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="photo" class="form-label">Foto</label>
                    <input
                        type="file" name="photo" id="photo" class="form-control"
                        accept="image/jpeg,image/png,image/gif" aria-describedby="foto-help" required
                    >
                    <div class="form-text" id="foto-help">
                        JPG, PNG, atau GIF maksimal 2 MB. Foto baru menggantikan foto mahasiswa sebelumnya.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary align-self-start">Unggah Foto</button>
            </form>
        <?php endif; ?>
        <a href="readphoto.php" class="btn btn-outline-secondary">Lihat Foto</a>
        <a href="../users.php" class="btn btn-outline-secondary">Data Mahasiswa</a>
    </main>
</body>
</html>
