<?php
include '../../config.php';

// mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// pastikan login sebagai penyewa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../../login.php");
    exit;
}

$penyewa_id = intval($_SESSION['user_id']);
$motor_id   = isset($_GET['motor_id']) ? intval($_GET['motor_id']) : 0;

if ($motor_id <= 0) {
    die("Motor tidak valid.");
}

// Ambil data motor & tarif
$stmt = mysqli_prepare($conn, "
    SELECT m.*, t.tarif_harian, t.tarif_mingguan, t.tarif_bulanan 
    FROM motor m
    LEFT JOIN tarif_rental t ON m.id = t.motor_id
    WHERE m.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $motor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$motor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$motor) {
    die("Motor tidak ditemukan");
}

// fungsi hitung harga
function hitungHarga($tipe, $tarif_harian, $tarif_mingguan, $tarif_bulanan, $mulai, $selesai) {
    $t1 = strtotime($mulai);
    $t2 = strtotime($selesai);
    if ($t2 < $t1) return false;
    $hari = intval(($t2 - $t1) / 86400) + 1;

    if ($tipe == 'harian')  return $hari * $tarif_harian;
    if ($tipe == 'mingguan') return ceil($hari/7) * $tarif_mingguan;
    if ($tipe == 'bulanan')  return ceil($hari/30) * $tarif_bulanan;
    return false;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipe_durasi    = $_POST['tipe_durasi'];
    $tanggal_mulai  = $_POST['tanggal_mulai'];
    $tanggal_selesai= $_POST['tanggal_selesai'];
    $metode_bayar   = $_POST['metode_pembayaran'];

    $harga = hitungHarga($tipe_durasi, 
                         $motor['tarif_harian'], 
                         $motor['tarif_mingguan'], 
                         $motor['tarif_bulanan'], 
                         $tanggal_mulai, 
                         $tanggal_selesai);

    if ($harga === false) {
        $errors[] = "Tanggal tidak valid.";
    }

    if (empty($errors)) {
        // Simpan ke penyewaan
        $sql = "INSERT INTO penyewaan (penyewaan_id, motor_id, tanggal_mulai, tanggal_selesai, tipe_durasi, harga, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iisssd", $penyewa_id, $motor_id, $tanggal_mulai, $tanggal_selesai, $tipe_durasi, $harga);
        if (mysqli_stmt_execute($stmt)) {
            $penyewaan_id = mysqli_insert_id($conn);

            // Simpan transaksi pembayaran
            $sql2 = "INSERT INTO transaksi (penyewaan_id, metode_pembayaran, jumlah, status) 
                     VALUES (?, ?, ?, 'pending')";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "isd", $penyewaan_id, $metode_bayar, $harga);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            echo "<script>alert('Pemesanan berhasil! Menunggu konfirmasi admin.');window.location='history.php';</script>";
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<h2>Pesan Motor</h2>
<p><b>Merk:</b> <?= htmlspecialchars($motor['merk']) ?> | <b>No Plat:</b> <?= htmlspecialchars($motor['no_plat']) ?></p>

<?php if (!empty($errors)): ?>
<div style="color:red;">
    <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
</div>
<?php endif; ?>

<form method="post">
    <label>Tanggal Mulai:</label><br>
    <input type="date" name="tanggal_mulai" required><br><br>

    <label>Tanggal Selesai:</label><br>
    <input type="date" name="tanggal_selesai" required><br><br>

    <label>Pilih Paket:</label><br>
    <select name="tipe_durasi" required>
        <option value="harian">Harian (Rp <?= number_format($motor['tarif_harian']) ?>/hari)</option>
        <option value="mingguan">Mingguan (Rp <?= number_format($motor['tarif_mingguan']) ?>/minggu)</option>
        <option value="bulanan">Bulanan (Rp <?= number_format($motor['tarif_bulanan']) ?>/bulan)</option>
    </select><br><br>

    <label>Metode Pembayaran:</label><br>
    <select name="metode_pembayaran" required>
        <option value="cash">Cash</option>
        <option value="transfer">Transfer Bank</option>
        <option value="ewallet">E-Wallet</option>
    </select><br><br>

    <button type="submit">Pesan & Bayar</button>
</form>
<a href="index.php">Kembali</a>