<?php
session_start();
include '../config.php';

// pastikan hanya pemilik yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pemilik') {
    header("Location: ../login.php");
    exit;
}

$pemilikId = (int) $_SESSION['user_id'];


// Debug: Tampilkan ID pemilik (bisa dihapus jika sudah tidak diperlukan)
// echo "<!-- Debug: pemilikId = " . $pemilikId . " -->";

// === Statistik ===
$totalMotor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM motor WHERE pemilik_id=$pemilikId"))['jml'];
$tersedia   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM motor WHERE pemilik_id=$pemilikId AND status='tersedia'"))['jml'];
$disewa     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM motor WHERE pemilik_id=$pemilikId AND status='disewa'"))['jml'];
$pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(b.bagi_hasil_pemilik),0) AS total
    FROM bagi_hasil b
    JOIN penyewaan p ON b.penyewaan_id = p.id
    JOIN motor m ON p.motor_id = m.id
    WHERE m.pemilik_id = $pemilikId
"))['total'];

// === History Bagi Hasil ===
$history = mysqli_query($conn, "
    SELECT p.id AS penyewaan_id, m.merk, m.no_plat, 
           p.tanggal_mulai, p.tanggal_selesai, 
           b.bagi_hasil_pemilik
    FROM bagi_hasil b
    JOIN penyewaan p ON b.penyewaan_id = p.id
    JOIN motor m ON p.motor_id = m.id
    WHERE m.pemilik_id = $pemilikId
    ORDER BY p.tanggal_mulai DESC
");

// === Motor yang sedang disewa ===
$motorDisewa = mysqli_query($conn, "
    SELECT m.merk, m.no_plat, p.tanggal_mulai, p.tanggal_selesai, p.harga
    FROM penyewaan p
    JOIN motor m ON p.motor_id = m.id
    WHERE m.pemilik_id = $pemilikId AND p.status='disewa'
    ORDER BY p.tanggal_mulai DESC
");

// === Daftar Motor Pemilik ===
$motorList = mysqli_query($conn, "
    SELECT m.*, t.tarif_harian, t.tarif_mingguan, t.tarif_bulanan
    FROM motor m
    LEFT JOIN tarif_rental t ON m.id = t.motor_id
    WHERE m.pemilik_id = $pemilikId
    ORDER BY m.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Pemilik</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #0d47a1;
        --primary-dark: #0a3d91;
        --secondary: #2c3e50;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #343a40;
        --gray: #6c757d;
        --white: #ffffff;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background: linear-gradient(135deg, #f5f7fa 0%, #e4eff9 100%);
        color: var(--dark);
        padding: 20px;
        min-height: 100vh;
    }

    .container { 
        max-width: 1200px; 
        margin: 0 auto; 
    }

    /* Header */
    .header { 
        background: var(--white); 
        padding: 25px; 
        border-radius: 10px; 
        margin-bottom: 25px;
        box-shadow: var(--shadow);
        border-left: 5px solid var(--primary);
        text-align: center;
    }

    .header h1 {
        color: var(--primary);
        font-size: 28px;
        margin-bottom: 8px;
    }

    .header p {
        color: var(--gray);
        font-size: 16px;
    }

    /* Stats */
    .stats-container {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px; 
        margin-bottom: 30px;
    }
    .stat-box {
        background: var(--white); 
        padding: 20px; 
        border-radius: 10px;
        box-shadow: var(--shadow);
        text-align: center; 
        border-top: 4px solid var(--primary);
        transition: transform 0.3s ease;
    }
    .stat-box:hover {
        transform: translateY(-5px);
    }
    .stat-box h3 { 
        color: var(--gray); 
        font-size: 14px; 
        margin-bottom: 10px; 
        text-transform: uppercase;
        font-weight: 600;
    }
    .stat-number { 
        font-size: 28px; 
        font-weight: bold; 
        color: var(--dark);
    }
    .stat-box i {
        font-size: 32px;
        color: var(--primary);
        margin-bottom: 10px;
    }

    /* Cards */
    .card { 
        background: var(--white); 
        padding: 25px; 
        border-radius: 10px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }
    
    .card h2 {
        color: var(--primary);
        font-size: 20px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }
    
    .card h2 i {
        margin-right: 10px;
    }

    /* Tables */
    table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 15px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    th, td { 
        border: 1px solid #e9ecef; 
        padding: 12px 15px; 
        text-align: left;
        font-size: 14px;
    }
    
    th { 
        background: var(--primary); 
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
    }
    
    tr:nth-child(even){ 
        background: #f8f9fa; 
    }
    
    tr:hover {
        background: #f1f1f1;
    }

    /* Buttons */
    .btn { 
        padding: 8px 16px; 
        border-radius: 6px; 
        text-decoration: none; 
        cursor: pointer;
        font-weight: 600;
        transition: var(--transition);
        display: inline-block;
        border: none;
        font-size: 14px;
    }
    
    .btn i {
        margin-right: 6px;
    }
    
    .btn-primary { 
        background: var(--primary); 
        color: var(--white);
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .btn-warning { 
        background: var(--warning); 
        color: #000;
    }
    
    .btn-warning:hover {
        background: #e0a800;
    }
    
    .btn-danger { 
        background: var(--danger); 
        color: var(--white);
    }
    
    .btn-danger:hover {
        background: #c82333;
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-tersedia {
        background: #d4edda;
        color: #155724;
    }
    
    .status-disewa {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    /* Images */
    img { 
        max-width: 80px; 
        border-radius: 4px;
        object-fit: cover;
    }

    /* Logout */
    .logout-container {
        text-align: center;
        margin-top: 30px;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 20px;
        color: var(--gray);
        font-style: italic;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
        
        table {
            font-size: 13px;
        }
        
        th, td {
            padding: 8px 10px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard Pemilik</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>! Kelola rental motor Anda dengan mudah.</p>
    </div>

    <!-- Statistik -->
    <div class="stats-container">
        <div class="stat-box">
            <i class="fas fa-motorcycle"></i>
            <h3>Total Motor</h3>
            <div class="stat-number"><?= $totalMotor ?></div>
        </div>
        <div class="stat-box">
            <i class="fas fa-check-circle"></i>
            <h3>Tersedia</h3>
            <div class="stat-number"><?= $tersedia ?></div>
        </div>
        <div class="stat-box">
            <i class="fas fa-clipboard-check"></i>
            <h3>Disewa</h3>
            <div class="stat-number"><?= $disewa ?></div>
        </div>
        <div class="stat-box">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Pendapatan</h3>
            <div class="stat-number">Rp <?= number_format($pendapatan,0,',','.') ?></div>
        </div>
    </div>

    <!-- History Bagi Hasil -->
    <div class="card">
        <h2><i class="fas fa-history"></i> History Bagi Hasil</h2>
        <table>
            <tr>
                <th>ID Sewa</th>
                <th>Motor</th>
                <th>No Plat</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Bagian Pemilik</th>
            </tr>
            <?php if (mysqli_num_rows($history)>0): ?>
                <?php while ($row=mysqli_fetch_assoc($history)): ?>
                <tr>
                    <td><?= $row['penyewaan_id'] ?></td>
                    <td><?= htmlspecialchars($row['merk']) ?></td>
                    <td><?= htmlspecialchars($row['no_plat']) ?></td>
                    <td><?= $row['tanggal_mulai'] ?></td>
                    <td><?= $row['tanggal_selesai'] ?></td>
                    <td>Rp <?= number_format($row['bagi_hasil_pemilik'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty-state">Belum ada data</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Motor Sedang Disewa -->
    <div class="card">
        <h2><i class="fas fa-clipboard-list"></i> Motor Sedang Disewa</h2>
        <table>
            <tr>
                <th>Motor</th>
                <th>No Plat</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Harga</th>
            </tr>
            <?php if (mysqli_num_rows($motorDisewa)>0): ?>
                <?php while ($row=mysqli_fetch_assoc($motorDisewa)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['merk']) ?></td>
                    <td><?= htmlspecialchars($row['no_plat']) ?></td>
                    <td><?= $row['tanggal_mulai'] ?></td>
                    <td><?= $row['tanggal_selesai'] ?></td>
                    <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state">Tidak ada motor yang disewa</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Daftar Motor -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0; padding: 0; border: none;"><i class="fas fa-motorcycle"></i> Daftar Motor</h2>
            <a href="motor/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Motor</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Merk</th>
                <th>Tipe CC</th>
                <th>No. Plat</th>
                <th>Status</th>
                <th>Tarif Harian</th>
                <th>Tarif Mingguan</th>
                <th>Tarif Bulanan</th>
                <th>Photo</th>
                <th>Dokumen</th>
                <th>Aksi</th>
            </tr>
            <?php if (mysqli_num_rows($motorList)>0): ?>
                <?php while ($row=mysqli_fetch_assoc($motorList)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['merk']) ?></td>
                    <td><?= htmlspecialchars($row['tipe_cc']) ?></td>
                    <td><?= htmlspecialchars($row['no_plat']) ?></td>
                    <td>
                        <span class="status-badge status-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td><?= $row['tarif_harian'] ? "Rp ".number_format($row['tarif_harian'],0,',','.') : "-" ?></td>
                    <td><?= $row['tarif_mingguan'] ? "Rp ".number_format($row['tarif_mingguan'],0,',','.') : "-" ?></td>
                    <td><?= $row['tarif_bulanan'] ? "Rp ".number_format($row['tarif_bulanan'],0,',','.') : "-" ?></td>
                    <td>
                        <?php if ($row['photo']): ?>
                            <img src='../uploads/<?= htmlspecialchars($row['photo']) ?>' alt="Motor">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 24px; color: #ccc;"></i>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['dokumen_kepemilikan']): ?>
                            <a href='../uploads/<?= htmlspecialchars($row['dokumen_kepemilikan']) ?>' target='_blank' class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="motor/edit.php?id=<?= $row['id'] ?>" class="btn btn-warning" style="padding: 4px 8px; font-size: 12px;">
                            <i class="fas fa-edit"></i> Edit
                        </a> 
                        <a href="motor/delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus motor ini?')" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12" class="empty-state">Belum ada motor</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="logout-container">
        <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
</body>
</html>