<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");  
    exit;
}
include '../../config.php';

$result = mysqli_query($conn, "SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen User</title>
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
    <h2><i class="fas fa-users"></i> Manajemen User</h2>
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
        <h2>Manajemen User</h2>
        <div class="action-buttons">
            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah User</a>
            <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-table"></i> Data User</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telp</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['no_tlpn']) ?></td>
                <td>
                    <?php 
                    $roleClass = '';
                    if ($row['role'] == 'admin') $roleClass = 'badge-danger';
                    elseif ($row['role'] == 'pemilik') $roleClass = 'badge-warning';
                    else $roleClass = 'badge-info';
                    ?>
                    <span class="badge <?= $roleClass ?>"><?= ucfirst($row['role']) ?></span>
                </td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning" style="padding: 6px 10px; font-size: 12px;">
                        <i class="fas fa-edit"></i> Edit
                    </a> 
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px;" onclick="return confirm('Yakin hapus user ini?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
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