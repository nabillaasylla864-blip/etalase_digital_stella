<?php
session_start();
require_once '../config/kuis_db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: ../index.php');
        exit();
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="../assets/images/logo-telkom-schools.png" alt="Logo Telkom Schools" class="auth-logo">
        <span class="auth-badge">Etalase Karya Siswa</span>
        <h2>Masuk ke Akun</h2>
        <p class="auth-subtitle">Silakan masuk untuk melihat dan mengunggah karya digital.</p>

        <?php if ($error): ?>
            <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-control">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="nama@stella.sch.id" required>
            </div>
            <div class="form-control">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun siswa? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</div>
</body>
</html>
