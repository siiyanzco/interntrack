<?php
require __DIR__ . '/config/koneksi.php';
if (empty($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit;
}
if ($_SESSION['user']['role'] === 'siswa') {
    header('Location: siswa/dashboard.php');
} else {
    header('Location: admin/dashboard.php');
}
exit;
