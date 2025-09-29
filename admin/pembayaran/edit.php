<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../../config.php';

$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM transaksi WHERE id=$id");
$data = mysqli_fetch_assoc($res);

if (!$data) { die("Data pembayaran tidak ditemukan"); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $sql = "UPDATE transaksi SET status='$status' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pembayaran</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .form-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.15); width:360px; }
        .form-box h2 { text-align:center; margin-bottom:20px; }
        select, button { width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:8px; }
        button { background:#007bff; color:#fff; cursor:pointer; }
        button:hover { background:#0056b3; }
        a { display:block; text-align:center; margin-top:12px; color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Edit Pembayaran</h2>
    <form method="post">
        <label>Status:</label>
        <select name="status" required>
    <option value="pending" <?= $data['status']=='pending'?'selected':'' ?>>Pending</option>
    <option value="lunas" <?= $data['status']=='lunas'?'selected':'' ?>>Lunas</option>
    <option value="invalid" <?= $data['status']=='invalid'?'selected':'' ?>>Invalid</option>
</select>

        <button type="submit">Simpan Perubahan</button>
    </form>
    <a href="index.php">⬅ Kembali</a>
</div>
</body>
</html>