<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        $basePath = preg_replace('~/admin$~', '', dirname($_SERVER['SCRIPT_NAME']));
        header('Location: ' . rtrim($basePath, '/') . '/auth/login.php');
        exit();
    }
}

function check_role($roles = []) {
    check_login();
    if (!in_array($_SESSION['role'], $roles)) {
        die("Akses Ditolak: Anda tidak memiliki hak akses ke halaman ini.");
    }
}
?>
