-- Instalasi baru / migrasi versi lama. Data yang sudah ada tetap dipertahankan.
CREATE DATABASE IF NOT EXISTS mahasiswa
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mahasiswa;

-- Sesuaikan nama tabel dengan ketentuan UTS tanpa menghapus data lama.
SET @migrasi = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhs')
    AND NOT EXISTS (SELECT 1 FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhsw'),
    'RENAME TABLE nilaimhs TO nilaimhsw',
    'SELECT ''Nama tabel sudah siap.'' AS status'
);
PREPARE migrasi_stmt FROM @migrasi;
EXECUTE migrasi_stmt;
DEALLOCATE PREPARE migrasi_stmt;

CREATE TABLE IF NOT EXISTS nilaimhsw (
    nim VARCHAR(10) NOT NULL,
    namalengkap VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    nilaiakhir DECIMAL(5,2) NOT NULL,
    nilaihuruf CHAR(1) NOT NULL,
    predikat VARCHAR(30) NOT NULL,
    created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nim),
    KEY idx_nilaiakhir (nilaiakhir, nim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel hasil rename mungkin belum memiliki kolom file_path.
SET @migrasi = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhsw'
                  AND COLUMN_NAME = 'file_path'),
    'ALTER TABLE nilaimhsw ADD COLUMN file_path VARCHAR(255) DEFAULT NULL AFTER namalengkap',
    'SELECT ''Kolom file_path sudah siap.'' AS status'
);
PREPARE migrasi_stmt FROM @migrasi;
EXECUTE migrasi_stmt;
DEALLOCATE PREPARE migrasi_stmt;

-- Dahulukan foto dari tabel photo yang dipakai aplikasi versi sebelumnya.
-- Kolom/tabel lama tidak dihapus dan path baru yang sudah terisi tidak ditimpa.
SET @migrasi = IF(
    EXISTS (SELECT 1 FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'photo'),
    'UPDATE nilaimhsw INNER JOIN photo ON photo.nim = nilaimhsw.nim
     SET nilaimhsw.file_path = photo.namafile,
         nilaimhsw.modified_date = nilaimhsw.modified_date
     WHERE (nilaimhsw.file_path IS NULL OR nilaimhsw.file_path = '''')
       AND photo.namafile IS NOT NULL AND photo.namafile <> ''''',
    'SELECT ''Tidak ada tabel photo lama.'' AS status'
);
PREPARE migrasi_stmt FROM @migrasi;
EXECUTE migrasi_stmt;
DEALLOCATE PREPARE migrasi_stmt;

SET @migrasi = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nilaimhsw'
              AND COLUMN_NAME = 'foto'),
    'UPDATE nilaimhsw SET file_path = foto, modified_date = modified_date
     WHERE (file_path IS NULL OR file_path = '''') AND foto IS NOT NULL AND foto <> ''''',
    'SELECT ''Migrasi foto selesai.'' AS status'
);
PREPARE migrasi_stmt FROM @migrasi;
EXECUTE migrasi_stmt;
DEALLOCATE PREPARE migrasi_stmt;
