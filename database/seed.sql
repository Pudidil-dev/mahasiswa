-- Pilih database aplikasi terlebih dahulu. Jalankan setelah setup.sql.
-- NIM contoh berawalan 9999; data dengan NIM yang sudah ada tidak ditimpa.
-- Avatar demo disertakan di assets/img/ agar foto langsung tampil setelah import.
INSERT IGNORE INTO nilaimhsw (nim, namalengkap, file_path, nilaiakhir, nilaihuruf, predikat) VALUES
('9999000001', 'Andi Saputra', 'assets/img/avatar.png', 87.90, 'A', 'Sangat Memuaskan'),
('9999000002', 'Budi Santoso', 'assets/img/avatar2.png', 72.50, 'B', 'Memuaskan'),
('9999000003', 'Citra Lestari', 'assets/img/avatar3.png', 95.00, 'A', 'Sangat Memuaskan'),
('9999000004', 'Dewi Anggraini', 'assets/img/avatar4.png', 58.00, 'C', 'Cukup'),
('9999000005', 'Eko Prasetyo', 'assets/img/avatar5.png', 43.75, 'D', 'Kurang'),
('9999000006', 'Fitri Handayani', 'assets/img/avatar.png', 80.50, 'B', 'Memuaskan'),
('9999000007', 'Gilang Ramadhan', 'assets/img/avatar2.png', 34.50, 'E', 'Tidak Lulus'),
('9999000008', 'Hana Putri', 'assets/img/avatar3.png', 90.25, 'A', 'Sangat Memuaskan'),
('9999000009', 'Indra Wijaya', 'assets/img/avatar4.png', 66.00, 'B', 'Memuaskan'),
('9999000010', 'Joko Susilo', 'assets/img/avatar5.png', 50.50, 'D', 'Kurang'),
('9999000011', 'Kartika Sari', 'assets/img/avatar.png', 76.25, 'B', 'Memuaskan'),
('9999000012', 'Lina Marlina', 'assets/img/avatar2.png', 60.50, 'C', 'Cukup'),
('9999000013', 'Maya Permata', 'assets/img/avatar3.png', 83.00, 'A', 'Sangat Memuaskan'),
('9999000014', 'Nanda Fajar', 'assets/img/avatar4.png', 29.00, 'E', 'Tidak Lulus'),
('9999000015', 'Oki Setiawan', 'assets/img/avatar5.png', 72.50, 'B', 'Memuaskan'),
('9999000016', 'Putri Amelia', 'assets/img/avatar.png', 99.00, 'A', 'Sangat Memuaskan');
