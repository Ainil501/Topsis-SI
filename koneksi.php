<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "si_database";
$port = 3307;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
