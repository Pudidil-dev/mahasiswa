-- Import file ini melalui phpMyAdmin atau perintah SOURCE di klien MySQL.
-- Untuk hosting, sesuaikan nama database pada CREATE DATABASE dan USE.
CREATE DATABASE IF NOT EXISTS mahasiswa
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mahasiswa;

CREATE TABLE IF NOT EXISTS nilaimhs (
    nim VARCHAR(10) NOT NULL,
    namalengkap VARCHAR(100) NOT NULL,
    nilaiakhir DECIMAL(5,2) NOT NULL,
    nilaihuruf CHAR(1) NOT NULL,
    predikat VARCHAR(30) NOT NULL,
    created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nim),
    KEY idx_nilaiakhir (nilaiakhir, nim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setiap mahasiswa mempunyai paling banyak satu foto aktif.
-- Tipe/charset/collation NIM mengikuti tabel lama agar foreign key cocok.
SELECT COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME
INTO @nim_type, @nim_charset, @nim_collation
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhs' AND COLUMN_NAME = 'nim';

SET @photo_ddl = CONCAT(
    'CREATE TABLE IF NOT EXISTS photo (',
    'id INT UNSIGNED NOT NULL AUTO_INCREMENT, ',
    'nim ', @nim_type, ' CHARACTER SET ', @nim_charset, ' COLLATE ', @nim_collation, ' NOT NULL, ',
    'namafile VARCHAR(255) NOT NULL, ',
    'tgl_unggah TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ',
    'PRIMARY KEY (id), ',
    'UNIQUE KEY uk_photo_nim (nim), ',
    'CONSTRAINT fk_photo_mahasiswa FOREIGN KEY (nim) REFERENCES nilaimhs (nim) ',
    'ON UPDATE CASCADE ON DELETE CASCADE',
    ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE photo_stmt FROM @photo_ddl;
EXECUTE photo_stmt;
DEALLOCATE PREPARE photo_stmt;

-- Upgrade aplikasi versi sebelumnya: salin path foto lama berdasarkan NIM.
-- Kolom lama tidak dihapus. Aplikasi selanjutnya membaca photo.namafile.
-- Baris photo yang sudah ada tidak ditimpa saat setup dijalankan ulang.
SET @migrasi_foto = IF(
    EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhs' AND COLUMN_NAME = 'foto'
    ),
    'INSERT INTO photo (nim, namafile)
     SELECT nilaimhs.nim, nilaimhs.foto FROM nilaimhs
     LEFT JOIN photo ON photo.nim = nilaimhs.nim
     WHERE nilaimhs.foto IS NOT NULL AND nilaimhs.foto <> '''' AND photo.nim IS NULL',
    'SELECT ''Tabel photo sudah siap.'' AS status'
);
PREPARE migrasi_stmt FROM @migrasi_foto;
EXECUTE migrasi_stmt;
DEALLOCATE PREPARE migrasi_stmt;
