<?php
require_once __DIR__ . '/aksi_nilai.php';
require_once __DIR__ . '/connectionForm.php';
require_once __DIR__ . '/foto_mahasiswa.php';

$sql = '
    SELECT photo.id, photo.nim, nilaimhs.namalengkap, photo.namafile, photo.tgl_unggah
    FROM photo
    INNER JOIN nilaimhs ON nilaimhs.nim = photo.nim
    ORDER BY photo.tgl_unggah DESC, photo.id DESC
';
$hasil = mysqli_query($conn, $sql);
$daftarFoto = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
mysqli_free_result($hasil);
mysqli_close($conn);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Foto Mahasiswa</title>
    <link rel="stylesheet" href="../css/adminlte.css">
</head>
<body class="bg-body-tertiary">
    <main class="container py-4">
        <h1 class="fs-4">Foto Mahasiswa</h1>
        <div class="d-flex gap-2 mb-3">
            <a href="form_upload.php" class="btn btn-primary">Unggah Foto</a>
            <a href="../users.php" class="btn btn-outline-secondary">Data Mahasiswa</a>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">NIM</th>
                            <th scope="col">Nama Mahasiswa</th>
                            <th scope="col">Path Gambar</th>
                            <th scope="col">Tanggal Unggah</th>
                            <th scope="col">Pratinjau</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarFoto as $foto): ?>
                            <?php $urlGambar = '../' . urlFotoMahasiswa($foto['namafile']); ?>
                            <tr>
                                <td><?= escapeNilai($foto['id']) ?></td>
                                <td><?= escapeNilai($foto['nim']) ?></td>
                                <td><?= escapeNilai($foto['namalengkap']) ?></td>
                                <td><?= escapeNilai($foto['namafile']) ?></td>
                                <td><?= escapeNilai($foto['tgl_unggah']) ?></td>
                                <td>
                                    <a href="<?= escapeNilai($urlGambar) ?>" target="_blank" rel="noopener">
                                        <img src="<?= escapeNilai($urlGambar) ?>" width="100" alt="Foto <?= escapeNilai($foto['namalengkap']) ?>">
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($daftarFoto) === 0): ?>
                            <tr><td colspan="6" class="text-center">Belum ada foto mahasiswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
