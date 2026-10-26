<?php
// Sesuaikan nilai default ini atau isi environment variable di server hosting.
$servername = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$username = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbname = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'mahasiswa';
$port = (int) (getenv('DB_PORT') !== false ? getenv('DB_PORT') : 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);
mysqli_set_charset($conn, 'utf8mb4');
