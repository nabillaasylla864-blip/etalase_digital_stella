<?php
session_start();
require_once '../config/kuis_db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $raw_pass = $_POST['password'] ?? '';

    if (strlen($raw_pass) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $password = password_hash($raw_pass, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'siswa')");
            $stmt->execute([
                ':nama' => $nama,
                ':email' => $email,
                ':password' => $password
            ]);
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $error = "Email sudah terdaftar atau terjadi kesalahan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Siswa - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <span class="auth-badge">Pendaftaran Siswa</span>
        <h2>Buat Akun Baru</h2>
        <p class="auth-subtitle">Daftarkan akun siswa untuk memamerkan karya terbaikmu.</p>

        <?php if ($error): ?>
            <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-control">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Contoh: Nadia Pratama" required>
            </div>
            <div class="form-control">
                <label for="email">Email Sekolah / Pribadi</label>
                <input type="email" id="email" name="email" placeholder="siswa@stella.sch.id" required>
            </div>
            <div class="form-control">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">Daftar Akun</button>
        </form>

        <div class="auth-footer">
            Sudah memiliki akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</div>
</body>
</html>
