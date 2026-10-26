<?php
// Path foto disimpan relatif terhadap folder utama aplikasi.
function pathFotoMahasiswa($foto)
{
    if (!is_string($foto)
        || !preg_match('~^uploads/mahasiswa/[a-f0-9]{32}\.(jpg|png|gif)$~', $foto)) {
        return null;
    }
    return dirname(__DIR__) . '/' . $foto;
}

function urlFotoMahasiswa($foto)
{
    // Avatar bawaan dipakai data demo dan tidak boleh dihapus saat data dihapus.
    if (in_array($foto, [
        'assets/img/avatar.png', 'assets/img/avatar2.png', 'assets/img/avatar3.png',
        'assets/img/avatar4.png', 'assets/img/avatar5.png',
    ], true)) {
        return $foto;
    }
    $path = pathFotoMahasiswa($foto);
    return $path !== null && is_file($path) ? $foto : 'assets/img/default-150x150.png';
}

function hapusFotoMahasiswa($foto)
{
    $path = pathFotoMahasiswa($foto);
    if ($path !== null && is_file($path)) {
        unlink($path);
    }
}

// Path foto disimpan langsung pada baris mahasiswa sesuai ketentuan UTS.
function simpanPathFotoMahasiswa($conn, $nim, $foto)
{
    if ($foto === null) {
        return;
    }

    $sql = 'UPDATE nilaimhsw SET file_path = ? WHERE nim = ?';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $foto, $nim);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function unggahFotoMahasiswa($namaInput = 'foto')
{
    $file = $_FILES[$namaInput] ?? null;
    if ($file === null || (is_array($file) && ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE)) {
        return null;
    }
    if (!is_array($file) || ($file['error'] ?? null) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Foto gagal diunggah. Pilih JPG, PNG, atau GIF maksimal 2 MB.');
    }
    if (!isset($file['size'], $file['tmp_name']) || !is_string($file['tmp_name'])
        || $file['size'] > 2 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Foto harus berupa JPG, PNG, atau GIF maksimal 2 MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $mime = $info['mime'] ?? '';
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Format foto tidak valid. Gunakan JPG, PNG, atau GIF.');
    }
    $directory = dirname(__DIR__) . '/uploads/mahasiswa';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder foto tidak dapat dibuat. Periksa izin uploads/mahasiswa.');
    }
    $foto = 'uploads/mahasiswa/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/' . $foto)) {
        throw new RuntimeException('Foto gagal disimpan. Periksa izin folder uploads/mahasiswa.');
    }
    return $foto;
}
