<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Update transaksi
    mysqli_query($conn, "UPDATE transaksi SET status='berhasil' WHERE penyewaan_id=$id");

    // Update penyewaan
    mysqli_query($conn, "UPDATE penyewaan SET status='disewa' WHERE id=$id");

    // Update motor
    mysqli_query($conn, "UPDATE motor SET status='disewa' WHERE id=(SELECT motor_id FROM penyewaan WHERE id=$id)");

    $_SESSION['success'] = "Booking #$id dikonfirmasi dan motor disewa!";
}

header("Location: manage.php");
exit;