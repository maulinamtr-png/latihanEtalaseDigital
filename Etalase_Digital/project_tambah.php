<?php
session_start();
require_once 'cofig/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');
    $user_id      = $_SESSION['user_id'];

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua kolom input wajib diisi!";
    } elseif (!filter_var($link_project, FILTER_VALIDATE_URL)) {
        $error = "Format Link Project tidak valid (Gunakan format https://...)!";
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = "Pilih gambar karya digital terlebih dahulu!";
    } else {
        $file_tmp   = $_FILES['gambar']['tmp_name'];
        $file_name  = $_FILES['gambar']['name'];
        $file_size  = $_FILES['gambar']['size'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size    = 2 * 1024 * 1024; // Maksimal 2MB

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Format gambar harus berupa JPG, JPEG, PNG, atau WEBP!";
        } elseif ($file_size > $max_size) {
            $error = "Ukuran berkas gambar tidak boleh lebih dari 2MB!";
        } else {
            $new_file_name = uniqid('project_', true) . '.' . $file_ext;
            $upload_dir    = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project) VALUES (:user_id, :nama_project, :deskripsi, :gambar, :link_project)");
                    $stmt->execute([
                        ':user_id'      => $user_id,
                        ':nama_project' => $nama_project,
                        ':deskripsi'    => $deskripsi,
                        ':gambar'       => $new_file_name,
                        ':link_project' => $link_project
                    ]);

                    header('Location: index.php');
                    exit();
                } catch (PDOException $e) {
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                    $error = "Terjadi kesalahan sistem: " . $e->getMessage();
                }
            } else {
                $error = "Gagal mengunggah berkas gambar ke server.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body class="project-form-page">
<main class="project-form-shell">
    <aside class="project-form-brand">
        <a href="index.php" class="back-link" aria-label="Kembali ke Etalase">
            <span aria-hidden="true">&larr;</span> Kembali ke Etalase
        </a>
        <div class="brand-content">
            <div class="brand-logo-wrap">
                <img src="uploads/logo.png" alt="Logo Telkom" class="form-brand-logo">
            </div>
            <p class="brand-eyebrow">ETALASE DIGITAL STELLA</p>
            <h1>Setiap karya punya cerita.</h1>
            <p class="brand-description">Bagikan karya digitalmu dan biarkan lebih banyak orang menemukan ide di baliknya.</p>
            <div class="brand-accent" aria-hidden="true"><span></span><span></span><span></span></div>
        </div>
        <p class="brand-footer">Ruang untuk berkarya, tumbuh, dan menginspirasi.</p>
    </aside>

    <section class="project-form-panel">
        <div class="form-heading">
            <span class="form-step">KARYA BARU <span aria-hidden="true">/</span> 01</span>
            <h2>Tambah karya digital</h2>
            <p>Lengkapi informasi berikut agar karyamu siap ditampilkan di etalase.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="form-alert" role="alert">
                <span class="alert-icon" aria-hidden="true">!</span>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form class="project-form" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_project">Nama karya <span class="required-mark">*</span></label>
                <input type="text" id="nama_project" name="nama_project" placeholder="Contoh: Aplikasi Belajar Interaktif" value="<?= htmlspecialchars($_POST['nama_project'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Cerita tentang karya ini <span class="required-mark">*</span></label>
                <textarea id="deskripsi" name="deskripsi" placeholder="Ceritakan fungsi, inspirasi, atau hal menarik dari karyamu..." required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                <span class="field-hint">Tulis deskripsi singkat yang membantu orang memahami karyamu.</span>
            </div>

            <div class="form-group">
                <label for="link_project">Link karya <span class="required-mark">*</span></label>
                <div class="input-with-icon">
                    <span aria-hidden="true">↗</span>
                    <input type="url" id="link_project" name="link_project" placeholder="https://contoh.com/karya" value="<?= htmlspecialchars($_POST['link_project'] ?? '') ?>" required>
                </div>
                <span class="field-hint">Gunakan tautan publik, seperti website live atau repositori GitHub.</span>
            </div>

            <div class="form-group upload-group">
                <label for="gambar">Sampul karya <span class="required-mark">*</span></label>
                <label class="upload-dropzone" for="gambar">
                    <span class="upload-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14.5v4A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="upload-copy"><strong>Pilih gambar untuk diunggah</strong><span>JPG, PNG, atau WEBP · Maksimal 2MB</span></span>
                    <span class="upload-action">Pilih file</span>
                    <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                </label>
                <div class="preview-box" id="previewBox">
                    <img id="imagePreview" src="" alt="Pratinjau sampul karya">
                    <span class="preview-name" id="fileName"></span>
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php" class="cancel-button">Batal</a>
                <button type="submit" class="save-button">
                    Simpan karya <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </form>
        <p class="form-required-note"><span class="required-mark">*</span> Semua kolom wajib diisi</p>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputGambar = document.getElementById('gambar');
        const previewBox = document.getElementById('previewBox');
        const imagePreview = document.getElementById('imagePreview');
        const fileName = document.getElementById('fileName');

        inputGambar.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                fileName.textContent = file.name;
                const reader = new FileReader();

                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                    previewBox.style.display = 'block';
                });

                reader.readAsDataURL(file);
            } else {
                imagePreview.setAttribute('src', '');
                fileName.textContent = '';
                previewBox.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>




