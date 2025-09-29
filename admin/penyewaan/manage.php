<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../../config.php';

$result = mysqli_query($conn, "
    SELECT p.*, u.nama AS penyewa, m.merk, m.no_plat, t.metode_pembayaran, t.status AS status_transaksi
    FROM penyewaan p
    JOIN users u ON p.penyewaan_id = u.id
    JOIN motor m ON p.motor_id = m.id
    LEFT JOIN transaksi t ON t.penyewaan_id = p.id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Booking</title>
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
    }

    /* Navbar */
    .navbar {
        background: var(--primary); 
        color: var(--white); 
        padding: 15px 20px;
        position: fixed; 
        top: 0; 
        width: 100%; 
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow);
    }
    .navbar h2 { 
        margin: 0; 
        font-size: 20px;
        font-weight: 600;
    }
    .menu-toggle { 
        display: none; 
        background: none;
        border: none;
        color: var(--white);
        font-size: 20px;
        cursor: pointer;
    }

    /* Sidebar */
    .sidebar {
        position: fixed; 
        top: 60px; 
        left: 0;
        width: 250px; 
        height: calc(100vh - 60px);
        background: var(--secondary); 
        padding-top: 20px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar ul { 
        list-style: none; 
    }
    .sidebar ul li { 
        margin: 5px 0; 
    }
    .sidebar ul li a {
        display: flex;
        align-items: center;
        padding: 15px 20px; 
        color: var(--white);
        text-decoration: none; 
        transition: var(--transition);
    }
    .sidebar ul li a:hover { 
        background: #34495e; 
    }
    .sidebar ul li a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    .logout { 
        background: var(--danger); 
        margin-top: 20px;
    }
    .logout:hover { 
        background: #c0392b; 
    }

    /* Main Content */
    .main-content {
        margin-left: 250px; 
        margin-top: 60px; 
        padding: 30px;
    }
    
    .header {
        background: var(--white); 
        padding: 25px; 
        border-radius: 10px;
        box-shadow: var(--shadow);
        border-left: 5px solid var(--primary);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header h2 {
        color: var(--primary);
        font-size: 24px;
        margin: 0;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn { 
        padding: 10px 16px; 
        border-radius: 6px; 
        text-decoration: none; 
        cursor: pointer;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
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
    
    .btn-secondary {
        background: var(--gray);
        color: var(--white);
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .btn-success { 
        background: var(--success); 
        color: var(--white);
    }
    
    .btn-success:hover {
        background: #218838;
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

    .card {
        background: var(--white); 
        padding: 25px; 
        border-radius: 10px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }
    
    .card h3 {
        color: var(--primary);
        font-size: 18px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }
    
    .card h3 i {
        margin-right: 10px;
    }

    .msg { 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .msg i {
        margin-right: 10px;
        font-size: 18px;
    }

    .msg-success { 
        background: #e6ffed; 
        color: #0b6b2d;
        border-left: 4px solid var(--success);
    }

    .msg-error { 
        background: #ffe6e6; 
        color: #8b1d1d;
        border-left: 4px solid var(--danger);
    }

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
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-dibayar {
        background: #d4edda;
        color: #155724;
    }
    
    .status-disewa {
        background: #cce5ff;
        color: #004085;
    }
    
    .status-selesai {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .payment-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .payment-success {
        background: #d4edda;
        color: #155724;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
        }
        .sidebar.active {
            left: 0;
        }
        .main-content {
            margin-left: 0;
        }
        .menu-toggle {
            display: block;
        }
        .header {
            flex-direction: column;
            gap: 15px;
        }
        .action-buttons {
            flex-direction: column;
            width: 100%;
        }
        .btn {
            width: 100%;
            justify-content: center;
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
    <!-- Navbar -->
    <nav class="navbar">
        <h2><i class="fas fa-clipboard-list"></i> Manajemen Booking</h2>
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="../users/index.php"><i class="fas fa-users"></i> Kelola Users</a></li>
            <li><a href="../motors/index.php"><i class="fas fa-motorcycle"></i> Kelola Motor</a></li>
            <li><a href="../penyewaan/manage.php"><i class="fas fa-clipboard-list"></i> Manajemen Penyewaan</a></li>
            <li><a href="../laporan/index.php"><i class="fas fa-chart-line"></i> Laporan</a></li>
            <li><a href="../tarif/index.php"><i class="fas fa-money-bill-wave"></i> Tarif Rental</a></li>
            <li><a href="../pembayaran/index.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="../../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manajemen Booking</h2>
            <div class="action-buttons">
                <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="msg msg-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php elseif(isset($_SESSION['error'])): ?>
            <div class="msg msg-error">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-table"></i> Data Booking</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Penyewa</th>
                    <th>Motor</th>
                    <th>No Plat</th>
                    <th>Periode</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Aksi</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['penyewa']) ?></td>
                    <td><?= htmlspecialchars($row['merk']) ?></td>
                    <td><?= htmlspecialchars($row['no_plat']) ?></td>
                    <td><?= $row['tanggal_mulai'] ?> - <?= $row['tanggal_selesai'] ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>
                        <span class="status-badge status-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['metode_pembayaran']): ?>
                            <span class="payment-badge payment-<?= $row['status_transaksi'] ?>">
                                <?= ucfirst($row['metode_pembayaran']) ?> - <?= ucfirst($row['status_transaksi']) ?>
                            </span>
                        <?php else: ?>
                            <span class="payment-badge payment-pending">Belum bayar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['status'] == 'pending'): ?>
                            <a href="confirm.php?id=<?= $row['id'] ?>" class="btn btn-success">
                                <i class="fas fa-check"></i> Konfirmasi
                            </a>
                        <?php elseif($row['status'] == 'dibayar'): ?>
                            <a href="start.php?id=<?= $row['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-play"></i> Mulai
                            </a>
                        <?php elseif($row['status'] == 'disewa'): ?>
                            <a href="finish.php?id=<?= $row['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-stop"></i> Selesai
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
    </script>
</body>
</html>