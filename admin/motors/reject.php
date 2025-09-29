<?php
include '../../config.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($conn, "UPDATE motor SET status='ditolak' WHERE id=$id");
}

header("Location: index.php");
exit;