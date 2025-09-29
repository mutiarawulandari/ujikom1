<?php
include '../../config.php';

// Ambil motor yang tersedia + tarif
$result = mysqli_query($conn, "
    SELECT m.*, 
           t.tarif_harian, 
           t.tarif_mingguan, 
           t.tarif_bulanan, 
           u.nama AS pemilik 
    FROM motor m
    JOIN users u ON m.pemilik_id = u.id
    LEFT JOIN tarif_rental t ON m.id = t.motor_id
    WHERE m.status = 'tersedia'
");
?>
<h2>Daftar Motor Tersedia</h2>
<a href="../dashboard.php">⬅ Kembali ke Dashboard</a> | 
<table border="1" cellpadding="8" cellspacing="0">
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
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?= htmlspecialchars($row['merk']) ?></td>
        <td><?= htmlspecialchars($row['tipe_cc']) ?></td>
        <td><?= htmlspecialchars($row['no_plat']) ?></td>
        <td><?= htmlspecialchars($row['pemilik']) ?></td>
        <td>Rp <?= number_format($row['tarif_harian'] ?? 0) ?></td>
        <td>Rp <?= number_format($row['tarif_mingguan'] ?? 0) ?></td>
        <td>Rp <?= number_format($row['tarif_bulanan'] ?? 0) ?></td>
        <td><a href="pesan.php?motor_id=<?= $row['id'] ?>">Pesan</a></td>
    </tr>
    <?php } ?>
</table>
