<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
check_login();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    die("Data karya tidak ditemukan.");
}

if ($_SESSION['role'] === 'siswa' && $project['user_id'] != $_SESSION['user_id']) {
    die("Akses Ditolak: Anda tidak dapat mengedit karya milik siswa lain.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');
    $gambar_nama  = $project['gambar'];

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($link_project, FILTER_VALIDATE_URL)) {
        $error = "Format Link Project tidak valid!";
    } else {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 2 * 1024 * 1024;

            if (!in_array($ext, $allowed_ext, true)) {
                $error = "Format gambar harus JPG, JPEG, PNG, atau WEBP!";
            } elseif ($_FILES['gambar']['size'] > $max_size) {
                $error = "Ukuran gambar maksimal 2MB!";
            } else {
                $new_name = uniqid('project_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $new_name)) {
                    if (!empty($project['gambar']) && file_exists('uploads/' . $project['gambar'])) {
                        unlink('uploads/' . $project['gambar']);
                    }
                    $gambar_nama = $new_name;
                } else {
                    $error = "Gagal mengunggah gambar baru.";
                }
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare("UPDATE projects
                                  SET nama_project = :nama, deskripsi = :desk, gambar = :img, link_project = :link
                                  WHERE id = :id");
            $stmt->execute([
                ':nama' => $nama_project,
                ':desk' => $deskripsi,
                ':img'  => $gambar_nama,
                ':link' => $link_project,
                ':id'   => $id
            ]);
            header('Location: index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-container">
    <a href="index.php" class="btn btn-secondary btn-sm" style="margin-bottom: 15px;">&larr; Batal</a>
    <h2>Edit Karya Digital</h2>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_project">Nama Karya</label>
            <input type="text" id="nama_project" name="nama_project" value="<?= htmlspecialchars($project['nama_project']) ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" required><?= htmlspecialchars($project['deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="link_project">Link Project</label>
            <input type="url" id="link_project" name="link_project" value="<?= htmlspecialchars($project['link_project']) ?>" required>
        </div>

        <div class="form-group">
            <label>Gambar Saat Ini</label>
            <div class="current-img-box">
                <img src="uploads/<?= htmlspecialchars($project['gambar']) ?>" alt="Gambar Saat Ini">
            </div>
        </div>

        <div class="form-group">
            <label for="gambar">Ganti Gambar (Opsional, Maks. 2MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp">
        </div>

        <button type="submit" class="btn btn-warning btn-block">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>
