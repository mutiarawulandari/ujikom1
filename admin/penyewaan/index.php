<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../../config.php';

// Ambil daftar penyewaan
$result = mysqli_query($conn, "
    SELECT p.*, u.nama AS penyewa, m.merk, m.no_plat, m.id AS motor_id 
    FROM penyewaan p
    JOIN users u ON p.penyewa_id = u.id
    JOIN motor m ON p.motor_id = m.id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-3">Daftar Booking</h2>
    <a href="../dashboard.php" class="btn btn-secondary mb-3">⬅ Kembali ke Dashboard</a>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-primary text-center">
            <tr>
                <th>Penyewa</th>
                <th>Motor</th>
                <th>Periode</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { 
            // Cek transaksi pembayaran
            $trx = mysqli_query($conn,"SELECT * FROM transaksi WHERE penyewaan_id=".$row['id']." LIMIT 1");
            $trxInfo = "Belum bayar";
            if (mysqli_num_rows($trx) > 0){
                $t = mysqli_fetch_assoc($trx);
                $trxInfo = htmlspecialchars($t['metode_pembayaran'])." - ".htmlspecialchars($t['status']);
            }
        ?>
            <tr>
                <td><?= htmlspecialchars($row['penyewa']) ?></td>
                <td><?= htmlspecialchars($row['merk'])." (".htmlspecialchars($row['no_plat']).")" ?></td>
                <td><?= $row['tanggal_mulai'] ?> - <?= $row['tanggal_selesai'] ?></td>
                <td>Rp<?= number_format($row['harga'],0,',','.') ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= $trxInfo ?></td>
                <td class="text-center">
                    <?php
                    if ($row['status']=='pending' && mysqli_num_rows($trx)>0){ 
                        // Tombol konfirmasi pembayaran
                    ?>
                        <form action="confirm.php" method="POST" style="display:inline;">
                            <input type="hidden" name="penyewaan_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="motor_id" value="<?= $row['motor_id'] ?>">
                            <button class="btn btn-success btn-sm">Konfirmasi</button>
                        </form>
                    <?php } elseif ($row['status']=='disewa'){ ?>
                        <form action="finish.php" method="POST" style="display:inline;">
                            <input type="hidden" name="penyewaan_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="motor_id" value="<?= $row['motor_id'] ?>">
                            <button class="btn btn-primary btn-sm">Selesai</button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
