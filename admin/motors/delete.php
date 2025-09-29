<?php
include '../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data motor
    $query = "DELETE FROM motor WHERE id='$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Redirect balik dengan pesan sukses
        header("Location: index.php?pesan=hapus");
        exit;
    } else {
        echo "Gagal menghapus motor: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit;
}
