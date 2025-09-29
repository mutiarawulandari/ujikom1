<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../../config.php';

$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM tarif_rental WHERE id=$id");
$tarif = mysqli_fetch_assoc($res);

if (!$tarif) { die("Tarif tidak ditemukan"); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $harian   = $_POST['tarif_harian'];
    $mingguan = $_POST['tarif_mingguan'];
    $bulanan  = $_POST['tarif_bulanan'];

    $sql = "UPDATE tarif_rental SET tarif_harian='$harian', tarif_mingguan='$mingguan', tarif_bulanan='$bulanan' WHERE id=$id";
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
    <title>Edit Tarif</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .form-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.15); width:360px; }
        .form-box h2 { text-align:center; margin-bottom:20px; }
        .form-box input { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:8px; }
        .form-box button { width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:8px; cursor:pointer; }
        .form-box button:hover { background:#0056b3; }
        .form-box a { display:block; text-align:center; margin-top:12px; color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Edit Tarif</h2>
    <form method="post">
        <input type="number" name="tarif_harian" value="<?= $tarif['tarif_harian'] ?>" required>
        <input type="number" name="tarif_mingguan" value="<?= $tarif['tarif_mingguan'] ?>" required>
        <input type="number" name="tarif_bulanan" value="<?= $tarif['tarif_bulanan'] ?>" required>
        <button type="submit">Update</button>
    </form>
    <a href="index.php">⬅ Kembali</a>
</div>
</body>
</html>