<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../../config.php';

$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM transaksi WHERE id=$id");
header("Location: index.php");
exit;