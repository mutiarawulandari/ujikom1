<?php
session_start(); // wajib untuk akses $_SESSION
include '../../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak!";
    exit;
}

// Cek apakah ada id motor
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Motor tidak ditemukan!";
    exit;
}

$id = intval($_GET['id']);
$pemilik_id = intval($_SESSION['user_id']);

// Ambil data motor dulu untuk hapus file (opsional)
$result = mysqli_query($conn, "SELECT * FROM motor WHERE id=$id AND pemilik_id=$pemilik_id");
if (mysqli_num_rows($result) == 0) {
    echo "Motor tidak ditemukan atau bukan milik Anda!";
    exit;
}

$motor = mysqli_fetch_assoc($result);

// Hapus file foto jika ada
if ($motor['photo'] && file_exists('../../uploads/'.$motor['photo'])) {
    unlink('../../uploads/'.$motor['photo']);
}

// Hapus dokumen jika ada
if ($motor['dokumen_kepemilikan'] && file_exists('../../uploads/'.$motor['dokumen_kepemilikan'])) {
    unlink('../../uploads/'.$motor['dokumen_kepemilikan']);
}

// Hapus data motor
$sql = "DELETE FROM motor WHERE id=$id AND pemilik_id=$pemilik_id";
if (mysqli_query($conn, $sql)) {
    header("Location: index.php?msg=hapus_sukses");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
