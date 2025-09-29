<?php
session_start();
if ($_SESSION['role'] != 'admin') {
  header("Location: ../login.php");
  exit;
}
include '../../config.php';

// === 1. Total Motor & Motor Disewa ===
$resTotalMotor = mysqli_query($conn, "SELECT COUNT(*) AS total FROM motor");
$totalMotors = mysqli_fetch_assoc($resTotalMotor)['total'];

$resDisewa = mysqli_query($conn, "SELECT COUNT(*) AS total FROM penyewaan WHERE status='disewa'");
$totalDisewa = mysqli_fetch_assoc($resDisewa)['total'];

// === 2. Pendapatan Pemilik & Admin ===
$resPendapatan = mysqli_query($conn, "
    SELECT 
        SUM(bh.bagi_hasil_pemilik) AS total_pemilik,
        SUM(bh.bagi_hasil_admin) AS total_admin
    FROM bagi_hasil bh
");
$rowPendapatan = mysqli_fetch_assoc($resPendapatan);
$totalPemilik = $rowPendapatan['total_pemilik'] ?? 0;
$totalAdmin   = $rowPendapatan['total_admin'] ?? 0;

// === 5. Motor yang sedang disewa ===
$resSedangDisewa = mysqli_query($conn, "
    SELECT m.merk, m.no_plat, u.nama AS penyewa, p.tanggal_mulai, p.tanggal_selesai
    FROM penyewaan p
    JOIN motor m ON p.motor_id = m.id
    JOIN users u ON p.penyewaan_id = u.id
    WHERE p.status='disewa'
");

// === 6. Semua motor ===
$resMotor = mysqli_query($conn, "SELECT * FROM motor ORDER BY id DESC");

// === 8. Riwayat Penyewaan ===
$resRiwayat = mysqli_query($conn, "
    SELECT p.*, u.nama AS penyewa, m.merk, m.no_plat
    FROM penyewaan p
    JOIN users u ON p.penyewaan_id = u.id
    JOIN motor m ON p.motor_id = m.id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Admin - Sistem Rental Motor</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    /* Stats Cards */
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
        margin-bottom: 30px;
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
    
    img {
        max-width: 80px;
        border-radius: 4px;
    }
    
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

    /* Chart Container */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
        margin-bottom: 20px;
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
    <!-- Navbar -->
    <nav class="navbar">
        <h2><i class="fas fa-chart-line"></i> Laporan Admin</h2>
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
            <h2>Laporan Admin</h2>
            <div class="action-buttons">
                <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-motorcycle"></i>
                <h3>Total Motor</h3>
                <div class="stat-number"><?= $totalMotors ?></div>
            </div>
            <div class="stat-box">
                <i class="fas fa-clipboard-check"></i>
                <h3>Motor Disewa</h3>
                <div class="stat-number"><?= $totalDisewa ?></div>
            </div>
            <div class="stat-box">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Pendapatan Pemilik</h3>
                <div class="stat-number">Rp <?= number_format($totalPemilik, 0, ',', '.') ?></div>
            </div>
            <div class="stat-box">
                <i class="fas fa-coins"></i>
                <h3>Pendapatan Admin</h3>
                <div class="stat-number">Rp <?= number_format($totalAdmin, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Grafik Pendapatan -->
        <div class="card">
            <h3><i class="fas fa-chart-pie"></i> Perbandingan Pendapatan</h3>
            <div class="chart-container">
                <canvas id="pendapatanChart"></canvas>
            </div>
        </div>

        <!-- Grafik Status Motor -->
        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Status Motor</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Motor yang Sedang Disewa -->
        <div class="card">
            <h3><i class="fas fa-clipboard-list"></i> Motor yang Sedang Disewa</h3>
            <table>
                <tr>
                    <th>Merk</th>
                    <th>No Plat</th>
                    <th>Penyewa</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resSedangDisewa)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['merk']) ?></td>
                        <td><?= htmlspecialchars($row['no_plat']) ?></td>
                        <td><?= htmlspecialchars($row['penyewa']) ?></td>
                        <td><?= $row['tanggal_mulai'] ?></td>
                        <td><?= $row['tanggal_selesai'] ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Daftar Motor Terdaftar -->
        <div class="card">
            <h3><i class="fas fa-motorcycle"></i> Daftar Motor Terdaftar</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Merk</th>
                    <th>No Plat</th>
                    <th>Status</th>
                    <th>Foto</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resMotor)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['merk']) ?></td>
                        <td><?= htmlspecialchars($row['no_plat']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['photo']) { ?>
                                <img src="../../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Motor">
                            <?php } else { 
                                echo "<i class='fas fa-image' style='font-size: 24px; color: #ccc;'></i>"; 
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Riwayat Penyewaan -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Riwayat Penyewaan</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Penyewa</th>
                    <th>Motor</th>
                    <th>Plat</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Status</th>
                    <th>Harga</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($resRiwayat)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['penyewa']) ?></td>
                        <td><?= htmlspecialchars($row['merk']) ?></td>
                        <td><?= htmlspecialchars($row['no_plat']) ?></td>
                        <td><?= $row['tanggal_mulai'] ?></td>
                        <td><?= $row['tanggal_selesai'] ?></td>
                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    // Grafik Pendapatan
    const pendapatanCtx = document.getElementById('pendapatanChart').getContext('2d');
    const pendapatanChart = new Chart(pendapatanCtx, {
        type: 'pie',
        data: {
            labels: ['Pendapatan Pemilik', 'Pendapatan Admin'],
            datasets: [{
                data: [<?= $totalPemilik ?>, <?= $totalAdmin ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Grafik Status Motor
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Tersedia', 'Disewa'],
            datasets: [{
                label: 'Jumlah Motor',
                data: [<?= $totalMotors - $totalDisewa ?>, <?= $totalDisewa ?>],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    </script>
</body>
</html>