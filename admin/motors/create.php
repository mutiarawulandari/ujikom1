<?php
// owner/motor/create.php

// koneksi
include '../../config.php';

// pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// proteksi: include check_owner jika ada (pastikan path benar)
if (file_exists(__DIR__ . '/../check_owner.php')) {
    include __DIR__ . '/../check_owner.php';
}

// ambil pemilik_id dari session dan validasi
$pemilik_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($pemilik_id <= 0) {
    echo "<p><strong>Error:</strong> Anda belum login sebagai pemilik. Silakan <a href='../../login.php'>login</a> dulu.</p>";
    exit;
}

// pastikan user dengan id ini ada di tabel users (untuk menghindari foreign key error)
$checkUser = mysqli_query($conn, "SELECT id FROM users WHERE id = $pemilik_id LIMIT 1");
if (!$checkUser || mysqli_num_rows($checkUser) == 0) {
    echo "<p><strong>Error:</strong> Data pemilik tidak ditemukan di database. Hubungi admin.</p>";
    exit;
}

// direktori upload (absolute path)
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) {
    // coba buat folder uploads jika belum ada
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        die("Gagal membuat folder upload. Pastikan permission folder web server memungkinkan membuat folder.");
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $merk = mysqli_real_escape_string($conn, $_POST['merk'] ?? '');
    $tipe_cc = mysqli_real_escape_string($conn, $_POST['tipe_cc'] ?? '');
    $no_plat = mysqli_real_escape_string($conn, $_POST['no_plat'] ?? '');

    // handling file photo
    $photoFile = "";
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $origName = basename($_FILES['photo']['name']);
        $safeName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $origName);
        $dest = $uploadDir . $safeName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $errors[] = "Gagal upload foto.";
        } else {
            $photoFile = $safeName;
        }
    }

    // handling file dokumen
    $dokumenFile = "";
    if (!empty($_FILES['dokumen']['name']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
        $origDoc = basename($_FILES['dokumen']['name']);
        $safeDoc = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $origDoc);
        $destDoc = $uploadDir . $safeDoc;
        if (!move_uploaded_file($_FILES['dokumen']['tmp_name'], $destDoc)) {
            $errors[] = "Gagal upload dokumen.";
        } else {
            $dokumenFile = $safeDoc;
        }
    }

    // jika tidak ada error upload, insert ke DB (prepared statement)
    if (empty($errors)) {
        $sql = "INSERT INTO motor (pemilik_id, merk, tipe_cc, no_plat, photo, dokumen_kepemilikan)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isssss", $pemilik_id, $merk, $tipe_cc, $no_plat, $photoFile, $dokumenFile);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php");
                exit;
            } else {
                // jika gagal, tampilkan error (mis. foreign key)
                $dbErr = mysqli_error($conn);
                mysqli_stmt_close($stmt);
                $errors[] = "Database error: " . htmlspecialchars($dbErr);
            }
        } else {
            $errors[] = "Gagal siapkan query: " . htmlspecialchars(mysqli_error($conn));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Motor</title>
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
        <h2><i class="fas fa-motorcycle"></i> Tambah Motor</h2>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="error-container">
                <?php foreach ($errors as $e): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?= $e ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="merk">Merk Motor</label>
                <input type="text" id="merk" name="merk" required placeholder="Contoh: Honda, Yamaha, Suzuki">
            </div>

            <div class="form-group">
                <label for="tipe_cc">Tipe CC</label>
                <select id="tipe_cc" name="tipe_cc" required>
                    <option value="">Pilih Tipe CC</option>
                    <option value="100">100 cc</option>
                    <option value="125">125 cc</option>
                    <option value="150">150 cc</option>
                </select>
            </div>

            <div class="form-group">
                <label for="no_plat">Nomor Plat</label>
                <input type="text" id="no_plat" name="no_plat" required placeholder="Contoh: B 1234 AB">
            </div>

            <div class="form-group">
                <label>Foto Motor</label>
                <div class="file-input-wrapper">
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <label for="photo" class="file-input-label">
                        <i class="fas fa-image"></i> <span id="photo-label">Pilih file foto...</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Dokumen Kepemilikan (PDF/JPG)</label>
                <div class="file-input-wrapper">
                    <input type="file" id="dokumen" name="dokumen" accept=".pdf,image/*">
                    <label for="dokumen" class="file-input-label">
                        <i class="fas fa-file-alt"></i> <span id="dokumen-label">Pilih file dokumen...</span>
                    </label>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update label when file is selected
    document.getElementById('photo').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file foto...';
        document.getElementById('photo-label').textContent = fileName;
    });
    
    document.getElementById('dokumen').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'Pilih file dokumen...';
        document.getElementById('dokumen-label').textContent = fileName;
    });
</script>
</body>
</html>