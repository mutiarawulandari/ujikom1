<?php
session_start();
include '../../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil data booking
    $q = mysqli_query($conn, "SELECT * FROM penyewaan WHERE id=$id");
    $booking = mysqli_fetch_assoc($q);

    if ($booking) {
        $harga = $booking['harga'];
        $motor_id = $booking['motor_id'];

        $pemilik_share = $harga * 0.7;
        $admin_share   = $harga * 0.3;

        // Update status penyewaan
        mysqli_query($conn, "UPDATE penyewaan SET status='selesai' WHERE id=$id");

        // Motor kembali tersedia
        mysqli_query($conn, "UPDATE motor SET status='tersedia' WHERE id=$motor_id");

        // Catat bagi hasil
        $insert = mysqli_query($conn, "INSERT INTO bagi_hasil 
            (penyewaan_id, bagi_hasil_pemilik, bagi_hasil_admin, tanggal, settled_at) 
            VALUES ($id, $pemilik_share, $admin_share, CURDATE(), NOW())");

        if ($insert) {
            $_SESSION['success'] = "Booking #$id selesai, bagi hasil tercatat!";
        } else {
            $_SESSION['error'] = "Gagal mencatat bagi hasil: " . mysqli_error($conn);
        }
    }
}

header("Location: manage.php");
exit;