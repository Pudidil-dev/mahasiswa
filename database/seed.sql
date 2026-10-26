-- Snapshot 9 data asli dari database lokal; NIM, nama, nilai, dan path foto dipertahankan.
-- Pilih database mahasiswa dan jalankan setelah setup.sql.
-- Path foto kosong tetap NULL. File foto terkait disertakan di uploads/mahasiswa/.
-- INSERT IGNORE tidak menimpa data dengan NIM yang sudah terdaftar.
INSERT IGNORE INTO nilaimhsw (nim, namalengkap, file_path, nilaiakhir, nilaihuruf, predikat, created_date, modified_date) VALUES
('3202316001', 'Ferry Faisal', NULL, '70.00', 'B', 'Memuaskan', '2026-10-13 10:59:19', '2026-10-15 14:04:25'),
('3202316002', 'Ferry', NULL, '78.50', 'B', 'Memuaskan', '2026-10-13 10:59:19', '2026-10-13 11:23:35'),
('3202316003', 'Habil', NULL, '60.75', 'B', 'Memuaskan', '2026-10-13 10:59:19', '2026-10-13 11:23:38'),
('3202316004', 'Ikna', 'uploads/mahasiswa/4a166a659c8846e43e83995242a2ed6a.jpg', '87.90', 'A', 'Sangat Memuaskan', '2026-10-13 10:59:19', '2026-10-21 22:32:09'),
('3202316005', 'Aqil', NULL, '77.75', 'B', 'Memuaskan', '2026-10-13 10:59:19', '2026-10-13 11:13:33'),
('3202316006', 'Freky', NULL, '79.82', 'B', 'Memuaskan', '2026-10-13 10:59:19', '2026-10-13 10:59:19'),
('3202316018', 'Fadil Rahman', 'uploads/mahasiswa/a4640af2d3d1baba3d13ee5925c5dd5c.jpg', '88.00', 'A', 'Sangat Memuaskan', '2026-10-13 11:23:57', '2026-10-21 22:31:52'),
('3202316024', 'pudidil', 'uploads/mahasiswa/0e59826562d3796d8e2d52326696b481.png', '99.00', 'A', 'Sangat Memuaskan', '2026-10-13 13:28:45', '2026-10-21 22:31:17'),
('3202316043', 'ryan', NULL, '78.00', 'B', 'Memuaskan', '2026-10-13 14:11:19', '2026-10-13 14:11:19');
