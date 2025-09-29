<?php
$host = "localhost";   // server database
$user = "root";        // username MySQL (default root di XAMPP)
$pass = "";            // password MySQL (kosong di XAMPP default)
$db   = "db_rental_muti";   // nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>