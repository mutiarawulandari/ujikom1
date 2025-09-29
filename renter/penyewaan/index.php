<?php
include '../../config.php';

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil motor yang tersedia + tarif
$sql = "
    SELECT m.*, t.tarif_harian, t.tarif_mingguan, t.tarif_bulanan, u.nama AS pemilik 
    FROM motor m
    JOIN users u ON m.pemilik_id = u.id
    LEFT JOIN tarif_rental t ON m.id = t.motor_id
    WHERE m.status = 'tersedia'
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Motor Tersedia</title>
<style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
    h2 { color: #0d47a1; }
    a { margin-right: 15px; text-decoration: none; color: #0d47a1; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #0d47a1; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
    tr:hover { background: #e0e0e0; }
    .btn { padding: 6px 12px; background: #0d47a1; color: white; border-radius: 4px; text-decoration: none; }
    .btn:hover { background: #08306b; }
</style>
</head>
<body>

<h2>Daftar Motor Tersedia</h2>
<a href="../dashboard.php">⬅ Kembali ke Dashboard</a>
<a href="history.php">📜 Riwayat Penyewaan</a>

<table>
    <tr>
        <th>Merk</th>
        <th>Tipe CC</th>
        <th>No Plat</th>
        <th>Pemilik</th>
        <th>Tarif Harian</th>
        <th>Tarif Mingguan</th>
        <th>Tarif Bulanan</th>
        <th>Aksi</th>
    </tr>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['merk']) ?></td>
            <td><?= htmlspecialchars($row['tipe_cc']) ?></td>
            <td><?= htmlspecialchars($row['no_plat']) ?></td>
            <td><?= htmlspecialchars($row['pemilik']) ?></td>
            <td>
                <?= $row['tarif_harian'] !== null ? 'Rp '.number_format($row['tarif_harian'],0,',','.') : '-' ?>
            </td>
            <td>
                <?= $row['tarif_mingguan'] !== null ? 'Rp '.number_format($row['tarif_mingguan'],0,',','.') : '-' ?>
            </td>
            <td>
                <?= $row['tarif_bulanan'] !== null ? 'Rp '.number_format($row['tarif_bulanan'],0,',','.') : '-' ?>
            </td>
            <td>
                <a class="btn" href="pesan.php?motor_id=<?= $row['id'] ?>">Pesan</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" style="text-align:center; padding: 15px;">Tidak ada motor tersedia saat ini.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
        