<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_tlpn  = mysqli_real_escape_string($conn, $_POST['no_tlpn']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (nama,email,password,no_tlpn,role) 
            VALUES ('$nama','$email','$password','$no_tlpn','$role')";
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
<title>Tambah User</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #dbeafe, #eef3f7);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 500px;
        overflow: hidden;
        animation: fadeIn .6s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-header {
        background: #2065d4;
        color: #fff;
        padding: 25px;
        text-align: center;
    }
    .card-header h1 {
        font-size: 24px;
        margin: 0 0 5px;
    }
    .card-header p {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
    }
    .card-body {
        padding: 30px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: all .2s ease;
    }
    .form-group input:focus,
    .form-group select:focus {
        border-color: #2065d4;
        box-shadow: 0 0 0 3px rgba(32,101,212,0.2);
        outline: none;
    }
    .btn {
        display: block;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: background .2s ease, transform .2s ease;
    }
    .btn-primary {
        background: #2065d4;
        color: #fff;
    }
    .btn-primary:hover {
        background: #1651ad;
        transform: translateY(-2px);
    }
    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #2065d4;
        text-decoration: none;
        font-weight: 600;
        transition: color .2s ease;
    }
    .back-link:hover {
        text-decoration: underline;
        color: #103b77;
    }
</style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h1>Tambah Pengguna</h1>
        <p>Isi data pengguna baru</p>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label for="no_tlpn">Nomor Telepon</label>
                <input type="text" id="no_tlpn" name="no_tlpn" placeholder="Masukkan nomor telepon">
            </div>
            <div class="form-group">
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="pemilik">Pemilik</option>
                    <option value="penyewa">Penyewa</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
        </form>
        <a href="index.php" class="back-link">⬅ Kembali ke Daftar Pengguna</a>
    </div>
</div>

</body>
</html>
