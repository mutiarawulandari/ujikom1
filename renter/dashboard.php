<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyewa') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Penyewa - Sistem Penyewaan Motor">
    <title>Dashboard Penyewa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6fdc;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .header h1 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .header p {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .header strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .menu-item {
            background: white;
            padding: 35px 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark-color);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .menu-item::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
            transform-origin: right;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .menu-item:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .menu-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            display: block;
            transition: var(--transition);
        }

        .menu-item:hover .menu-icon {
            transform: scale(1.1);
        }

        .menu-item h3 {
            margin-bottom: 12px;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .menu-item p {
            color: var(--secondary-color);
            font-size: 1rem;
            line-height: 1.5;
        }

        .menu-wide {
            grid-column: 1 / -1;
        }

        .logout {
            text-align: center;
            margin-top: 20px;
        }

        .logout a {
            background: var(--danger-color);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .logout a:hover {
            background: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(220, 53, 69, 0.2);
        }

        @media (max-width: 768px) {
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .menu {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .menu-item {
                padding: 25px 20px;
            }
            
            .menu-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard Penyewa</h1>
            <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong></p>
        </div>

        <div class="menu">
            <a href="penyewaan/index.php" class="menu-item" aria-label="Cari dan Pesan Motor">
                <span class="menu-icon"><i class="fas fa-motorcycle"></i></span>
                <h3>Cari & Pesan Motor</h3>
                <p>Lihat dan pesan motor yang tersedia</p>
            </a>

            <a href="penyewaan/history.php" class="menu-item" aria-label="Riwayat Penyewaan">
                <span class="menu-icon"><i class="fas fa-history"></i></span>
                <h3>Riwayat Penyewaan</h3>
                <p>Lihat riwayat penyewaan Anda</p>
            </a>

            <a href="laporan/pembayaran.php" class="menu-item menu-wide" aria-label="Riwayat Pembayaran">
                <span class="menu-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <h3>Riwayat Pembayaran</h3>
                <p>Lihat riwayat pembayaran Anda</p>
            </a>
        </div>

        <div class="logout">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</body>
</html>