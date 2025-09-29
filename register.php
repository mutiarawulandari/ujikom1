<?php
include 'config.php'; // koneksi database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_tlpn  = mysqli_real_escape_string($conn, $_POST['no_tlpn']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email sudah terdaftar!');window.location='register.php';</script>";
    } else {
        $sql = "INSERT INTO users (nama, email, password, no_tlpn, role) 
                VALUES ('$nama','$email','$password','$no_tlpn','$role')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registrasi berhasil, silakan login!');window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            width: 350px;
        }
        .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .register-box input, 
        .register-box select {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
        }
        .register-box input:focus, 
        .register-box select:focus {
            border-color: #007bff;
        }
        .register-box button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
        }
        .register-box button:hover {
            background: #0056b3;
        }
        .register-box p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .register-box a {
            color: #007bff;
            text-decoration: none;
        }
        .register-box a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Register</h2>
        <form method="post">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="no_tlpn" placeholder="No Telepon">
            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="pemilik">Pemilik</option>
                <option value="penyewa">Penyewa</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</body>
</html>