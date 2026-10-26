-- Opsional: pilih database aplikasi terlebih dahulu, lalu jalankan skrip ini.
-- NIM contoh berawalan 9999; data dengan NIM yang sudah ada tidak ditimpa.
-- Foto contoh tidak dibuat; tabel photo diisi ketika mahasiswa mengunggah file.
INSERT IGNORE INTO nilaimhs (nim, namalengkap, nilaiakhir, nilaihuruf, predikat) VALUES
('9999000001', 'Andi Saputra', 87.90, 'A', 'Sangat Memuaskan'),
('9999000002', 'Budi Santoso', 72.50, 'B', 'Memuaskan'),
('9999000003', 'Citra Lestari', 95.00, 'A', 'Sangat Memuaskan'),
('9999000004', 'Dewi Anggraini', 58.00, 'C', 'Cukup'),
('9999000005', 'Eko Prasetyo', 43.75, 'D', 'Kurang'),
('9999000006', 'Fitri Handayani', 80.50, 'B', 'Memuaskan'),
('9999000007', 'Gilang Ramadhan', 34.50, 'E', 'Tidak Lulus'),
('9999000008', 'Hana Putri', 90.25, 'A', 'Sangat Memuaskan'),
('9999000009', 'Indra Wijaya', 66.00, 'B', 'Memuaskan'),
('9999000010', 'Joko Susilo', 50.50, 'D', 'Kurang'),
('9999000011', 'Kartika Sari', 76.25, 'B', 'Memuaskan'),
('9999000012', 'Lina Marlina', 60.50, 'C', 'Cukup'),
('9999000013', 'Maya Permata', 83.00, 'A', 'Sangat Memuaskan'),
('9999000014', 'Nanda Fajar', 29.00, 'E', 'Tidak Lulus'),
('9999000015', 'Oki Setiawan', 72.50, 'B', 'Memuaskan'),
('9999000016', 'Putri Amelia', 99.00, 'A', 'Sangat Memuaskan');
