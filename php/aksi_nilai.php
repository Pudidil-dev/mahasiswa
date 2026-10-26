<?php
session_start();
if (!isset($_SESSION['token_aksi'])) {
    $_SESSION['token_aksi'] = bin2hex(random_bytes(32));
}

function escapeNilai($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Dipakai oleh tambah dan edit supaya aturan penilaian selalu sama.
function hitungHasilNilai($nilai)
{
    if ($nilai <= 34.50) {
        return ['huruf' => 'E', 'predikat' => 'Tidak Lulus'];
    }
    if ($nilai <= 50.50) {
        return ['huruf' => 'D', 'predikat' => 'Kurang'];
    }
    if ($nilai <= 60.50) {
        return ['huruf' => 'C', 'predikat' => 'Cukup'];
    }
    if ($nilai <= 80.50) {
        return ['huruf' => 'B', 'predikat' => 'Memuaskan'];
    }

    return ['huruf' => 'A', 'predikat' => 'Sangat Memuaskan'];
}

function pesanAksi($pesan, $status)
{
    http_response_code($status);
    exit(escapeNilai($pesan) . '<br><a href="../users.php">Kembali ke data mahasiswa</a>');
}

function cekTokenAksi()
{
    $token = $_POST['token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['token_aksi'], $token)) {
        pesanAksi('Form tidak valid. Buka kembali halaman data mahasiswa.', 403);
    }
}

// Fungsi bantu daftar tetap dipanggil dari users.php.
function ambilParameterTeks($nama, $nilaiDefault = '')
{
    $nilai = $_GET[$nama] ?? $nilaiDefault;

    if (!is_string($nilai)) {
        return $nilaiDefault;
    }

    return trim($nilai);
}

function urlDaftarMahasiswa($parameter, $halaman, $urutan = null)
{
    $parameter['page'] = $halaman;

    if ($urutan !== null) {
        $parameter['sort'] = $urutan;
    }

    return 'users.php?' . http_build_query($parameter);
}
