<?php
// Wajib set $base dan $judul sebelum include file ini
$role = $_SESSION['user']['role'] ?? '';
$menu = [];
if ($role === 'admin' || $role === 'pembimbing') {
    $menu = [
        'admin/dashboard.php' => 'Dashboard',
        'admin/siswa.php' => 'Siswa',
        'admin/perusahaan.php' => 'Perusahaan',
        'admin/pembimbing.php' => 'Pembimbing',
        'admin/penempatan.php' => 'Penempatan',
        'admin/jurnal.php' => 'Jurnal',
        'admin/absensi.php' => 'Absensi',
        'admin/monitoring.php' => 'Monitoring',
        'laporan/index.php' => 'Laporan',
    ];
} elseif ($role === 'siswa') {
    $menu = [
        'siswa/dashboard.php' => 'Dashboard',
        'siswa/profil.php' => 'Profil',
        'siswa/tempat.php' => 'Tempat PKL',
        'siswa/jurnal.php' => 'Jurnal',
        'siswa/absensi.php' => 'Absensi',
        'siswa/status.php' => 'Status PKL',
        'siswa/monitoring.php' => 'Monitoring',
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'InternTrack') ?> - InternTrack</title>
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">InternTrack</div>
    <nav>
        <?php foreach ($menu as $url => $label): ?>
            <a href="<?= $base . $url ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="user">
        <?= e($_SESSION['user']['nama'] ?? '') ?> (<?= e($role) ?>)
        <a class="logout" href="<?= $base ?>auth/logout.php">Logout</a>
    </div>
</header>
<main class="container">
<h2><?= e($judul ?? '') ?></h2>
<?php tampil_flash(); ?>
