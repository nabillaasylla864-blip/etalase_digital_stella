<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';
check_role(['super_admin']);

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_admin'])) {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, 'admin')");
        $stmt->execute([
            ':nama'  => $nama,
            ':email' => $email,
            ':pass'  => $pass
        ]);
        header('Location: users.php');
        exit();
    } catch (PDOException $e) {
        $pesan = "Gagal menambahkan admin. Email mungkin sudah digunakan.";
    }
}

if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'super_admin'");
    $stmt->execute([':id' => $_GET['hapus']]);
    header('Location: users.php');
    exit();
}

$users = $pdo->query("SELECT id, nama, email, role FROM users ORDER BY role ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Super Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">
        <h2>Panel Super Admin</h2>
        <span class="brand-sub">Manajemen Pengguna Etalase Stella</span>
    </div>
    <div class="navbar-user">
        <a href="../index.php" class="btn btn-secondary">&larr; Kembali ke Etalase</a>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="container">
    <div class="form-container admin-form-card">
        <h2>Tambah Admin Baru</h2>
        <?php if ($pesan): ?>
            <div class="alert-danger"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama Admin</label>
                <input type="text" name="nama" placeholder="Nama lengkap admin" required>
            </div>
            <div class="form-group">
                <label>Email Admin</label>
                <input type="email" name="email" placeholder="admin2@stella.sch.id" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password awal" required>
            </div>
            <button type="submit" name="tambah_admin" class="btn btn-primary">Tambah Admin</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Daftar Pengguna Terdaftar</h3>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nama']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge-role badge-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                        <td>
                            <?php if ($u['role'] !== 'super_admin'): ?>
                                <a href="users.php?hapus=<?= (int)$u['id'] ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-danger btn-sm">Hapus</a>
                            <?php else: ?>
                                <span class="text-muted">Terkunci</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
