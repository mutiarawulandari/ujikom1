<?php
// koneksi
include '../../config.php';

// pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil user id dari session
$pemilik_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($pemilik_id <= 0) {
    echo "<p><strong>Error:</strong> Anda belum login sebagai pemilik. Silakan <a href='../../login.php'>login</a> ulang.</p>";
    exit;
}

// Jalankan query
$sql = "SELECT * FROM motor WHERE pemilik_id = $pemilik_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Motor - Pemilik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 20px;
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        a {
            text-decoration: none;
            color: #0d6efd;
            margin-right: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #0d6efd;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        img {
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .btn-danger {
            color: #dc3545;
        }
        .btn-danger:hover {
            color: #a71d2a;
        }
    </style>
</head>
<body>
    <h2>Daftar Motor</h2>
    <a href="../dashboard.php">⬅ Kembali ke Dashboard</a> |
    <a href="create.php">+ Tambah Motor</a>
    <br><br>

    <table>
        <tr>
            <th>ID</th>
            <th>Merk</th>
            <th>Tipe CC</th>
            <th>No. Plat</th>
            <th>Status</th>
            <th>Photo</th>
            <th>Dokumen</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['merk']) ?></td>
            <td><?= htmlspecialchars($row['tipe_cc']) ?></td>
            <td><?= htmlspecialchars($row['no_plat']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <?= $row['photo'] ? "<img src='../../uploads/".htmlspecialchars($row['photo'])."' width='80' alt='photo'>" : "-" ?>
            </td>
            <td>
                <?= $row['dokumen_kepemilikan'] ? "<a href='../../uploads/".htmlspecialchars($row['dokumen_kepemilikan'])."' target='_blank'>Lihat</a>" : "-" ?>
            </td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a class="btn-danger" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus motor ini?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
