<?php
// Halaman HTML meminta token ini untuk form hapus data.
if (isset($_GET['token'])) {
    require_once __DIR__ . '/aksi_nilai.php';
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['token' => $_SESSION['token_aksi']]);
    exit;
}
// Endpoint latihan lama. Query dan tampilan halaman utama berada di users.php.
require_once __DIR__ . '/connection.php';
mysqli_set_charset($conn, 'utf8mb4');
header('Cache-Control: no-store');

$sql = 'SELECT nilaimhsw.*, file_path AS foto FROM nilaimhsw ORDER BY nim';
$result = mysqli_query($conn, $sql);

// Mode tabel HTML tetap tersedia untuk latihan/halaman lama.
if (isset($_GET['tabel'])) {
    require_once __DIR__ . '/aksi_nilai.php';
    header('Content-Type: text/html; charset=utf-8');
    require_once __DIR__ . '/foto_mahasiswa.php';
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $urlFoto = escapeNilai(urlFotoMahasiswa($row['foto']));
            // Amankan teks sebelum dicetak sebagai HTML.
            foreach ($row as $kolom => $isi) {
                $row[$kolom] = htmlspecialchars($isi ?? '', ENT_QUOTES, 'UTF-8');
            }
            echo "<tr>";
            echo "<td>" . $row['nim'] . "</td>";
            echo '<td><img src="' . $urlFoto
                . '" width="44" height="44" alt="Foto mahasiswa"> ' . $row['namalengkap'] . '</td>';
            echo "<td>" . $row['nilaiakhir'] . "</td>";
            echo "<td>" . $row['nilaihuruf'] . "</td>";
            echo "<td>" . $row['predikat'] . "</td>";
            echo "<td>" . $row['created_date'] . "</td>";
            echo "<td>" . $row['modified_date'] . "</td>";
            echo '<td class="text-end text-nowrap">';
            echo '<button type="button" class="edit btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-edit-mahasiswa" data-nim="' . $row['nim'] . '" data-nama="' . $row['namalengkap'] . '" data-nilai="' . $row['nilaiakhir'] . '" data-token="' . $_SESSION['token_aksi'] . '">Edit</button> ';
            echo '<form method="post" action="./php/deletenilai.php" class="d-inline" onsubmit="return confirm(\'Hapus data mahasiswa ini?\');">';
            echo '<input type="hidden" name="nim" value="' . $row['nim'] . '">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token_aksi'] . '">';
            echo '<button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>';
            echo '</form></td>';
            echo "</tr>";
        }
    } else {
        echo '<tr><td colspan="8">0 results - Data not found!</td></tr>';
    }
    mysqli_close($conn);
    exit;
}

// Tanpa parameter tabel, respons dikirim sebagai JSON.
header('Content-Type: application/json; charset=utf-8');
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row; 
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
mysqli_free_result($result);
mysqli_close($conn);
