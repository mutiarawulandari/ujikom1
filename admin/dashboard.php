<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");  
    exit;
}

include '../config.php'; // koneksi database

// === 1. Total Users ===
$resUsers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalUsers = mysqli_fetch_assoc($resUsers)['total'];

// === 2. Motor Tersedia ===
$resMotor = mysqli_query($conn, "SELECT COUNT(*) AS total FROM motor WHERE status='tersedia'");
$motorTersedia = mysqli_fetch_assoc($resMotor)['total'];

// === 3. Penyewaan Aktif ===
$resPenyewaan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM penyewaan WHERE status='disewa'");
$penyewaanAktif = mysqli_fetch_assoc($resPenyewaan)['total'];

// === 4. Pendapatan Pemilik Hari Ini ===
$resPendapatanPemilik = mysqli_query($conn, "
    SELECT COALESCE(SUM(bh.bagi_hasil_pemilik),0) AS total_pemilik
    FROM bagi_hasil bh
    WHERE DATE(bh.tanggal) = CURDATE()
");
$rowPendapatanPemilik = mysqli_fetch_assoc($resPendapatanPemilik);
$totalPemilikHariIni = $rowPendapatanPemilik['total_pemilik'] ?? 0;

// === 5. Grafik Penyewaan 7 Hari Terakhir ===
// Generate 7 hari terakhir
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Ambil data penyewaan untuk 7 hari terakhir
$resGrafik = mysqli_query($conn, "
    SELECT DATE(tanggal_mulai) AS tgl, COUNT(*) AS jumlah
    FROM penyewaan
    WHERE tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal_mulai)
");

// Buat array asosiatif dengan tanggal sebagai key
$dataByDate = [];
while ($row = mysqli_fetch_assoc($resGrafik)) {
    $dataByDate[$row['tgl']] = $row['jumlah'];
}

// Siapkan labels dan data untuk grafik
$labels = [];
$dataGrafik = [];
foreach ($dates as $date) {
    // Format tanggal menjadi dd/mm
    $labels[] = date('d/m', strtotime($date));
    // Jika ada data untuk tanggal ini, gunakan, jika tidak 0
    $dataGrafik[] = $dataByDate[$date] ?? 0;
}

// === 6. Data Motor dengan Gambar ===
$resMotorWithImages = mysqli_query($conn, "
    SELECT id, merk, tipe_cc, no_plat, photo, status 
    FROM motor 
    ORDER BY id DESC 
    LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5;
        }

        /* Navbar */
        .navbar {
            background: #0d47a1; 
            color: white; 
            padding: 15px 20px;
            position: fixed; 
            top: 0; 
            width: 100%; 
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            color: white;
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
            background: #2c3e50; 
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
            color: white;
            text-decoration: none; 
            transition: background 0.3s;
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
            background: #e74c3c; 
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
        .welcome {
            background: white; 
            padding: 25px; 
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 25px;
            border-left: 5px solid #0d47a1;
        }
        .welcome h1 {
            color: #0d47a1;
            font-size: 24px;
            margin-bottom: 8px;
        }
        .welcome p {
            color: #666;
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
            background: white; 
            padding: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center; 
            border-top: 4px solid #0d47a1;
            transition: transform 0.3s ease;
        }
        .stat-box:hover {
            transform: translateY(-5px);
        }
        .stat-box h3 { 
            color: #666; 
            font-size: 14px; 
            margin-bottom: 10px; 
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-number { 
            font-size: 28px; 
            font-weight: bold; 
            color: #333;
        }

        /* Motor Gallery */
        .motor-gallery {
            background: white; 
            padding: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 30px;
        }
        .motor-gallery h3 {
            color: #0d47a1;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .motor-gallery h3 i {
            margin-right: 10px;
        }
        .motor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .motor-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .motor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .motor-image {
            height: 150px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .motor-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .motor-info {
            padding: 15px;
        }
        .motor-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        .motor-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .motor-status {
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

        /* Chart */
        .chart-container {
            background: white; 
            padding: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 30px;
        }
        .chart-container h3 { 
            margin-bottom: 20px; 
            color: #333;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .chart-container h3 i {
            margin-right: 10px;
        }
        
        /* Tambahan untuk chart */
        .chart-wrapper {
            position: relative;
            height: 300px;
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
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .motor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .chart-wrapper {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h2>
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="users/index.php"><i class="fas fa-users"></i> Kelola Users</a></li>
            <li><a href="motors/index.php"><i class="fas fa-motorcycle"></i> Kelola Motor</a></li>
            <li><a href="penyewaan/manage.php"><i class="fas fa-clipboard-list"></i> Manajemen Penyewaan</a></li>
            <li><a href="laporan/index.php"><i class="fas fa-chart-line"></i> Laporan</a></li>
            <li><a href="tarif/index.php"><i class="fas fa-money-bill-wave"></i> Tarif Rental</a></li>
            <li><a href="pembayaran/index.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome">
            <h1>Selamat datang Admin, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>
            <p>Kelola sistem rental motor Anda dengan mudah.</p>
        </div>

        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-box">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <div class="stat-number"><?= $totalUsers; ?></div>
            </div>
            <div class="stat-box">
                <h3><i class="fas fa-motorcycle"></i> Motor Tersedia</h3>
                <div class="stat-number"><?= $motorTersedia; ?></div>
            </div>
            <div class="stat-box">
                <h3><i class="fas fa-clipboard-check"></i> Penyewaan Aktif</h3>
                <div class="stat-number"><?= $penyewaanAktif; ?></div>
            </div>
            <div class="stat-box">
                <h3><i class="fas fa-money-bill-wave"></i> Pendapatan Pemilik Hari Ini</h3>
                <div class="stat-number">Rp <?= number_format($totalPemilikHariIni, 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- Motor Gallery -->
        <div class="motor-gallery">
            <h3><i class="fas fa-images"></i> Galeri Motor</h3>
            <div class="motor-grid">
                <?php if (mysqli_num_rows($resMotorWithImages) > 0): ?>
                    <?php while ($motor = mysqli_fetch_assoc($resMotorWithImages)): ?>
                        <div class="motor-card">
                            <div class="motor-image">
                                <?php if ($motor['photo']): ?>
                                    <img src="../uploads/<?= htmlspecialchars($motor['photo']) ?>" alt="<?= htmlspecialchars($motor['merk']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-motorcycle" style="font-size: 48px; color: #ccc;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="motor-info">
                                <div class="motor-name"><?= htmlspecialchars($motor['merk']) ?></div>
                                <div class="motor-details"><?= htmlspecialchars($motor['tipe_cc']) ?> - <?= htmlspecialchars($motor['no_plat']) ?></div>
                                <span class="motor-status status-<?= $motor['status'] ?>"><?= ucfirst($motor['status']) ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Belum ada data motor</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <h3><i class="fas fa-chart-bar"></i> Grafik Penyewaan 7 Hari Terakhir</h3>
            <div class="chart-wrapper">
                <canvas id="rentalChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Script Chart -->
    <script>
    // Toggle Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    // Chart Configuration
    const ctx = document.getElementById('rentalChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Jumlah Penyewaan',
                data: <?= json_encode($dataGrafik) ?>,
                backgroundColor: 'rgba(13, 71, 161, 0.6)',
                borderColor: 'rgba(13, 71, 161, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1 // Pastikan sumbu Y menampilkan bilangan bulat
                    },
                    title: { 
                        display: true, 
                        text: 'Jumlah Penyewaan',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    title: { 
                        display: true, 
                        text: 'Tanggal',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    },
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return `Jumlah Penyewaan: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>