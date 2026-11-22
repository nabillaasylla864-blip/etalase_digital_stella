<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
check_login();

$q = trim($_GET['q'] ?? '');
$mine = isset($_GET['mine']) && $_GET['mine'] === '1';

$sql = "SELECT projects.*, users.nama AS pembuat
        FROM projects
        JOIN users ON projects.user_id = users.id
        WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (projects.nama_project LIKE :q OR projects.deskripsi LIKE :q OR users.nama LIKE :q)";
    $params[':q'] = "%{$q}%";
}

if ($mine) {
    $sql .= " AND projects.user_id = :uid";
    $params[':uid'] = $_SESSION['user_id'];
}

$sql .= " ORDER BY projects.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">
        <img src="assets/images/logo-telkom-schools.png" alt="Logo Telkom Schools" class="brand-logo">
        <div>
            <h2>Etalase Digital Stella</h2>
            <span class="brand-sub">Galeri Karya & Inovasi Siswa</span>
        </div>
    </div>
    <div class="navbar-user">
        <span class="user-pill">
            <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
            <small>(<?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role'])) ?>)</small>
        </span>
        <a href="project_tambah.php" class="btn btn-primary">+ Tambah Karya</a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="btn btn-warning">Kelola Users</a>
        <?php endif; ?>
        <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="container">
    <section class="hero-banner">
        <div>
            <span class="hero-label">Dashboard Etalase Karya</span>
            <h1>Eksplorasi Karya Digital Siswa</h1>
            <p>Temukan proyek web, desain, aplikasi, dan inovasi kreatif buatan siswa Stella.</p>
        </div>
        <div class="hero-stats">
            <span class="stat-icon" aria-hidden="true">&#9733;</span>
            <span class="stat-number"><?= count($projects) ?></span>
            <span class="stat-label">Karya Ditampilkan</span>
        </div>
    </section>

    <section class="toolbar">
        <form method="GET" class="search-form">
            <?php if ($mine): ?>
                <input type="hidden" name="mine" value="1">
            <?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama karya, deskripsi, atau nama siswa...">
            <button type="submit" class="btn btn-primary">Cari</button>
            <?php if ($q !== '' || $mine): ?>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>
        <div class="filter-pills">
            <a href="index.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="pill <?= !$mine ? 'active' : '' ?>">Semua Karya</a>
            <a href="index.php?mine=1<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="pill <?= $mine ? 'active' : '' ?>">Karya Saya</a>
        </div>
    </section>

    <?php if (empty($projects)): ?>
        <div class="empty-state">
            <h3>Belum ada karya yang cocok</h3>
            <p>Mulai bagikan proyek digital pertamamu atau ubah kata kunci pencarian.</p>
            <a href="project_tambah.php" class="btn btn-primary">+ Unggah Karya Baru</a>
        </div>
    <?php else: ?>
        <div class="grid-projects">
            <?php foreach ($projects as $p): ?>
                <article class="card-project">
                    <div class="card-img-wrap">
                        <img src="uploads/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_project']) ?>" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($p['nama_project']) ?></h3>
                        <p class="card-author">Oleh <strong><?= htmlspecialchars($p['pembuat']) ?></strong></p>
                        <p class="card-text"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>

                        <a href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer" class="project-link">
                            Buka Tautan Project &rarr;
                        </a>

                        <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $p['user_id']): ?>
                            <div class="card-actions">
                                <a href="project_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="project_hapus.php?id=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus karya ini?')" class="btn btn-danger btn-sm">Hapus</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
