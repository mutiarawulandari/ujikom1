<?php
// owner/motor/edit.php
session_start();
include '../../config.php'; // pastikan $conn ada di sini

// cek login & role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// ambil id dari GET atau POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// ambil data motor
$stmt = mysqli_prepare($conn, "SELECT * FROM motor WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$motor = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$motor) {
    $_SESSION['error'] = "Motor tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// izin: pemilik hanya boleh edit motornya sendiri
if ($role === 'pemilik' && intval($motor['pemilik_id']) !== $user_id) {
    $_SESSION['error'] = "Tidak punya izin untuk mengedit motor ini.";
    header("Location: index.php");
    exit;
}

// upload folder (pastikan writable)
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ambil input dan sanitasi ringan
    $merk    = trim($_POST['merk'] ?? $motor['merk']);
    $tipe_cc = trim($_POST['tipe_cc'] ?? $motor['tipe_cc']);
    $no_plat = trim($_POST['no_plat'] ?? $motor['no_plat']);
    $status  = trim($_POST['status'] ?? $motor['status']);

    // handle photo upload (jika ada)
    $photoFile = $motor['photo'];
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $orig = basename($_FILES['photo']['name']);
        $safe = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $orig);
        $dest = $uploadDir . $safe;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            // hapus file lama jika ada
            if (!empty($motor['photo']) && file_exists($uploadDir . $motor['photo'])) {
                @unlink($uploadDir . $motor['photo']);
            }
            $photoFile = $safe;
        } else {
            $errors[] = "Gagal upload foto.";
        }
    }

    // handle dokumen upload (jika ada)
    $dokFile = $motor['dokumen_kepemilikan'];
    if (!empty($_FILES['dokumen']['name']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
        $origd = basename($_FILES['dokumen']['name']);
        $safed = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $origd);
        $destd = $uploadDir . $safed;
        if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $destd)) {
            if (!empty($motor['dokumen_kepemilikan']) && file_exists($uploadDir . $motor['dokumen_kepemilikan'])) {
                @unlink($uploadDir . $motor['dokumen_kepemilikan']);
            }
            $dokFile = $safed;
        } else {
            $errors[] = "Gagal upload dokumen.";
        }
    }

    // jika tidak ada error, lakukan update
    if (empty($errors)) {
        $sql = "UPDATE motor SET merk = ?, tipe_cc = ?, no_plat = ?, status = ?, photo = ?, dokumen_kepemilikan = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $merk, $tipe_cc, $no_plat, $status, $photoFile, $dokFile, $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['success'] = "Motor berhasil diperbarui.";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Motor</title>
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

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4eff9 100%);
            color: var(--dark);
            padding: 20px;
            min-height: 100vh;
        }

        .container { 
            max-width: 800px; 
            margin: 0 auto; 
        }

        .header { 
            background: var(--white); 
            padding: 25px; 
            border-radius: 10px; 
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            color: var(--primary);
            font-size: 24px;
        }

        .card { 
            background: var(--white); 
            padding: 30px; 
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(13, 71, 161, 0.2);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 12px;
            background: var(--light);
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-input-label:hover {
            background: #e9ecef;
        }

        .file-input-label i {
            margin-right: 8px;
            color: var(--primary);
        }

        .current-file {
            display: flex;
            align-items: center;
            margin-top: 8px;
            padding: 8px;
            background: var(--light);
            border-radius: 6px;
            font-size: 14px;
        }

        .current-file img {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }

        .current-file a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .current-file a:hover {
            text-decoration: underline;
        }

        .btn { 
            padding: 12px 24px; 
            border-radius: 6px; 
            text-decoration: none; 
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
            border: none;
            font-size: 16px;
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

        .error-container {
            background: #ffe6e6;
            color: #8b1d1d;
            border-left: 4px solid var(--danger);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error-container p {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }

        .error-container i {
            margin-right: 10px;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2><i class="fas fa-edit"></i> Edit Motor #<?= htmlspecialchars($motor['id']) ?></h2>
        <a href="../dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="error-container">
                <?php foreach($errors as $e): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $motor['id'] ?>">
            
            <div class="form-group">
                <label for="merk">Merk Motor</label>
                <input type="text" id="merk" name="merk" value="<?= htmlspecialchars($motor['merk']) ?>" required placeholder="Contoh: Honda, Yamaha, Suzuki">
            </div>

            <div class="form-group">
                <label for="tipe_cc">Tipe CC</label>
                <select id="tipe_cc" name="tipe_cc" required>
                    <option value="100" <?= $motor['tipe_cc']=='100'?'selected':'' ?>>100 cc</option>
                    <option value="125" <?= $motor['tipe_cc']=='125'?'selected':'' ?>>125 cc</option>
                    <option value="150" <?= $motor['tipe_cc']=='150'?'selected':'' ?>>150 cc</option>
                </select>
            </div>

            <div class="form-group">
                <label for="no_plat">Nomor Plat</label>
                <input type="text" id="no_plat" name="no_plat" value="<?= htmlspecialchars($motor['no_plat']) ?>" required placeholder="Contoh: B 1234 AB">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="pending" <?= $motor['status']=='pending'?'selected':'' ?>>Pending</option>
                    <option value="tersedia" <?= $motor['status']=='tersedia'?'selected':'' ?>>Tersedia</option>
                    <option value="disewa" <?= $motor['status']=='disewa'?'selected':'' ?>>Disewa</option>
                    <option value="perawatan" <?= $motor['status']=='perawatan'?'selected':'' ?>>Perawatan</option>
                    <option value="ditolak" <?= $motor['status']=='ditolak'?'selected':'' ?>>Ditolak</option>
                </select>
            </div>

            <div class="form-group">
                <label>Foto Saat Ini</label>
                <?php if (!empty($motor['photo']) && file_exists(__DIR__ . '/../../uploads/' . $motor['photo'])): ?>
                    <div class="current-file">
                        <img src="../../uploads/<?= htmlspecialchars($motor['photo']) ?>" alt="Motor">
                        <span><?= htmlspecialchars($motor['photo']) ?></span>
                    </div>
                <?php else: ?>
                    <div class="current-file">
                        <i class="fas fa-image" style="font-size: 24px; color: #ccc; margin-right: 10px;"></i>
                        <span>Tidak ada foto</span>
                    </div>
                <?php endif; ?>
                
                <label>Ganti Foto (opsional)</label>
                <div class="file-input-wrapper">
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <label for="photo" class="file-input-label">
                        <i class="fas fa-image"></i> <span id="photo-label">Pilih file foto baru...</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Dokumen Saat Ini</label>
                <?php if (!empty($motor['dokumen_kepemilikan'])): ?>
                    <div class="current-file">
                        <i class="fas fa-file-alt" style="font-size: 24px; color: var(--primary); margin-right: 10px;"></i>
                        <a href="../../uploads/<?= htmlspecialchars($motor['dokumen_kepemilikan']) ?>" target="_blank">Lihat Dokumen</a>
                    </div>
                <?php else: ?>
                    <div class="current-file">
                        <i class="fas fa-file-alt" style="font-size: 24px; color: #ccc; margin-right: 10px;"></i>
                        <span>Tidak ada dokumen</span>
                    </div>
                <?php endif; ?>
                
                <label>Ganti Dokumen (opsional)</label>
                <div class="file-input-wrapper">
                    <input type="file" id="dokumen" name="dokumen" accept=".pdf,image/*">
                    <label for="dokumen" class="file-input-label">
                        <i class="fas fa-file-alt"></i> <span id="dokumen-label">Pilih file dokumen baru...</span>
                    </label>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update label when file is selected
    document.getElementById('photo').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file foto baru...';
        document.getElementById('photo-label').textContent = fileName;
    });
    
    document.getElementById('dokumen').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file dokumen baru...';
        document.getElementById('dokumen-label').textContent = fileName;
    });
</script>
</body>
</html>