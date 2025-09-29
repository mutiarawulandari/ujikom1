<?php
include '../../config.php';

// Ambil id motor dari URL
$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM motor WHERE id=$id");
$motor = mysqli_fetch_assoc($result);

// Proses update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $merk     = mysqli_real_escape_string($conn, $_POST['merk']);
    $tipe_cc  = mysqli_real_escape_string($conn, $_POST['tipe_cc']);
    $no_plat  = mysqli_real_escape_string($conn, $_POST['no_plat']);

    // Upload photo baru jika ada
    if (!empty($_FILES['photo']['name'])) {
        $photo = time().'_'.$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], '../../uploads/'.$photo);
    } else {
        $photo = $motor['photo'];
    }

    // Upload dokumen baru jika ada
    if (!empty($_FILES['dokumen']['name'])) {
        $dokumen = time().'_'.$_FILES['dokumen']['name'];
        move_uploaded_file($_FILES['dokumen']['tmp_name'], '../../uploads/'.$dokumen);
    } else {
        $dokumen = $motor['dokumen_kepemilikan'];
    }

    // Update database
    $sql = "UPDATE motor SET 
                merk='$merk',
                tipe_cc='$tipe_cc',
                no_plat='$no_plat',
                photo='$photo',
                dokumen_kepemilikan='$dokumen'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error: ".mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Motor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], select, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 6px 0 12px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #0056b3;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #007bff;
            text-decoration: none;
        }
        img {
            max-width: 120px;
            border-radius: 4px;
            margin-top: 6px;
        }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Edit Motor</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Merk:</label>
        <input type="text" name="merk" value="<?= htmlspecialchars($motor['merk']) ?>" required>

        <label>Tipe CC:</label>
        <select name="tipe_cc" required>
            <option value="100" <?= $motor['tipe_cc']=='100'?'selected':'' ?>>100</option>
            <option value="125" <?= $motor['tipe_cc']=='125'?'selected':'' ?>>125</option>
            <option value="150" <?= $motor['tipe_cc']=='150'?'selected':'' ?>>150</option>
        </select>

        <label>No Plat:</label>
        <input type="text" name="no_plat" value="<?= htmlspecialchars($motor['no_plat']) ?>" required>

        <label>Photo Lama:</label>
        <?= $motor['photo'] ? "<img src='../../uploads/".$motor['photo']."' alt='Motor'>" : '-' ?>
        <label>Ganti Photo:</label>
        <input type="file" name="photo">

        <label>Dokumen Lama:</label>
        <?= $motor['dokumen_kepemilikan'] ? "<a href='../../uploads/".$motor['dokumen_kepemilikan']."' target='_blank'>Lihat</a>" : '-' ?>
        <label>Ganti Dokumen:</label>
        <input type="file" name="dokumen">

        <button type="submit">Update Motor</button>
    </form>
    <a href="index.php">Kembali</a>
</div>
</body>
</html>
