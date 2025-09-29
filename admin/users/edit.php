<?php
include '../../config.php';

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $no_tlpn = mysqli_real_escape_string($conn, $_POST['no_tlpn']);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama='$nama', email='$email', no_tlpn='$no_tlpn', password='$password' WHERE id=$id";
    } else {
        $sql = "UPDATE users SET nama='$nama', email='$email', no_tlpn='$no_tlpn' WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #eef3f7;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    .card {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        max-width: 500px;
        width: 100%;
    }
    h2 {
        margin-bottom: 20px;
        color: #2065d4;
        text-align: center;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
        color: #333;
    }
    .form-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: border .2s, box-shadow .2s;
    }
    .form-group input:focus {
        border-color: #2065d4;
        box-shadow: 0 0 0 3px rgba(32,101,212,0.2);
        outline: none;
    }
    .password-hint {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
        font-style: italic;
    }
    .btn {
        display: block;
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: background .2s;
    }
    .btn-submit {
        background: #2065d4;
        color: #fff;
    }
    .btn-submit:hover {
        background: #1651ad;
    }
    .btn-back {
        background: #6c757d;
        color: #fff;
        text-align: center;
        text-decoration: none;
        line-height: 42px;
        display: block;
    }
    .btn-back:hover {
        background: #5a6268;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Edit User</h2>
    <form method="post">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= $user['nama'] ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required>
        </div>
        <div class="form-group">
            <label for="no_tlpn">Nomor Telepon</label>
            <input type="text" id="no_tlpn" name="no_tlpn" value="<?= $user['no_tlpn'] ?>">
        </div>
        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ganti password">
            <div class="password-hint">Biarkan kosong jika tidak ingin mengubah password</div>
        </div>
        <button type="submit" class="btn btn-submit">Update User</button>
    </form>
    <a href="index.php" class="btn btn-back">⬅ Kembali</a>
</div>

</body>
</html>
