<?php
session_start();
include '../../config.php';

$pemesanan_id = $_GET['id']; // id penyewaan

// Ambil detail penyewaan
$res = mysqli_query($conn, "
    SELECT p.*, m.merk, m.no_plat, m.photo
    FROM penyewaan p
    JOIN motor m ON p.motor_id = m.id
    WHERE p.id = $pemesanan_id
");
$data = mysqli_fetch_assoc($res);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = $_POST['jumlah'];
    $metode = $_POST['metode_pembayaran'];

    $sql = "INSERT INTO transaksi (penyewaan_id, jumlah, metode_pembayaran, status, tanggal)
            VALUES ('$pemesanan_id', '$jumlah', '$metode', 'pending', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pembayaran berhasil dikirim, menunggu verifikasi admin'); window.location='history.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bayar Penyewaan</title>
</head>
<body>
    <h2>Pembayaran untuk Motor: <?= $data['merk']." - ".$data['no_plat'] ?></h2>
    <form method="post">
        <label>Jumlah:</label><br>
        <input type="number" name="jumlah" value="<?= $data['harga'] ?>" required><br><br>

        <label>Metode Pembayaran:</label><br>
        <select name="metode_pembayaran" required>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="QRIS">QRIS</option>
            <option value="Cash">Cash</option>
        </select><br><br>

        <button type="submit">Bayar</button>
    </form>
</body>
</html>