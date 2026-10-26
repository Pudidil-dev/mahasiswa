<?php
// Urutan proses: buka koneksi, baca data, lalu tampilkan HTML.
require_once __DIR__ . '/php/aksi_nilai.php';
require_once __DIR__ . '/php/connection.php';
require_once __DIR__ . '/php/foto_mahasiswa.php';
// 1. Baca pilihan pengguna dari URL dan batasi nilai yang diperbolehkan.
$pencarian = ambilParameterTeks('q');
$pilihanPredikat = ['Sangat Memuaskan', 'Memuaskan', 'Cukup', 'Kurang', 'Tidak Lulus'];
$filterPredikat = ambilParameterTeks('predikat');

if (!in_array($filterPredikat, $pilihanPredikat, true)) {
    $filterPredikat = '';
}

$urutan = ambilParameterTeks('sort');
if (!in_array($urutan, ['asc', 'desc'], true)) {
    $urutan = '';
}

$ukuran = (int) ambilParameterTeks('size', '5');
if (!in_array($ukuran, [5, 10, 15], true)) {
    $ukuran = 5;
}

$halaman = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if (!$halaman || $halaman < 1) {
    $halaman = 1;
}

// LOCATE mencari potongan nama/NIM. Pencarian kosong berarti semua data.
// Tanda ? diisi lewat bind_param supaya input tidak menjadi perintah SQL.
$sqlFilter = "
    WHERE (? = '' OR LOCATE(?, nilaimhsw.namalengkap) > 0 OR LOCATE(?, nilaimhsw.nim) > 0)
      AND (? = '' OR nilaimhsw.predikat = ?)
";

// 2. Hitung jumlah data yang cocok, sebelum membatasi data per halaman.
$stmtJumlah = mysqli_prepare($conn, 'SELECT COUNT(*) FROM nilaimhsw ' . $sqlFilter);
mysqli_stmt_bind_param(
    $stmtJumlah,
    'sssss',
    $pencarian,
    $pencarian,
    $pencarian,
    $filterPredikat,
    $filterPredikat
);
mysqli_stmt_execute($stmtJumlah);
mysqli_stmt_bind_result($stmtJumlah, $jumlah);
mysqli_stmt_fetch($stmtJumlah);
mysqli_stmt_close($stmtJumlah);
$jumlah = (int) $jumlah;

$jumlahHalaman = max(1, (int) ceil($jumlah / $ukuran));
$halaman = min($halaman, $jumlahHalaman);
$offset = ($halaman - 1) * $ukuran;

// Nama kolom dan arah ORDER BY ditentukan kode, bukan input SQL pengguna.
$urutanSql = 'nilaimhsw.nim ASC';
if ($urutan !== '') {
    $urutanSql = 'nilaimhsw.nilaiakhir ' . strtoupper($urutan) . ', nilaimhsw.nim ASC';
}

// 3. Ambil hanya baris pada halaman aktif. s = string, i = integer.
$sqlData = '
    SELECT nilaimhsw.nim, nilaimhsw.namalengkap, nilaimhsw.file_path AS foto,
           nilaimhsw.nilaiakhir, nilaimhsw.nilaihuruf, nilaimhsw.predikat,
           nilaimhsw.created_date, nilaimhsw.modified_date
    FROM nilaimhsw
' . $sqlFilter;
$sqlData .= ' ORDER BY ' . $urutanSql . ' LIMIT ? OFFSET ?';
$stmtData = mysqli_prepare($conn, $sqlData);
mysqli_stmt_bind_param(
    $stmtData,
    'sssssii',
    $pencarian,
    $pencarian,
    $pencarian,
    $filterPredikat,
    $filterPredikat,
    $ukuran,
    $offset
);
mysqli_stmt_execute($stmtData);
$hasilQuery = mysqli_stmt_get_result($stmtData);

$daftarMahasiswa = [];
while ($mahasiswa = mysqli_fetch_assoc($hasilQuery)) {
    $daftarMahasiswa[] = $mahasiswa;
}
mysqli_free_result($hasilQuery);
mysqli_stmt_close($stmtData);

// 4. Siapkan informasi yang diperlukan tampilan.
$awalData = $jumlah > 0 ? $offset + 1 : 0;
$akhirData = min($offset + $ukuran, $jumlah);
$parameterDaftar = [
    'q' => $pencarian,
    'predikat' => $filterPredikat,
    'sort' => $urutan,
    'size' => $ukuran,
];

$urutanBerikut = 'asc';
$ikonUrutan = 'bi-caret-up-fill';
$statusUrutan = 'none';
$warnaIkon = 'text-body-tertiary';

if ($urutan === 'asc') {
    $urutanBerikut = 'desc';
    $statusUrutan = 'ascending';
    $warnaIkon = 'text-primary';
} elseif ($urutan === 'desc') {
    $ikonUrutan = 'bi-caret-down-fill';
    $statusUrutan = 'descending';
    $warnaIkon = 'text-primary';
}

$labelUrutan = 'Urutkan nilai akhir ASC (terendah)';
if ($urutanBerikut === 'desc') {
    $labelUrutan = 'Urutkan nilai akhir DESC (tertinggi)';
}

// Tampilkan halaman pertama, terakhir, dan dua tetangga halaman aktif.
$nomorHalaman = [];
for ($nomor = max(1, $halaman - 2); $nomor <= min($jumlahHalaman, $halaman + 2); $nomor++) {
    $nomorHalaman[] = $nomor;
}
if ($nomorHalaman[0] !== 1) {
    array_unshift($nomorHalaman, 1);
}
if (end($nomorHalaman) !== $jumlahHalaman) {
    $nomorHalaman[] = $jumlahHalaman;
}

mysqli_close($conn);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

// Untuk AJAX, kirim bagian tabel dan pagination saja dari template yang sama.
$permintaanAjax = ambilParameterTeks('ajax') === '1';
?>
<?php if (!$permintaanAjax): ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="Kelola data, foto, dan nilai mahasiswa.">
    <title>Data Mahasiswa</title>

    <script src="./js/theme.js"></script>
    <link
        rel="stylesheet"
        href="./assets/vendor/cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
    >
    <link
        rel="stylesheet"
        href="./assets/vendor/cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    >
    <link
        rel="stylesheet"
        href="./assets/vendor/cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >
    <link rel="stylesheet" href="./css/adminlte.css">
    <link rel="stylesheet" href="./css/users.css">
</head>
<body class="users-page bg-body-tertiary">
    <div class="app-wrapper">
        <main class="app-main" id="main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h1 class="mb-0 fs-3">Data Mahasiswa</h1>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <h2 class="card-title">Data Mahasiswa</h2>
                                </div>
                                <div class="col-12 col-md-8">
                                    <form
                                        method="get"
                                        action="users.php"
                                        id="filter-mahasiswa"
                                        class="d-flex flex-wrap justify-content-md-end gap-2"
                                    >
                                        <input type="hidden" name="sort" value="<?= escapeNilai($urutan) ?>">
                                        <div class="input-group input-group-sm w-auto">
                                            <span class="input-group-text">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </span>
                                            <input
                                                type="search"
                                                id="user-search"
                                                name="q"
                                                value="<?= escapeNilai($pencarian) ?>"
                                                class="form-control"
                                                placeholder="Cari nama atau NIM"
                                                aria-label="Cari mahasiswa"
                                                autocomplete="off"
                                                aria-controls="hasil-mahasiswa"
                                            >
                                        </div>
                                        <select
                                            id="user-role-filter"
                                            name="predikat"
                                            class="form-select form-select-sm w-auto"
                                            aria-label="Filter predikat"
                                        >
                                            <option value="">Semua predikat</option>
                                            <?php foreach ($pilihanPredikat as $pilihan): ?>
                                                <option
                                                    value="<?= escapeNilai($pilihan) ?>"
                                                    <?= $filterPredikat === $pilihan ? 'selected' : '' ?>
                                                ><?= escapeNilai($pilihan) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-add-user"
                                        >
                                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>
                                            Tambah data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div id="pesan-pencarian" class="px-3 py-2 small" role="status" hidden></div>
                        <!-- Hanya isi bagian ini yang diganti oleh JavaScript. -->
                        <div id="hasil-mahasiswa" aria-live="polite">
                        <?php endif; ?>

                            <!-- Tabel dan pagination ditulis langsung di users.php. -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle m-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">NIM</th>
                                                <th scope="col">Nama Mahasiswa</th>
                                                <th scope="col" aria-sort="<?= $statusUrutan ?>">
                                                    <div class="d-inline-flex align-items-center gap-2 text-nowrap">
                                                        <span>Nilai Akhir</span>
                                                        <a
                                                            href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, 1, $urutanBerikut)) ?>"
                                                            data-urutan="<?= $urutanBerikut ?>"
                                                            class="<?= $warnaIkon ?> text-decoration-none lh-1"
                                                            aria-label="<?= $labelUrutan ?>"
                                                            title="<?= $labelUrutan ?>"
                                                        >
                                                            <i class="bi <?= $ikonUrutan ?>" aria-hidden="true"></i>
                                                        </a>
                                                    </div>
                                                </th>
                                                <th scope="col">Nilai Huruf</th>
                                                <th scope="col">Predikat</th>
                                                <th scope="col">Tgl Dibuat</th>
                                                <th scope="col">Tgl Diubah</th>
                                                <th scope="col" class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mahasiswa-body">
                                            <?php foreach ($daftarMahasiswa as $mahasiswa): ?>
                                                <tr>
                                                    <td><?= escapeNilai($mahasiswa['nim']) ?></td>
                                                    <td>
                                                        <div class="mahasiswa-identitas">
                                                            <img
                                                                src="<?= escapeNilai(urlFotoMahasiswa($mahasiswa['foto'])) ?>"
                                                                class="mahasiswa-foto"
                                                                width="44"
                                                                height="44"
                                                                alt="Foto <?= escapeNilai($mahasiswa['namalengkap']) ?>"
                                                                loading="lazy"
                                                            >
                                                            <span><?= escapeNilai($mahasiswa['namalengkap']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= escapeNilai($mahasiswa['nilaiakhir']) ?></td>
                                                    <td><?= escapeNilai($mahasiswa['nilaihuruf']) ?></td>
                                                    <td><?= escapeNilai($mahasiswa['predikat']) ?></td>
                                                    <td><?= escapeNilai($mahasiswa['created_date']) ?></td>
                                                    <td><?= escapeNilai($mahasiswa['modified_date']) ?></td>
                                                    <td class="text-end text-nowrap">
                                                        <button
                                                            type="button"
                                                            class="edit btn btn-sm btn-outline-secondary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modal-edit-mahasiswa"
                                                            data-nim="<?= escapeNilai($mahasiswa['nim']) ?>"
                                                            data-nama="<?= escapeNilai($mahasiswa['namalengkap']) ?>"
                                                            data-nilai="<?= escapeNilai($mahasiswa['nilaiakhir']) ?>"
                                                            data-token="<?= escapeNilai($_SESSION['token_aksi']) ?>"
                                                            title="Edit Data"
                                                            aria-label="Edit Data"
                                                        >
                                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                                        </button>
                                                        <form
                                                            method="post"
                                                            action="./php/deletenilai.php"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Hapus data mahasiswa ini?');"
                                                        >
                                                            <input type="hidden" name="nim" value="<?= escapeNilai($mahasiswa['nim']) ?>">
                                                            <input type="hidden" name="token" value="<?= escapeNilai($_SESSION['token_aksi']) ?>">
                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Hapus Data"
                                                                aria-label="Hapus Data"
                                                            >
                                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if ($jumlah === 0): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-body-secondary py-4">
                                                        <?php if ($pencarian !== '' || $filterPredikat !== ''): ?>
                                                            Tidak ada mahasiswa yang cocok dengan pencarian atau filter.
                                                        <?php else: ?>
                                                            Belum ada data mahasiswa.
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="small text-body-secondary">
                                        Jumlah mahasiswa: <span id="jumlah-mahasiswa"><?= $jumlah ?></span>
                                        <span class="d-block">Menampilkan <?= $awalData ?>–<?= $akhirData ?> dari <?= $jumlah ?> data</span>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                        <!-- Atribut form menghubungkan dropdown footer dengan form di header. -->
                                        <select
                                            name="size"
                                            id="page-size"
                                            form="filter-mahasiswa"
                                            class="form-select form-select-sm w-auto"
                                            aria-label="Jumlah mahasiswa per halaman"
                                        >
                                            <?php foreach ([5, 10, 15] as $pilihanUkuran): ?>
                                                <option value="<?= $pilihanUkuran ?>" <?= $ukuran === $pilihanUkuran ? 'selected' : '' ?>>
                                                    <?= $pilihanUkuran ?> per halaman
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <nav id="navigation" aria-label="Halaman data mahasiswa">
                                            <ul class="pagination pagination-sm mb-0">
                                                <?php if ($halaman > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" data-halaman="1"
                                                           href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, 1)) ?>"
                                                           aria-label="Halaman pertama">First Page</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled"><span class="page-link" aria-disabled="true">First Page</span></li>
                                                <?php endif; ?>
                                                <?php if ($halaman > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" data-halaman="<?= $halaman - 1 ?>"
                                                           href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, $halaman - 1)) ?>"
                                                           aria-label="Halaman sebelumnya">Previous</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                                <?php endif; ?>
                            
                                                <?php $halamanSebelumnya = 0; ?>
                                                <?php foreach ($nomorHalaman as $nomor): ?>
                                                    <?php if ($halamanSebelumnya > 0 && $nomor > $halamanSebelumnya + 1): ?>
                                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                                    <?php endif; ?>
                                                    <?php if ($nomor === $halaman): ?>
                                                        <li class="page-item active" aria-current="page">
                                                            <span class="page-link"><?= $nomor ?></span>
                                                        </li>
                                                    <?php else: ?>
                                                        <li class="page-item">
                                                            <a class="page-link" data-halaman="<?= $nomor ?>"
                                                               href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, $nomor)) ?>"><?= $nomor ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php $halamanSebelumnya = $nomor; ?>
                                                <?php endforeach; ?>
                            
                                                <?php if ($halaman < $jumlahHalaman): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" data-halaman="<?= $halaman + 1 ?>"
                                                           href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, $halaman + 1)) ?>"
                                                           aria-label="Halaman berikutnya">Next</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                                <?php endif; ?>
                                                <?php if ($halaman < $jumlahHalaman): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" data-halaman="<?= $jumlahHalaman ?>"
                                                           href="<?= escapeNilai(urlDaftarMahasiswa($parameterDaftar, $jumlahHalaman)) ?>"
                                                           aria-label="Halaman terakhir">Last Page</a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled"><span class="page-link" aria-disabled="true">Last Page</span></li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>

                            <?php
                            // Hentikan respons AJAX sebelum bagian modal dan penutup halaman.
                            if ($permintaanAjax) {
                                exit;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="modal fade" id="modal-add-user" tabindex="-1" aria-labelledby="modal-add-user-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formTambahMahasiswa" method="post" action="./php/insertnilai.php" enctype="multipart/form-data">
                        <input type="hidden" name="kembali" value="../users.php">
                        <input type="hidden" name="token" value="<?= escapeNilai($_SESSION['token_aksi']) ?>">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="modal-add-user-label">Tambah Data Mahasiswa</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-body-secondary mb-3">
                                Isi data mahasiswa. Nilai huruf dan predikat dihitung otomatis.
                            </p>
                            <div id="pesanTambahMahasiswa" class="alert alert-danger d-none" role="alert"></div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-7">
                                    <label for="new-user-nim" class="form-label">NIM</label>
                                    <input
                                        type="text" class="form-control" id="new-user-nim" name="nim"
                                        inputmode="numeric" pattern="[0-9]{10}" maxlength="10"
                                        placeholder="3202316001" aria-describedby="nim-help" required
                                    >
                                    <div id="nim-help" class="form-text">NIM terdiri dari 10 angka.</div>
                                </div>
                                <div class="col-12 col-sm-5">
                                    <label for="new-user-nilai" class="form-label">Nilai Akhir</label>
                                    <input
                                        type="number" class="form-control" id="new-user-nilai" name="nilai_akhir"
                                        min="0" max="100" step="0.01" placeholder="87.90" required
                                    >
                                    <div class="form-text">Nilai antara 0 dan 100.</div>
                                </div>
                                <div class="col-12">
                                    <label for="new-user-name" class="form-label">Nama Mahasiswa</label>
                                    <input
                                        type="text" class="form-control" id="new-user-name" name="nama_mhsw"
                                        maxlength="100" placeholder="Masukkan nama lengkap" autocomplete="name" required
                                    >
                                </div>
                                <div class="col-12">
                                    <label for="new-user-foto" class="form-label">Foto Mahasiswa (opsional)</label>
                                    <input
                                        type="file" class="form-control" id="new-user-foto" name="foto"
                                        accept="image/jpeg,image/png,image/gif" aria-describedby="new-foto-help"
                                    >
                                    <div id="new-foto-help" class="form-text">
                                        JPG, PNG, atau GIF maksimal 2 MB. Tanpa foto akan memakai gambar bawaan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="modal-edit-mahasiswa" tabindex="-1" aria-labelledby="judulEditMahasiswa" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formEditMahasiswa" method="post" action="./php/editnilai.php" enctype="multipart/form-data">
                        <input type="hidden" name="token" id="edit-token">
                        <input type="hidden" name="modal" value="1">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="judulEditMahasiswa">Edit Nilai Mahasiswa</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div id="pesanEditMahasiswa" class="alert alert-danger d-none" role="alert"></div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-7">
                                    <label for="edit-nim" class="form-label">NIM</label>
                                    <input class="form-control" id="edit-nim" name="nim" readonly>
                                </div>
                                <div class="col-12 col-sm-5">
                                    <label for="edit-nilai" class="form-label">Nilai Akhir</label>
                                    <input
                                        type="number" class="form-control" id="edit-nilai" name="nilai_akhir"
                                        min="0" max="100" step="0.01" required
                                    >
                                </div>
                                <div class="col-12">
                                    <label for="edit-nama" class="form-label">Nama Mahasiswa</label>
                                    <input class="form-control" id="edit-nama" name="nama_mhsw" maxlength="100" required>
                                </div>
                                <div class="col-12">
                                    <label for="edit-foto" class="form-label">Ganti Foto Mahasiswa (opsional)</label>
                                    <input
                                        type="file" class="form-control" id="edit-foto" name="foto"
                                        accept="image/jpeg,image/png,image/gif" aria-describedby="edit-foto-help"
                                    >
                                    <div id="edit-foto-help" class="form-text">
                                        JPG, PNG, atau GIF maksimal 2 MB. Kosongkan untuk mempertahankan foto lama.
                                    </div>
                                </div>
                            </div>
                            <p class="form-text mb-0 mt-3">
                                Nilai huruf dan predikat dihitung otomatis setelah disimpan.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./assets/vendor/cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="./assets/vendor/cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="./assets/vendor/cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="./js/adminlte.js"></script>
    <script src="./js/form-mahasiswa.js"></script>
    <script src="./js/users.js"></script>
</body>
</html>
