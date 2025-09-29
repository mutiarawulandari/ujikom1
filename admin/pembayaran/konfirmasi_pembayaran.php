<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../../config.php';

// Pastikan ada parameter id
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID pembayaran tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Cek data pembayaran
$query = "SELECT * FROM penyewaan WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Data pembayaran tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$payment = mysqli_fetch_assoc($result);

// Jika tombol konfirmasi ditekan
if (isset($_POST['konfirmasi'])) {
    $update = "UPDATE penyewaan SET status='selesai' WHERE id=$id";
    if (mysqli_query($conn, $update)) {
        $_SESSION['success'] = "Pembayaran berhasil dikonfirmasi!";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . mysqli_error($conn);
    }
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 50px; }
        .card { background: white; padding: 25px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #0d47a1; color: white; }
        .btn-primary:hover { background: #0a3d91; }
        .btn-secondary { background: gray; color: white; margin-left: 10px; }
    </style>
</head>
<body>

<div class="card">
    <h2><i class="fas fa-check-circle"></i> Konfirmasi Pembayaran</h2>
    <p><strong>ID Penyewaan:</strong> <?= $payment['id'] ?></p>
    <p><strong>Status Saat Ini:</strong> <?= ucfirst($payment['status']) ?></p>
    <form method="POST">
        <button type="submit" name="konfirmasi" class="btn btn-primary"
            onclick="return confirm('Yakin ingin mengonfirmasi pembayaran ini?')">
            <i class="fas fa-check"></i> Konfirmasi Pembayaran
        </button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </form>
</div>

</body>
</html>
