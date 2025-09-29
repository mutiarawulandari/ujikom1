<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../../config.php';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $motor_id = intval($_POST['motor_id']);
    $jenis    = $_POST['jenis_tarif']; // harian, mingguan, bulanan
    $harga    = $_POST['harga'];

    // Pastikan tarif_rental sudah ada untuk motor ini
    $cek = mysqli_query($conn, "SELECT * FROM tarif_rental WHERE motor_id = $motor_id LIMIT 1");

    if (mysqli_num_rows($cek) > 0) {
        // Update tarif sesuai jenis
        $sql = "UPDATE tarif_rental 
                SET tarif_$jenis = '$harga' 
                WHERE motor_id = $motor_id";
    } else {
        // Insert baru, isi hanya jenis yang dipilih
        $harian = $jenis == 'harian' ? $harga : 0;
        $mingguan = $jenis == 'mingguan' ? $harga : 0;
        $bulanan = $jenis == 'bulanan' ? $harga : 0;

        $sql = "INSERT INTO tarif_rental (motor_id, tarif_harian, tarif_mingguan, tarif_bulanan) 
                VALUES ('$motor_id','$harian','$mingguan','$bulanan')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Ambil semua motor
$motors = mysqli_query($conn, "SELECT id, merk, no_plat FROM motor");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah / Update Tarif</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .form-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.15); width:360px; }
        .form-box h2 { text-align:center; margin-bottom:20px; }
        .form-box input, .form-box select { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:8px; }
        .form-box button { width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:8px; cursor:pointer; }
        .form-box button:hover { background:#0056b3; }
        .form-box a { display:block; text-align:center; margin-top:12px; color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Tambah / Update Tarif</h2>
    <form method="post">
        <label>Pilih Motor:</label>
        <select name="motor_id" required>
            <option value="">-- Pilih Motor --</option>
            <?php while ($m = mysqli_fetch_assoc($motors)) { ?>
                <option value="<?= $m['id'] ?>">
                    <?= $m['merk'] ?> - <?= $m['no_plat'] ?>
                </option>
            <?php } ?>
        </select>

        <label>Pilih Jenis Tarif:</label>
        <select name="jenis_tarif" required>
            <option value="">-- Pilih Jenis --</option>
            <option value="harian">Harian</option>
            <option value="mingguan">Mingguan</option>
            <option value="bulanan">Bulanan</option>
        </select>

        <input type="number" name="harga" placeholder="Masukkan Harga" required>

        <button type="submit">Simpan</button>
    </form>
    <a href="index.php">⬅ Kembali</a>
</div>
</body>
</html>