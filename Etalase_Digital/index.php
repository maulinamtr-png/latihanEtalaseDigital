<?php
require_once 'cofig/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

// Ambil semua karya beserta nama pembuat
$stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat 
                     FROM projects 
                     JOIN users ON projects.user_id = users.id 
                     ORDER BY projects.created_at DESC");
$projects = $stmt->fetchAll();

$roleLabels = [
    'super_admin' => 'Super Admin',
    'admin' => 'Admin',
    'siswa' => 'Siswa',
];
$roleLabel = $roleLabels[$_SESSION['role']] ?? 'Pengguna';
$projectCount = count($projects);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="dashboard-page">
<main class="dashboard-shell">
    <header class="dashboard-header">
        <a class="dashboard-brand" href="index.php">
            <span class="dashboard-brand-mark"><img src="uploads/logo.png" alt=""></span>
            <span><strong>Etalase Digital</strong><small>STELLA</small></span>
        </a>
        <nav class="dashboard-nav" aria-label="Navigasi dashboard">
            <span class="dashboard-user">
                Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
                <span class="user-role"><?= htmlspecialchars($roleLabel) ?></span>
            </span>
            <a href="project_tambah.php" class="dashboard-button dashboard-button-primary"><span aria-hidden="true">+</span> Tambah karya</a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/index.php" class="dashboard-button dashboard-button-light">Kelola pengguna</a>
        <?php endif; ?>
            <a href="auth/logout.php" class="dashboard-logout">Keluar</a>
        </nav>
    </header>

    <section class="dashboard-hero" aria-label="Selamat datang di dashboard">
        <div class="hero-copy">
            <span class="hero-eyebrow"><span></span> RUANG KARYA DIGITAL</span>
            <h1>Ide hebat layak<br>untuk <em>dilihat.</em></h1>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>. Temukan inspirasi dan bagikan karya digitalmu bersama komunitas Stella.</p>
            <a href="project_tambah.php" class="hero-cta">Bagikan karyamu <span aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="hero-art" aria-hidden="true">
            <span class="hero-orbit hero-orbit-one"></span>
            <span class="hero-orbit hero-orbit-two"></span>
            <span class="hero-glow"></span>
            <div class="hero-logo-card"><img src="uploads/logo.png" alt=""></div>
            <span class="hero-spark hero-spark-one"></span>
            <span class="hero-spark hero-spark-two"></span>
        </div>
        <div class="hero-count"><strong><?= $projectCount ?></strong><span>Karya<br>dibagikan</span></div>
    </section>

    <section class="showcase-section" aria-labelledby="showcase-title">
        <div class="section-heading">
            <div>
                <span class="section-kicker">ETALASE KOMUNITAS</span>
                <h2 id="showcase-title">Karya pilihan</h2>
                <p>Jelajahi ide dan kreativitas dari komunitas Stella.</p>
            </div>
            <span class="showcase-count"><?= $projectCount ?> karya</span>
        </div>
        <?php if ($projects): ?>
            <div class="dashboard-grid">
                <?php foreach($projects as $p): ?>
                    <article class="project-card">
                        <a class="project-cover" href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Buka <?= htmlspecialchars($p['nama_project']) ?>">
                            <img src="uploads/<?= htmlspecialchars($p['gambar']) ?>" alt="Sampul karya <?= htmlspecialchars($p['nama_project']) ?>">
                            <span class="cover-link" aria-hidden="true">&nearr;</span>
                        </a>
                        <div class="project-card-body">
                            <div class="project-card-meta"><span>KARYA DIGITAL</span><span class="meta-dot"></span><span>STELLA</span></div>
                            <h3><?= htmlspecialchars($p['nama_project']) ?></h3>
                            <p class="project-description"><?= htmlspecialchars($p['deskripsi']) ?></p>
                            <div class="project-card-footer">
                                <span class="project-author"><span class="author-avatar"><?= htmlspecialchars(strtoupper(substr($p['pembuat'], 0, 1))) ?></span> <?= htmlspecialchars($p['pembuat']) ?></span>
                                <div class="project-actions">
                                    <a class="project-open" href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer">Lihat karya <span aria-hidden="true">&rarr;</span></a>
                                    <?php if($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $p['user_id']): ?>
                                        <a class="project-edit" href="project_edit.php?id=<?= $p['id'] ?>" aria-label="Edit <?= htmlspecialchars($p['nama_project']) ?>">Edit</a>
                                        <a class="project-delete" href="project_hapus.php?id=<?= $p['id'] ?>" onclick="return confirm('Hapus karya ini?')" aria-label="Hapus <?= htmlspecialchars($p['nama_project']) ?>">Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dashboard-empty">
                <div class="empty-icon" aria-hidden="true"><span>+</span></div>
                <h3>Etalase dimulai dari idemu.</h3>
                <p>Belum ada karya yang dibagikan. Jadilah yang pertama mengisi ruang ini.</p>
                <a href="project_tambah.php" class="dashboard-button dashboard-button-primary">Tambah karya pertama <span aria-hidden="true">&rarr;</span></a>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
